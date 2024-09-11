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
$jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn1 = @isset($_REQUEST['th1']) ? $_REQUEST['th1'] : date('Y');
$thn2 = @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : $thn1;
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
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
//$kelurahan = "";
$nj1 = @isset($_REQUEST['nj1']) ? $_REQUEST['nj1'] : "";
$nj2 = @isset($_REQUEST['nj2']) ? $_REQUEST['nj2'] : "";
$nj3 = @isset($_REQUEST['nj3']) ? $_REQUEST['nj3'] : "";
$nj4 = @isset($_REQUEST['nj4']) ? $_REQUEST['nj4'] : "";
$operator = @isset($_REQUEST['operator']) ? $_REQUEST['operator'] : "";
$isShowAll = isset($_REQUEST['showAll']) && $_REQUEST['showAll'] == 'true' ? true : false;

if($thn1=='' || $thn2==''){
    $tahunawal  = 1994;
    $tahunakhir = date('Y');
    $nTahun 	= (1+$tahunakhir)-$tahunawal;
}else{
    $tahunawal  = ($thn2<$thn1) ? $thn2 : $thn1;
    $tahunakhir = ($thn2<$thn1) ? $thn1 : $thn2;
    $nTahun 	= ($tahunawal!==$tahunakhir) ? (1+$tahunakhir)-$tahunawal : 1;
}


if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

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

$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";

$qBuku = "";
if ($buku != 0) {
    switch ($buku) {
        case 1:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
            break;
        case 12:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 123:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 1234:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 12345:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 2:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 23:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 234:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 2345:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 3:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 34:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 345:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 4:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 45:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 5:
            $qBuku = " AND (A.SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
    }
}

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "nop like '{$kelurahan}%'");
    else array_push($arrWhere, "nop like '{$kecamatan}%'");
}

//if ($nop != "") array_push($arrWhere, "nop='{$nop}'");
if ($nop1 != "") array_push($arrWhere, "SUBSTR(nop, 1, 2) = '{$nop1}'");
if ($nop2 != "") array_push($arrWhere, "SUBSTR(nop, 3, 2) = '{$nop2}'");
if ($nop3 != "") array_push($arrWhere, "SUBSTR(nop, 5, 3) = '{$nop3}'");
if ($nop4 != "") array_push($arrWhere, "SUBSTR(nop, 8, 3) = '{$nop4}'");
if ($nop5 != "") array_push($arrWhere, "SUBSTR(nop, 11, 3) = '{$nop5}'");
if ($nop6 != "") array_push($arrWhere, "SUBSTR(nop, 14, 4) = '{$nop6}'");
if ($nop7 != "") array_push($arrWhere, "SUBSTR(nop, 18, 1) = '{$nop7}'");

if ($nTahun == 1){
    array_push($arrWhere, "sppt_tahun_pajak='{$tahunawal}'");
}else{
    array_push($arrWhere, "sppt_tahun_pajak>='{$tahunawal}'");
    array_push($arrWhere, "sppt_tahun_pajak<='{$tahunakhir}'");
}

if ($na != "") array_push($arrWhere, "wp_nama like '%{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "payment_flag = 1");
    } else {
        if(!$isShowAll){
			array_push($arrWhere, "(A.payment_flag != 1 OR A.payment_flag IS NULL)");
		}   
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

if($status === '1'){
	if($bank == 2){
		array_push($arrWhere, "A.PAYMENT_OFFLINE_USER_ID != 'Sistem Bank Lampung' ");
	}else{
		if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");
	}
}

if($status === '2'){
	if ($nj1 !== '' && $nj2 !== '') array_push($arrWhere, "((A.OP_NJOP_BUMI/A.OP_LUAS_BUMI) BETWEEN {$nj1} AND {$nj2} )");
	if ($nj3 !== '' && $nj4 !== '') array_push($arrWhere, "((A.OP_NJOP_BANGUNAN/A.OP_LUAS_BANGUNAN) BETWEEN {$nj3} AND {$nj4} )");
}

if($status === '1'){
	if ($operator != "") array_push($arrWhere, "A.PAYMENT_OFFLINE_USER_ID like '%{$operator}%'");
}

$where = implode(" AND ", $arrWhere);
$where2 = $where . ' ' . $qBuku;

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring(ONPAYS_DBHOST, ONPAYS_DBPORT, ONPAYS_DBUSER, ONPAYS_DBPWD, OTP_DBNAME);
    $monPBB->setConnectToMysql();
    $monPBB->setTable("PBB_SPPT A");
    $monPBB->setWhere($where2);
    echo $monPBB->getCountData();
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
