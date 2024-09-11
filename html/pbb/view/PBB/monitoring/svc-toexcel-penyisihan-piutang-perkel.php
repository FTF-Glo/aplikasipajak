<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);
//date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
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

//error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBlink = "";

// koneksi postgres
function openMysql()
{
	global $appConfig;
	$host = $appConfig['GW_DBHOST'];
	$port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
	$user = $appConfig['GW_DBUSER'];
	$pass = $appConfig['GW_DBPWD'];
	$dbname = $appConfig['GW_DBNAME'];
	$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		echo mysqli_error($myDBLink);
		//exit();
	}
	//$database = mysql_select_db($dbname, $myDBLink);
	return $myDBLink;
}

function closeMysql($con)
{
	mysqli_close($con);
}

function convertDate($str)
{
	if ($str == '') return '';
	$tmp = explode('-', $str);
	return $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
}

function getData($kelurahan, $tahun)
{
	global $myDBLink;

	$myDBLink 	= openMysql();
	$data 		= array();

	$query = "SELECT
					NOP,
					WP_NAMA AS NAMA,
					SPPT_PBB_HARUS_DIBAYAR AS TAGIHAN,
					SPPT_TAHUN_PAJAK AS TAHUN,
					SPPT_TANGGAL_TERBIT AS TANGGAL_TERBIT,
					SPPT_TANGGAL_JATUH_TEMPO AS TANGGAL_JATUH_TEMPO
				FROM
					PBB_SPPT
				WHERE
					SPPT_TAHUN_PAJAK = '" . $tahun . "'
				AND (
					PAYMENT_FLAG IS NULL
					OR PAYMENT_FLAG != '1'
					OR (
						PAYMENT_FLAG = '1'
						AND PAYMENT_PAID >= '2015-12-31'
					)
				)
				AND NOP LIKE '" . $kelurahan . "%'
				ORDER BY
					NOP ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["NOP"] 				 = ($row["NOP"] != "") ? $row["NOP"] : "";
		$data[$i]["NAMA"] 				 = ($row["NAMA"] != "") ? $row["NAMA"] : "";
		$data[$i]["TAGIHAN"] 			 = ($row["TAGIHAN"] != "") ? $row["TAGIHAN"] : 0;
		$data[$i]["TAHUN"] 				 = ($row["TAHUN"] != "") ? $row["TAHUN"] : "";
		$data[$i]["TANGGAL_TERBIT"] 	 = ($row["TANGGAL_TERBIT"] != "") ? $row["TANGGAL_TERBIT"] : "";
		$data[$i]["TANGGAL_JATUH_TEMPO"] = ($row["TANGGAL_JATUH_TEMPO"] != "") ? $row["TANGGAL_JATUH_TEMPO"] : "";
		$i++;
	}
	closeMysql($myDBLink);
	// print_r($data); exit;
	return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$kd 				= $appConfig['KODE_KOTA'];
$kadis 				= $appConfig['PEJABAT_SK2'];
$kadisNama 			= $appConfig['NAMA_PEJABAT_SK2'];
$kadisNIP 			= 'NIP. ' . $appConfig['NAMA_PEJABAT_SK2_NIP'];
$kadisJabatan 		= $appConfig['NAMA_PEJABAT_SK2_JABATAN'];
$kabid 				= 'KEPALA BIDANG PEMBUKUAN DAN PELAPORAN,';
$kabidNama 			= 'JOCKE S. M. BOELAN, S.Ip'; //$appConfig['KABID_NAMA'];
$kabidNIP 			= 'NIP. 19630808 198903 2 014'; //$appConfig['KABID_NIP'];
$kabidJabatan 		= strtoupper('Penata Tingkat I'); //$appConfig['KABID_JABATAN'];
$kota 				= $appConfig['C_KABKOT'];
$namaKota 			= $appConfig['NAMA_KOTA'];
$namaKotaPengesahan = $appConfig['NAMA_KOTA_PENGESAHAN'];

$kab 				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 			= @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";
$kelurahan 			= @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "";
$tahunawal 			= @isset($_REQUEST['tahunawal']) ? $_REQUEST['tahunawal'] : "";
$namakec 			= @isset($_REQUEST['namakec']) ? $_REQUEST['namakec'] : "";
$namakel 			= @isset($_REQUEST['namakel']) ? $_REQUEST['namakel'] : "";

$data = getData($kelurahan, $tahunawal);

$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");

$objPHPExcel->getActiveSheet()->getStyle('A1:N2')->applyFromArray(
	array(
		'font' => array(
			'size' => $fontSizeHeader
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENYISIHAN PIUTANG PAJAK BUMI DAN BANGUNAN (PBB) ' . $kota . ' ' . $namaKota);
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']) . ' ' . $namakel);
$objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A2:J2');

// Header Of Table

$objPHPExcel->getActiveSheet()->setCellValue('A4', "NO");
$objPHPExcel->getActiveSheet()->setCellValue('B4', "NOP");
$objPHPExcel->getActiveSheet()->setCellValue('C4', "NAMA WP");
$objPHPExcel->getActiveSheet()->setCellValue('D4', "NILAI \nPIUTANG");
$objPHPExcel->getActiveSheet()->setCellValue('E4', "TAHUN");
$objPHPExcel->getActiveSheet()->setCellValue('F4', "TANGGAL \nKETETAPAN");
$objPHPExcel->getActiveSheet()->setCellValue('G4', "TANGGAL \nJATUH TEMPO");
$objPHPExcel->getActiveSheet()->setCellValue('H4', "TINDAKAN \nPENAGIHAN TERAKHIR");
$objPHPExcel->getActiveSheet()->setCellValue('I4', "NILAI \nAGUNAN/BARANG SITAAN (Rp)");
$objPHPExcel->getActiveSheet()->setCellValue('J4', "KETERANGAN");

$objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('F4')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('G4')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('I4')->getAlignment()->setWrapText(true);

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle($tahunawal);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A4:J4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A4:J4')->getFill()->getStartColor()->setRGB('E4E4E4');
$objPHPExcel->getActiveSheet()->getStyle('A4:J4')->applyFromArray(
	array(
		'font' => array(
			'size' => $fontSizeHeader
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:J50')->applyFromArray(
	array(
		'alignment' => array(
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('size' => $fontSizeDefault, 'bold' => false));
$noBoldCenter = array('font' => array('size' => $fontSizeDefault, 'bold' => false), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$bold = array('font' => array('bold' => true));

$summary = array(0);
foreach ($data as $buffer) {
	$objPHPExcel->getActiveSheet()->getRowDimension(4 + $no)->setRowHeight(18);
	$objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), $no)->getStyle('A' . (4 + $no))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $no), $buffer["NOP"] . " ")->getStyle('B' . (4 + $no))->applyFromArray($noBoldCenter);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $no), $buffer["NAMA"])->getStyle('C' . (4 + $no))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $buffer["TAGIHAN"])->getStyle('D' . (4 + $no))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $no), $buffer["TAHUN"])->getStyle('E' . (4 + $no))->applyFromArray($noBoldCenter);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $no), convertDate($buffer["TANGGAL_TERBIT"]))->getStyle('F' . (4 + $no))->applyFromArray($noBoldCenter);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . (4 + $no), convertDate($buffer["TANGGAL_JATUH_TEMPO"]))->getStyle('G' . (4 + $no))->applyFromArray($noBoldCenter);

	$summary[0] += $buffer["TAGIHAN"];

	$no++;
}
$objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $no))->applyFromArray(
	array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);

$no++;
$objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A4:J' . (5 + count($data)))->applyFromArray(
	array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//===================================[SHEET >1]====================================================

//Start adding next sheets
$i = 1;
while ($i <= 5) {

	// // Add new sheet
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex($i);

	// //Write cells
	$tahunsheet = ($tahunawal - $i);
	$data2 = getData($kelurahan, $tahunsheet);

	$fontSizeHeader = 10;
	$fontSizeDefault = 9;

	// Set properties
	$objPHPExcel->getProperties()->setCreator("vpost")
		->setLastModifiedBy("vpost")
		->setTitle("Alfa System")
		->setSubject("Alfa System pbb")
		->setDescription("pbb")
		->setKeywords("Alfa System");

	$objPHPExcel->getActiveSheet()->getStyle('A1:N2')->applyFromArray(
		array(
			'font' => array(
				'size' => $fontSizeHeader
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)
	);

	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('PENYISIHAN PIUTANG PAJAK BUMI DAN BANGUNAN (PBB) ' . $kota . ' ' . $namaKota);
	$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']) . ' ' . $namakel);
	$objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A2:J2');

	// Header Of Table

	$objPHPExcel->getActiveSheet()->setCellValue('A4', "NO");
	$objPHPExcel->getActiveSheet()->setCellValue('B4', "NOP");
	$objPHPExcel->getActiveSheet()->setCellValue('C4', "NAMA WP");
	$objPHPExcel->getActiveSheet()->setCellValue('D4', "NILAI \nPIUTANG");
	$objPHPExcel->getActiveSheet()->setCellValue('E4', "TAHUN");
	$objPHPExcel->getActiveSheet()->setCellValue('F4', "TANGGAL \nKETETAPAN");
	$objPHPExcel->getActiveSheet()->setCellValue('G4', "TANGGAL \nJATUH TEMPO");
	$objPHPExcel->getActiveSheet()->setCellValue('H4', "TINDAKAN \nPENAGIHAN TERAKHIR");
	$objPHPExcel->getActiveSheet()->setCellValue('I4', "NILAI \nAGUNAN/BARANG SITAAN (Rp)");
	$objPHPExcel->getActiveSheet()->setCellValue('J4', "KETERANGAN");

	$objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle('F4')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle('G4')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle('I4')->getAlignment()->setWrapText(true);

	// Set style for header row using alternative method
	$objPHPExcel->getActiveSheet()->getStyle('A4:J4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A4:J4')->getFill()->getStartColor()->setRGB('E4E4E4');
	$objPHPExcel->getActiveSheet()->getStyle('A4:J4')->applyFromArray(
		array(
			'font' => array(
				'size' => $fontSizeHeader
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			)
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:J50')->applyFromArray(
		array(
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)
	);

	//Set column widths
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

	$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
	$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
	$no = 1;

	$noBold = array('font' => array('size' => $fontSizeDefault, 'bold' => false));
	$noBoldCenter = array('font' => array('size' => $fontSizeDefault, 'bold' => false), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
	$bold = array('font' => array('bold' => true));

	$summary = array(0);
	foreach ($data2 as $buffer) {
		$objPHPExcel->getActiveSheet()->getRowDimension(4 + $no)->setRowHeight(18);
		$objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), $no)->getStyle('A' . (4 + $no))->applyFromArray($noBold);
		$objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $no), $buffer["NOP"] . " ")->getStyle('B' . (4 + $no))->applyFromArray($noBoldCenter);
		$objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $no), $buffer["NAMA"])->getStyle('C' . (4 + $no))->applyFromArray($noBold);
		$objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $buffer["TAGIHAN"])->getStyle('D' . (4 + $no))->applyFromArray($noBold);
		$objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $no), $buffer["TAHUN"])->getStyle('E' . (4 + $no))->applyFromArray($noBoldCenter);
		$objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $no), convertDate($buffer["TANGGAL_TERBIT"]))->getStyle('F' . (4 + $no))->applyFromArray($noBoldCenter);
		$objPHPExcel->getActiveSheet()->setCellValue('G' . (4 + $no), convertDate($buffer["TANGGAL_JATUH_TEMPO"]))->getStyle('G' . (4 + $no))->applyFromArray($noBoldCenter);

		$summary[0] += $buffer["TAGIHAN"];

		$no++;
	}
	$objPHPExcel->getActiveSheet()->getStyle('A' . (4 + $no))->applyFromArray(
		array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)
	);

	$objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), 'TOTAL');
	$objPHPExcel->getActiveSheet()->mergeCells('A' . (4 + $no) . ':C' . (4 + $no));
	$objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $summary[0])->getStyle('D' . (4 + $no))->applyFromArray($noBold);

	$no++;
	$objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), '');
	$objPHPExcel->getActiveSheet()->getStyle('A4:J' . (5 + count($data2)))->applyFromArray(
		array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
	);

	// Set page orientation and size
	$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	// // Rename sheet
	$objPHPExcel->getActiveSheet()->setTitle("$tahunsheet");

	$i++;
}

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="KUALITAS_PIUTANG.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
