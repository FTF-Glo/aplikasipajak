<?php
/** PHPExcel */
require_once '../Classes/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();



// Header
$objRichText = new PHPExcel_RichText();
$objRichText->createText('Influencer List');
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Topic');
$objPHPExcel->getActiveSheet()->setCellValue('B2', ':');
$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Periode');
$objPHPExcel->getActiveSheet()->setCellValue('B3', ':');
$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Sum Of Influencer');
$objPHPExcel->getActiveSheet()->setCellValue('B4', ':');

// Header Of Table
$objRichTextHT1 = new PHPExcel_RichText();
$objRichTextHT1->createText('Name');
$objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichTextHT1);
$objPHPExcel->getActiveSheet()->mergeCells('A6:A7');

$objRichTextHT2 = new PHPExcel_RichText();
$objRichTextHT2->createText('Media');
$objPHPExcel->getActiveSheet()->getCell('B6')->setValue($objRichTextHT2);
$objPHPExcel->getActiveSheet()->mergeCells('B6:B7');

$objRichTextHT = new PHPExcel_RichText();
$objRichTextHT->createText('Sentiment');
$objPHPExcel->getActiveSheet()->getCell('C6')->setValue($objRichTextHT);
$objPHPExcel->getActiveSheet()->mergeCells('C6:E6');
$objPHPExcel->getActiveSheet()->setCellValue('C7', 'Positive');
$objPHPExcel->getActiveSheet()->setCellValue('D7', 'Neutral');
$objPHPExcel->getActiveSheet()->setCellValue('E7', 'Negative');

$objRichTextHT6 = new PHPExcel_RichText();
$objRichTextHT6->createText('Statement');
$objPHPExcel->getActiveSheet()->getCell('F6')->setValue($objRichTextHT6);
$objPHPExcel->getActiveSheet()->mergeCells('F6:F7');

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A6:F7')->applyFromArray(
    array(
        'font'    => array(
            'bold' => true
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

// Set fonts
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('Arial');
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

// Set alignments
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="01simple.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>