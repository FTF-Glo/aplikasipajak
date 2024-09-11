<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

try {
    ini_set('memory_limit', '500M');
    ini_set("max_execution_time", "100000000");
    //session_start();
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembatalan-sppt', '', dirname(__FILE__))) . '/';

    /** Error reporting */
    // error_reporting(E_ALL);

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
    require_once("classPembatalan.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }
    $dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
    $json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    $setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

    $tahun_range = @isset($_REQUEST['tahun_range']) ? $_REQUEST['tahun_range'] : 1;
    $q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
    $p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
    $jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
    $thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
    $nop1 = @isset($_REQUEST['n1']) ? $_REQUEST['n1'] : "";
    $nop2 = @isset($_REQUEST['n2']) ? $_REQUEST['n2'] : "";
    $nop3 = @isset($_REQUEST['n3']) ? $_REQUEST['n3'] : "";
    $nop4 = @isset($_REQUEST['n4']) ? $_REQUEST['n4'] : "";
    $nop5 = @isset($_REQUEST['n5']) ? $_REQUEST['n5'] : "";
    $nop6 = @isset($_REQUEST['n6']) ? $_REQUEST['n6'] : "";
    $nop7 = @isset($_REQUEST['n7']) ? $_REQUEST['n7'] : "";
    $na = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
    $status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
    $total = @isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;

    $nmFile = "Data-WP-Sudah-Bayar";
    if ($status == 2) {
        $nmFile = "Data-WP-Belum-Bayar";
    }

    $tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
    $tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
    $kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
    $kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
    $tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
    $export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
    $bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";

    if ($q == "") exit(1);
    $q = base64_decode($q);

    $j = $json->decode($q);
    $uid = $j->uid;
    $area = $j->a;
    $moduleIds = $j->m;

    $host = $_REQUEST['GW_DBHOST'];
    $port = $_REQUEST['GW_DBPORT'];
    $user = $_REQUEST['GW_DBUSER'];
    $pass = $_REQUEST['GW_DBPWD'];
    $dbname = $_REQUEST['GW_DBNAME'];
    // echo "<pre>";
    // print_r($_REQUEST);
    // echo "</pre>";

    $svcPembatalan = new SvcPembatalanSPPT($dbSpec);
    $svcPembatalan->C_HOST_PORT = $host;
    $svcPembatalan->C_PORT = $port;
    $svcPembatalan->C_USER = $user;
    $svcPembatalan->C_PWD = $pass;
    $svcPembatalan->C_DB = $dbname;
    $arrWhere = array();
    if ($nop1 != "") array_push($arrWhere, "SUBSTR(nop, 1, 2) = '{$nop1}'");
    if ($nop2 != "") array_push($arrWhere, "SUBSTR(nop, 3, 2) = '{$nop2}'");
    if ($nop3 != "") array_push($arrWhere, "SUBSTR(nop, 5, 3) = '{$nop3}'");
    if ($nop4 != "") array_push($arrWhere, "SUBSTR(nop, 8, 3) = '{$nop4}'");
    if ($nop5 != "") array_push($arrWhere, "SUBSTR(nop, 11, 3) = '{$nop5}'");
    if ($nop6 != "") array_push($arrWhere, "SUBSTR(nop, 14, 4) = '{$nop6}'");
    if ($nop7 != "") array_push($arrWhere, "SUBSTR(nop, 18, 1) = '{$nop7}'");
    $where = implode(" AND ", $arrWhere);
    if (stillInSession($DBLink, $json, $sdata)) {
        $res = $svcPembatalan->getRiwayatPembatalan($nop, "");
        $rowCount = mysqli_num_rows($res);
        // while ($row = mysqli_fetch_assoc($res)){
        // echo $row['NOP'];
        // echo "<br>";
        // }  


        // print_r($svcPembatalan);
    } else {
        echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
    }
    // exit;
?>
<?php
    // <textarea style="width: 100%;height: 100%"><?php echo $monPBB->getAllQuery() </textarea> -->
    // echo "masu";
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set properties
    $objPHPExcel->getProperties()->setCreator("vpost")
        ->setLastModifiedBy("vpost")
        ->setTitle("Alfa System")
        ->setSubject("Alfa System pbb")
        ->setDescription("pbb")
        ->setKeywords("Alfa System");

    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'No.')
        ->setCellValue('B1', 'NOP.')
        ->setCellValue('C1', 'Nama WP')
        ->setCellValue('D1', 'Alamat WP')
        ->setCellValue('E1', 'Alamat OP')
        ->setCellValue('F1', 'Tahun')
        ->setCellValue('G1', 'Piutang');
    $objPHPExcel->setActiveSheetIndex(0);
    // // echo $monPBB->getAllQuery();
    // // print_r($result);
    $row = 2;
    // $sumRows = mysqli_num_rows($result['data']);
    // echo "<pre>";
    // print_r($_REQUEST);
    // echo "</pre>";

    // $totalPokok = $totalDenda = $totalBayar = 0;
    while ($rowData = mysqli_fetch_assoc($res)) {
        // $tgl_jth_tempo = explode('-', $rowData['sppt_tanggal_jatuh_tempo']);
        // if(count($tgl_jth_tempo) == 3 )
        //      $tgl_jth_tempo = $tgl_jth_tempo[2].'-'.$tgl_jth_tempo[1].'-'.$tgl_jth_tempo[0];

        // $payment_date = '';
        // if($rowData['payment_paid'] != null && $rowData['payment_paid'] != '')
        //     $payment_date = substr($rowData['payment_paid'], 8, 2).'-'.substr($rowData['payment_paid'], 5, 2).'-'.substr($rowData['payment_paid'], 0, 4).' '.substr($rowData['payment_paid'], 11);
        // echo $rowData['sppt_tahun_pajak'] ."<br>";

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['NOP'] . " "));
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['WP_NAMA']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['WP_ALAMAT']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['OP_ALAMAT']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['SPPT_TAHUN_PAJAK']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['SPPT_PBB_HARUS_DIBAYAR']);

        // $objPHPExcel->getActiveSheet()->setCellValue('AF1'.$row, $rowData['op_kelurahan_kode']);
        // $objPHPExcel->getActiveSheet()->setCellValue('AG1'.$row, $rowData['op_kotakab_kode']);
        // $objPHPExcel->getActiveSheet()->setCellValue('AH1'.$row, $rowData['op_provinsi_kode']);
        // $objPHPExcel->getActiveSheet()->setCellValue('M'.$row, ' '.number_format($rowData['op_njop_bumi'],0,',','.'));


        $row++;
        // $totalPokok += $rowData['sppt_pbb_harus_dibayar'];
        // $totalDenda += $rowData['pbb_denda'];
        // $totalBayar += $rowData['pbb_total_bayar'];
    }

    // $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, 'JUMLAH');
    // $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, ' '.number_format($totalPokok,0,',','.'));
    // $objPHPExcel->getActiveSheet()->setCellValue('R'.$row, ' '.number_format($totalDenda,0,',','.'));
    // $objPHPExcel->getActiveSheet()->setCellValue('S'.$row, ' '.number_format($totalBayar,0,',','.'));
    // $objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':P'.$row);


    // // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle('Daftar WP');

    // //----set style cell
    // // if($status == 1)
    // //     $lastColumn = 'V';
    // // else $lastColumn = 'S';
    //style header
    // $objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->applyFromArray(
    //     array(
    //         'font'    => array(
    //             'bold' => true
    //         ),
    //         'alignment' => array(
    //             'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    //             'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    //         )
    //     )
    // );
    // $objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastColumn.($sumRows+2))->applyFromArray(
    //     array(
    //         'borders' => array(
    //             'allborders' => array(
    //                 'style' => PHPExcel_Style_Border::BORDER_THIN
    //             )
    //         )
    //     )
    // );
    // $objPHPExcel->getActiveSheet()->getStyle('I2:L'.($sumRows+2))->applyFromArray(
    //     array(
    //         'alignment' => array(
    //             'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    //             'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    //         )
    //     )
    // );

    // // $objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    // // $objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastColumn.'1')->getFill()->getStartColor()->setRGB('E4E4E4');

    // // $objPHPExcel->getActiveSheet()->getStyle('A2:B'.($sumRows+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // // $objPHPExcel->getActiveSheet()->getStyle('N2:N'.($sumRows+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    // // $objPHPExcel->getActiveSheet()->getStyle('M2:M'.($sumRows+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    // // $objPHPExcel->getActiveSheet()->getStyle('O2:O'.($sumRows+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // // $objPHPExcel->getActiveSheet()->getStyle('P2:P'.($sumRows+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // // $objPHPExcel->getActiveSheet()->getStyle('Q2:Q'.($sumRows+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    // // $objPHPExcel->getActiveSheet()->getStyle('R2:R'.($sumRows+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    // // $objPHPExcel->getActiveSheet()->getStyle('S2:S'.($sumRows+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    // // i
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // $objPHPExcel->getActiveSheet()->getColumnDimension('AF1')->setAutoSize(true);
    // $objPHPExcel->getActiveSheet()->getColumnDimension('AG1')->setAutoSize(true);
    // $objPHPExcel->getActiveSheet()->getColumnDimension('AH1')->setAutoSize(true);


    // echo "123";
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    if ($p != 'all')
        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
    else
        header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');

    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
} catch (Exception $e) {
    echo $e;
}
?>
