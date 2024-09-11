<?php
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
require_once("dbMonitoring.php");

date_default_timezone_set("Asia/Jakarta");

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
$jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";

$qBuku = "";
if ($buku != 0) {
    switch ($buku) {
        case 1:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
            break;
        case 12:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 123:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 1234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 12345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 2:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 23:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 2345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 3:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 34:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 4:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 45:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 5:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
    }
}

// print_r($_REQUEST);exit();

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
{\"field\":\"wp_kelurahan\", \"length\" : \"180px\", \"title\" : \"" . $_REQUEST['LBL_KEL'] . " WP\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"op_kelurahan\", \"length\" : \"180px\", \"title\" : \"" . $_REQUEST['LBL_KEL'] . " OP\", \"align\":\"center\"},
{\"field\":\"op_rt\", \"length\" : \"160px\", \"title\" : \"RT OP\", \"align\":\"center\"},
{\"field\":\"op_rw\", \"length\" : \"160px\", \"title\" : \"RW OP\", \"align\":\"center\"},
{\"field\":\"op_luas_bumi\", \"length\" : \"140px\", \"title\" : \"Luas Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_luas_bangunan\", \"length\" : \"140px\", \"title\" : \"Luas Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_njop_bumi\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_njop_bangunan\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Tgl Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Pokok\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_denda\", \"length\" : \"80px\", \"title\" : \"Denda\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_total_bayar\", \"length\" : \"80px\", \"title\" : \"Total\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\", \"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
{\"field\":\"payment_paid\", \"length\" : \"180px\", \"title\" : \"Tgl Bayar\", \"align\":\"right\",\"format\":\"date\"},
{\"field\":\"CDC_B_NAME\", \"length\" : \"200px\", \"title\" : \"Bank\", \"align\":\"center\"}
]}";

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "A.payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "A.nop like '{$kelurahan}%'");
    else array_push($arrWhere, "A.nop like '{$kecamatan}%'");
}

if ($nop != "") array_push($arrWhere, "A.nop LIKE '{$nop}%'");
if ($thn != "") array_push($arrWhere, "A.sppt_tahun_pajak='{$thn}'");
if ($na != "") array_push($arrWhere, "A.wp_nama like '%{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "A.payment_flag = 1");
    } else {
        array_push($arrWhere, "(A.payment_flag != 1 OR A.payment_flag IS NULL)");
    }
}
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");

if ($tagihan != 0) {
    switch ($tagihan) {
        case 1:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
            break;
        case 2:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR > 100000 AND A.SPPT_PBB_HARUS_DIBAYAR <= 200000) ");
            break;
        case 3:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR > 200000 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 4:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR > 500000 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 5:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR > 2000000 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
            //case 6 : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR > 40000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 50000000) "); break;
            //case 7 : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 100000000) "); break;
            //case 8 : array_push($arrWhere," (A.SPPT_PBB_HARUS_DIBAYAR >= 100000000) "); break;
        case 6:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR > 5000000) ");
            break;
    }
}

if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");

$where = implode(" AND ", $arrWhere);
$where2 = $where . " " . $qBuku;
// $where;

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToMysql();
    $monPBB->setRowPerpage(30);
    $monPBB->setPage($p);
    $monPBB->setStatus($status);
    if ($status == '1') {
        $monPBB->setTable(" pbb_sppt A LEFT JOIN cdccore_bank B ON A.PAYMENT_BANK_CODE=B.CDC_B_ID ");
        $monPBB->setWhere($where2);
        $monPBB->query("SELECT A.nop, A.wp_nama, A.wp_alamat, A.wp_kelurahan, A.op_alamat, A.op_kecamatan, A.op_kelurahan, A.op_rt, A.op_rw,
            A.op_luas_bumi, A.op_luas_bangunan,A.op_njop_bumi,A.op_njop_bangunan,A.sppt_tahun_pajak, 
            A.sppt_tanggal_jatuh_tempo , IFNULL(A.sppt_pbb_harus_dibayar,0) AS sppt_pbb_harus_dibayar, IFNULL(A.pbb_denda,0) as pbb_denda , IFNULL(A.pbb_total_bayar,0) as pbb_total_bayar, IFNULL(A.payment_flag,0) AS payment_flag, A.payment_paid, B.CDC_B_NAME ");
    } else {
        $monPBB->setTable(" pbb_sppt A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ");
        $monPBB->setWhere($where2);
        $monPBB->query("SELECT A.nop, A.wp_nama, A.wp_alamat, A.wp_kelurahan, A.op_alamat, A.op_kecamatan, A.op_kelurahan, A.op_rt, A.op_rw,
            A.op_luas_bumi, A.op_luas_bangunan,A.op_njop_bumi,A.op_njop_bangunan,A.sppt_tahun_pajak, 
            A.sppt_tanggal_jatuh_tempo , IFNULL(A.sppt_pbb_harus_dibayar,0) AS sppt_pbb_harus_dibayar, IFNULL(B.pbb_denda,0) as pbb_denda , IFNULL(A.sppt_pbb_harus_dibayar+B.pbb_denda,0) as pbb_total_bayar ");
    }
    $monPBB->setTitleHeader($jsonTitle);
    if ($export == "") echo $monPBB->showHTML();
    else $monPBB->exportToXls();
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
