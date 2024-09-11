<?php
//error_reporting(E_ALL);
class SCANPaymentPointDBSession
{
  private $iDebug = 0;
  private $sLogFileName = "";

  private $DBLink = NULL;
  private $iInterval = 180;

  private $sThisFile;

  private $iErrCode = 0;
  private $sErrMsg = '';

  function __construct($iDebug = 0, $sLogFileName, $DBLink, $iInterval = 180)
  {
    $this->iDebug = $iDebug;
    $this->sLogFileName = $sLogFileName;

    $this->DBLink = $DBLink;
    $this->iInterval = $iInterval;

    $this->sThisFile = $this->sThisFile;
  } // end of __construct

  public function GenerateSession($sUID, $sUName, $sOther)
  {
    return md5($sUID.'.'.$sUName.'.'.$sOther.'.'.time());
  } // end of __construct

  // return  0 : valid
  //        -1 : session expired
  //        -2 : not login
  public function CheckSession($sUID, $sSID)
  {
    $iSessionStatus = -2; // not logged-in

    // check in PP system session table
    // retrieve session data
    //$sUID = (isset($_COOKIE['onpays_pp_ud']) ? base64_decode($_COOKIE['onpays_pp_ud']) : '');
    if (trim($sUID) != '')
    {
      $sQ = "select CPC_CUS_LASTSESSION from cpccore_user_session where CPC_CUS_ID='".CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL")."' and CPC_CUS_SESSION='".CTOOLS_ValidateQueryForDB($sSID, "'", "MYSQL")."'";
      if ($res = mysqli_query($this->DBLink, $sQ))
      {
        $nRes = mysqli_num_rows($res);
        $nRecord = $nRes;
        if ($nRes > 0)
        {
          // check session expiration
          $row = mysqli_fetch_array($res);
          $iLastSession = intval(strtotime($row['CPC_CUS_LASTSESSION']));
          if (time() - $iLastSession <= $this->iInterval)
            $iSessionStatus = 0; // session is still valid
          else {
            $iSessionStatus = -1; // session is expired
          }
        }
      }
      else
      {
        $this->iErrCode = -3;
        $this->sErrMsg = mysqli_error($this->DBLink);
        if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
          error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
      }
    }

    return $iSessionStatus;
  } // end of CheckSession

  public function SetSessionCookie($sCookieName, $sCookieVal)
  {
    setcookie($sCookieName, $sCookieVal, time() + $this->iInterval);
  } // end of SetSessionCookies

  public function GetSessionCookie($sCookieName)
  {
    $sCookieVal = (isset($_COOKIE[$sCookieName]) ? $_COOKIE[$sCookieName] : '');
    return $sCookieVal;
  } // end of GetSessionCookies

  public function SaveSessionToDB($sUID, $sSID,$sPP)
  {
    $bOK = false;

    if (trim($sUID) != '')
    {
      $sQ = "insert into cpccore_user_session(CPC_CUS_ID, CPC_CUS_SESSION, CPC_CUS_FROM_PP,CPC_CUS_IP,CPC_CUS_LASTSESSION) values('$sUID', '$sSID','$sPP','".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')."', '".strftime("%Y-%m-%d %H:%M:%S", time())."')";
      //echo $sQ;
      if (mysqli_query($this->DBLink, $sQ))
      {
        $bOK = true;
      }
      else
      {
        $this->iErrCode = -3;
        $this->sErrMsg = mysqli_error($this->DBLink);
        if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
          error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
      }
    }

    return $bOK;
  } // end of SaveSessionToDB

  public function DeleteSessionFromDB($sUID)
  {
    $bOK = false;

    if (trim($sUID) != '')
    {
      $sQ = "delete from cpccore_user_session where CPC_CUS_ID='$sUID'";
      if (mysqli_query($this->DBLink, $sQ))
      {
        $bOK = true;
      }
      else
      {
        $this->iErrCode = -3;
        $this->sErrMsg = mysqli_error($this->DBLink);
        if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
          error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFileName);
      }
    }

    return $bOK;
  } // end of UpdateSessionInDB

  public function UpdateSessionInDB($sUID, $sSID,$sPP)
  {
    $bOK = true;

    $this->DeleteSessionFromDB($sUID);
    $this->SaveSessionToDB($sUID, $sSID,$sPP);

    return $bOK;
  } // end of UpdateSessionInDB

} // end of SCANPaymentPointDBSession

?>
