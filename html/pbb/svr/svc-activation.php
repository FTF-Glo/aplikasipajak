<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();
error_reporting(E_ALL);
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
$HASH="VSI@2009Started";
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
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
SCANPayment_Pref_GetAllWithFilter($DBLink, "%PC.%", $aCentralPrefs);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aCentralPrefs [".print_r($aCentralPrefs, true)."]\n", 3, LOG_FILENAME);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, C_MESSAGE_LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);

// ---------------
// LOCAL FUNCTIONS
// ---------------

function isPPIDExist($sPPID,&$RegInfo){
	global $DBLink,$DBConn;
	$bOK=false;
	$RegInfo=Array();
	$sQ="SELECT CSC_CD_ACTIVATE,CSC_CD_REGKEY,CSC_CD_MAINTENANCEKEY,CSC_CD_USE_ACTIVATION FROM csccore_central_downline WHERE CSC_CD_ID='".$sPPID."'";
	if ($res = mysqli_query($DBLink, $sQ))
	{
		if(($row=mysqli_fetch_array($res))){
			$bOK=true;
			$RegInfo=$row;
		}
		mysqli_free_result($res);
	}
	return $bOK;
}

function updatePPIDActivation($sPPID,$RegKey){
	global $DBLink,$DBConn;
	$bOK=false;
	$RegInfo=Array();
	$sQ="UPDATE csccore_central_downline SET CSC_CD_ACTIVATE=NOW(),CSC_CD_REGKEY='".$RegKey."' WHERE CSC_CD_ID='".$sPPID."'";
	if ($res = mysqli_query($DBLink, $sQ))
	{
		if(mysql_affected_rows($DBLink)==1){
			$bOK=true;
		}
	}
	return $bOK;
}

function isPPIDHaveValidCentral($sPPID,$sCentral){
	global $DBLink,$DBConn;
	$bOK=false;
	$sQ="SELECT CSC_DCD_DID FROM csccore_down_central_downline WHERE CSC_DCD_DID='".$sPPID."' AND CSC_DCD_CID='".$sCentral."'";
	if ($res = mysqli_query($DBLink, $sQ))
	{
		if(($row=mysqli_fetch_array($res))){
			$bOK=true;
		}
		mysqli_free_result($res);
	}
	return $bOK;
}

function isValidMaintenanceKey($ppid,$central,$Mkey){
	global $DBLink,$DBConn,$HASH;
	$vmkey=md5($ppid+$HASH+$central);
	return ($vmkey==$Mkey);
}

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

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] REQUEST ".print_r($_REQUEST,TRUE)."\n", 3, LOG_FILENAME);


$sJSONClientInfo = @base64_decode($sClientInfo);

if ($sJSONClientInfo != '')
{
  $ClientInfo = $json->decode($sJSONClientInfo);
}


if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] ACTIVATE INFO from Client ".print_r($ClientInfo,TRUE)."  sJSONClientInfo [$sJSONClientInfo] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);

$sUID='';
$useActivation=1;
if (isPPIDExist($ClientInfo->ppid,$RegInfo))
{
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] ACTIVATE INFO from Client ".print_r($ClientInfo,TRUE)."  sJSONClientInfo [".print_r($RegInfo,TRUE)."] \n", 3, LOG_FILENAME);
	if (isPPIDHaveValidCentral($ClientInfo->ppid,$ClientInfo->central))
	{
		  if(($RegInfo[0]!=null && $RegInfo[0]!="") && ($RegInfo[1]!=null && $RegInfo[1]!="") && ($RegInfo[2]==null || $RegInfo[2]=="") && ($RegInfo[3]!=0) ){
				//already activated
				$iErrCode=-5;
		  }else if(($RegInfo[0]!=null && $RegInfo[0]!="") && ($RegInfo[1]==null || $RegInfo[1]=="" ) && ($RegInfo[2]==null || $RegInfo[2]=="") && ($RegInfo[3]!=0) ){
				//lost regkey
				$iErrCode=-6;
		  }else if(($RegInfo[3]!=0) ) {
			  //not active
			  if($RegInfo[2]!=null && $RegInfo[2]!=""){
				//maintenance key needed
				if(isValidMaintenanceKey($ClientInfo->ppid,$ClientInfo->central,$RegInfo[2])){
					if(!updatePPIDActivation($ClientInfo->ppid,$ClientInfo->regkey)){
						$iErrCode=-8;
					}
				}else{
					$iErrCode=-7;
				}
			  }else if(!updatePPIDActivation($ClientInfo->ppid,$ClientInfo->regkey)){
					$iErrCode=-8;
			  
			  }
			
		  }else{
				$useActivation=0;
		  }
					  
	}else{
		$iErrCode=-4;
	}
}
else
{
      $iErrCode=-3;
}

$aResponse['success'] = ($iErrCode == 0);
$aResponse['errcode'] = $iErrCode;
$aResponse['useactivation']=$useActivation;
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
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response [$sResponse] Final Response [$sFinalResponse]\n", 3, LOG_FILENAME);
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, LOG_FILENAME);
}

ob_end_flush();
?>

