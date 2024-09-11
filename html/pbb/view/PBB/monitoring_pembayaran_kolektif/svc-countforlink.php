<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_pembayaran_kolektif', '', dirname(__FILE__))) . '/';
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
require_once("dbMonitoring.php");

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
// print_r($_REQUEST);exit;

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
//$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$nop1 = @isset($_REQUEST['n1']) ? $_REQUEST['n1'] : "";
$nop2 = @isset($_REQUEST['n2']) ? $_REQUEST['n2'] : "";
$nop3 = @isset($_REQUEST['n3']) ? $_REQUEST['n3'] : "";
$nop4 = @isset($_REQUEST['n4']) ? $_REQUEST['n4'] : "";
$nop5 = @isset($_REQUEST['n5']) ? $_REQUEST['n5'] : "";
$nop6 = @isset($_REQUEST['n6']) ? $_REQUEST['n6'] : "";
$nop7 = @isset($_REQUEST['n7']) ? $_REQUEST['n7'] : "";
$na = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$kelurahan = "";
$kode = @isset($_REQUEST['kd_kolektif']) ? $_REQUEST['kd_kolektif'] : "";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";


// print_r($_REQUEST);exit;
if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$host = $_REQUEST['GW_DBHOST'];
$port = $_REQUEST['GW_DBPORT'];
$user = $_REQUEST['GW_DBUSER'];
$pass = $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME'];

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_kelurahan\", \"length\" : \"120px\", \"title\" : \"Kelurahan WP\"},
{\"field\":\"wp_kecamatan\", \"length\" : \"120px\", \"title\" : \"Kecamatan WP\"},
{\"field\":\"wp_handphone\", \"length\" : \"120px\", \"title\" : \"Nomor HP\", \"align\":\"center\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"op_kelurahan\", \"length\" : \"160px\", \"title\" : \"Kelurahan OP\", \"align\":\"center\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Tagihan\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\"}, 
{\"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
]}";

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "nop like '{$kelurahan}%'");
    else array_push($arrWhere, "nop like '{$kecamatan}%'");
}

if ($kode != "") array_push($arrWhere, "CPM_CG_PAYMENT_CODE =" . $kode);

//if ($nop!="") array_push($arrWhere,"nop='{$nop}'");
if ($nop1 != "") array_push($arrWhere, "SUBSTR(nop, 1, 2) = '{$nop1}'");
if ($nop2 != "") array_push($arrWhere, "SUBSTR(nop, 3, 2) = '{$nop2}'");
if ($nop3 != "") array_push($arrWhere, "SUBSTR(nop, 5, 3) = '{$nop3}'");
if ($nop4 != "") array_push($arrWhere, "SUBSTR(nop, 8, 3) = '{$nop4}'");
if ($nop5 != "") array_push($arrWhere, "SUBSTR(nop, 11, 3) = '{$nop5}'");
if ($nop6 != "") array_push($arrWhere, "SUBSTR(nop, 14, 4) = '{$nop6}'");
if ($nop7 != "") array_push($arrWhere, "SUBSTR(nop, 18, 1) = '{$nop7}'");
if ($thn != "") array_push($arrWhere, "sppt_tahun_pajak='{$thn}'");
if ($na != "") array_push($arrWhere, "wp_nama like '{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "payment_flag = 1");
    } else {
        array_push($arrWhere, "(payment_flag != 1 OR payment_flag IS NULL)");
    }
}
if ($kolektif != "") array_push($arrWhere, "A.COLL_PAYMENT_CODE = '{$kolektif}'");

if ($tempo1 != "") array_push($arrWhere, "({$tempo})");
if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");

if ($tagihan != 0) {
    switch ($tagihan) {
        case 1:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR < 5000000) ");
            break;
        case 2:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND SPPT_PBB_HARUS_DIBAYAR < 10000000) ");
            break;
        case 3:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND SPPT_PBB_HARUS_DIBAYAR < 20000000) ");
            break;
        case 4:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND SPPT_PBB_HARUS_DIBAYAR < 30000000) ");
            break;
        case 5:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND SPPT_PBB_HARUS_DIBAYAR < 40000000) ");
            break;
        case 6:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND SPPT_PBB_HARUS_DIBAYAR < 50000000) ");
            break;
        case 7:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND SPPT_PBB_HARUS_DIBAYAR < 100000000) ");
            break;
        case 8:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100000000) ");
            break;
        case 9:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR > 100000000) ");
            break;
    }
}

if ($buku != 0) {
    switch ($buku) {
        case 1:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
            break;
        case 12:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 123:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 1234:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 12345:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 2:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 23:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 234:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 2345:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 3:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 34:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 345:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 4:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 45:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 5:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
    }
}

$where = implode(" AND ", $arrWhere);

// echo $where;exit;
if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToMysql();
    $monPBB->setTable("PBB_SPPT A LEFT JOIN CDCCORE_BANK B ON A.PAYMENT_BANK_CODE=B.CDC_B_ID LEFT JOIN CPPMOD_COLLECTIVE_GROUP G ON A.COLL_PAYMENT_CODE = G.CPM_CG_PAYMENT_CODE");
    $monPBB->setWhere("A.COLL_PAYMENT_CODE IS NOT NULL AND " . $where);
    echo $monPBB->getCountData();
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
