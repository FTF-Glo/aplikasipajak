<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembayaran_va', '', dirname(__FILE__))) . '/';

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
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/FlaggingKolektif.php");
require_once("classCollective.php");
$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec  = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json    = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$dbUtils = new DbUtils($dbSpec);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User      = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig("aPBB");
$tahun     = $appConfig['tahun_tagihan'];
$host      = $appConfig['GW_DBHOST'];
$port      = $appConfig['GW_DBPORT'];
$user      = $appConfig['GW_DBUSER'];
$pass      = $appConfig['GW_DBPWD'];
$dbname    = $appConfig['GW_DBNAME'];

$svcCollective = new classCollective($dbSpec, $dbUtils);

$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER = $user;
$svcCollective->C_PWD = $pass;
$svcCollective->C_DB = $dbname;
$svcCollective->C_PORT = $port;

// $data = $svcCollective->checkGroupMemberPaymentStatus();
$data = (new FlaggingKolektif())->execute();

header("Content-Type: application/json");
echo json_encode($data);