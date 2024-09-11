<?php
    ini_set('memory_limit','400M');
    ini_set ("max_execution_time", "1000");
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
    require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");

    
    error_reporting(E_ALL);

    date_default_timezone_set('Asia/Jakarta');

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    //akses database gateway devel

    //akses database gateway devel
    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_REQUEST['dbhost'],$_REQUEST['dbuser'],$_REQUEST['dbpwd'],$_REQUEST['dbname']);
    $nm = @isset($_REQUEST['nm']) ? $_REQUEST['nm'] : "";
    $tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "";
    $kec = @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : "";
    $kel = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
    $status = @isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
    $sp1 = @isset($_REQUEST['sp1']) ? $_REQUEST['sp1'] : "";
    $sp2 = @isset($_REQUEST['sp1']) ? $_REQUEST['sp2'] : "";
    $sp3 = @isset($_REQUEST['sp1']) ? $_REQUEST['sp3'] : "";
    $tahun = @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
    $nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
    $lblkel = @isset($_REQUEST['lblkel']) ? $_REQUEST['lblkel'] : "Kelurahan";

    
    function conditionBuilder(){
        global $nm, $tagihan, $kec, $kel, $tahun, $nop;

        $condQuery = "";

        if($nm) $condQuery .= " AND (WP_NAMA LIKE '%$nm%') ";
        if($tagihan){
            switch ($tagihan){
                case 1 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR < 5000000) "; break;
                case 2 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND SPPT_PBB_HARUS_DIBAYAR < 10000000) "; break;
                case 3 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND SPPT_PBB_HARUS_DIBAYAR < 20000000) "; break;
                case 4 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND SPPT_PBB_HARUS_DIBAYAR < 30000000) "; break;
                case 5 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND SPPT_PBB_HARUS_DIBAYAR < 40000000) "; break;
                case 6 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND SPPT_PBB_HARUS_DIBAYAR < 50000000) "; break;
                case 7 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND SPPT_PBB_HARUS_DIBAYAR < 100000000) "; break;
                case 8 : $condQuery .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100000000) "; break;
            }
        }
        if($kel) $condQuery .= " AND (NOP like '$kel%') ";
        else if($kec) $condQuery .= " AND (NOP like '$kec%') ";
        
        if($tahun) $condQuery .= " AND (SPPT_TAHUN_PAJAK ='".$tahun."') ";
        if($nop) $condQuery .= " AND (NOP like'%".$nop."%') ";
            
        return $condQuery;
    }

    $where = conditionBuilder();
    $query ="";

    $qrangetime = "";
    $postfixNameFile = "";
    switch($status){
        case 1 : $qrangetime = " AND (TGL_SP1 = '' OR TGL_SP1 IS NULL) ";
                 $postfixNameFile = "SP1";
                 break;
        case 2 : $qrangetime = " AND DATEDIFF(CURDATE(), DATE(TGL_SP1)) >= $sp2 AND (TGL_SP2 = '' OR TGL_SP2 IS NULL) ";
                 $postfixNameFile = "SP2";
                 break;
        case 3 : $qrangetime = " AND DATEDIFF(CURDATE(), DATE(TGL_SP2)) >= $sp3 AND (TGL_SP3 = '' OR TGL_SP3 IS NULL) ";
                 $postfixNameFile = "SP3";
                 break;
        case 4 : $qrangetime = " AND SUBSTR(STATUS_SP,1,2) = 'SP' ";
                 $postfixNameFile = "ALLSP";
                 break;
        case 5 : $postfixNameFile = "STPD";
                 break;
    }

    $query = "SELECT NOP,
            SPPT_TAHUN_PAJAK, WP_NAMA, WP_KELURAHAN, WP_ALAMAT, OP_ALAMAT, OP_KECAMATAN, OP_KELURAHAN,
            OP_RT, OP_RW, OP_LUAS_BUMI, OP_LUAS_BANGUNAN, OP_NJOP_BUMI, OP_NJOP_BANGUNAN, SPPT_TANGGAL_JATUH_TEMPO, SPPT_PBB_HARUS_DIBAYAR,
            TGL_STPD, KETERANGAN_STPD, KETERANGAN_SP 
            FROM VIEW_PBB_SPPT_PENAGIHAN WHERE SPPT_TAHUN_PAJAK >= '2007' $qrangetime $where ORDER BY WP_NAMA ASC, SPPT_TAHUN_PAJAK DESC";
//echo $where; exit();
    $data = mysqli_query($DBLinkLookUp, $query);
    
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();



    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'NO');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Nomor Objek Pajak');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Nama WP');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Alamat WP');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', $lblkel.' WP');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Alamat OP');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Kecamatan OP');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', $lblkel.' OP');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'RT OP');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'RW OP');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Luas Bumi');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Luas Bangunan');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Tot NJOP Bumi');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Tot NJOP Bangunan');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Tahun Pajak');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Tanggal Jatuh Tempo');
    $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Tagihan');


    $ctr=2;
    while($tmp = mysqli_fetch_assoc($data)){
        $tgltempo = explode("-",$tmp['SPPT_TANGGAL_JATUH_TEMPO']);

        $objPHPExcel->getActiveSheet()->setCellValue('A'.$ctr, ($ctr-1));
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$ctr, $tmp['NOP']." ");
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$ctr, $tmp['WP_NAMA']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$ctr, $tmp['WP_ALAMAT']);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$ctr, $tmp['WP_KELURAHAN']);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$ctr, $tmp['OP_ALAMAT']);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$ctr, $tmp['OP_KECAMATAN']);
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$ctr, $tmp['OP_KELURAHAN']);
        $objPHPExcel->getActiveSheet()->setCellValue('I'.$ctr, $tmp['OP_RT']);
        $objPHPExcel->getActiveSheet()->setCellValue('J'.$ctr, $tmp['OP_RW']);
        $objPHPExcel->getActiveSheet()->setCellValue('K'.$ctr, $tmp['OP_LUAS_BUMI']);
        $objPHPExcel->getActiveSheet()->setCellValue('L'.$ctr, $tmp['OP_LUAS_BANGUNAN']);
        $objPHPExcel->getActiveSheet()->setCellValue('M'.$ctr, $tmp['OP_NJOP_BUMI']);
        $objPHPExcel->getActiveSheet()->setCellValue('N'.$ctr, $tmp['OP_NJOP_BANGUNAN']);
        $objPHPExcel->getActiveSheet()->setCellValue('O'.$ctr, $tmp['SPPT_TAHUN_PAJAK']);
        $objPHPExcel->getActiveSheet()->setCellValue('P'.$ctr, ($tgltempo[2]."-".$tgltempo[1]."-".$tgltempo[0]));
        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$ctr, $tmp['SPPT_PBB_HARUS_DIBAYAR']);
        $ctr++;
    }

    // Set style for header row using alternative method
    $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray(
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
    $objPHPExcel->getActiveSheet()->getStyle('A2:Q'.($ctr-1))->applyFromArray(
        array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        )
    );

    // Set column widths
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
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);

    // Set page orientation and size
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Daftar-Tagihan-'.$postfixNameFile.'.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
?>