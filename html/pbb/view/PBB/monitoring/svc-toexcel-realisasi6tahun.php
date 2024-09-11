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

function getKelurahan($p)
{
    global $DBLink, $kelurahan;
    $query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";

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

function getRealisasi()
{
    global $DBLink, $kd, $kecnama, $kec, $thn, $date_start, $date_end, $kab;

    $periode = "";
    if ($date_start != "" && $date_end != "") {
        $periode = "and payment_paid between '{$date_start}' and '{$date_end}'"; //Antara tanggal 01 sampai tanggal 30 bulan sekarang
    }

    if ($kec)
        $kec = getKelurahan($kec);
    else
        $kec =  getKecamatan($kab);

    $c = count($kec);

    $strtahun = "SPPT_TAHUN_PAJAK in ('" . $thn . "'";
    for ($t = $thn - 1; $t >= $thn - 10; $t--) {
        $strtahun .= ",'" . $t . "'";
    }
    $strtahun .= ") ";
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        for ($j = 0; $j < 44; $j++) {
            $data[$i][$j] = '0';
        }
        $data[$i][0] = $i + 1;
        $whr = " WHERE NOP like '" . $kec[$i]["id"] . "%' " . $periode . " and payment_flag='1' and $strtahun ";
        $da = getData($whr);
        $data[$i][1] = $kec[$i]["name"];
        $data[$i][2] = $da[$thn]["wp"];
        $data[$i][3] = $da[$thn]["total"];
        $data[$i][4] = $da[$thn - 1]["wp"];
        $data[$i][5] = $da[$thn - 1]["pokok"];
        $data[$i][6] = $da[$thn - 1]["denda"];
        $data[$i][7] = $da[$thn - 1]["total"];
        $data[$i][8] = $da[$thn - 2]["wp"];
        $data[$i][9] = $da[$thn - 2]["pokok"];
        $data[$i][10] = $da[$thn - 2]["denda"];
        $data[$i][11] = $da[$thn - 2]["total"];
        $data[$i][12] = $da[$thn - 3]["wp"];
        $data[$i][13] = $da[$thn - 3]["pokok"];
        $data[$i][14] = $da[$thn - 3]["denda"];
        $data[$i][15] = $da[$thn - 3]["total"];
        $data[$i][16] = $da[$thn - 4]["wp"];
        $data[$i][17] = $da[$thn - 4]["pokok"];
        $data[$i][18] = $da[$thn - 4]["denda"];
        $data[$i][19] = $da[$thn - 4]["total"];
        $data[$i][20] = $da[$thn - 5]["wp"];
        $data[$i][21] = $da[$thn - 5]["pokok"];
        $data[$i][22] = $da[$thn - 5]["denda"];
        $data[$i][23] = $da[$thn - 5]["total"];
        $data[$i][24] = $da[$thn - 6]["wp"];
        $data[$i][25] = $da[$thn - 6]["pokok"];
        $data[$i][26] = $da[$thn - 6]["denda"];
        $data[$i][27] = $da[$thn - 6]["total"];
        $data[$i][28] = $da[$thn - 7]["wp"];
        $data[$i][29] = $da[$thn - 7]["pokok"];
        $data[$i][30] = $da[$thn - 7]["denda"];
        $data[$i][31] = $da[$thn - 7]["total"];
        $data[$i][32] = $da[$thn - 8]["wp"];
        $data[$i][33] = $da[$thn - 8]["pokok"];
        $data[$i][34] = $da[$thn - 8]["denda"];
        $data[$i][35] = $da[$thn - 8]["total"];
        $data[$i][36] = $da[$thn - 9]["wp"];
        $data[$i][37] = $da[$thn - 9]["pokok"];
        $data[$i][38] = $da[$thn - 9]["denda"];
        $data[$i][39] = $da[$thn - 9]["total"];
        $data[$i][40] = $da[$thn - 10]["wp"];
        $data[$i][41] = $da[$thn - 10]["pokok"];
        $data[$i][42] = $da[$thn - 10]["denda"];
        $data[$i][43] = $da[$thn - 10]["total"];
    }
    return $data;
}

function getData($where)
{
    global $myDBLink, $thn;

    $myDBLink = openMysql();
    $return = array();
    for ($t = $thn; $t >= $thn - 10; $t--) {
        $return[$t]["pokok"] = 0;
        $return[$t]["denda"] = 0;
        $return[$t]["total"] = 0;
        $return[$t]["wp"] = 0;
    }

    $query = " SELECT COUNT(*) AS wp, sum(SPPT_PBB_HARUS_DIBAYAR) as pokok, sum(PBB_DENDA) as denda, sum(PBB_TOTAL_BAYAR) as total,  SPPT_TAHUN_PAJAK FROM PBB_SPPT 
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
        $return[$row["SPPT_TAHUN_PAJAK"]]["denda"] = ($row["denda"] != "") ? $row["denda"] : 0;
        $return[$row["SPPT_TAHUN_PAJAK"]]["total"] = ($row["total"] != "") ? $row["total"] : 0;
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
// $kab = $appConfig['KODE_KOTA'];
// $thn = $appConfig['tahun_tagihan'];
// $date_start = @isset($_REQUEST['ds']) ? $_REQUEST['ds'] : "2014-01-01";
// $date_end = @isset($_REQUEST['de']) ? $_REQUEST['de'] : "2014-05-30";
// $kec = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
// $kecnama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
// $date_start = $date_start.' 00:00:00';
// $date_end = $date_end.' 23:59:59';

$kab = $appConfig['KODE_KOTA'];
$thn = $appConfig['tahun_tagihan'];
$date_start = ($_REQUEST['ds'] != '') ? $_REQUEST['ds'] : "";
$date_end = ($_REQUEST['de'] != '') ? $_REQUEST['de'] : "";
$kec = ($_REQUEST['kc'] != '') ? $_REQUEST['kc'] : "";
$kecnama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";

$headerKec = $kec;

// var_dump($kec);exit;
if ($date_start != "") {
    //$date_start = $date_start . ' 00:00:00';
	$date_start = $date_start.'-01-01 00:00:00';
}

if ($date_start != "") {
    // $date_start = $date_start.' 00:00:00';
    //$date_end = $date_end . ' 23:59:59';
	$date_end = $date_end.'-12-31 23:59:59';
}

$dt = getRealisasi();

// var_dump($dt);exit;

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

$objRichText->createText('REALISASI (TAHUN BERJALAN DAN TUNGGAKAN)' . (($kec) ? ' KECAMATAN ' . $kecnama : ''));
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:AR1');
$objPHPExcel->getActiveSheet()->getStyle('A1:AR1')->applyFromArray(
    array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);


//// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A3:A7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText((($headerKec != "") ? 'KELURAHAN' : 'KECAMATAN'));
$objPHPExcel->getActiveSheet()->getCell('B3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B3:B7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALIASI PBB TANGGAL');
$objPHPExcel->getActiveSheet()->getCell('C3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C3:AR3');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TAHUN BERJALAN');
$objPHPExcel->getActiveSheet()->getCell('C4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TAHUN TUNGGAKAN');
$objPHPExcel->getActiveSheet()->getCell('E4')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E4:AR4');
// $objRichText = new PHPExcel_RichText();
// $objRichText->createText('TAHUN TUNGGAKAN');
// $objPHPExcel->getActiveSheet()->getCell('E4')->setValue($objRichText);
// $objPHPExcel->getActiveSheet()->mergeCells('E4:X4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn);
$objPHPExcel->getActiveSheet()->getCell('C5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C5:D5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 1);
$objPHPExcel->getActiveSheet()->getCell('E5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E5:H5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 2);
$objPHPExcel->getActiveSheet()->getCell('I5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I5:L5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 3);
$objPHPExcel->getActiveSheet()->getCell('M5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M5:P5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 4);
$objPHPExcel->getActiveSheet()->getCell('Q5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Q5:T5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 5);
$objPHPExcel->getActiveSheet()->getCell('U5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('U5:X5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 6);
$objPHPExcel->getActiveSheet()->getCell('Y5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Y5:AB5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 7);
$objPHPExcel->getActiveSheet()->getCell('AC5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AC5:AF5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 8);
$objPHPExcel->getActiveSheet()->getCell('AG5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AG5:AJ5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 9);
$objPHPExcel->getActiveSheet()->getCell('AK5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AK5:AN5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText($thn - 10);
$objPHPExcel->getActiveSheet()->getCell('AO5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AO5:AR5');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('C6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C6:C7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('D6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D6:D7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('E6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E6:E7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('I6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I6:I7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('M6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M6:M7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('Q6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Q6:Q7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('U6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('U6:U7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('Y6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Y6:Y7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('AC6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AC6:AC7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('AG6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AG6:AG7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('AK6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AK6:AK7');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP');
$objPHPExcel->getActiveSheet()->getCell('AO6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AO6:AO7');


$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('F6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('F6:H6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('J6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J6:L6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('N6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('N6:P6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('R6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('R6:T6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('V6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('V6:X6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('Z6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Z6:AB6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('AD6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AD6:AF6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('AH6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AH6:AJ6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('AL6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AL6:AN6');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('RP');
$objPHPExcel->getActiveSheet()->getCell('AP6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('AP6:AR6');


$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('F7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('J7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('N7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('R7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('V7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('Z7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('AD7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('AH7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('AL7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('POKOK');
$objPHPExcel->getActiveSheet()->getCell('AP7')->setValue($objRichText);

$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('G7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('K7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('M7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('S7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('W7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('AA7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('AE7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('AI7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('AM7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DENDA');
$objPHPExcel->getActiveSheet()->getCell('AQ7')->setValue($objRichText);

$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('H7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('L7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('N7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('T7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('X7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('AB7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('AF7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('AJ7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('AN7')->setValue($objRichText);
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TOTAL');
$objPHPExcel->getActiveSheet()->getCell('AR7')->setValue($objRichText);
// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A3:AR7')->applyFromArray(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:AR50')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(15);

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
$summary['14'] = 0;
$summary['15'] = 0;
$summary['16'] = 0;
$summary['17'] = 0;
$summary['18'] = 0;
$summary['19'] = 0;
$summary['20'] = 0;
$summary['21'] = 0;
$summary['22'] = 0;
$summary['23'] = 0;
$summary['24'] = 0;
$summary['25'] = 0;
$summary['26'] = 0;
$summary['27'] = 0;
$summary['28'] = 0;
$summary['29'] = 0;
$summary['30'] = 0;
$summary['31'] = 0;
$summary['32'] = 0;
$summary['33'] = 0;
$summary['34'] = 0;
$summary['35'] = 0;
$summary['36'] = 0;
$summary['37'] = 0;
$summary['38'] = 0;
$summary['39'] = 0;
$summary['40'] = 0;
$summary['41'] = 0;
$summary['42'] = 0;
$summary['43'] = 0;

for ($i = 0; $i < $c; $i++) {
    $objPHPExcel->getActiveSheet()->getRowDimension(7 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (7 + $no), $dt[$i][1]);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $no), $dt[$i][2])->getStyle('C' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $no), $dt[$i][3])->getStyle('D' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $no), $dt[$i][4])->getStyle('E' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $no), $dt[$i][5])->getStyle('F' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $no), $dt[$i][6])->getStyle('G' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (7 + $no), $dt[$i][7])->getStyle('H' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (7 + $no), $dt[$i][8])->getStyle('I' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (7 + $no), $dt[$i][9])->getStyle('J' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . (7 + $no), $dt[$i][10])->getStyle('K' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . (7 + $no), $dt[$i][11])->getStyle('L' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . (7 + $no), $dt[$i][12])->getStyle('M' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . (7 + $no), $dt[$i][13])->getStyle('N' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('O' . (7 + $no), $dt[$i][14])->getStyle('O' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('P' . (7 + $no), $dt[$i][15])->getStyle('P' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . (7 + $no), $dt[$i][16])->getStyle('Q' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('R' . (7 + $no), $dt[$i][17])->getStyle('R' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('S' . (7 + $no), $dt[$i][18])->getStyle('S' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('T' . (7 + $no), $dt[$i][19])->getStyle('T' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('U' . (7 + $no), $dt[$i][20])->getStyle('U' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('V' . (7 + $no), $dt[$i][21])->getStyle('V' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('W' . (7 + $no), $dt[$i][22])->getStyle('W' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('X' . (7 + $no), $dt[$i][23])->getStyle('X' . (7 + $no))->applyFromArray($noBold);

    $objPHPExcel->getActiveSheet()->setCellValue('Y' . (7 + $no), $dt[$i][24])->getStyle('Y' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('Z' . (7 + $no), $dt[$i][25])->getStyle('Z' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AA' . (7 + $no), $dt[$i][26])->getStyle('AA' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AB' . (7 + $no), $dt[$i][27])->getStyle('AB' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AC' . (7 + $no), $dt[$i][28])->getStyle('AC' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AD' . (7 + $no), $dt[$i][29])->getStyle('AD' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AE' . (7 + $no), $dt[$i][30])->getStyle('AE' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AF' . (7 + $no), $dt[$i][31])->getStyle('AF' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AG' . (7 + $no), $dt[$i][32])->getStyle('AG' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AH' . (7 + $no), $dt[$i][33])->getStyle('AH' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AI' . (7 + $no), $dt[$i][34])->getStyle('AI' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AJ' . (7 + $no), $dt[$i][35])->getStyle('AJ' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AK' . (7 + $no), $dt[$i][36])->getStyle('AK' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AL' . (7 + $no), $dt[$i][37])->getStyle('AL' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AM' . (7 + $no), $dt[$i][38])->getStyle('AM' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AN' . (7 + $no), $dt[$i][39])->getStyle('AN' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AO' . (7 + $no), $dt[$i][40])->getStyle('AO' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AP' . (7 + $no), $dt[$i][41])->getStyle('AP' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AQ' . (7 + $no), $dt[$i][42])->getStyle('AQ' . (7 + $no))->applyFromArray($noBold);
    $objPHPExcel->getActiveSheet()->setCellValue('AR' . (7 + $no), $dt[$i][43])->getStyle('AR' . (7 + $no))->applyFromArray($noBold);
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
    $summary['14'] += $dt[$i][14];
    $summary['15'] += $dt[$i][15];
    $summary['16'] += $dt[$i][16];
    $summary['17'] += $dt[$i][17];
    $summary['18'] += $dt[$i][18];
    $summary['19'] += $dt[$i][19];
    $summary['20'] += $dt[$i][20];
    $summary['21'] += $dt[$i][21];
    $summary['22'] += $dt[$i][22];
    $summary['23'] += $dt[$i][23];
    $summary['24'] += $dt[$i][4];
    $summary['25'] += $dt[$i][5];
    $summary['26'] += $dt[$i][6];
    $summary['27'] += $dt[$i][7];
    $summary['28'] += $dt[$i][8];
    $summary['29'] += $dt[$i][9];
    $summary['30'] += $dt[$i][10];
    $summary['31'] += $dt[$i][11];
    $summary['32'] += $dt[$i][12];
    $summary['33'] += $dt[$i][13];
    $summary['34'] += $dt[$i][14];
    $summary['35'] += $dt[$i][15];
    $summary['36'] += $dt[$i][16];
    $summary['37'] += $dt[$i][17];
    $summary['38'] += $dt[$i][18];
    $summary['39'] += $dt[$i][19];
    $summary['40'] += $dt[$i][20];
    $summary['41'] += $dt[$i][21];
    $summary['42'] += $dt[$i][22];
    $summary['43'] += $dt[$i][23];
}

$objPHPExcel->getActiveSheet()->setCellValue('A' . (7 + $no), 'JUMLAH');
$objPHPExcel->getActiveSheet()->mergeCells('A' . (7 + $no) . ':B' . (7 + $no));
$objPHPExcel->getActiveSheet()->setCellValue('C' . (7 + $no), $summary['2']);
$objPHPExcel->getActiveSheet()->setCellValue('D' . (7 + $no), $summary['3']);
$objPHPExcel->getActiveSheet()->setCellValue('E' . (7 + $no), $summary['4']);
$objPHPExcel->getActiveSheet()->setCellValue('F' . (7 + $no), $summary['5']);
$objPHPExcel->getActiveSheet()->setCellValue('G' . (7 + $no), $summary['6']);
$objPHPExcel->getActiveSheet()->setCellValue('H' . (7 + $no), $summary['7']);
$objPHPExcel->getActiveSheet()->setCellValue('I' . (7 + $no), $summary['8']);
$objPHPExcel->getActiveSheet()->setCellValue('J' . (7 + $no), $summary['9']);
$objPHPExcel->getActiveSheet()->setCellValue('K' . (7 + $no), $summary['10']);
$objPHPExcel->getActiveSheet()->setCellValue('L' . (7 + $no), $summary['11']);
$objPHPExcel->getActiveSheet()->setCellValue('M' . (7 + $no), $summary['12']);
$objPHPExcel->getActiveSheet()->setCellValue('N' . (7 + $no), $summary['13']);
$objPHPExcel->getActiveSheet()->setCellValue('O' . (7 + $no), $summary['14']);
$objPHPExcel->getActiveSheet()->setCellValue('P' . (7 + $no), $summary['15']);
$objPHPExcel->getActiveSheet()->setCellValue('Q' . (7 + $no), $summary['16']);
$objPHPExcel->getActiveSheet()->setCellValue('R' . (7 + $no), $summary['17']);
$objPHPExcel->getActiveSheet()->setCellValue('S' . (7 + $no), $summary['18']);
$objPHPExcel->getActiveSheet()->setCellValue('T' . (7 + $no), $summary['19']);
$objPHPExcel->getActiveSheet()->setCellValue('U' . (7 + $no), $summary['20']);
$objPHPExcel->getActiveSheet()->setCellValue('V' . (7 + $no), $summary['21']);
$objPHPExcel->getActiveSheet()->setCellValue('W' . (7 + $no), $summary['22']);
$objPHPExcel->getActiveSheet()->setCellValue('X' . (7 + $no), $summary['23']);
$objPHPExcel->getActiveSheet()->setCellValue('Y' . (7 + $no), $summary['24']);
$objPHPExcel->getActiveSheet()->setCellValue('Z' . (7 + $no), $summary['25']);
$objPHPExcel->getActiveSheet()->setCellValue('AA' . (7 + $no), $summary['26']);
$objPHPExcel->getActiveSheet()->setCellValue('AB' . (7 + $no), $summary['27']);
$objPHPExcel->getActiveSheet()->setCellValue('AC' . (7 + $no), $summary['28']);
$objPHPExcel->getActiveSheet()->setCellValue('AD' . (7 + $no), $summary['29']);
$objPHPExcel->getActiveSheet()->setCellValue('AE' . (7 + $no), $summary['30']);
$objPHPExcel->getActiveSheet()->setCellValue('AF' . (7 + $no), $summary['31']);
$objPHPExcel->getActiveSheet()->setCellValue('AG' . (7 + $no), $summary['32']);
$objPHPExcel->getActiveSheet()->setCellValue('AH' . (7 + $no), $summary['33']);
$objPHPExcel->getActiveSheet()->setCellValue('AI' . (7 + $no), $summary['34']);
$objPHPExcel->getActiveSheet()->setCellValue('AJ' . (7 + $no), $summary['35']);
$objPHPExcel->getActiveSheet()->setCellValue('AK' . (7 + $no), $summary['36']);
$objPHPExcel->getActiveSheet()->setCellValue('AL' . (7 + $no), $summary['37']);
$objPHPExcel->getActiveSheet()->setCellValue('AM' . (7 + $no), $summary['38']);
$objPHPExcel->getActiveSheet()->setCellValue('AN' . (7 + $no), $summary['39']);
$objPHPExcel->getActiveSheet()->setCellValue('AO' . (7 + $no), $summary['40']);
$objPHPExcel->getActiveSheet()->setCellValue('AP' . (7 + $no), $summary['41']);
$objPHPExcel->getActiveSheet()->setCellValue('AQ' . (7 + $no), $summary['42']);
$objPHPExcel->getActiveSheet()->setCellValue('AR' . (7 + $no), $summary['43']);

//$objPHPExcel->getActiveSheet()->setCellValue('A'.(8+$no), '');
$objPHPExcel->getActiveSheet()->getStyle('A8:AR' . (8 + count($dt)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporting_realisasi_' . date('Ymdhis') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
