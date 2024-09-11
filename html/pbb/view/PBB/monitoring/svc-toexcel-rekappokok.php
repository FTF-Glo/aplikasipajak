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
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='" . $p . "' ORDER BY CPC_TKC_URUTAN";
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

	return $data;
}

function getKelurahan($p)
{
	global $DBLink;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$data = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}
	return $data;
}

function getData($mod)
{
	global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s, $qBuku;
	if ($mod == 0) $kec =  getKecamatan($kab);
	else {
		if ($kelurahan)
			$kec = getKelurahan($kelurahan);
		else
			$kec = getKelurahan($kecamatan);
	}

	$tahun = "";
	if ($thn != "") {
		$tahun = "and sppt_tahun_pajak='{$thn}'";
	}

	$c = count($kec);
	$data = array();
	for ($i = 0; $i < $c; $i++) {
		$data[$i]["name"] 	= $kec[$i]["name"];
		$data[$i]["id"] 	= $kec[$i]["id"];
		$pedesaan 			= getOpPedesaan($kec[$i]["id"]);
		$perkotaan			= getOpPerkotaan($kec[$i]["id"]);
		// $dhkpPedesaan		= getCountDHKPPedesaan($data[$i]["id"]);
		// $dhkpPerkotaan		= getCountDHKPPerkotaan($data[$i]["id"]);

		$data[$i]["OP_PEDESAAN"] 	= $pedesaan["OP"];
		$data[$i]["DHKP_PEDESAAN"] 	= $pedesaan["DHKP"];
		$data[$i]["PBB_PEDESAAN"]  	= $pedesaan["PBB"];

		$data[$i]["OP_PERKOTAAN"] 	= $perkotaan["OP"];
		$data[$i]["DHKP_PERKOTAAN"] = $perkotaan["DHKP"];
		$data[$i]["PBB_PERKOTAAN"]  = $perkotaan["PBB"];
	}

	return $data;
}

function getOpPedesaan($kel)
{
	global $myDBLink, $kd, $thn, $bulan;

	$myDBLink = openMysql();
	$return = array();
	$return["OP"] = 0;
	$return["PBB"] = 0;
	$return["DHKP"] = 0;
	$query = "SELECT COUNT(*) AS OP, SUM(SPPT_PBB_HARUS_DIBAYAR) AS PBB, COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS DHKP FROM (
				SELECT
					A.NOP, A.SPPT_PBB_HARUS_DIBAYAR, C.CPC_KD_SEKTOR, A.OP_KELURAHAN_KODE
				FROM
					PBB_SPPT A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				) AS PBB 
				WHERE CPC_KD_SEKTOR = '10' ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["OP"]	= ($row["OP"] != "") ? $row["OP"] : 0;
		$return["PBB"]	= ($row["PBB"] != "") ? $row["PBB"] : 0;
		$return["DHKP"]	= ($row["DHKP"] != "") ? $row["DHKP"] : 0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getOpPerkotaan($kel)
{
	global $myDBLink, $kd, $thn, $bulan;

	$myDBLink = openMysql();
	$return = array();
	$return["OP"] = 0;
	$return["PBB"] = 0;
	$return["DHKP"] = 0;
	$query = "SELECT COUNT(*) AS OP, SUM(SPPT_PBB_HARUS_DIBAYAR) AS PBB, COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS DHKP FROM (
				SELECT
					A.NOP, A.SPPT_PBB_HARUS_DIBAYAR, C.CPC_KD_SEKTOR, A.OP_KELURAHAN_KODE
				FROM
					PBB_SPPT A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				) AS PBB 
				WHERE CPC_KD_SEKTOR = '20' ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["OP"]	= ($row["OP"] != "") ? $row["OP"] : 0;
		$return["PBB"]	= ($row["PBB"] != "") ? $row["PBB"] : 0;
		$return["DHKP"]	= ($row["DHKP"] != "") ? $row["DHKP"] : 0;
	}
	closeMysql($myDBLink);
	return $return;
}

// function getCountDHKPPedesaan($kec) {
// global $myDBLink,$kd,$thn,$bulan;

// $myDBLink = openMysql();
// $return=array();
// $return["DHKP_PEDESAAN"]=0;
// $query = "SELECT COUNT(*) AS DHKP_PEDESAAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID = '{$kec}' AND CPC_TKL_KDSEKTOR = '10' "; 
// // echo $query.'<br/>';
// $res = mysqli_query($myDBLink, $query);
// if ($res === false) {
// echo mysqli_error($DBLink);
// exit();
// }

// while ($row = mysqli_fetch_assoc($res)) {
// //print_r($row);
// $return["DHKP_PEDESAAN"]=($row["DHKP_PEDESAAN"]!="")?$row["DHKP_PEDESAAN"]:0;
// }
// closeMysql($myDBLink);
// return $return;
// }

// function getCountDHKPPerkotaan($kec) {
// global $myDBLink,$kd,$thn,$bulan;

// $myDBLink = openMysql();
// $return=array();
// $return["DHKP_PERKOTAAN"]=0;
// $query = "SELECT COUNT(*) AS DHKP_PERKOTAAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID = '{$kec}' AND CPC_TKL_KDSEKTOR = '20' "; 
// // echo $query.'<br/>';
// $res = mysqli_query($myDBLink, $query);
// if ($res === false) {
// echo mysqli_error($DBLink);
// exit();
// }

// while ($row = mysqli_fetch_assoc($res)) {
// // print_r($row);
// $return["DHKP_PERKOTAAN"]=($row["DHKP_PERKOTAAN"]!="")?$row["DHKP_PERKOTAAN"]:0;
// }
// closeMysql($myDBLink);
// return $return;
// }

function showTable($mod = 0, $nama = "")
{
	global $eperiode;
	$dt 		= getData($mod);
	$data		= array();
	$c 			= count($dt);
	$summary = array('name' => 'TOTAL', 'op_pedesaan' => 0, 'dhkp_pedesaan' => 0, 'pbb_pedesaan' => 0, 'op_perkotaan' => 0, 'dhkp_perkotaan' => 0, 'pbb_perkotaan' => 0, 'op_all' => 0, 'dhkp_all' => 0, 'pbb_all' => 0);
	for ($i = 0; $i < $c; $i++) {

		$dtname 		= $dt[$i]["name"];

		$opPedesaan 	= $dt[$i]["OP_PEDESAAN"];
		$dhkpPedesaan 	= $dt[$i]["DHKP_PEDESAAN"];
		$pbbPedesaan 	= $dt[$i]["PBB_PEDESAAN"];

		$opPerkotaan 	= $dt[$i]["OP_PERKOTAAN"];
		$dhkpPerkotaan 	= $dt[$i]["DHKP_PERKOTAAN"];
		$pbbPerkotaan 	= $dt[$i]["PBB_PERKOTAAN"];

		$op_all 		= $dt[$i]["OP_PEDESAAN"] + $dt[$i]["OP_PERKOTAAN"];
		$dhkp_all 		= $dt[$i]["DHKP_PEDESAAN"] + $dt[$i]["DHKP_PERKOTAAN"];
		$pbb_all 		= $dt[$i]["PBB_PEDESAAN"] + $dt[$i]["PBB_PERKOTAAN"];

		$tmp = array(
			'KECAMATAN' => $dtname,
			'OP_PEDESAAN' => $opPedesaan,
			'DHKP_PEDESAAN' => $dhkpPedesaan,
			'PBB_PEDESAAN' => $pbbPedesaan,
			'OP_PERKOTAAN' => $opPerkotaan,
			'DHKP_PERKOTAAN' => $dhkpPerkotaan,
			'PBB_PERKOTAAN' => $pbbPerkotaan,
			'OP_ALL' => $op_all,
			'DHKP_ALL' => $dhkp_all,
			'PBB_ALL' => $pbb_all
		);
		$data[] = $tmp;
	}

	return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$kd 		= $appConfig['KODE_KOTA'];
$perpage	= $appConfig['ITEM_PER_PAGE'];

// print_r($appConfig);EXIT;
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec	= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel	= @isset($_REQUEST['nk']) ? $_REQUEST['nk'] : "";

// print_r($_REQUEST);
$lKecamatan = "";
$lKelurahan = "";
$arrWhere = array();
if ($kecamatan != "") {
	if ($kelurahan != "") {
		array_push($arrWhere, "NOP like '{$kelurahan}%'");
		$lKecamatan = "KECAMATAN : " . strtoupper($namaKec);
		$lKelurahan = strtoupper($appConfig['LABEL_KELURAHAN']) . " : " . strtoupper($namaKel);
	} else {
		array_push($arrWhere, "NOP like '{$kecamatan}%'");
		$lKecamatan = "KECAMATAN : " . strtoupper($namaKec);
		$lKelurahan = "";
	}
}
if ($thn != "") {
	array_push($arrWhere, "SPPT_TAHUN_PAJAK ='{$thn}'");
}
$where = implode(" AND ", $arrWhere);

$data = showTable();

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:K2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:K3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' REKAPITULASI POKOK KETETAPAN PBB TAHUN ' . $thn . '');
$objPHPExcel->getActiveSheet()->setCellValue('A3', ' ' . strtoupper($appConfig['C_KABKOT']) . ' ' . strtoupper($appConfig['NAMA_KOTA']) . ' ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');
$objPHPExcel->getActiveSheet()->mergeCells('B5:B6');
$objPHPExcel->getActiveSheet()->mergeCells('C5:E5');
$objPHPExcel->getActiveSheet()->mergeCells('F5:H5');
$objPHPExcel->getActiveSheet()->mergeCells('I5:K5');

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A5', ' NO ')
	->setCellValue('B5', ' KECAMATAN ')
	->setCellValue('C5', " PEDESAAN ")
	->setCellValue('F5', " PERKOTAAN ")
	->setCellValue('I5', " PEDESAAN - PERKOTAAN ");

$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('C6', ' OP ')
	->setCellValue('D6', ' DHKP ')
	->setCellValue('E6', " PBB ")
	->setCellValue('F6', ' OP ')
	->setCellValue('G6', ' DHKP ')
	->setCellValue('H6', " PBB ")
	->setCellValue('I6', ' OP ')
	->setCellValue('J6', ' DHKP ')
	->setCellValue('K6', " PBB ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 7;
$summary = array('T_OP_PEDESAAN' => 0, 'T_DHKP_PEDESAAN' => 0, 'T_PBB_PEDESAAN' => 0, 'T_OP_PERKOTAAN' => 0, 'T_DHKP_PERKOTAAN' => 0, 'T_PBB_PERKOTAAN' => 0, 'T_OP_ALL' => 0, 'T_DHKP_ALL' => 0, 'T_PBB_ALL' => 0);
for ($i = 0; $i < $sumRows; $i++) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($row - 6));
	$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[$i]['KECAMATAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[$i]['OP_PEDESAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[$i]['DHKP_PEDESAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[$i]['PBB_PEDESAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $data[$i]['OP_PERKOTAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $data[$i]['DHKP_PERKOTAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $data[$i]['PBB_PERKOTAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[$i]['OP_ALL']);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[$i]['DHKP_ALL']);
	$objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[$i]['PBB_ALL']);
	$row++;

	$summary['T_OP_PEDESAAN'] 		 += $data[$i]['OP_PEDESAAN'];
	$summary['T_DHKP_PEDESAAN'] 	 += $data[$i]['DHKP_PEDESAAN'];
	$summary['T_PBB_PEDESAAN'] 		 += $data[$i]['PBB_PEDESAAN'];
	$summary['T_OP_PERKOTAAN'] 		 += $data[$i]['OP_PERKOTAAN'];
	$summary['T_DHKP_PERKOTAAN'] 	 += $data[$i]['DHKP_PERKOTAAN'];
	$summary['T_PBB_PERKOTAAN'] 	 += $data[$i]['PBB_PERKOTAAN'];
	$summary['T_OP_ALL'] 		 	 += $data[$i]['OP_ALL'];
	$summary['T_DHKP_ALL'] 	 		 += $data[$i]['DHKP_ALL'];
	$summary['T_PBB_ALL'] 	 		 += $data[$i]['PBB_ALL'];
}

// JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'B' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $summary['T_OP_PEDESAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $summary['T_DHKP_PEDESAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $summary['T_PBB_PEDESAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $summary['T_OP_PERKOTAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $summary['T_DHKP_PERKOTAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $summary['T_PBB_PERKOTAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $summary['T_OP_ALL']);
$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $summary['T_DHKP_ALL']);
$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $summary['T_PBB_ALL']);

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
$objPHPExcel->getActiveSheet()->setTitle('Rekapitulasi Ketetapan PBB');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:K6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:K' . ($sumRows + 7))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A5:K6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:K6')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekapitulasi Pokok Ketetapan PBB ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
