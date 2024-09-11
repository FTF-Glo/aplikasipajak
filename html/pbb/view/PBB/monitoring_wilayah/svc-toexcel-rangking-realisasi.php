<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
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
	global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s, $qBuku, $where_plus;
	if ($mod == 0) $kec =  getKecamatan($kab);
	else {
		if ($kelurahan)
			$kec = getKelurahan($kelurahan);
		else
			$kec = getKelurahan($kecamatan);
	}
	$c = count($kec);
	$data = array();
	for ($i = 0; $i < $c; $i++) {
		$ketetapan = getKetetapan($kec[$i]["id"]);
		$realisasi = getRealisasi($kec[$i]["id"]);

		$data[$i]["name"] 		= $kec[$i]["name"];
		$data[$i]["id"] 		= $kec[$i]["id"];
		$data[$i]["ketetapan"] 	= $ketetapan['KETETAPAN'];
		$data[$i]["realisasi"] 	= $realisasi['REALISASI'];
		if ($data[$i]["ketetapan"] != 0 && $data[$i]["realisasi"] != 0) {
			$data[$i]["persentase"] = (($realisasi['REALISASI'] / $ketetapan['KETETAPAN']) * 100);
		} else {
			$data[$i]["persentase"] = 0;
		}
	}

	return $data;
}

function getDataAll()
{
	global $myDBLink, $kd, $thn, $bulan, $kecamatan, $kelurahan, $kab, $speriode, $eperiode, $qBuku;

	$myDBLink = openMysql();
	$return = array();
	$whr = "";
	//$srckel=

	if ($kecamatan != "") {
		$kec = " AND PBB.OP_KECAMATAN_KODE=" . $kecamatan . " ";
	}

	if ($kelurahan != "") {
		$kel = " AND KEL.CPC_TKL_ID=" . $kelurahan . " ";
	}

	if ($speriode != "") {
		$whr .= " AND PBB.PAYMENT_PAID >= '" . $speriode . " 00:00:00' ";
	}

	if ($eperiode != "") {
		$whr .= " AND PBB.PAYMENT_PAID <= '" . $eperiode . " 23:59:59' ";
	}


	if ($kecamatan == "") {
		$query = "SELECT
						ID,
						KECAMATAN,
						sum( JML ) AS JUMLAH_KETETAPAN,
						SUM( JUMLAH ) AS JUMLAH_REALISASI,
						ROUND((SUM( JUMLAH )/sum( JML )*100),2) AS PERC1
					FROM
						(
						SELECT
							KEC.CPC_TKC_ID AS ID,
							KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
							0 JML,
							0 JUMLAH
						FROM
							cppmod_tax_kecamatan KEC 
						GROUP BY
							KEC.CPC_TKC_ID 
						UNION ALL
						SELECT
							KEC.CPC_TKC_ID AS ID,
							KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
							SUM( PBB.SPPT_PBB_HARUS_DIBAYAR ) AS JML,
							0 AS JUMLAH
						FROM
							cppmod_tax_kecamatan KEC
							JOIN PBB_SPPT PBB ON KEC.CPC_TKC_ID = PBB.OP_KECAMATAN_KODE 
						WHERE
							1 = 1 
							AND PBB.SPPT_TAHUN_PAJAK = 2019 {$whr} $qBuku
							
						GROUP BY
							KEC.CPC_TKC_ID UNION ALL
						SELECT
							KEC.CPC_TKC_ID AS ID,
							KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
							0 JML,
							SUM( PBB.PBB_TOTAL_BAYAR ) AS JUMLAH
						FROM
							cppmod_tax_kecamatan KEC
							JOIN PBB_SPPT PBB ON KEC.CPC_TKC_ID = PBB.OP_KECAMATAN_KODE 
						WHERE
							PBB.PAYMENT_FLAG = '1' 
							AND PBB.SPPT_TAHUN_PAJAK = 2019 {$whr} $qBuku
							
						GROUP BY
							KEC.CPC_TKC_ID 
						) y 
					GROUP BY
						ID 
					ORDER BY
						PERC1 DESC";
		// echo $query.'<br/>';
		// exit;
	} else {
		$query = "SELECT
						ID,
						KELURAHAN,
						sum( JML ) AS JUMLAH_KETETAPAN,
						SUM( JUMLAH ) AS JUMLAH_REALISASI,
						ROUND((SUM( JUMLAH )/sum( JML )*100),2) AS PERC1
					FROM
						(
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							0 JML,
							0 JUMLAH
						FROM
							cppmod_tax_kelurahan KEL 
						WHERE
							KEL.CPC_TKL_KCID = '" . $kecamatan . "' $kel
						GROUP BY
							KEL.CPC_TKL_ID UNION ALL
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							SUM( PBB.SPPT_PBB_HARUS_DIBAYAR ) AS JML,
							0 AS JUMLAH
						FROM
							cppmod_tax_kelurahan KEL
							JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE 
						WHERE
							KEL.CPC_TKL_KCID = '" . $kecamatan . "' {$whr} $kel $qBuku
							AND PBB.SPPT_TAHUN_PAJAK = 2019 
							
						GROUP BY
							KEL.CPC_TKL_ID UNION ALL
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							0 JML,
							SUM( PBB.PBB_TOTAL_BAYAR ) AS JUMLAH
						FROM
							cppmod_tax_kelurahan KEL
							JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE 
						WHERE
							PBB.PAYMENT_FLAG = '1' 
							AND KEL.CPC_TKL_KCID = '" . $kecamatan . "' {$whr} $kel $qBuku
							AND PBB.SPPT_TAHUN_PAJAK = 2019 
							
						GROUP BY
							KEL.CPC_TKL_ID 
						) y 
					GROUP BY
						ID 
					ORDER BY
						PERC1 DESC";
	}
	//echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		if ($kecamatan == "") {
			$return[$i]["KECAMATAN"]    = ($row["KECAMATAN"] != "") ? $row["KECAMATAN"] : "";
		} else {
			$return[$i]["KECAMATAN"]    = ($row["KELURAHAN"] != "") ? $row["KELURAHAN"] : "";
		}
		$return[$i]["JUMLAH_KETETAPAN"] 			= ($row["JUMLAH_KETETAPAN"] != "") ? $row["JUMLAH_KETETAPAN"] : 0;
		$return[$i]["JUMLAH_REALISASI"] 		= ($row["JUMLAH_REALISASI"] != "") ? $row["JUMLAH_REALISASI"] : 0;
		if ($row["PERC1"] > 100) {
			$row["PERC1"] = 100;
		}
		$return[$i]["PERC1"] 		= ($row["PERC1"] != "") ? $row["PERC1"] : 0;

		$i++;
	}


	closeMysql($myDBLink);
	return $return;
}

function getKetetapan($kel)
{
	global $myDBLink, $kd, $bulan, $appConfig, $where_plus;

	$myDBLink 			= openMysql();
	$db_gw 				= $appConfig['GW_DBNAME'];
	$thn 				= $appConfig['tahun_tagihan'];
	$query = "SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) AS KETETAPAN
				FROM
					{$db_gw}.PBB_SPPT
				WHERE SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%' $where_plus";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);
	$return["KETETAPAN"] = ($row["KETETAPAN"] != "") ? $row["KETETAPAN"] : 0;
	closeMysql($myDBLink);
	return $return;
}

function getRealisasi($kel)
{
	global $myDBLink, $kd, $bulan, $appConfig, $where_plus;

	$myDBLink 			= openMysql();
	$db_gw 				= $appConfig['GW_DBNAME'];
	$thn 				= $appConfig['tahun_tagihan'];
	$query = "SELECT SUM(PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.PBB_SPPT
				WHERE PAYMENT_FLAG = '1' 
				AND PAYMENT_PAID LIKE '$thn%'
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%' $where_plus ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);
	$return["REALISASI"] = ($row["REALISASI"] != "") ? $row["REALISASI"] : 0;
	closeMysql($myDBLink);
	return $return;
}

function showTable($mod = 0, $nama = "")
{
	global $eperiode, $kecamatan;
	$dt 		= getDataAll();
	$data		= array();
	$c 			= count($dt);
	$summary = array('name' => 'TOTAL', 'sum_ketetapan' => 0, 'sum_realisasi' => 0, 'sum_persen' => 0);
	for ($i = 0; $i < $c; $i++) {

		if ($kecamatan == "") {
			$dtname = $dt[$i]['KECAMATAN'];
		} else {
			$dtname = $dt[$i]['KELURAHAN'];
		}

		$ketetapan 	= $dt[$i]["JUMLAH_KETETAPAN"];
		$realisasi 	= $dt[$i]["JUMLAH_REALISASI"];
		$persentase	= $dt[$i]["PERC1"];

		$tmp = array(
			'KECAMATAN' => $dtname,
			'KETETAPAN' => $ketetapan,
			'REALISASI' => $realisasi,
			'PERSENTASE' => $persentase
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

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$kd 				= $appConfig['KODE_KOTA'];
$thn				= $appConfig['tahun_tagihan'];
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
if ($kecamatan == null) {
	$kecamatan 			= "";
}
$kelurahan 			= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
if ($kelurahan == null) {
	$kelurahan 			= "";
}
$namakec 			= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel 			= @isset($_REQUEST['nKel']) ? $_REQUEST['nKel'] : "";
$eperiode 			= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan 	= @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$qBuku = "";
$buku 	= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";
$arrWhere = array();
$qBuku = "";
if ($buku != 0) {
	switch ($buku) {
		case 1:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
			break;
		case 12:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
			break;
		case 123:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
			break;
		case 1234:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 12345:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 2:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
			break;
		case 23:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
			break;
		case 234:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 2345:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 3:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
			break;
		case 34:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 345:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 4:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 45:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 5:
			$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
	}
}
$where = implode(" AND ", $arrWhere);
$where_plus = " AND" . $where;

if ($kecamatan == "" && $kelurahan == "") {
	$data = showTable();
} else {
	$data = showTable(1, $namakec);
}


// print_r($_REQUEST);
$lKecamatan = "";
$lKelurahan = "";
$arrWhere = array();
if ($kecamatan != "") {
	$label = "KELURAHAN";
	if ($kelurahan != "") {
		array_push($arrWhere, "NOP like '{$kelurahan}%'");
		$lKecamatan = "KECAMATAN : " . strtoupper($namakec);
		$lKelurahan = strtoupper($appConfig['LABEL_KELURAHAN']) . " : " . strtoupper($namaKel);
	} else {
		array_push($arrWhere, "NOP like '{$kecamatan}%'");
		$lKecamatan = "KECAMATAN : " . strtoupper($namakec);
		$lKelurahan = "";
	}
} else {
	$label = "KECAMATAN";
}

if ($kecamatan == "" && $kelurahan == "") {
	$data = showTable();
} else {
	$data = showTable(1, $namakec);
}

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' RANGKING REALISASI PBB TAHUN ' . $thn . '');
$objPHPExcel->getActiveSheet()->setCellValue('A3', ' ' . strtoupper($appConfig['C_KABKOT']) . ' ' . strtoupper($appConfig['NAMA_KOTA']) . ' ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A5', ' NO ')
	->setCellValue('B5', $label)
	->setCellValue('C5', " KETETAPAN ")
	->setCellValue('D5', " REALISASI ")
	->setCellValue('E5', " PERSENTASE ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$summary = array('T_KETETAPAN' => 0, 'T_REALISASI' => 0, 'T_PERSENTASE' => 0);
for ($i = 0; $i < $sumRows; $i++) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($row - 5));
	$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[$i]['KECAMATAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[$i]['KETETAPAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[$i]['REALISASI']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[$i]['PERSENTASE']);
	$row++;

	$summary['T_KETETAPAN'] 	+= $data[$i]['KETETAPAN'];
	$summary['T_REALISASI'] 	+= $data[$i]['REALISASI'];
	$summary['T_PERSENTASE'] 	+= $data[$i]['PERSENTASE'];
}
$summary['T_PERSENTASE'] = ($summary['T_KETETAPAN'] != 0 && $summary['T_REALISASI'] != 0) ? ($summary["T_REALISASI"] / $summary["T_KETETAPAN"] * 100) : 0;
// JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'B' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $summary['T_KETETAPAN']);
$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $summary['T_REALISASI']);
$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, number_format($summary['T_PERSENTASE'], 2, ',', '.'));

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
$objPHPExcel->getActiveSheet()->getStyle('C' . $row)->applyFromArray(
	array(
		'font'    => array(
			'bold' => true
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);
$objPHPExcel->getActiveSheet()->getStyle('D' . $row)->applyFromArray(
	array(
		'font'    => array(
			'bold' => true
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);
$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->applyFromArray(
	array(
		'font'    => array(
			'bold' => true
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
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
$objPHPExcel->getActiveSheet()->setTitle('Rangking Realisasi PBB');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:E' . ($sumRows + 6))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rangking Realisasi PBB ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
