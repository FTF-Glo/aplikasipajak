<?php
ini_set('memory_limit', '-1');
ini_set("max_execution_time", "-1");

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

require_once $sRootPath . "inc/PBB/SimpleDB.php";
require_once $sRootPath . "inc/phpexcel/Classes/PHPExcel.php";

$kelurahanId = isset($_REQUEST['kelurahan_id']) ? $_REQUEST['kelurahan_id'] : null;
$tahun = isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : '2021';

$db = new SimpleDB();
$phpExcel = new PHPExcel();

///////////////////////////////////////////////////////////////////

if (!$kelurahanId) {
	$kelurahanList = $db->dbQuery("SELECT * FROM cppmod_tax_kelurahan KEL JOIN cppmod_tax_kecamatan KEC ON KEC.CPC_TKC_ID = KEL.CPC_TKL_KCID")->fetchAll();
	foreach ($kelurahanList as $kelurahan) {
		$url = "/view/PBB/monitoring/svc-toexcel-per-kelurahan.php?kelurahan_id={$kelurahan['CPC_TKL_ID']}&tahun={$tahun}";
		echo "<iframe hidden src='{$url}'></iframe>";
	}

	exit;
}

///////////////////////////////////////////////////////////////////

$kelurahan = $db->dbQuery("SELECT * FROM cppmod_tax_kelurahan KEL JOIN cppmod_tax_kecamatan KEC ON KEC.CPC_TKC_ID = KEL.CPC_TKL_KCID WHERE KEL.CPC_TKL_ID = '" . $db->dbEscape($kelurahanId) . "'")->fetchRow();

if (!$kelurahanId || !$kelurahan) {
	echo 'Kelurahan tidak ditemukan.';
	exit;
}

$kelurahanName = $kelurahan['CPC_TKL_KELURAHAN'];
$kecamatanName = $kelurahan['CPC_TKC_KECAMATAN'];

///////////////////////////////////////////////////////////////////

$phpExcel->getProperties()
	->setCreator("FTF Globalindo")
	->setLastModifiedBy("FTF Globalindo")
	->setTitle("KETETAPAN TAHUN {$kecamatanName} - {$kelurahanName} - {$tahun}")
	->setSubject("PBB PESAWARAN")
	->setDescription("PBB PESAWARAN")
	->setKeywords("PBB");

$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle($kelurahanName . ' - ' . $tahun);

$sheet
	->setCellValue('A1', 'NOP')
	->setCellValue('B1', 'TAHUN')
	->setCellValue('C1', 'NAMA WP')
	->setCellValue('D1', 'ALAMAT WP')
	->setCellValue('E1', 'ALAMAT OP')
	->setCellValue('F1', 'KELURAHAN OP')
	->setCellValue('G1', 'KECAMATAN OP')
	->setCellValue('H1', 'KELAS TANAH')
	->setCellValue('I1', 'KELAS BANGUNAN')
	->setCellValue('J1', 'LUAS TANAH')
	->setCellValue('K1', 'LUAS BANGUNAN')
	->setCellValue('L1', 'NJOP TANAH PER M2')
	->setCellValue('M1', 'NJOP BANGUNAN PER M2')
	->setCellValue('N1', 'NJOP TANAH')
	->setCellValue('O1', 'NJOP BANGUNAN')
	->setCellValue('P1', 'NJOP (NJOP TANAH + NJOP BANGUNAN)')
	->setCellValue('Q1', 'NJOPTKP')
	->setCellValue('R1', 'NJKP')
	->setCellValue('S1', 'KETETAPAN')
	->setCellValue('T1', 'STATUS BAYAR');

$sheet->getStyle('A1:T1')->applyFromArray([
	'font' => [
		'bold' => true
	]
]);

$rowNumber = 2;
$formatNumber = '#,##0';

///////////////////////////////////////////////////////////////////

$gwDb = clone $db;
$gwDb->dbOpen('gw');

$sql = "SELECT * FROM pbb_sppt WHERE SPPT_TAHUN_PAJAK = '" . $gwDb->dbEscape($tahun) . "' AND NOP LIKE '" . $gwDb->dbEscape($kelurahanId) . "%'";
$result = $gwDb->dbQuery($sql)->get('result');

while ($row = mysqli_fetch_assoc($result)) {
	$njoptanah    = $row['OP_NJOP_BUMI'];
	$njopbangunan = $row['OP_NJOP_BANGUNAN'];
	$luastanah    = $row['OP_LUAS_BUMI'];
	$luasbangunan = $row['OP_LUAS_BANGUNAN'];

	$njoptanahpermeter = round($luastanah ? ($njoptanah / $luastanah) : 0);
	$njopbangunanpermeter = round($luasbangunan ? ($njopbangunan / $luasbangunan) : 0);

	$sheet
		->setCellValueExplicit('A' . $rowNumber, $row['NOP'], 's')
		->setCellValueExplicit('B' . $rowNumber, $row['SPPT_TAHUN_PAJAK'], 's')
		->setCellValueExplicit('C' . $rowNumber, $row['WP_NAMA'], 's')
		->setCellValueExplicit('D' . $rowNumber, $row['WP_ALAMAT'], 's')
		->setCellValueExplicit('E' . $rowNumber, $row['OP_ALAMAT'], 's')
		->setCellValueExplicit('F' . $rowNumber, $row['OP_KELURAHAN'], 's')
		->setCellValueExplicit('G' . $rowNumber, $row['OP_KECAMATAN'], 's')
		->setCellValueExplicit('H' . $rowNumber, $row['OP_KELAS_BUMI'], 's')
		->setCellValueExplicit('I' . $rowNumber, $row['OP_KELAS_BANGUNAN'], 's')
		->setCellValueExplicit('J' . $rowNumber, $row['OP_LUAS_BUMI'], 'n')
		->setCellValueExplicit('K' . $rowNumber, $row['OP_LUAS_BANGUNAN'], 'n')
		->setCellValueExplicit('L' . $rowNumber, $njoptanahpermeter, 'n')
		->setCellValueExplicit('M' . $rowNumber, $njopbangunanpermeter, 'n')
		->setCellValueExplicit('N' . $rowNumber, $row['OP_NJOP_BUMI'], 'n')
		->setCellValueExplicit('O' . $rowNumber, $row['OP_NJOP_BANGUNAN'], 'n')
		->setCellValueExplicit('P' . $rowNumber, $row['OP_NJOP'], 'n')
		->setCellValueExplicit('Q' . $rowNumber, $row['OP_NJOPTKP'], 'n')
		->setCellValueExplicit('R' . $rowNumber, $row['OP_NJKP'], 'n')
		->setCellValueExplicit('S' . $rowNumber, $row['SPPT_PBB_HARUS_DIBAYAR'], 'n')
		->setCellValue('T' . $rowNumber, ((int) $row['PAYMENT_FLAG']) === 1 ? "LUNAS {$row['PAYMENT_PAID']}" : 'BELUM LUNAS');

	$sheet->getStyle("J{$rowNumber}:S{$rowNumber}")->getNumberFormat()->setFormatCode($formatNumber);

	$rowNumber++;
}

foreach (range('A', 'T') as $column) {
	$sheet->getColumnDimension($column)->setAutoSize(true);
}

$sheet->getStyle('A1:T' . ($rowNumber - 1))->applyFromArray([
	'borders' => [
		'allborders' => [
			'style' => 'thin'
		]
	]
]);

$filename = "KETETAPAN PBB LAMSEL KEC.{$kecamatanName} KEL.{$kelurahanName} - {$tahun} [". date('YmdHi') ."].xls";

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=' . $filename);
header('Cache-Control: max-age=0');

$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
$writer->save('php://output');
