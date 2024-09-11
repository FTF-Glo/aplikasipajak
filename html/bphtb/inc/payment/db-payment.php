<?php
set_time_limit(0);
//set_include_path(get_include_path().PATH_SEPARATOR.str_replace(DIRECTORY_SEPARATOR.'inc', '', dirname(__FILE__)));
// $sRootPathDBPayment = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'inc', '', dirname(__FILE__)).'/');
require_once("constant.php");
require_once("ctools.php");

// $iErrCode, $sErrMsg must be declared as global variables
function SCANPayment_ConnectToDB(&$DBLink, &$DBConn, $sDBHost, $sDBUser, $sDBPwd, $sDBName, $newLink=false)
{
  global $iErrCode, $sErrMsg;
  // die(var_dump($sDBHost, $sDBUser, $sDBPwd, $sDBName));
  $iErrCode = 0;
  $sErrMsg = '';

  $sThisFile = basename(__FILE__);
  // var_dump(mysqli_connect($sDBHost, $sDBUser, $sDBPwd, $sDBName));die();

  // AKAR MASALAH SOLVED
  $explodeHost = explode(':', $sDBHost);
  $newHost = $explodeHost[0]; 
  $newPort = isset($explodeHost[1]) ? $explodeHost[1] : '3306';

  // if ($DBConn = mysqli_connect($sDBHost, $sDBUser, $sDBPwd, $sDBName))
  if ($DBConn = mysqli_connect($newHost, $sDBUser, $sDBPwd, $sDBName, $newPort))
  {
    $DBLink = $DBConn;
    // var_dump($DBLink);die;
    if ($DBConn = mysqli_select_db($DBLink, $sDBName))
    {
      
    }
    else
    {
      $iErrCode = -2;
      $sErrMsg = mysqli_error($DBLink);
      if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log ("[".$sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    }
  }
  else
  {
    $iErrCode = -1;
    //$sErrMsg = mysqli_error();
	
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".$sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }
} // end of SCANPayment_ConnectToDB

function SCANPayment_CloseDB(&$DBLink)
{
  @mysqli_close($DBLink);
} // end of SCANPayment_CloseDB
?>
