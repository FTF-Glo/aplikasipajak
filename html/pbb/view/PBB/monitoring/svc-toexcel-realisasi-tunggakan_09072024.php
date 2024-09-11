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

function headerMonitoringRealisasi($mod, $nama) {
    global $appConfig;
    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $nama;
    }
    $html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"21\"><b>{$dl}<b></td></tr>
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
		<td rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<td rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">KETETAPAN</td>
		<td colspan=\"4\" width=\"136\" align=\"center\">REALISASI BULAN LALU (RP)</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"4\" width=\"136\" align=\"center\">REALISASI BULAN INI</td>
		<td colspan=\"4\" width=\"136\" align=\"center\">REALISASI S/D BULAN  INI</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"137\" align=\"center\">SISA     KETETAPAN</td>
		<td rowspan=\"2\" width=\"56\" align=\"center\">SISA  %</td>
	  </tr>
	  <tr>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		
                <td align=\"center\">WP</td>		
                <td align=\"center\">Pokok</td>
                <td align=\"center\">Denda</td>
                <td align=\"center\">Total</td>
                
		<td align=\"center\">WP</td>		
                <td align=\"center\">Pokok</td>
                <td align=\"center\">Denda</td>
                <td align=\"center\">Total</td>
                
		<td align=\"center\">WP</td>		
                <td align=\"center\">Pokok</td>
                <td align=\"center\">Denda</td>
                <td align=\"center\">Total</td>
                
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
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
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s, $qBuku, $thntagihan;
    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $tahun = " sppt_tahun_pajak = '{$thn}' and ((payment_flag!='1' or payment_flag is null) or (payment_flag='1' and payment_paid >= '{$thntagihan}-01-01 00:00:00'))";

    $c = count($kec);
    $data = array();
    for ($i = 0; $i < $c; $i++) {
        $data[$i]["name"] = $kec[$i]["name"];
        $whr = " NOP like '" . $kec[$i]["id"] . "%' AND " . $tahun . $qBuku;
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

/* function getBulanLalu($mod) {
  global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;

  if ($mod==0) $kec =  getKecamatan($kab);
  else $kec = getKelurahan($kecamatan);

  $date = date("Y-m-d");
  $ardate = explode("-",$date);
  $month = $ardate[1]-1;
  $tdate = mktime(0,0,0,$month,$ardate[2],$ardate[0]);
  $prev = date("Y-m",$tdate);

  $periode = "and payment_paid like '{$prev}%'";
  $tahun = "";
  if($thn != ""){
  $tahun = "and sppt_tahun_pajak='{$thn}'";
  if($speriode != -1 && $eperiode != -1){
  $tmp_eperiod = $eperiode;
  if($speriode != $eperiode){$tmp_eperiod = $eperiode - 1;}

  $s_date = $thn."-".$speriode."-1";
  $lastday = date('t',strtotime($tmp_eperiod.'/1/'.$thn));
  $e_date = $thn."-".$tmp_eperiod."-".$lastday;
  $periode = " AND (date_format(payment_paid,'YYYY-MM-DD') >= date_format('$s_date','YYYY-MM-DD') AND date_format(payment_paid,'YYYY-MM-DD') <= date_format('$e_date','YYYY-MM-DD'))";
  }
  }

  //        $tahun = "";
  //	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
  //	$kec =  getKecamatan($kab);
  $c = count($kec);
  $data = array();
  for ($i=0;$i<$c;$i++) {
  $data[$i]["name"] = $kec[$i]["name"];
  //		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_paid like '{$prev}%' and payment_flag=1 ".$tahun;
  $whr = " NOP like '".$kec[$i]["id"]."%' ".$periode." and payment_flag=1 ".$tahun;
  $da = getData($whr);
  $data[$i]["WP"] = $da["WP"];
  $data[$i]["RP"] = $da["RP"];
  }

  return $data;
  } */

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
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku, $thntagihan;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $firstMon = firstDay('01', $thntagihan); //Ambil tanggal awal bulan
    $lastMon = lastDay($eperiode - 1, $thntagihan); //Ambil tanggal akhir bulan
    
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
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku, $thntagihan;

    if ($mod == 0)
        $kec = getKecamatan($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $firstMon = firstDay('01', $thntagihan);
    $nowMon = lastDay($eperiode, $thntagihan);

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

function getBulanSekarang($mod) {
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $eperiode, $qBuku, $thntagihan;

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


    $firstMon = firstDay($eperiode, $thntagihan); //Ambil tanggal awal bulan
    $lastMon = lastDay($eperiode, $thntagihan); //Ambil tanggal akhir bulan


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

function showTable($mod = 0, $nama = "") {
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

    $c = count($dt);
    $html = "";
    $a = 1;
    $html = headerMonitoringRealisasi($mod, $nama);

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
        $rpsisa = $dt[$i]["POKOK"] - $dtall[$i]["TOTAL"];

        $percent1 = ($dt1[$i]["TOTAL"] != 0 && $dt[$i]["POKOK"] != 0) ? ($dt1[$i]["TOTAL"] / $dt[$i]["POKOK"] * 100) : 0;
        $percent2 = ($dtall[$i]["TOTAL"] != 0 && $dt[$i]["POKOK"] != 0 ) ? ($dtall[$i]["TOTAL"] / $dt[$i]["POKOK"] * 100) : 0;
        $percent3 = ($rpsisa != 0 && $dt[$i]["POKOK"] != 0) ? ($rpsisa / $dt[$i]["POKOK"] * 100) : 0;
        ###

        $tmp = array(
            "name" => $dt[$i]["name"],
            "ketetapan_wp" => number_format($dt[$i]["WP"], 0, "", ""),
            "ketetapan_rp" => number_format($dt[$i]["POKOK"], 0, "", ""),
            "rbl_wp" => number_format($dt1[$i]["WP"], 0, "", ""),
            "rbl_pokok" => number_format($dt1[$i]["POKOK"], 0, "", ""),
            "rbl_denda" => number_format($dt1[$i]["DENDA"], 0, "", ""),
            "rbl_total" => number_format($dt1[$i]["TOTAL"], 0, "", ""),
            "percent1" => number_format($percent1, 2, ",", "."),
            "rbi_wp" => number_format($dt2[$i]["WP"], 0, "", ""),
            "rbi_pokok" => number_format($dt2[$i]["POKOK"], 0, "", ""),
            "rbi_denda" => number_format($dt2[$i]["DENDA"], 0, "", ""),
            "rbi_total" => number_format($dt2[$i]["TOTAL"], 0, "", ""),
            "kom_rbi_wp" => number_format($dtall[$i]["WP"], 0, "", ""),
            "kom_rbi_pokok" => number_format($dtall[$i]["POKOK"], 0, "", ""),
            "kom_rbi_denda" => number_format($dtall[$i]["DENDA"], 0, "", ""),
            "kom_rbi_total" => number_format($dtall[$i]["TOTAL"], 0, "", ""),
            "percent2" => number_format($percent2, 2, ",", "."),
            "sk_wp" => number_format($wpsisa, 0, "", ""),
            "sk_rp" => number_format($rpsisa, 0, "", ""),
            "percent3" => number_format($percent3, 2, ",", ".")
        );
        $data[] = $tmp;
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

        $summary['sk_wp'] += $wpsisa;
        $summary['sk_rp'] += $rpsisa;

        $a++;
    }

    $summary['percent1'] = ($summary['ketetapan_rp'] != 0 && $summary['rbl_total'] != 0) ? ($summary["rbl_total"] / $summary["ketetapan_rp"] * 100) : 0;
    $summary['percent2'] = ($summary['ketetapan_rp'] != 0 && $summary['kom_rbi_total'] != 0) ? ($summary["kom_rbi_total"] / $summary["ketetapan_rp"] * 100) : 0;
    $summary['percent3'] = ($summary['ketetapan_rp'] != 0 && $summary['sk_rp'] != 0) ? ($summary["sk_rp"] / $summary["ketetapan_rp"] * 100) : 0;

    $summary['percent1'] = number_format($summary['percent1'], 2, ",", ".");
    $summary['percent2'] = number_format($summary['percent2'], 2, ",", ".");
    $summary['percent3'] = number_format($summary['percent3'], 2, ",", ".");

    $data[] = $summary;

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
        $whr = " where {$where}";
    }
    $query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK, sum(PBB_DENDA) AS DENDA, "
            . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM pbb_sppt {$whr}";
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
    $query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) AS POKOK FROM pbb_sppt {$whr}";
    // echo $query.'<br/>';
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kab = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$thntagihan = @isset($_REQUEST['thntagihan']) ? $_REQUEST['thntagihan'] : "";
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$speriode = @isset($_REQUEST['speriode']) ? $_REQUEST['speriode'] : "";
$eperiode = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : 0;


// $arrWhere = array();
// if ($kecamatan !="") {
// array_push($arrWhere,"nop like '{$kecamatan}%'");
// }
// if ($thn!=""){
// array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
// array_push($arrWhere,"payment_paid like '{$thn}%'");
// }
// $where = implode (" AND ",$arrWhere);

$qBuku = "";
$sBuku = "";
// if($buku != 0){
// switch ($buku){
// case 1 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; 
// $sBuku = "I";
// break;		
// case 12 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; 
// $sBuku = "I s/d II";
// break;
// case 123 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; 
// $sBuku = "I s/d III";
// break;
// case 1234 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; 
// $sBuku = "I s/d IV";
// break;
// case 12345 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; 
// $sBuku = "I s/d V";
// break;
// case 2 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
// $sBuku = "II";			
// break;
// case 23 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
// $sBuku = "II s/d III";
// break;
// case 234 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; 
// $sBuku = "II s/d IV";
// break;
// case 2345 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; 
// $sBuku = "II s/d V";
// break;
// case 3 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; 
// $sBuku = "III";
// break;
// case 34 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; 
// $sBuku = "III s/d IV";
// break;
// case 345 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; 
// $sBuku = "III s/d V";
// break;
// case 4 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; 
// $sBuku = "IV";
// break;
// case 45 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; 
// $sBuku = "IV s/d V";
// break;
// case 5 : 
// $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; 
// $sBuku = "V";
// break;
// }
// }
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
if ($kecamatan == "") {
    $data = showTable();
} else {
    $data = showTable(1, $nama);
}
#print_r($data);
#echo count($data);
#print_r($_REQUEST);
#exit;
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

$objRichText->createText(': KETETAPAN DAN REALISASI TUNGGAKAN PBB TAHUN ANGGARAN ' . $thntagihan .' UNTUK PBB TAHUN PAJAK '.$thn);
$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D1:J1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $sBuku);
$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D2:J2');
$objRichText = new PHPExcel_RichText();
if ($eperiode > 1)
    $objRichText->createText(': JANUARI s/d ' . strtoupper($bulan[$eperiode - 1]) . ' ' . $thn);
else
    $objRichText->createText(': JANUARI  ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:J3');


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'BUKU');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BULAN');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Model E.2');

$objPHPExcel->getActiveSheet()->getStyle('C1:L3')->applyFromArray(
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
    $objRichText->createText('KECAMATAN : ' . $nama);
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
$objRichText->createText('KETETAPAN');
$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C8:D8');
$objPHPExcel->getActiveSheet()->setCellValue('C9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('D9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI BULAN LALU (RP)');
$objPHPExcel->getActiveSheet()->getCell('E8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E8:H8');
$objPHPExcel->getActiveSheet()->setCellValue('E9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('F9', 'POKOK');
$objPHPExcel->getActiveSheet()->setCellValue('G9', 'DENDA');
$objPHPExcel->getActiveSheet()->setCellValue('H9', 'TOTAL');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('I8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I8:I9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI BULAN INI (RP)');
$objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J8:M8');
$objPHPExcel->getActiveSheet()->setCellValue('J9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('K9', 'POKOK');
$objPHPExcel->getActiveSheet()->setCellValue('L9', 'DENDA');
$objPHPExcel->getActiveSheet()->setCellValue('M9', 'TOTAL');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI s/d BULAN INI (RP)');
$objPHPExcel->getActiveSheet()->getCell('N8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('N8:Q8');
$objPHPExcel->getActiveSheet()->setCellValue('N9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('O9', 'POKOK');
$objPHPExcel->getActiveSheet()->setCellValue('P9', 'DENDA');
$objPHPExcel->getActiveSheet()->setCellValue('Q9', 'TOTAL');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('R8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('R8:R9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('SISA KETETAPAN');
$objPHPExcel->getActiveSheet()->getCell('S8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('S8:T8');
$objPHPExcel->getActiveSheet()->setCellValue('S9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('T9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('U8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('U8:U9');


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A8:U9')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(8);

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
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $buffer['ketetapan_wp']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $no), $buffer['ketetapan_rp']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $no), $buffer['rbl_wp']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $no), $buffer['rbl_pokok']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $no), $buffer['rbl_denda']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $no), $buffer['rbl_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $no), $buffer['percent1']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (9 + $no), $buffer['rbi_wp']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (9 + $no), $buffer['rbi_pokok']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (9 + $no), $buffer['rbi_denda']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . (9 + $no), $buffer['rbi_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . (9 + $no), $buffer['kom_rbi_wp']);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . (9 + $no), $buffer['kom_rbi_pokok']);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . (9 + $no), $buffer['kom_rbi_denda']);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . (9 + $no), $buffer['kom_rbi_total']);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . (9 + $no), $buffer['percent2']);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . (9 + $no), $buffer['sk_wp']);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . (9 + $no), $buffer['sk_rp']);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . (9 + $no), $buffer['percent3']);
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (9 + $no), $buffer['ketetapan_wp'])->getStyle('C' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (9 + $no), $buffer['ketetapan_rp'])->getStyle('D' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (9 + $no), $buffer['rbl_wp'])->getStyle('E' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (9 + $no), $buffer['rbl_pokok'])->getStyle('F' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (9 + $no), $buffer['rbl_denda'])->getStyle('G' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (9 + $no), $buffer['rbl_total'])->getStyle('H' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (9 + $no), $buffer['percent1'])->getStyle('I' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (9 + $no), $buffer['rbi_wp'])->getStyle('J' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (9 + $no), $buffer['rbi_pokok'])->getStyle('K' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (9 + $no), $buffer['rbi_denda'])->getStyle('L' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . (9 + $no), $buffer['rbi_total'])->getStyle('M' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . (9 + $no), $buffer['kom_rbi_wp'])->getStyle('N' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . (9 + $no), $buffer['kom_rbi_pokok'])->getStyle('O' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . (9 + $no), $buffer['kom_rbi_denda'])->getStyle('P' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . (9 + $no), $buffer['kom_rbi_total'])->getStyle('Q' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('R' . (9 + $no), $buffer['percent2'])->getStyle('R' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . (9 + $no), $buffer['sk_wp'])->getStyle('S' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . (9 + $no), $buffer['sk_rp'])->getStyle('T' . (9 + $no))->applyFromArray($noBold);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . (9 + $no), $buffer['percent3'])->getStyle('U' . (9 + $no))->applyFromArray($noBold);
    }
    $no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A10:U' . (9 + count($data)))->applyFromArray(
        array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
$objPHPExcel->getActiveSheet()->getStyle('A10:A' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('C10:F' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('G10:G' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('H10:K' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('L10:L' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('M10:N' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('O10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('P10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('Q10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('R10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('S10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('T10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('U10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_KOTA_PENGESAHAN'] . ', ' . strtoupper($bulan[date('m') - 1]) . ' ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('I' . (11 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (11 + count($data)) . ':K' . (11 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['PEJABAT_SK2']);
$objPHPExcel->getActiveSheet()->getCell('I' . (12 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (12 + count($data)) . ':K' . (12 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA']);
$objPHPExcel->getActiveSheet()->getCell('I' . (13 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (13 + count($data)) . ':K' . (13 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_PEJABAT_SK2']);
$objPHPExcel->getActiveSheet()->getCell('I' . (17 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (17 + count($data)) . ':K' . (17 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_PEJABAT_SK2_JABATAN']);
$objPHPExcel->getActiveSheet()->getCell('I' . (18 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (18 + count($data)) . ':K' . (18 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NIP. ' . $appConfig['NAMA_PEJABAT_SK2_NIP']);
$objPHPExcel->getActiveSheet()->getCell('I' . (19 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (19 + count($data)) . ':K' . (19 + count($data)));

$objPHPExcel->getActiveSheet()->getStyle('I' . (17 + count($data)) . ':K' . (17 + count($data)));
$objPHPExcel->getActiveSheet()->getStyle('I' . (11 + count($data)) . ':K' . (19 + count($data)))->applyFromArray(
        array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="realisasi_tunggakan_pbb.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
