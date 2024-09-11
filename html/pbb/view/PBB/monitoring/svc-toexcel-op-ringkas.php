<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

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


function bulan($bln)
{
	$bulan = $bln;
	switch ($bulan) {
		case 1:
			$bulan = "Januari";
			break;
		case 2:
			$bulan = "Februari";
			break;
		case 3:
			$bulan = "Maret";
			break;
		case 4:
			$bulan = "April";
			break;
		case 5:
			$bulan = "Mei";
			break;
		case 6:
			$bulan = "Juni";
			break;
		case 7:
			$bulan = "Juli";
			break;
		case 8:
			$bulan = "Agustus";
			break;
		case 9:
			$bulan = "September";
			break;
		case 10:
			$bulan = "Oktober";
			break;
		case 11:
			$bulan = "November";
			break;
		case 12:
			$bulan = "Desember";
			break;
	}
	return strtoupper($bulan);
}

function getData()
{
	global $DBLink;

	//$nop = $_POST['nop'];
	$nop1 = $_POST['nop1'];
	$nop2 = $_POST['nop2'];
	$nop3 = $_POST['nop3'];
	$nop4 = $_POST['nop4'];
	$nop5 = $_POST['nop5'];
	$nop6 = $_POST['nop6'];
	$nop7 = $_POST['nop7'];
	$blok_awal = $_POST['blok_awal'];
	$blok_akhir = $_POST['blok_akhir'];

	//$where = empty($nop) ? '' : sprintf("AND CPM_NOP = '%s'", $nop);
	$where = null;
	//$where = empty($nop) ? '' : sprintf("AND CPM_NOP = '%s'", $nop);
	$where .= empty($nop1) ? '' : sprintf("AND SUBSTR(CPM_NOP, 1, 2) = '%s'", $nop1);
	$where .= empty($nop2) ? '' : sprintf("AND SUBSTR(CPM_NOP, 3, 2) = '%s'", $nop2);
	$where .= empty($nop3) ? '' : sprintf("AND SUBSTR(CPM_NOP, 5, 3) = '%s'", $nop3);
	$where .= empty($nop4) ? '' : sprintf("AND SUBSTR(CPM_NOP, 8, 3) = '%s'", $nop4);
	$where .= empty($nop5) ? '' : sprintf("AND SUBSTR(CPM_NOP, 11, 3) = '%s'", $nop5);
	$where .= empty($nop6) ? '' : sprintf("AND SUBSTR(CPM_NOP, 14, 4) = '%s'", $nop6);
	$where .= empty($nop7) ? '' : sprintf("AND SUBSTR(CPM_NOP, 18, 1) = '%s'", $nop7);

	$query = "
	SELECT A.* FROM (
		" . sprintf(" SELECT 
		CPM_NOP, CPM_OP_ALAMAT, CPM_OP_RT, CPM_OP_RW, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, 
		CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN, CPM_WP_NAMA
		FROM cppmod_pbb_sppt_final 
		WHERE 
		SUBSTR(CPM_NOP,1,13)>= '%s' AND
		SUBSTR(CPM_NOP,1,13)<= '%s' {$where}
		UNION ", $blok_awal, $blok_akhir) .

		sprintf(" SELECT 
		CPM_NOP, CPM_OP_ALAMAT, CPM_OP_RT, CPM_OP_RW, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, 
		CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN, CPM_WP_NAMA
		FROM cppmod_pbb_sppt_susulan 
		WHERE 
		SUBSTR(CPM_NOP,1,13)>= '%s' AND
		SUBSTR(CPM_NOP,1,13)<= '%s' {$where}
		UNION ", $blok_awal, $blok_akhir) .

		sprintf(" SELECT 
		CPM_NOP, CPM_OP_ALAMAT, CPM_OP_RT, CPM_OP_RW, CPM_OT_ZONA_NILAI, CPM_OP_LUAS_TANAH, 
		CPM_OP_LUAS_BANGUNAN, CPM_NJOP_TANAH, CPM_NJOP_BANGUNAN, CPM_WP_NAMA
		FROM cppmod_pbb_sppt 
		WHERE 
		SUBSTR(CPM_NOP,1,13)>= '%s' AND
		SUBSTR(CPM_NOP,1,13)<= '%s' {$where} 
		", $blok_awal, $blok_akhir) . "
		
		) AS A
	ORDER BY A.CPM_NOP ASC";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	$i     = 0;
	while ($rows  = mysqli_fetch_assoc($res)) {

		$data[$i]['NOP'] 			= $rows['CPM_NOP'];
		$data[$i]['NAMA'] 			= $rows['CPM_WP_NAMA'];
		$data[$i]['ALAMAT'] 		= $rows['CPM_OP_ALAMAT'];
		$data[$i]['RT'] 			= $rows['CPM_OP_RT'];
		$data[$i]['RW'] 			= $rows['CPM_OP_RW'];
		$data[$i]['LUAS_TANAH'] 	= $rows['CPM_OP_LUAS_TANAH'];
		$data[$i]['LUAS_BANGUNAN'] 	= $rows['CPM_OP_LUAS_BANGUNAN'];
		$data[$i]['ZNT'] 			= $rows['CPM_OT_ZONA_NILAI'];
		$data[$i]['NJOP_TANAH']		= $rows['CPM_NJOP_TANAH'];
		$data[$i]['NJOP_BANGUNAN']	= $rows['CPM_NJOP_BANGUNAN'];

		$i++;
	}

	return $data;
}

/* inisiasi parameter */
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set properties
$objPHPExcel->getProperties()
	->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");

$objPHPExcel->getDefaultStyle()->getFont()
	->setName('Courier New');
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

function judul($row)
{
	global $objPHPExcel, $appConfig;

	$prop = $_POST['prop'];
	$kota = $_POST['kota'];
	$kec = $_POST['kec'];
	$kel = $_POST['kel'];

	$kd_prop = substr($_POST['kd_prop'], -2);
	$kd_kota = substr($_POST['kd_kota'], -2);
	$kd_kec = substr($_POST['kd_kec'], -3);
	$kd_kel = substr($_POST['kd_kel'], -3);

	$blok_awal = substr($_POST['blok_awal'], -3);
	$blok_akhir = substr($_POST['blok_akhir'], -3);

	$row6 = $row;
	$row7 = $row + 1;
	#6
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " LAPORAN DAFTAR RINGKAS OBJEK PAJAK ");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$row++; #7
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " URUT NOMOR OBJEK PAJAK ");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$row++; #8
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " ( Semua Objek Terdaftar ) ");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$row++; #9
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:E{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:H{$row}");
	$row++; #10
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:E{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " PROVINSI : {$kd_prop} - " . $prop);
	$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " " . strtoupper($appConfig["LABEL_KELURAHAN"]) . " : {$kd_kel} - " . $kel);

	$row++; #11
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:E{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " KOTA : {$kd_kota} - " . $kota);
	$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " BLOK AWAL : " . $blok_awal);

	$row++; #12
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:E{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " KECAMATAN : {$kd_kec} - " . $kec);
	$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " BLOK AKHIR : " . $blok_akhir);

	$objPHPExcel->getActiveSheet()->getStyle("A{$row6}:H{$row7}")->applyFromArray(
		array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row6}:H{$row}")->applyFromArray(
		array(
			'font' => array(
				'bold' => true
			)
		)
	);

	return $row;
}
function table_header($row)
{
	global $objPHPExcel;
	#12
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue("A{$row}", " NO ")
		->setCellValue("B{$row}", " NOMOR OBJEK\n PAJAK ")
		->setCellValue("C{$row}", " NAMA WAJIB PAJAK\n ALAMAT OBJEK PAJAK ")
		->setCellValue("D{$row}", " RT/\nRW ")
		->setCellValue("E{$row}", " KODE\nZNT ")
		->setCellValue("F{$row}", " LUAS BUMI\n LUAS BNG ")
		->setCellValue("G{$row}", " NJOP BUMI\n NJOP BNG ")
		->setCellValue("H{$row}", " JUMLAH NJOP");

	//style header
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->applyFromArray(
		array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true
			)
		)
	);

	return $row++;
}

function fetchData()
{
	global $objPHPExcel, $data, $row;

	$row1 = $row;
	for ($i = 0; $i < count($data); $i++) {
		if (!isset($data[$i])) break;

		$CPM_NOP = substr($data[$i]['NOP'], 10, 3) . "-" . substr($data[$i]['NOP'], 13, 4) . "." . substr($data[$i]['NOP'], 17, 1);

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($i + 1));
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($CPM_NOP . " "));
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($data[$i]['NAMA'] . "\n" . $data[$i]['ALAMAT']));
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($data[$i]['RT'] . "/" . $data[$i]['RW']));
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, (" " . $data[$i]['ZNT'] . " "));
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, ($data[$i]['LUAS_TANAH'] . "\n" . $data[$i]['LUAS_BANGUNAN']));

		$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ($data[$i]['NJOP_TANAH'] . "\n" . $data[$i]['NJOP_BANGUNAN']));
		$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ($data[$i]['NJOP_TANAH'] + $data[$i]['NJOP_BANGUNAN']));


		$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
		$row++;
	}

	$objPHPExcel->getActiveSheet()->getStyle("A{$row1}:B{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("C{$row1}:C{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("D{$row1}:E{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("F{$row1}:H{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);

	//border list data
	$objPHPExcel->getActiveSheet()->getStyle("A" . ($row1 - 1) . ":H" . ($row - 1))->applyFromArray(
		array('borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		))
	);

	return $row;
}

$data = getData();
$sumRows = count($data);
$row = 1;
$row = judul($row);
$row += 2;
$row = table_header($row);
$row++;
$row = fetchData();

#setup print
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 12);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:H' . ($row + 7));
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(73);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.75);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.26);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.75);
$objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
$objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
#end setup print

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('OP Ringkas');

#$objPHPExcel->getActiveSheet()->getStyle('A12:H13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
#$objPHPExcel->getActiveSheet()->getStyle('A12:H13')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Op_Ringkas_' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
