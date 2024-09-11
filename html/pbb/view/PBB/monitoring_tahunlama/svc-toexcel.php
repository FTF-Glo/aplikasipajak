<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_tahunlama', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

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
require_once($sRootPath . "inc/PBB/dbMonitoring.php");
require_once $sRootPath . 'portlet-new/Portlet.php';

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

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);
// print_r($_REQUEST);exit;
$q         = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p         = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml     = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn     = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
//$nop     = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$nop1 = @isset($_REQUEST['n1']) ? $_REQUEST['n1'] : "";
$nop2 = @isset($_REQUEST['n2']) ? $_REQUEST['n2'] : "";
$nop3 = @isset($_REQUEST['n3']) ? $_REQUEST['n3'] : "";
$nop4 = @isset($_REQUEST['n4']) ? $_REQUEST['n4'] : "";
$nop5 = @isset($_REQUEST['n5']) ? $_REQUEST['n5'] : "";
$nop6 = @isset($_REQUEST['n6']) ? $_REQUEST['n6'] : "";
$nop7 = @isset($_REQUEST['n7']) ? $_REQUEST['n7'] : "";
$na     = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$total     = @isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;
$nmFile = "Data-WP-Sudah-Bayar";
if ($status == 2) {
    $nmFile = "Data-WP-Belum-Bayar";
}
$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$rw        = @isset($_REQUEST['rw']) ? $_REQUEST['rw'] : "";
$tagihan     = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$buku         = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";
$export     = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$bank         = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$alamat        = @isset($_REQUEST['almt']) ? $_REQUEST['almt'] : "";

if ($q == "") exit(1);
$q = base64_decode($q);

$j             = $json->decode($q);
$uid         = $j->uid;
$area         = $j->a;
$moduleIds     = $j->m;

$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig     = $User->GetAppConfig($area);

$host     = $appConfig['GW_DBHOST'];
$port     = $appConfig['GW_DBPORT'];
$user     = $appConfig['GW_DBUSER'];
$pass     = $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];

$arrTempo = array();
if ($tempo1 != "") array_push($arrTempo, "A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "A.payment_paid<='{$tempo2} 23:59:59'");
if ($alamat != "") array_push($arrWhere, "A.OP_ALAMAT like '%{$alamat}%'");
$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();
if ($kecamatan != "") array_push($arrWhere, "A.nop like '{$kecamatan}%'");
if ($kelurahan != "") array_push($arrWhere, "A.nop like '{$kelurahan}%'");
if ($rw != "") {
    if ($rw == '000') array_push($arrWhere, "(A.OP_RW = '{$rw}' OR A.OP_RW = '' OR OP_RW IS NULL)");
    else array_push($arrWhere, "A.OP_RW = '{$rw}'");
}
//if ($nop != "") array_push($arrWhere, "A.nop LIKE '{$nop}%'");
if ($nop1 != "") array_push($arrWhere, "SUBSTR(A.nop, 1, 2) = '{$nop1}'");
if ($nop2 != "") array_push($arrWhere, "SUBSTR(A.nop, 3, 2) = '{$nop2}'");
if ($nop3 != "") array_push($arrWhere, "SUBSTR(A.nop, 5, 3) = '{$nop3}'");
if ($nop4 != "") array_push($arrWhere, "SUBSTR(A.nop, 8, 3) = '{$nop4}'");
if ($nop5 != "") array_push($arrWhere, "SUBSTR(A.nop, 11, 3) = '{$nop5}'");
if ($nop6 != "") array_push($arrWhere, "SUBSTR(A.nop, 14, 4) = '{$nop6}'");
if ($nop7 != "") array_push($arrWhere, "SUBSTR(A.nop, 18, 1) = '{$nop7}'");
if ($thn != "") array_push($arrWhere, "A.sppt_tahun_pajak='{$thn}'");
if ($na != "") array_push($arrWhere, "A.wp_nama like '%{$na}%'");
if ($status != "") {
    if ($status == 1) {
        array_push($arrWhere, "A.payment_flag = 1");
    } else {
        array_push($arrWhere, "(A.payment_flag != 1 OR A.payment_flag IS NULL)");
    }
}
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");

if ($tagihan != 0) {
    switch ($tagihan) {
        case 1:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR < 5000000) ");
            break;
        case 2:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 10000000) ");
            break;
        case 3:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 20000000) ");
            break;
        case 4:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 30000000) ");
            break;
        case 5:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 40000000) ");
            break;
        case 6:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 50000000) ");
            break;
        case 7:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND A.SPPT_PBB_HARUS_DIBAYAR < 100000000) ");
            break;
        case 8:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100000000) ");
            break;
        case 9:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR > 100000000) ");
            break;
    }
}

if ($buku != 0) {
    switch ($buku) {
        case 1:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
            break;
        case 12:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 123:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 1234:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 12345:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 2:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 23:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 234:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 2345:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 3:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 34:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 345:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 4:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 45:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 5:
            array_push($arrWhere, " (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
    }
}

if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");

$where = implode(" AND ", $arrWhere);
// echo $where; exit;
if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoring($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToMysql();
    if ($p == 'all') {
        $monPBB->setRowPerpage($total);
        $monPBB->setPage(1);
    } else {
        $monPBB->setRowPerpage(20000);
        $monPBB->setPage($p);
    }
    // $monPBB->setTable("PBB_SPPT");
    $a = "HITUNGDENDA('{$now}', DATE(SPPT_TANGGAL_JATUH_TEMPO), IFNULL(SPPT_PBB_HARUS_DIBAYAR, 0), ". Portlet::PBB_ONE_MONTH .", ". Portlet::PBB_MAXPENALTY_MONTH .", ". Portlet::PBB_PENALTY_PERCENT .")";
    $monPBB->setWhere($where);
    $monPBB->setStatus($status);
    if ($status == '1') {
        $query = "SELECT A.nop, A.wp_nama, A.wp_alamat, A.wp_kelurahan, A.op_alamat, A.op_kecamatan, A.op_kelurahan, A.op_rt, A.op_rw,
            A.op_luas_bumi, A.op_luas_bangunan,A.op_njop_bumi,A.op_njop_bangunan,A.sppt_tahun_pajak, 
            A.sppt_tanggal_jatuh_tempo , IFNULL(A.sppt_pbb_harus_dibayar,0) AS sppt_pbb_harus_dibayar, {$a} as pbb_denda , IFNULL(A.sppt_pbb_harus_dibayar+({$a}),0) as pbb_total_bayar, b.BTL_LOG_SK as SK, b.BTL_LOG_ALASAN as Alasan FROM pbb_sppt_pembatalan A INNER JOIN pbb_pembatalan_sppt_log B ON b.BTL_LOG_NOP = A.NOP AND B.BTL_LOG_TAHUN = A.SPPT_TAHUN_PAJAK ";
    } else {
        $query = "SELECT A.nop, A.wp_nama, A.wp_alamat, A.wp_kelurahan, A.op_alamat, A.op_kecamatan, A.op_kelurahan, A.op_rt, A.op_rw,
                A.op_luas_bumi, A.op_luas_bangunan,A.op_njop_bumi,A.op_njop_bangunan,A.sppt_tahun_pajak, 
                A.sppt_tanggal_jatuh_tempo , IFNULL(A.sppt_pbb_harus_dibayar,0) AS sppt_pbb_harus_dibayar, {$a} as pbb_denda , 
                IFNULL(A.sppt_pbb_harus_dibayar+({$a}),0) as pbb_total_bayar 
                FROM PBB_SPPT_TAHUNLAMA A LEFT JOIN PBB_DENDA B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";
    }
    // var_dump($query);exit;
    $result = $monPBB->query_result($query);
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("Alfa System")
    ->setSubject("Alfa System pbb")
    ->setDescription("pbb")
    ->setKeywords("Alfa System");


// Add some data
if ($status == '1') {
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'No.')
        ->setCellValue('B1', 'NOP')
        ->setCellValue('C1', 'Nama WP')
        ->setCellValue('D1', 'Alamat WP')
        ->setCellValue('E1', $_REQUEST['LBL_KEL'] . ' WP')
        ->setCellValue('F1', 'Alamat OP')
        ->setCellValue('G1', 'Kecamatan OP')
        ->setCellValue('H1', $_REQUEST['LBL_KEL'] . ' OP')
        ->setCellValue('I1', 'RT OP')
        ->setCellValue('J1', 'RW OP')
        ->setCellValue('K1', 'Luas Bumi')
        ->setCellValue('L1', 'Luas Bangunan')
        ->setCellValue('M1', 'Total NJOP Bumi')
        ->setCellValue('N1', 'Total NJOP Bangunan')
        ->setCellValue('O1', 'Tahun Pajak')
        ->setCellValue('P1', 'Tgl Jatuh Tempo')
        ->setCellValue('Q1', 'Pokok')
        ->setCellValue('R1', 'Denda')
        ->setCellValue('S1', 'Total')
        ->setCellValue('T1', 'Selisih')
        ->setCellValue('U1', 'Status')
        ->setCellValue('V1', 'Tanggal Bayar')
        ->setCellValue('W1', 'Bank');
} else {
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'No.')
        ->setCellValue('B1', 'NOP')
        ->setCellValue('C1', 'Nama WP')
        ->setCellValue('D1', 'Alamat WP')
        ->setCellValue('E1', $_REQUEST['LBL_KEL'] . ' WP')
        ->setCellValue('F1', 'Alamat OP')
        ->setCellValue('G1', 'Kecamatan OP')
        ->setCellValue('H1', $_REQUEST['LBL_KEL'] . ' OP')
        ->setCellValue('I1', 'RT OP')
        ->setCellValue('J1', 'RW OP')
        ->setCellValue('K1', 'Luas Bumi')
        ->setCellValue('L1', 'Luas Bangunan')
        ->setCellValue('M1', 'Total NJOP Bumi')
        ->setCellValue('N1', 'Total NJOP Bangunan')
        ->setCellValue('O1', 'Tahun Pajak')
        ->setCellValue('P1', 'Tgl Jatuh Tempo')
        ->setCellValue('Q1', 'Pokok')
        ->setCellValue('R1', 'Denda')
        ->setCellValue('S1', 'Total');
}
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 2;
$sumRows = mysqli_num_rows($result['data']);

$totalPokok = $totalDenda = $totalBayar = 0;
while ($rowData = mysqli_fetch_assoc($result['data'])) {
    $tgl_jth_tempo = explode('-', $rowData['sppt_tanggal_jatuh_tempo']);
    if (count($tgl_jth_tempo) == 3) $tgl_jth_tempo = $tgl_jth_tempo[2] . '-' . $tgl_jth_tempo[1] . '-' . $tgl_jth_tempo[0];

    $payment_date = '';
    if ($rowData['payment_paid'] != null && $rowData['payment_paid'] != '')
        $payment_date = substr($rowData['payment_paid'], 8, 2) . '-' . substr($rowData['payment_paid'], 5, 2) . '-' . substr($rowData['payment_paid'], 0, 4) . ' ' . substr($rowData['payment_paid'], 11);


    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['nop'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['wp_nama']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['wp_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['wp_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['op_alamat']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['op_kecamatan']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['op_kelurahan']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['op_rt']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['op_rw']);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['op_luas_bumi']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['op_luas_bangunan']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, ' ' . number_format($rowData['op_njop_bumi'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, ' ' . number_format($rowData['op_njop_bangunan'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['sppt_tahun_pajak']);
    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $tgl_jth_tempo);
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, ' ' . number_format($rowData['sppt_pbb_harus_dibayar'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, ' ' . number_format($rowData['pbb_denda'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, ' ' . number_format($rowData['pbb_total_bayar'], 0, ',', '.'));

    if ($status == '1') {
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, ' ' . number_format($rowData['selisih'], 0, ',', '.'));
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, ($rowData['payment_flag'] == '1') ? 'Lunas' : 'Terutang');
        $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $payment_date);
        $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $rowData['CDC_B_NAME']);
    }
    $row++;
    $totalPokok += $rowData['sppt_pbb_harus_dibayar'];
    $totalDenda += $rowData['pbb_denda'];
    $totalBayar += $rowData['pbb_total_bayar'];
    $totalSelisih += $rowData['selisih'];
}

$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'JUMLAH');
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, ' ' . number_format($totalPokok, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('R' . $row, ' ' . number_format($totalDenda, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('S' . $row, ' ' . number_format($totalBayar, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('T' . $row, ' ' . number_format($totalSelisih, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':P' . $row);


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar WP');

//----set style cell
if ($status == 1)
    $lastColumn = 'W';
else $lastColumn = 'S';
//style header
$objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->applyFromArray(
    array(
        'font'    => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . ($sumRows + 2))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle('I2:L' . ($sumRows + 2))->applyFromArray(
    array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A2:B' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('N2:N' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('O2:O' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('P2:P' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('Q2:Q' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('R2:R' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('S2:S' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
if ($status == '1') {
    $objPHPExcel->getActiveSheet()->getStyle('T2:T' . ($sumRows + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $objPHPExcel->getActiveSheet()->getStyle('U2:U' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('V2:V' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('W2:V' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
}

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
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
if ($p != 'all')
    header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
else header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
