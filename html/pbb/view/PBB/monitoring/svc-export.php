<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
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

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml = @isset($_REQUEST['jml-bars']) ? $_REQUEST['jml-bars'] : 1;
$thn = @isset($_REQUEST['tahun-pajak']) ? $_REQUEST['tahun-pajak'] : 1;
$nop = @isset($_REQUEST['nop]']) ? $_REQUEST['nop]'] : "";
$na = @isset($_REQUEST['wp-name']) ? str_replace("%20", " ", $_REQUEST['wp-name']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$tempo1 = @isset($_REQUEST['jatuh-tempo']) ? $_REQUEST['jatuh-tempo'] : "";
$tempo2 = @isset($_REQUEST['jatuh-tempo2']) ? $_REQUEST['jatuh-tempo2'] : "";

if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$host = "10.24.110.3";
$port = "5432";
$user = "payment_pbb";
$pass = "SS26P@ssw0rd";
$dbname = "db-pajak-palembangkota";

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"wp_nama\", \"length\" : \"180px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"220px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_handphone\", \"length\" : \"120px\", \"title\" : \"Nomor HP\", \"align\":\"center\"},
{\"field\":\"op_alamat\", \"length\" : \"260px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Tagihan\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\", 
\"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
]}";

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "sppt_tanggal_jatuh_tempo='{$tempo1}'");
if ($tempo2 != "") array_push($arrTempo, "sppt_tanggal_jatuh_tempo='{$tempo2}'");
$tempo = implode(" OR ", $arrTempo);

$arrWhere = array();
if ($nop != "") array_push($arrWhere, "nop='{$nop}'");
if ($thn != "") array_push($arrWhere, "sppt_tahun_pajak='{$thn}'");
if ($na != "") array_push($arrWhere, "wp_nama like '%{$na}%'");
if ($status != "") array_push($arrWhere, "payment_flag = '{$status}'");
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");
$where = implode(" AND ", $arrWhere);

$monPBB = new dbMonitoring($host, $port, $user, $pass, $dbname);
$monPBB->setConnectToPostgres();
$monPBB->setTable("PBB.PBB_SPPT");
$monPBB->setWhere($where);
$monPBB->query("select nop, wp_nama, wp_alamat, wp_handphone , op_alamat, op_kecamatan, 
				sppt_tahun_pajak, sppt_tanggal_jatuh_tempo , sppt_pbb_harus_dibayar, payment_flag	 
				");
$monPBB->setTitleHeader($jsonTitle);
$monPBB->exportToXls();
