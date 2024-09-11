<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();
error_reporting(E_ALL);
// includes
// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'pc'.DIRECTORY_SEPARATOR.'svr'.DIRECTORY_SEPARATOR.'central', '', dirname(__FILE__))).'/';
require_once("inc/payment/constant.php");
require_once("inc/payment/db-payment.php");
require_once("inc/payment/ctools.php");
require_once("inc/payment/json.php");
require_once("inc/payment/inc-payment-c.php");
require_once("inc/payment/inc-payment-db-c.php");
require_once("inc/central/session-central.php");
require_once("inc/central/user-central.php");

// start stopwatch
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
	$iStart = microtime(true);
}

// global variables
$iCentralTS = time();
$iErrCode = 0;
$sErrMsg = '';
$sResponse = '';
$DBLink = NULL;
$DBConn = NULL;
$aCentralPrefs = NULL;

// Payment related initialization
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$Session = new SCANCentralDBSession(DEBUG, LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
$User = new SCANCentralUser(DEBUG, LOG_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aCentralPrefs [".print_r($aCentralPrefs, true)."]\n", 3, LOG_FILENAME);

// ---------------
// LOCAL FUNCTIONS
// ---------------
function login($usr, $pwd) {
	global $User, $Session;
	
	$sUID = '';
	$aResponse = array();
	$auth = $User->IsAuthUser($usr, $pwd, $sUID);
	$blocked = $User->IsBlockedUser($usr);
	$remoteAddr = $_SERVER['REMOTE_ADDR'];
	
	if ($blocked) {
		// User blocked
		$aResponse["error"] = "Username '$usr' is blocked";
	} else {
		if ($auth) {
			$canMultipleLogin = $User->IsMultipleLogin($sUID);
			
			$loginable = true;
			if (!$canMultipleLogin) {
				// Multiple login not permitted, check first
				$anotherLogin = $Session->CheckAnotherLogin($sUID, $usr, $addr);
				if ($anotherLogin && $addr != $remoteAddr) {
					$aResponse["error"] = "Username '$usr' still logged in<br /> from another computer";
					$loginable = false;
				}
			}
		
			if ($loginable) {
			// set session
				$sSID = $Session->GenerateSession($sUID, $usr, $remoteAddr);
				$Session->SaveSessionToDB($sUID, $sSID);
				$aResponse['session'] = $sSID;
				$aResponse['uname'] = $usr;
				$aResponse['uid'] = $sUID;
				
				setcookie("errorUser", "", time() - 10);
				setcookie("errorAttempt", "", time() - 10);
			}
		} else {
			// Jika $sUID == null, username tidak terdaftar
			// Jika $sUID != null, password salah
			if ($sUID == null) {
				// No username
				$usr = htmlentities($usr, ENT_QUOTES);
				$aResponse["error"] = "Username '$usr' not registered";
			} else {
				// Increase error attempt jika user sama dengan sebelumnya
				$errorAttempt = 0;
				if (isset($_COOKIE["errorUser"])) {
					$errorUser = $_COOKIE["errorUser"];
					if ($errorUser == $usr) {
						$errorAttempt = $_COOKIE["errorAttempt"];
					}
				}
				
				// NEW: counter wrong password increment
				// Set error attempt
				$errorAttempt++;
				setcookie("errorUser", $usr, time() + 3600);
				setcookie("errorAttempt", $errorAttempt, time() + 3600);
				echo $errorAttempt;
				if ($errorAttempt >= 3) {
					// Auto block
					$blocked = $User->ChangeBlockUser($sUID, true);
					if ($blocked) {
						$aResponse["error"] = "Wrong password, user '$usr' blocked";
					} else {
						$aResponse["error"] = "Wrong password, user '$usr' (not) blocked";
					}
				} else {
					$aResponse["error"] = "Wrong password, attempt $errorAttempt";
				}
			}
		}
	}
	
	return $aResponse;
}

ob_end_flush();
?>
