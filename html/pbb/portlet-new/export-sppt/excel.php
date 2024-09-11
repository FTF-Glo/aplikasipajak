<?php
ob_clean();

$PHPExcel = $portlet->getExcel(new PHPExcel());
$properties = $portlet->get('excelProperties');

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'. $properties['filename'] .'_'. date('YmdHis') .'.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0


$PHPExceLWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
$PHPExceLWriter->save('php://output');
exit;
