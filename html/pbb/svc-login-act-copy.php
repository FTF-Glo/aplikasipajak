<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");
ob_start();
error_reporting(E_ALL);
// includes
$sRootPath = str_replace('\\', '/', str_replace('', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/uuid.php");
require_once($sRootPath."inc/payment/session-payment-c.php");
require_once($sRootPath."inc/payment/user-payment-c-copy.php");

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

$Session = new SCANPaymentPointDBSession(DEBUG, LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
$User = new SCANPaymentPointUser(DEBUG, LOG_FILENAME, $DBLink);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
SCANPayment_Pref_GetAllWithFilter($DBLink, "%PC.%", $aCentralPrefs);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aCentralPrefs [".print_r($aCentralPrefs, true)."]\n", 3, LOG_FILENAME);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, C_MESSAGE_LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);

// ---------------
// LOCAL FUNCTIONS
// ---------------

function isHaveRootAccess($sUID){
	global $DBLink,$DBConn;
	$bOK=false;
	$sQ="SELECT CRUMM.CPC_RM2M_RID FROM CPCCORE_USER_ROLE_USER_MODULE CURUM,CPCCORE_ROLE_USER_MODULE_TO_MODULE CRUMM WHERE CURUM.CPC_URUM_M2MID=CRUMM.CPC_RM2M_ID AND CRUMM.CPC_RM2M_MID='ONPAYS_SYSTEM' AND CPC_RM2M_RID='root' AND CURUM.CPC_URUM_UID='".$sUID."'";
	if ($res = mysqli_query($DBLink, $sQ))
	{
		if(($row=mysqli_fetch_array($res))){
			$bOK=true;
		}
		mysqli_free_result($res);
	}
	return $bOK;
}

function isUseActivation($sPPID,&$Activate,&$regkeyv2){
	global $DBLink,$DBConn;
	$bOK=true;
	$sQ="SELECT CSC_CD_USE_ACTIVATION,CSC_CD_ACTIVATE,CSC_CD_REGKEY FROM csccore_central_downline WHERE CSC_CD_ID='".$sPPID."'";
	if ($res = mysqli_query($DBLink, $sQ))
	{
		if(($row=mysqli_fetch_array($res))){
			$bOK=($row[0]==1);
			$Activate=$row[1];
			$regkeyv2=$row[2];
		}
		mysqli_free_result($res);
	}
	return $bOK;
}

function getConfigSetup($sPPID){
	global $DBLink,$DBConn;
	$config=null;
	$sQ="SELECT CCSV.CPC_CSV_KEY,CCSV.CPC_CSV_VALUE FROM CPCCORE_CONFIG_SETUP_VALUE CCSV INNER JOIN CPCCORE_CONFIG_SETUP_PPID CCSP ON CCSV.CPC_CSV_ID=CCSP.CPC_CSP_ID
WHERE CCSP.CPC_CSP_PPID='".$sPPID."'";
	if ($res = mysqli_query($DBLink, $sQ))
	{
		$config=array();
		while(($row=mysqli_fetch_array($res))){
			$config[]=$row;
		}
		mysqli_free_result($res);
	}
	return $config;
}

function registerMAC($sPPID,$RegKey){
	global $DBLink,$DBConn;
	$bOK=false;
	if(is_array($RegKey)){
		//$n=count($RegKey);
		$n=1;
		for($i=0;$i<$n;$i++){
			if(trim($RegKey[$i])!="" && substr($RegKey[$i],0,17)!="02-00-4C-4F-4F-50"){
				$sQ="INSERT INTO CPCCORE_PAYMENT_POINT_REGKEY(CPC_PPR_ID,CPC_PPR_REGKEY,CPC_PPR_BLOCKED,CPC_PPR_REGISTERED) VALUES('$sPPID','".$RegKey[$i]."',0,NOW())";
				//var_dump($sQ);
				if (mysqli_query($DBLink, $sQ))
				{
					$bOK=true;
				}
			}
		}
	}
	return $bOK;
}

function isEmptyPPIDMAC($sPPID){
		global $DBLink,$DBConn;
		$bOK=false;
		$sQ="SELECT COUNT(*) FROM CPCCORE_PAYMENT_POINT_REGKEY WHERE CPC_PPR_ID='$sPPID' AND CPC_PPR_BLOCKED=0";
		//var_dump($sQ);
		if ($res = mysqli_query($DBLink, $sQ))
		{
			if(($row=mysqli_fetch_array($res))){
				$bOK=(intval($row[0])==0);
			}
			mysqli_free_result($res);
		}
		return $bOK;
}

function isValidMAC($sPPID,$RegKey){
	global $DBLink,$DBConn;
	$bOK=false;
	if(is_array($RegKey)){
		//$strMAC=implode($RegKey,"','");
		//$strMAC="('".$strMAC."')";
		$strMAC="('".$RegKey[0]."')";
		$sQ="SELECT COUNT(*) FROM CPCCORE_PAYMENT_POINT_REGKEY WHERE CPC_PPR_ID='$sPPID' AND CPC_PPR_REGKEY IN $strMAC AND CPC_PPR_BLOCKED=0";
		//var_dump($sQ);
		if ($res = mysqli_query($DBLink, $sQ))
		{
			if(($row=mysqli_fetch_array($res))){
				$bOK=(intval($row[0])>0);
			}
			mysqli_free_result($res);
		}
	}
	return $bOK;
}

function Activate($sPPID){
	global $DBLink,$DBConn;
	$bOK=false;
	$sQ="UPDATE csccore_central_downline SET CSC_CD_ACTIVATE=NOW(),CSC_CD_REGKEY='ONPAYS-V2E-ACTIVATION' WHERE CSC_CD_ID='$sPPID'";
	//var_dump($sQ);
	if (mysqli_query($DBLink, $sQ))
	{
		$bOK=true;
	}
	return $bOK;
}

function isValidRegKey($sUid,$RegKey,$actived,$ppid,&$PPInfo){
	global $DBLink,$DBConn;
	$bOK=false;
	$PP=null;
	$sQ="select CCD.CSC_CD_ID PP,CCD.CSC_CD_NAME PPNAME,CCD.CSC_CD_ADDRESS PPADDRESS,CCD.CSC_CD_AREA PPAREA,CCD.CSC_CD_TERMINAL_TYPE PPMERCHANT,
	CDCD.CSC_DCD_CID PPSENTRAL,CBD.CSC_B_ID PPBANK,CB.CSC_B_REALNAME PPBANKNAME,CCD.CSC_CD_SWCODE PPSWCODE,CPCDD.CSM_CCD_FUND_TYPE PPDEPOSIT,CCD.CSC_CD_ALLOW_PPID_MIGRATION MIGRATE,CCD.CSC_CD_RESENT_CONFIG RESENT
	FROM CPCCORE_PAYMENT_POINT_USER_BLOCK CPPUB INNER JOIN csccore_central_downline CCD ON CPPUB.CPC_PPUB_ID=CCD.CSC_CD_ID 
	INNER JOIN CSCMOD_EL_POST_CENTRAL_DOWN_DTL CPCDD ON CCD.CSC_CD_ID=CPCDD.CSM_CCD_PPID
	INNER JOIN csccore_down_central_downline CDCD ON CCD.CSC_CD_ID=CDCD.CSC_DCD_DID
	INNER JOIN (SELECT DISTINCT CSC_D_ID,CSC_B_ID FROM csccore_bank_downline) CBD ON CDCD.CSC_DCD_CID=CBD.CSC_D_ID
	INNER JOIN csccore_bank CB ON CBD.CSC_B_ID=CB.CSC_B_ID
	WHERE CPPUB.CPC_PPUB_UID='$sUid' LIMIT 1";
	if(isset($_REQUEST['v'])) var_dump($sQ);
	if ($res = mysqli_query($DBLink, $sQ))
	{
		if(($row=mysqli_fetch_assoc($res))){
			$PP=$row;
		}
		mysqli_free_result($res);
	}
	//var_dump($PP,$sQ);

	$activate="";
	$regkeyv2="";
	$differentppid=false;
	if(isset($ppid)){
		if($ppid!=$PP["PP"] && $ppid!='0000000000000000'){
			$differentppid=true;
		}
	}



	if($differentppid && intval($PP["MIGRATE"])==0 || (is_null($PP["PP"])||trim($PP["PP"])=="")){
		$PPInfo=$PP;
		return $bOK;
	}

	if(isUseActivation($PP["PP"],$activate,$regkeyv2)){
		
		if(is_null($activate) || trim($activate)==""){
			//register Mac Address
			$PPInfo=$PP;
			$PPInfo["PPREGISTER"]=1;
			$PPInfo["SETUP"]=getConfigSetup($PP["PP"]);
			$bOK=registerMAC($PP["PP"],$RegKey);
			$bOK=Activate($PP["PP"]);
		}else{
			//Cek Mac Address			
			$PPInfo["PPREGISTER"]=0;
			$PPInfo["PP"]=$PP["PP"];
			if($actived==0 || $differentppid || $PP["RESENT"]==1){
				$PPInfo=$PP;
				$PPInfo["PPREGISTER"]=1;
				$PPInfo["SETUP"]=getConfigSetup($PP["PP"]);				
			}
			
			if(isEmptyPPIDMAC($PP["PP"]) && (is_null($regkeyv2) || trim($regkeyv2)=="") ){
				$bOK=registerMAC($PP["PP"],$RegKey);
			}else
				$bOK=isValidMAC($PP["PP"],$RegKey);
		}
		
	}
	else{
		if(is_null($activate) || trim($activate)==""){
			//full info
			$PPInfo=$PP;
			$PPInfo["PPREGISTER"]=1;
			$PPInfo["SETUP"]=getConfigSetup($PP["PP"]);
			registerMAC($PP["PP"],$RegKey);
			$bOK=Activate($PP->PP);
		}else{
			//Cek Mac Address
			$PPInfo["PPREGISTER"]=0;
			$PPInfo["PP"]=$PP["PP"];
			registerMAC($PP["PP"],$RegKey);
			if($actived==0 || $differentppid || $PP["RESENT"]==1){
				$PPInfo=$PP;
				$PPInfo["PPREGISTER"]=1;
				$PPInfo["SETUP"]=getConfigSetup($PP["PP"]);				
			}
		}
		$bOK=true;
	}

	return $bOK;
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
  //var_dump($ClientInfo);
}

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] LOGIN INFO from Client ".print_r($ClientInfo,TRUE)."  sJSONClientInfo [$sJSONClientInfo] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);

$sUID='';
$PPinfo=null;
if ($User->IsAuthUser($ClientInfo->uname, $ClientInfo->upwd,$sUID))
{
	
	//var_dump($sUID);
// --	if(isValidRegKey($sUID,$ClientInfo->regkey,$ClientInfo->actived,$ClientInfo->ppid,$PPinfo)){
	  //var_dump($PPinfo);
      // set session
	  	
		
// --   	if(!$User->isBlockedUser($PPinfo["PP"],$sUID)){
        		$sSID = $Session->GenerateSession($sUID, $ClientInfo->uname, $_SERVER['REMOTE_ADDR'].'.'.$PPinfo["PP"]);
        		$Session->SaveSessionToDB($sUID, $sSID,$PPinfo["PP"]);
        		$aResponse['session'] = $sSID;
        		$aResponse['uname'] = $ClientInfo->uname;
        		$aResponse['mkey'] = $MaintenanceKey;
			$aResponse['uid'] = $sUID;
			// --	$aResponse['ppinfo'] = $PPinfo; 
			$aResponse['ppinfo'] = array( "PPREGISTER"=>"1", "PPBANKNAME"=>"BCAZ", "PPBANK" => "0022295", "PPSENTRAL" => "0110101", "PP"=>"VSIONPAYSV2ACMAC", "PPNAME"=>"VSI Devel", 
				"PPADDRESS"=>"Jl. Sukasenang 26 Bandung", "PPAREA"=>"Jawa Barat", "PPMERCHANT"=>"6012", "PPDEPOSIT"=>"0", "PPSWCODE"=>"VSI", "SETUP" => array("pan" => "99504", "port" => "12011") );
        		$aPerms = Array();
        		$User->GetModulesPermissions($sUID,$aPerms);
        		$aResponse['modperms'] = $aPerms;
// --      	}else{
// --      		$iErrCode=-4; 
// -- 	}  
// --	}
// --	else
// --	{
/* --		  if(count($ClientInfo->regkey)>0){
			$iErrCode=-6;
		  if(trim($ClientInfo->regkey[0])!="")
				$iErrCode=-5;
		  }else{
			if((is_null($PPinfo["PP"])||trim($PPinfo["PP"])==""))
				$iErrCode=-4;
				// -- $iErrCode=-5;
			else
				$iErrCode=-6;
		  }
	} -- */
 }else{
		$iErrCode=-3;
 }
$aResponse['success'] = ($iErrCode == 0);
$aResponse['errcode'] = $iErrCode;
$sResponse = $json->encode($aResponse);
//$sFinalResponse=$sResponse;
$sFinalResponse = base64_encode($sResponse);
//$sFinalResponse=$sResponse;
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

