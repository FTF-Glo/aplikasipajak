<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
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

//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
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
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname,$port);
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

function getPiutang($mod) {
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
    if ($tahunawal != "") {
        $tahun = "and sppt_tahun_pajak >= '{$tahunawal}' ";
    }
    if ($tahunakhir != "") {
        $tahun .= "and sppt_tahun_pajak <= '{$tahunakhir}' ";
    }
    
    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $tahun;
        $da = getDataPiutang($whr);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["POKOK"] = $da["POKOK"];
    }

    return $data;
}

function getBulanSekarang($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $tahunawal, $tahunakhir, $kab, $tglawal, $tglakhir, $bank;

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
        $periode = "and payment_paid >= '{$tglawal}' ";
    }else{
        $periode = "and payment_paid >= '".date('Y-m-d')." 23:59:59' ";
    }
    if ($tglakhir != "") {
        $periode .= "and payment_paid <= '{$tglakhir}' ";
    }
    
    $tahun = "";
    if ($tahunawal != "") {
        $tahun = "and sppt_tahun_pajak >= '{$tahunawal}' ";
    }
    if ($tahunakhir != "") {
        $tahun .= "and sppt_tahun_pajak <= '{$tahunakhir}' ";
    }
    
    $selectbank = '';
    if ($bank != "") {
        $selectbank = "and PAYMENT_BANK_CODE IN ('".str_replace(",", "','", $bank)."') ";
    }

    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $periode . " and payment_flag='1' " . $tahun.$selectbank ;
        $da = getData($whr);
        $data[$i]["POKOK"] = $da["POKOK"];
        $data[$i]["DENDA"] = $da["DENDA"];
        $data[$i]["TOTAL"] = $da["TOTAL"];
    }
    return $data;
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
    // echo $query.'<br/>';
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
    global $myDBLink, $tahunakhir,$where_plus,$tglawal;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["WP"] = 0;
    $whr = "";
    if ($where) {
        $whr = " AND {$where}";
    }

    $query = "SELECT sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK FROM PBB_SPPT
			  WHERE (PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1' OR (PAYMENT_FLAG = '1' AND PAYMENT_PAID > '{$tglawal} 00:00:00')) $whr $where_plus ";
    // echo $query;
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {      
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function showTable($mod = 0, $nama = "") {
    global $eperiode;
    $dt = getPiutang($mod);
    $dt2 = getBulanSekarang($mod);

    $c = count($dt);
    $html = "";
    $a = 1;

    $summary = array('name' => 'JUMLAH', 'ketetapan_rp' => 0,
        'sisa' => 0, 
        'rbi_pokok' => 0, 
        'rbi_denda' => 0, 
        'rbi_total' => 0);

    for ($i = 0; $i < $c; $i++) {
        $dtname = $dt[$i]["name"];
        $rp = number_format($dt[$i]["POKOK"], 0, ",", ".");
        $tmp = array(
            "name" => $dt[$i]["name"],
            "ketetapan_rp" => number_format($dt[$i]["POKOK"], 0, "", ""),
            "rbi_pokok" => number_format($dt2[$i]["POKOK"], 0, "", ""),
            "rbi_denda" => number_format($dt2[$i]["DENDA"], 0, "", ""),
            "rbi_total" => number_format($dt2[$i]["TOTAL"], 0, "", ""),
            "sisa" => number_format($dt[$i]["POKOK"] - $dt2[$i]["POKOK"], 0, "", "")
        );
        $data[] = $tmp;
        $summary['ketetapan_rp'] += $dt[$i]["POKOK"];
        $summary['rbi_pokok'] += $dt2[$i]["POKOK"];
        $summary['rbi_denda'] += $dt2[$i]["DENDA"];
        $summary['rbi_total'] += $dt2[$i]["TOTAL"];
        $summary['sisa'] += $dt[$i]["POKOK"] - $dt2[$i]["POKOK"];

        $a++;
    }

    $data[] = $summary;

    return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$kd 		= $appConfig['KODE_KOTA'];

$kab 		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 	= @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";
$kelurahan 	= @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "";
$tahunawal 	= @isset($_REQUEST['tahunawal']) ? $_REQUEST['tahunawal'] : "";
$tahunakhir = @isset($_REQUEST['tahunakhir']) ? $_REQUEST['tahunakhir'] : "";
$namakec 	= @isset($_REQUEST['namakec']) ? $_REQUEST['namakec'] : "";
$namakel 	= @isset($_REQUEST['namakel']) ? $_REQUEST['namakel'] : "";
$tglawal 	= @isset($_REQUEST['tglawal']) ? $_REQUEST['tglawal'] : "";
$tglakhir 	= @isset($_REQUEST['tglakhir']) ? $_REQUEST['tglakhir'] : "";
$bank 		= @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";
$nmbank     = @isset($_REQUEST['nmbank']) ? $_REQUEST['nmbank'] : "";

$buku 	= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";
$arrWhere = array();
if($buku != 0){
    switch ($buku){
        case 1      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "); break;
        case 12     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "); break;
        case 123    : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
        case 1234   : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 12345  : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 2      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "); break;
        case 23     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
        case 234    : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 2345   : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 3      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
        case 34     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 345    : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 4      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 45     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 5      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
    }
}
$where = implode (" AND ",$arrWhere);
$where_plus = " AND".$where;

if ($kecamatan == "" && $kelurahan=="") {
    $data = showTable();
} else {
    $data = showTable(1, $namakec);
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
if($tahunakhir!= '' && $tahunawal < $tahunakhir) $strTahun = $tahunawal.' s/d '.$tahunakhir;
else $strTahun = $tahunawal;
$objRichText->createText(': PIUTANG PBB TAHUN PAJAK '.$strTahun);
$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D1:G1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . convertDate($tglawal).' s/d '.convertDate($tglakhir));
$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D2:G2');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $nmbank);

$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $buku);
$objPHPExcel->getActiveSheet()->getCell('D4')->setValue($objRichText);


$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:G3');


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'TANGGAL BAYAR');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BANK');
$objPHPExcel->getActiveSheet()->setCellValue('C4', 'BUKU');

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
} else {
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

$objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('SISA PIUTANG');
$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C8:C9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI');
$objPHPExcel->getActiveSheet()->getCell('D8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D8:F8');
$objPHPExcel->getActiveSheet()->setCellValue('D9', 'POKOK');
$objPHPExcel->getActiveSheet()->setCellValue('E9', 'DENDA');
$objPHPExcel->getActiveSheet()->setCellValue('F9', 'TOTAL');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('SALDO PIUTANG');
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
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $buffer['ketetapan_rp']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $no), $buffer['rbi_pokok']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $no), $buffer['rbi_denda']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $no), $buffer['rbi_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $no), $buffer['sisa']);
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $buffer['ketetapan_rp'])->getStyle('C' . (9 + $no))->applyFromArray($noBold);
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
$objPHPExcel->getActiveSheet()->getStyle('B10:G' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DAFTAR_PIUTANG.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>