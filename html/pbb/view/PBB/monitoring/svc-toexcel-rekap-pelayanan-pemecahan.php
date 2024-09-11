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
	global $DBLink, $appConfig, $queryDate, $jnsBerkas, $thn, $kec, $kel;

	$where = '';
	if ($kec != "") {
		if ($kel != "") $where .= "AND A.CPM_OP_NUMBER like '{$kel}%'";
		else $where .= "AND A.CPM_OP_NUMBER like '{$kec}%'";
	}

	if ($thn == $appConfig['tahun_tagihan']) {
		$tableCetak = "cppmod_pbb_sppt_current";
	} else {
		$tableCetak = "cppmod_pbb_sppt_cetak_" . $thn;
	}

	$query = "SELECT
					CPM_DATE_RECEIVE,
					CPM_ID,
					CPM_OP_KECAMATAN,
					C.CPC_TKC_KECAMATAN,
					CPM_OP_KELURAHAN,
					D.CPC_TKL_KELURAHAN,
					CPM_OP_NUMBER AS NOP_INDUK,
					CPM_SPPT_YEAR,
					E.SPPT_TANGGAL_CETAK
				FROM
					cppmod_pbb_services A
				JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
				LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID
				LEFT JOIN {$tableCetak} E ON B.CPM_SP_NOP = E.NOP AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS = '4' {$queryDate}
					{$where} 
				GROUP BY
					CPM_OP_NUMBER
				ORDER BY 
					CPM_OP_NUMBER,CPM_ID";

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

function getListNOPPecahan($nop)
{
	global $DBLink, $appConfig, $thn, $jnsBerkas, $thn, $jnsBerkas;

	if ($thn == $appConfig['tahun_tagihan']) {
		$tableCetak = "cppmod_pbb_sppt_current";
	} else {
		$tableCetak = "cppmod_pbb_sppt_cetak_" . $thn;
	}

	$query = "SELECT
					A.CPM_DATE_RECEIVE AS TANGGAL,
					A.CPM_ID AS ID,
					E.NOP,
					E.WP_NAMA AS NAMA,
					E.OP_ALAMAT AS ALAMAT,
					E.OP_LUAS_BUMI AS LT,
					E.OP_LUAS_BANGUNAN AS LB,
					E.SPPT_PBB_HARUS_DIBAYAR AS TAGIHAN
				FROM
					cppmod_pbb_services A
				JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
				JOIN {$tableCetak} E ON B.CPM_SP_NOP = E.NOP
				AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS = '4'
					AND CPM_OP_NUMBER = '{$nop}'
				ORDER BY 
					CPM_OP_NUMBER,CPM_ID ";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	while ($rows  = mysqli_fetch_assoc($res)) {
		$data[]	= $rows;
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

$jnsBerkas 	= $_POST['jnsBerkas'];
$thn 		= $_POST['thn'];
$kec 		= $_POST['kecamatan'];
$kel		= $_POST['kelurahan'];


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
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " REKAP PELAYANAN PEMECAHAN ");
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
		->setCellValue("B{$row}", " NOP INDUK ")
		->setCellValue("C{$row}", " KECAMATAN ")
		->setCellValue("D{$row}", " KELURAHAN ")
		->setCellValue("E{$row}", " TANGGAL REGISTRASI ")
		->setCellValue("F{$row}", " NO PELAYANAN ")
		->setCellValue("G{$row}", " DATA HASIL PEMECAHAN ")
		->setCellValue("G" . ($row + 1), " NOP BARU ")
		->setCellValue("H" . ($row + 1), " NAMA ")
		->setCellValue("I" . ($row + 1), " ALAMAT ")
		->setCellValue("J" . ($row + 1), " LUAS TANAH ")
		->setCellValue("K" . ($row + 1), " LUAS BANGUNAN ")
		->setCellValue("L{$row}", " PBB TERHUTANG")
		->setCellValue("M{$row}", " TANGGAL CETAK");

	$objPHPExcel->getActiveSheet()->mergeCells("A" . $row . ":A" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("B" . $row . ":B" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("C" . $row . ":C" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("D" . $row . ":D" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("E" . $row . ":E" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("F" . $row . ":F" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("G" . $row . ":K" . ($row));
	$objPHPExcel->getActiveSheet()->mergeCells("L" . $row . ":L" . ($row + 1));
	$objPHPExcel->getActiveSheet()->mergeCells("M" . $row . ":M" . ($row + 1));



	//style header
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:M" . ($row + 1))->applyFromArray(
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

		$listPecah 	 = getListNOPPecahan($data[$i]['NOP_INDUK']);
		if (!$listPecah) {
			$listPecah = getDT($data[$i]['NOP_INDUK']);
		}
		$listId 	 = "";
		$listTgl 	 = "";
		$listNOP 	 = "";
		$listNama 	 = "";
		$listAlamat	 = "";
		$listLT		 = "";
		$listLB		 = "";
		$listTagihan = "";
		// print_r($listPecah); exit;
		// // echo count($listPecah); exit;
		if (count($listPecah) > 0) {
			$j = 1;
			foreach ($listPecah as $val) {
				$listId 		.= "{$val['ID']}\n";
				$listTgl 		.= "{$val['TANGGAL']}\n";
				$listNOP 		.= "{$j}. {$val['NOP']}\n";
				$listNama 		.= "{$j}. {$val['NAMA']}\n";
				$listAlamat 	.= "{$j}. {$val['ALAMAT']}\n";
				$listLT 		.= "{$j}. {$val['LT']}\n";
				$listLB 		.= "{$j}. {$val['LB']}\n";
				$listTagihan	.= "{$j}. {$val['TAGIHAN']}\n";
				$j++;
			}
		}

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($i + 1));
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, " " . $data[$i]['NOP_INDUK']);
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $data[$i]['CPC_TKC_KECAMATAN']);
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $data[$i]['CPC_TKL_KELURAHAN']);
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $listTgl);
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $listId);
		$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $listNOP);
		$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $listNama);
		$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $listAlamat);
		$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $listLT);
		$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $listLB);
		$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $listTagihan);
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $data[$i]['SPPT_TANGGAL_CETAK']);

		$objPHPExcel->getActiveSheet()->getStyle('E' . $row . ':M' . $row)->getAlignment()->setWrapText(true);
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
	$objPHPExcel->getActiveSheet()->getStyle("D{$row1}:D{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("E{$row1}:E{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("F{$row1}:F{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("G{$row1}:J{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("K{$row1}:K{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("L{$row1}:L{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("M{$row1}:N{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);

	//border list data
	$objPHPExcel->getActiveSheet()->getStyle("A" . ($row1 - 2) . ":M" . ($row - 1))->applyFromArray(
		array('borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		))
	);

	return $row;
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
	global $DBLink, $jnsBerkas;

	$query = "SELECT
				A.CPM_DATE_RECEIVE AS TANGGAL,
				A.CPM_ID AS ID,
				E.CPM_NOP AS NOP,
				E.CPM_WP_NAMA AS NAMA,
				E.CPM_OP_ALAMAT AS ALAMAT,
				E.CPM_OP_LUAS_TANAH AS LT,
				E.CPM_OP_LUAS_BANGUNAN AS LB,
				0 TAGIHAN
			FROM
				cppmod_pbb_services A
			JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
			JOIN cppmod_pbb_sppt_final E ON B.CPM_SP_NOP = E.CPM_NOP
			WHERE
				CPM_TYPE = '{$jnsBerkas}'
			AND CPM_STATUS >= '1'
			AND CPM_OP_NUMBER = '{$nop}'
			ORDER BY
				CPM_OP_NUMBER,
				CPM_ID ";

	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while ($row = mysqli_fetch_assoc($res)) {
		$dt[] = $row;
	}

	return $dt;
}

function getSusulan($nop)
{
	global $DBLink, $jnsBerkas;

	$query = "SELECT
				A.CPM_DATE_RECEIVE AS TANGGAL,
				A.CPM_ID AS ID,
				E.CPM_NOP AS NOP,
				E.CPM_WP_NAMA AS NAMA,
				E.CPM_OP_ALAMAT AS ALAMAT,
				E.CPM_OP_LUAS_TANAH AS LT,
				E.CPM_OP_LUAS_BANGUNAN AS LB,
				0 TAGIHAN
			FROM
				cppmod_pbb_services A
			JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
			JOIN cppmod_pbb_sppt_susulan E ON B.CPM_SP_NOP = E.CPM_NOP
			WHERE
				CPM_TYPE = '{$jnsBerkas}'
			AND CPM_STATUS >= '1'
			AND CPM_OP_NUMBER = '{$nop}'
			ORDER BY
				CPM_OP_NUMBER,
				CPM_ID ";

	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while ($row = mysqli_fetch_assoc($res)) {
		$dt[] = $row;
	}

	return $dt;
}

function getPendataan($nop)
{
	global $DBLink, $jnsBerkas;

	$query = "SELECT
				A.CPM_DATE_RECEIVE AS TANGGAL,
				A.CPM_ID AS ID,
				E.CPM_NOP AS NOP,
				E.CPM_WP_NAMA AS NAMA,
				E.CPM_OP_ALAMAT AS ALAMAT,
				E.CPM_OP_LUAS_TANAH AS LT,
				E.CPM_OP_LUAS_BANGUNAN AS LB,
				0 TAGIHAN
			FROM
				cppmod_pbb_services A
			JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
			JOIN cppmod_pbb_sppt E ON B.CPM_SP_NOP = E.CPM_NOP
			WHERE
				CPM_TYPE = '{$jnsBerkas}'
			AND CPM_STATUS >= '1'
			AND CPM_OP_NUMBER = '{$nop}'
			ORDER BY
				CPM_OP_NUMBER,
				CPM_ID ";

	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while ($row = mysqli_fetch_assoc($res)) {
		$dt[] = $row;
	}

	return $dt;
}

$data = getData();
// echo "<pre>";
// print_r($data); exit;
$sumRows = count($data);
$row = 1;
$row = judul($row);
$row += 2;
$row = table_header($row);
$row += 2;
$row = fetchData();

#setup print
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 12);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:R' . ($row + 7));
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
$objPHPExcel->getActiveSheet()->setTitle('Rekap Pemecahan');

#$objPHPExcel->getActiveSheet()->getStyle('A12:H13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
#$objPHPExcel->getActiveSheet()->getStyle('A12:H13')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
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

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekap_Pemecahan_' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
