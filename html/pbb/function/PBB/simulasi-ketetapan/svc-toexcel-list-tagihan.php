<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBLink = "";

// koneksi mysql
function headerListTagihan()
{
	global $appConfig;

	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" width=\"100%\">
	  <tr>
		<th width=\"80\" align=\"center\">TAHUN</td>
		<th width=\"117\" align=\"center\">NOP</td>
		<th width=\"136\" align=\"center\">NAMA</td>
		<th width=\"136\" align=\"center\">ALAMAT</td>
		<th width=\"100\" align=\"center\">LUAS BUMI</td>
		<th width=\"100\" align=\"center\">LUAS BANGUNAN</td>
		<th width=\"100\" align=\"center\">NJOP BUMI</td>
		<th width=\"100\" align=\"center\">NJOP BANGUNAN</td>
		<th width=\"100\" align=\"center\">TOTAL NJOP</td>
		<th width=\"100\" align=\"center\">POKOK</td>
		<th width=\"100\" align=\"center\">DENDA</td>
		<th width=\"100\" align=\"center\">TOTAL</td>
		<th width=\"100\" align=\"center\">STATUS</td>
		<th width=\"100\" align=\"center\">TANGGAL BAYAR</td>
	  </tr>
	";
	return $html;
}

// koneksi mysql
function openMysql()
{
	global $appConfig;
	$host 	= $appConfig['GW_DBHOST'];
	$port 	= isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
	$user 	= $appConfig['GW_DBUSER'];
	$pass 	= $appConfig['GW_DBPWD'];
	$dbname = $appConfig['GW_DBNAME'];
	$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		echo mysqli_error($myDBLink);
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con)
{
	mysqli_close($con);
}

function showTable()
{
	global $nop;

	$dt 		= getListTagihan($nop);
	// print_r($dt);
	$c 			= count($dt);
	$a = 1;
	$data = array();
	$summary = array('name' => 'TOTAL', 'SUM_POKOK' => 0, 'SUM_DENDA' => 0, 'GRAND_TOTAL' => 0);
	for ($i = 0; $i < $c; $i++) {

		$tmp = array(
			"TAHUN" 			=> $dt[$i]['TAHUN'],
			"NOP" 				=> $dt[$i]['NOP'],
			"NAMA" 				=> $dt[$i]['NAMA'],
			"ALAMAT" 			=> $dt[$i]['ALAMAT'],
			"LUAS_BUMI" 		=> $dt[$i]['LUAS_BUMI'],
			"LUAS_BANGUNAN" 	=> $dt[$i]['LUAS_BANGUNAN'],
			"NJOP_BUMI" 		=> $dt[$i]['NJOP_BUMI'],
			"NJOP_BANGUNAN" 	=> $dt[$i]['NJOP_BANGUNAN'],
			"NJOP" 				=> $dt[$i]['NJOP'],
			"POKOK" 			=> $dt[$i]['POKOK'],
			"DENDA" 			=> $dt[$i]['DENDA'],
			"TOTAL" 			=> $dt[$i]['TOTAL'],
			"STATUS" 			=> $dt[$i]['STATUS'],
			"TGL_BAYAR" 		=> $dt[$i]['TGL_BAYAR']
		);
		$data[] = $tmp;

		$a++;
	}
	return $data;
}

function getListTagihan($nop)
{
	global $myDBLink, $appConfig;

	$myDBLink = openMysql();
	$return = array();
	$query = "SELECT 
				NOP,
				SPPT_TAHUN_PAJAK AS TAHUN,
				WP_NAMA AS NAMA,
				WP_ALAMAT AS ALAMAT,
				SPPT_PBB_HARUS_DIBAYAR AS POKOK,
				PBB_DENDA AS DENDA,
				PBB_TOTAL_BAYAR AS TOTAL,
				PAYMENT_FLAG AS STATUS,
				PAYMENT_PAID AS TGL_BAYAR,
				OP_LUAS_BUMI AS LUAS_BUMI, 
				OP_LUAS_BANGUNAN AS LUAS_BANGUNAN, 
				OP_NJOP_BUMI AS NJOP_BUMI, 
				OP_NJOP_BANGUNAN AS NJOP_BANGUNAN, 
				OP_NJOP AS NJOP
			FROM
				PBB_SPPT
			WHERE
				NOP = '" . $nop . "' ORDER BY SPPT_TAHUN_PAJAK DESC ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		// print_r($row);
		$return[$i]["NOP"]				= ($row["NOP"] != "") ? $row["NOP"] : "";
		$return[$i]["TAHUN"]			= ($row["TAHUN"] != "") ? $row["TAHUN"] : "";
		$return[$i]["NAMA"]				= ($row["NAMA"] != "") ? $row["NAMA"] : "";
		$return[$i]["ALAMAT"]			= ($row["ALAMAT"] != "") ? $row["ALAMAT"] : "";
		$return[$i]["LUAS_BUMI"]		= ($row["LUAS_BUMI"] != "") ? $row["LUAS_BUMI"] : 0;
		$return[$i]["LUAS_BANGUNAN"]	= ($row["LUAS_BANGUNAN"] != "") ? $row["LUAS_BANGUNAN"] : 0;
		$return[$i]["NJOP_BUMI"]		= ($row["NJOP_BUMI"] != "") ? $row["NJOP_BUMI"] : 0;
		$return[$i]["NJOP_BANGUNAN"]	= ($row["NJOP_BANGUNAN"] != "") ? $row["NJOP_BANGUNAN"] : 0;
		$return[$i]["NJOP"]				= ($row["NJOP"] != "") ? $row["NJOP"] : 0;
		$return[$i]["POKOK"]			= ($row["POKOK"] != "") ? $row["POKOK"] : 0;
		$return[$i]["DENDA"]			= ($row["DENDA"] != "") ? $row["DENDA"] : 0;
		$return[$i]["TOTAL"]			= ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
		$return[$i]["STATUS"]			= ($row["STATUS"] != "") ? $row["STATUS"] : 0;
		$return[$i]["TGL_BAYAR"]		= ($row["TGL_BAYAR"] != "") ? $row["TGL_BAYAR"] : 0;
		$i++;
	}
	closeMysql($myDBLink);
	return $return;
}

$nop 				= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$a 					= @isset($_REQUEST['app']) ? $_REQUEST['app'] : "";
$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);

// print_r($appConfig); exit;

$data = showTable();
// echo "<pre>";
// print_r($data);exit;
$sumRows = count($data);
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");
//COP
$objPHPExcel->getActiveSheet()->mergeCells('A2:I2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:I3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' LIST TAGIHAN ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A5', " TAHUN ")
	->setCellValue('B5', " NOP ")
	->setCellValue('C5', " NAMA ")
	->setCellValue('D5', " ALAMAT ")
	->setCellValue('E5', " LUAS BUMI ")
	->setCellValue('F5', " LUAS BANGUNAN ")
	->setCellValue('G5', " NJOP BUMI ")
	->setCellValue('H5', " NJOP BANGUNAN ")
	->setCellValue('I5', " TOTAL NJOP ")
	->setCellValue('J5', " POKOK ")
	->setCellValue('K5', " DENDA ")
	->setCellValue('L5', " TOTAL ")
	->setCellValue('M5', " STATUS ")
	->setCellValue('N5', " TANGGAL BAYAR ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$summary = array('SUM_POKOK' => 0, 'SUM_DENDA' => 0, 'GRAND_TOTAL' => 0);
for ($i = 0; $i < $sumRows; $i++) {

	$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[$i]['TAHUN']);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[$i]['NOP'] . " ");
	$objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[$i]['NAMA']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[$i]['ALAMAT']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[$i]['LUAS_BUMI']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $data[$i]['LUAS_BANGUNAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $data[$i]['NJOP_BUMI']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $data[$i]['NJOP_BANGUNAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[$i]['NJOP']);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[$i]['POKOK']);
	$objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[$i]['DENDA']);
	$objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[$i]['TOTAL']);
	$objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), ($data[$i]['STATUS'] == 1 ? "LUNAS" : "BELUM BAYAR"));
	$objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $data[$i]['TGL_BAYAR']);
	$row++;

	$summary['SUM_POKOK'] 	+= $data[$i]['POKOK'];
	$summary['SUM_DENDA'] 	+= $data[$i]['DENDA'];
	$summary['GRAND_TOTAL'] += $data[$i]['TOTAL'];
}

// JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':I' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $summary['SUM_POKOK']);
$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $summary['SUM_DENDA']);
$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $summary['GRAND_TOTAL']);


$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(
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

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('list Tagihan');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:N5')->applyFromArray(
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

//border header table
$objPHPExcel->getActiveSheet()->getStyle('A5:N' . ($sumRows + 6))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A5:N5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:N5')->getFill()->getStartColor()->setRGB('E4E4E4');

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

// Redirect output to a clients web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="List Tagihan ' . $nop . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
