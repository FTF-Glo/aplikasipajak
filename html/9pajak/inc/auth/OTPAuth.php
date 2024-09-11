<?php

$OTPDBLink = NULL;
$OTPDBConn = NULL;
SCANPayment_ConnectToDB($OTPDBLink, $OTPDBConn, OTP_DBHOST, OTP_DBUSER,OTP_DBPWD, OTP_DBNAME);
if ($iErrCode != 0) {
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

class OTPAuth extends AuthBase {

	// render input
	public function element() {
		return array(
			array("label" => "OTP", "id" => "otp", "type" => "text", "autocomplete" => "on","input"=>"<input type='text' id='otp' name='otp' value='' autocomplete='on'></input>")
		);
	}
	
	// authentication process
	public function auth(&$input, &$arResponse) {
		global $User, $OTPDBLink;
		
		$otp = $input["otp"];
		$ppid = isset($input["ppid"])?$input["ppid"]:$arResponse["ppid"];
		
		$usr = $input["usr"];
		$pwd = $input["pwd"];
		$sUID = "";
		$auth = $User->IsAuthUser($usr, $pwd, $sUID);
		
		$status = null;
		//var_dump( $input);
		if ($User->IsO2wUser($sUID)) {
			$User->CheckOTPPass($ppid, $otp, $status, $OTPDBLink);
			if ($status == 0) {
				// Login succeed
				return true;
			} else if ($status == -1) {
				// No PPID
				$arResponse["error"] = "PPID '$ppid' is not registered";
			} else if ($status == -2) {
				// Wrong Password
				$arResponse["error"] = "Wrong OTP";
			} else if ($status == -3) {
				// Password Expired
				$arResponse["error"] = "OTP already expired";
			} else if ($status == -4) {
				// Password Hasn't Inited
				$arResponse["error"] = "OTP has not requested";
			}
			
			return false;
		} else {
			return true;
		}
		
		// die();
	}
}

?>
