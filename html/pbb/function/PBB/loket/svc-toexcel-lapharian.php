<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel_oldver/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");


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
// ini_set('display_errors', 1);


$myDBlink = "";

$arrType = array(
	1 => "OP Baru",
	2 => "Pemecahan",
	3 => "Penggabungan",
	4 => "Mutasi",
	5 => "Perubahan Data",
	6 => "Pembatalan",
	7 => "Duplikat",
	8 => "Penghapusan",
	9 => "Pengurangan",
	10 => "Keberatan"
);

function headerMonitoringE2($mod, $nama)
{
	global $appConfig;
	$model = ($mod == 0) ? "KECAMATAN" : strtoupper($_REQUEST['LBL_KEL']);
	$dl = "";
	if ($mod == 0) {
		$dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"15\"><b>{$dl}<b></td></tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" span=\"2\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"48\" />
	  <col width=\"89\" />
	  <col width=\"56\" />
	  <tr>
		<td rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<td rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">KETETAPAN</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN LALU</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN INI</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI S/D BULAN  INI</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"137\" align=\"center\">SISA     KETETAPAN</td>
		<td rowspan=\"2\" width=\"56\" align=\"center\">SISA  %</td>
	  </tr>
	  <tr>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
	  </tr>
	";
	return $html;
}

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
		//echo mysqli_error($myDBLink);
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con)
{
	mysqli_close($con);
}

function convertDate($date, $delimiter = '-')
{
	if ($date == null || $date == '') return '';

	$tmp = explode($delimiter, $date);
	return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}

function getData($where = '')
{
	global $DBLink, $srcTglAwal, $srcTglAkhir, $srcJnsBerkas;

	$whereClause = array();
	$where = " ";

	if ($srcJnsBerkas != "") $whereClause[] = " CPM_TYPE = '" . $srcJnsBerkas . "' ";
	if ($srcTglAwal != "") $whereClause[] = " CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
	if ($srcTglAkhir != "") $whereClause[] = " CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";

	if ($whereClause) $where = " WHERE " . join('AND', $whereClause);

	$query = "SELECT
				A.*, B.CPC_TKC_KECAMATAN, C.CPC_TKL_KELURAHAN, IFNULL(D.CPM_WP_NO_HP, A.CPM_WP_HANDPHONE) AS CPM_WP_HANDPHONE
			FROM
				cppmod_pbb_services A
			JOIN cppmod_tax_kecamatan B ON A.CPM_OP_KECAMATAN=B.CPC_TKC_ID
			JOIN cppmod_tax_kelurahan C ON A.CPM_OP_KELURAHAN=C.CPC_TKL_ID 
			LEFT JOIN cppmod_pbb_wajib_pajak D ON A.CPM_WP_NO_KTP = D.CPM_WP_ID
            $where ORDER BY CPM_DATE_RECEIVE DESC ";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	return $res;
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

// print_r($_REQUEST); exit;

$srcTglAwal  	= $_REQUEST['srcTglAwal'];
$srcTglAkhir  	= $_REQUEST['srcTglAkhir'];
$srcJnsBerkas	= $_REQUEST['srcJnsBerkas'];

$result = getData();

$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.8);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.5);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(false);

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();

$objRichText->createText('LAPORAN HARIAN');
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
$objRichText = new PHPExcel_RichText();
// $objRichText->createText('TANGGAL');
$objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A2:J2');
$objPHPExcel->getActiveSheet()->getStyle('A1:J2')->applyFromArray(
	array(
		'font'    => array('bold' => true, 'size' => $fontSizeHeader),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
	)
);

$objPHPExcel->setActiveSheetIndex(0);


$objPHPExcel->getActiveSheet()->getStyle('A1:J2')->applyFromArray(
	array('font'    => array('size' => $fontSizeHeader))
);

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NOMOR PELAYANAN');
$objPHPExcel->getActiveSheet()->getCell('B3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B3:B4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DATA SPPT PBB');
$objPHPExcel->getActiveSheet()->getCell('C3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C3:H3');
$objPHPExcel->getActiveSheet()->setCellValue('C4', 'NOP');
$objPHPExcel->getActiveSheet()->setCellValue('D4', 'NAMA');
$objPHPExcel->getActiveSheet()->setCellValue('E4', 'TELP');
$objPHPExcel->getActiveSheet()->setCellValue('F4', 'ALAMAT OP');
$objPHPExcel->getActiveSheet()->setCellValue('G4', 'KELURAHAN');
$objPHPExcel->getActiveSheet()->setCellValue('H4', 'KECAMATAN');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL MASUK');
$objPHPExcel->getActiveSheet()->getCell('I3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I3:I4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('JENIS BERKAS');
$objPHPExcel->getActiveSheet()->getCell('J3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J3:J4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENERIMA');
$objPHPExcel->getActiveSheet()->getCell('K3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('K3:K4');

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A3:K4')->applyFromArray(
	array(
		'font'    => array(
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

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);

//$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
//$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

while ($row = mysqli_fetch_assoc($result)) {
	$objPHPExcel->getActiveSheet()->getRowDimension(4 + $no)->setRowHeight(18);
	$objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), $no);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $no), $row['CPM_ID']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $no), " " . $row['CPM_OP_NUMBER']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $row['CPM_WP_NAME']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $no), $row['CPM_WP_HANDPHONE']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $no), $row['CPM_OP_ADDRESS']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . (4 + $no), $row['CPC_TKL_KELURAHAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . (4 + $no), $row['CPC_TKC_KECAMATAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . (4 + $no), convertDate($row['CPM_DATE_RECEIVE']));
	$objPHPExcel->getActiveSheet()->setCellValue('J' . (4 + $no), $arrType[$row['CPM_TYPE']]);
	$objPHPExcel->getActiveSheet()->setCellValue('K'. (4 + $no), $row['CPM_RECEIVER']);
	$no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A3:K' . (3 + $no))->applyFromArray(
	array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
//$objPHPExcel->getActiveSheet()->getStyle('A10:A'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('C10:F'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('G10:G'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('H10:K'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('L10:L'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('M10:N'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('O10:O'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="laporan_harian.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
// exit;
