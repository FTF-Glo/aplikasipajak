<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();

// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'pc'.DIRECTORY_SEPARATOR.'svr', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/constant.php");
require_once($sRootPath."pc/inc/inc-payment-c.php");
require_once($sRootPath."pc/inc/inc-payment-db-c.php");
require_once($sRootPath."inc/prefs-payment.php");
require_once($sRootPath."inc/db-payment.php");
require_once($sRootPath."inc/ctools.php");
require_once($sRootPath."inc/json.php");
require_once($sRootPath."inc/uuid.php");
require_once($sRootPath."pc/svr/inc-msg.php");

// start stopwatch
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
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
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, C_MESSAGE_LOG_FILENAME);
  exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
SCANPayment_Pref_GetAllWithFilter($DBLink, "%PC.%", $aCentralPrefs);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aCentralPrefs [".print_r($aCentralPrefs, true)."]\n", 3, C_MESSAGE_LOG_FILENAME);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, C_MESSAGE_LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);

// ---------------
// LOCAL FUNCTIONS
// ---------------

function SavePaymentPointMessage($ClientInfo, $sClientRemoteAddress)
{
  global $iErrCode, $sErrMsg, $DBLink, $DBConn, $aCentralPrefs;

  //print_r($ClientInfo);

  // get available broadcast messages
  $sQ = "insert into CPCCORE_PAYMENT_POINT_MESSAGE(CPC_PPM_ID, CPC_PPM_PPID, CPC_PPM_MODULE, CPC_PPM_MSG, CPC_PPM_SENT, CPC_PPM_OPERATORUID, CPC_PPM_OPERATORUNAME,CPC_PPM_MSGTYPE) values('".c_uuid()."', '".CTOOLS_ValidateQueryForDB(@isset($ClientInfo->ppid) ? $ClientInfo->ppid : '', "'", 'MYSQL')."', '".CTOOLS_ValidateQueryForDB(@isset($ClientInfo->m) ? $ClientInfo->m : '', "'", 'MYSQL')."', '".CTOOLS_ValidateQueryForDB(@isset($ClientInfo->msg) ? $ClientInfo->msg : '', "'", 'MYSQL')."', '".strftime("%Y-%m-%d %H:%M:%S", time())."', '".CTOOLS_ValidateQueryForDB(@isset($ClientInfo->uid) ? $ClientInfo->uid : '', "'", 'MYSQL')."', '".CTOOLS_ValidateQueryForDB(@isset($ClientInfo->un) ? $ClientInfo->un : '', "'", 'MYSQL')."',".intval(isset($ClientInfo->mt)?$ClientInfo->mt:99).")";
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, C_MESSAGE_LOG_FILENAME);
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $iErrCode = 0;
    $sErrMsg = '';
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, C_MESSAGE_LOG_FILENAME);
  }
} // end of SavePaymentPointMessage

// ------------
// MAIN PROGRAM
// ------------

// get remote parameters
$sClientInfo = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$sClientRemoteAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

// TEST PURPOSE ONLY (comment all following test codes from operational use)
//$sClientRemoteAddress = "127.0.0.1";
//$sClientInfo = base64_encode('{ppid:"12345678901234",m:"m1",msg:"Tolong dong!",uid:"u1",un:"comm"}');
// end of TEST PURPOSE ONLY

$sJSONClientInfo = @base64_decode($sClientInfo);
if ($sJSONClientInfo != '')
{
  $ClientInfo = $json->decode($sJSONClientInfo);
}

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sJSONClientInfo [$sJSONClientInfo] Client Address [$sClientRemoteAddress]\n", 3, C_MESSAGE_LOG_FILENAME);

SavePaymentPointMessage($ClientInfo, $sClientRemoteAddress);

$aResponse['success'] = ($iErrCode == 0);
$sResponse = $json->encode($aResponse);
$sFinalResponse = base64_encode($sResponse);

//header("content-type: application/json; charset=utf-8");
header("content-type: text/plain");
header("Content-length: ".strlen($sFinalResponse));
echo $sFinalResponse;

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
	$iEnd = microtime(true);
	$iExec = $iEnd - $iStart;
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response [$sResponse] Final Response [$sFinalResponse]\n", 3, C_MESSAGE_LOG_FILENAME);
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, C_MESSAGE_LOG_FILENAME);
}

ob_end_flush();
?>

