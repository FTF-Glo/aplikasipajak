<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
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

// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
// ini_set('display_errors', 1);


$myDBLink = "";

function headerMonitoringRealisasi($mod, $nama) {
    global $appConfig;
    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $nama;
    }
    $html = "<table class='table table-bordered table-striped'><tr><th colspan=7 class=tleft>{$dl}</th></tr>
	  <tr>
		<th rowspan=2>NO</th>
		<th rowspan=2>{$model}</th>
		<th rowspan=2>SISA PIUTANG</th>
		<th colspan=3>REALISASI</th>
		<th rowspan=2>SALDO PIUTANG</th>
	  </tr>
	  <tr>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
	  </tr>
	";
    return $html;
}

// koneksi postgres
function openMysql() {
    global $appConfig;
    $host = $appConfig['GW_DBHOST'];
    $port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user = $appConfig['GW_DBUSER'];
    $pass = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    $myDBLink = mysqli_connect($host , $user, $pass, $dbname,$port);
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
    global $myDBLink, $kd, $thn, $bulan, $where_plus;

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
            . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM PBB_SPPT {$whr} {$where_plus}";
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
			  WHERE (PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1' OR (PAYMENT_FLAG = '1' AND DATE(PAYMENT_PAID) >= '{$tglawal}')) $whr $where_plus ";
    // echo $query, ' whereNICH:', $where, ' wherePLUSNICH', $where_plus;
    // echo "<br>";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink) . ' funct: getDataPiutang';
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {      
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function showTable($mod = 0, $nama = "") {

    $dt = getPiutang($mod);
    $dt2 = getBulanSekarang($mod);
    
    $c = count($dt);
    $html = "<div id=\"frame-tbl-monitoringx\" class=\"tbl-monitoring\">";
    $a = 1;
    $html .= headerMonitoringRealisasi($mod, $nama);
    
    $summary = array('name' => 'JUMLAH', 'ketetapan_rp' => 0,
        'sisa' => 0, 
        'rbi_pokok' => 0, 
        'rbi_denda' => 0, 
        'rbi_total' => 0);
    
    for ($i = 0; $i < $c; $i++) {
        $dtname = $dt[$i]["name"];
        $rp = number_format($dt[$i]["POKOK"], 0, ",", ".");
        $pokokn = number_format($dt2[$i]["POKOK"], 0, ",", ".");
        $dendan = number_format($dt2[$i]["DENDA"], 0, ",", ".");
        $totaln = number_format($dt2[$i]["TOTAL"], 0, ",", ".");
        $rpsisa = number_format($dt[$i]["POKOK"] - $dt2[$i]["POKOK"], 0, ",", ".");
        $html .= "<tr class=tright>
	            <td>{$a}</td>
	            <td class=tleft>{$dtname}</td>
	            <td>{$rp}</td>
	            <td>{$pokokn}</td>
	            <td>{$dendan}</td>
	            <td>{$totaln}</td>
	            <td>{$rpsisa}</td>
	          </tr>";

        $summary['ketetapan_rp'] += $dt[$i]["POKOK"];
        $summary['rbi_pokok'] += $dt2[$i]["POKOK"];
        $summary['rbi_denda'] += $dt2[$i]["DENDA"];
        $summary['rbi_total'] += $dt2[$i]["TOTAL"];
        $summary['sisa'] += $dt[$i]["POKOK"] - $dt2[$i]["POKOK"];

        $a++;
    }

    $html .= "<tr class='tright tbold'>
            <td colspan=2>" . $summary['name'] . "</td>
            <td>" . number_format($summary['ketetapan_rp'], 0, ',', '.') . "</td>
            <td>" . number_format($summary['rbi_pokok'], 0, ',', '.') . "</td>
            <td>" . number_format($summary['rbi_denda'], 0, ',', '.') . "</td>
            <td>" . number_format($summary['rbi_total'], 0, ',', '.') . "</td>
            <td>" . number_format($summary['sisa'], 0, ',', '.') . "</td>
          </tr>";

    return $html . "</table>";
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$kd 		= $appConfig['KODE_KOTA'];
$kab 		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];

$buku  = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";
$kecamatan 	= @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";
$kelurahan 	= @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "";
$tahunawal 	= @isset($_REQUEST['tahunawal']) ? $_REQUEST['tahunawal'] : "";
$tahunakhir = @isset($_REQUEST['tahunakhir']) ? $_REQUEST['tahunakhir'] : "";
$namakec 	= @isset($_REQUEST['namakec']) ? $_REQUEST['namakec'] : "";
$namakel 	= @isset($_REQUEST['namakel']) ? $_REQUEST['namakel'] : "";
$tglawal	= @isset($_REQUEST['tglawal']) ? $_REQUEST['tglawal'] : "";
$tglakhir 	= @isset($_REQUEST['tglakhir']) ? $_REQUEST['tglakhir'] : "";
$bank 		= @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";
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
$where_plus = $where ? (" AND".$where) : '';


if ($kecamatan == "" && $kelurahan=="") {
    echo showTable();
} else {
    echo showTable(1, $namakec);
}
?>