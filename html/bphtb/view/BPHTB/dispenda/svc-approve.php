<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dispenda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/payment/uuid.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$aResponse = array();
$aResponse['success'] = true;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

function getConfigValue ($key) {
	global $DBLink;	
	$a = @isset($_REQUEST['a']) ? base64_decode($_REQUEST['a']):"";
	$qry = "select * from central_app_config where CTR_AC_AID = '".$a."' and CTR_AC_KEY = '$key'";

	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$DbName = getConfigValue('BPHTBDBNAME');
$DbHost = getConfigValue('BPHTBHOSTPORT');
$DbPwd = getConfigValue('BPHTBPASSWORD');
$DbTable = getConfigValue('BPHTBTABLE');
$DbUser = getConfigValue('BPHTBUSERNAME');
//echo $DbName;

SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
$ids = @isset($_REQUEST['d']) ? base64_decode($_REQUEST['d']):"";	

$idtran = c_uuid();
$refnum = c_uuid();
$trdate = date("Y-m-d H:i:s");

$query = "SELECT * FROM cppmod_ssb_tranmain WHERE CPM_TRAN_SSB_ID = '{$ids}' ORDER BY CPM_TRAN_DATE DESC LIMIT 1";
$resFind = mysqli_query($DBLink, $query);

while ($rowFind = mysqli_fetch_assoc($resFind)) {
	$version = $rowFind['CPM_TRAN_SSB_VERSION'];
	$opr = $rowFind['CPM_TRAN_OPR_NOTARIS'];
	$dispenda1 = $rowFind['CPM_TRAN_OPR_DISPENDA_1'];
	$dispenda2 = $rowFind['CPM_TRAN_OPR_DISPENDA_2'];
	$status = $rowFind['CPM_TRAN_STATUS'];
}

if ($status != "6") {
	$query = sprintf("INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,
			CPM_TRAN_DATE,CPM_TRAN_CLAIM,CPM_TRAN_OPR_NOTARIS,CPM_TRAN_OPR_DISPENDA_1,CPM_TRAN_OPR_DISPENDA_2) 
			VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",$idtran,$refnum,$ids,$version,'6','0',mysqli_real_escape_string($DBLink, $trdate),
			'',$opr,$dispenda1,$dispenda2);	

	$result = mysqli_query($DBLink, $query);
	if ( $result === false ){
		$aResponse['success'] = false;
	}		
}

$sResponse = $json->encode($aResponse);
echo $sResponse;
?>
