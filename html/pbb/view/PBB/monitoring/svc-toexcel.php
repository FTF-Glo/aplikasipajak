<?php
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

/** PHPExcel */
require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
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
$total = @isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;


$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
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
if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

// aldes
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($area);

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "A.payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "A.nop like '{$kelurahan}%'");
    else array_push($arrWhere, "A.nop like '{$kecamatan}%'");
}

//if ($nop != "") array_push($arrWhere, "A.nop='{$nop}'");
if ($nop1 != "") array_push($arrWhere, "SUBSTR(A.nop, 1, 2) = '{$nop1}'");
if ($nop2 != "") array_push($arrWhere, "SUBSTR(A.nop, 3, 2) = '{$nop2}'");
if ($nop3 != "") array_push($arrWhere, "SUBSTR(A.nop, 5, 3) = '{$nop3}'");
if ($nop4 != "") array_push($arrWhere, "SUBSTR(A.nop, 8, 3) = '{$nop4}'");
if ($nop5 != "") array_push($arrWhere, "SUBSTR(A.nop, 11, 3) = '{$nop5}'");
if ($nop6 != "") array_push($arrWhere, "SUBSTR(A.nop, 14, 4) = '{$nop6}'");
if ($nop7 != "") array_push($arrWhere, "SUBSTR(A.nop, 18, 1) = '{$nop7}'");

if ($nTahun == 1){
    array_push($arrWhere, "A.sppt_tahun_pajak='{$tahunawal}'");
}else{
    array_push($arrWhere, "A.sppt_tahun_pajak>='{$tahunawal}'");
    array_push($arrWhere, "A.sppt_tahun_pajak<='{$tahunakhir}'");
}

if ($na != "") array_push($arrWhere, "A.wp_nama like '%{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "A.payment_flag = 1");
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
	if ($operator != "") array_push($arrWhere, "A.PAYMENT_OFFLINE_USER_ID like '%{$operator}%'");
}

$where = implode(" AND ", $arrWhere);
$where2 = $where . " " . $qBuku;

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring(ONPAYS_DBHOST, ONPAYS_DBPORT, ONPAYS_DBUSER, ONPAYS_DBPWD, OTP_DBNAME);
    $monPBB->setConnectToMysql();
    if ($p == 'all') {
        $monPBB->setRowPerpage($total);
        $monPBB->setPage(1);
    } else {
        $monPBB->setRowPerpage(10000);
        $monPBB->setPage($p);
    }
    //$monPBB->setTable("PBB_SPPT");
    $monPBB->setWhere($where2);
    $monPBB->setStatus($status);
    if ($status == '1') {
        $query = "SELECT A.nop, A.wp_nama, A.wp_alamat, A.wp_kelurahan, A.op_alamat, A.op_kecamatan, A.op_kelurahan, A.op_rt, A.op_rw,
                A.op_luas_bumi, A.op_luas_bangunan,A.op_njop_bumi,A.op_njop_bangunan,A.sppt_tahun_pajak, 
                A.sppt_tanggal_jatuh_tempo , IFNULL(A.sppt_pbb_harus_dibayar,0) AS sppt_pbb_harus_dibayar, IFNULL(A.pbb_denda,0) as pbb_denda , IFNULL(A.pbb_total_bayar,0) as pbb_total_bayar, IFNULL(A.payment_flag,0) AS payment_flag, A.payment_paid, 
                A.PAYMENT_OFFLINE_USER_ID AS CDC_B_NAME 
                FROM PBB_SPPT A LEFT JOIN CDCCORE_BANK B ON A.PAYMENT_BANK_CODE=B.CDC_B_ID ";
    } else {
        $query = "SELECT A.nop, A.wp_nama, A.wp_alamat, A.wp_kelurahan, A.op_alamat, A.op_kecamatan, A.op_kelurahan, A.op_rt, A.op_rw,
                A.op_luas_bumi, A.op_luas_bangunan,A.op_njop_bumi,A.op_njop_bangunan,A.sppt_tahun_pajak, 
                A.sppt_tanggal_jatuh_tempo , IFNULL(A.sppt_pbb_harus_dibayar,0) AS sppt_pbb_harus_dibayar, IFNULL(B.pbb_denda,0) as pbb_denda , IFNULL(A.sppt_pbb_harus_dibayar+B.pbb_denda,0) as pbb_total_bayar,
				(A.OP_NJOP_BUMI/A.OP_LUAS_BUMI) AS njop_permeter_bumi, (A.OP_NJOP_BANGUNAN/A.OP_LUAS_BANGUNAN) as njop_permeter_bangunan
                FROM PBB_SPPT A LEFT JOIN PBB_DENDA B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";
    }
    $result = $monPBB->query_result($query);
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("Alfa System")
    ->setSubject("Alfa System pbb")
    ->setDescription("pbb")
    ->setKeywords("Alfa System");


// Add some data
if ($status == '1') {
    /*objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'No.')
        ->setCellValue('B1', 'NOP')
        ->setCellValue('C1', 'Nama WP')
        ->setCellValue('D1', 'Alamat WP')
        ->setCellValue('E1', $_REQUEST['LBL_KEL'] . ' WP')
        ->setCellValue('F1', 'Alamat OP')
        ->setCellValue('G1', 'Kecamatan OP')
        ->setCellValue('H1', $_REQUEST['LBL_KEL'] . ' OP')
        ->setCellValue('I1', 'RT OP')
        ->setCellValue('J1', 'RW OP')
        ->setCellValue('K1', 'Luas Bumi')
        ->setCellValue('L1', 'Luas Bangunan')
        ->setCellValue('M1', 'Total NJOP Bumi')
        ->setCellValue('N1', 'Total NJOP Bangunan')
        ->setCellValue('O1', 'Tahun Pajak')
        ->setCellValue('P1', 'Tgl Jatuh Tempo')
        ->setCellValue('Q1', 'Pokok')
        ->setCellValue('R1', 'Denda')
        ->setCellValue('S1', 'Total')
        ->setCellValue('T1', 'Status')
        ->setCellValue('U1', 'Tanggal Bayar')
        ->setCellValue('V1', 'Bank');*/

    // aldes, reordering header column
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'No.')
        ->setCellValue('B1', 'NOP')
        ->setCellValue('C1', 'Nama WP')
        ->setCellValue('D1', 'Tahun Pajak')
        ->setCellValue('E1', 'Tgl Jatuh Tempo')
        ->setCellValue('F1', 'Alamat WP')
        ->setCellValue('G1', $_REQUEST['LBL_KEL'] . ' WP')
        ->setCellValue('H1', 'Alamat OP')
        ->setCellValue('I1', 'Kecamatan OP')
        ->setCellValue('J1', $_REQUEST['LBL_KEL'] . ' OP')
        ->setCellValue('K1', 'RT OP')
        ->setCellValue('L1', 'RW OP')
        ->setCellValue('M1', 'Luas Bumi')
        ->setCellValue('N1', 'Luas Bangunan')
        ->setCellValue('O1', 'Total NJOP Bumi')
        ->setCellValue('P1', 'Total NJOP Bangunan')
        ->setCellValue('Q1', 'Pokok')
        ->setCellValue('R1', 'Denda')
        ->setCellValue('S1', 'Total')
        ->setCellValue('T1', 'Status')
        ->setCellValue('U1', 'Tanggal Bayar')
        ->setCellValue('V1', 'Bank');
    $row = 2;
} else {
    $tahun = date('Y');
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A2', 'DAFTAR NOMINATIF')
        ->setCellValue('A3', 'VERIFIKASI OBJEK PAJAK PIUTANG PBB-P2 KECAMATAN ' . $_REQUEST['nmkc'] . ' ,DESA ' . $_REQUEST['nmkl'])
        ->setCellValue('A4', "BADAN PENDAPATAN PAJAK DAN RETRIBUSI DAERAH {$appConfig['C_KABKOT']} {$appConfig['KANWIL']}")
        ->setCellValue('A5', 'TAHUN ' . $thn)
        ->setCellValue('A6', 'No.')
        ->setCellValue('B6', 'NOP')
        ->setCellValue('C6', 'Nama WP')
        ->setCellValue('D6', 'Tahun Pajak')
        ->setCellValue('E6', 'Tgl Jatuh Tempo')
        ->setCellValue('F6', 'Alamat WP')
        ->setCellValue('G6', $_REQUEST['LBL_KEL'] . ' WP')
        ->setCellValue('H6', 'Alamat OP')
        ->setCellValue('I6', 'Kecamatan OP')
        ->setCellValue('J6', $_REQUEST['LBL_KEL'] . ' OP')
        ->setCellValue('K6', 'RT OP')
        ->setCellValue('L6', 'RW OP')
        ->setCellValue('M6', 'Luas Bumi')
        ->setCellValue('N6', 'Luas Bangunan')
        ->setCellValue('O6', 'Total NJOP Bumi')
        ->setCellValue('P6', 'Total NJOP Bangunan')
        ->setCellValue('Q6', 'Pokok')
        ->setCellValue('R6', 'Denda')
        ->setCellValue('S6', 'Total')
        ->setCellValue('T6', 'Kategori Piutang')
        ->setCellValue('T7', '1')
        ->setCellValue('U7', '2')
        ->setCellValue('V7', '3')
        ->setCellValue('W7', '4')
        ->setCellValue('X6', 'Keterangan')
		->setCellValue('Y6', 'NJOP Bumi Permeter')
        ->setCellValue('Z6', 'NJOP Bangunan Permeter');
    $objPHPExcel->getActiveSheet()->mergeCells('A6:A7');
    $objPHPExcel->getActiveSheet()->mergeCells('B6:B7');
    $objPHPExcel->getActiveSheet()->mergeCells('C6:C7');
    $objPHPExcel->getActiveSheet()->mergeCells('D6:D7');
    $objPHPExcel->getActiveSheet()->mergeCells('E6:E7');
    $objPHPExcel->getActiveSheet()->mergeCells('F6:F7');
    $objPHPExcel->getActiveSheet()->mergeCells('G6:G7');
    $objPHPExcel->getActiveSheet()->mergeCells('H6:H7');
    $objPHPExcel->getActiveSheet()->mergeCells('I6:I7');
    $objPHPExcel->getActiveSheet()->mergeCells('J6:J7');
    $objPHPExcel->getActiveSheet()->mergeCells('K6:K7');
    $objPHPExcel->getActiveSheet()->mergeCells('L6:L7');
    $objPHPExcel->getActiveSheet()->mergeCells('M6:M7');
    $objPHPExcel->getActiveSheet()->mergeCells('N6:N7');
    $objPHPExcel->getActiveSheet()->mergeCells('O6:O7');
    $objPHPExcel->getActiveSheet()->mergeCells('P6:P7');
    $objPHPExcel->getActiveSheet()->mergeCells('Q6:Q7');
    $objPHPExcel->getActiveSheet()->mergeCells('R6:R7');
    $objPHPExcel->getActiveSheet()->mergeCells('S6:S7');
    $objPHPExcel->getActiveSheet()->mergeCells('X6:X7');
    $objPHPExcel->getActiveSheet()->mergeCells('Y6:Y7');
    $objPHPExcel->getActiveSheet()->mergeCells('Z6:Z7');



    $objPHPExcel->getActiveSheet()->mergeCells('T6:W6');
    $objPHPExcel->getActiveSheet()->mergeCells('A2:S2');
    $objPHPExcel->getActiveSheet()->mergeCells('A3:S3');
    $objPHPExcel->getActiveSheet()->mergeCells('A4:S4');
    $objPHPExcel->getActiveSheet()->mergeCells('A5:S5');

    $row = 8;
}
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$sumRows = mysqli_num_rows($result['data']);

$totalPokok = $totalDenda = $totalBayar = 0;
while ($rowData = mysqli_fetch_assoc($result['data'])) {
    $tgl_jth_tempo = explode('-', $rowData['sppt_tanggal_jatuh_tempo']);
    if (count($tgl_jth_tempo) == 3) $tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];

    $payment_date = '';
    if ($rowData['payment_paid'] != null && $rowData['payment_paid'] != '')
        $payment_date = substr($rowData['payment_paid'], 8, 2) . '-' . substr($rowData['payment_paid'], 5, 2) . '-' . substr($rowData['payment_paid'], 0, 4) . ' ' . substr($rowData['payment_paid'], 11);


    if ($status == '1') {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 7));
    }
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['nop'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['wp_nama']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['sppt_tahun_pajak']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $tgl_jth_tempo);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['wp_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['wp_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['op_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['op_kecamatan']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['op_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['op_rt']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['op_rw']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['op_luas_bumi']);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['op_luas_bangunan']);
    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, ' ' . number_format($rowData['op_njop_bumi'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, ' ' . number_format($rowData['op_njop_bangunan'], 0, ',', '.'));
    // $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row, ' '.number_format($rowData['sppt_pbb_harus_dibayar'],0,',','.'));
    // $objPHPExcel->getActiveSheet()->setCellValue('R'.$row, ' '.number_format($rowData['pbb_denda'],0,',','.'));
    // $objPHPExcel->getActiveSheet()->setCellValue('S'.$row, ' '.number_format($rowData['pbb_total_bayar'],0,',','.'));
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['sppt_pbb_harus_dibayar']);
    $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['pbb_denda']);
    $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['pbb_total_bayar']);
    if ($status == '1') {
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : 'Terutang');
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $payment_date);
        $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $rowData['CDC_B_NAME']);
    }else{
		$objPHPExcel->getActiveSheet()->setCellValue('Y' . $row, $rowData['njop_permeter_bumi']);
        $objPHPExcel->getActiveSheet()->setCellValue('Z' . $row, $rowData['njop_permeter_bangunan']);
	}
    $row++;
    $totalPokok += $rowData['sppt_pbb_harus_dibayar'];
    $totalDenda += $rowData['pbb_denda'];
    $totalBayar += $rowData['pbb_total_bayar'];
}

$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'JUMLAH');
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, ' ' . number_format($totalPokok, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('R' . $row, ' ' . number_format($totalDenda, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('S' . $row, ' ' . number_format($totalBayar, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':P' . $row);
$row += 1;

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar WP');

//----set style cell

if ($status == 1)
    $lastColumn = 'V';
else $lastColumn = 'Z';

if ($status == '1') {
    //style header
    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->applyFromArray(
        array(
            'font'    => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . ($sumRows + 2))->applyFromArray(
        array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('I2:L' . ($sumRows + 2))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
    );

    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFill()->getStartColor()->setRGB('E4E4E4');

    $objPHPExcel->getActiveSheet()->getStyle('A2:B' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('N2:N' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('O2:O' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('P2:P' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('Q2:Q' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('R2:R' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('S2:S' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    if ($status == '1') {
        $objPHPExcel->getActiveSheet()->getStyle('T2:T' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('U2:U' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('V2:U' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
} else {

    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row += 1, 'kategori objek pajak');
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row += 2, '1. Objek pajak yang memiliki dua atau lebih NOP sehingga SPPT PPB-nya di terbitkan lebih dari satu kali pada tahun pajak yang sama (SPPT Dobel)');
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':H' . $row);

    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row += 1, '2. Objek pajak yang telah terdaftar namun secara nyata tidak dapat di temukan lokasinya di lapangan (objek tidak ditemukan)');
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':H' . $row);

    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row += 1, '3. Objek pajak yang identitas subjek pajaknya tidak jelas (subek tidak ditemukan/tidak ada)');
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':H' . $row);

    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row += 1, '4. Objek pajak yang lokasi dan subjek pajaknya tidak dapat teridentifikasi dengan jelas (subjek dan objek pajak tidak ditemukan/tidak ada)');
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':H' . $row);

    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row += 2, date('d-m-Y'));
    $objPHPExcel->getActiveSheet()->mergeCells('G' . $row . ':H' . $row);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $nmkelurahan = ucfirst($_REQUEST['nmkl']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row += 1, 'Kepala Desa ' . $nmkelurahan);
    $objPHPExcel->getActiveSheet()->mergeCells('G' . $row . ':H' . $row);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $nmkecamatan = ucfirst($_REQUEST['nmkc']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row += 1, 'Kecamatan ' . $nmkecamatan);
    $objPHPExcel->getActiveSheet()->mergeCells('G' . $row . ':H' . $row);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row += 4, 'TTD');
    $objPHPExcel->getActiveSheet()->mergeCells('G' . $row . ':H' . $row);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row += 1, '(___________________________)');
    $objPHPExcel->getActiveSheet()->mergeCells('G' . $row . ':H' . $row);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


    //style header
    $objPHPExcel->getActiveSheet()->getStyle('A6:' . $lastColumn . '7')->applyFromArray(
        array(
            'font'    => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('A6:' . $lastColumn . ($sumRows + 8))->applyFromArray(
        array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('I6:L' . ($sumRows + 8))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
    );



    $objPHPExcel->getActiveSheet()->getStyle('A6:' . $lastColumn . '7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A6:' . $lastColumn . '7')->getFill()->getStartColor()->setRGB('E4E4E4');

    $objPHPExcel->getActiveSheet()->getStyle('A8:B' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('N8:N' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('M8:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('O8:O' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('P8:P' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('Q8:Q' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('R8:R' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('S8:S' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('D8:E' . ($sumRows + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A2:A5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("30");
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth("30");
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth("18");
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth("7");
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth("7");
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth("10");
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth("15");
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth("22");
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth("10");
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth("10");
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth("10");
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth("10");
    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth("20");
	$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
}

$namafile = ($status=='1') ? 'SUDAH_BAYAR' : 'Belum_Bayar';
$uniq = uniqid();

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
if ($p != 'all'){
    $uniq = substr($uniq,-5);
    header('Content-Disposition: attachment;filename="' . $namafile . '_Part_' . $p . '_(' . $uniq . ').xls"');
}else{
    $uniq = substr($uniq,-7);
    header('Content-Disposition: attachment;filename="' . $namafile . '_(' . $uniq . ').xls"');
}
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
