<?php

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
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

date_default_timezone_set("Asia/Jakarta");

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

function headerTABLE($mod, $nama)
{
    global $appConfig, $tahunakhir, $tahunawal;

    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        //var_dump($nama);
        $dl = $nama;
    }

    $tahunx = ($tahunakhir==$tahunawal) ? $tahunakhir : $tahunawal . ' - ' . $tahunakhir;

    $html = "
    <h4 style=\"margin:15px auto\">" . $dl . "</h4>
    <div class=\"col-md-1\"></div>
    <div class=\"col-md-10\">
    <table class=\"table table-bordered table-striped table-hover\" style=\"width:850px\">
        <tr>
            <th rowspan=2 width=10>NO</th>
            <th rowspan=2>{$model}</th>
            <th rowspan=2>PIUTANG {$tahunx}</th>
            <th colspan=3>REALISASI PIUTANG</th>
            <th rowspan=2>SISA PIUTANG</th>
        </tr>
        <tr>
            <th width=120>POKOK</th>
            <th width=80>DENDA</th>
            <th width=130>TOTAL</th>
        </tr>
	";
    return $html;
}

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

    // print_r($kec);
    // exit;

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
            $whr_kec .= " AND s.NOP LIKE '" . $kec[$i]["id"] . "%' ";
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
    // else{
    // $periode = " AND s.PAYMENT_PAID >= '".date('Y-m-d')." 23:59:59' ";
    // }
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
    if ($bank != "") {
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
    global $namakec, $namakel, $nop;

    $dt = getTotalPIUTANG($mod);
    // print_r($dt);exit;
    $dt2 = getRealisasiPIUTANG($mod);

    $c = count($dt);
    $html = "";
    $a = 1;
    $html = headerTABLE($mod, $nama);

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
        $rp = number_format($dt[$i]["POKOK"], 0, ",", ".");
        $pokokn = number_format($dt2[$i]["POKOK"], 0, ",", ".");
        $dendan = number_format($dt2[$i]["DENDA"], 0, ",", ".");
        $totaln = number_format($dt2[$i]["TOTAL"], 0, ",", ".");
        $rpSISAX = $dt[$i]["POKOK"] - $dt2[$i]["TOTAL"];
        $rpSISAX =($rpSISAX < 0 ) ? 0 : $rpSISAX;
        $rpsisa = number_format($rpSISAX, 0, ",", ".");
        $html .= "<tr class=tright>
            <td>{$a}</td>
            <td class=tleft>{$dtname}</td>
            <td>{$rp}</td>
            <td>{$pokokn}</td>
            <td>{$dendan}</td>
            <td>{$totaln}</td>
            <td>{$rpsisa}</td>
        </tr>";

        $summary['totalpiutang'] += $dt[$i]["POKOK"];
        $summary['rbi_pokok'] += $dt2[$i]["POKOK"];
        $summary['rbi_denda'] += $dt2[$i]["DENDA"];
        $summary['rbi_total'] += $dt2[$i]["TOTAL"];
        $summary['sisa'] += $rpSISAX;

        $a++;
    }

    $html .= "<tr class=tright style=\"background:#ddd\">
        <td colspan=2>" . $summary['name'] . "</td>
        <td>" . number_format($summary['totalpiutang'], 0, ',', '.') . "</td>
        <td>" . number_format($summary['rbi_pokok'], 0, ',', '.') . "</td>
        <td>" . number_format($summary['rbi_denda'], 0, ',', '.') . "</td>
        <td>" . number_format($summary['rbi_total'], 0, ',', '.') . "</td>
        <td>" . number_format($summary['sisa'], 0, ',', '.') . "</td>
    </tr>";

    return $html . "</table></div>";
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
    // echo $query.'<br/>';exit;
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return["POKOK"] = $row["POKOK"];
        $return["DENDA"] = $row["DENDA"];
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
                SUM(d.PBB_DENDA) AS DENDA 
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

//echo $s;

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
//$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
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


if ($kecamatan == "") {
    echo showTable();
} else if ($kelurahan == "") {
    echo showTable(1, $namakec);
} else {
    echo showTable(2, $namakel);
}
