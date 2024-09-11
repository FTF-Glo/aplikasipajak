<?php
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'rekonsiliasi', '', dirname(__FILE__))) . '/';

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

$q         = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p         = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml     = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn     = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nop     = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na     = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
$status = @isset($_REQUEST['stp']) ? $_REQUEST['stp'] : "";
$total     = @isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;

$nmFile = "Data-WP-Sudah-Bayar";
if ($status == 2) {
    $nmFile = "Data-WP-Belum-Bayar";
}

$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";

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

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "nop like '{$kelurahan}%'");
    else array_push($arrWhere, "nop like '{$kecamatan}%'");
}

if ($nop != "") array_push($arrWhere, "nop='{$nop}'");
if ($thn != "") array_push($arrWhere, "sppt_tahun_pajak='{$thn}'");
if ($na != "") array_push($arrWhere, "wp_nama like '%{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "payment_flag = 1");
    } else if ($status == 2) {
        array_push($arrWhere, "(payment_flag != 1 OR payment_flag IS NULL)");
    }
}
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");

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

$where = implode(" AND ", $arrWhere);

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToMysql();
    if ($p == 'all') {
        $monPBB->setRowPerpage($total);
        $monPBB->setPage(1);
    } else {
        $monPBB->setRowPerpage(20000);
        $monPBB->setPage($p);
    }
    $monPBB->setTable("PBB_SPPT");
    $monPBB->setWhere($where);
    $query = "select nop, wp_nama, wp_alamat, wp_kelurahan, op_alamat, op_kecamatan, op_kelurahan, op_rt, op_rw,
                op_luas_bumi, op_luas_bangunan,op_njop_bumi,op_njop_bangunan,sppt_tahun_pajak, sppt_tanggal_jatuh_tempo , sppt_pbb_harus_dibayar, payment_flag, payment_paid ";
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
$objPHPExcel->setActiveSheetIndex(0)
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
    ->setCellValue('Q1', 'Tagihan')
    ->setCellValue('R1', 'Status')
    ->setCellValue('S1', 'Tanggal Bayar');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 2;
$sumRows = mysqli_num_rows($result['data']);
$total = 0;
while ($rowData = mysqli_fetch_assoc($result['data'])) {
    $tgl_jth_tempo = explode('-', $rowData['sppt_tanggal_jatuh_tempo']);
    if (count($tgl_jth_tempo) == 3) $tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];

    $payment_date = '';
    if ($rowData['payment_paid'] != null && $rowData['payment_paid'] != '')
        $payment_date = substr($rowData['payment_paid'], 8, 2) . '-' . substr($rowData['payment_paid'], 5, 2) . '-' . substr($rowData['payment_paid'], 0, 4) . ' ' . substr($rowData['payment_paid'], 11);


    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['nop'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['wp_nama']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['wp_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['wp_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['op_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['op_kecamatan']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['op_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['op_rt']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['op_rw']);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['op_luas_bumi']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['op_luas_bangunan']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, ' ' . number_format($rowData['op_njop_bumi'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, ' ' . number_format($rowData['op_njop_bangunan'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['sppt_tahun_pajak']);
    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $tgl_jth_tempo);
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, ' ' . number_format($rowData['sppt_pbb_harus_dibayar'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : '');
    $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $payment_date);
    $row++;
    $total += $rowData['sppt_pbb_harus_dibayar'];
}

$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'JUMLAH');
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, ' ' . number_format($total, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':P' . $row);

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar WP');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A1:S1')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A1:S' . ($sumRows + 2))->applyFromArray(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A1:S1')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A2:B' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('N2:N' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('O2:O' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('P2:P' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('Q2:Q' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('R2:R' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('S2:S' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
if ($p != 'all')
    header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
else header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
