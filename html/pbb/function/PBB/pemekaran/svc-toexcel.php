<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemekaran', '', dirname(__FILE__))) . '/';
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
    global $DBLink, $appConfig, $tahun;

    // $thnTagihan = $appConfig['tahun_tagihan'];
    // $return = array();
    // $table = ($tahun!=$thnTagihan) ? "cppmod_pbb_sppt_cetak_".$tahun : "cppmod_pbb_sppt_current";

    // 	$query = "SELECT 
    //     A.JENIS, 
    //     A.NOP_LAMA, 
    //     A.NOP_BARU,  
    //     (SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = LEFT(A.NOP_LAMA,7)) AS KECAMATAN_LAMA,  
    //     (SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = LEFT(A.NOP_LAMA,10)) AS KELURAHAN_LAMA,
    //     (SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = LEFT(A.NOP_BARU,7)) AS KECAMATAN_BARU,  
    //     (SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = LEFT(A.NOP_BARU,10)) AS KELURAHAN_BARU,
    //     A.TGL_UPDATE
    // FROM cppmod_pbb_perubahan_nop A;";

    $query = "SELECT 
		
IF(A.JENIS='1', 'Pindah kelurahan keseluruhan ke kecamatan', 
IF(A.JENIS='2','Pindah blok keseluruhan ke kelurahan lain',
IF(A.JENIS='3','Gabung Beberapa Blok','Pindah NOP Ke Blok Lain'))) AS JENIS,
A.NOP_LAMA, 
A.NOP_BARU,  
(SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = LEFT(A.NOP_LAMA,7)) AS KECAMATAN_LAMA,  
(SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = LEFT(A.NOP_LAMA,10)) AS KELURAHAN_LAMA,
(SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = LEFT(A.NOP_BARU,7)) AS KECAMATAN_BARU,  
(SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = LEFT(A.NOP_BARU,10)) AS KELURAHAN_BARU,
A.TGL_UPDATE
FROM cppmod_pbb_perubahan_nop A WHERE STATUS = '1'";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        //echo mysqli_error($DBLink);
        // echo "Data SPPT untuk tahun {$tahun} tidak tersedia.";
        // exit();
        return $return;
    }

    while ($row = mysql_fetch_object($res)) {
        $return[] = $row;
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
$tahun        = $_GET['tahun'];
$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig     = $User->GetAppConfig($a);
$data        = getData();

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
$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
$objPHPExcel->getActiveSheet()->mergeCells('A2:I2');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'REKAPITULASI PERUBAHAN NOP');
// $objPHPExcel->getActiveSheet()->setCellValue('A2', 'PAJAK BUMI DAN BANGUNAN TAHUN '.$tahun);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'NO')
    ->setCellValue('B5', 'JENIS')
    ->setCellValue('C5', "NOP LAMA")
    ->setCellValue('D5', "KECAMATAN LAMA")
    ->setCellValue('E5', "KELURAHAN LAMA")
    ->setCellValue('F5', "NOP BARU")
    ->setCellValue('G5', "KECAMATAN BARU")
    ->setCellValue('H5', "KELURAHAN BARU")
    ->setCellValue('I5', "TANGGAL PERUBAHAN");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
if (count($data) > 0) {
    foreach ($data as $data) {
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':I' . $row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            )
        );

        $nopLama = substr($data->NOP_LAMA, 0, 2) . "." . substr($data->NOP_LAMA, 2, 2) . "." . substr($data->NOP_LAMA, 4, 3) . "." . substr($data->NOP_LAMA, 7, 3) . "." . substr($data->NOP_LAMA, 10, 3) . "." . substr($data->NOP_LAMA, 13, 4) . "." . substr($data->NOP_LAMA, 17);
        $nopBaru = substr($data->NOP_BARU, 0, 2) . "." . substr($data->NOP_BARU, 2, 2) . "." . substr($data->NOP_BARU, 4, 3) . "." . substr($data->NOP_BARU, 7, 3) . "." . substr($data->NOP_BARU, 10, 3) . "." . substr($data->NOP_BARU, 13, 4) . "." . substr($data->NOP_BARU, 17);

        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($row - 5));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data->JENIS);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $nopLama);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data->KECAMATAN_LAMA);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data->KELURAHAN_LAMA);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $nopBaru);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $data->KECAMATAN_BARU);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $data->KELURAHAN_BARU);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data->TGL_UPDATE);

        $row++;
    }
} else {
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':I' . $row . '');
    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), "Tidak ada data untuk ditampilkan");
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
$objPHPExcel->getActiveSheet()->setTitle('Rekapitulasi Perubahan NOP');


//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:I5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:I5')->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:I5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:I5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
// $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);

$objPHPExcel->getActiveSheet()->getStyle('A5:I5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D6:I6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('C6:C6' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
$objPHPExcel->getActiveSheet()->getStyle('F6:F6' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

// $objPHPExcel->getActiveSheet()->getColumnDimension('D5')->setAutoSize(true);
// $objPHPExcel->getActiveSheet()->getColumnDimension('F5')->setAutoSize(true);
for ($x = "B"; $x <= "C"; $x++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
}
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekapitulasi Perubahan NOP ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
