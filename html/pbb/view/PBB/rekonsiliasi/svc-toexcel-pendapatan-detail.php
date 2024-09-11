<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'rekonsiliasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

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

error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBLink = "";

// koneksi mysql
function openMysql()
{
    global $appConfig;
    $host     = $appConfig['GW_DBHOST'];
    $port     = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user     = $appConfig['GW_DBUSER'];
    $pass     = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
    if (!$myDBLink) {
        echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname,$myDBLink);
    return $myDBLink;
}

function closeMysql($con)
{
    mysqli_close($con);
}

function showTable()
{
    global $tgl, $bln, $thn;

    $dt         = getPendapatan($tgl, $bln, $thn);
    // print_r($dt);
    $c             = count($dt);
    $a = 1;
    $data = array();
    $summary = array('name' => 'TOTAL', 'SUM_POKOK' => 0, 'SUM_DENDA' => 0, 'GRAND_TOTAL' => 0);
    for ($i = 0; $i < $c; $i++) {

        $tmp = array(
            "NOP"         => $dt[$i]['NOP'],
            "NAMA"         => $dt[$i]['NAMA'],
            "ALAMAT"     => $dt[$i]['ALAMAT'],
            "POKOK"     => $dt[$i]['POKOK'],
            "DENDA"     => $dt[$i]['DENDA'],
            "TOTAL"     => $dt[$i]['TOTAL']
        );
        $data[] = $tmp;

        $a++;
    }
    return $data;
}

function getPendapatan($tgl, $bln, $thn)
{
    global $myDBLink, $appConfig;

    $myDBLink = openMysql();
    $return = array();
    $query = "SELECT 
				NOP,
				WP_NAMA AS NAMA,
				WP_ALAMAT AS ALAMAT,
				SPPT_PBB_HARUS_DIBAYAR AS POKOK,
				PBB_DENDA AS DENDA,
				PBB_TOTAL_BAYAR AS TOTAL
			FROM
				PBB_SPPT
			WHERE
			PAYMENT_FLAG = '1'
			AND PAYMENT_SETTLEMENT_DATE LIKE '%$thn$bln$tgl%' ";
    // echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        // print_r($row);
        $return[$i]["NOP"]        = ($row["NOP"] != "") ? $row["NOP"] : "";
        $return[$i]["NAMA"]        = ($row["NAMA"] != "") ? $row["NAMA"] : "";
        $return[$i]["ALAMAT"]    = ($row["ALAMAT"] != "") ? $row["ALAMAT"] : "";
        $return[$i]["POKOK"]    = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
        $return[$i]["DENDA"]    = ($row["DENDA"] != "") ? $row["DENDA"] : 0;
        $return[$i]["TOTAL"]    = ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
        $i++;
    }
    closeMysql($myDBLink);
    return $return;
}

$thn                 = @isset($_REQUEST['thn']) ? $_REQUEST['thn'] : "";
$bln                = @isset($_REQUEST['bln']) ? $_REQUEST['bln'] : "";
$tgl                 = @isset($_REQUEST['tgl']) ? $_REQUEST['tgl'] : "";
$a                     = @isset($_REQUEST['app']) ? $_REQUEST['app'] : "";
$User                 = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig             = $User->GetAppConfig($a);
$tgl                = sprintf("%02d", $tgl);
$arrBulan             = array("01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus", "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember");

// print_r($_REQUEST);exit;
// print_r($appConfig);exit;

$data = showTable();
// echo "<pre>";
// print_r($data);exit;
$sumRows = count($data);
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("Alfa System")
    ->setSubject("Alfa System pbb")
    ->setDescription("pbb")
    ->setKeywords("Alfa System");
//COP
$objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' DETAIL PENDAPATAN PBB-P2 ');
$objPHPExcel->getActiveSheet()->setCellValue('A3', $tgl . ' ' . strtoupper($arrBulan[$bln]) . ' ' . $thn . ' ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', " NOP ")
    ->setCellValue('B5', " NAMA ")
    ->setCellValue('C5', " ALAMAT ")
    ->setCellValue('D5', " POKOK ")
    ->setCellValue('E5', " DENDA ")
    ->setCellValue('F5', " TOTAL ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$summary = array('SUM_POKOK' => 0, 'SUM_DENDA' => 0, 'GRAND_TOTAL' => 0);
for ($i = 0; $i < $sumRows; $i++) {

    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[$i]['NOP'] . " ");
    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[$i]['NAMA']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[$i]['ALAMAT']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[$i]['POKOK']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[$i]['DENDA']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $data[$i]['TOTAL']);
    $row++;

    $summary['SUM_POKOK']     += $data[$i]['POKOK'];
    $summary['SUM_DENDA']     += $data[$i]['DENDA'];
    $summary['GRAND_TOTAL'] += $data[$i]['TOTAL'];
}

// JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $summary['SUM_POKOK']);
$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $summary['SUM_DENDA']);
$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $summary['GRAND_TOTAL']);


$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(
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

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Detail Pendapatan');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->applyFromArray(
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

//border header table
$objPHPExcel->getActiveSheet()->getStyle('A5:F' . ($sumRows + 6))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

// Redirect output to a client�s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Detail Pendapatan PBB-P2 ' . $tgl . ' ' . $arrBulan[$bln] . ' ' . $thn . '-' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
