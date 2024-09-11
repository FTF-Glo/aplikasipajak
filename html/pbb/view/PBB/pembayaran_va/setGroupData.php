<?php
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

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$dbUtils = new DbUtils($dbSpec);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

// var_dump($uid);
// exit;

$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting     = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$userLogin  = new SCANCentralUser(0, LOG_FILENAME, $DBLink);

$arConfig     = $User->GetModuleConfig($moduleIds);
$appConfig     = $User->GetAppConfig("aPBB");
$tahun        = $appConfig['tahun_tagihan'];
// print_r($appConfig);
$host     = $appConfig['GW_DBHOST'];
$port     = $appConfig['GW_DBPORT'];
$user     = $appConfig['GW_DBUSER'];
$pass     = $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];


$svcCollective = new classCollective($dbSpec, $dbUtils);

$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER = $user;
$svcCollective->C_PWD = $pass;
$svcCollective->C_DB = $dbname;
$svcCollective->C_PORT = $port;
$message = "";
$param = array();
$param[userID] = $_REQUEST['userID'];
$param[CPM_CG_ID] = $_REQUEST['data-edit-group-id'];
$param[CPM_CG_NAME] = $_REQUEST['data-nama'];
$param[CPM_CG_DESC] =  $_REQUEST['data-keterangan'];
$param[CPM_CG_COLLECTOR] = $_REQUEST['data-nama-kolektor'];
$param[CPM_CG_HP_COLLECTOR] = $_REQUEST['data-no-kolektor'];
$param[CPM_CG_AREA_CODE] = $_REQUEST['data-kelurahan-group'];
// var_dump($_REQUEST['data-edit-group-id']);

if ($_REQUEST['data-edit-group-id'] == "") {
    $data = $svcCollective->saveGroup($param);
    if ($data) {
        $array = array();
        $array['success'] = true;
    } else {
        $array['success'] = false;
        $array['message'] = $data;
    }
} else {
    $data = $svcCollective->updateGroup($param);
    if ($data) {
        $array = array();
        $array['success'] = true;
    } else {
        $array['success'] = false;
        $array['message'] = $data;
    }
}
echo json_encode($array);
