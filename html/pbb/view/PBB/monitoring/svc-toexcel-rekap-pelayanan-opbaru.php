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
	global $DBLink, $appConfig, $queryDate;

	$jnsBerkas 	= $_POST['jnsBerkas'];
	$thn 		= $_POST['thn'];
	$kec 		= $_POST['kecamatan'];
	$kel		= $_POST['kelurahan'];

	if ($thn == $appConfig['tahun_tagihan']) {
		$tableCetak = "cppmod_pbb_sppt_current";
	} else {
		$tableCetak = "cppmod_pbb_sppt_cetak_" . $thn;
	}

	$where = '';
	if ($kec != "") {
		if ($kel != "" && $kel != "null") $where .= "AND A.CPM_OP_KELURAHAN = '$kel'";
		else $where .= "AND A.CPM_OP_KECAMATAN = '$kec'";
	}

	$query = "SELECT
					CPM_DATE_RECEIVE,
					CPM_ID,
					CPM_OP_KECAMATAN,
					C.CPC_TKC_KECAMATAN,
					CPM_OP_KELURAHAN,
					D.CPC_TKL_KELURAHAN,
					B.CPM_NEW_NOP,
					CPM_WP_NAME,
					CPM_WP_ADDRESS,
					CPM_OP_ADDRESS,
					CPM_SPPT_YEAR,
					E.OP_LUAS_BUMI,
					E.OP_LUAS_BANGUNAN,
					E.SPPT_PBB_HARUS_DIBAYAR,
					E.SPPT_TANGGAL_CETAK
				FROM
					cppmod_pbb_services A
				JOIN cppmod_pbb_service_new_op B ON A.CPM_ID = B.CPM_NEW_SID
				LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID
				LEFT JOIN {$tableCetak} E ON B.CPM_NEW_NOP = E.NOP AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS = '4' {$queryDate}
					{$where}
				ORDER BY 
					CPM_ID";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	$i     = 0;
	while ($rows  = mysqli_fetch_assoc($res)) {

		$data[]	= $rows;
		$i++;
	}

	return $data;
}

function getDT($nop)
{
	$data = getFinal($nop);
	if (empty($data)) {
		$data = getSusulan($nop);
		if (empty($data)) {
			$data = getPendataan($nop);
		}
	}
	return $data;
}

function getFinal($nop)
{
	global $DBLink;

	$query = "SELECT
				CPM_OP_LUAS_TANAH,
				CPM_OP_LUAS_BANGUNAN
			FROM
				cppmod_pbb_sppt_final
			WHERE CPM_NOP = '{$nop}' ";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		mysqli_error($DBLink);
		exit;
	}
	$row = mysqli_fetch_object($res);

	return $row;
}

function getSusulan($nop)
{
	global $DBLink;

	$query = "SELECT
				CPM_OP_LUAS_TANAH,
				CPM_OP_LUAS_BANGUNAN
			FROM
				cppmod_pbb_sppt_susulan
			WHERE CPM_NOP = '{$nop}' ";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		mysqli_error($DBLink);
		exit;
	}
	$row = mysqli_fetch_object($res);

	return $row;
}

function getPendataan($nop)
{
	global $DBLink;

	$query = "SELECT
				CPM_OP_LUAS_TANAH,
				CPM_OP_LUAS_BANGUNAN
			FROM
				cppmod_pbb_sppt
			WHERE CPM_NOP = '{$nop}' ";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		mysqli_error($DBLink);
		exit;
	}
	$row = mysqli_fetch_object($res);

	return $row;
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

$filterFromDate = isset($_REQUEST['fromDate']) && $_REQUEST['fromDate'] ? $_REQUEST['fromDate'] : '';
$filterToDate = isset($_REQUEST['toDate']) && $_REQUEST['toDate'] ? $_REQUEST['toDate'] : '';

$filterDate = array(
	mysqli_escape_string($DBLink, $filterFromDate), 
	mysqli_escape_string($DBLink, $filterToDate)
);

list($fromDate, $toDate) = $filterDate;
	
$queryDate = "";
if ($fromDate || $toDate) {
	$queryDate = array();
	$queryDate[] = $fromDate ? "A.CPM_DATE_RECEIVE >= '{$fromDate}'" : false;
	$queryDate[] = $toDate ? "A.CPM_DATE_RECEIVE <= '{$toDate}'" : false;

	$queryDate = array_filter($queryDate, function($value) { return $value; });
	$queryDate = 'AND ('. implode(' AND ', $queryDate) .')';
}

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

	$jnsBerkas 	= $_POST['jnsBerkas'];
	$thn 		= $_POST['thn'];

	$row6 = $row;
	$row7 = $row + 1;
	#6
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:M{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " REKAP PELAYANAN OP BARU ");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$row++; #7

	$objPHPExcel->getActiveSheet()->getStyle("A{$row6}:M{$row7}")->applyFromArray(
		array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row6}:M{$row}")->applyFromArray(
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
		->setCellValue("B{$row}", " TANGGAL REGISTRASI ")
		->setCellValue("C{$row}", " NO PELAYANAN ")
		->setCellValue("D{$row}", " KECAMATAN ")
		->setCellValue("E{$row}", " KELURAHAN ")
		->setCellValue("F{$row}", " NOP HASIL ")
		->setCellValue("G{$row}", " NAMA ")
		->setCellValue("H{$row}", " ALAMAT WP")
		->setCellValue("I{$row}", " ALAMAT OP")
		->setCellValue("J{$row}", " LUAS TANAH")
		->setCellValue("K{$row}", " LUAS BANGUNAN")
		->setCellValue("L{$row}", " PBB TERHUTANG")
		->setCellValue("M{$row}", " TANGGAL CETAK");

	//style header
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:M{$row}")->applyFromArray(
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

		$dt = getDT($data[$i]['CPM_NEW_NOP']);
		// die(var_dump($dt->CPM_OP_LUAS_TANAH, $data[$i]));
		$lt = ($data[$i]['OP_LUAS_BUMI'] != 0 || $data[$i]['OP_LUAS_BUMI'] != null ? $data[$i]['OP_LUAS_BUMI'] : (!empty($dt) ? $dt->CPM_OP_LUAS_TANAH : 0));
		$lb = ($data[$i]['OP_LUAS_BANGUNAN'] != 0 || $data[$i]['OP_LUAS_BANGUNAN'] != null ? $data[$i]['OP_LUAS_BANGUNAN'] : (!empty($dt) ? $dt->CPM_OP_LUAS_BANGUNAN : 0));

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($i + 1));
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($data[$i]['CPM_DATE_RECEIVE']));
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($data[$i]['CPM_ID']));
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($data[$i]['CPC_TKC_KECAMATAN']));
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, ($data[$i]['CPC_TKL_KELURAHAN']));
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, (" " . $data[$i]['CPM_NEW_NOP']));
		$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ($data[$i]['CPM_WP_NAME']));
		$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ($data[$i]['CPM_WP_ADDRESS']));
		$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ($data[$i]['CPM_OP_ADDRESS']));
		$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($lt));
		$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, ($lb));
		$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($data[$i]['SPPT_PBB_HARUS_DIBAYAR']));
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, ($data[$i]['SPPT_TANGGAL_CETAK']));


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
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("F{$row1}:F{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("G{$row1}:I{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("J{$row1}:L{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("M{$row1}:M{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);

	//border list data
	$objPHPExcel->getActiveSheet()->getStyle("A" . ($row1 - 1) . ":M" . ($row - 1))->applyFromArray(
		array('borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		))
	);

	return $row;
}

$data = getData();
// echo "<pre>";
// print_r($data); exit;
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
$objPHPExcel->getActiveSheet()->setTitle('Rekap OP Baru');

#$objPHPExcel->getActiveSheet()->getStyle('A12:H13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
#$objPHPExcel->getActiveSheet()->getStyle('A12:H13')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekap_Op_Baru_' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
