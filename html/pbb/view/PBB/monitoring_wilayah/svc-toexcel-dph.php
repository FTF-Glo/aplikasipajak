<?php
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';

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
require_once("dbMonitoringDph.php");

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

$q              = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p              = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml            = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn            = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$thn2           = @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : 1;
$nop            = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na             = @isset($_REQUEST['na']) ? str_replace("%20", " ", $_REQUEST['na']) : "";
$status         = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$total          = @isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;

$nmFile         = "Data-WP-Sudah-Bayar";
if ($status == 2) {
    $nmFile = "Data-WP-Belum-Bayar";
}

$tempo1         = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2         = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan      = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan      = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$tagihan        = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$export         = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$bank           = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$DPH            = @isset($_REQUEST['noDph']) ? $_REQUEST['noDph'] : "0";

if ($q == "") exit(1);

$q              = base64_decode($q);
$j              = $json->decode($q);
$uid            = $j->uid;
$area           = $j->a;
$moduleIds      = $j->m;

$host           = $_REQUEST['GW_DBHOST'];
$port           = $_REQUEST['GW_DBPORT'];
$user           = $_REQUEST['GW_DBUSER'];
$pass           = $_REQUEST['GW_DBPWD'];
$dbname         = $_REQUEST['GW_DBNAME'];
function getdenda($jatuhtempo, $pokok)
{

    define("PBB_ONE_MONTH", 30);
    define("PBB_PENALTY_PERCENT", 2);
    define("PBB_MAXPENALTY_MONTH", 24);

    $thnJatuhTempo  = substr($jatuhtempo, 0, 4);
    $blnJatuhTempo  = substr($jatuhtempo, 5, 2);

    if (PBB_ONE_MONTH == 0) {
        if ((date('Y') == $thnJatuhTempo) && (date('m') > $blnJatuhTempo)) {
            $monthinterval = date('m') - substr($jatuhtempo, 5, 2);
        } else if (date('Y') > $thnJatuhTempo) {
            $monthinterval = ((date("Y") - $thnJatuhTempo - 1) * 12) + (11 - $blnJatuhTempo) + date("m") + 1;
        }
    } else {
        $dtjatuhtempo   = mktime(23, 59, 59, substr($jatuhtempo, 5, 2), substr($jatuhtempo, 8, 2), substr($jatuhtempo, 0, 4));
        $dtnow          = time();
        // $dayinterval = ceil(($dtnow-$dtjatuhtempo)/(24*60*60));
        $dayinterval    = ($dtnow - $dtjatuhtempo) / (24 * 60 * 60);
        $monthinterval  = ceil($dayinterval / PBB_ONE_MONTH);
    }

    if ($monthinterval < 0) {
        $monthinterval = 0;
    } else {
        $monthinterval = $monthinterval >= PBB_MAXPENALTY_MONTH ? PBB_MAXPENALTY_MONTH : $monthinterval;
    }
    // echo $monthinterval."<br>";
    $denda          = floor((PBB_PENALTY_PERCENT / 100) * $monthinterval * $pokok);
    return $denda;
}


$arrTempo = array();

if ($tempo1 != "") array_push($arrTempo, "A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2 != "") array_push($arrTempo, "A.payment_paid<='{$tempo2} 23:59:59'");

$tempo = implode(" AND ", $arrTempo);

$arrWhere = array();

if ($kecamatan != "" && $kecamatan != 'undefined') {
    array_push($arrWhere, "A.OP_KECAMATAN_KODE like '{$kecamatan}%'");
}

if ($kelurahan != "") {
    array_push($arrWhere, "A.OP_KELURAHAN_KODE like '{$kelurahan}%'");
}

if ($nop != "") array_push($arrWhere, "A.nop='{$nop}'");
if ($thn != "") array_push($arrWhere, "A.sppt_tahun_pajak between  '{$thn}' and '{$thn2}'  ");
if ($na != "") array_push($arrWhere, "A.wp_nama like '%{$na}%'");
if ($status != "") {
    array_push($arrWhere, "(A.payment_flag != 1 OR A.payment_flag IS NULL)");
}
if ($tempo1 != "") array_push($arrWhere, "({$tempo})");

if ($tagihan != 0) {
    switch ($tagihan) {
        case 1:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 100000) ");
            break;
        case 12:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 123:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 1234:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 12345:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 0 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 2:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 500000) ");
            break;
        case 23:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 234:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 2345:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 100001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 3:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 2000000) ");
            break;
        case 34:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 345:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 500001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 4:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 5000000) ");
            break;
        case 45:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
        case 5:
            array_push($arrWhere, " (A.SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND A.SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ");
            break;
    }
}

if ($bank != 0) array_push($arrWhere, "A.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ");
if ($DPH != "" && $DPH != "0")
    array_push($arrWhere, "B.NO_DPH LIKE '{$DPH}%'");

$where = implode(" AND ", $arrWhere);

if (stillInSession($DBLink, $json, $sdata)) {
    $monPBB = new dbMonitoringDph($host, $port, $user, $pass, $dbname);
    $monPBB->setConnectToMysql();
    if ($p == 'all') {
        $monPBB->setRowPerpage($total);
        $monPBB->setPage(1);
    } else {
        $monPBB->setRowPerpage(10000);
        $monPBB->setPage($p);
    }

    $sql_table = "PBB_SPPT A";
    $sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        IFNULL(A.PBB_DENDA,0) as DENDA ,
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH";

    $_sql_table = "PBB_SPPT A JOIN cppmod_pbb_dph_DETAIL B ON  A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.TAHUN";
    $_sql_select = " SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        IFNULL(A.PBB_DENDA,0) as DENDA ,
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH";

    $monPBB->setTable($_sql_table);
    $monPBB->setWhere($where);
    $monPBB->query($_sql_select);
    $result = $monPBB->query_result($_sql_select);
    //    print_r($result);
    //     print_r(mysqli_fetch_assoc($result['data']));
    // exit;
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

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'No.')
    ->setCellValue('B1', 'NOP')
    ->setCellValue('C1', 'TAHUN PAJAK')
    ->setCellValue('D1', 'NAMA WAJIB PAJAK')
    ->setCellValue('E1', 'DESA OP')
    ->setCellValue('F1', 'KECAMATAN OP')
    ->setCellValue('G1', 'PBB TERHUTANG')
    ->setCellValue('H1', 'DENDA')
    ->setCellValue('I1', 'JUMLAH');

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
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['NOP'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['TAHUN']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['WP_NAMA']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['DESA_OP']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['KECAMATAN_OP']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ' ' . number_format($rowData['PBB_TERHUTANG'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ' ' . number_format($rowData['DENDA'], 0, ',', '.'));
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ' ' . number_format($rowData['JUMLAH'], 0, ',', '.'));

    $row++;


    $totalPokok += $rowData['PBB_TERHUTANG'];
    $totalDenda += $rowData['DENDA'];
    $totalBayar += $rowData['JUMLAH'];
}

$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'JUMLAH');
$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ' ' . number_format($totalPokok, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ' ' . number_format($totalDenda, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ' ' . number_format($totalBayar, 0, ',', '.'));
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':F' . $row);


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar WP');

//----set style cell
if ($status == 1)
    $lastColumn = 'I';
else $lastColumn = 'I';
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
    $objPHPExcel->getActiveSheet()->getStyle('T2:T' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('U2:U' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('V2:U' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
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

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
if ($p != 'all')
    header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '-part-' . $p . '.xls"');
else header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
