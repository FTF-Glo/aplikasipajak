<?php
/* 
	Nama Developer : Jajang Apriansyah (jajang@vsi.co.id)
	Tanggal dibuat : 02/12/2016
*/

/** Error reporting */
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);
// date_default_timezone_set('Asia/Jakarta');

define("NAMA_DINAS", "BADAN PELAYANAN PAJAK DAERAH");

require_once("PHPExcel_1.8.0/Classes/PHPExcel.php");
require_once("inc-config.php");

$nop 	= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$idwp 	= @isset($_REQUEST['idwp']) ? $_REQUEST['idwp'] : "";
$thn1 	= @isset($_REQUEST['thn1']) ? $_REQUEST['thn1'] : "";
$thn2 	= @isset($_REQUEST['thn2']) ? $_REQUEST['thn2'] : "";

$dt 	= GetListByNOP($nop, $idwp, $thn1, $thn2);
// echo "<pre>";
// print_r($dt); exit;
$sumRows  = count($dt);
if ($sumRows < 1) {
	echo 'Data tidak tersedia';
	exit;
}
// echo $dt[$sumRows-1]['NOP']; exit;
$bulan = array(
	"01" => "Januari",
	"02" => "Februari",
	"03" => "Maret",
	"04" => "April",
	"05" => "Mei",
	"06" => "Juni",
	"07" => "Juli",
	"08" => "Agustus",
	"09" => "September",
	"10" => "Oktober",
	"11" => "November",
	"12" => "Desember"
);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Alfa System")
	->setLastModifiedBy("Alfa System")
	->setTitle("Data Pembayaran")
	->setSubject("Data Pembayaran")
	->setDescription("Data Pembayaran Wajib Pajak PBB")
	->setKeywords("Alfa System pbb");

//Style Align
$center = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	)
);
$bold = array('font'    => array('bold' => true));

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A2', 'PEMERINTAH ' . getConfig('C_KABKOT') . ' ' . getConfig('NAMA_KOTA'))
	->setCellValue('A3', NAMA_DINAS)
	->setCellValue('A5', 'INFORMASI DATA PEMBAYARAN')
	->setCellValue('A6', 'Nomor Objek Pajak')
	->setCellValue('E6', 'Tahun Ketetapan')
	->setCellValue('A11', 'Nama Wajib Pajak')
	->setCellValue('A12', 'Alamat Wajib Pajak')
	->setCellValue('A10', 'Alamat Objek Pajak')
	->setCellValue('A9', 'Kecamatan Objek Pajak')
	->setCellValue('E9', 'Kelurahan Objek Pajak')
	->setCellValue('A7', 'Luas Bumi')
	->setCellValue('E7', 'NJOP Bumi')
	->setCellValue('A8', 'Luas Bangunan')
	->setCellValue('E8', 'NJOP Bangunan')
	->setCellValue('A13', 'Tanggal Printout');
$idx = ($sumRows - 1);
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('B6', ": " . substr($dt[$idx]['NOP'], 0, 2) . "." . substr($dt[$idx]['NOP'], 2, 2) . "." . substr($dt[$idx]['NOP'], 4, 3) . "." . substr($dt[$idx]['NOP'], 7, 3) . "." . substr($dt[$idx]['NOP'], 10, 3) . '-' . substr($dt[$idx]['NOP'], 13, 4) . "." . substr($dt[$idx]['NOP'], 17, 1))
	->setCellValue('F6', ": " . $dt[$idx]['SPPT_TAHUN_PAJAK'])
	->setCellValue('B11', ": " . $dt[$idx]['WP_NAMA'])
	->setCellValue('B12', ": " . $dt[$idx]['WP_ALAMAT'])
	->setCellValue('B10', ": " . $dt[$idx]['OP_ALAMAT'])
	->setCellValue('B9', ": " . $dt[$idx]['OP_KECAMATAN'])
	->setCellValue('F9', ": " . $dt[$idx]['OP_KELURAHAN'])
	->setCellValue('B7', ": " . $dt[$idx]['OP_LUAS_BUMI'] . " m2")
	//->setCellValue('B14', ": ".$dt[$idx]['OP_NJOP_BUMI']."/m2")
	->setCellValue('F7', ": " . number_format($dt[$idx]['OP_NJOP_BUMI'] / $dt[$idx]['OP_LUAS_BUMI'], 2, ",", ".") . "/m2")
	->setCellValue('B8', ": " . $dt[$idx]['OP_LUAS_BANGUNAN'] . " m2")
	//->setCellValue('B16', ": ".$dt[$idx]['OP_NJOP_BANGUNAN']."/m2")
	->setCellValue('F8', ": " . number_format($dt[$idx]['OP_NJOP_BANGUNAN'] / $dt[$idx]['OP_LUAS_BANGUNAN'], 2, ",", ".") . "/m2")
	->setCellValue('B13', ": " . date("d/m/Y"));

//style header cop
$objPHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('A2:A5')->applyFromArray($bold);

$objPHPExcel->getActiveSheet()->mergeCells('A2:G2')
	->mergeCells('A3:G3')
	->mergeCells('A5:G5')
	->mergeCells('B6:D6')
	->mergeCells('F6:G6')
	->mergeCells('B7:D7')
	->mergeCells('F7:G7')
	->mergeCells('B8:D8')
	->mergeCells('F8:G8')
	->mergeCells('B9:D9')
	->mergeCells('F9:G9')
	->mergeCells('B10:G10')
	->mergeCells('B11:G11')
	->mergeCells('B12:G12')
	->mergeCells('B13:G13')
	->mergeCells('A14:G14');

//create Header
$start = 15;
$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A' . $start, ($nop == "" ? "NOP" : "NAMA WAJIB PAJAK"))
	->setCellValue('B' . $start, 'TAHUN PAJAK')
	->setCellValue('C' . $start, 'PBB')
	->setCellValue('D' . $start, 'DENDA(*)')
	->setCellValue('E' . $start, 'JATUH TEMPO')
	->setCellValue('F' . $start, 'KURANG BAYAR')
	->setCellValue('G' . $start, 'STATUS BAYAR');
//style header
$objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':G' . $start)->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':G' . $start)->applyFromArray($bold);

//border table
$objPHPExcel->getActiveSheet()->getStyle('A' . $start . ':G' . ($start + $sumRows))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

//create data			
$no		= 1;
foreach ($dt as $dt) {
	// Jika berdasarkan NOP
	if ($nop == "") {
		$dtNOP = substr($dt['NOP'], 0, 2) . '.' . substr($dt['NOP'], 2, 2) . '.' . substr($dt['NOP'], 4, 3) . '.' . substr($dt['NOP'], 7, 3) . '.' . substr($dt['NOP'], 10, 3) . '-' . substr($dt['NOP'], 13, 4) . '.' . substr($dt['NOP'], 17, 1);
	} else { //Jika berdasarkan ID WP
		$dtNOP = $dt['WP_NAMA'];
	}
	$tglJatuhTempo = substr($dt['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . "/" . substr($dt['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) . "/" . substr($dt['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);

	$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no), $dtNOP);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . ($start + $no), $dt['SPPT_TAHUN_PAJAK']);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . ($start + $no), $dt['SPPT_PBB_HARUS_DIBAYAR_XLS']);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . ($start + $no), number_format($dt['DENDA_XLS'], 0, "", ""));
	$objPHPExcel->getActiveSheet()->setCellValue('E' . ($start + $no), $tglJatuhTempo);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . ($start + $no), number_format($dt['SPPT_PBB_HARUS_DIBAYAR_XLS'] + $dt['DENDA_XLS'], 0, "", ""));
	$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no), $dt['STATUS_XLS']);
	$no++;
}

//style data		
$objPHPExcel->getActiveSheet()->getStyle('B' . ($start) . ':B' . ($start + $sumRows))->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('E' . ($start) . ':E' . ($start + $sumRows))->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('G' . ($start) . ':G' . ($start + $sumRows))->applyFromArray($center);

//data summary
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 1) . ':F' . ($start + $no + 1));
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 2) . ':F' . ($start + $no + 2));
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 3) . ':F' . ($start + $no + 3));
$objPHPExcel->getActiveSheet()->mergeCells('A' . ($start + $no + 4) . ':F' . ($start + $no + 4));
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 1), 'TOTAL PBB YANG BELUM DIBAYAR');
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 2), 'TOTAL DENDA (SESUAI TANGGAL PRINTOUT)');
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 3), 'JUMLAH YANG HARUS DIBAYAR');
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 1), $dt['SUM_TOTAL_XLS']);
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 2), number_format($dt['SUM_DENDA_XLS'], 0, "", ""));
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 3), number_format(($dt['SUM_TOTAL_XLS'] + $dt['SUM_DENDA_XLS']), 0, "", ""));
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 4), '*Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.');
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 6), 'Petugas');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($start + $no + 6), ': ..................................................');
$objPHPExcel->getActiveSheet()->setCellValue('A' . ($start + $no + 7), 'Keperluan');
$objPHPExcel->getActiveSheet()->setCellValue('B' . ($start + $no + 7), ': ..................................................');

//Set TTD
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 6), getConfig('NAMA_KOTA_PENGESAHAN') . ', ' . date('d') . ' ' . $bulan[date('m')] . ' ' . date('Y'));
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 10), getConfig('KABID_NAMA'));
$objPHPExcel->getActiveSheet()->setCellValue('G' . ($start + $no + 11), " " . getConfig('KABID_NIP'));
//style TTD
$objPHPExcel->getActiveSheet()->getStyle('G' . ($start + $no + 6) . ':G' . ($start + $no + 11))->applyFromArray($center);

//border summary
$objPHPExcel->getActiveSheet()->getStyle('A' . ($start + $no + 1) . ':G' . ($start + $no + 3))->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		)
	)
);

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Data Pembayaran');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
// $objPHPExcel->setActiveSheetIndex(0);

// $gdImage = imagecreatefromjpeg('logo.jpg');
// // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
// $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
// $objDrawing->setName('logo');$objDrawing->setDescription('logo');
// $objDrawing->setImageResource($gdImage);
// $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
// $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
// $objDrawing->setHeight(40);
// $objDrawing->setCoordinates('A2');
// $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="data_pembayaran.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
