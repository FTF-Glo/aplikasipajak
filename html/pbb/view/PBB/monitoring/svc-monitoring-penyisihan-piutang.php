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

function headerMonitoringRealisasi($mod, $nama) {
    global $appConfig, $tahunawal;
    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $nama;
    }
    $html = "<div class=\"tbl-monitoring responsive\">
        <table class=\"table table-bordered table-striped table-hover\">
        <tr><th colspan=26 class=tleft>{$dl}</th></tr>
	  
	  <tr>
		<th rowspan=3 width=10>NO</th>
		<th rowspan=3>{$model}</th>
		<th colspan=6>TOTAL OBJEK PAJAK<br/>SPPT</th>
		<th colspan=6>PIUTANG PAJAK BUMI DAN BANGUNAN (PBB)TAHUN ".$tahunawal."<br/>(Rp)</th>
		<th colspan=6>PENYISIHAN PIUTANG PAJAK PER 31 DESEMBER ".$tahunawal."<br/>(Rp)</th>
		<th colspan=6>PIUTANG PAJAK PER 31 DESEMBER ".$tahunawal."<br/>(Rp)</th>
	  </tr>
	  <tr>
		<th rowspan=2>".($tahunawal-5)."</th>
		<th rowspan=2>".($tahunawal-4)."</th>
		<th rowspan=2>".($tahunawal-3)."</th>
		<th rowspan=2>".($tahunawal-2)."</th>
		<th rowspan=2>".($tahunawal-1)."</th>
		<th rowspan=2>".($tahunawal)."</th>
		<th rowspan=2>".($tahunawal-5)."</th>
		<th rowspan=2>".($tahunawal-4)."</th>
		<th rowspan=2>".($tahunawal-3)."</th>
		<th rowspan=2>".($tahunawal-2)."</th>
		<th rowspan=2>".($tahunawal-1)."</th>
		<th rowspan=2>".($tahunawal)."</th>
		<th>".($tahunawal-5)."</th>
		<th>".($tahunawal-4)."</th>
		<th>".($tahunawal-3)."</th>
		<th>".($tahunawal-2)."</th>
		<th>".($tahunawal-1)."</th>
		<th>".($tahunawal)."</th>
		<th rowspan=2>".($tahunawal-5)."</th>
		<th rowspan=2>".($tahunawal-4)."</th>
		<th rowspan=2>".($tahunawal-3)."</th>
		<th rowspan=2>".($tahunawal-2)."</th>
		<th rowspan=2>".($tahunawal-1)."</th>
		<th rowspan=2>".($tahunawal)."</th>
	  </tr>
      <tr>
		<th>100%</th>
		<th>50%</th>
		<th>50%</th>
		<th>10%</th>
		<th>10%</th>
		<th>5%</th>
	  </tr>
	";
    
    return $html;
}

function headerMonitoringPenyisihanPiutangKel(){
    global $appConfig, $tahunawal, $namakec, $namakel;
    $model 	= strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl 	= "KELURAHAN" . " " . $namakel;
	
    $html = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">
        <table width=\"2200\" cellspacing=\"1\" cellpadding=\"4\" border=\"0\">
        <tr><th colspan=\"26\" style=\"text-align:left;\"><b>{$dl}<b></th></tr>
	  
	  <tr>
		<th rowspan=\"3\" width=\"28\" align=\"center\">NO</th>
		<th rowspan=\"3\" width=\"117\" align=\"center\">NOP</th>
		<th rowspan=\"3\" width=\"117\" align=\"center\">NAMA</th>
		<th colspan=\"6\"align=\"center\">PIUTANG PAJAK BUMI DAN BANGUNAN (PBB)TAHUN ".$tahunawal."<br/>(Rp)</th>
		<th colspan=\"6\"align=\"center\">PENYISIHAN PIUTANG PAJAK PER 31 DESEMBER ".$tahunawal."<br/>(Rp)</th>
		<th colspan=\"6\"align=\"center\">PIUTANG PAJAK PER 31 DESEMBER ".$tahunawal."<br/>(Rp)</th>
	  </tr>
	  <tr>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-5)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-4)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-3)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-2)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-1)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal)."</th>
		<th align=\"center\">".($tahunawal-5)."</th>
		<th align=\"center\">".($tahunawal-4)."</th>
		<th align=\"center\">".($tahunawal-3)."</th>
		<th align=\"center\">".($tahunawal-2)."</th>
		<th align=\"center\">".($tahunawal-1)."</th>
		<th align=\"center\">".($tahunawal)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-5)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-4)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-3)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-2)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal-1)."</th>
		<th rowspan=\"2\" align=\"center\">".($tahunawal)."</th>
	  </tr>
      <tr>
		<th align=\"center\">100%</th>
		<th align=\"center\">50%</th>
		<th align=\"center\">50%</th>
		<th align=\"center\">10%</th>
		<th align=\"center\">10%</th>
		<th align=\"center\">5%</th>
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

function getKetetapanKel() {
    global $DBLink, $tahunawal, $kelurahan;
	
	$whr = " NOP like '" . $kelurahan . "%' ";
    $dat = getDataPiutangKel($whr);
	
	// echo "<pre>";
	// print_r($dat);exit;
	
    $c 		= count($dat);
    $data 	= array();
	
	$i = 0;
    foreach ($dat as $da) {
		if($da[$tahunawal."NAMA"]!=""){
			$nama = $da[$tahunawal."NAMA"];
		} else if ($da[($tahunawal-1)."NAMA"]!=""){
			$nama = $da[($tahunawal-1)."NAMA"];
		} else if ($da[($tahunawal-2)."NAMA"]!=""){
			$nama = $da[($tahunawal-2)."NAMA"];
		} else if ($da[($tahunawal-3)."NAMA"]!=""){
			$nama = $da[($tahunawal-3)."NAMA"];
		} else if ($da[($tahunawal-4)."NAMA"]!=""){
			$nama = $da[($tahunawal-4)."NAMA"];
		} else if ($da[($tahunawal-5)."NAMA"]!=""){
			$nama = $da[($tahunawal-5)."NAMA"];
		}
        $data[$i]["NOP"] 					= $da["NOP"];
        $data[$i][$tahunawal."NAMA"] 		= $nama;
        $data[$i][$tahunawal."TAGIHAN"] 	= $da[$tahunawal."TAGIHAN"];
        $data[$i][($tahunawal-1)."TAGIHAN"] = $da[($tahunawal-1)."TAGIHAN"];
        $data[$i][($tahunawal-2)."TAGIHAN"] = $da[($tahunawal-2)."TAGIHAN"];
        $data[$i][($tahunawal-3)."TAGIHAN"] = $da[($tahunawal-3)."TAGIHAN"];
        $data[$i][($tahunawal-4)."TAGIHAN"] = $da[($tahunawal-4)."TAGIHAN"];
        $data[$i][($tahunawal-5)."TAGIHAN"] = $da[($tahunawal-5)."TAGIHAN"];
		$i++;
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
        $selectbank = "and PAYMENT_BANK_CODE = '{$bank}' ";
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

function showTable($mod = 0, $nama = "") {
    global $namakec, $namakel, $tahunawal;

    $dt = getKetetapan($mod);
//    $dt2 = getBulanSekarang($mod);
    
    $c = count($dt);
    $html = "";
    $a = 1;
    $html = headerMonitoringRealisasi($mod, $nama);
    
    $summary = array('name' => 'JUMLAH', 'ketetapan_rp' => 0,
        'sisa' => 0, 
        'rbi_pokok' => 0, 
        'rbi_denda' => 0, 
        'rbi_total' => 0);
    
    for ($i = 0; $i < $c; $i++) {
        $dtname = $dt[$i]["name"];
//        $rp = number_format($dt[$i]["POKOK"], 0, ",", ".");
//        $pokokn = number_format($dt2[$i]["POKOK"], 0, ",", ".");
//        $dendan = number_format($dt2[$i]["DENDA"], 0, ",", ".");
//        $totaln = number_format($dt2[$i]["TOTAL"], 0, ",", ".");
//        $rpsisa = number_format($dt[$i]["POKOK"] - $dt2[$i]["POKOK"], 0, ",", ".");
        $html .= " <tr class=tright>
	            <td>{$a}</td>
	            <td class=tleft>{$dtname}</td>
	            <td>".number_format($dt[$i][($tahunawal-5)."WP"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-4)."WP"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-3)."WP"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-2)."WP"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-1)."WP"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal)."WP"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-5)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-4)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-3)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-2)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-1)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-5)."TAGIHAN"], 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-4)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-3)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-2)."TAGIHAN"]*0.1, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-1)."TAGIHAN"]*0.1, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal)."TAGIHAN"]*0.05, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-5)."TAGIHAN"]*0, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-4)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-3)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-2)."TAGIHAN"]*0.9, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal-1)."TAGIHAN"]*0.9, 0, ",", ".")."</td>
	            <td>".number_format($dt[$i][($tahunawal)."TAGIHAN"]*0.95, 0, ",", ".")."</td>
	          </tr>";

//        $summary['ketetapan_rp'] += $dt[$i]["POKOK"];
//        $summary['rbi_pokok'] += $dt2[$i]["POKOK"];
//        $summary['rbi_denda'] += $dt2[$i]["DENDA"];
//        $summary['rbi_total'] += $dt2[$i]["TOTAL"];
//        $summary['sisa'] += $dt[$i]["POKOK"] - $dt2[$i]["POKOK"];

        $a++;
    }

//    $html .= " <tr>
//            <td align=\"right\"> </td>
//            <td>" . $summary['name'] . "</td>
//            <td align=\"right\">" . number_format($summary['ketetapan_rp'], 0, ',', '.') . "</td>
//            <td align=\"right\">" . number_format($summary['rbi_pokok'], 0, ',', '.') . "</td>
//            <td align=\"right\">" . number_format($summary['rbi_denda'], 0, ',', '.') . "</td>
//            <td align=\"right\">" . number_format($summary['rbi_total'], 0, ',', '.') . "</td>
//            <td align=\"right\">" . number_format($summary['sisa'], 2, ',', '.') . "</td>
//          </tr>";

    return $html . "</table></div>";
}

function showTableKel() {
    global $namakec, $namakel, $tahunawal;

    $dt = getKetetapanKel();
	
	// print_r($dat);
    
    $c = count($dt);
    $html = "";
    $a = 1;
    $html = headerMonitoringPenyisihanPiutangKel(); 
    
    // $summary = array('name' => 'JUMLAH', 'ketetapan_rp' => 0,
        // 'sisa' => 0, 
        // 'rbi_pokok' => 0, 
        // 'rbi_denda' => 0, 
        // 'rbi_total' => 0);
    
    for ($i = 0; $i < $c; $i++) {
        $nop 	= $dt[$i]["NOP"];
        $nama 	= $dt[$i][$tahunawal."NAMA"];
			 
		$html .= " <tr>
	            <td align=\"right\">{$a}</td>
	            <td>{$nop}</td>
	            <td>{$nama}</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-5)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-4)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-3)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-2)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-1)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-5)."TAGIHAN"], 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-4)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-3)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-2)."TAGIHAN"]*0.1, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-1)."TAGIHAN"]*0.1, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal)."TAGIHAN"]*0.05, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-5)."TAGIHAN"]*0, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-4)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-3)."TAGIHAN"]*0.5, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-2)."TAGIHAN"]*0.9, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal-1)."TAGIHAN"]*0.9, 0, ",", ".")."</td>
	            <td align=\"right\">".number_format($dt[$i][($tahunawal)."TAGIHAN"]*0.95, 0, ",", ".")."</td>
	          </tr>";

        $a++;
    }

    return $html . "</table></div>";
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
            . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM pbb_sppt {$whr}";
     //echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
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
                FROM pbb_sppt
                WHERE
                    SPPT_TAHUN_PAJAK IN ('".$tahunawal."', '".($tahunawal-1)."', '".($tahunawal-2)."', '".($tahunawal-3)."', '".($tahunawal-4)."', '".($tahunawal-5)."')
                    AND ( PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1' OR (PAYMENT_FLAG = '1' AND PAYMENT_PAID >= '".$tahunawal."-12-31')) 
                    $whr
                GROUP BY SPPT_TAHUN_PAJAK
                ) AS BBB";
				
	//echo $query;exit;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {      
        $return = $row;
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataPiutangKel($where) {
    global $myDBLink, $tahunawal;

    $myDBLink = openMysql();
    $return = array();
	
	$whr = "";
    if ($where) {
        $whr = " AND {$where}";
    }

    $query = "SELECT 
				NOP, WP_NAMA,
				max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal)."'),`WP_NAMA`,'')) AS `".($tahunawal)."NAMA`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal)."'),`SPPT_PBB_HARUS_DIBAYAR`,0)) AS `".($tahunawal)."TAGIHAN`,
				max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-1)."'),`WP_NAMA`,'')) AS `".($tahunawal-1)."NAMA`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-1)."'),`SPPT_PBB_HARUS_DIBAYAR`,0)) AS `".($tahunawal-1)."TAGIHAN`,
				max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-2)."'),`WP_NAMA`,'')) AS `".($tahunawal-2)."NAMA`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-2)."'),`SPPT_PBB_HARUS_DIBAYAR`,0)) AS `".($tahunawal-2)."TAGIHAN`,
				max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-3)."'),`WP_NAMA`,'')) AS `".($tahunawal-3)."NAMA`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-3)."'),`SPPT_PBB_HARUS_DIBAYAR`,0)) AS `".($tahunawal-3)."TAGIHAN`,
				max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-4)."'),`WP_NAMA`,'')) AS `".($tahunawal-4)."NAMA`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-4)."'),`SPPT_PBB_HARUS_DIBAYAR`,0)) AS `".($tahunawal-4)."TAGIHAN`,
				max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-5)."'),`WP_NAMA`,'')) AS `".($tahunawal-5)."NAMA`,
                max(IF ((`SPPT_TAHUN_PAJAK` = '".($tahunawal-5)."'),`SPPT_PBB_HARUS_DIBAYAR`,0)) AS `".($tahunawal-5)."TAGIHAN`
                FROM
					pbb_sppt
                WHERE
                    SPPT_TAHUN_PAJAK IN ('".$tahunawal."', '".($tahunawal-1)."', '".($tahunawal-2)."', '".($tahunawal-3)."', '".($tahunawal-4)."', '".($tahunawal-5)."')
                    AND ( PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1' OR (PAYMENT_FLAG = '1' AND PAYMENT_PAID >= '".$tahunawal."-12-31')) 
                    $whr
                GROUP BY NOP
				ORDER BY NOP, SPPT_TAHUN_PAJAK ";
				
	//echo $query;exit;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {      
        $return[] = $row;
    }
	// print_r($return); exit;
    closeMysql($myDBLink);
    return $return;
}

// function getKetetapanPerNop($nop){
	// global $myDBLink;
	
	// $myDBLink 	= openMysql();
    // $data 		= array();
	
// }

// function getListData() {
    // global $myDBLink, $kelurahan, $tahunawal;

    // $myDBLink 	= openMysql();
    // $data 		= array();

    // $query = "SELECT
				// NOP,
				// WP_NAMA
			// FROM
				// pbb_sppt
			// WHERE
				// OP_KELURAHAN_KODE = '".mysql_escape_string($kelurahan)."'
			// AND SPPT_TAHUN_PAJAK = '".mysql_escape_string($tahunawal)."' ";
     // // echo $query.'<br/>';
    // $res = mysqli_query($myDBLink, $query);
    // if ($res === false) {
        // echo mysqli_error($DBLink);
        // exit();
    // }
	
	// $i=0;
    // while ($row = mysqli_fetch_assoc($res)) {
        // $data[$i]["NOP"] 	 = ($row["NOP"] != "") ? $row["NOP"] : 0;
        // $data[$i]["WP_NAMA"] = ($row["WP_NAMA"] != "") ? $row["WP_NAMA"] : 0;
		// $i++;
    // }
    // closeMysql($myDBLink);
    // return $data;
// }


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
$tglawal = @isset($_REQUEST['tglawal']) ? $_REQUEST['tglawal'] : "";
$tglakhir = @isset($_REQUEST['tglakhir']) ? $_REQUEST['tglakhir'] : "";
$bank = @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "";

if($kelurahan != ""){
	echo showTableKel();
} else if ($kecamatan != ""){
	echo showTable(1, $namakec);
} else{
    echo showTable();
}
