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

$nmFile = "Laporan_Proses_SP1";

$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$sp		   = @isset($_REQUEST['sp']) ? $_REQUEST['sp'] : 0;
$thn	   = @isset($_REQUEST['thn']) ? $_REQUEST['thn'] : "";
$t1			= @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$t2			= @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$cb_batal   = @isset($_REQUEST['cb_batal']) ? $_REQUEST['cb_batal'] : 0;

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

$qSP = "";
switch ($sp) {
	case 1:
		$qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
		$fieldKet = "KETERANGAN_SP1";
		$fieldThn = "TAHUN_SP1";
		$fieldTgl = "TGL_SP1";
		break;
	case 2:
		$qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
		$fieldKet = "KETERANGAN_SP2";
		$fieldThn = "TAHUN_SP2";
		$fieldTgl = "TGL_SP2";
		break;
	case 3:
		$qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NOT NULL OR A.TGL_SP3 <> '')";
		$fieldKet = "KETERANGAN_SP3";
		$fieldThn = "TAHUN_SP3";
		$fieldTgl = "TGL_SP3";
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
if ($cb_batal <> 0) {
	array_push($arrWhere, "A.STATUS_SP = $cb_batal");
}
$where = implode(" AND ", $arrWhere);

if ($sp == 1)
	$noSP = "I";
else if ($sp == 2)
	$noSP = "II";
else
	$noSP = "III";

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
	global $myDBLink, $kecamatan, $sp, $t1, $t2, $qSP, $where, $fieldKet, $fieldTgl, $fieldThn;

	$myDBLink = openMysql();

	$whr = "";
	if ($where) {
		$whr .= " AND $where";
	}

	$query = "SELECT
					*, SUM(SPPT_PBB_HARUS_DIBAYAR) AS KETETAPAN
				FROM
					(
						SELECT
							A.NOP,
							C.WP_NAMA,
							C.WP_ALAMAT,
							C.WP_RT,
							C.WP_RW,
							C.WP_KELURAHAN,
							C.OP_ALAMAT,
							C.OP_RT,
							C.OP_RW,
							C.OP_KELURAHAN,
							C.OP_KECAMATAN,
							C.SPPT_PBB_HARUS_DIBAYAR,
							A.$fieldTgl,
							A.$fieldThn,
							A.$fieldKet,
							A.STATUS_SP,
							A.STATUS_PERSETUJUAN
						FROM
							PBB_SPPT_PENAGIHAN A
						JOIN PBB_SPPT C
						WHERE
							A.NOP = C.NOP AND A.STATUS_SP NOT IN ('0','1','2')
						$qSP $whr
					) AS PENAGIHAN
				GROUP BY
					NOP";
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

$tittle = "SETELAH PENERBITAN SURAT PEMBERITAHUAN $noSP";

switch ($cb_batal) {
	case 0:
		$txtJnsPembatalan = "DAFTAR SP$sp YANG BERMASALAH";
		$namaFile = "SP" . $sp . " BERMASALAH";
		break;
	case 3:
		$txtJnsPembatalan = "DAFTAR PEMBATALAN SPPT PBB";
		$namaFile = "WP SUDAH PERUBAHAN DATA SP" . $sp;
		break;
	case 4:
		$txtJnsPembatalan = "DAFTAR WP YANG ALAMATNYA TIDAK DITEMUKAN";
		$namaFile = "ALAMAT TIDAK DITEMUKAN SP" . $sp;
		break;
	case 5:
		$txtJnsPembatalan = "DAFTAR TANAH YANG SENGKETA";
		$namaFile = "TANAH SENGKETA SP" . $sp;
		break;
	case 6:
		$txtJnsPembatalan = "DAFTAR WP SUDAH PERUBAHAN DATA";
		$namaFile = "PEMBATALAN SPPT PBB SP" . $sp;
		break;
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
$objPHPExcel->getActiveSheet()->mergeCells('A2:O2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:O3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', $txtJnsPembatalan);
$objPHPExcel->getActiveSheet()->setCellValue('A3', $tittle);

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
	->setCellValue('D5', ' ALAMAT WAJIB PAJAK ')
	->setCellValue('E5', ' RT WP ')
	->setCellValue('F5', ' RW WP ')
	->setCellValue('G5', ' KELURAHAN WP ')
	->setCellValue('H5', ' ALAMAT OBJEK PAJAK ')
	->setCellValue('I5', ' RT OP ')
	->setCellValue('J5', ' RW OP ')
	->setCellValue('K5', ' KELURAHAN OP ')
	->setCellValue('L5', ' KECAMATAN OP ')
	->setCellValue('M5', ' KETETAPAN ')
	->setCellValue('N5', ' TAHUN PAJAK ')
	->setCellValue('O5', " KETERANGAN ");

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
	$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['WP_ALAMAT']);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['WP_RT']);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['WP_RW']);
	$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['WP_KELURAHAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['OP_ALAMAT']);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['OP_RT']);
	$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['OP_RW']);
	$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['OP_KELURAHAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['OP_KECAMATAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['KETETAPAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData[$fieldThn]);
	$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData[$fieldKet]);
	$row++;
	$i++;

	$summary['TOTAL'] += $rowData['KETETAPAN'];
	if ($rowData['STATUS_SP'] == 1) {
		$summary['DITERIMA'] += 1;
	} else if ($rowData['STATUS_SP'] <> 1) {
		$summary['DIKEMBALIKAN'] += 1;
	}
}

//JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'L' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL KETETAPAN');
$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $summary['TOTAL']);
//MENGETAHUI
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 2), 'Mengetahui,');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 3), 'Kepala Bidang PBB dan BPHTB');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 7), 'Drs. H. OKTORIYANIS M, MM');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 8), 'Pembina Tingkat I');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row + 9), 'NIP. 195910171988101001');

$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 2) . ':' . 'G' . ($row + 2));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 3) . ':' . 'G' . ($row + 3));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 4) . ':' . 'G' . ($row + 4));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 5) . ':' . 'G' . ($row + 5));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 6) . ':' . 'G' . ($row + 6));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 7) . ':' . 'G' . ($row + 7));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 8) . ':' . 'G' . ($row + 8));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 9) . ':' . 'G' . ($row + 9));
$objPHPExcel->getActiveSheet()->mergeCells('F' . ($row + 10) . ':' . 'G' . ($row + 10));

$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 2), 'Palembang,             2013');
$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 3), 'Kasi Penagihan, Keberatan dan');
$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 4), 'Pengurangan PBB');
$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 8), 'ELY DALTI, SH,. M.SI');
$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 9), 'Pembina/ IV.a');
$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row + 10), 'NIP. 196004111995032002');

$objPHPExcel->getActiveSheet()->getStyle('B' . ($row + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B' . ($row + 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B' . ($row + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B' . ($row + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B' . ($row + 9))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 9))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 10))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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
$objPHPExcel->getActiveSheet()->setTitle($namaFile);

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:O5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:O' . ($sumRows + 6))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A5:O5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:O5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A6:A' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('B6:B' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('E6:E' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('F6:F' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('G6:G' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('H6:H' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

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
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('O5')->getAlignment()->setWrapText(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $namaFile . " " . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
