<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'loket', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");


error_reporting(E_ALL);
ini_set('display_errors', 1);

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$par = @isset($_REQUEST['param'])? $_REQUEST['param'] : "";
$idkc = @isset($_REQUEST['idkc'])? $_REQUEST['idkc'] : "";
$param = $json->decode(base64_decode($par));

$id  = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$area =  $param->a;
$uid = $param->u;
$module = $param->m;
$function =  $param->f;
$city =  @isset($_REQUEST['c'])? $_REQUEST['c'] : "";

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if ($bOK) {
	$return = array();
	$r = array();
 	if ($city==1) {
		$query= "SELECT * FROM cppmod_tax_kabkota WHERE CPC_TK_PID = '{$idkc}'";
		$res = mysqli_query($DBLink, $query);
		$i=0;
		
		while ($row = mysqli_fetch_assoc($res)){
			$r[$i]["value"] = $row['CPC_TK_ID'];
			$r[$i]["label"] = $row['CPC_TK_KABKOTA'];
			$i++;
		}
		$return['msg'] = $r;
		echo $json->encode($return);
	}	
	if ($city==2) {
		$query= "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID = '{$idkc}'";
		$res = mysqli_query($DBLink, $query);
		$i=0;
		
		while ($row = mysqli_fetch_assoc($res)){
			$r[$i]["value"] = $row['CPC_TKC_ID'];
			$r[$i]["label"] = $row['CPC_TKC_KECAMATAN'];
			$i++;
		}
		$return['msg'] = $r;
		echo $json->encode($return);
	}	
	if ($city==3) {
		$query= "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID = '{$idkc}'";
		$res = mysqli_query($DBLink, $query);
		$i=0;
		
		while ($row = mysqli_fetch_assoc($res)){
			$r[$i]["value"] = $row['CPC_TKL_ID'];
			$r[$i]["label"] = $row['CPC_TKL_KELURAHAN'];
			$i++;
		}
		$return['msg'] = $r;
		echo $json->encode($return);
	}	
}

?>
