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
require_once 'penguranganDendaClass.php';

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

$core = new penguranganDenda($appConfig);

$term = isset($_POST['term']) ? $_POST['term'] : '';
$deleted = isset($_POST['deleted']) ? $_POST['deleted'] : 0;

/*
 * DataTables example server-side processing script.
 * https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/server_processing.php
 */
$table = $core->get('table');
$tableNOP = $core->get('tableNOP');

$join = "LEFT JOIN {$tableNOP} ON {$table}.NOP = {$tableNOP}.NOP AND {$table}.TAHUN = {$tableNOP}.SPPT_TAHUN_PAJAK";
$whereAll = $deleted ? "{$table}.DELETED_AT IS NOT NULL" : "{$table}.DELETED_AT IS NULL";
$where = '';
if ($term) {
    $_term = "'%". $core->dbEscape($term) ."%'";
    $where = "{$table}.NOP LIKE {$_term}";
    $where .= " OR {$table}.TAHUN LIKE {$_term}";
    $where .= " OR {$table}.CREATED_AT LIKE {$_term}";
    $where .= " OR {$table}.CREATED_BY LIKE {$_term}";
    $where .= " OR {$table}.DELETED_AT LIKE {$_term}";
    $where .= " OR {$table}.DELETED_BY LIKE {$_term}";
    $where .= " OR {$table}.DESKRIPSI LIKE {$_term}";
    $where .= " OR {$tableNOP}.WP_NAMA LIKE {$_term}";
    $where .= " OR {$tableNOP}.WP_ALAMAT LIKE {$_term}";
    $where .= " OR {$tableNOP}.WP_KELURAHAN LIKE {$_term}";
    $where .= " OR {$tableNOP}.WP_KECAMATAN LIKE {$_term}";
    $where .= " OR {$tableNOP}.OP_ALAMAT LIKE {$_term}";
    $where .= " OR {$tableNOP}.OP_KELURAHAN LIKE {$_term}";
    $where .= " OR {$tableNOP}.OP_KECAMATAN LIKE {$_term}";
    $where .= " OR {$tableNOP}.SPPT_TANGGAL_JATUH_TEMPO LIKE {$_term}";
}
$columnDate = $deleted ? "DELETED_AT" : "CREATED_AT";
$columnUser = $deleted ? "DELETED_BY" : "CREATED_BY";

$primaryKey = "{$table}.NOP";
$columns = array(
    array(
        'db' => "{$table}.NOP",
        'name' => 'NOP', 
        'dt' => 0
    ),
    array(
        'db' => "{$tableNOP}.WP_NAMA",
        'name' => 'WP_NAMA', 
        'dt' => 1
    ),
    array(
        'db' => "{$tableNOP}.WP_ALAMAT",
        'name' => 'WP_ALAMAT', 
        'dt' => 2
    ),
    array(
        'db' => "{$tableNOP}.OP_ALAMAT",
        'name' => 'OP_ALAMAT', 
        'dt' => 3
    ),
    array(
        'db' => "{$tableNOP}.SPPT_TAHUN_PAJAK",
        'name' => 'SPPT_TAHUN_PAJAK', 
        'dt' => 4
    ),
    array(
        'db' => "{$table}.NILAI",
        'name' => 'NILAI', 
        'dt' => 5
    ),
    array(
        'db' => "{$table}.PERSENTASE",
        'name' => 'PERSENTASE', 
        'dt' => 6
    ),
    array(
        'db' => "{$table}.{$columnDate}",
        'name' => $columnDate, 
        'dt' => 7
    ),
    array(
        'db' => "{$table}.{$columnUser}",
        'name' => $columnUser, 
        'dt' => 8
    ),
    array(
        'db' => "{$table}.DESKRIPSI",
        'name' => 'DESKRIPSI', 
        'dt' => 9
    ),
    array(
        'db' => 'ID', 
        'dt' => 10,
        'name' => 'ID',
        'formatter' => function($d, $row) use ($deleted) {
            return !$deleted ? '<form action="" method="post">
                        <input type="hidden" name="delete" value="'. $d .'">
                        <button type="submit" class="btn btn-danger btn-sm btn-flat" onclick="return confirm(\'Apakah anda yakin ?\')">Hapus</a>
                    </form>' : '';
        }
    ),
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
