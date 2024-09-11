<?php  

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembatalan-sppt', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once("classPembatalan.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec 		= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcPembatalan	= new SvcPembatalanSPPT($dbSpec);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$nop 		= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$tahun 		= @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$uid		= @isset($_REQUEST['uid']) ? $_REQUEST['uid'] : "";
$a			= @isset($_REQUEST['a']) ? $_REQUEST['a'] : "";
$m			= @isset($_REQUEST['m']) ? $_REQUEST['m'] : "";

$userLogin	= new SCANCentralUser (0,LOG_FILENAME,$DBLink);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

// print_r($appConfig); exit;

$host 	= $appConfig['GW_DBHOST'];
$port 	= $appConfig['GW_DBPORT'];
$user 	= $appConfig['GW_DBUSER'];
$pass 	= $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME']; 

$uname	= $userLogin->GetUserName($uid);

$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_PORT = $port;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;

$bOK = false;

$bOK = $svcPembatalan->copySPPTCurrentToPembatalanPerKel($nop,$tahun);
if(!$bOK){
	$bOK = $svcPembatalan->replaceSPPTCurrentToPembatalanPerKel($nop,$tahun);
}
if($bOK){
	//DELETE dari SW.cppmod_pbb_sppt_current (kalau data di tabel CURRENT nya ada/SPPT tahun berjalan)
	$bOK = $svcPembatalan->deleteSPPTCurrentPerKel($nop,$tahun);
}
if($bOK){
	//Update tahun Penetapan menjadi 0 di tabel cppmod_pbb_sppt_final
	$svcPembatalan->updateTahunPenetapan($nop,$tahun);
}
if($bOK){
	//Copy data dari PBB_SPPT ke PBB_SPPT_DIBATALKAN
	$bOK = $svcPembatalan->copyToPembatalanPerKel($nop,$tahun);
}
if($bOK){ 
	//Delete data yang sudah di copy ke PBB_SPPT_DIBATALKAN dari PBB_SPPT 
	$bOK = $svcPembatalan->delGateWayPBBSPPTPerKel($nop,$tahun);
}
if($bOK){
	//INSERT proses pembatalan ke cppmod_pbb_log_pembatalan
	$svcPembatalan->addToLog($uname,$nop,$tahun);
}
if(!$bOK){
    $respon['respon'] = false;
	$respon['message'] = mysqli_error($DBLink);
}else{
	$respon['respon'] = true;
	$respon['message'] = "sukses: ".$nop;
}
echo json_encode($respon);exit;
?>
