<?php
$sRootPath = str_replace('\\', '/', str_replace('', '', dirname(__FILE__))) . '/';
require_once("Classes/PHPExcel.php");

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
                             ->setTitle("-")
                             ->setSubject("-")
                             ->setDescription("pbb")
                             ->setKeywords("-");

// Header
$objRichText = new PHPExcel_RichText();

$objRichText->createText(': KETETAPAN DAN REALISASI PBB TAHUN ANGGARAN '.$thn);
$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D1:J1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': I s/d III');
$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D2:J2');
$objRichText = new PHPExcel_RichText();
if($eperiode > 1)
	$objRichText->createText(': JANUARI s/d '.strtoupper($bulan[$eperiode-1]).' '.$thn);
else $objRichText->createText(': JANUARI  '.$thn);
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:J3'); 


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'BUKU');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BULAN');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Model E.2');

$objPHPExcel->getActiveSheet()->getStyle('C1:L3')->applyFromArray(
    array('font'    => array('size'=>$fontSizeHeader))
);

        

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
$objRichText = new PHPExcel_RichText();

if ($kecamatan=="") {
	$objRichText->createText('KECAMATAN');
} else {
	$objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
}

$objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KETETAPAN');
$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C8:D8');
$objPHPExcel->getActiveSheet()->setCellValue('C9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('D9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI BULAN LALU');
$objPHPExcel->getActiveSheet()->getCell('E8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E8:F8');
$objPHPExcel->getActiveSheet()->setCellValue('E9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('F9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('G8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('G8:G9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI BULAN INI');
$objPHPExcel->getActiveSheet()->getCell('H8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('H8:I8');
$objPHPExcel->getActiveSheet()->setCellValue('H9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('I9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI s/d BULAN INI');
$objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J8:K8');
$objPHPExcel->getActiveSheet()->setCellValue('J9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('K9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('L8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('L8:L9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('SISA KETETAPAN');
$objPHPExcel->getActiveSheet()->getCell('M8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M8:N8');
$objPHPExcel->getActiveSheet()->setCellValue('M9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('N9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('O8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('O8:O9');


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A8:O9')->applyFromArray(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:P50')->applyFromArray(
    array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(7);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no=1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporting_model_e2.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>