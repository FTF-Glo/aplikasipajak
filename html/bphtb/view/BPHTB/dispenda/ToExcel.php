<?php

ini_set("display_error",1);
ini_set("error_reporting","E_ALL");
    $sRoot = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB'. DIRECTORY_SEPARATOR . 'dispenda', '', dirname(__FILE__))) . '/';
 define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
require_once($sRoot . "inc/payment/db-payment.php");
require_once($sRoot . "inc/payment/constant.php");
require_once($sRoot . "inc/payment/inc-payment-c.php");
require_once($sRoot . "inc/payment/inc-payment-db-c.php");  
require_once($sRoot . 'phpexcel/Classes/PHPExcel.php');

function getBPHTBPayment($lb, $nb, $lt, $nt, $h, $p, $jh, $NPOPTKP) {
    //$a = $_REQUEST['a'];
    /* $NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_STANDAR');

      $typeR = $jh;

      if (($typeR==4) || ($typeR==6)){
      $NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_WARIS');
      } else {

      } */

    /* if($this->getNOKTP($noktp,$nop,$tgl)) {	
      $NPOPTKP = 0;
      } */

    $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
    $b = strval($h);
    $npop = 0;
    if ($b < $a)
	$npop = $a;
    else
	$npop = $b;

    $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
    $tp = strval($p);
    if ($tp != 0)
	$jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

    if ($jmlByr < 0)
	$jmlByr = 0;
    return $jmlByr;
}
    
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("Bayu kusumah")
            ->setLastModifiedBy("Bayu kusumah")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Tranfer Data");
                        $objPHPExcel->getActiveSheet()->setTitle("verifikasi BPHTB");
                        $styleArray = array(
       'borders' => array(
             'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                                ),
                        ),
        );     
for ($col = 'A'; $col != 'H'; $col++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
	     
}
$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'LAPORAN VERIFIKASI BPHTB');
$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');


$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('F4')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($styleArray);

$objPHPExcel->getActiveSheet()->getStyle('A4:G4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A4:G4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', 'No');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', 'Nomor Object Pajak');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C4', 'Wajib Pajak');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D4', 'Alamat Object Pajak');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E4', 'User Pelapor');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F4', 'BPHTB yang harus dibayar');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G4', 'Tanggal');


 
$qry = "SELECT B.CPM_OP_NOMOR,B.CPM_WP_NAMA,B.CPM_OP_LETAK,B.CPM_SSB_AUTHOR,B.CPM_SSB_CREATED,
	B.CPM_OP_LUAS_BANGUN,B.CPM_OP_NJOP_BANGUN,B.CPM_OP_LUAS_TANAH,B.CPM_OP_NJOP_TANAH,B.CPM_OP_HARGA,
	B.CPM_PAYMENT_TIPE_PENGURANGAN,B.CPM_OP_JENIS_HAK,B.CPM_OP_NPOPTKP
	FROM cppmod_ssb_tranmain A JOIN cppmod_ssb_doc B 
	ON (B.CPM_SSB_ID = A.CPM_TRAN_SSB_ID) 
	WHERE A.CPM_TRAN_STATUS='5' LIMIT 0, 10";
	
$result = mysqli_query($DBLink, $qry);

$count=5;
$i=1;
while($hasil = mysqli_fetch_array($result)){
    
    $objPHPExcel->getActiveSheet()->getStyle('A'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('C'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('E'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('G'.$count)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
     $bayar = getBPHTBPayment($hasil[CPM_OP_LUAS_BANGUN],$hasil[CPM_OP_NJOP_BANGUN],$hasil[CPM_OP_LUAS_TANAH],
	 $hasil[CPM_OP_NJOP_TANAH],$hasil[CPM_OP_HARGA],$hasil[CPM_PAYMENT_TIPE_PENGURANGAN],$hasil[CPM_OP_JENIS_HAK],$hasil[CPM_OP_NPOPTKP]);
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$count, $i);
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$count, $hasil['CPM_OP_NOMOR'].' ');
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$count, $hasil['CPM_WP_NAMA']);
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$count, $hasil['CPM_OP_LETAK']);
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$count, $hasil['CPM_SSB_AUTHOR']);                    
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$count, number_format($bayar, 0, ",", "."));
     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$count, $hasil['CPM_SSB_CREATED']);
   $i++;  
   $count++;
}
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	 
	 
	 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Verifikasi BPHTB.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output'); 
exit;               

?>