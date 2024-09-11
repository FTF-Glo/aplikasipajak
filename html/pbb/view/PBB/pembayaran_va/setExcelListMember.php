<?php
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembayaran_va', '', dirname(__FILE__))) . '/';

// require_once("tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/ctools.php");
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
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once("classCollective.php");
// require_once("PHPExcel_1.8.0/Classes/PHPExcel.php");
require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$dbUtils = new DbUtils($dbSpec);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting 	= new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$appConfig 	= $User->GetAppConfig("aPBB");
$tahun		= $appConfig['tahun_tagihan'];
$host 	= $appConfig['GW_DBHOST'];
$port 	= $appConfig['GW_DBPORT'];
$user 	= $appConfig['GW_DBUSER'];
$pass 	= $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];


$svcCollective = new classCollective($dbSpec, $dbUtils);
$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER = $user;
$svcCollective->C_PWD = $pass;
$svcCollective->C_DB = $dbname;
$svcCollective->C_PORT = $port;

$dt = $svcCollective->getMemberByIDArray($_REQUEST['id']);
$NAMA_GROUP = strtoupper($dt[0]['NAMA_GROUP']);
// echo "<pre>";
// print_r($dt);
// echo "</pre>";
// exit;
// $dt 	= GetListByNOP($nop,$idwp,$thn1,$thn2);
// echo "<pre>";
// print_r($dt); exit;
$sumRows  = count($dt);
if ($sumRows < 1) {
	echo 'Data tidak tersedia';
	exit;
}
// echo $dt[$sumRows-1]['NOP']; exit;
$bulan = array(
	"01" => "Januari",
	"02" => "Februari",
	"03" => "Maret",
	"04" => "April",
	"05" => "Mei",
	"06" => "Juni",
	"07" => "Juli",
	"08" => "Agustus",
	"09" => "September",
	"10" => "Oktober",
	"11" => "November",
	"12" => "Desember"
);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Alfa System")
	->setLastModifiedBy("Alfa System")
	->setTitle("Data Pembayaran")
	->setSubject("Data Pembayaran")
	->setDescription("Data Pembayaran Kolektif Wajib Pajak PBB")
	->setKeywords("Alfa System pbb");
//Style Align
$center = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	)
);
$bold = array('font' => array('bold' => true));

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A2', 'PEMERINTAH ' . $appConfig['C_KABKOT'] . ' ' . $appConfig['NAMA_KOTA'])
	->setCellValue('A3', 'BADAN PELAYANAN PAJAK DAERAH')
	->setCellValue('A5', "INFORMASI DATA PEMBAYARAN  $NAMA_GROUP");
// ->setCellValue('A6', 'Nomor Objek Pajak')
// ->setCellValue('E6', 'Tahun Ketetapan')
// ->setCellValue('A11', 'Nama Wajib Pajak')
// ->setCellValue('A12', 'Alamat Wajib Pajak')
// ->setCellValue('A10', 'Alamat Objek Pajak')
// ->setCellValue('A9', 'Kecamatan Objek Pajak')
// ->setCellValue('E9', 'Kelurahan Objek Pajak')
// ->setCellValue('A7', 'Luas Bumi')
// ->setCellValue('E7', 'NJOP Bumi')
// ->setCellValue('A8', 'Luas Bangunan')
// ->setCellValue('E8', 'NJOP Bangunan')
// ->setCellValue('A13', 'Tanggal Printout');
// $idx = ($sumRows-1);	

// $objPHPExcel->setActiveSheetIndex(0)
//             ->setCellValue('B6', ": ".substr($dt[$idx]['NOP'],0,2).".".substr($dt[$idx]['NOP'],2,2).".".substr($dt[$idx]['NOP'],4,3).".".substr($dt[$idx]['NOP'],7,3).".".substr($dt[$idx]['NOP'],10,3).'-'.substr($dt[$idx]['NOP'],13,4).".".substr($dt[$idx]['NOP'],17,1))
//             ->setCellValue('F6', ": ".$dt[$idx]['SPPT_TAHUN_PAJAK'])
//             ->setCellValue('B11', ": ".$dt[$idx]['WP_NAMA'])
//             ->setCellValue('B12', ": ".$dt[$idx]['WP_ALAMAT'])
//             ->setCellValue('B10', ": ".$dt[$idx]['OP_ALAMAT'])
//             ->setCellValue('B9', ": ".$dt[$idx]['OP_KECAMATAN'])
//             ->setCellValue('F9', ": ".$dt[$idx]['OP_KELURAHAN'])
//             ->setCellValue('B7', ": ".$dt[$idx]['OP_LUAS_BUMI']." m2")
//             //->setCellValue('B14', ": ".$dt[$idx]['OP_NJOP_BUMI']."/m2")
//             ->setCellValue('F7',": ".number_format($dt[$idx]['OP_NJOP_BUMI']/$dt[$idx]['OP_LUAS_BUMI'], 2, ",", ".")."/m2")
//             ->setCellValue('B8', ": ".$dt[$idx]['OP_LUAS_BANGUNAN']." m2")
//             //->setCellValue('B16', ": ".$dt[$idx]['OP_NJOP_BANGUNAN']."/m2")
// 	    ->setCellValue('F8', ": ".number_format($dt[$idx]['OP_NJOP_BANGUNAN']/$dt[$idx]['OP_LUAS_BANGUNAN'], 2, ",", ".")."/m2")
//             ->setCellValue('B13', ": ".date("d/m/Y"));

//style header cop
$objPHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($bold);

$objPHPExcel->getActiveSheet()->mergeCells('A2:H2')
	->mergeCells('A3:H3');
// ->mergeCells('A5:G5')
// ->mergeCells('B6:D6')
// ->mergeCells('F6:G6')
// ->mergeCells('B7:D7')
// ->mergeCells('F7:G7')
// ->mergeCells('B8:D8')
// ->mergeCells('F8:G8')
// ->mergeCells('B9:D9')
// ->mergeCells('F9:G9')
// ->mergeCells('B10:G10')
// ->mergeCells('B11:G11')
// ->mergeCells('B12:G12')
// ->mergeCells('B13:G13')
// ->mergeCells('A14:G14');

//create Header
$start = 6;
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A' . $start, ($nop == "" ? "NOP" : "NAMA WAJIB PAJAK"))
	->setCellValue('B' . $start, 'TAHUN PAJAK')
	->setCellValue('C' . $start, 'NAMA WP')
	->setCellValue('D' . $start, 'PBB')
	->setCellValue('E' . $start, 'DENDA(*)')
	->setCellValue('F' . $start, 'JATUH TEMPO')
	->setCellValue('G' . $start, 'KURANG BAYAR')
	->setCellValue('H' . $start, 'STATUS BAYAR');
//style header
$objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':H' . $start)->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':H' . $start)->applyFromArray($bold);

//border table
$objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':H' . ($start + $sumRows))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
// $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(6)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(7)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(8)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(9)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(10)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(11)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(12)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(13)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(14)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(22)->setVisible(false);
// $objPHPExcel->getActiveSheet()->getRowDimension(23)->setVisible(false);

$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

//create data			
$no		= 1;
foreach ($dt as $dt) {
	// Jika berdasarkan NOP
	if ($nop == "") {
		$dtNOP = substr($dt['NOP'], 0, 2) . '.' . substr($dt['NOP'], 2, 2) . '.' . substr($dt['NOP'], 4, 3) . '.' . substr($dt['NOP'], 7, 3) . '.' . substr($dt['NOP'], 10, 3) . '-' . substr($dt['NOP'], 13, 4) . '.' . substr($dt['NOP'], 17, 1);
	} else { //Jika berdasarkan ID WP
		$dtNOP = $dt['WP_NAMA'];
	}
	$tglJatuhTempo = substr($dt['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . "/" . substr($dt['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) . "/" . substr($dt['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);

	$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no), $dtNOP);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . ($start + $no), $dt['SPPT_TAHUN_PAJAK']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . ($start + $no), $dt['WP_NAMA']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . ($start + $no), $dt['SPPT_PBB_HARUS_DIBAYAR']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . ($start + $no), $dt['PBB_DENDA'] /** $dt['CPM_CGM_PENALTY_FEE'] */);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . ($start + $no), $tglJatuhTempo);

	if ($dt['PAYMENT_FLAG'] === NULL && $dt['PAYMENT_FLAG'] != '1') {
		$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no), $dt['SPPT_PBB_HARUS_DIBAYAR']);

		$status = "BELUM BAYAR";
	} else if ($row['PAYMENT_FLAG'] -= '1') {
		$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no), 0);
		$status = "SUDAH BAYAR";
	}

	$objPHPExcel->getActiveSheet()->setCellValue('H' . ($start + $no), $status);
	$no++;

	$sum_total += $dt['SPPT_PBB_HARUS_DIBAYAR'];
}
// var_dump($start);
// var_dump($sumRows);
// exit;

//style data		
$objPHPExcel->getActiveSheet()->getStyle('B' . ($start) . ':B' . ($start + $sumRows))->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('E' . ($start) . ':E' . ($start + $sumRows))->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('G' . ($start) . ':G' . ($start + $sumRows))->applyFromArray($center);
//data summary
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 1) . ':F' . ($start + $no + 1));
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 2) . ':F' . ($start + $no + 2));
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 3) . ':F' . ($start + $no + 3));
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 4) . ':F' . ($start + $no + 4));
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 1), 'TOTAL PBB YANG BELUM DIBAYAR');
// $objPHPExcel->getActiveSheet()->setCellValue('A'.($start+$no+2), 'TOTAL DENDA (SESUAI TANGGAL PRINTOUT)');
// $objPHPExcel->getActiveSheet()->setCellValue('A'.($start+$no+3), 'JUMLAH YANG HARUS DIBAYAR');
$objPHPExcel->getActiveSheet()->setCellValue('H' . ($start + $no + 1), $sum_total);
// $objPHPExcel->getActiveSheet()->setCellValue('G'.($start+$no+2), $dt['SUM_DENDA_XLS'],0,"","");
// $objPHPExcel->getActiveSheet()->setCellValue('G'.($start+$no+3), ($dt['SUM_TOTAL_XLS']+$dt['SUM_DENDA_XLS']),0,"","");
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 4), '*Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.');
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 6), 'Petugas');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($start + $no + 6), ': ..................................................');
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 7), 'Keperluan');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($start + $no + 7), ': ..................................................');

//Set TTD
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 6), $appConfig['NAMA_KOTA_PENGESAHAN'] . ', ' . date('d') . ' ' . $bulan[date('m')] . ' ' . date('Y'));
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 10), $appConfig['KABID_NAMA']);

$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 11), " " . $appConfig['KABID_NIP']);
//style TTD
$objPHPExcel->getActiveSheet()->getStyle('G' . ($start + $no + 6) . ':H' . ($start + $no + 11))->applyFromArray($center);

//border summary
$objPHPExcel->getActiveSheet()->getStyle('A' . ($start + $no + 1) . ':H' . ($start + $no + 3))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);
// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Data Pembayaran');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
// $objPHPExcel->setActiveSheetIndex(0);

// $gdImage = imagecreatefromjpeg('logo.jpg');
// // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
// $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
// $objDrawing->setName('logo');$objDrawing->setDescription('logo');
// $objDrawing->setImageResource($gdImage);
// $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
// $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
// $objDrawing->setHeight(40);
// $objDrawing->setCoordinates('A2');
// $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment;filename=DATA_PEMBAYARAN_GROUP_{$NAMA_GROUP}.xls");
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
