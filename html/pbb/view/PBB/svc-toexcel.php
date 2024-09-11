<?php
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';

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
require_once("config-monitoring.php");

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

$nmFile = "Data-WP-Sudah-Bayar";
if ($status == 2) {
    $nmFile = "Data-WP-Belum-Bayar";
}

$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$kelurahan = "";
if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$host = DBHOST;
$port = DBPORT;
$user = DBUSER;
$pass = DBPWD;
$dbname = DBNAME;


$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "sppt_tanggal_jatuh_tempo='{$tempo1}'");
if ($tempo2 != "") array_push($arrTempo, "sppt_tanggal_jatuh_tempo='{$tempo2}'");
$tempo = implode(" OR ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "nop like '{$kelurahan}%'");
    else array_push($arrWhere, "nop like '{$kecamatan}%'");
}

if ($nop != "") array_push($arrWhere, "nop='{$nop}'");
if ($thn != "") array_push($arrWhere, "sppt_tahun_pajak='{$thn}'");
if ($na != "") array_push($arrWhere, "wp_nama like '%{$na}%'");
if ($status != "") {
    $sts = 0;
    if ($status == 1) $sts = 1;
    array_push($arrWhere, "payment_flag = '{$sts}'");
}
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");
$where = implode(" AND ", $arrWhere);

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToPostgres();
    $monPBB->setRowPerpage(1000);
    $monPBB->setPage($p);
    $monPBB->setTable("PBB.PBB_SPPT");
    $monPBB->setWhere($where);
    $query = "select nop, wp_nama, wp_alamat, wp_kelurahan, wp_kecamatan, wp_handphone , op_alamat, op_kecamatan, op_kelurahan,
					sppt_tahun_pajak, sppt_tanggal_jatuh_tempo , sppt_pbb_harus_dibayar, payment_flag";
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
    ->setCellValue('E1', 'Kelurahan WP')
    ->setCellValue('F1', 'Kecamatan WP')
    ->setCellValue('G1', 'Nomor HP')
    ->setCellValue('H1', 'Alamat OP')
    ->setCellValue('I1', 'Kecamatan OP')
    ->setCellValue('J1', 'Kelurahan OP')
    ->setCellValue('K1', 'Thn Pajak')
    ->setCellValue('L1', 'Jth Tempo')
    ->setCellValue('M1', 'Tagihan')
    ->setCellValue('N1', 'Status');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 2;
$sumRows = pg_num_rows($result['data']);

while ($rowData = pg_fetch_assoc($result['data'])) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['nop'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['wp_nama']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['wp_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['wp_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['wp_kecamatan']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['wp_handphone']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['op_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['op_kecamatan']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['op_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['sppt_tahun_pajak']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['sppt_tanggal_jatuh_tempo']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, 'Rp. ' . number_format($rowData['sppt_pbb_harus_dibayar'], 0, '.', ','));
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['payment_flag']);
    $row++;
}



// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar WP');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A1:N' . ($sumRows + 1))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle('I2:L' . ($sumRows + 1))->applyFromArray(
    array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A2:B' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('N2:N' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

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


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
