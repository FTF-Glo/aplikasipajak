<?php
session_start();
$actualLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan_denda', '', dirname(__FILE__))) . '/';

require_once $sRootPath . "inc/payment/ctools.php";
require_once $sRootPath . "inc/payment/constant.php";
require_once $sRootPath . "inc/payment/comm-central.php";
require_once $sRootPath . "inc/payment/inc-payment-c.php";
require_once $sRootPath . "inc/payment/inc-payment-db-c.php";
require_once $sRootPath . "inc/payment/prefs-payment.php";
require_once $sRootPath . "inc/payment/db-payment.php";
require_once $sRootPath . "inc/check-session.php";
require_once $sRootPath . "inc/payment/json.php";
require_once $sRootPath . "inc/payment/sayit.php";
require_once $sRootPath . "inc/central/setting-central.php";
require_once $sRootPath . "inc/central/user-central.php";
require_once $sRootPath . "inc/central/dbspec-central.php";
require_once $sRootPath . "inc/PBB/dbUtils.php";
require_once $sRootPath . "inc/datatables/ssp.class.php";
require_once($sRootPath . "inc/PBB/dbServices.php");

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
$dbServices = new DbServices($dbSpec);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User      = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting   = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);
$q         = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';

$q         = base64_decode($q);
$j         = $json->decode($q);
$uid       = isset($j->uid) ? $j->uid : '';
$area      = isset($j->a) ? $j->a : '';
$moduleIds = isset($j->m) ? $j->m : '';

$arConfig  = $User->GetModuleConfig($moduleIds);
$appConfig = $User->GetAppConfig("aPBB");
$tahun     = $appConfig['tahun_tagihan'];
$host      = $appConfig['GW_DBHOST'];
$user      = $appConfig['GW_DBUSER'];
$pass      = $appConfig['GW_DBPWD'];
$dbname    = $appConfig['GW_DBNAME'];
$port      = $appConfig['GW_DBPORT'];

$table = 'sw_pbb.cppmod_pbb_services';
$tablekec = 'sw_pbb.cppmod_tax_kecamatan';
$tablekel = 'sw_pbb.cppmod_tax_kelurahan';

$join = "LEFT JOIN {$tablekec} ON {$table}.CPM_OP_KECAMATAN={$tablekec}.CPC_TKC_ID ";
$join .= "LEFT JOIN {$tablekel} ON {$table}.CPM_OP_KELURAHAN={$tablekel}.CPC_TKL_ID ";
$whereAll = "{$table}.CPM_STATUS='1' AND {$table}.CPM_TYPE='12' ";
$where = '';

$primaryKey = "{$table}.CPM_ID";
$columns = array(
    array(
        'db' => "{$table}.CPM_ID",
        'name' => 'CPM_ID', 
        'dt' => 0,
        'formatter' => function($d, $row) use ($deleted) {
            return '<a href="javascript:;" onclick="caridaritable(\''. $d .'\',\''. $row['CPM_OP_NUMBER'] .'\',\''. $row['CPM_SPPT_YEAR'] .'\')"><b>'. $d .'</b></a>';
        }
    ),
    array(
        'db' => "{$tablekec}.CPC_TKC_KECAMATAN",
        'name' => 'CPC_TKC_KECAMATAN', 
        'dt' => 1
    ),
    array(
        'db' => "{$tablekel}.CPC_TKL_KELURAHAN",
        'name' => 'CPC_TKL_KELURAHAN', 
        'dt' => 2
    ),
    array(
        'db' => "{$table}.CPM_OP_NUMBER",
        'name' => 'CPM_OP_NUMBER', 
        'dt' => 3,
        'formatter' => function($d, $row) use ($deleted) {
            return '<a href="javascript:;" onclick="caridaritable(\''. $row['CPM_ID'] .'\',\''. $d .'\',\''. $row['CPM_SPPT_YEAR'] .'\')"><b>'. $d .'</b></a>';
        }
    ),
    array(
        'db' => "{$table}.CPM_SPPT_YEAR",
        'name' => 'CPM_SPPT_YEAR', 
        'dt' => 4
    ),
    array(
        'db' => "{$table}.CPM_WP_NAME",
        'name' => 'CPM_WP_NAME', 
        'dt' => 5
    ),
    array(
        'db' => "{$table}.CPM_REPRESENTATIVE",
        'name' => 'CPM_REPRESENTATIVE', 
        'dt' => 6
    ),
    array(
        'db' => "{$table}.CPM_DATE_RECEIVE",
        'name' => 'CPM_DATE_RECEIVE', 
        'dt' => 7
    )
);

// SQL server connection information
$sql_details = array(
    'host' => $host,
    'user' => $user,
    'pass' => $pass,
    'db'   => $dbname,
    'port' => $port
);

echo json_encode(
    SSP::complex($_POST, $sql_details, $table, $primaryKey, $columns, $where, $whereAll, null, $join)
);
