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
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";
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

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_kelurahan\", \"length\" : \"180px\", \"title\" : \"" . $_REQUEST['LBL_KEL'] . " WP\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\"},
{\"field\":\"op_kelurahan\", \"length\" : \"180px\", \"title\" : \"" . $_REQUEST['LBL_KEL'] . " OP\"},
{\"field\":\"op_rt\", \"length\" : \"160px\", \"title\" : \"RT OP\", \"align\":\"center\"},
{\"field\":\"op_rw\", \"length\" : \"160px\", \"title\" : \"RW OP\", \"align\":\"center\"},
{\"field\":\"op_luas_bumi\", \"length\" : \"140px\", \"title\" : \"Luas Bumi\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"op_luas_bangunan\", \"length\" : \"140px\", \"title\" : \"Luas Bangunan\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"op_njop_bumi\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bumi\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"op_njop_bangunan\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bangunan\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Tgl Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Pokok\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_denda\", \"length\" : \"80px\", \"title\" : \"Denda\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_total_bayar\", \"length\" : \"80px\", \"title\" : \"Total\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\", \"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
{\"field\":\"payment_paid\", \"length\" : \"180px\", \"title\" : \"Tgl Bayar\", \"align\":\"center\",\"format\":\"date\"},
{\"field\":\"cdc_b_name\", \"length\" : \"200px\", \"title\" : \"Bank/User Trx\"},
{\"field\":\"njop_permeter_bumi\", \"length\" : \"200px\", \"title\" : \"NJOP Bumi Permeter\", \"align\":\"right\"},
{\"field\":\"njop_permeter_bangunan\", \"length\" : \"200px\", \"title\" : \"NJOP Bangunan Permeter\", \"align\":\"right\"}
]}";

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "A.PAYMENT_PAID>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "A.PAYMENT_PAID<='{$tempo2} 23:59:59'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "A.NOP like '{$kelurahan}%'");
    else array_push($arrWhere, "A.NOP like '{$kecamatan}%'");
}

//if ($nop != "") array_push($arrWhere, "A.NOP LIKE '{$nop}%'");
if ($nop1 != "") array_push($arrWhere, "SUBSTR(A.NOP, 1, 2) = '{$nop1}'");
if ($nop2 != "") array_push($arrWhere, "SUBSTR(A.NOP, 3, 2) = '{$nop2}'");
if ($nop3 != "") array_push($arrWhere, "SUBSTR(A.NOP, 5, 3) = '{$nop3}'");
if ($nop4 != "") array_push($arrWhere, "SUBSTR(A.NOP, 8, 3) = '{$nop4}'");
if ($nop5 != "") array_push($arrWhere, "SUBSTR(A.NOP, 11, 3) = '{$nop5}'");
if ($nop6 != "") array_push($arrWhere, "SUBSTR(A.NOP, 14, 4) = '{$nop6}'");
if ($nop7 != "") array_push($arrWhere, "SUBSTR(A.NOP, 18, 1) = '{$nop7}'");

if ($nTahun == 1){
    array_push($arrWhere, "A.SPPT_TAHUN_PAJAK='{$tahunawal}'");
}else{
    array_push($arrWhere, "A.SPPT_TAHUN_PAJAK>='{$tahunawal}'");
    array_push($arrWhere, "A.SPPT_TAHUN_PAJAK<='{$tahunakhir}'");
}

if ($na != "") array_push($arrWhere, "A.WP_NAMA LIKE '%{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "A.PAYMENT_FLAG = 1");
    } else {
		if(!$isShowAll){
			array_push($arrWhere, "(A.PAYMENT_FLAG <> '1' OR A.PAYMENT_FLAG IS NULL)");
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
	if($bank == 1){  // Bank Lampung
		array_push($arrWhere, "(LEFT(A.PAYMENT_REF_NUMBER,3)='AQC' OR TRIM(A.PAYMENT_OFFLINE_USER_ID)='Bank Lampung')");
    }elseif($bank == 3){ // Bank BJB
		array_push($arrWhere, "A.PAYMENT_OFFLINE_USER_ID LIKE '%BJB%' ");
    }elseif($bank == 2){ // Lainnya
		array_push($arrWhere, "A.PAYMENT_BANK_CODE NOT IN (1,2,3)");
	}else{
		if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");
	}
}

if($status === '2'){
	if ($nj1 !== '' && $nj2 !== '') array_push($arrWhere, "((A.OP_NJOP_BUMI/A.OP_LUAS_BUMI) BETWEEN {$nj1} AND {$nj2} )");
	if ($nj3 !== '' && $nj4 !== '') array_push($arrWhere, "((A.OP_NJOP_BANGUNAN/A.OP_LUAS_BANGUNAN) BETWEEN {$nj3} AND {$nj4} )");
}

if($status === '1'){
	if ($operator != "") array_push($arrWhere, "TRIM(A.PAYMENT_OFFLINE_USER_ID) LIKE '%{$operator}%'");
}

$where = implode(" AND ", $arrWhere);
$where2 = $where . " " . $qBuku;
// $where;

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring(ONPAYS_DBHOST, ONPAYS_DBPORT, ONPAYS_DBUSER, ONPAYS_DBPWD, OTP_DBNAME);
    $monPBB->setConnectToMysql();
    $monPBB->setRowPerpage(30);
    $monPBB->setPage($p);
    $monPBB->setStatus($status);
    if ($status == '1') {
        $monPBB->setTable(" pbb_sppt A LEFT JOIN cdccore_bank B ON A.PAYMENT_OFFLINE_USER_ID=B.CDC_B_ID ");
        $monPBB->setWhere($where2);
        $monPBB->query("SELECT 
            A.NOP, A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.OP_ALAMAT, A.OP_KECAMATAN, A.OP_KELURAHAN, A.OP_RT, A.OP_RW,
            A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN,A.OP_NJOP_BUMI,A.OP_NJOP_BANGUNAN,A.SPPT_TAHUN_PAJAK, 
            A.SPPT_TANGGAL_JATUH_TEMPO , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS SPPT_PBB_HARUS_DIBAYAR, IFNULL(A.PBB_DENDA,0) as PBB_DENDA , IFNULL(A.pbb_total_bayar,0) as pbb_total_bayar, IFNULL(A.PAYMENT_FLAG,0) AS PAYMENT_FLAG, A.PAYMENT_PAID, 
            A.PAYMENT_OFFLINE_USER_ID AS CDC_B_NAME, (A.OP_NJOP_BUMI/A.OP_LUAS_BUMI) AS njop_permeter_bumi, (A.OP_NJOP_BANGUNAN/A.OP_LUAS_BANGUNAN) as njop_permeter_bangunan ");
    } else {
        $monPBB->setTable(" pbb_sppt A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ");
        $monPBB->setWhere($where2);
        $monPBB->query("SELECT A.NOP, A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.OP_ALAMAT, A.OP_KECAMATAN, A.OP_KELURAHAN, A.OP_RT, A.OP_RW,
            A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN,A.OP_NJOP_BUMI,A.OP_NJOP_BANGUNAN,A.SPPT_TAHUN_PAJAK, 
            A.SPPT_TANGGAL_JATUH_TEMPO , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS SPPT_PBB_HARUS_DIBAYAR, IFNULL(B.PBB_DENDA,0) as PBB_DENDA , IFNULL(A.SPPT_PBB_HARUS_DIBAYAR+B.PBB_DENDA,0) as pbb_total_bayar,
			(A.OP_NJOP_BUMI/A.OP_LUAS_BUMI) AS njop_permeter_bumi, (A.OP_NJOP_BANGUNAN/A.OP_LUAS_BANGUNAN) as njop_permeter_bangunan");
    }
	
    $monPBB->setTitleHeader($jsonTitle);
    if ($export == "") echo $monPBB->showHTML();
    else $monPBB->exportToXls();
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
