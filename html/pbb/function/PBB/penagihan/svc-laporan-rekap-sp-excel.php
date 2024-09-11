<?php
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

/** PHPExcel */
require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
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

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$nkc = @isset($_REQUEST['nkc']) ? $_REQUEST['nkc'] : "";

$nmFile = "PROGRES_PENAGIHAN_SP1";

$kelurahan = "";
if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($area);
$host 		= $appConfig['GW_DBHOST'];
$port 		= $appConfig['GW_DBPORT'];
$user 		= $appConfig['GW_DBUSER'];
$pass 		= $appConfig['GW_DBPWD'];
$dbname 	= $appConfig['GW_DBNAME'];
$myDBLink 	= "";

$kab  	   = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$sp		   = @isset($_REQUEST['sp']) ? $_REQUEST['sp'] : 0;
$thn	   = @isset($_REQUEST['thn']) ? $_REQUEST['thn'] : "";
$t1	   	   = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$t2	   	   = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";

$qSP = "";
switch ($sp) {
	case 1:
		$qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
		$fieldKet = "KETERANGAN_SP1";
		$fieldThn = "TAHUN_SP1";
		$fieldTgl = "TGL_SP1";
		$fieldKetetapan = "KETETAPAN_SP1";
		break;
	case 2:
		$qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
		$fieldKet = "KETERANGAN_SP2";
		$fieldThn = "TAHUN_SP2";
		$fieldTgl = "TGL_SP2";
		$fieldKetetapan = "KETETAPAN_SP2";
		break;
	case 3:
		$qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NOT NULL OR A.TGL_SP3 <> '')";
		$fieldKet = "KETERANGAN_SP3";
		$fieldThn = "TAHUN_SP3";
		$fieldTgl = "TGL_SP3";
		$fieldKetetapan = "KETETAPAN_SP3";
		break;
}

$arrWhere = array();
// if ($kecamatan !=""){
// array_push($arrWhere,"A.NOP LIKE '{$kecamatan}%'");
// }
if ($thn != "") {
	array_push($arrWhere, "A.$fieldThn LIKE '%{$thn}%'");
}
if (($t1) && ($t2)) {
	array_push($arrWhere, "A.$fieldTgl >= '$t1' AND A.$fieldTgl <= '$t2'");
}
$where = implode(" AND ", $arrWhere);

if ($nkc != "Pilih Semua") {
	$kcm = "Kecamatan " . $nkc;
} else {
	$kcm = ucfirst(strtolower($appConfig['C_KABKOT'])) . " " . $appConfig['kota'];
}

if (stillInSession($DBLink, $json, $sdata)) {
	$result = getData();
	// echo "<pre>";
	// print_r($result);exit;
} else {
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

function openMysql()
{
	global $host, $port, $dbname, $user, $pass;
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

function getJmlSP($qSP, $where, $kecKode)
{
	global $myDBLink;

	$myDBLink = openMysql();
	$return["TOTAL_SP_TERBIT"] = 0;

	$whr = "";
	if ($where) {
		$whr = " AND $where";
	}

	$query = "SELECT COUNT(*) AS TOTAL_SP_TERBIT
                FROM
                        PBB_SPPT_PENAGIHAN A
                WHERE
                A.NOP LIKE '$kecKode%' $whr $qSP";
	// echo $query."<br>";
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);

	$return["TOTAL_SP_TERBIT"] = ($row["TOTAL_SP_TERBIT"] != "") ? $row["TOTAL_SP_TERBIT"] : 0;

	// closeMysql($myDBLink);
	return $return["TOTAL_SP_TERBIT"];
}

function getSPDikembalikan($qSP, $where, $kecKode)
{
	global $myDBLink, $fieldKet;

	$myDBLink = openMysql();
	$return["TOTAL_SP_KEMBALI"] = 0;

	$whr = "";
	if ($where) {
		$whr = " AND {$where}";
	}

	$query = "SELECT
					COUNT(DISTINCT(A.NOP)) AS TOTAL_SP_KEMBALI
				FROM
					PBB_SPPT_PENAGIHAN A
					JOIN PBB_SPPT C
				WHERE
					A.NOP = C.NOP
				AND C.OP_KECAMATAN_KODE = '$kecKode' $qSP $whr AND ($fieldKet <> '' OR $fieldKet IS NOT NULL)
				ORDER BY
					C.SPPT_TAHUN_PAJAK DESC";

	// echo $query."<br>";
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);

	$return["TOTAL_SP_KEMBALI"] = ($row["TOTAL_SP_KEMBALI"] != "") ? $row["TOTAL_SP_KEMBALI"] : 0;

	// closeMysql($myDBLink);
	return $return["TOTAL_SP_KEMBALI"];
}

function getKetetapan($qSP, $where, $kecKode)
{
	global $myDBLink, $fieldKet, $fieldKetetapan;

	$myDBLink = openMysql();
	$return["TOTAL_SP_KEMBALI"] = 0;

	$whr = "";
	if ($where) {
		$whr = " AND {$where}";
	}

	//	$query = "SELECT
	//					SUM(A.$fieldKetetapan) AS KETETAPAN
	//				FROM
	//					PBB_SPPT_PENAGIHAN A
	//				LEFT JOIN PBB_SPPT_TAHUN_PENAGIHAN B ON A.ID = B.ID
	//				JOIN PBB_SPPT C
	//				WHERE
	//					A.NOP = C.NOP
	//				AND B.SPPT_TAHUN_PAJAK = C.SPPT_TAHUN_PAJAK
	//				AND C.OP_KECAMATAN_KODE = '$kecKode' $qSP $whr
	//				ORDER BY
	//					B.SPPT_TAHUN_PAJAK DESC"; 
	$query = "SELECT
                    SUM(A.$fieldKetetapan) AS KETETAPAN
                FROM
					PBB_SPPT_PENAGIHAN A
                WHERE
                A.NOP LIKE '$kecKode%' $whr $qSP";

	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);

	$return["KETETAPAN"] = ($row["KETETAPAN"] != "") ? $row["KETETAPAN"] : 0;

	// closeMysql($myDBLink);
	return $return["KETETAPAN"];
}

function getData()
{
	global $myDBLink, $kd, $bulan, $thn, $sp, $kecamatan, $kab, $qSP, $where;

	$myDBLink = openMysql();

	$kec = getKecamatan($kab);

	$data = array();
	$c 	  = count($kec);
	$i = 0;
	for ($i = 0; $i < $c; $i++) {
		$data[$i]["KECAMATAN"] 		 		= $kec[$i]['name'];
		$data[$i]["TOTAL_SP_TERBIT"] 		= getJmlSP($qSP, $where, $kec[$i]['id']);
		$data[$i]["TOTAL_SP_KEMBALI"] 		= getSPDikembalikan($qSP, $where, $kec[$i]['id']);
		$data[$i]["TOTAL_SP_BELUM_KEMBALI"] = $data[$i]["TOTAL_SP_TERBIT"] - $data[$i]["TOTAL_SP_KEMBALI"];
		$data[$i]["KETETAPAN"]		 		= getKetetapan($qSP, $where, $kec[$i]['id']);
	}
	closeMysql($myDBLink);
	return $data;
}

if ($sp == 1)
	$noSP = "I";
else if ($sp == 2)
	$noSP = "II";
else
	$noSP = "III";

$sumRows = count($result);

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:F2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:F3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'DAFTAR REKAP PENYAMPAIAN SURAT PEMBERITAHUAN ' . $noSP);
$objPHPExcel->getActiveSheet()->setCellValue('A3', 'DI ' . $sumRows . ' KECAMATAN');
// if($nkc!="Pilih Semua"){
// $objPHPExcel->getActiveSheet()->setCellValue('A3', 'KECAMATAN : '.$nkc);
// } else {
// $objPHPExcel->getActiveSheet()->setCellValue('A3', 'KABUPATEN/KOTA : '.$appConfig['NAMA_KOTA']);
// }
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A5', ' NO ')
	->setCellValue('B5', ' KECAMATAN ')
	->setCellValue('C5', " TOTAL SP" . $sp . " \nYANG DITERBITKAN ")
	->setCellValue('D5', " TOTAL SP" . $sp . " \nYANG BELUM DIKEMBALIKAN ")
	->setCellValue('E5', " TOTAL SP" . $sp . " \nYANG DIKEMBALIKAN ")
	->setCellValue('F5', " TOTAL KETETAPAN \nSP" . $sp . " ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$summary = array('SUM_TOTAL_SP_TERBIT' => 0, 'SUM_TOTAL_SP_BELUM_KEMBALI' => 0, 'SUM_TOTAL_SP_KEMBALI' => 0, 'SUM_KETETAPAN' => 0);
for ($i = 0; $i < $sumRows; $i++) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 5));
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($result[$i]['KECAMATAN']));
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($result[$i]['TOTAL_SP_TERBIT']));
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($result[$i]['TOTAL_SP_KEMBALI']));
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, ($result[$i]['TOTAL_SP_BELUM_KEMBALI']));
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, ($result[$i]['KETETAPAN']));
	$row++;

	$summary['SUM_TOTAL_SP_TERBIT'] 		 += $result[$i]['TOTAL_SP_TERBIT'];
	$summary['SUM_TOTAL_SP_BELUM_KEMBALI'] 	 += $result[$i]['TOTAL_SP_BELUM_KEMBALI'];
	$summary['SUM_TOTAL_SP_KEMBALI'] 		 += $result[$i]['TOTAL_SP_KEMBALI'];
	$summary['SUM_KETETAPAN'] 		 	 	 += $result[$i]['KETETAPAN'];
}

//JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'B' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $summary['SUM_TOTAL_SP_TERBIT']);
$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $summary['SUM_TOTAL_SP_BELUM_KEMBALI']);
$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $summary['SUM_TOTAL_SP_KEMBALI']);
$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $summary['SUM_KETETAPAN']);

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
$objPHPExcel->getActiveSheet()->setTitle('Laporan Progres SP' . $sp);

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:F' . ($sumRows + 6))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('C5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('F5')->getAlignment()->setWrapText(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Laporan Progres SP' . $sp . ' ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
