<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);
//date_default_timezone_set('Asia/Jakarta');

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
        echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname, $myDBLink);
    return $myDBLink;
}

function closeMysql($con)
{
    mysqli_close($con);
}

function convertDate($str)
{
    if ($str == '') return '';
    $tmp = explode('-', $str);
    return $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
}

function getKecamatan($p)
{
    global $DBLink;
    $return = array();
    $query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='" . $p . "' ORDER BY CPC_TKC_URUTAN";
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

function getKelurahan($p)
{
    global $DBLink, $kelurahan;
    $query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
    // echo $query."<br>";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["id"] = $row["CPC_TKL_ID"];
        $data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
        $i++;
    }
    return $data;
}

function getTotalPIUTANG($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $tahunawal, $tahunakhir, $kab, $s, $qBuku, $nop1, $nop2, $nop3, $nop4, $nop5, $nop6, $nop7;


    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $tahun = "";
    if ($tahunawal != "") {
        $tahun = " AND s.SPPT_TAHUN_PAJAK >= '{$tahunawal}' ";
    }
    if ($tahunakhir != "") {
        $tahun .= " AND s.SPPT_TAHUN_PAJAK <= '{$tahunakhir}' ";
    }

    $whrNOP = [];

    if (!empty($nop1) && $nop1 != null) {
        array_push($whrNOP, " MID(s.NOP, 1, 2) = '$nop1' ");
    }

    if (!empty($nop2) && $nop2 != null) {
        array_push($whrNOP, " MID(s.NOP, 3, 2) = '$nop2' ");
    }

    if (!empty($nop3) && $nop3 != null) {
        array_push($whrNOP, " MID(s.NOP, 5, 3) = '$nop3' ");
    }

    if (!empty($nop4) && $nop4 != null) {
        array_push($whrNOP, " MID(s.NOP, 8, 3) = '$nop4' ");
    }

    if (!empty($nop5) && $nop5 != null) {
        array_push($whrNOP, " MID(s.NOP, 11, 3) = '$nop5' ");
    }

    if (!empty($nop6) && $nop6 != null) {
        array_push($whrNOP, " MID(s.NOP, 14, 4) = '$nop6' ");
    }

    if (!empty($nop7) && $nop7 != null) {
        array_push($whrNOP, " MID(s.NOP, 18, 1) = '$nop7' ");
    }

    $whrNOP = (count($whrNOP)>0) ? " AND ".implode('AND',$whrNOP) : "";

    $c = count($kec);

    $data = array();

    for ($i = 0; $i < $c; $i++) {
        $whr_kec = '';
        $data[$i]["name"] = $kec[$i]["name"];
        
        if (empty($nop1) && $nop1 == null && empty($nop2) && $nop2 == null && empty($nop3) && $nop3 == null && empty($nop4) && $nop4 == null && empty($nop5) && $nop5 == null && empty($nop6) && $nop6 == null && empty($nop7) && $nop7 == null) {
            $whr_kec .= " AND s.NOP like '" . $kec[$i]["id"] . "%' ";
        }

        $whr_kec .= $tahun . $whrNOP ;
        $da = getDataPiutang($whr_kec);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["POKOK"] = $da["POKOK"];
    }

    return $data;
}

function getRealisasiPIUTANG($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $tahunawal, $tahunakhir, $kab, $tglawal, $tglakhir, $bank, $nop1, $nop2, $nop3, $nop4, $nop5, $nop6, $nop7;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $periode = '';
    if ($tglawal != "") {
        $periode = " AND DATE(LEFT(s.PAYMENT_PAID,10)) >= '{$tglawal}' ";
    }
    if ($tglakhir != "") {
        $periode .= " AND DATE(LEFT(s.PAYMENT_PAID,10)) <= '{$tglakhir}' ";
    }

    $tahun = "";
    if ($tahunawal != "") {
        $tahun = " AND s.SPPT_TAHUN_PAJAK >= '{$tahunawal}' ";
    }
    if ($tahunakhir != "") {
        $tahun .= " AND s.SPPT_TAHUN_PAJAK <= '{$tahunakhir}' ";
    }
    $selectbank = '';
    if ($bank != "" && $bank != "undefined") {
        $selectbank = " AND s.PAYMENT_BANK_CODE IN ('" . str_replace(",", "','", $bank) . "') ";
    }

    $whrNOP = [];

    if (!empty($nop1) && $nop1 != null) {
        array_push($whrNOP, " MID(s.NOP, 1, 2) = '$nop1' ");
    }

    if (!empty($nop2) && $nop2 != null) {
        array_push($whrNOP, " MID(s.NOP, 3, 2) = '$nop2' ");
    }

    if (!empty($nop3) && $nop3 != null) {
        array_push($whrNOP, " MID(s.NOP, 5, 3) = '$nop3' ");
    }

    if (!empty($nop4) && $nop4 != null) {
        array_push($whrNOP, " MID(s.NOP, 8, 3) = '$nop4' ");
    }

    if (!empty($nop5) && $nop5 != null) {
        array_push($whrNOP, " MID(s.NOP, 11, 3) = '$nop5' ");
    }

    if (!empty($nop6) && $nop6 != null) {
        array_push($whrNOP, " MID(s.NOP, 14, 4) = '$nop6' ");
    }

    if (!empty($nop7) && $nop7 != null) {
        array_push($whrNOP, " MID(s.NOP, 18, 1) = '$nop7' ");
    }

    $whrNOP = (count($whrNOP)>0) ? " AND ".implode('AND',$whrNOP) : "";

    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $whr_kec = '';
        $data[$i]["name"] = $kec[$i]["name"];

        if (empty($nop1) && $nop1 == null && empty($nop2) && $nop2 == null && empty($nop3) && $nop3 == null && empty($nop4) && $nop4 == null && empty($nop5) && $nop5 == null && empty($nop6) && $nop6 == null && empty($nop7) && $nop7 == null) {
            $whr_kec = " AND s.NOP LIKE '" . $kec[$i]["id"] . "%'";
        }

        $whr_kec .= $tahun . $selectbank . $periode . $whrNOP;

        /*if (!empty($nop) && $nop != null) {
            $whr = " NOP = '" . $nop . "' " . $periode . " AND PAYMENT_FLAG='1' " . $tahun . $selectbank;
        } else {
            $whr = " NOP LIKE '" . $kec[$i]["id"] . "%' " . $periode . " AND PAYMENT_FLAG='1' " . $tahun . $selectbank;
        }*/
        $da = getDataRealisasiPIUTANG($whr_kec);
        $data[$i]["POKOK"] = $da["POKOK"];
        $data[$i]["DENDA"] = $da["DENDA"];
        $data[$i]["TOTAL"] = $da["TOTAL"];
    }
    return $data;
}

function showTable($mod = 0, $nama = "")
{
    global $eperiode;
    $dt = getTotalPIUTANG($mod);
    $dt2 = getRealisasiPIUTANG($mod);

    $c = count($dt);
    $html = "";
    $a = 1;

    $summary = array(
        'name' => 'JUMLAH', 
        'totalpiutang' => 0,
        'rbi_pokok' => 0,
        'rbi_denda' => 0,
        'rbi_total' => 0,
        'sisa' => 0
    );

    for ($i = 0; $i < $c; $i++) {
        $dtname = $dt[$i]["name"];
        $rpSISAX = $dt[$i]["POKOK"] - $dt2[$i]["TOTAL"];
        $rpSISAX =($rpSISAX < 0 ) ? 0 : $rpSISAX;
        $tmp = array(
            "name" => $dt[$i]["name"],
            "totalpiutang" => $dt[$i]["POKOK"],
            "rbi_pokok" => $dt2[$i]["POKOK"],
            "rbi_denda" => $dt2[$i]["DENDA"],
            "rbi_total" => $dt2[$i]["TOTAL"],
            "sisa" => $rpSISAX
        );
        $data[] = $tmp;
        $summary['totalpiutang'] += $dt[$i]["POKOK"];
        $summary['rbi_pokok'] += $dt2[$i]["POKOK"];
        $summary['rbi_denda'] += $dt2[$i]["DENDA"];
        $summary['rbi_total'] += $dt2[$i]["TOTAL"];
        $summary['sisa'] += $rpSISAX;

        $a++;
    }

    $data[] = $summary;

    return $data;
}

function getDataRealisasiPIUTANG($where)
{
    global $myDBLink, $kd, $thn, $bulan;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["POKOK"] = 0;
    $return["DENDA"] = 0;
    $return["TOTAL"] = 0;

    $query = "SELECT 
                SUM(s.SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
                SUM(d.PBB_DENDA) AS DENDA
            FROM pbb_sppt s 
            LEFT JOIN pbb_denda d ON d.NOP = s.NOP AND d.SPPT_TAHUN_PAJAK = s.SPPT_TAHUN_PAJAK
            WHERE
                s.PAYMENT_FLAG = '1' AND 
                DATE(LEFT(s.SPPT_TANGGAL_JATUH_TEMPO, 10)) <= DATE(LEFT(s.PAYMENT_PAID, 10)) 
                $where";
    // echo '<pre>'; print_r($query);exit;
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return["POKOK"] = ($row["POKOK"]==null) ? 0 : $row["POKOK"];
        $return["DENDA"] = ($row["DENDA"]==null) ? 0 : $row["DENDA"];
        $return["TOTAL"] = $row["POKOK"] + $row["DENDA"];
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataPiutang($where)
{
    global $myDBLink, $tahunakhir;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["WP"] = 0;

    $query = "SELECT 
                SUM(s.SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
                SUM(d.PBB_DENDA) DENDA 
            FROM pbb_sppt s
            LEFT JOIN pbb_denda d ON d.NOP=s.NOP AND d.SPPT_TAHUN_PAJAK=s.SPPT_TAHUN_PAJAK 
            WHERE 
                (s.PAYMENT_FLAG IS NULL OR s.PAYMENT_FLAG = '0' OR (s.PAYMENT_FLAG = '1' AND DATE(LEFT(s.SPPT_TANGGAL_JATUH_TEMPO,10)) <= DATE(LEFT(s.PAYMENT_PAID,10)) ) ) 
                $where";
    // echo $query.'<br/>';exit;
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return["POKOK"] = ($row["POKOK"] != "") ? ($row["POKOK"] + $row["DENDA"]) : 0;
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
$kd = $appConfig['KODE_KOTA'];
$kab = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];

$kecamatan = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";
$kelurahan = @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "";
$tahunawal = @isset($_REQUEST['tahunawal']) ? $_REQUEST['tahunawal'] : "";
$tahunakhir = @isset($_REQUEST['tahunakhir']) ? $_REQUEST['tahunakhir'] : "";
$namakec = @isset($_REQUEST['namakec']) ? $_REQUEST['namakec'] : "";
$namakel = @isset($_REQUEST['namakel']) ? $_REQUEST['namakel'] : "";
// $nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$nop1 = @isset($_REQUEST['nop1']) ? $_REQUEST['nop1'] : "";
$nop2 = @isset($_REQUEST['nop2']) ? $_REQUEST['nop2'] : "";
$nop3 = @isset($_REQUEST['nop3']) ? $_REQUEST['nop3'] : "";
$nop4 = @isset($_REQUEST['nop4']) ? $_REQUEST['nop4'] : "";
$nop5 = @isset($_REQUEST['nop5']) ? $_REQUEST['nop5'] : "";
$nop6 = @isset($_REQUEST['nop6']) ? $_REQUEST['nop6'] : "";
$nop7 = @isset($_REQUEST['nop7']) ? $_REQUEST['nop7'] : "";
$tglawal = @isset($_REQUEST['tglawal']) ? $_REQUEST['tglawal'] : "";
$tglakhir = @isset($_REQUEST['tglakhir']) ? $_REQUEST['tglakhir'] : "";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";
$nmbank = @isset($_REQUEST['nmbank']) ? $_REQUEST['nmbank'] : "";


if ($kecamatan == "") {
    $data = showTable();
} else if ($kelurahan == "") {
    $data = showTable(1, $namakec);
} else {
    $data = showTable(1, $namakel);
}

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

$strTahun = '';
if ($tahunakhir != '' && $tahunawal < $tahunakhir) $strTahun = $tahunawal . ' s/d ' . $tahunakhir;
else $strTahun = $tahunawal;
$objRichText->createText(': PIUTANG PBB TAHUN PAJAK ' . $strTahun);
$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D1:G1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . convertDate($tglawal) . ' s/d ' . convertDate($tglakhir));
$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D2:G2');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $nmbank);
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:G3');


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'TANGGAL BAYAR');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BANK');

$objPHPExcel->getActiveSheet()->getStyle('C1:G3')->applyFromArray(
    array('font' => array('size' => $fontSizeHeader))
);
if ($kecamatan == '') {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('RANGKING');
    $objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText($appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA']);
    $objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
    $objPHPExcel->getActiveSheet()->getStyle('A5:B6')->applyFromArray(
        array(
            'font' => array('italic' => true, 'size' => $fontSizeHeader),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        )
    );
} else if ($kelurahan == '') {
    $objRichText = new PHPExcel_RichText();
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KECAMATAN : ' . $namakec);
    $objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
    $objPHPExcel->getActiveSheet()->getStyle('A5:D6')->applyFromArray(
        array(
            'font' => array('italic' => false, 'size' => $fontSizeHeader),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
        )
    );
} else {
    $objRichText = new PHPExcel_RichText();
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KELURAHAN : ' . $namakel);
    $objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
    $objPHPExcel->getActiveSheet()->getStyle('A5:D6')->applyFromArray(
        array(
            'font' => array('italic' => false, 'size' => $fontSizeHeader),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
        )
    );
}



// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
$objRichText = new PHPExcel_RichText();

if ($kecamatan == "") {
    $objRichText->createText('KECAMATAN');
} else {
    $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
}

$tahunx = ($tahunakhir==$tahunawal) ? $tahunakhir : $tahunawal . ' - ' . $tahunakhir;

$objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PIUTANG '.$tahunx);
$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C8:C9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI PIUTANG');
$objPHPExcel->getActiveSheet()->getCell('D8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D8:F8');
$objPHPExcel->getActiveSheet()->setCellValue('D9', 'POKOK');
$objPHPExcel->getActiveSheet()->setCellValue('E9', 'DENDA');
$objPHPExcel->getActiveSheet()->setCellValue('F9', 'TOTAL');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('SISA PIUTANG');
$objPHPExcel->getActiveSheet()->getCell('G8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('G8:G9');


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A8:G9')->applyFromArray(
    array(
        'font' => array(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:P50')->applyFromArray(
    array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(16);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

foreach ($data as $buffer) {
    $objPHPExcel->getActiveSheet()->getRowDimension(9 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $no), $buffer['name']);
    if ($buffer['name'] == 'JUMLAH') {
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $buffer['totalpiutang']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $no), $buffer['rbi_pokok']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $no), $buffer['rbi_denda']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $no), $buffer['rbi_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $no), $buffer['sisa']);
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $buffer['totalpiutang'])->getStyle('C' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $no), $buffer['rbi_pokok'])->getStyle('D' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $no), $buffer['rbi_denda'])->getStyle('E' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $no), $buffer['rbi_total'])->getStyle('F' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $no), $buffer['sisa'])->getStyle('G' . (9 + $no))->applyFromArray($noBold);
    }
    $no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A10:G' . (9 + count($data)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
$objPHPExcel->getActiveSheet()->getStyle('A10:A' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('C10:G' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
$uniq = substr(uniqid(),-5);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DAFTAR_PIUTANG_'.date('d-m-Y').'_'.$uniq.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
