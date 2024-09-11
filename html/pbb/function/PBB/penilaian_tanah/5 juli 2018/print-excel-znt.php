<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_tanah', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
//require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
//require_once($sRootPath . "inc/central/setting-central.php");
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

ini_set('display_errors', 1);


$myDBlink = "";

function openMysql()
{
    global $appConfig;
    $host = $appConfig['GW_DBHOST'];
    $port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user = $appConfig['GW_DBUSER'];
    $pass = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
    if (!$myDBLink) {
        //echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname,$myDBLink);
    return $myDBLink;
}

function closeMysql($con)
{
    mysqli_close($con);
}

function getListZNT($kel)
{
    global $DBLink;
    $return = array();


    $queryZNT = "SELECT CPM_ID, CPM_KODE_ZNT, IFNULL(CPM_NIR2,CPM_NIR) AS CPM_NIR FROM (
                    SELECT CPM_ID, A.CPM_KODE_ZNT,A.CPM_NIR, B.CPM_NJOP_M2 as CPM_NIR2 FROM cppmod_pbb_znt A
                    LEFT JOIN cppmod_pbb_kelas_bumi B 
                    ON rpad(B.CPM_KELAS,3,' ')= rpad(A.CPM_KODE_ZNT,3,' ') 
                    WHERE A.CPM_KODE_LOKASI = '" . $kel . "'  
                    ) TBL ORDER BY CPM_KODE_ZNT";

    $res = mysql_query($queryZNT, $DBLink);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["CPM_KODE_ZNT"]           = $row["CPM_KODE_ZNT"];
        $data[$i]["CPM_NIR"]              = $row["CPM_NIR"] * 1000;

        $i++;
    }

    return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;

$namakelurahan  = @isset($_REQUEST['nkel']) ? $_REQUEST['nkel'] : "";
$namakecamatan  = @isset($_REQUEST['nkec']) ? $_REQUEST['nkec'] : "";
$thn            = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namakota       = @isset($_REQUEST['kota']) ? $_REQUEST['kota'] : "";
$kel            = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";

$data = getListZNT($kel);

$c = count($data);

$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.8);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.5);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(true);

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("Alfa System")
    ->setSubject("Alfa System pbb")
    ->setDescription("pbb")
    ->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();

$objRichText->createText('ZNT DAN NIR TAHUN ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KABUPATEN / KOTA');
$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $namakota);
$objPHPExcel->getActiveSheet()->getCell('C3')->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('D3:G3');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KECAMATAN');
$objPHPExcel->getActiveSheet()->getCell('A4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A4:B4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $namakecamatan);
$objPHPExcel->getActiveSheet()->getCell('C4')->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('D4:G4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KELURAHAN');
$objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $namakelurahan);
$objPHPExcel->getActiveSheet()->getCell('C5')->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('D5:G5');

//// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KODE ZNT');
$objPHPExcel->getActiveSheet()->getCell('B7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NIR');
$objPHPExcel->getActiveSheet()->getCell('C7')->setValue($objRichText);

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Klasifikasi ZNT');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
    array(
        'font'    => array(
            'size' => $fontSizeHeader
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A7:C7')->applyFromArray(
    array(
        'font'    => array(
            'size' => $fontSizeHeader
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

$objPHPExcel->getActiveSheet()->getStyle('A1:C' . (7 + count($data)))->applyFromArray(
    array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);

$objPHPExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

for ($i = 0; $i < $c; $i++) {
    $objPHPExcel->getActiveSheet()->getRowDimension(7 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $no), $no)->getStyle('A' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $no), $data[$i]["CPM_KODE_ZNT"])->getStyle('B' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $no), $data[$i]["CPM_NIR"])->getStyle('C' . (7 + $no))->applyFromArray($noBold);
    $no++;
}

$objPHPExcel->getActiveSheet()->getStyle('A7:C' . (7 + count($data)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
//$objPHPExcel->getActiveSheet()->getStyle('A10:A'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('C10:F'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('G10:G'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('H10:K'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('L10:L'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('M10:N'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('O10:O'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['NAMA_KOTA_PENGESAHAN'].', '.strtoupper($bulan[date('m')-1]).' '.$thn);
//$objPHPExcel->getActiveSheet()->getCell('I'.(11+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(11+count($data)).':K'.(11+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['PEJABAT_SK2']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(12+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(12+count($data)).':K'.(12+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(13+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(13+count($data)).':K'.(13+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['NAMA_PEJABAT_SK2']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(17+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(17+count($data)).':K'.(17+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText($appConfig['NAMA_PEJABAT_SK2_JABATAN']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(18+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(18+count($data)).':K'.(18+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('NIP. '.$appConfig['NAMA_PEJABAT_SK2_NIP']);
//$objPHPExcel->getActiveSheet()->getCell('I'.(19+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(19+count($data)).':K'.(19+count($data)));
//
//$objPHPExcel->getActiveSheet()->getStyle('I'.(17+count($data)).':K'.(17+count($data)));
//$objPHPExcel->getActiveSheet()->getStyle('I'.(11+count($data)).':K'.(19+count($data)))->applyFromArray(
//    array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
//);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="ZNT_' . date('Ymdhis') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
