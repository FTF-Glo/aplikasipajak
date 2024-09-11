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

// ---------------
// LOCAL FUNCTIONS
// ---------------
function getInfoSPPT($prov,$cityID,$kec,$kel,&$result) {
	global $iErrCode, $sErrMsg, $aClientVar, $aPrefs, $iPPtimeDiff, $DBLink, $json;
	$prov = '16';
	$cityID='1671';
	$OK = false; 
	$sQCondKec = "";
  	$sQCondKel = "";
  
  	//if ($kec !="") $sQCondKec = " AND OP_KECAMATAN_KODE = '{$kec}'";
  	$sQCondKel = " AND OP_KELURAHAN_KODE = '1671010004'";
	
	$qry = "SELECT * FROM (SELECT COUNT(*) AS TOTAL_SPPT FROM cppmod_pbb_sppt_current where OP_PROVINSI_KODE ='{$prov}' and OP_KOTAKAB_KODE = '{$cityID}' {$sQCondKec} {$sQCondKel}) AS A, 
(SELECT COUNT(*) AS TOTAL_PRINTED FROM cppmod_pbb_sppt_current where OP_PROVINSI_KODE ='{$prov}' and OP_KOTAKAB_KODE = '{$cityID}' AND FLAG = 1 {$sQCondKec} {$sQCondKel}) AS B,
(SELECT COUNT(*) AS TOTAL_UNPRINTED FROM cppmod_pbb_sppt_current where OP_PROVINSI_KODE ='{$prov}' and OP_KOTAKAB_KODE = '{$cityID}' AND FLAG = 0 {$sQCondKec} {$sQCondKel}) AS C";
	echo $qry;
	if ($res = mysqli_query($DBLink, $qry)) {
		$OK = true;
		while ($row = mysqli_fetch_assoc($res)) {
			$result["ts"] = $row["TOTAL_SPPT"];
			$result["tp"] = $row["TOTAL_PRINTED"];
			$result["tu"] = $row["TOTAL_UNPRINTED"];
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
$aResponse['result'] = array();


if (getInfoSPPT($pr->p,$pr->c,$pr->kc,$pr->kl,$result))
{
	//print_r($result);
	$aResponse['success'] = true;
	$aResponse['result'] = $result;
}
else 
{
	$aResponse['errcode'] = -1; // return empty or fail

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