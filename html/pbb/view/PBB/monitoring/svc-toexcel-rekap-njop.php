<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
//error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

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

//error_reporting(E_ALL);
ini_set('display_errors', 0);


function closeMysql($con)
{
    mysqli_close($con);
}
function getData()
{
    global $DBLink;

    $return = array();
    $return["RESULT"] = 0;
    $default = array("JUMLAH_AKTIF" => 0, "JUMLAH_FASUM" => 0, "BUMI_AKTIF" => 0, "BUMI_FASUM" => 0, "BANGUNAN_AKTIF" => 0, "BANGUNAN_FASUM" => 0, "NJOP_AKTIF" => 0, "NJOP_FASUM" => 0);
    $queryKecamatan = "SELECT CPC_TKC_ID, CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan ORDER BY CPC_TKC_ID";
    $res = mysqli_query($DBLink, $queryKecamatan);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return[$row["CPC_TKC_ID"]]             = $default;
        $return[$row["CPC_TKC_ID"]]["NAMA"]    = $row['CPC_TKC_KECAMATAN'];
    }

    $query = "SELECT OP_KECAMATAN_KODE AS KODE, COUNT(*) AS JUMLAH, SUM(OP_LUAS_BUMI) AS BUMI, SUM(OP_LUAS_BANGUNAN) AS BANGUNAN, SUM(OP_NJOP) AS NJOP  FROM cppmod_pbb_sppt_current
                WHERE NOP != ''
                GROUP BY OP_KECAMATAN_KODE
                ORDER BY OP_KECAMATAN_KODE ASC";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return[$row["KODE"]]["JUMLAH_AKTIF"]    = $row["JUMLAH"];
        $return[$row["KODE"]]["BUMI_AKTIF"]    = $row["BUMI"];
        $return[$row["KODE"]]["BANGUNAN_AKTIF"]    = $row["BANGUNAN"];
        $return[$row["KODE"]]["NJOP_AKTIF"]    = $row["NJOP"];
    }

    $query2 = "SELECT CPM_OP_KECAMATAN AS KODE, COUNT(*) AS JUMLAH, SUM(CPM_OP_LUAS_TANAH) AS BUMI, SUM(CPM_OP_LUAS_BANGUNAN) AS BANGUNAN, SUM(CPM_NJOP_TANAH +CPM_NJOP_BANGUNAN) AS NJOP  FROM cppmod_pbb_sppt_final
                WHERE CPM_OT_JENIS='4'
                GROUP BY CPM_OP_KECAMATAN
                ORDER BY CPM_OP_KECAMATAN ASC";


    $res = mysqli_query($DBLink, $query2);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return[$row["KODE"]]["JUMLAH_FASUM"]    = $row["JUMLAH"];
        $return[$row["KODE"]]["BUMI_FASUM"]    = $row["BUMI"];
        $return[$row["KODE"]]["BANGUNAN_FASUM"]    = $row["BANGUNAN"];
        $return[$row["KODE"]]["NJOP_FASUM"]    = $row["NJOP"];
    }
    closeMysql($DBLink);
    return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig     = $User->GetAppConfig($a);
$data = getData();

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
$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
$objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'REKAPITULASI OBJEK PAJAK');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'PAJAK BUMI DAN BANGUNAN TAHUN ' . $appConfig['tahun_tagihan']);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');
$objPHPExcel->getActiveSheet()->mergeCells('B5:B6');
$objPHPExcel->getActiveSheet()->mergeCells('C5:C6');
$objPHPExcel->getActiveSheet()->mergeCells('D5:D6');
$objPHPExcel->getActiveSheet()->mergeCells('E5:F5');
$objPHPExcel->getActiveSheet()->mergeCells('G5:G6');

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'NO')
    ->setCellValue('B5', 'KECAMATAN')
    ->setCellValue('C5', "JUMLAH OP")
    ->setCellValue('D5', "JUMLAH OP FASUM")
    ->setCellValue('E5', "LUAS")
    ->setCellValue('G5', "NJOP");

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('E6', 'TANAH')
    ->setCellValue('F6', 'BANGUNAN');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 7;
foreach ($data as $key => $value) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($row - 6));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $value['NAMA']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $value['JUMLAH_AKTIF']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $value['JUMLAH_FASUM']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), ($value['BUMI_AKTIF'] + $value['BUMI_FASUM']));
    $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), ($value['BANGUNAN_AKTIF'] + $value['BANGUNAN_FASUM']));
    $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), ($value['NJOP_AKTIF'] + $value['NJOP_FASUM']));
    $row++;
}

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
$objPHPExcel->getActiveSheet()->getStyle('A1:A2')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->setTitle('Rekapitulasi Objek Pajak');


//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:G6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:G' . (count($data) + 6))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:G6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:G6')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
$objPHPExcel->getActiveSheet()->getStyle('C5:C6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D5:D6')->getAlignment()->setWrapText(true);
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekapitulasi Objek Pajak ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
