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

$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$sp		   = @isset($_REQUEST['sp']) ? $_REQUEST['sp'] : 0;
$thn	   = @isset($_REQUEST['thn']) ? $_REQUEST['thn'] : "";
$t1	   	   = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$t2	   	   = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";

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

if ($nkc != "Pilih Semua") {
	$kcm = "Kecamatan " . $nkc;
} else {
	$kcm = ucfirst(strtolower($appConfig['C_KABKOT'])) . " " . $appConfig['kota'];
}

if (stillInSession($DBLink, $json, $sdata)) {
	$result = getData();
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

function getData()
{
	global $myDBLink, $kecamatan, $sp, $qSP, $fieldKet, $fieldTgl, $fieldThn, $where;

	$myDBLink = openMysql();

	$qSP = "";
	switch ($sp) {
		case 1:
			$qSP .= "(A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
			$fieldKet = "KETERANGAN_SP1";
			$fieldThn = "TAHUN_SP1";
			$fieldTgl = "TGL_SP1";
			$fieldKetetapan = "KETETAPAN_SP1";
			break;
		case 2:
			$qSP .= "(A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
			$fieldKet = "KETERANGAN_SP2";
			$fieldThn = "TAHUN_SP2";
			$fieldTgl = "TGL_SP2";
			$fieldKetetapan = "KETETAPAN_SP2";
			break;
		case 3:
			$qSP .= "(A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NOT NULL OR A.TGL_SP3 <> '')";
			$fieldKet = "KETERANGAN_SP3";
			$fieldThn = "TAHUN_SP3";
			$fieldTgl = "TGL_SP3";
			$fieldKetetapan = "KETETAPAN_SP3";
			break;
	}

	$arrWhere = array();
	if ($kecamatan != "") {
		array_push($arrWhere, "A.NOP LIKE '{$kecamatan}%'");
	}
	if ($thn != "") {
		array_push($arrWhere, "A.$fieldThn LIKE '%{$thn}%'");
	}
	if (($t1) && ($t2)) {
		array_push($arrWhere, "A.$fieldTgl >= '$t1' AND A.$fieldTgl <= '$t2'");
	}
	$where = implode(" AND ", $arrWhere);

	$whr = "";
	if ($where) {
		$whr .= " AND $where";
	}

	$result = array();
	$query = "SELECT
			A.NOP,
			C.WP_NAMA,
			C.OP_ALAMAT,
			C.OP_RT,
			C.OP_RW,
			C.OP_KELURAHAN,
			C.SPPT_PBB_HARUS_DIBAYAR,
			C.SPPT_TAHUN_PAJAK,
			A." . $fieldTgl . ",
			A." . $fieldThn . ",
			A." . $fieldKet . "  ,
			A.STATUS_SP,
			A.STATUS_PERSETUJUAN,
			A." . $fieldKetetapan . " AS KETETAPAN
		FROM
			PBB_SPPT_PENAGIHAN A
		LEFT JOIN PBB_SPPT C ON A.NOP = C.NOP
		WHERE $qSP $whr AND ($fieldKet <> '' OR $fieldKet IS NOT NULL)
                GROUP BY A.ID
						ORDER BY
							A.NOP,A." . $fieldTgl . "";
	// echo $query;exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res) {
		$result["result"] = "true";
		$result["data"] = $res;
	} else {
		$result["result"] = "false";
		$result["data"] = mysqli_error($myDBLink);
	}
	closeMysql($myDBLink);
	return $result;
}

function getKecamatanNama($kode)
{
	global $DBLink;
	$query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
	$res   = mysqli_query($DBLink, $query);
	$row   = mysqli_fetch_array($res);
	return $row['CPC_TKC_KECAMATAN'];
}
function getKelurahanNama($kode)
{
	global $DBLink;
	$query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
	$res   = mysqli_query($DBLink, $query);
	$row   = mysqli_fetch_array($res);
	return $row['CPC_TKL_KELURAHAN'];
}

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:G3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'PROSES PENAGIHAN PIUTANG WAJIB PAJAK POTENSIAL');
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
	->setCellValue('B5', ' NOP ')
	->setCellValue('C5', ' NAMA ')
	->setCellValue('D5', ' ALAMAT OBJEK PAJAK ')
	->setCellValue('E5', ' KETETAPAN ')
	->setCellValue('F5', ' TAHUN PAJAK ')
	->setCellValue('G5', ' KETERANGAN ');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

// $arrStatus = array(
// 1 => "SP1 yang sudah diterima Wajib Pajak",
// 2 => "Wajib Pajak yang sudah membayar PBB setelah penerbitaan SP 1",
// 3 => "Data Wajib Pajak yang dibatalkan",
// 4 => "Alamat tidak ditemukan",
// 5 => "Tanah sengketa",
// 6 => "Wajib Pajak sudah melakukan perubahan data"
// );

$row = 6;
$sumRows = mysqli_num_rows($result['data']);
$summary = array('TOTAL' => 0, 'DITERIMA' => 0, 'DIKEMBALIKAN' => 0);
while ($rowData = mysqli_fetch_assoc($result['data'])) {
	$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 5));
	$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['NOP'] . " "));
	$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['WP_NAMA']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['OP_ALAMAT']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['KETETAPAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData[$fieldThn]);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData[$fieldKet]);
	$row++;
	$i++;

	$summary['TOTAL'] += $rowData['KETETAPAN'];
	$summary['DITERIMA'] += 1;

	if ($rowData[$fieldKet]) {
		$summary['DIKEMBALIKAN'] += 1;
	}
}

//JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'D' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL KETETAPAN');
$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $summary['TOTAL']);
//JML SP1 DITERIMA
// $objPHPExcel->getActiveSheet()->mergeCells('A'.($row+2).':'.'G'.($row+2));
// $objPHPExcel->getActiveSheet()->setCellValue('A'.($row+2), 'JUMLAH SP'.$sp.' YANG DITERIMA = '.$summary['DITERIMA']);
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 3) . ':' . 'G' . ($row + 3));
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 3), 'JUMLAH SP' . $sp . ' YANG DIKEMBALIKAN = ' . $summary['DIKEMBALIKAN']);
// $objPHPExcel->getActiveSheet()->mergeCells('A'.($row+4).':'.'G'.($row+4));
// $objPHPExcel->getActiveSheet()->setCellValue('A'.($row+4), 'SISA SP'.$sp.' YANG BELUM DIKEMBALIKAN = '.($summary['DITERIMA'] - $summary['DIKEMBALIKAN']));
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
$objPHPExcel->getActiveSheet()->getStyle('A5:G5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:G' . ($sumRows + 6))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A5:G5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:G5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A6:A' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('B6:B' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('E6:E' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('F6:F' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('G6:G' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('G5')->getAlignment()->setWrapText(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Laporan Progres SP' . $sp . ' ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
