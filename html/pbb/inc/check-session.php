<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'inc', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/inc-dms-c.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/central/session-central.php");

function stillInSession($DBLink,$json,&$rdata) {
	global $data;
	$activeTime=ONPAYS_SESSION_INTERVAL;
	$Session = new SCANCentralDBSession(DEBUG, LOG_DMS_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
	$cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
	// echo $cData."<br/>";
	if($data==null){
		if ($cData!="") {
			$decData = base64_decode($cData);
			// echo "decdata:".$decData."<br/>";
			if ($decData) {
				$data = $json->decode($decData);
				$rdata=$data;
			}
		}
	}
	$inSession = -2;
	
	if ($data) {
		$uid = $data->uid;
		$sid = $data->session;
		
		$inSession = $Session->CheckSession($uid, $sid);
		if ($inSession == 0) {
			// update session
			$Session->UpdateSessionInDB($uid, $sid);
			setcookie("centraldata", $cData, time() + $activeTime);
		} else if ($inSession == -1) {
			// expired session
			// delete data & cookies
			setcookie("centraldata", "", time() - 10);
			$data = null;
		} else {
			if (isset($cData)) {
				// pre-empted by other login
				
				// delete data & cookies
				setcookie("centraldata", "", time() - 10);
				$data = null;
			}
		}
	}
	
	return ($inSession == 0);
}


function stillInSession2() {
	global $Session, $cData, $data, $activeTime, $errorLogin;
	$inSession = -2;
	
	if ($data) {
		$uid = $data->uid;
		$sid = $data->session;
		
		$inSession = $Session->CheckSession($uid, $sid);
		// $inSession = -1;
		if ($inSession == 0) {
			// update session
			$Session->UpdateSessionInDB($uid, $sid);
			setcookie("centraldata", $cData, time() + $activeTime);
		} else if ($inSession == -1) {
			// expired session
			$Session->DeleteSessionFromDB($uid, $sid);
		
			// delete data & cookies
			setcookie("centraldata", "", time() - 10);
			$data = null;
			
			// set error message
			// $errorLogin = "The session has expired. Please login again.";
			setcookie("errorLogin", "The session has expired. Please login again.", time() + $activeTime);			
			header("Location: main.php");
		} else {
			if (isset($cData)) {
				// pre-empted by other login
				
				// delete data & cookies
				setcookie("centraldata", "", time() - 10);
				$data = null;
				
				$errorLogin = "Forced login from other computer";
				
				setcookie("errorLogin", "The session has expired. Please login again.", time() + $activeTime);
				header("Location: main.php");
			}
		}
	}
	
	return ($inSession == 0);
}

?>