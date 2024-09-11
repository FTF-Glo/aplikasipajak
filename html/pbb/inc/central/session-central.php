<?php
date_default_timezone_set("Asia/Jakarta");

class SCANCentralDBSession {
	private $iDebug = 0;
	private $sLogFileName = "";

	private $DBLink = NULL;
	private $iInterval = 180;

	private $sThisFile;

	private $iErrCode = 0;
	private $sErrMsg = '';

	function __construct($iDebug = 0, $sLogFileName, $DBLink, $iInterval = 180) {
		$this->iDebug = $iDebug;
		$this->sLogFileName = $sLogFileName;

		$this->DBLink = $DBLink;
		$this->iInterval = $iInterval;

		$this->sThisFile = $this->sThisFile;
	}

	public function GenerateSession($sUID, $sUName, $sOther) {
		return md5($sUID.'.'.$sUName.'.'.$sOther.'.'.time());
	}

	// return  0 : valid
	//        -1 : session expired
	//        -2 : not login
	public function CheckSession($sUID, $sSID) {
		$iSessionStatus = -2; // not logged-in

		// check in PP system session table
		// retrieve session data
		//$sUID = (isset($_COOKIE['onpays_pp_ud']) ? base64_decode($_COOKIE['onpays_pp_ud']) : '');
		if (trim($sUID) != '') {
			$sQ = "select CTR_CUS_LASTSESSION from central_user_session where CTR_CUS_ID = '" . CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL") .
				"' and CTR_CUS_SESSION = '" . CTOOLS_ValidateQueryForDB($sSID, "'", "MYSQL") . "'";

			if ($res =mysqli_query($this->DBLink, $sQ)) {
				$nRes = mysqli_num_rows($res);
				$nRecord = $nRes;
				if ($nRes > 0) {
					// check session expiration
					$row = mysqli_fetch_array($res);
					$iLastSession = intval(strtotime($row['CTR_CUS_LASTSESSION']));
					if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
						error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [SESSION] uid=$sUID, iLastSession=$iLastSession, interval=".$this->iInterval." now=".time()."\n", 3, $this->sLogFileName);
					if (time() - $iLastSession <= $this->iInterval) {
						$iSessionStatus = 0; // session is still valid
					} else {
						$iSessionStatus = -1; // session is expired
					}
				}
			} else {
				$this->iErrCode = -3;
				$this->sErrMsg = mysqli_error($this->DBLink);
					if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
						error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
			}
		}

		return $iSessionStatus;
	}
  
	public function CheckAnotherLogin($sUID, $usr, &$addr) {
		$anotherLogin = false;
		if (trim($sUID) != '') {
			$sQ = "select CTR_CUS_LASTSESSION, CTR_CUS_IP from central_user_session where " .
					" CTR_CUS_ID = '" . CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL") . "' " .
					// " and CTR_CUS_IP = '" . CTOOLS_ValidateQueryForDB($remoteAddr, "'", "MYSQL") . "' " .
					" order by CTR_CUS_LASTSESSION desc limit 1";
			//echo $sQ;
			if ($res =mysqli_query($this->DBLink, $sQ)) {
				$nRes = mysqli_num_rows($res);
				$nRecord = $nRes;
				if ($nRes > 0) {
					// check session expiration
					$row = mysqli_fetch_array($res);
					$addr = $row['CTR_CUS_IP'];
					$iLastSession = intval(strtotime($row['CTR_CUS_LASTSESSION']));
					if (time() - $iLastSession <= $this->iInterval) {
						$anotherLogin = true;
					}
				}
			} else {
				$this->iErrCode = -3;
				$this->sErrMsg = mysqli_error($this->DBLink);
				if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
					error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
			}
		}
		
		return $anotherLogin;
	}

	public function SetSessionCookie($sCookieName, $sCookieVal) {
		setcookie($sCookieName, $sCookieVal, time() + $this->iInterval);
	}

	public function GetSessionCookie($sCookieName) {
		$sCookieVal = (isset($_COOKIE[$sCookieName]) ? $_COOKIE[$sCookieName] : '');
		return $sCookieVal;
	}

	public function SaveSessionToDB($sUID, $sSID) {
		$bOK = false;

		if (trim($sUID) != '') {
			$sQ = "insert into central_user_session(CTR_CUS_ID, CTR_CUS_SESSION, CTR_CUS_IP, CTR_CUS_LASTSESSION) " . 
				"values('$sUID', '$sSID','" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', '" .
				strftime("%Y-%m-%d %H:%M:%S", time())."')";
			//echo $sQ;
			if (mysqli_query($this->DBLink, $sQ)) {
				$bOK = true;
			} else {
				$this->iErrCode = -3;
				$this->sErrMsg = mysqli_error($this->DBLink);
				if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
					error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
			}
		}

		return $bOK;
	}

	public function DeleteSessionFromDB($sUID, $sSID = "") {
		$bOK = false;

		if (trim($sUID) != '') {
			$sQ = "delete from central_user_session where CTR_CUS_ID = '$sUID'";
			if ($sSID != "") {
				$sQ .= " AND CTR_CUS_SESSION = '$sSID'";
			}
			
			// echo "sQ = $sQ<br />\n";
			if (mysqli_query($this->DBLink, $sQ)) {
				$bOK = true;
			} else {
				$this->iErrCode = -3;
				$this->sErrMsg = mysqli_error($this->DBLink);
				if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
					error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
			}
		}

		return $bOK;
	}

	public function GetIpAddress($sUID, $sSID) {
		$sQ = "select CTR_CUS_IP from central_user_session where CTR_CUS_ID = '" . CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL") .
			"' and CTR_CUS_SESSION = '" . CTOOLS_ValidateQueryForDB($sSID, "'", "MYSQL") . "'";
		if ($res =mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			$nRecord = $nRes;
			if ($nRes > 0) {
				// check session expiration
				if ($row = mysqli_fetch_array($res)) {
					$ip = $row['CTR_CUS_IP'];
					return $ip;
				}
			}
		} else {
			$this->iErrCode = -3;
			$this->sErrMsg = mysqli_error($this->DBLink);
			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
		}
		
		return null;
	}

	public function UpdateSessionInDB($sUID, $sSID) {
		$bOK = true;

		$sQ = "update central_user_session set CTR_CUS_LASTSESSION = '" . strftime("%Y-%m-%d %H:%M:%S", time()) . "' where CTR_CUS_ID = '$sUID' AND CTR_CUS_SESSION = '$sSID'";
		if (mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			$this->iErrCode = -3;
			$this->sErrMsg = mysqli_error($this->DBLink);
			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
		}

		return $bOK;
	}
	
	public function CleanUpSessionInDB($sUID) {
		$bOK = true;

		$sQ = "delete from central_user_session where CTR_CUS_ID = '$sUID' AND date(CTR_CUS_LASTSESSION) < date(now())";
		if (mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			$this->iErrCode = -3;
			$this->sErrMsg = mysqli_error($this->DBLink);
			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
		}

		return $bOK;
	}
	
	public function DeleteOtherLogin($sUID, $addr) {
		$bOK = true;

		$sQ = "delete from central_user_session where CTR_CUS_ID = '$sUID' AND CTR_CUS_IP <> '$addr'";
		if (mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			$this->iErrCode = -3;
			$this->sErrMsg = mysqli_error($this->DBLink);
			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
		}

		return $bOK;
	}
	
	public function GetAllSession($username) {
		$username = mysqli_real_escape_string($this->DBLink, $username);
	
		$sQ = "select S.CTR_CUS_ID,U.CTR_U_UID, S.CTR_CUS_SESSION, S.CTR_CUS_LASTSESSION, S.CTR_CUS_IP from central_user_session S, central_user U " .
				"where U.CTR_U_ID = S.CTR_CUS_ID and U.CTR_U_UID like '%$username%'";
	
		// echo $sQ;
		$arSession = null;
		if ($res =mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				// check session expiration
				$i = 0;
				$arSession = array();
				while ($row = mysqli_fetch_array($res)) {
					$arSession[$i]["id"] = $row["CTR_CUS_ID"];
					$arSession[$i]["uid"] = $row["CTR_U_UID"];
					$arSession[$i]["session"] = $row["CTR_CUS_SESSION"];
					$arSession[$i]["lastSession"] = $row["CTR_CUS_LASTSESSION"];
					$arSession[$i]["ip"] = $row["CTR_CUS_IP"];
					
					$stillActive = false;
					$lastSession = intval(strtotime($row['CTR_CUS_LASTSESSION']));
					if (time() - $lastSession <= $this->iInterval) {
						$stillActive = true;
					}
					$arSession[$i]["stillActive"] = $stillActive;
					$i++;
				}
			}
		}
		
		// var_dump($arSession);
		
		return $arSession;
	}
}

?>
