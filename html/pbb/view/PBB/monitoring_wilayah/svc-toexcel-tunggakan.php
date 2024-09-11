<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

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

//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 1);


$myDBlink = "";


function headerMonitoringTunggakan($mod, $nama)
{
	global $appConfig;
	$model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod == 0) {
		$dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"4\"><b>{$dl}<b></td></tr>
	  <tr>
		<td width=\"28\" align=\"center\">NO</td>
		<td width=\"200\" align=\"center\">{$model}</td>
		<td width=\"136\" align=\"center\">JUMLAH OP MENUNGGAK</td>
		<td width=\"200\" align=\"center\">NILAI TUNGGAKAN</td>
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

function getKecamatan($p)
{
	global $DBLink;
	$return = array();
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='" . $p . "' ORDER BY CPC_TKC_URUTAN ASC";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$data = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKC_ID"];
		$data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
		$i++;
	}
	/* $data = array();
	$pgDBlink = openPostgres();

	$result = pg_query($pgDBlink,"SELECT kode_kecamatan, nama_kecamatan FROM ".DBTABLEKECAMATAN." WHERE id_kota='97' ORDER BY nama_kecamatan");

	if ($result===false) {
		echo pg_result_error($result);
	  exit;
	}
	$i=0;
	while ($row = pg_fetch_assoc($result)) {
		$data[$i]["id"] = $row["kode_kecamatan"];
		$data[$i]["name"] = $row["nama_kecamatan"];
		$i++;
	}
	closePostgres($pgDBlink); */

	return $data;
}

function getKelurahan($p)
{
	global $DBLink;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID ='" . $p . "' ORDER BY CPC_TKL_URUTAN ASC";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		$result['msg'] = mysqli_error($DBLink);
		echo $json->encode($result);
		exit();
	}
	$data = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}

	/* $pgDBlink = openPostgres();
	$data = array();
	$dbresult = pg_query($pgDBlink,"SELECT id_kelurahan,kode_kelurahan, nama_kelurahan FROM ".DBTABLEKELURAHAN." WHERE kode_kelurahan like '{$p}%' ORDER BY nama_kelurahan");

	if ($dbresult ===false) {
		echo pg_result_error($dbresult );
	  exit;
	}
	$i=0;
	while ($row = pg_fetch_assoc($dbresult )) {
		$data[$i]["id"] = $row["kode_kelurahan"];
		$data[$i]["name"] = $row["nama_kelurahan"];
		$i++;
	}
	closePostgres($pgDBlink); */
	return $data;
}

//get tanggal akhir pada bulan
function lastDay($month = '', $year = '')
{
	if (empty($month)) {
		$month = date('m');
	}
	if (empty($year)) {
		$year = date('Y');
	}
	$result = strtotime("{$year}-{$month}-01");
	$result = strtotime('-1 second', strtotime('+1 month', $result));
	return date('Y-m-d', $result) . ' 23:59:59';
}

//get tanggal awal pada bulan
function firstDay($month = '', $year = '')
{
	if (empty($month)) {
		$month = date('m');
	}
	if (empty($year)) {
		$year = date('Y');
	}
	$result = strtotime("{$year}-{$month}-01");
	return date('Y-m-d', $result) . ' 00:00:00';
}


function getTunggakan($mod)
{
	global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s;
	if ($mod == 0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);

	$tahun = "";
	if ($thn != "") {
		$tahun = "and sppt_tahun_pajak='{$thn}'";
	}

	$c = count($kec);
	$data = array();
	for ($i = 0; $i < $c; $i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '" . $kec[$i]["id"] . "%' " . $tahun . " AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL)";
		$da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}

	return $data;
}

function showTable($mod = 0, $nama = "")
{
	global $eperiode;
	$dt = getTunggakan($mod);

	$c = count($dt);
	$a = 1;
	$data = array();
	$summary = array('name' => 'JUMLAH', 'wp' => 0, 'rp' => 0);

	$jmlWP = 0;
	$jmlTunggakan = 0;
	for ($i = 0; $i < $c; $i++) {
		$jmlWP += $dt[$i]["WP"];
		$jmlTunggakan += $dt[$i]["RP"];
		$wp = number_format($dt[$i]["WP"], 0, ",", ".");
		$rp = number_format($dt[$i]["RP"], 0, ",", ".");
		$tmp = array(
			"name" => $dt[$i]["name"],
			"wp" => $wp,
			"rp" => $rp
		);
		$data[] = $tmp;
		$summary['wp'] += $dt[$i]["WP"];
		$summary['rp'] += $dt[$i]["RP"];
	}
	$summary['wp'] = number_format($summary['wp'], 0, ",", ".");
	$summary['rp'] = number_format($summary['rp'], 0, ",", ".");
	$data[] = $summary;

	return $data;
}

function getData($where)
{
	global $myDBLink, $kd, $thn, $bulan;

	$myDBLink = openMysql();
	$return = array();
	$return["RP"] = 0;
	$return["WP"] = 0;
	$whr = "";
	if ($where) {
		$whr = " where {$where}";
	}
	$query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) as RP FROM PBB_SPPT {$whr}"; //echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["RP"] = ($row["RP"] != "") ? $row["RP"] : 0;
		$return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
	}
	closeMysql($myDBLink);
	return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kab  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";

$arrWhere = array();


$where = implode(" AND ", $arrWhere);

if ($kecamatan == "") {
	$data = showTable();
} else {
	$data = showTable(1, $nama);
}


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
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(true);

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();

$objRichText->createText('REKAP TUNGGAKAN PBB TAHUN ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->getStyle('C1:L3')->applyFromArray(
	array('font'    => array('size' => $fontSizeHeader))
);
if ($kecamatan == '') {
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText($appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA']);
	$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
	$objPHPExcel->getActiveSheet()->getStyle('A3:B3')->applyFromArray(
		array(
			'font'    => array('italic' => true, 'size' => $fontSizeHeader),
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
		)
	);
} else {
	$objRichText = new PHPExcel_RichText();
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('KECAMATAN : ' . $nama);
	$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
	$objPHPExcel->getActiveSheet()->getStyle('A3:B3')->applyFromArray(
		array(
			'font'    => array('italic' => true, 'size' => $fontSizeHeader),
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
		)
	);
}



// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();

if ($kecamatan == "") {
	$objRichText->createText('KECAMATAN');
} else {
	$objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
}

$objPHPExcel->getActiveSheet()->getCell('B5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->setCellValue('C5', 'JUMLAH OP MENUNGGAK');
$objPHPExcel->getActiveSheet()->setCellValue('D5', 'NILAI TUNGGAKAN (RP)');

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->applyFromArray(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:D50')->applyFromArray(
	array(
		'alignment' => array(
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

foreach ($data as $buffer) {
	$objPHPExcel->getActiveSheet()->getRowDimension(5 + $no)->setRowHeight(18);
	$objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $no), $no);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $no), $buffer['name']);
	if ($buffer['name'] == 'JUMLAH') {
		$objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $no), $buffer['wp']);
		$objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $no), $buffer['rp']);
	} else {
		$objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $no), $buffer['wp'])->getStyle('C' . (5 + $no))->applyFromArray($noBold);
		$objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $no), $buffer['rp'])->getStyle('D' . (5 + $no))->applyFromArray($noBold);
	}
	$no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A6:D' . (5 + count($data)))->applyFromArray(
	array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
$objPHPExcel->getActiveSheet()->getStyle('A6:A' . (5 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('C6:D' . (5 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['NAMA_KOTA_PENGESAHAN'].', '.strtoupper($bulan[date('m')-1]).' '.$thn);
//$objPHPExcel->getActiveSheet()->getCell('I'.(11+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(11+count($data)).':K'.(11+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['PEJABAT_SK2']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(12+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(12+count($data)).':K'.(12+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(13+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(13+count($data)).':K'.(13+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['NAMA_PEJABAT_SK2']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(17+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(17+count($data)).':K'.(17+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['NAMA_PEJABAT_SK2_JABATAN']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(15+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(15+count($data)).':K'.(15+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('NIP. '.$appConfig['NAMA_PEJABAT_SK2_NIP']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(19+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(19+count($data)).':K'.(19+count($data)));
//
//$objPHPExcel->getActiveSheet()->getStyle('I'.(17+count($data)).':K'.(17+count($data)));
//$objPHPExcel->getActiveSheet()->getStyle('I'.(11+count($data)).':K'.(19+count($data)))->applyFromArray(
//    array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
//);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="rekaptunggakan.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
