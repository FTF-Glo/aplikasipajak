<?php 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
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
require_once($sRootPath . "inc/PBB/dbMonitoring.php");
require_once("config-monitoring.php");

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

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$nopp = @isset($_REQUEST['nopp']) ? $_REQUEST['nopp'] : "";
$tahun = @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$sts = @isset($_REQUEST['sts']) ? $_REQUEST['sts'] : "";

/* $nopp = $_GET['nopp'];
$tahun = $_POST['tahun']; */
//$nop = '167108100201301180';
//$tahun = '2001';

$host = DBHOST;
$port = DBPORT;
$user = DBUSER;
$pass = DBPWD;
$dbname = DBNAME; 

$arrWhere = array();
if ($nopp!="") array_push($arrWhere, "nop = '{$nopp}'");
if ($tahun!="") array_push($arrWhere, "sppt_tahun_pajak = '{$tahun}'");
$where = implode (" AND ",$arrWhere);

if(stillInSession($DBLink,$json,$sdata)){			
	$monPBB = new dbMonitoring ($host,$port,$user,$pass,$dbname);
	$monPBB->setConnectToPostgres();
	$monPBB->setRowPerpage(30);
	$monPBB->setPage($p);
	$monPBB->setTableUpdate("PBB.PBB_SPPT");
	$monPBB->setWhere($where);
	echo $monPBB->pembayaran($sts);
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

?>