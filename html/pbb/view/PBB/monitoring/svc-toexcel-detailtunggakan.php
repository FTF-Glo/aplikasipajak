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

//error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBlink = "";

// koneksi postgres
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

function getData($kel)
{
    global $myDBLink, $appConfig, $qBuku;

    $myDBLink = openMysql();
    $return = array();
    $whr = "";
    if($kel) {
        $len = strlen($kel);
        $whr = " AND LEFT(g.NOP,$len)='$kel'";
    }
    $query="SELECT 
                g.NOP, 
                TRIM(g.WP_NAMA) AS NAMA, 
                TRIM(g.WP_ALAMAT) AS ALMT, 
                g.WP_RT AS RT, 
                g.WP_RW AS RW, 
                TRIM(g.WP_KELURAHAN) AS KEL, 
                TRIM(g.WP_KECAMATAN) AS KEC, 
                TRIM(g.WP_KOTAKAB) AS KOTA, 
                TRIM(g.OP_KELURAHAN) AS OPKEL,
                TRIM(g.OP_KECAMATAN) AS OPKEC,
                IF(g.ID_WP=g.NOP, '', TRIM(g.ID_WP)) AS KTP, 
                g.SPPT_TAHUN_PAJAK AS THN,
                g.SPPT_PBB_HARUS_DIBAYAR AS POKOK,
                IFNULL(d.PBB_DENDA,0) AS DENDA
            FROM pbb_sppt g
            LEFT JOIN pbb_denda d on d.NOP=g.NOP AND d.SPPT_TAHUN_PAJAK=g.SPPT_TAHUN_PAJAK
            WHERE 
                (g.PAYMENT_FLAG<>'1' OR g.PAYMENT_FLAG IS NULL) 
                $qBuku 
                AND g.SPPT_TAHUN_PAJAK < '{$appConfig['tahun_tagihan']}' 
                $whr 
            ORDER BY NAMA, NOP, THN";
    // echo $query;exit;
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while($row = mysqli_fetch_assoc($res)) {
        $tagihan = $row["POKOK"]+$row["DENDA"];
        if($tagihan>0){
            $alamat = $row["ALMT"];
            $alamat .= (int)($row["RT"])>0 ? ' RT:'.$row["RT"] : '';
            $alamat .= (int)($row["RW"])>0 ? ' RW:'.$row["RW"] : '';

            $kotawp = strtoupper($row["KOTA"]);
            if($kotawp=='PESAWARAN'){
                $row["KOTA"] = '';
            }

            $kecwp = strtoupper($row["KEC"]);
            if($row["KEC"]==$row["OPKEC"]){
                $row["KEC"] = '';
            }

            $kelwp = strtoupper($row["KEL"]);
            if($row["KEL"]==$row["OPKEL"]){
                $row["KEL"] = '';
            }

            $alamat .= $row["KEL"]!='' ? ' '.$kelwp : '';
            $alamat .= $row["KEC"]!='' ? ', KEC. '.$kecwp : '';
            $alamat .= $row["KOTA"]!='' ? ', '.$kotawp : '';
            $return[$row['NOP']]['KTP'] = $row["KTP"];
            $return[$row['NOP']]['NAMA'] = $row["NAMA"];
            $return[$row['NOP']]['ALAMAT'] = $alamat;
            $return[$row['NOP']]['TAHUN_PAJAK'][$row["THN"]] = $tagihan;
        }
    }

    closeMysql($myDBLink);
    return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$namakelurahan = @isset($_REQUEST['nkl']) ? $_REQUEST['nkl'] : "";
$namakecamatan = @isset($_REQUEST['nkc']) ? $_REQUEST['nkc'] : "";
$buku = @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : 0;
$arrWhere = array();

$qBuku = "";
if ($buku != 0) {
    switch ($buku) {
        case 1:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
            break;
        case 12:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 123:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 1234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 12345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 2:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 23:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 2345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 3:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 34:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 4:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 45:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 5:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
    }
}
$where = implode(" AND ", $arrWhere);

if ($kelurahan)
    $data = getData($kelurahan);
else $data = getData($kecamatan);


$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$e = $objPHPExcel->getActiveSheet();
$e->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$e->getPageMargins()->setTop(0.8);
$e->getPageMargins()->setRight(0);
$e->getPageMargins()->setLeft(0.5);
$e->getPageMargins()->setBottom(0.3);

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

$objRichText->createText('DETAIL TUNGGAKAN PBB');
$e->getCell('A1')->setValue($objRichText);
$e->mergeCells('A1:AH1');
$e->getStyle('A1:AH1')->applyFromArray(
    array(
        'font'    => array('italic' => true, 'size' => $fontSizeHeader),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    )
);
$objPHPExcel->setActiveSheetIndex(0);

$e->getStyle('C1:L3')->applyFromArray(
    array('font'    => array('size' => $fontSizeHeader))
);

$objRichText = new PHPExcel_RichText();
$objRichText->createText('KECAMATAN : ' . $namakecamatan);
$e->getCell('A3')->setValue($objRichText);
$e->mergeCells('A3:D3');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KELURAHAN : ' . $namakelurahan);
$e->getCell('A4')->setValue($objRichText);
$e->mergeCells('A4:D4');

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$e->getCell('A5')->setValue($objRichText);
$e->mergeCells('A5:A6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NOP');
$e->getCell('B5')->setValue($objRichText);
$e->mergeCells('B5:B6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NAMA');
$e->getCell('C5')->setValue($objRichText);
$e->mergeCells('C5:C6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('ALAMAT');
$e->getCell('D5')->setValue($objRichText);
$e->mergeCells('D5:D6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PIUTANG / TUNGGAKAN');
$e->getCell('E5')->setValue($objRichText);
$e->mergeCells('E5:AH5');

$e->setCellValue('E6', '1994');
$e->setCellValue('F6', '1995');
$e->setCellValue('G6', '1996');
$e->setCellValue('H6', '1997');
$e->setCellValue('I6', '1998');
$e->setCellValue('J6', '1999');
$e->setCellValue('K6', '2000');
$e->setCellValue('L6', '2001');
$e->setCellValue('M6', '2002');
$e->setCellValue('N6', '2003');
$e->setCellValue('O6', '2004');
$e->setCellValue('P6', '2005');
$e->setCellValue('Q6', '2006');
$e->setCellValue('R6', '2007');
$e->setCellValue('S6', '2008');
$e->setCellValue('T6', '2009');
$e->setCellValue('U6', '2010');
$e->setCellValue('V6', '2011');
$e->setCellValue('W6', '2012');
$e->setCellValue('X6', '2013');
$e->setCellValue('Y6', '2014');
$e->setCellValue('Z6', '2015');
$e->setCellValue('AA6', '2016');
$e->setCellValue('AB6', '2017');
$e->setCellValue('AC6', '2018');
$e->setCellValue('AD6', '2019');
$e->setCellValue('AE6', '2020');
$e->setCellValue('AF6', '2021');
$e->setCellValue('AG6', '2022');
$e->setCellValue('AH6', '2023');

// Rename sheet
$e->setTitle('Detail Tunggakan');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$e->getStyle('A5:AH6')->applyFromArray(
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

//$e->getStyle('A1:D50')->applyFromArray(
//    array(
//        'alignment' => array(
//            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
//        )
//    )
//);

//Set column widths
$e->getColumnDimension('A')->setWidth(5);
$e->getColumnDimension('B')->setWidth(19);
$e->getColumnDimension('C')->setAutoSize(true);
$e->getColumnDimension('D')->setAutoSize(true);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

foreach ($data as $nop => $d) {
    $e->setCellValue('A' . (6 + $no), $no)->getStyle('A' . (6 + $no))->applyFromArray($noBold);

    $e->setCellValue('B' . (6 + $no), $nop. ' ')->getStyle('B' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('C' . (6 + $no), $d['NAMA'])->getStyle('C' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('D' . (6 + $no), $d['ALAMAT'])->getStyle('D' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('E' . (6 + $no), isset($d['TAHUN_PAJAK']['1994']) ? $d['TAHUN_PAJAK']['1994'] : '')->getStyle('E' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('F' . (6 + $no), isset($d['TAHUN_PAJAK']['1995']) ? $d['TAHUN_PAJAK']['1995'] : '')->getStyle('F' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('G' . (6 + $no), isset($d['TAHUN_PAJAK']['1996']) ? $d['TAHUN_PAJAK']['1996'] : '')->getStyle('G' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('H' . (6 + $no), isset($d['TAHUN_PAJAK']['1997']) ? $d['TAHUN_PAJAK']['1997'] : '')->getStyle('H' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('I' . (6 + $no), isset($d['TAHUN_PAJAK']['1998']) ? $d['TAHUN_PAJAK']['1998'] : '')->getStyle('I' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('J' . (6 + $no), isset($d['TAHUN_PAJAK']['1999']) ? $d['TAHUN_PAJAK']['1999'] : '')->getStyle('J' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('K' . (6 + $no), isset($d['TAHUN_PAJAK']['2000']) ? $d['TAHUN_PAJAK']['2000'] : '')->getStyle('K' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('L' . (6 + $no), isset($d['TAHUN_PAJAK']['2001']) ? $d['TAHUN_PAJAK']['2001'] : '')->getStyle('L' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('M' . (6 + $no), isset($d['TAHUN_PAJAK']['2002']) ? $d['TAHUN_PAJAK']['2002'] : '')->getStyle('M' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('N' . (6 + $no), isset($d['TAHUN_PAJAK']['2003']) ? $d['TAHUN_PAJAK']['2003'] : '')->getStyle('N' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('O' . (6 + $no), isset($d['TAHUN_PAJAK']['2004']) ? $d['TAHUN_PAJAK']['2004'] : '')->getStyle('O' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('P' . (6 + $no), isset($d['TAHUN_PAJAK']['2005']) ? $d['TAHUN_PAJAK']['2005'] : '')->getStyle('P' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('Q' . (6 + $no), isset($d['TAHUN_PAJAK']['2006']) ? $d['TAHUN_PAJAK']['2006'] : '')->getStyle('Q' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('R' . (6 + $no), isset($d['TAHUN_PAJAK']['2007']) ? $d['TAHUN_PAJAK']['2007'] : '')->getStyle('R' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('S' . (6 + $no), isset($d['TAHUN_PAJAK']['2008']) ? $d['TAHUN_PAJAK']['2008'] : '')->getStyle('S' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('T' . (6 + $no), isset($d['TAHUN_PAJAK']['2009']) ? $d['TAHUN_PAJAK']['2009'] : '')->getStyle('T' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('U' . (6 + $no), isset($d['TAHUN_PAJAK']['2010']) ? $d['TAHUN_PAJAK']['2010'] : '')->getStyle('U' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('V' . (6 + $no), isset($d['TAHUN_PAJAK']['2011']) ? $d['TAHUN_PAJAK']['2011'] : '')->getStyle('V' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('W' . (6 + $no), isset($d['TAHUN_PAJAK']['2012']) ? $d['TAHUN_PAJAK']['2012'] : '')->getStyle('W' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('X' . (6 + $no), isset($d['TAHUN_PAJAK']['2013']) ? $d['TAHUN_PAJAK']['2013'] : '')->getStyle('X' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('Y' . (6 + $no), isset($d['TAHUN_PAJAK']['2014']) ? $d['TAHUN_PAJAK']['2014'] : '')->getStyle('Y' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('Z' . (6 + $no), isset($d['TAHUN_PAJAK']['2015']) ? $d['TAHUN_PAJAK']['2015'] : '')->getStyle('Z' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AA' . (6 + $no), isset($d['TAHUN_PAJAK']['2016']) ? $d['TAHUN_PAJAK']['2016'] : '')->getStyle('AA' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AB' . (6 + $no), isset($d['TAHUN_PAJAK']['2017']) ? $d['TAHUN_PAJAK']['2017'] : '')->getStyle('AB' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AC' . (6 + $no), isset($d['TAHUN_PAJAK']['2018']) ? $d['TAHUN_PAJAK']['2018'] : '')->getStyle('AC' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AD' . (6 + $no), isset($d['TAHUN_PAJAK']['2019']) ? $d['TAHUN_PAJAK']['2019'] : '')->getStyle('AD' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AE' . (6 + $no), isset($d['TAHUN_PAJAK']['2020']) ? $d['TAHUN_PAJAK']['2020'] : '')->getStyle('AE' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AF' . (6 + $no), isset($d['TAHUN_PAJAK']['2021']) ? $d['TAHUN_PAJAK']['2021'] : '')->getStyle('AF' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AG' . (6 + $no), isset($d['TAHUN_PAJAK']['2022']) ? $d['TAHUN_PAJAK']['2022'] : '')->getStyle('AG' . (6 + $no))->applyFromArray($noBold);
    $e->setCellValue('AH' . (6 + $no), isset($d['TAHUN_PAJAK']['2023']) ? $d['TAHUN_PAJAK']['2023'] : '')->getStyle('AH' . (6 + $no))->applyFromArray($noBold);
    $no++;
    //$e->setCellValue('E'.(6+$no), '=SUM(E7:E'.(6+$no).')')->getStyle('E'.(6+$no))->applyFromArray($noBold);
}
$e->setCellValue('D' . (6 + $no), 'TOTAL')->getStyle('D' . (7 + $no))->applyFromArray($bold);
$e->setCellValue('E' . (6 + $no), '=SUM(E7:E' . (5 + $no) . ')')->getStyle('E' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('F' . (6 + $no), '=SUM(F7:F' . (5 + $no) . ')')->getStyle('F' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('G' . (6 + $no), '=SUM(G7:G' . (5 + $no) . ')')->getStyle('G' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('H' . (6 + $no), '=SUM(H7:H' . (5 + $no) . ')')->getStyle('H' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('I' . (6 + $no), '=SUM(I7:I' . (5 + $no) . ')')->getStyle('I' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('J' . (6 + $no), '=SUM(J7:J' . (5 + $no) . ')')->getStyle('J' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('K' . (6 + $no), '=SUM(K7:K' . (5 + $no) . ')')->getStyle('K' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('L' . (6 + $no), '=SUM(L7:L' . (5 + $no) . ')')->getStyle('L' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('M' . (6 + $no), '=SUM(M7:M' . (5 + $no) . ')')->getStyle('M' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('N' . (6 + $no), '=SUM(N7:N' . (5 + $no) . ')')->getStyle('N' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('O' . (6 + $no), '=SUM(O7:O' . (5 + $no) . ')')->getStyle('O' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('P' . (6 + $no), '=SUM(P7:P' . (5 + $no) . ')')->getStyle('P' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('Q' . (6 + $no), '=SUM(Q7:Q' . (5 + $no) . ')')->getStyle('Q' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('R' . (6 + $no), '=SUM(R7:R' . (5 + $no) . ')')->getStyle('R' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('S' . (6 + $no), '=SUM(S7:S' . (5 + $no) . ')')->getStyle('S' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('T' . (6 + $no), '=SUM(T7:T' . (5 + $no) . ')')->getStyle('T' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('U' . (6 + $no), '=SUM(U7:U' . (5 + $no) . ')')->getStyle('U' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('V' . (6 + $no), '=SUM(V7:V' . (5 + $no) . ')')->getStyle('V' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('W' . (6 + $no), '=SUM(W7:W' . (5 + $no) . ')')->getStyle('W' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('X' . (6 + $no), '=SUM(X7:X' . (5 + $no) . ')')->getStyle('X' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('Y' . (6 + $no), '=SUM(Y7:Y' . (5 + $no) . ')')->getStyle('Y' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('Z' . (6 + $no), '=SUM(Z7:Z' . (5 + $no) . ')')->getStyle('Z' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AA' . (6 + $no), '=SUM(AA7:AA' . (5 + $no) . ')')->getStyle('AA' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AB' . (6 + $no), '=SUM(AB7:AB' . (5 + $no) . ')')->getStyle('AB' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AC' . (6 + $no), '=SUM(AC7:AC' . (5 + $no) . ')')->getStyle('AC' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AD' . (6 + $no), '=SUM(AD7:AD' . (5 + $no) . ')')->getStyle('AD' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AE' . (6 + $no), '=SUM(AE7:AE' . (5 + $no) . ')')->getStyle('AE' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AF' . (6 + $no), '=SUM(AF7:AF' . (5 + $no) . ')')->getStyle('AF' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AG' . (6 + $no), '=SUM(AG7:AG' . (5 + $no) . ')')->getStyle('AG' . (6 + $no))->applyFromArray($bold);
$e->setCellValue('AH' . (6 + $no), '=SUM(AH7:AH' . (5 + $no) . ')')->getStyle('AH' . (6 + $no))->applyFromArray($bold);

$e->getStyle('A7:AH' . (7 + count($data)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
//$e->getStyle('A7:A'.(6+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$e->getStyle('C7:Y'.(6+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


// Set page orientation and size
$e->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$e->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
$namakelurahan = str_replace(' ','_',$namakelurahan) . '_' . strtoupper(substr(uniqid(), -5));
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DETAIL_TUNGGAKAN_'.$namakelurahan.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
