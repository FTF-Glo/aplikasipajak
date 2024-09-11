<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

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
require_once($sRootPath . "inc/PBB/dbMonitoring.php");


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

function getKecamatan($p)
{
    global $DBLink;
    $return = array();
    $query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='" . $p . "' ORDER BY CPC_TKC_URUTAN ASC";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["id"] = $row["CPC_TKC_ID"];
        $data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
        $i++;
    }

    return $data;
}

function getRealisasi()
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $date_start, $date_end, $kab;

    //$periode = "and payment_paid between '{$date_start}' and '{$date_end}'"; //Antara tanggal 01 sampai tanggal 30 bulan sekarang
    $kec =  getKecamatan($kab);
    $c = count($kec);

    $strtahun = "SPPT_TAHUN_PAJAK in ('" . $thn . "'";
    for ($t = $thn - 1; $t >= $thn - 5; $t--) {
        $strtahun .= ",'" . $t . "'";
    }
    $strtahun .= ") ";
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        for ($j = 0; $j < 24; $j++) {
            $data[$i][$j] = '0';
        }
        $data[$i][0] = $i + 1;
        $whr = " WHERE NOP like '" . $kec[$i]["id"] . "%' and (payment_flag!='1' OR payment_flag is null) and $strtahun ";
        $da = getData($whr);
        $data[$i][1] = $kec[$i]["name"];
        $data[$i][2] = $da[$thn]["wp"];
        $data[$i][3] = $da[$thn]["pokok"];
        $data[$i][4] = $da[$thn - 1]["wp"];
        $data[$i][5] = $da[$thn - 1]["pokok"];
        $data[$i][6] = $da[$thn - 2]["wp"];
        $data[$i][7] = $da[$thn - 2]["pokok"];
        $data[$i][8] = $da[$thn - 3]["wp"];
        $data[$i][9] = $da[$thn - 3]["pokok"];
        $data[$i][10] = $da[$thn - 4]["wp"];
        $data[$i][11] = $da[$thn - 4]["pokok"];
        $data[$i][12] = $da[$thn - 5]["wp"];
        $data[$i][13] = $da[$thn - 5]["pokok"];
    }
    return $data;
}

function getData($where)
{
    global $myDBLink, $thn;

    $myDBLink = openMysql();
    $return = array();
    for ($t = $thn; $t >= $thn - 5; $t--) {
        $return[$t]["pokok"] = 0;
        $return[$t]["wp"] = 0;
    }

    $query = " SELECT COUNT(wp_nama) AS wp, sum(SPPT_PBB_HARUS_DIBAYAR) as pokok, SPPT_TAHUN_PAJAK FROM PBB_SPPT 
                {$where}
                GROUP BY SPPT_TAHUN_PAJAK
                ORDER BY SPPT_TAHUN_PAJAK DESC";

    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return[$row["SPPT_TAHUN_PAJAK"]]["pokok"] = ($row["pokok"] != "") ? $row["pokok"] : 0;
        $return[$row["SPPT_TAHUN_PAJAK"]]["wp"] = ($row["wp"] != "") ? $row["wp"] : 0;
    }

    closeMysql($myDBLink);
    return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kab = $appConfig['KODE_KOTA'];
$thn = $appConfig['tahun_tagihan'];
$date_start = @isset($_REQUEST['ds']) ? $_REQUEST['ds'] : "2014-01-01";
$date_end = @isset($_REQUEST['de']) ? $_REQUEST['de'] : "2014-05-30";
$date_start = $date_start . ' 00:00:00';
$date_end = $date_end . ' 23:59:59';

$dt = getRealisasi();

$c = count($dt);

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

$objRichText->createText('REKAPAN KESELURUHAN');
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:N1');

//// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A3:A5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KECAMATAN');
$objPHPExcel->getActiveSheet()->getCell('B3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B3:B5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENETAPAN ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('C3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C3:D3');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TUNGGAKAN PENETAPAN');
$objPHPExcel->getActiveSheet()->getCell('E3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E3:N3');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 1);
$objPHPExcel->getActiveSheet()->getCell('E4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 2);
$objPHPExcel->getActiveSheet()->getCell('G4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('G4:H4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 3);
$objPHPExcel->getActiveSheet()->getCell('I4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I4:J4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 4);
$objPHPExcel->getActiveSheet()->getCell('K4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 5);
$objPHPExcel->getActiveSheet()->getCell('M4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M4:N4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('C4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C4:C5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('D4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D4:D5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('E5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('G5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('I5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('K5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('M5')->setValue($objRichText);


$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('D4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D4:D5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('F5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('H5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('J5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('L5')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('N5')->setValue($objRichText);

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

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

$objPHPExcel->getActiveSheet()->getStyle('A3:N5')->applyFromArray(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:N50')->applyFromArray(
    array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));
$summary = array();
$summary['2'] = 0;
$summary['3'] = 0;
$summary['4'] = 0;
$summary['5'] = 0;
$summary['6'] = 0;
$summary['7'] = 0;
$summary['8'] = 0;
$summary['9'] = 0;
$summary['10'] = 0;
$summary['11'] = 0;
$summary['12'] = 0;
$summary['13'] = 0;

for ($i = 0; $i < $c; $i++) {
    $objPHPExcel->getActiveSheet()->getRowDimension(5 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $no), $dt[$i][1]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $no), $dt[$i][2])->getStyle('C' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $no), $dt[$i][3])->getStyle('D' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $no), $dt[$i][4])->getStyle('E' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $no), $dt[$i][5])->getStyle('F' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $no), $dt[$i][6])->getStyle('G' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (5 + $no), $dt[$i][7])->getStyle('H' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (5 + $no), $dt[$i][8])->getStyle('I' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (5 + $no), $dt[$i][9])->getStyle('J' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . (5 + $no), $dt[$i][10])->getStyle('K' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . (5 + $no), $dt[$i][11])->getStyle('L' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . (5 + $no), $dt[$i][12])->getStyle('M' . (5 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . (5 + $no), $dt[$i][13])->getStyle('N' . (5 + $no))->applyFromArray($noBold);
    $no++;

    $summary['2'] += $dt[$i][2];
    $summary['3'] += $dt[$i][3];
    $summary['4'] += $dt[$i][4];
    $summary['5'] += $dt[$i][5];
    $summary['6'] += $dt[$i][6];
    $summary['7'] += $dt[$i][7];
    $summary['8'] += $dt[$i][8];
    $summary['9'] += $dt[$i][9];
    $summary['10'] += $dt[$i][10];
    $summary['11'] += $dt[$i][11];
    $summary['12'] += $dt[$i][12];
    $summary['13'] += $dt[$i][13];
}

$objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $no), 'JUMLAH');
$objPHPExcel->getActiveSheet()->mergeCells('A' . (5 + $no) . ':B' . (5 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $no), $summary['2']);
$objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $no), $summary['3']);
$objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $no), $summary['4']);
$objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $no), $summary['5']);
$objPHPExcel->getActiveSheet()->setCellValue('G' . (5 + $no), $summary['6']);
$objPHPExcel->getActiveSheet()->setCellValue('H' . (5 + $no), $summary['7']);
$objPHPExcel->getActiveSheet()->setCellValue('I' . (5 + $no), $summary['8']);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (5 + $no), $summary['9']);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (5 + $no), $summary['10']);
$objPHPExcel->getActiveSheet()->setCellValue('L' . (5 + $no), $summary['11']);
$objPHPExcel->getActiveSheet()->setCellValue('M' . (5 + $no), $summary['12']);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (5 + $no), $summary['13']);

//$objPHPExcel->getActiveSheet()->setCellValue('A'.(8+$no), '');
$objPHPExcel->getActiveSheet()->getStyle('A6:N' . (6 + count($dt)))->applyFromArray(
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
header('Content-Disposition: attachment;filename="reporting_rekapkeseluruhan_' . date('Ymdhis') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
