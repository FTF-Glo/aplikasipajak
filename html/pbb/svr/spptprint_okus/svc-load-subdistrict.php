<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");


error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

ob_start();
// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'svr'.DIRECTORY_SEPARATOR.'spptprint', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/log-payment.php");
require_once($sRootPath."inc/payment/sayit.php");

// start stopwatch
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
	$iStart = microtime(true);
}

// global variables
$iCentralTS = time();
$iErrCode = 0;
$sErrMsg = '';
$DBLink = NULL;
$DBConn = NULL;
$sUID = '';
$sUName = '';
$bMLPOSignedOn = false; // pp is not signed-on MLPO system (use by NetMan)

// Payment related initialization
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

SCANPayment_Pref_GetAllWithFilter($DBLink, "PC.%", $aCentralPrefs);
//var_dump($aCentralPrefs);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);


function getSubDistrict($cityID,&$result) {
	global $iErrCode, $sErrMsg, $aClientVar, $aPrefs, $iPPtimeDiff, $DBLink, $json;
	$OK = false; 
	$qry = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID = '{$cityID}' ORDER BY CPC_TKC_KECAMATAN";
	if ($res = mysqli_query($DBLink, $qry)) {
		$OK = true;
		$i = 0;
		$ii = 0;
		while ($row = mysqli_fetch_assoc($res)) {
			$result["sbd"][$i]["id"] = $row["CPC_TKC_ID"];
			$result["sbd"][$i]["name"] = $row["CPC_TKC_KECAMATAN"];
		
			$qry = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID = '{$row["CPC_TKC_ID"]}' ORDER BY CPC_TKL_KELURAHAN";
			if ($resx = mysqli_query($DBLink, $qry)) {
				$OK = true;
				while ($rowx = mysqli_fetch_assoc($resx)) {
					$result["kel"][$ii]["id"] = $rowx["CPC_TKL_ID"];
					$result["kel"][$ii]["idk"] = $row["CPC_TKC_ID"];
					$result["kel"][$ii]["name"] = $rowx["CPC_TKL_KELURAHAN"];
					$ii++;
				}
				
			} 
			$i++;
		}
		
	} 
	return $OK;
}

// ------------
// MAIN PROGRAM
// ------------

// get remote parameters

$sQueryString = (@isset($_REQUEST['q']) ? $_REQUEST['q'] : ''); // because of post (form-urlencoded)
$sClientRemoteAddress = $_SERVER['REMOTE_ADDR'];
 

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sQueryString] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);
	
$aResponse = array();
$aResponse['success'] = true;
$aResponse['errcode'] = 0;




if ($sQueryString != '')
{
    $sBlockReq = base64_decode($sQueryString);

    if (CTOOLS_IsInFlag(DEBUG, DEBUG_INFO))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Payment point do data check for [$sBlockReq]\n", 3, LOG_FILENAME.'-data_check');

    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQueryString [$sQueryString] sBlockReq [$sBlockReq]\n", 3, LOG_FILENAME);

    if (trim($sBlockReq) != '')
    {
		$pr = $json->decode($sBlockReq);
		
          if ($pr->k != "0") getSubDistrict($pr->c,$res);
		  $aResponse['success'] = true;
		  $aResponse['subdistrict'] = $res;
    }
    else // $sBlockReq == ''
    {
      $aResponse['errcode'] = -2; // request decode return empty string
    }
}
else
{
  $aResponse['errcode'] = -3; // invalid request (require more specific stuffs)
}

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aResponse [".print_r($aResponse, true)."]\n", 3, LOG_FILENAME);

$sResponse = $json->encode($aResponse);

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] JSON Response [$sResponse]\n", 3, LOG_FILENAME);

//header("content-type: application/json; charset=utf-8");

echo $sResponse;

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
	$iEnd = microtime(true);
	$iExec = $iEnd - $iStart;
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, LOG_FILENAME);
}

ob_end_flush();

?>