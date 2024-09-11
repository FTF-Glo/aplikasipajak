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
function openMysql() {
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

function closeMysql($con) {
    mysqli_close($con);
}

function convertDate($str){
    if($str == '') return '';
    $tmp = explode('-', $str);
    return $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
}

function getKecamatan($p) {
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

function getKelurahan($p) {
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

function getKetetapan($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $tahunawal, $tahunakhir, $kab, $s, $qBuku;
    
    
    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $tahun = "";
//    if ($tahunawal != "") {
//        $tahun = "and sppt_tahun_pajak >= '{$tahunawal}' ";
//    }
//    if ($tahunakhir != "") {
//        $tahun .= "and sppt_tahun_pajak <= '{$tahunakhir}' ";
//    }
    
    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' ";
        $da = getDataPiutang($whr);
        $data[$i][$tahunawal."WP"] = $da[$tahunawal."WP"];
        $data[$i][$tahunawal."TAGIHAN"] = $da[$tahunawal."TAGIHAN"];
        $data[$i][($tahunawal-1)."WP"] = $da[($tahunawal-1)."WP"];
        $data[$i][($tahunawal-1)."TAGIHAN"] = $da[($tahunawal-1)."TAGIHAN"];
        $data[$i][($tahunawal-2)."WP"] = $da[($tahunawal-2)."WP"];
        $data[$i][($tahunawal-2)."TAGIHAN"] = $da[($tahunawal-2)."TAGIHAN"];
        $data[$i][($tahunawal-3)."WP"] = $da[($tahunawal-3)."WP"];
        $data[$i][($tahunawal-3)."TAGIHAN"] = $da[($tahunawal-3)."TAGIHAN"];
        $data[$i][($tahunawal-4)."WP"] = $da[($tahunawal-4)."WP"];
        $data[$i][($tahunawal-4)."TAGIHAN"] = $da[($tahunawal-4)."TAGIHAN"];
        $data[$i][($tahunawal-5)."WP"] = $da[($tahunawal-5)."WP"];
        $data[$i][($tahunawal-5)."TAGIHAN"] = $da[($tahunawal-5)."TAGIHAN"];
    }

    return $data;
}

function showTable($mod = 0, $nama = "") {
    global $namakec, $namakel, $tahunawal;
    
    $dt = getKetetapan($mod);
    
    return $dt;
}

function getData($where) {
    global $myDBLink, $kd, $thn, $bulan;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["POKOK"] = 0;
    $return["DENDA"] = 0;
    $return["TOTAL"] = 0;
    $whr = "";
    if ($where) {
        $whr = " WHERE {$where}";
    }
    $query = "SELECT sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK, sum(PBB_DENDA) AS DENDA, "
            . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM PBB_SPPT {$whr}";
     //echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
        $return["DENDA"] = ($row["DENDA"] != "") ? $row["DENDA"] : 0;
        $return["TOTAL"] = ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataPiutang($where) {
    global $myDBLink, $tahunawal;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["WP"] = 0;
    $whr = "";
    if ($where) {
        $whr = " AND {$where}";
    }

    $query = "SELECT 
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal)."'),`TAGIHAN`,0)) AS `".$tahunawal."TAGIHAN`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal)."'),`WP`,0)) AS `".$tahunawal."WP`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-1)."'),`TAGIHAN`,0)) AS `".($tahunawal-1)."TAGIHAN`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-1)."'),`WP`,0)) AS `".($tahunawal-1)."WP`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-2)."'),`TAGIHAN`,0)) AS `".($tahunawal-2)."TAGIHAN`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-2)."'),`WP`,0)) AS `".($tahunawal-2)."WP`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-3)."'),`TAGIHAN`,0)) AS `".($tahunawal-3)."TAGIHAN`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-3)."'),`WP`,0)) AS `".($tahunawal-3)."WP`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-4)."'),`TAGIHAN`,0)) AS `".($tahunawal-4)."TAGIHAN`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-4)."'),`WP`,0)) AS `".($tahunawal-4)."WP`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-5)."'),`TAGIHAN`,0)) AS `".($tahunawal-5)."TAGIHAN`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-5)."'),`WP`,0)) AS `".($tahunawal-5)."WP`
                FROM
                (SELECT
                    SPPT_TAHUN_PAJAK,count(*) AS WP,
                    SUM(SPPT_PBB_HARUS_DIBAYAR) AS TAGIHAN
                FROM PBB_SPPT
                WHERE
                    SPPT_TAHUN_PAJAK IN ('".$tahunawal."', '".($tahunawal-1)."', '".($tahunawal-2)."', '".($tahunawal-3)."', '".($tahunawal-4)."', '".($tahunawal-5)."')
                    AND ( PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1' OR (PAYMENT_FLAG = '1' AND PAYMENT_PAID >= '".$tahunawal."-12-31')) 
                    $whr
                GROUP BY SPPT_TAHUN_PAJAK
                ) AS BBB";
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {      
        $return = $row;
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
$kadis = $appConfig['PEJABAT_SK2'];
$kadisNama = $appConfig['NAMA_PEJABAT_SK2'];
$kadisNIP = 'NIP. '.$appConfig['NAMA_PEJABAT_SK2_NIP'];
$kadisJabatan = $appConfig['NAMA_PEJABAT_SK2_JABATAN'];
$kabid = 'KEPALA BIDANG PEMBUKUAN DAN PELAPORAN,';
$kabidNama = 'JOCKE S. M. BOELAN, S.Ip';//$appConfig['KABID_NAMA'];
$kabidNIP = 'NIP. 19630808 198903 2 014';//$appConfig['KABID_NIP'];
$kabidJabatan = strtoupper('Penata Tingkat I');//$appConfig['KABID_JABATAN'];
$kota = $appConfig['C_KABKOT'];
$namaKota = $appConfig['NAMA_KOTA'];
$namaKotaPengesahan = $appConfig['NAMA_KOTA_PENGESAHAN'];
$kab = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";
$tahunawal = @isset($_REQUEST['tahunawal']) ? $_REQUEST['tahunawal'] : "";
$namakec = @isset($_REQUEST['namakec']) ? $_REQUEST['namakec'] : "";
$kecamatan = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";


if ($kecamatan == "") {
    $data = showTable();
} else {
    $data = showTable(1, $namakec);
}

$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
                             ->setLastModifiedBy("vpost")
                             ->setTitle("Alfa System")
                             ->setSubject("Alfa System pbb")
                             ->setDescription("pbb")
                             ->setKeywords("Alfa System");

$objPHPExcel->getActiveSheet()->getStyle('A1:N2')->applyFromArray(
        array(
            'font' => array(
                'size' => $fontSizeHeader
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);

if ($kecamatan == '') {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('PENYISIHAN PIUTANG PAJAK BUMI DAN BANGUNAN (PBB)');
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText($kota.' '.$namaKota);
    $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
}else{
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('PENYISIHAN PIUTANG PAJAK BUMI DAN BANGUNAN (PBB) '.$kota.' '.$namaKota);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KECAMATAN '.$namakec);
    $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
}

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
$objRichText = new PHPExcel_RichText();

if ($kecamatan == "") {
    $objRichText->createText('KECAMATAN');
} else {
    $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
}

$objPHPExcel->getActiveSheet()->getCell('B4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL OBJEK PAJAK SPPT');
$objPHPExcel->getActiveSheet()->getCell('C4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C4:H4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PIUTANG PAJAK BUMI DAN BANGUNAN (PBB) TAHUN '.$tahunawal.' (Rp)');
$objPHPExcel->getActiveSheet()->getCell('I4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I4:N4');

$objPHPExcel->getActiveSheet()->setCellValue('C5', $tahunawal-5);
$objPHPExcel->getActiveSheet()->setCellValue('D5', $tahunawal-4);
$objPHPExcel->getActiveSheet()->setCellValue('E5', $tahunawal-3);
$objPHPExcel->getActiveSheet()->setCellValue('F5', $tahunawal-2);
$objPHPExcel->getActiveSheet()->setCellValue('G5', $tahunawal-1);
$objPHPExcel->getActiveSheet()->setCellValue('H5', $tahunawal);
$objPHPExcel->getActiveSheet()->setCellValue('I5', $tahunawal-5);
$objPHPExcel->getActiveSheet()->setCellValue('J5', $tahunawal-4);
$objPHPExcel->getActiveSheet()->setCellValue('K5', $tahunawal-3);
$objPHPExcel->getActiveSheet()->setCellValue('L5', $tahunawal-2);
$objPHPExcel->getActiveSheet()->setCellValue('M5', $tahunawal-1);
$objPHPExcel->getActiveSheet()->setCellValue('N5', $tahunawal);

$objPHPExcel->getActiveSheet()->setCellValue('A6', '(1)');
$objPHPExcel->getActiveSheet()->setCellValue('B6', '(2)');
$objPHPExcel->getActiveSheet()->setCellValue('C6', '(3)');
$objPHPExcel->getActiveSheet()->setCellValue('D6', '(4)');
$objPHPExcel->getActiveSheet()->setCellValue('E6', '(5)');
$objPHPExcel->getActiveSheet()->setCellValue('F6', '(6)');
$objPHPExcel->getActiveSheet()->setCellValue('G6', '(7)');
$objPHPExcel->getActiveSheet()->setCellValue('H6', '(8)');
$objPHPExcel->getActiveSheet()->setCellValue('I6', '(9)');
$objPHPExcel->getActiveSheet()->setCellValue('J6', '(10)');
$objPHPExcel->getActiveSheet()->setCellValue('K6', '(11)');
$objPHPExcel->getActiveSheet()->setCellValue('L6', '(12)');
$objPHPExcel->getActiveSheet()->setCellValue('M6', '(13)');
$objPHPExcel->getActiveSheet()->setCellValue('N6', '(14)');


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('PENYISIHAN 1');

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A4:N6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A4:N6')->getFill()->getStartColor()->setRGB('E4E4E4');
$objPHPExcel->getActiveSheet()->getStyle('A4:N6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('size' => $fontSizeDefault, 'bold' => false));
$bold = array('font' => array('bold' => true));

$summary = array(0,0,0,0,0,0,0,0,0,0,0,0);
foreach ($data as $buffer) {
    $objPHPExcel->getActiveSheet()->getRowDimension(6 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $no), $no)->getStyle('A' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $no), $buffer['name'])->getStyle('B' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $no), $buffer[($tahunawal-5)."WP"])->getStyle('C' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $no), $buffer[($tahunawal-4)."WP"])->getStyle('D' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $no), $buffer[($tahunawal-3)."WP"])->getStyle('E' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $no), $buffer[($tahunawal-2)."WP"])->getStyle('F' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $no), $buffer[($tahunawal-1)."WP"])->getStyle('G' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $no), $buffer[($tahunawal)."WP"])->getStyle('H' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $no), $buffer[($tahunawal-5)."TAGIHAN"])->getStyle('I' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $no), $buffer[($tahunawal-4)."TAGIHAN"])->getStyle('J' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $no), $buffer[($tahunawal-3)."TAGIHAN"])->getStyle('K' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $no), $buffer[($tahunawal-2)."TAGIHAN"])->getStyle('L' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . (6 + $no), $buffer[($tahunawal-1)."TAGIHAN"])->getStyle('M' . (6 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . (6 + $no), $buffer[($tahunawal)."TAGIHAN"])->getStyle('N' . (6 + $no))->applyFromArray($noBold);
    $summary[0] += $buffer[($tahunawal-5)."WP"];
    $summary[1] += $buffer[($tahunawal-4)."WP"];
    $summary[2] += $buffer[($tahunawal-3)."WP"];
    $summary[3] += $buffer[($tahunawal-2)."WP"];
    $summary[4] += $buffer[($tahunawal-1)."WP"];
    $summary[5] += $buffer[($tahunawal)."WP"];
    $summary[6] += $buffer[($tahunawal-5)."TAGIHAN"];
    $summary[7] += $buffer[($tahunawal-4)."TAGIHAN"];
    $summary[8] += $buffer[($tahunawal-3)."TAGIHAN"];
    $summary[9] += $buffer[($tahunawal-2)."TAGIHAN"];
    $summary[10] += $buffer[($tahunawal-1)."TAGIHAN"];
    $summary[11] += $buffer[($tahunawal)."TAGIHAN"];
    $no++;
}
$objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $no))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $no), 'TOTAL');
$objPHPExcel->getActiveSheet()->mergeCells('A' . (6 + $no).':B'.(6 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $no), $summary[0])->getStyle('C' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $no), $summary[1])->getStyle('D' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $no), $summary[2])->getStyle('E' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $no), $summary[3])->getStyle('F' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $no), $summary[4])->getStyle('G' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $no), $summary[5])->getStyle('H' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $no), $summary[6])->getStyle('I' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $no), $summary[7])->getStyle('J' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $no), $summary[8])->getStyle('K' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $no), $summary[9])->getStyle('L' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('M' . (6 + $no), $summary[10])->getStyle('M' . (6 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (6 + $no), $summary[11])->getStyle('N' . (6 + $no))->applyFromArray($noBold);
$no++;
$objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A4:N' . (7 + count($data)))->applyFromArray(
        array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//-------------------------------------------SHEET 2--------------------------------------------------------
$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex(1);

$objPHPExcel->getActiveSheet()->getStyle('A1:N2')->applyFromArray(
        array(
            'font' => array(
                'size' => $fontSizeHeader
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);

if ($kecamatan == '') {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('PENYISIHAN PIUTANG PAJAK BUMI DAN BANGUNAN (PBB)');
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText($kota.' '.$namaKota);
    $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
}else{
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('PENYISIHAN PIUTANG PAJAK BUMI DAN BANGUNAN (PBB) '.$kota.' '.$namaKota);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KECAMATAN '.$namakec);
    $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
}


// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A4:A6');
$objRichText = new PHPExcel_RichText();

if ($kecamatan == "") {
    $objRichText->createText('KECAMATAN');
} else {
    $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
}

$objPHPExcel->getActiveSheet()->getCell('B4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B4:B6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENYISIHAN PIUTANG PAJAK PER 31 DESEMBER '.$tahunawal.' (Rp)');
$objPHPExcel->getActiveSheet()->getCell('C4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C4:H4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PIUTANG PAJAK PER 31 DESEMBER '.$tahunawal.' (Rp)');
$objPHPExcel->getActiveSheet()->getCell('I4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I4:N4');

$objPHPExcel->getActiveSheet()->setCellValue('C5', $tahunawal-5);
$objPHPExcel->getActiveSheet()->setCellValue('D5', $tahunawal-4);
$objPHPExcel->getActiveSheet()->setCellValue('E5', $tahunawal-3);
$objPHPExcel->getActiveSheet()->setCellValue('F5', $tahunawal-2);
$objPHPExcel->getActiveSheet()->setCellValue('G5', $tahunawal-1);
$objPHPExcel->getActiveSheet()->setCellValue('H5', $tahunawal);
$objPHPExcel->getActiveSheet()->setCellValue('I5', $tahunawal-5);$objPHPExcel->getActiveSheet()->mergeCells('I5:I6');
$objPHPExcel->getActiveSheet()->setCellValue('J5', $tahunawal-4);$objPHPExcel->getActiveSheet()->mergeCells('J5:J6');
$objPHPExcel->getActiveSheet()->setCellValue('K5', $tahunawal-3);$objPHPExcel->getActiveSheet()->mergeCells('K5:K6');
$objPHPExcel->getActiveSheet()->setCellValue('L5', $tahunawal-2);$objPHPExcel->getActiveSheet()->mergeCells('L5:L6');
$objPHPExcel->getActiveSheet()->setCellValue('M5', $tahunawal-1);$objPHPExcel->getActiveSheet()->mergeCells('M5:M6');
$objPHPExcel->getActiveSheet()->setCellValue('N5', $tahunawal);$objPHPExcel->getActiveSheet()->mergeCells('N5:N6');

$objPHPExcel->getActiveSheet()->setCellValue('C6', '(100%)');
$objPHPExcel->getActiveSheet()->setCellValue('D6', '(50%)');
$objPHPExcel->getActiveSheet()->setCellValue('E6', '(50%)');
$objPHPExcel->getActiveSheet()->setCellValue('F6', '(10%)');
$objPHPExcel->getActiveSheet()->setCellValue('G6', '(10%)');
$objPHPExcel->getActiveSheet()->setCellValue('H6', '(0.5%)');

$objPHPExcel->getActiveSheet()->setCellValue('A7', '(1)');
$objPHPExcel->getActiveSheet()->setCellValue('B7', '(2)');
$objPHPExcel->getActiveSheet()->setCellValue('C7', '(3)');
$objPHPExcel->getActiveSheet()->setCellValue('D7', '(4)');
$objPHPExcel->getActiveSheet()->setCellValue('E7', '(5)');
$objPHPExcel->getActiveSheet()->setCellValue('F7', '(6)');
$objPHPExcel->getActiveSheet()->setCellValue('G7', '(7)');
$objPHPExcel->getActiveSheet()->setCellValue('H7', '(8)');
$objPHPExcel->getActiveSheet()->setCellValue('I7', '(9)');
$objPHPExcel->getActiveSheet()->setCellValue('J7', '(10)');
$objPHPExcel->getActiveSheet()->setCellValue('K7', '(11)');
$objPHPExcel->getActiveSheet()->setCellValue('L7', '(12)');
$objPHPExcel->getActiveSheet()->setCellValue('M7', '(13)');
$objPHPExcel->getActiveSheet()->setCellValue('N7', '(14)');

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('PENYISIHAN 2');

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A4:N7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A4:N7')->getFill()->getStartColor()->setRGB('E4E4E4');
$objPHPExcel->getActiveSheet()->getStyle('A4:N7')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('size' => $fontSizeDefault, 'bold' => false));
$bold = array('font' => array('bold' => true));

$summaryPenyisihan = array(0,0,0,0,0,0,0,0,0,0,0,0);
foreach ($data as $buffer) {
    $objPHPExcel->getActiveSheet()->getRowDimension(7 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $no), $no)->getStyle('A' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $no), $buffer['name'])->getStyle('B' . (7 + $no))->applyFromArray($noBold);
    
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $no), number_format($buffer[($tahunawal-5)."TAGIHAN"], 0, '', ''))->getStyle('C' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $no), number_format($buffer[($tahunawal-4)."TAGIHAN"]*0.5, 0, '', ''))->getStyle('D' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $no), number_format($buffer[($tahunawal-3)."TAGIHAN"]*0.5, 0, '', ''))->getStyle('E' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $no), number_format($buffer[($tahunawal-2)."TAGIHAN"]*0.1, 0, '', ''))->getStyle('F' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $no), number_format($buffer[($tahunawal-1)."TAGIHAN"]*0.1, 0, '', ''))->getStyle('G' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (7 + $no), number_format($buffer[($tahunawal)."TAGIHAN"]*0.05, 0, '', ''))->getStyle('H' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (7 + $no), number_format($buffer[($tahunawal-5)."TAGIHAN"]*0, 0, '', ''))->getStyle('I' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (7 + $no), number_format($buffer[($tahunawal-4)."TAGIHAN"]*0.5, 0, '', ''))->getStyle('J' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . (7 + $no), number_format($buffer[($tahunawal-3)."TAGIHAN"]*0.5, 0, '', ''))->getStyle('K' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . (7 + $no), number_format($buffer[($tahunawal-2)."TAGIHAN"]*0.9, 0, '', ''))->getStyle('L' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . (7 + $no), number_format($buffer[($tahunawal-1)."TAGIHAN"]*0.9, 0, '', ''))->getStyle('M' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . (7 + $no), number_format($buffer[($tahunawal)."TAGIHAN"]*0.95, 0, '', ''))->getStyle('N' . (7 + $no))->applyFromArray($noBold);
    $summaryPenyisihan[0] += $buffer[($tahunawal-5)."TAGIHAN"];
    $summaryPenyisihan[1] += number_format($buffer[($tahunawal-4)."TAGIHAN"]*0.5, 0, '', '');
    $summaryPenyisihan[2] += number_format($buffer[($tahunawal-3)."TAGIHAN"]*0.5, 0, '', '');
    $summaryPenyisihan[3] += number_format($buffer[($tahunawal-2)."TAGIHAN"]*0.1, 0, '', '');
    $summaryPenyisihan[4] += number_format($buffer[($tahunawal-1)."TAGIHAN"]*0.1, 0, '', '');
    $summaryPenyisihan[5] += number_format($buffer[($tahunawal)."TAGIHAN"]*0.05, 0, '', '');
    $summaryPenyisihan[6] += number_format($buffer[($tahunawal-5)."TAGIHAN"]*0, 0, '', '');
    $summaryPenyisihan[7] += number_format($buffer[($tahunawal-4)."TAGIHAN"]*0.5, 0, '', '');
    $summaryPenyisihan[8] += number_format($buffer[($tahunawal-3)."TAGIHAN"]*0.5, 0, '', '');
    $summaryPenyisihan[9] += number_format($buffer[($tahunawal-2)."TAGIHAN"]*0.9, 0, '', '');
    $summaryPenyisihan[10] += number_format($buffer[($tahunawal-1)."TAGIHAN"]*0.9, 0, '', '');
    $summaryPenyisihan[11] += number_format($buffer[($tahunawal)."TAGIHAN"]*0.95, 0, '', '');
    $no++;
}
$objPHPExcel->getActiveSheet()->getStyle('A' . (7 + $no))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $no), 'TOTAL');
$objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $no).':B'.(7 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $no), $summaryPenyisihan[0])->getStyle('C' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $no), $summaryPenyisihan[1])->getStyle('D' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $no), $summaryPenyisihan[2])->getStyle('E' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $no), $summaryPenyisihan[3])->getStyle('F' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $no), $summaryPenyisihan[4])->getStyle('G' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('H' . (7 + $no), $summaryPenyisihan[5])->getStyle('H' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('I' . (7 + $no), $summaryPenyisihan[6])->getStyle('I' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (7 + $no), $summaryPenyisihan[7])->getStyle('J' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (7 + $no), $summaryPenyisihan[8])->getStyle('K' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('L' . (7 + $no), $summaryPenyisihan[9])->getStyle('L' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('M' . (7 + $no), $summaryPenyisihan[10])->getStyle('M' . (7 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (7 + $no), $summaryPenyisihan[11])->getStyle('N' . (7 + $no))->applyFromArray($noBold);
$no++;
$objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A4:N' . (8 + count($data)))->applyFromArray(
        array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);

$ttd = array('font' => array('size' => $fontSizeDefault, 'bold' => false, 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)));

$objPHPExcel->getActiveSheet()->setCellValue('K' . (12 + $no), $namaKotaPengesahan.', 31 DESEMBER '.$tahunawal)->getStyle('K' . (12 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('K' . (12 + $no).':M'.(12 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('K' . (13 + $no), 'KEPALA BIDANG PEMBUKUAN')->getStyle('K' . (13 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('K' . (13 + $no).':M'.(13 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('K' . (14 + $no), 'DAN PELAPORAN,')->getStyle('K' . (14 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('K' . (14 + $no).':M'.(14 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('K' . (19 + $no), $kabidNama)->getStyle('K' . (19 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('K' . (19 + $no).':M'.(19 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('K' . (20 + $no), $kabidJabatan)->getStyle('K' . (20 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('K' . (20 + $no).':M'.(20 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('K' . (21 + $no), $kabidNIP)->getStyle('K' . (21 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('K' . (21 + $no).':M'.(21 + $no));

$objPHPExcel->getActiveSheet()->setCellValue('B' . (13 + $no), 'MENGETAHUI')->getStyle('B' . (13 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('B' . (13 + $no).':D'.(13 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $no), $kadis.' '.$kota.' '.$namaKota.',')->getStyle('B' . (14 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('B' . (14 + $no).':D'.(14 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('B' . (19 + $no), $kadisNama)->getStyle('B' . (19 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('B' . (19 + $no).':D'.(19 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('B' . (20 + $no), $kadisJabatan)->getStyle('B' . (20 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('B' . (20 + $no).':D'.(20 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('B' . (21 + $no), $kadisNIP)->getStyle('B' . (21 + $no))->applyFromArray($ttd);$objPHPExcel->getActiveSheet()->mergeCells('B' . (21 + $no).':D'.(21 + $no));

$objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $no).':M' . (21 + $no))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);

//---------------------------- KEMBALI KE SHEET 0
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $no), 'NO')->getStyle('A' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('B' . (8 + $no), 'TAHUN')->getStyle('B' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('C' . (8 + $no), 'PIUTANG')->getStyle('C' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('E' . (8 + $no), 'PENYISIHAN PIUTANG')->getStyle('E' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('G' . (8 + $no), 'PIUTANG')->getStyle('G' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->mergeCells('C' . (8 + $no).':D'.(8 + $no));
$objPHPExcel->getActiveSheet()->mergeCells('E' . (8 + $no).':F'.(8 + $no));
$objPHPExcel->getActiveSheet()->mergeCells('G' . (8 + $no).':H'.(8 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $summary[11])->getStyle('C' . (9 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (9 + $no).':D'.(9 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $summary[10])->getStyle('C' . (10 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (10 + $no).':D'.(10 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (11 + $no), $summary[9])->getStyle('C' . (11 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (11 + $no).':D'.(11 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $no), $summary[8])->getStyle('C' . (12 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (12 + $no).':D'.(12 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (13 + $no), $summary[7])->getStyle('C' . (13 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (13 + $no).':D'.(13 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $no), $summary[6])->getStyle('C' . (14 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (14 + $no).':D'.(14 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $no), $summaryPenyisihan[5])->getStyle('E' . (9 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (9 + $no).':F'.(9 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $summaryPenyisihan[4])->getStyle('E' . (10 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (10 + $no).':F'.(10 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (11 + $no), $summaryPenyisihan[3])->getStyle('E' . (11 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (11 + $no).':F'.(11 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $no), $summaryPenyisihan[2])->getStyle('E' . (12 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (12 + $no).':F'.(12 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (13 + $no), $summaryPenyisihan[1])->getStyle('E' . (13 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (13 + $no).':F'.(13 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $no), $summaryPenyisihan[0])->getStyle('E' . (14 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (14 + $no).':F'.(14 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $no), $summaryPenyisihan[11])->getStyle('G' . (9 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (9 + $no).':H'.(9 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $summaryPenyisihan[10])->getStyle('G' . (10 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (10 + $no).':H'.(10 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (11 + $no), $summaryPenyisihan[9])->getStyle('G' . (11 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (11 + $no).':H'.(11 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $no), $summaryPenyisihan[8])->getStyle('G' . (12 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (12 + $no).':H'.(12 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (13 + $no), $summaryPenyisihan[7])->getStyle('G' . (13 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (13 + $no).':H'.(13 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $no), $summaryPenyisihan[6])->getStyle('G' . (14 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (14 + $no).':H'.(14 + $no));


$objPHPExcel->getActiveSheet()->setCellValue('A' . (9 + $no), '1')->getStyle('A' . (9 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), '2')->getStyle('A' . (10 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (11 + $no), '3')->getStyle('A' . (11 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $no), '4')->getStyle('A' . (12 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (13 + $no), '5')->getStyle('A' . (13 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $no), '6')->getStyle('A' . (14 + $no))->applyFromArray($noBold);

$objPHPExcel->getActiveSheet()->setCellValue('B' . (9 + $no), $tahunawal)->getStyle('B' . (9 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), ($tahunawal-1))->getStyle('B' . (10 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('B' . (11 + $no), ($tahunawal-2))->getStyle('B' . (11 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $no), ($tahunawal-3))->getStyle('B' . (12 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('B' . (13 + $no), ($tahunawal-4))->getStyle('B' . (13 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $no), ($tahunawal-5))->getStyle('B' . (14 + $no))->applyFromArray($noBold);

$total1 = $total2 = $total3 = 0;
$total1 = $summary[11] + $summary[10] + $summary[9] + $summary[8] + $summary[7] + $summary[6];
$total3 = $summaryPenyisihan[11] + $summaryPenyisihan[10] + $summaryPenyisihan[9] + $summaryPenyisihan[8] + $summaryPenyisihan[7] + $summaryPenyisihan[6];
$total2 = $summaryPenyisihan[0] + $summaryPenyisihan[1] + $summaryPenyisihan[2] + $summaryPenyisihan[3] + $summaryPenyisihan[4] + $summaryPenyisihan[5];
$objPHPExcel->getActiveSheet()->setCellValue('A' . (15 + $no), 'JUMLAH')->getStyle('A' . (15 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('A' . (15 + $no).':B'.(15 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (15 + $no), $total1)->getStyle('C' . (15 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('C' . (15 + $no).':D'.(15 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('E' . (15 + $no), $total2)->getStyle('E' . (15 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('E' . (15 + $no).':F'.(15 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('G' . (15 + $no), $total3)->getStyle('G' . (15 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('G' . (15 + $no).':H'.(15 + $no));
$objPHPExcel->getActiveSheet()->getStyle('A' . (8 + $no).':H' . (15 + $no))->applyFromArray(
        array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
$objPHPExcel->getActiveSheet()->getStyle('A' . (8 + $no).':B' . (15 + $no))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);
$objPHPExcel->getActiveSheet()->getStyle('C' . (8 + $no).':H' . (8 + $no))->applyFromArray(
        array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        )
);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (7 + $no), 'PENYISIHAN PIUTANG SESUAI  SAP NOMOR 02 TAHUN 2005, ')->getStyle('J' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (8 + $no), ' SEBAGAI BERIKUT :')->getStyle('J' . (8 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), 'NO')->getStyle('J' . (10 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), 'KLASIFIKASI PIUTANG')->getStyle('K' . (10 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('K' . (10 + $no).':L'.(10 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), 'TAHUN')->getStyle('M' . (10 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), '% PENYISIHAN')->getStyle('N' . (10 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (11 + $no), 'a')->getStyle('J' . (11 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (11 + $no), 'LANCAR')->getStyle('K' . (11 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('K' . (11 + $no).':L'.(11 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('M' . (11 + $no), $tahunawal)->getStyle('M' . (11 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (11 + $no), '0.5%')->getStyle('N' . (11 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (12 + $no), 'b')->getStyle('J' . (12 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (12 + $no), 'KURANG LANCAR')->getStyle('K' . (12 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('K' . (12 + $no).':L'.(12 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('M' . (12 + $no), ($tahunawal-1).' DAN '.($tahunawal-2))->getStyle('M' . (12 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (12 + $no), '10%')->getStyle('N' . (12 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (13 + $no), 'c')->getStyle('J' . (13 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (13 + $no), 'DIRAGUKAN')->getStyle('K' . (13 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('K' . (13 + $no).':L'.(13 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('M' . (13 + $no), ($tahunawal-3).' DAN '.($tahunawal-4))->getStyle('M' . (13 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (13 + $no), '50%')->getStyle('N' . (13 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (14 + $no), 'd')->getStyle('J' . (14 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (14 + $no), 'MACET')->getStyle('K' . (14 + $no))->applyFromArray($noBold);$objPHPExcel->getActiveSheet()->mergeCells('K' . (14 + $no).':L'.(14 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('M' . (14 + $no), ($tahunawal-3).' DAN '.($tahunawal-4))->getStyle('M' . (14 + $no))->applyFromArray($noBold);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (14 + $no), '100%')->getStyle('N' . (14 + $no))->applyFromArray($noBold);

$objPHPExcel->getActiveSheet()->getStyle('J' . (10 + $no).':N' . (14 + $no))->applyFromArray(
        array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ))
);
$objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $no).':N' . (15 + $no))->applyFromArray(
        array('font' => array(
                'size' => $fontSizeDefault
            ))
);
// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DAFTAR_PENYISIHAN_PIUTANG.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
