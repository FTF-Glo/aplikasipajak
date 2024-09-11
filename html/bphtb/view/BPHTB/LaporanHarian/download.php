<?php
$sRoot = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB'. DIRECTORY_SEPARATOR . 'LaporanHarian', '', dirname(__FILE__))) . '/';
 define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
require_once($sRoot . "inc/payment/db-payment.php");
require_once($sRoot . "inc/payment/constant.php");
require_once($sRoot . "inc/payment/inc-payment-c.php");
require_once($sRoot . "inc/payment/inc-payment-db-c.php");  
require_once($sRoot.'phpexcel/Classes/PHPExcel.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, 'SW_SSB_O2W_DEMO',true);
         $objPHPExcel = new PHPExcel();  
         
                        $objPHPExcel->getProperties()->setCreator("Bayu kusumah")
							 ->setLastModifiedBy("Bayu kusumah")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Tranfer Data");
                        $objPHPExcel->getActiveSheet()->setTitle("Laporan Harian BPHTB");
                        $styleArray = array(
       'borders' => array(
             'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                                ),
                        ),
        );
         date_default_timezone_set('Asia/Jakarta');               
         switch (date("w")) {
            case "0" : $hari="Minggu";break;
            case "1" : $hari="Senin";break;
            case "2" : $hari="Selasa";break;
            case "3" : $hari="Rabu";break;
            case "4" : $hari="Kamis";break;
            case "5" : $hari="Jumat";break;
            case "6" : $hari="Sabtu";break;
        } 
        switch (date("m")) {
            case "1" : $bulan="Januari";break;
            case "2" : $bulan="Februari";break;
            case "3" : $bulan="Maret";break;
            case "4" : $bulan="April";break;
            case "5" : $bulan="Mei";break;
            case "6" : $bulan="Juni";break;
            case "7" : $bulan="Juli";break;
            case "8" : $bulan="Agustus";break;
            case "9" : $bulan="September";break;
            case "10" : $bulan="Oktober";break;
            case "11" : $bulan="November";break;
            case "12" : $bulan="Desember";break;
        }               
         $hari = $hari .' '. date('d').' '. $bulan .' '.date('Y'); 
         if(isset($_GET['tgl'])){
             for ($col = 'A'; $col != 'I'; $col++) {
                   $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                            
              }
              $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
              $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
              $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getFont()->setBold(true);
              $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
              
              $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'LAPORAN BERKAS BPHTB YANG TELAH DITANDATANGANI');
              $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
              $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', $hari);
              $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
              
               $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('B4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('D4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('F4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('G4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($styleArray);
               $objPHPExcel->getActiveSheet()->getStyle('A4:H4')->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->getStyle('A4:H4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', 'No');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', 'Nama Wajib Pajak');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C4', 'Alamat Object Pajak');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D4', 'LUAS TANAH');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E4', 'LUAS BANGUNAN');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F4', 'HARGA TRANSAKSI (Rp)');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G4', 'BPHTB YANG DIBAYAR (Rp)');
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H4', 'JENIS PEROLEHAN HAK');
                $hak = array();
                $hak[1]= 'Jual Beli';
                $hak[2]= 'Tukar Menukar';
                $hak[3]= 'Hibah';
                $hak[4]= 'Hibah Wasiat Sedarah Satu Derajat';
                $hak[5]= 'Hibah Wasiat Non Sedarah Satu Derajat';
                $hak[6]= 'Waris';
                $hak[7]= 'Pemasukan dalam perseroan/badan hukum lainnya';
                $hak[8]= 'Pemisahan hak yang mengakibatkan peralihan';
                $hak[9]= 'Penunjukan pembeli dalam lelang';
                $hak[10]= 'Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap';
                $hak[12]= 'Penggabungan usaha';
                $hak[13]= 'Peleburan usaha';
                $hak[14]= 'Pemekaran usaha';
                $hak[15]= 'Hadiah';
                $hak[16]= 'Jual beli khusus perolehan hak Rumah Sederhana dan Rumah Susun Sederhana melalui KPR bersubsidi';
                $hak[17]= 'Pemberian hak baru sebagai kelanjutan pelepasan hak';
                $hak[18]= 'Pemberian hak baru diluar pelepasan hak';
               $qry = "SELECT B.CPM_WP_NAMA,B.CPM_WP_ALAMAT,B.CPM_OP_LUAS_TANAH,B.CPM_OP_LUAS_BANGUN,
                       B.CPM_OP_HARGA,(B.CPM_SSB_AKUMULASI-B.CPM_OP_HARGA) AS BAYAR,CPM_OP_JENIS_HAK
                       FROM cppmod_ssb_tranmain A JOIN cppmod_ssb_doc B 
                       ON (B.CPM_SSB_ID = A.CPM_TRAN_SSB_ID) 
                       WHERE A.CPM_TRAN_STATUS='5' AND A.CPM_TRAN_DATE LIKE '%{$_GET['tgl']}%'";
              $result = mysqli_query($qry);
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
                   $objPHPExcel->getActiveSheet()->getStyle('H'.$count)->applyFromArray($styleArray);
                   $objPHPExcel->getActiveSheet()->getStyle('F'.$count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                   $objPHPExcel->getActiveSheet()->getStyle('G'.$count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                   $objPHPExcel->getActiveSheet()->getStyle('H'.$count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$count, $i);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$count, $hasil['CPM_WP_NAMA']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$count, $hasil['CPM_WP_ALAMAT']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$count, $hasil['CPM_OP_LUAS_TANAH']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$count, $hasil['CPM_OP_LUAS_BANGUN']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$count, number_format($hasil['CPM_OP_HARGA'], 0, ",", "."));
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$count, number_format($hasil['BAYAR'], 0, ",", "."));
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$count, $hak[$hasil['CPM_OP_JENIS_HAK']]);
                  $i++;  
                  $count++;
              }
              $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
              $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                        
              header('Content-Type: application/vnd.ms-excel');
              header('Content-Disposition: attachment;filename="Laporan Harian.xls"');
              header('Cache-Control: max-age=0');

              $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
              $objWriter->save('php://output'); 
              exit;
         }                
?>