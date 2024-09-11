<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();

// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'svr', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/constant.php");
require_once($sRootPath."pc/inc/inc-payment-c.php");
require_once($sRootPath."pc/inc/inc-payment-db-c.php");
require_once($sRootPath."inc/prefs-payment.php");
require_once($sRootPath."inc/db-payment.php");
require_once($sRootPath."inc/ctools.php");
require_once($sRootPath."inc/json.php");
require_once($sRootPath."pc/svr/inc-broadcast.php");

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
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, C_BROADCAST_LOG_FILENAME);
  exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
SCANPayment_Pref_GetAllWithFilter($DBLink, "%PC.%", $aCentralPrefs);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aCentralPrefs [".print_r($aCentralPrefs, true)."]\n", 3, C_BROADCAST_LOG_FILENAME);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, C_BROADCAST_LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);

// ---------------
// LOCAL FUNCTIONS
// ---------------

function GetPPIDCentral($ppid){
	global $DBLink, $DBConn, $aCentralPrefs;
	$central='';
	$sQ = "select * from csccore_down_central_downline";
  $sQ .= " where CSC_DCD_DID = '".CTOOLS_ValidateQueryForDB($ppid, "'", 'MYSQL')."'";
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, C_BROADCAST_LOG_FILENAME);
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    if ($nRes > 0)
    {
      if ($row = mysqli_fetch_array($res, MYSQL_ASSOC))
      {
        $central=$row['CSC_DCD_CID'];
      }
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, C_BROADCAST_LOG_FILENAME);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, C_BROADCAST_LOG_FILENAME);
  }
	return $central;
}

function GetBroadcastMessageList($ClientInfo, $sClientRemoteAddress)
{
  global $DBLink, $DBConn, $aCentralPrefs;

  //print_r($ClientInfo);

  $aBroadcastMsg = Array();
  $central=GetPPIDCentral($ClientInfo->ppid);
  // get available broadcast messages

  $sQ = "select * from CPCCORE_BROADCAST_MESSAGE";
  $sQ .= " where (CPC_BM_FORCENTRAL='' or (CPC_BM_FORCENTRAL!='' and  (CPC_BM_FORCENTRAL like '%".CTOOLS_ValidateQueryForDB($central, "'", 'MYSQL')."%' or '".CTOOLS_ValidateQueryForDB($central, "'", 'MYSQL')."' rlike CPC_BM_FORCENTRAL))) and  (CPC_BM_FORCLIENT = '' or (CPC_BM_FORCLIENT != ''  and (CPC_BM_FORCLIENT like '%".CTOOLS_ValidateQueryForDB($ClientInfo->ppid, "'", 'MYSQL')."%' or '".CTOOLS_ValidateQueryForDB($ClientInfo->ppid, "'", 'MYSQL')."' rlike CPC_BM_FORCLIENT)))  and (CPC_BM_END > '".$ClientInfo->svrdt."') order by CPC_BM_START asc";
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, C_BROADCAST_LOG_FILENAME);
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    $aBroadcastMsg['n'] = $nRes;
    $aBroadcastMsg['a'] = array();
    if ($nRes > 0)
    {
      $i = 0;
      while ($row = mysqli_fetch_array($res, MYSQL_ASSOC))
      {
        $aBroadcastMsg['a'][$i] = array();
        $aBroadcastMsg['a'][$i]['k'] = $row['CPC_BM_ID'];
        $aBroadcastMsg['a'][$i]['m'] = $row['CPC_BM_MSG'];
        $aBroadcastMsg['a'][$i]['s'] = $row['CPC_BM_START'];
        $aBroadcastMsg['a'][$i]['e'] = $row['CPC_BM_END'];

        $i++;
      }
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, C_BROADCAST_LOG_FILENAME);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, C_BROADCAST_LOG_FILENAME);
  }

  return $aBroadcastMsg;
} // end of GetAvailableUpdateList

// ------------
// MAIN PROGRAM
// ------------

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] _REQUEST [".print_r($_REQUEST, true)."]\n", 3, C_BROADCAST_LOG_FILENAME);

// get remote parameters
$sClientInfo = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$sClientRemoteAddress = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

// TEST PURPOSE ONLY (comment all following test codes from operational use)
//$sClientRemoteAddress = "127.0.0.1";
//$sClientInfo = base64_encode('{ppid:"12345678901234",svrdt:"2007-10-30 10:00:00"}');
// end of TEST PURPOSE ONLY

$sJSONClientInfo = @base64_decode($sClientInfo);
if ($sJSONClientInfo != '')
{
  $ClientInfo = $json->decode($sJSONClientInfo);
}

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sJSONClientInfo [$sJSONClientInfo] Client Address [$sClientRemoteAddress]\n", 3, C_BROADCAST_LOG_FILENAME);

$aResponse = GetBroadcastMessageList($ClientInfo, $sClientRemoteAddress);
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
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response [$sResponse] Final Response [$sFinalResponse]\n", 3, C_BROADCAST_LOG_FILENAME);
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, C_BROADCAST_LOG_FILENAME);
}

ob_end_flush();
?>

