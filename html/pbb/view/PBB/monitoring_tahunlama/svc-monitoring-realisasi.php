<?php

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

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 1);


$myDBLink = "";

function headerMonitoringRealisasi($mod, $namaKec) {
    global $appConfig;
    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $namaKec;
    }
    $html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><th colspan=\"21\"><b>{$dl}<b></th></tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" span=\"2\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"48\" />
	  <col width=\"89\" />
	  <col width=\"56\" />
	  <tr>
		<th rowspan=\"2\" width=\"28\" align=\"center\">NO</th>
		<th rowspan=\"2\" width=\"117\" align=\"center\">{$model}</th>
		<th colspan=\"2\" width=\"136\" align=\"center\">KETETAPAN</th>
		<th colspan=\"4\" width=\"136\" align=\"center\">REALISASI BULAN LALU (RP)</th>
		<th rowspan=\"2\" width=\"47\" align=\"center\">%</th>
		<th colspan=\"4\" width=\"136\" align=\"center\">REALISASI BULAN INI</th>
		<th colspan=\"4\" width=\"136\" align=\"center\">REALISASI S/D BULAN  INI</th>
		<th rowspan=\"2\" width=\"47\" align=\"center\">%</th>
		<th colspan=\"2\" width=\"137\" align=\"center\">SISA     KETETAPAN</th>
		<th rowspan=\"2\" width=\"56\" align=\"center\">SISA  %</th>
	  </tr>
	  <tr>
		<th align=\"center\">WP</th>
		<th align=\"center\">Rp</th>
		
                <th align=\"center\">WP</th>		
                <th align=\"center\">Pokok</th>
                <th align=\"center\">Denda</th>
                <th align=\"center\">Total</th>
                
		<th align=\"center\">WP</th>		
                <th align=\"center\">Pokok</th>
                <th align=\"center\">Denda</th>
                <th align=\"center\">Total</th>
                
		<th align=\"center\">WP</th>		
                <th align=\"center\">Pokok</th>
                <th align=\"center\">Denda</th>
                <th align=\"center\">Total</th>
                
		<th align=\"center\">WP</th>
		<th align=\"center\">Rp</th>
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
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s, $qBuku;
    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $tahun = "";
    if ($thn != "") {
        $tahun = "and sppt_tahun_pajak='{$thn}'";
    }
    
    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $tahun . $qBuku;
        $da = getDataTargetE2($whr);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["POKOK"] = $da["POKOK"];
    }

    return $data;
}

function getSisaKetetapan($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $qBuku;
    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $tahun = "";
    if ($thn != "") {
        $tahun = "and sppt_tahun_pajak='{$thn}'";
    }

    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' and (payment_flag!='1' or payment_flag is null) " . $tahun . $qBuku;
        $da = getData($whr);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["RP"] = $da["RP"];
    }

    return $data;
}

//get tanggal akhir pada bulan
function lastDay($month = '', $year = '') {
    if (empty($month)) {
        $month = date('m');
    }
    if (empty($year)) {
        $year = date('Y');
    }
    $result = strtotime("{$year}-{$month}-01");
    $result = strtotime('-1 second', strtotime('+1 month', $result));
    return date('Y-m-d', $result) . ' 23:59:59';
}

//get tanggal awal pada bulan
function firstDay($month = '', $year = '') {
    if (empty($month)) {
        $month = date('m');
    }
    if (empty($year)) {
        $year = date('Y');
    }
    $result = strtotime("{$year}-{$month}-01");
    return date('Y-m-d', $result) . ' 00:00:00';
}

function getBulanLalu($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $firstMon = firstDay('01', $thn); //Ambil tanggal awal bulan
    $lastMon = lastDay($eperiode - 1, $thn); //Ambil tanggal akhir bulan

    $tahun = "and sppt_tahun_pajak = '{$thn}'";

    $periode = "and payment_paid between '{$firstMon}' and '{$lastMon}'";


    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        if ($eperiode > 1) {
            $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $periode . " and payment_flag='1' " . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
            $data[$i]["DENDA"] = $da["DENDA"];
            $data[$i]["TOTAL"] = $da["TOTAL"];
        } else {
            $data[$i]["WP"] = 0;
            $data[$i]["POKOK"] = 0;
            $data[$i]["DENDA"] = 0;
            $data[$i]["TOTAL"] = 0;
        }
    }

    return $data;
}

function getSampaiBulanSekarang($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $firstMon = firstDay('01', $thn);
    $nowMon = lastDay($eperiode, $thn);

    $periode = "and payment_paid between '{$firstMon}' and '{$nowMon}'"; //Antara tanggal 01/01/ sampai sekarang

    $tahun = "and sppt_tahun_pajak = '{$thn}'";

    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' and payment_flag='1' " . $periode . $tahun . $qBuku;
        $da = getData($whr);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["POKOK"] = $da["POKOK"];
        $data[$i]["DENDA"] = $da["DENDA"];
        $data[$i]["TOTAL"] = $da["TOTAL"];
    }

    return $data;
}

function getSisaSampaiBulanSekarang($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $e_date = date('Y-m-') . date('t', strtotime(date('m') . '/1/' . date('Y')));
    $periode = " AND date(sppt_tanggal_terbit) < '$e_date'";

    $tahun = "and sppt_tahun_pajak = '{$thn}'";
    if ($thn != "") {
        if ($eperiode != -1) {
            $lastday = date('t', strtotime($eperiode . '/1/' . $thn));
            $e_date = $thn . "-" . $eperiode . "-" . $lastday;
            $periode = " AND date(sppt_tanggal_terbit) < '$e_date'";
        }
    }


    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' and payment_flag!='1' " . $periode . $tahun . $qBuku;
        $da = getData($whr);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["RP"] = $da["RP"];
    }

    return $data;
}

function getBulanSekarang($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $eperiode, $qBuku;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }


    $firstMon = firstDay($eperiode, $thn); //Ambil tanggal awal bulan
    $lastMon = lastDay($eperiode, $thn); //Ambil tanggal akhir bulan


    $periode = "and payment_paid between '{$firstMon}' and '{$lastMon}'"; //Antara tanggal 01 sampai tanggal 30 bulan sekarang

    $tahun = "and sppt_tahun_pajak = '{$thn}'";

    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $periode . " and payment_flag='1' " . $tahun . $qBuku;
        $da = getData($whr);
        $data[$i]["WP"] = $da["WP"];
        $data[$i]["POKOK"] = $da["POKOK"];
        $data[$i]["DENDA"] = $da["DENDA"];
        $data[$i]["TOTAL"] = $da["TOTAL"];
    }
    return $data;
}

function showTable($mod = 0, $namaKec = "") {
    global $eperiode;
    $dt = getKetetapan($mod);
    $dt1 = getBulanLalu($mod);
    $dt2 = getBulanSekarang($mod);
    $dtall = array();
    if ($eperiode == 1)
        $dtall = getSampaiBulanSekarang($mod);
    else {
        foreach ($dt1 as $key => $row) {
            $dtall[$key]["WP"] = $row["WP"] + $dt2[$key]["WP"];
            $dtall[$key]["POKOK"] = $row["POKOK"] + $dt2[$key]["POKOK"];
            $dtall[$key]["DENDA"] = $row["DENDA"] + $dt2[$key]["DENDA"];
            $dtall[$key]["TOTAL"] = $row["TOTAL"] + $dt2[$key]["TOTAL"];
        }
    }

//	$dtsisa = getSisaSampaiBulanSekarang($mod);
    //$dtsisa = getSisaKetetapan($mod);
    $c = count($dt);
    $html = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
    $a = 1;
    $html .= headerMonitoringRealisasi($mod, $namaKec);
    
    $summary = array('name' => 'JUMLAH', 'ketetapan_wp' => 0, 'ketetapan_rp' => 0, 
        'rbl_wp' => 0, 
        'rbl_pokok' => 0, 
        'rbl_denda' => 0, 
        'rbl_total' => 0, 
        'percent1' => 0, 
        
        'rbi_wp' => 0, 
        'rbi_pokok' => 0, 
        'rbi_denda' => 0, 
        'rbi_total' => 0, 
        
        'kom_rbi_wp' => 0, 
        'kom_rbi_pokok' => 0, 
        'kom_rbi_denda' => 0, 
        'kom_rbi_total' => 0,         
        'percent2' => 0, 
        
        'sk_wp' => 0, 
        'sk_rp' => 0, 
        'percent3' => 0);
    
    for ($i = 0; $i < $c; $i++) {
        $wpsisa = $dt[$i]["WP"] - $dtall[$i]["WP"];
        $rpsisa = $dt[$i]["POKOK"] - ($dtall[$i]["TOTAL"] - $dtall[$i]["DENDA"]);

        $percent1 = (($dt1[$i]["TOTAL"] - $dt1[$i]["DENDA"]) != 0 && $dt[$i]["POKOK"] != 0) ? (($dt1[$i]["TOTAL"] - $dt1[$i]["DENDA"])  / $dt[$i]["POKOK"] * 100) : 0;
        $percent2 = (($dtall[$i]["TOTAL"] - $dtall[$i]["DENDA"]) != 0 && $dt[$i]["POKOK"] != 0 ) ? (($dtall[$i]["TOTAL"] - $dtall[$i]["DENDA"]) / $dt[$i]["POKOK"] * 100) : 0;
        $percent3 = ($rpsisa != 0 && $dt[$i]["POKOK"] != 0) ? ($rpsisa / $dt[$i]["POKOK"] * 100) : 0;

        $dtname = $dt[$i]["name"];
        $wp = number_format($dt[$i]["WP"], 0, ",", ".");
        $rp = number_format($dt[$i]["POKOK"], 0, ",", ".");

        $wpp = number_format($dt1[$i]["WP"], 0, ",", ".");
        $pokokp = number_format($dt1[$i]["POKOK"], 0, ",", ".");
        $dendap = number_format($dt1[$i]["DENDA"], 0, ",", ".");
        $totalp = number_format($dt1[$i]["TOTAL"], 0, ",", ".");
        $prc1 = number_format($percent1, 2, ",", ".");

        $wpn = number_format($dt2[$i]["WP"], 0, ",", ".");
        $pokokn = number_format($dt2[$i]["POKOK"], 0, ",", ".");
        $dendan = number_format($dt2[$i]["DENDA"], 0, ",", ".");
        $totaln = number_format($dt2[$i]["TOTAL"], 0, ",", ".");

        $wpall = number_format($dtall[$i]["WP"], 0, ",", ".");
        $pokokall = number_format($dtall[$i]["POKOK"], 0, ",", ".");
        $dendaall = number_format($dtall[$i]["DENDA"], 0, ",", ".");
        $totalall = number_format($dtall[$i]["TOTAL"], 0, ",", ".");
        $prc2 = number_format($percent2, 2, ",", ".");

        // $dataParse[] = 
        $prc3 = number_format($percent3, 2, ",", ".");
        $dataParse[$i] = array("NAMA"=>$dtname,
            "WP"=>$wp,
            "RP"=>$rp,
            "WPP"=>$wpp,
            "POKOKP"=>$pokokp,
            "DENDAP"=>$dendap,
            "TOTALP"=>$totalp,
            "PRC1"=>$percent1,
            "WPN"=>$wpn,
            "POKOKN"=>$pokokn,
            "DENDAN"=>$dendan,
            "TOTALN"=>$totaln,
            "WPALL"=>$wpall,
            "POKOKALL"=>$pokokall,
            "DENDAALL"=>$dendaall,
            "TOTALALL"=>$totalall,
            "PRC2"=>$percent2,
            "WPSISA"=>$wpsisa,
            "RPSISA"=>$rpsisa,
            "PRC3"=>$percent3
        ); 

        $summary['ketetapan_wp'] += $dt[$i]["WP"];
        $summary['ketetapan_rp'] += $dt[$i]["POKOK"];        
        
        $summary['rbl_wp'] += $dt1[$i]["WP"];        
        $summary['rbl_pokok'] += $dt1[$i]["POKOK"];
        $summary['rbl_denda'] += $dt1[$i]["DENDA"];        
        $summary['rbl_total'] += $dt1[$i]["TOTAL"];

        $summary['rbi_wp'] += $dt2[$i]["WP"];
        $summary['rbi_pokok'] += $dt2[$i]["POKOK"];
        $summary['rbi_denda'] += $dt2[$i]["DENDA"];
        $summary['rbi_total'] += $dt2[$i]["TOTAL"];
        
        $summary['kom_rbi_wp'] += $dtall[$i]["WP"];
        $summary['kom_rbi_pokok'] += $dtall[$i]["POKOK"];
        $summary['kom_rbi_denda'] += $dtall[$i]["DENDA"];
        $summary['kom_rbi_total'] += $dtall[$i]["TOTAL"];
        
        //$summary['percent2'] += $summary['percent2'] + $percent2;
        $summary['sk_wp'] += $wpsisa;
        $summary['sk_rp'] += $rpsisa;
        //$summary['percent3'] += $summary['percent3'] + $percent3;

        $a++;
    }

     $list = array_sort($dataParse, 'PRC2', SORT_DESC);

     $no=1;
    
    foreach ($list as $list){ 
        $PRC1=number_format($list["PRC1"],2,",",".");
        $PRC2=number_format($list["PRC2"],2,",",".");
        $PRC3=number_format($list["PRC3"],2,",",".");

        
        $html .= " <tr>
	            <td align=\"right\">{$no}</td>
	            <td>{$list["NAMA"]}</td>
	            <td align=\"right\">{$list["WP"]}</td>
	            <td align=\"right\">{$list["RP"]}</td>
	            
                    <td align=\"right\">{$list["WPP"]}</td>
	            <td align=\"right\">{$list["POKOKP"]}</td>
                    <td align=\"right\">{$list["DENDAP"]}</td>
	            <td align=\"right\">{$list["TOTALP"]}</td>
	            <td align=\"right\">{$PRC1}</td>
	            
                    <td align=\"right\">{$list["WPN"]}</td>
	            <td align=\"right\">{$list["POKOKN"]}</td>
                    <td align=\"right\">{$list["DENDAN"]}</td>
	            <td align=\"right\">{$list["TOTALN"]}</td>
	            
                    <td align=\"right\">{$list["WPALL"]}</td>
	            <td align=\"right\">{$list["POKOKALL"]}</td>
                    <td align=\"right\">{$list["DENDAALL"]}</td>
	            <td align=\"right\">{$list["TOTALALL"]}</td>
	            <td align=\"right\">{$PRC2}</td>
                        
	            <td align=\"right\">" . number_format($list["WPSISA"], 0, ",", ".") . "</td>
	            <td align=\"right\">" . number_format($list["RPSISA"], 0, ",", ".") . "</td>
	            <td align=\"right\">{$PRC3}</td>
              </tr>";
              
        $no++;

    }

    $summary['percent1'] = ($summary['ketetapan_rp'] != 0 && $summary['rbl_total'] != 0) ? ($summary["rbl_total"] / $summary["ketetapan_rp"] * 100) : 0;
    $summary['percent2'] = ($summary['ketetapan_rp'] != 0 && $summary['kom_rbi_total'] != 0) ? ($summary["kom_rbi_total"] / $summary["ketetapan_rp"] * 100) : 0;
    $summary['percent3'] = ($summary['ketetapan_rp'] != 0 && $summary['sk_rp'] != 0) ? ($summary["sk_rp"] / $summary["ketetapan_rp"] * 100) : 0;
    $html .= " <tr style=\"font-weight: bold;\">
            <td align=\"right\"> </td>
            <td>" . $summary['name'] . "</td>
            <td align=\"right\">" . number_format($summary['ketetapan_wp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['ketetapan_rp'], 0, ',', '.') . "</td>
            
            <td align=\"right\">" . number_format($summary['rbl_wp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbl_pokok'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbl_denda'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbl_total'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['percent1'], 2, ',', '.') . "</td>
                
            <td align=\"right\">" . number_format($summary['rbi_wp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbi_pokok'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbi_denda'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbi_total'], 0, ',', '.') . "</td>
                
            <td align=\"right\">" . number_format($summary['kom_rbi_wp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['kom_rbi_pokok'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['kom_rbi_denda'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['kom_rbi_total'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['percent2'], 2, ',', '.') . "</td>
                
            <td align=\"right\">" . number_format($summary['sk_wp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['sk_rp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['percent3'], 2, ',', '.') . "</td>
          </tr>";
     return $html . "</table>";

     


    //  echo "<pre>";
    //     print_r($list);
    //  echo "</pre>";
    //  exit;
}

function array_sort($array, $on, $order=SORT_ASC){

    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
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
        $whr = " where {$where}";
    }
    $query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK, sum(PBB_DENDA) AS DENDA, "
            . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM PBB_SPPT {$whr}";
    // echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        //print_r($row);        
        $return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
        $return["DENDA"] = ($row["DENDA"] != "") ? $row["DENDA"] : 0;
        $return["TOTAL"] = ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataTargetE2($where) {
    global $myDBLink, $kd, $thn, $bulan;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["WP"] = 0;
    $whr = "";
    if ($where) {
        $whr = " where {$where}";
    }
    $query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK FROM PBB_SPPT {$whr}";
    // echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        //print_r($row);        
        $return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataTarget($where) {
    global $myDBLink, $kd, $thn, $bulan, $target_ketetapan;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["WP"] = 0;
    $whr = "";
    if ($where) {
        $whr = " where {$where}";
    }

    if ($target_ketetapan == 'semua')
        $query = "SELECT sum(A.TARGET_WP) AS WP, sum(A.TARGET_VALUE) as RP FROM PBB_SPPT_TARGET A {$whr}";
    else
        $query = "SELECT (COALESCE(AWP,0) - COALESCE(BWP,0)) AS WP, (COALESCE(ARP,0) - COALESCE(BRP,0)) AS RP FROM (
		SELECT sum(A.TARGET_WP) AS AWP, sum(A.TARGET_VALUE) as ARP, sum(B.TARGET_WP) AS BWP, sum(B.TARGET_VALUE) as BRP FROM PBB_SPPT_TARGET A LEFT JOIN PBB_SPPT_TARGET_PENGECUALIAN B
		ON A.KELURAHAN = B.KELURAHAN AND A.TAHUN=B.TAHUN 
		{$whr}
		) TBL1";

    //echo $query;
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        //print_r($row);
        $return["RP"] = ($row["RP"] != "") ? $row["RP"] : 0;
        $return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
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

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);

$kd 				= $appConfig['KODE_KOTA'];
$kab 				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 			= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 			= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec 			= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel 			= @isset($_REQUEST['nkel']) ? $_REQUEST['nkel'] : "";
$eperiode 			= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan	= @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$buku 				= @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : "";


$qBuku = "";
	if($buku != 0){
	 switch ($buku){
		 case 1 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
		 case 12 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
		 case 123 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		 case 1234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 12345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 2 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
		 case 23 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		 case 234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 2345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 3 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		 case 34 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 4 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 45 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 5 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
	}
 }

if ($kecamatan == "" && $kelurahan=="") {
    echo showTable();
} else {
    echo showTable(1, $namaKec);
}
?>