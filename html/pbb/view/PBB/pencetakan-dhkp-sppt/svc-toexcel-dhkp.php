<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pencetakan-dhkp-sppt', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL);
ini_set('display_errors', 0);
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
	return $bulan;
}

function getData()
{
	global $DBLink, $kabKotLabel, $kabKotNama, $prop, $kota, $kec, $kel, $kd_prop, $kd_kota, $kd_kec, $kd_kel, $tahun, $buku, $kd_buku,
		$cover_jumlah_op, $cover_luas_bumi, $cover_luas_bangunan, $cover_pokok_ketetapan;

	$where = sprintf("WHERE OP_KELURAHAN_KODE = '%s' AND SPPT_TAHUN_PAJAK = '%s'", $kd_kel, $tahun);
	switch ($kd_buku) {
		case 1:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
			break;
		case 12:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
			break;
		case 123:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
			break;
		case 1234:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 12345:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 2:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
			break;
		case 23:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
			break;
		case 234:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 2345:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 3:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
			break;
		case 34:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 345:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 4:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
			break;
		case 45:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
		case 5:
			$where .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
			break;
	}

	$query = "SELECT * FROM cppmod_pbb_sppt_current	{$where} ORDER BY NOP ASC";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	$i     = 0;
	while ($rows  = mysqli_fetch_assoc($res)) {

		$data[$i]['NOP'] = $rows['NOP'];
		$data[$i]['WP_NAMA'] = $rows['WP_NAMA'];
		$data[$i]['WP_ALAMAT'] = $rows['WP_ALAMAT'];
		$data[$i]['OP_ALAMAT'] = $rows['OP_ALAMAT'];
		$data[$i]['OP_LUAS_BUMI'] = $rows['OP_LUAS_BUMI'];
		$data[$i]['OP_LUAS_BANGUNAN'] = $rows['OP_LUAS_BANGUNAN'];
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] = $rows['SPPT_PBB_HARUS_DIBAYAR'];

		$cover_jumlah_op++;
		$cover_luas_bumi += (int) $rows['OP_LUAS_BUMI'];
		$cover_luas_bangunan += (int) $rows['OP_LUAS_BANGUNAN'];
		$cover_pokok_ketetapan += (int) $rows['SPPT_PBB_HARUS_DIBAYAR'];
		$i++;
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

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

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

$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$kabKotLabel = strtoupper($appConfig['C_KABKOT']);
$kabKotNama = strtoupper($appConfig['NAMA_KOTA']);
$TDD_NAMA = strtoupper($appConfig['NAMA_PEJABAT_SK2']);
$TDD_JABATAN = strtoupper($appConfig['NAMA_PEJABAT_SK2_JABATAN']);
$TDD_NIP = strtoupper($appConfig['NAMA_PEJABAT_SK2_NIP']);


$prop = $_POST['prop'];
$kota = $_POST['kota'];
$kec = $_POST['kec'];
$kel = $_POST['kel'];
$kd_prop = $_POST['kd_prop'];
$kd_kota = $_POST['kd_kota'];
$kd_kec = $_POST['kd_kec'];
$kd_kel = $_POST['kd_kel'];
$tahun = $_POST['thn'];
$kd_buku = $_POST['kd_buku'];
$buku = strtoupper($_POST['buku']);

function cover($row)
{
	global $objPHPExcel, $kabKotLabel, $kabKotNama, $prop, $kota, $kec, $kel, $kd_prop, $kd_kota, $kd_kec, $kd_kel, $tahun, $buku, $kd_buku,
		$cover_jumlah_op, $cover_luas_bumi, $cover_luas_bangunan, $cover_pokok_ketetapan, $TDD_NAMA, $TDD_JABATAN, $TDD_NIP;

	/*header*/
	$row_begin = $row;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "PEMERINTAH {$kabKotLabel} {$kabKotNama}");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setSize(24);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "DINAS PENDAPATAN DAERAH");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setSize(16);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
	$row += 3;

	$kd_prop = substr($kd_kec, 0, 2);
	$kd_kota = substr($kd_kec, 2, 2);
	$kd_kec = substr($kd_kec, -3);
	$kd_kel = substr($kd_kel, -3);

	/*indentitas*/
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "PROPINSI");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . $kd_prop . ' - ' . $prop);
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", $kabKotLabel);
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . $kd_kota . ' - ' . $kota);
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "KECAMATAN");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . $kd_kec . ' - ' . $kec);
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "KELURAHAN/");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . $kd_kel . ' - ' . $kel);
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "DESA");
	$row += 3;

	/*judul 2*/
	$row_judul2 = $row;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "DAFTAR HIMPUNAN KETETAPAN PAJAK DAN PEMBAYARAN (DHKP)");
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "PAJAK BUMI DAN BANGUNAN");
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "TAHUN {$tahun}");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row_judul2}:A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	/*judul buku*/
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "{$buku}");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setSize(16);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
	$row += 3;

	/*keterangan*/
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "JUMLAH OBJEK");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . number_format($cover_jumlah_op, 0));
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "LUAS BUMI");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . number_format($cover_luas_bumi, 0));
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "LUAS BANGUNAN");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     " . number_format($cover_luas_bangunan, 0));
	$row++;
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", "POKOK KETETAPAN");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", ":     Rp." . number_format($cover_pokok_ketetapan, 0));

	/*footer*/
	$row += 10;
	$row_begin = $row;
	$objPHPExcel->getActiveSheet()->mergeCells("D{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", "{$kota}, " . date("d") . " " . bulan((int) date("m")) . " " . date('Y'));
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("D{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", "KEPALA DINAS PENDAPATAN DAERAH");
	$objPHPExcel->getActiveSheet()->getStyle("D{$row}:H{$row}")->getFont()->setBold(true);
	$row += 7;
	$objPHPExcel->getActiveSheet()->mergeCells("D{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", $TDD_NAMA);
	$objPHPExcel->getActiveSheet()->getStyle("D{$row}:H{$row}")->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle("D{$row}")->applyFromArray(array('font' => array('underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE)));

	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("D{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", $TDD_JABATAN);
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("D{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("D{$row}", "NIP. " . $TDD_NIP);
	$objPHPExcel->getActiveSheet()->getStyle("D{$row_begin}:H{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	/*lebar kolom*/
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

	#setup print
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:H' . $row);
	$objPHPExcel->getActiveSheet()->getStyle('A1:H' . $row)->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(73);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.75);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.26);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.75);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
	$objPHPExcel->getActiveSheet()->setTitle('COVER');
	return $row;
}

function table_header($row)
{
	global $objPHPExcel, $kabKotLabel, $kabKotNama, $prop, $kota, $kec, $kel, $kd_prop, $kd_kota, $kd_kec, $kd_kel, $tahun, $buku, $kd_buku;

	$kd_kec = substr($kd_kec, -3);
	$kd_kel = substr($kd_kel, -3);

	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(1);
	$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('Halaman &P / &N');
	$objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('Halaman &P / &N');

	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "DAFTAR HIMPUNAN KETETAPAN PAJAK TAHUN {$tahun}");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->getFont()->setSize(16);
	$row++;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:B{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", "KECAMATAN");
	$objPHPExcel->getActiveSheet()->setCellValue("C{$row}", ":  {$kd_kec} - {$kec}");

	$objPHPExcel->getActiveSheet()->setCellValue("E{$row}", "DESA");
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:H{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", ":  {$kd_kel} - {$kel}");
	$row++;
	$objPHPExcel->setActiveSheetIndex(1)
		->setCellValue("A{$row}", "NO")
		->setCellValue("B{$row}", "NOP")
		->setCellValue("C{$row}", "NAMA WAJIB PAJAK")
		->setCellValue("D{$row}", "ALAMAT WAJIB PAJAK\nALAMAT OBJEK PAJAK")
		->setCellValue("E{$row}", "LUAS BUMI\nLUAS BNG")
		->setCellValue("F{$row}", "PBB\nTERHUTANG")
		->setCellValue("G{$row}", "KET.");

	//style header
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:H{$row}")->applyFromArray(
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
	global $objPHPExcel, $kabKotLabel, $kabKotNama, $prop, $kota, $kec, $kel, $kd_prop, $kd_kota, $kd_kec, $kd_kel, $tahun, $buku, $kd_buku,
		$data, $row;

	$row_begin = $row;
	for ($i = 0; $i < count($data); $i++) {
		if (!isset($data[$i])) break;

		$CPM_NOP = substr($data[$i]['NOP'], 10, 3) . "-" . substr($data[$i]['NOP'], 13, 4) . "." . substr($data[$i]['NOP'], 17, 1);

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($i + 1));
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($CPM_NOP . ""));
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($data[$i]['WP_NAMA']));
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($data[$i]['WP_ALAMAT'] . "\n" . $data[$i]['OP_ALAMAT']));
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, number_format($data[$i]['OP_LUAS_BUMI'], 0) . "\n" . number_format($data[$i]['OP_LUAS_BANGUNAN'], 0));
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, number_format($data[$i]['SPPT_PBB_HARUS_DIBAYAR'], 0));
		$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '');

		$row++;
	}

	/*align top*/
	$objPHPExcel->getActiveSheet()->getStyle("A{$row_begin}:G{$row}")->applyFromArray(
		array("alignment" => array(
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	/*align center*/
	$objPHPExcel->getActiveSheet()->getStyle("A{$row_begin}:B{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	/*align right*/
	$objPHPExcel->getActiveSheet()->getStyle("E{$row_begin}:F{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	/*border table*/
	$objPHPExcel->getActiveSheet()->getStyle("A" . ($row_begin - 1) . ":G" . ($row - 1))->applyFromArray(
		array('borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		))
	);

	/*lebar kolom*/
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);

	#setup print
	$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:H' . $row);
	$objPHPExcel->getActiveSheet()->getStyle('A1:H' . $row)->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(73);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.75);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.26);
	$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.75);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
	$objPHPExcel->getActiveSheet()->setTitle('DHKP');


	return $row;
}

$cover_jumlah_op = 0;
$cover_luas_bumi = 0;
$cover_luas_bangunan = 0;
$cover_pokok_ketetapan = 0;

/*main process*/
$data = getData();
$sumRows = count($data);

//sheet 1
$row = 10;
$row = cover($row);

//sheet 2
$row = 1;
$row = table_header($row);
$row++;
$row = fetchData();

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DHKP_' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
