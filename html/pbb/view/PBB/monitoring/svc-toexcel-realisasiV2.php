<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

//date_default_timezone_set('Asia/Jakarta');
//error_reporting(E_ALL);
ini_set('display_errors', 1);

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



$myDBlink = "";

function headerMonitoringRealisasi($mod, $nama)
{
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
    }
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

function getKelKec($p)
{
    global $DBLink, $kelurahan;
    $query = "SELECT * FROM cppmod_tax_kelurahan A JOIN cppmod_tax_kecamatan B ON A.CPC_TKL_KCID=B.CPC_TKC_ID WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKC_KECAMATAN";
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
        $data[$i]["idkec"] = $row["CPC_TKL_KCID"];
        $data[$i]["namekec"] = $row["CPC_TKC_KECAMATAN"];

        $i++;
    }
    return $data;
}

function getKetetapan($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s, $qBuku;
    if ($mod == 0)
        $kec = getKelKec($kab);
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
    if ($mod == 0) {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $data[$i]["namekec"] = $kec[$i]["namekec"];
            $whr = " OP_KELURAHAN_KODE ='" . $kec[$i]["id"] . "' AND OP_KECAMATAN_KODE = '" . $kec[$i]["idkec"] . "' " . $tahun . $qBuku;
            $da = getDataTargetE2($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
        }
    } else {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $tahun . $qBuku;
            $da = getDataTargetE2($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
        }
    }
    return $data;
}

function getSisaKetetapan($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $qBuku;
    if ($mod == 0)
        $kec = getKelKec($kab);
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
    if ($mod == 0) {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $data[$i]["namekec"] = $kec[$i]["namekec"];
            $whr = " OP_KELURAHAN_KODE ='" . $kec[$i]["id"] . "' AND OP_KECAMATAN_KODE = '" . $kec[$i]["idkec"] . "' and (payment_flag!='1' or payment_flag is null) " . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["RP"] = $da["RP"];
        }
    } else {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $whr = " NOP like '" . $kec[$i]["id"] . "%' and (payment_flag!='1' or payment_flag is null) " . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["RP"] = $da["RP"];
        }
    }

    return $data;
}

//get tanggal akhir pada bulan
function lastDay($month = '', $year = '')
{
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
function firstDay($month = '', $year = '')
{
    if (empty($month)) {
        $month = date('m');
    }
    if (empty($year)) {
        $year = date('Y');
    }
    $result = strtotime("{$year}-{$month}-01");
    return date('Y-m-d', $result) . ' 00:00:00';
}

function getBulanLalu($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku;

    if ($mod == 0)
        //$kec = getKecamatan($kab);
        $kec = getKelKec($kab);
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
    if ($mod == 0) {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $data[$i]["namekec"] = $kec[$i]["namekec"];
            if ($eperiode > 1) {
                $whr = " OP_KELURAHAN_KODE ='" . $kec[$i]["id"] . "' AND OP_KECAMATAN_KODE = '" . $kec[$i]["idkec"] . "' " . $periode . " and payment_flag='1' " . $tahun . $qBuku;
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
    } else {
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
    }

    return $data;
}

function getSampaiBulanSekarang($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $speriode, $eperiode, $qBuku;

    if ($mod == 0)
        //$kec = getKecamatan($kab);
        $kec = getKelKec($kab);
    else {
        if ($kelurahan)
            $kec = getKelurahan($kelurahan);
        else
            $kec = getKelurahan($kecamatan);
    }

    $firstMon = firstDay('01', $thn);
    $nowMon = lastDay($eperiode, $thn);

    $periode = "and payment_paid between '{$firstMon}' and '{$nowMon}'"; //Antara tanggal 01/01/ sampai sekarang
    if ($thn != "")
        $periode .= "and sppt_tahun_pajak = '{$thn}'";

    $c = count($kec);
    $data = array();
    if ($mod == 0) {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $data[$i]["namekec"] = $kec[$i]["namekec"];
            $whr = " OP_KELURAHAN_KODE ='" . $kec[$i]["id"] . "' AND OP_KECAMATAN_KODE = '" . $kec[$i]["idkec"] . "' and payment_flag='1' " . $periode . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
            $data[$i]["DENDA"] = $da["DENDA"];
            $data[$i]["TOTAL"] = $da["TOTAL"];
        }
    } else {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $whr = " NOP like '" . $kec[$i]["id"] . "%' and payment_flag='1' " . $periode . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
            $data[$i]["DENDA"] = $da["DENDA"];
            $data[$i]["TOTAL"] = $da["TOTAL"];
        }
    }
    return $data;
}

function getBulanSekarang($mod)
{
    global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $eperiode, $qBuku;

    if ($mod == 0)
        //$kec = getKecamatan($kab);
        $kec = getKelKec($kab);
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
    if ($mod == 0) {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $data[$i]["namekec"] = $kec[$i]["namekec"];
            $whr = " OP_KELURAHAN_KODE ='" . $kec[$i]["id"] . "' AND OP_KECAMATAN_KODE = '" . $kec[$i]["idkec"] . "' " . $periode . " and payment_flag='1' " . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
            $data[$i]["DENDA"] = $da["DENDA"];
            $data[$i]["TOTAL"] = $da["TOTAL"];
        }
    } else {
        for ($i = 0; $i < $c; $i++) {
            $data[$i]["name"] = $kec[$i]["name"];
            $whr = " NOP like '" . $kec[$i]["id"] . "%' " . $periode . " and payment_flag='1' " . $tahun . $qBuku;
            $da = getData($whr);
            $data[$i]["WP"] = $da["WP"];
            $data[$i]["POKOK"] = $da["POKOK"];
            $data[$i]["DENDA"] = $da["DENDA"];
            $data[$i]["TOTAL"] = $da["TOTAL"];
        }
    }
    return $data;
}

function showTable($mod = 0, $nama = "")
{
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

    $summary = array(
        'name' => 'JUMLAH', 'ketetapan_wp' => 0, 'ketetapan_rp' => 0,
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
        'percent3' => 0
    );

    for ($i = 0; $i < $c; $i++) {
        $wpsisa = $dt[$i]["WP"] - $dtall[$i]["WP"];
        $rpsisa = $dt[$i]["POKOK"] - $dtall[$i]["TOTAL"];

        $percent1 = ($dt1[$i]["TOTAL"] != 0 && $dt[$i]["POKOK"] != 0) ? ($dt1[$i]["TOTAL"] / $dt[$i]["POKOK"] * 100) : 0;
        $percent2 = ($dtall[$i]["TOTAL"] != 0 && $dt[$i]["POKOK"] != 0) ? ($dtall[$i]["TOTAL"] / $dt[$i]["POKOK"] * 100) : 0;
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
function showTableAll($mod = 0, $nama = "")
{
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

    $summary = array(
        'name' => 'TOTAL', 'namekec' => '', 'ketetapan_wp' => 0, 'ketetapan_rp' => 0,
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
        'percent3' => 0
    );

    $summarys = array(
        'name' => 'JUMLAH', 'namekec' => '', 'ketetapan_wps' => 0, 'ketetapan_rps' => 0,
        'rbl_wps' => 0,
        'rbl_pokoks' => 0,
        'rbl_dendas' => 0,
        'rbl_totals' => 0,
        'percent1s' => 0,

        'rbi_wps' => 0,
        'rbi_pokoks' => 0,
        'rbi_dendas' => 0,
        'rbi_totals' => 0,

        'kom_rbi_wps' => 0,
        'kom_rbi_pokoks' => 0,
        'kom_rbi_dendas' => 0,
        'kom_rbi_totals' => 0,
        'percent2s' => 0,

        'sk_wps' => 0,
        'sk_rps' => 0,
        'percent3s' => 0
    );

    for ($i = 0; $i < $c; $i++) {
        $wpsisa = $dt[$i]["WP"] - $dtall[$i]["WP"];
        $rpsisa = $dt[$i]["POKOK"] - $dtall[$i]["TOTAL"];

        $percent1 = ($dt1[$i]["TOTAL"] != 0 && $dt[$i]["POKOK"] != 0) ? ($dt1[$i]["TOTAL"] / $dt[$i]["POKOK"] * 100) : 0;
        $percent2 = ($dtall[$i]["TOTAL"] != 0 && $dt[$i]["POKOK"] != 0) ? ($dtall[$i]["TOTAL"] / $dt[$i]["POKOK"] * 100) : 0;
        $percent3 = ($rpsisa != 0 && $dt[$i]["POKOK"] != 0) ? ($rpsisa / $dt[$i]["POKOK"] * 100) : 0;

        $dtname = $dt[$i]["name"];
        $dtnamekec = $dt[$i]["namekec"];


        $tmp = array(
            "name" => $dt[$i]["name"],
            "namekec" => $dt[$i]["namekec"],
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

        $summarys['ketetapan_wps'] += $dt[$i]["WP"];
        $summarys['ketetapan_rps'] += $dt[$i]["POKOK"];

        $summarys['rbl_wps'] += $dt1[$i]["WP"];
        $summarys['rbl_pokoks'] += $dt1[$i]["POKOK"];
        $summarys['rbl_dendas'] += $dt1[$i]["DENDA"];
        $summarys['rbl_totals'] += $dt1[$i]["TOTAL"];
        $summarys['percent1s'] += $prc1s;

        $summarys['rbi_wps'] += $dt2[$i]["WP"];
        $summarys['rbi_pokoks'] += $dt2[$i]["POKOK"];
        $summarys['rbi_dendas'] += $dt2[$i]["DENDA"];
        $summarys['rbi_totals'] += $dt2[$i]["TOTAL"];

        $summarys['kom_rbi_wps'] += $dtall[$i]["WP"];
        $summarys['kom_rbi_pokoks'] += $dtall[$i]["POKOK"];
        $summarys['kom_rbi_dendas'] += $dtall[$i]["DENDA"];
        $summarys['kom_rbi_totals'] += $dtall[$i]["TOTAL"];
        $summarys['percent2s'] += $prc2s;

        //$summary['percent2'] += $summary['percent2'] + $percent2;
        $summarys['sk_wps'] += $wpsisa;
        $summarys['sk_rps'] += $rpsisa;
        $summarys['percent3s'] += $prc3s;


        if ($i != 0) {

            if ($dtnamekec == $tempdtnamekec) {
                $dtnamekec = "";

                //hitung total jumlah jumlah sagala macem.
            } else { // ganti kecamatan
                //tulis jumlah heula kecamatan sebelumnya.


                $dtnamekec = $dt[$i]["namekec"];
                $tempdtnamekec = $dtnamekec;
                $data[] = $summarys;

                $summarys['ketetapan_wps'] = 0;
                $summarys['ketetapan_rps'] = 0;
                $summarys['rbl_wps'] = 0;
                $summarys['rbl_pokoks'] = 0;
                $summarys['rbl_dendas'] = 0;
                $summarys['rbl_totals'] = 0;
                $summarys['percent1s'] = 0;
                $summarys['rbi_wps'] = 0;
                $summarys['rbi_pokoks'] = 0;
                $summarys['rbi_dendas'] = 0;
                $summarys['rbi_totals'] = 0;
                $summarys['kom_rbi_wps'] = 0;
                $summarys['kom_rbi_pokoks'] = 0;
                $summarys['kom_rbi_dendas'] = 0;
                $summarys['kom_rbi_totals'] = 0;
                $summarys['sk_wps'] = 0;
                $summarys['sk_rps'] = 0;
                $summarys['percent3s'] = 0;
            }
        } else {
            $tempdtnamekec = $dtnamekec;
        }

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

        //$summary['percent2'] += $summary['percent2'] + $percent2;
        $summary['sk_wp'] += $wpsisa;
        $summary['sk_rp'] += $rpsisa;
        //$summary['percent3'] += $summary['percent3'] + $percent3;


    }

    $summary['percent1'] = ($summary['ketetapan_rp'] != 0 && $summary['rbl_total'] != 0) ? ($summary["rbl_total"] / $summary["ketetapan_rp"] * 100) : 0;
    $summary['percent2'] = ($summary['ketetapan_rp'] != 0 && $summary['kom_rbi_total'] != 0) ? ($summary["kom_rbi_total"] / $summary["ketetapan_rp"] * 100) : 0;
    $summary['percent3'] = ($summary['ketetapan_rp'] != 0 && $summary['sk_rp'] != 0) ? ($summary["sk_rp"] / $summary["ketetapan_rp"] * 100) : 0;

    $data[] = $summary;
    return $data;
}
function getKetetapanAll()
{
    global $DBLink, $appConfig, $thn, $qBuku, $speriode, $eperiode, $kecamatan;
    $myDBLink = openMysql();

    $where = "";
    $wherekec = "";
    if ($thn != "") {
        $where .= "AND PBB.SPPT_TAHUN_PAJAK='$thn'";
    }
    if ($kecamatan != "") {
        $where .= " AND PBB.OP_KECAMATAN_KODE='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }
    $query = "  SELECT ID, KELURAHAN, KECAMATAN, sum(JML) AS JML, SUM(JUMLAH) AS JUMLAH
FROM(	
					SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 0 JML, 0 JUMLAH
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				" . $wherekec . "
				GROUP BY KEL.CPC_TKL_ID

			UNION ALL
				SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, COUNT(PBB.WP_NAMA) AS JML, SUM(PBB.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=PBB.OP_KECAMATAN_KODE WHERE 1=1 " . $where . $qBuku . "
				GROUP BY KEL.CPC_TKL_ID


) y
GROUP BY ID
ORDER BY KECAMATAN, KELURAHAN
			";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    $data     = array();
    $i        = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]              = $row["ID"];
        $data[$i]["KECAMATAN"]       = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]       = $row["KELURAHAN"];
        $data[$i]["JML"]          = $row["JML"];
        $data[$i]["JUMLAH"]          = $row["JUMLAH"];

        $i++;
    }
    return $data;
}


function getBulanLaluAll()
{
    global $DBLink, $appConfig, $thn, $qBuku,  $eperiode, $eperiode2, $speriode, $kecamatan, $arrBln;
    $myDBLink = openMysql();

    $bulan_ini_idx = date("n", strtotime($eperiode2));
    if ($bulan_ini_idx == "1")
        $bulan_lalu_idx = 12;
    else
        $bulan_lalu_idx  = $bulan_ini_idx - 1;


    $bulan_ini = $arrBln[$bulan_ini_idx];
    $bulan_lalu = $arrBln[$bulan_lalu_idx];
    $tahun_ini = date("Y", strtotime($eperiode2));
    $tahun_lalu = date("Y", strtotime($eperiode2)) - 1;
    $periode = "and month(payment_paid) = '$bulan_lalu_idx' and year(payment_paid)='$tahun_ini' ";
    if ($thn != "")
        $periode .= "and sppt_tahun_pajak = '{$thn}'";

    $where = "";
    $wherekec = "";

    if ($kecamatan != "") {
        $where .= " AND PBB.OP_KECAMATAN_KODE='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }
    $query = "  SELECT ID, KELURAHAN, KECAMATAN, sum(JML) AS JML, SUM(JUMLAH) AS JUMLAH, SUM(JUMLAH) AS JUMLAH,
				sum(DENDA) AS DENDA, sum(TOTAL) AS TOTAL
FROM(	
					SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 0 JML, 0 JUMLAH, 0 DENDA, 0 TOTAL
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				" . $wherekec . "
				GROUP BY KEL.CPC_TKL_ID

			UNION ALL
				SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, COUNT(PBB.WP_NAMA) AS JML, SUM(PBB.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH,
					sum(PBB.PBB_DENDA) AS DENDA, sum(PBB.PBB_TOTAL_BAYAR) AS TOTAL
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=PBB.OP_KECAMATAN_KODE WHERE PBB.PAYMENT_FLAG='1' " . $where . " " . $qBuku . " " . $periode . " 
				GROUP BY KEL.CPC_TKL_ID


) y
GROUP BY ID
ORDER BY KECAMATAN, KELURAHAN
			";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    $data     = array();
    $i        = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]              = $row["ID"];
        $data[$i]["KECAMATAN"]       = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]       = $row["KELURAHAN"];
        $data[$i]["JML"]          = $row["JML"];
        $data[$i]["JUMLAH"]          = $row["JUMLAH"];
        $data[$i]["DENDA"]          = $row["DENDA"];
        $data[$i]["TOTAL"]          = $row["TOTAL"];
        $i++;
    }
    return $data;
}

function getBulanSekarangAll()
{
    global $DBLink, $appConfig, $thn, $qBuku,  $eperiode, $eperiode2, $speriode, $kecamatan, $arrBln;
    $myDBLink = openMysql();

    $bulan_ini_idx = date("n", strtotime($eperiode2));
    if ($bulan_ini_idx == "1")
        $bulan_lalu_idx = 12;
    else
        $bulan_lalu_idx  = $bulan_ini_idx - 1;


    $bulan_ini = $arrBln[$bulan_ini_idx];
    $bulan_lalu = $arrBln[$bulan_lalu_idx];
    $tahun_ini = date("Y", strtotime($eperiode2));
    $periode = "and month(payment_paid) = '$bulan_ini_idx' and year(payment_paid)='$tahun_ini'  ";
    if ($thn != "")
        $periode .= "and sppt_tahun_pajak = '{$thn}'";

    $tahun = "";
    $where = "";
    $wherekec = "";
    if ($kecamatan != "") {
        $where .= " AND PBB.OP_KECAMATAN_KODE='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }
    $query = "  SELECT ID, KELURAHAN, KECAMATAN, sum(JML) AS JML, SUM(JUMLAH) AS JUMLAH, SUM(JUMLAH) AS JUMLAH,
                sum(DENDA) AS DENDA, sum(TOTAL) AS TOTAL
FROM(   
                    SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 0 JML, 0 JUMLAH, 0 DENDA, 0 TOTAL
                FROM
                    cppmod_tax_kelurahan KEL
                JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                " . $wherekec . "
                GROUP BY KEL.CPC_TKL_ID

            UNION ALL
                SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, COUNT(PBB.WP_NAMA) AS JML, SUM(PBB.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH,
                    sum(PBB.PBB_DENDA) AS DENDA, sum(PBB.PBB_TOTAL_BAYAR) AS TOTAL
                FROM
                    cppmod_tax_kelurahan KEL
                JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=PBB.OP_KECAMATAN_KODE WHERE PBB.PAYMENT_FLAG='1' " . $where . " " . $qBuku . " " . $periode . " 
                GROUP BY KEL.CPC_TKL_ID


) y
GROUP BY ID
ORDER BY KECAMATAN, KELURAHAN
            ";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    $data   = array();
    $i      = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]           = $row["ID"];
        $data[$i]["KECAMATAN"]    = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]    = $row["KELURAHAN"];
        $data[$i]["JML"]          = $row["JML"];
        $data[$i]["JUMLAH"]       = $row["JUMLAH"];
        $data[$i]["DENDA"]        = $row["DENDA"];
        $data[$i]["TOTAL"]        = $row["TOTAL"];
        $i++;
    }
    return $data;
}

function getSampaiBulanSekarangAll()
{
    global $DBLink, $appConfig, $thn, $qBuku,  $eperiode, $eperiode2, $speriode, $kecamatan, $arrBln;
    $myDBLink = openMysql();
    $e_date = date('Y-m-') . date('t', strtotime(date('m') . '/1/' . date('Y')));
    $periode = " AND date(sppt_tanggal_terbit) < '$e_date'";

    $where = "";
    $wherekec = "";
    
    $bulan_ini_idx = date("n", strtotime($eperiode2));
    if ($bulan_ini_idx == "1")
        $bulan_lalu_idx = 12;
    else
        $bulan_lalu_idx  = $bulan_ini_idx - 1;


    $bulan_ini = $arrBln[$bulan_ini_idx];
    $bulan_lalu = $arrBln[$bulan_lalu_idx];
    $tahun_ini = date("Y", strtotime($eperiode2));
    $periode = "and payment_paid between '$eperiode 00:00:00' and '$eperiode2 23:59:59'";

    if ($thn != "")
        $periode .= "and sppt_tahun_pajak = '{$thn}'";

    if ($kecamatan != "") {
        $where .= " AND PBB.OP_KECAMATAN_KODE='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }
    $query = "  SELECT ID, KELURAHAN, KECAMATAN, sum(JML) AS JML, SUM(JUMLAH) AS JUMLAH, SUM(JUMLAH) AS JUMLAH,
                    sum(DENDA) AS DENDA, sum(TOTAL) AS TOTAL
    FROM(   
                        SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 0 JML, 0 JUMLAH, 0 DENDA, 0 TOTAL
                    FROM
                        cppmod_tax_kelurahan KEL
                    JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                    " . $wherekec . "
                    GROUP BY KEL.CPC_TKL_ID
                UNION ALL
                    SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, COUNT(PBB.WP_NAMA) AS JML, SUM(PBB.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH,
                        sum(PBB.PBB_DENDA) AS DENDA, sum(PBB.PBB_TOTAL_BAYAR) AS TOTAL
                    FROM
                        cppmod_tax_kelurahan KEL
                    JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
                    JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=PBB.OP_KECAMATAN_KODE WHERE PBB.PAYMENT_FLAG='1' " . $where . " " . $qBuku . " " . $periode . " 
                    GROUP BY KEL.CPC_TKL_ID

    ) y
    GROUP BY ID
    ORDER BY KECAMATAN, KELURAHAN
                ";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    $data   = array();
    $i      = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]           = $row["ID"];
        $data[$i]["KECAMATAN"]    = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]    = $row["KELURAHAN"];
        $data[$i]["JML"]          = $row["JML"];
        $data[$i]["JUMLAH"]       = $row["JUMLAH"];
        $data[$i]["DENDA"]        = $row["DENDA"];
        $data[$i]["TOTAL"]        = $row["TOTAL"];
        $i++;
    }
    return $data;
}


function getSisaSampaiBulanSekarangAll()
{
    global $DBLink, $appConfig, $thn, $qBuku,  $eperiode, $speriode, $kecamatan;
    $myDBLink = openMysql();

    $firstMon = firstDay('01', $thn);
    $nowMon = lastDay($eperiode, $thn);

    $periode = "and payment_paid between '{$firstMon}' and '{$nowMon}'"; //Antara tanggal 01/01/ sampai sekarang

    $where = "";
    $wherekec = "";
    if ($thn != "") {
        $where .= "AND PBB.SPPT_TAHUN_PAJAK='$thn'";
    }
    if ($kecamatan != "") {
        $where .= " AND PBB.OP_KECAMATAN_KODE='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }
    $query = "  SELECT ID, KELURAHAN, KECAMATAN, sum(JML) AS JML, SUM(JUMLAH) AS JUMLAH, SUM(JUMLAH) AS JUMLAH,
				sum(DENDA) AS DENDA, sum(TOTAL) AS TOTAL
FROM(	
					SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 0 JML, 0 JUMLAH, 0 DENDA, 0 TOTAL
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				" . $wherekec . "
				GROUP BY KEL.CPC_TKL_ID
			UNION ALL
				SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, COUNT(PBB.WP_NAMA) AS JML, SUM(PBB.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH,
					sum(PBB.PBB_DENDA) AS DENDA, sum(PBB.PBB_TOTAL_BAYAR) AS TOTAL
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=PBB.OP_KECAMATAN_KODE WHERE PBB.PAYMENT_FLAG='1' " . $where . " " . $qBuku . " 
				GROUP BY KEL.CPC_TKL_ID

) y
GROUP BY ID
ORDER BY KECAMATAN, KELURAHAN
			";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    $data     = array();
    $i        = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]              = $row["ID"];
        $data[$i]["KECAMATAN"]       = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]       = $row["KELURAHAN"];
        $data[$i]["JML"]          = $row["JML"];
        $data[$i]["JUMLAH"]          = $row["JUMLAH"];
        $data[$i]["DENDA"]          = $row["DENDA"];
        $data[$i]["TOTAL"]          = $row["TOTAL"];
        $i++;
    }
    return $data;
}
function showTableAllNew($mod = 0, $nama = "")
{
    global $appConfig, $kecamatan;

    $dtketetapan        = getKetetapanAll();

    //Ambil data tertunda
    $dtBulanLalu               = getBulanLaluAll();
    $dtBulanSekarang           = getBulanSekarangAll();
    $dtSampaiBulanSekarang       = getSampaiBulanSekarangAll();
    $dtSisaSampaiBulanSekarang = getSisaSampaiBulanSekarangAll();


    $tutupa = "";
    $satu = "";
    $dua = "";
    $tiga = "";
    $empat = "";

    $c             = count($dtketetapan);
    $html         = "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
    $a             = 1;
    $html         .= headerMonitoringRealisasi($mod, $nama);
    $summary = array(
        'name' => 'TOTAL', 'namekec' => '', 'ketetapan_wp' => 0, 'ketetapan_rp' => 0,
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
        'percent3' => 0
    );

    $summarys = array(
        'name' => 'JUMLAH', 'namekec' => '', 'ketetapan_wps' => 0, 'ketetapan_rps' => 0,
        'rbl_wps' => 0,
        'rbl_pokoks' => 0,
        'rbl_dendas' => 0,
        'rbl_totals' => 0,
        'percent1s' => 0,

        'rbi_wps' => 0,
        'rbi_pokoks' => 0,
        'rbi_dendas' => 0,
        'rbi_totals' => 0,

        'kom_rbi_wps' => 0,
        'kom_rbi_pokoks' => 0,
        'kom_rbi_dendas' => 0,
        'kom_rbi_totals' => 0,
        'percent2s' => 0,

        'sk_wps' => 0,
        'sk_rps' => 0,
        'percent3s' => 0
    );
    for ($i = 0; $i < $c; $i++) {

        $wpsisa = $dtketetapan[$i]["JML"] - $dtSampaiBulanSekarang[$i]["JML"];
        $rpsisa = $dtketetapan[$i]["JUMLAH"] - $dtSampaiBulanSekarang[$i]["JUMLAH"];

        $percent1 = ($dtBulanLalu[$i]["TOTAL"] != 0 && $dtketetapan[$i]["JUMLAH"] != 0) ? ($dtBulanLalu[$i]["TOTAL"] / $dtketetapan[$i]["JUMLAH"] * 100) : 0;
        $percent2 = ($dtSampaiBulanSekarang[$i]["TOTAL"] != 0 && $dtketetapan[$i]["JUMLAH"] != 0) ? ($dtSampaiBulanSekarang[$i]["TOTAL"] / $dtketetapan[$i]["JUMLAH"] * 100) : 0;
        $percent3 = ($rpsisa != 0 && $dtketetapan[$i]["JUMLAH"] != 0) ? ($rpsisa / $dtketetapan[$i]["JUMLAH"] * 100) : 0;

        $dtname = $dtketetapan[$i]["KELURAHAN"];
        $dtnamekec = $dtketetapan[$i]["KECAMATAN"];
        $tmp = array(
            "name" => $dtketetapan[$i]["KELURAHAN"],
            "namekec" => $dtketetapan[$i]["KECAMATAN"],
            "ketetapan_wp" => number_format($dtketetapan[$i]["JML"], 0, "", ""),
            "ketetapan_rp" => number_format($dtketetapan[$i]["JUMLAH"], 0, "", ""),
            "rbl_wp" => number_format($dtBulanLalu[$i]["JML"], 0, "", ""),
            "rbl_pokok" => number_format($dtBulanLalu[$i]["JUMLAH"], 0, "", ""),
            "rbl_denda" => number_format($dtBulanLalu[$i]["DENDA"], 0, "", ""),
            "rbl_total" => number_format($dtBulanLalu[$i]["TOTAL"], 0, "", ""),
            "percent1" => number_format($percent1, 2, ",", "."),
            "rbi_wp" => number_format($dtBulanSekarang[$i]["JML"], 0, "", ""),
            "rbi_pokok" => number_format($dtBulanSekarang[$i]["JUMLAH"], 0, "", ""),
            "rbi_denda" => number_format($dtBulanSekarang[$i]["DENDA"], 0, "", ""),
            "rbi_total" => number_format($dtBulanSekarang[$i]["TOTAL"], 0, "", ""),
            "kom_rbi_wp" => number_format($dtSampaiBulanSekarang[$i]["JML"], 0, "", ""),
            "kom_rbi_pokok" => number_format($dtSampaiBulanSekarang[$i]["JUMLAH"], 0, "", ""),
            "kom_rbi_denda" => number_format($dtSampaiBulanSekarang[$i]["DENDA"], 0, "", ""),
            "kom_rbi_total" => number_format($dtSampaiBulanSekarang[$i]["TOTAL"], 0, "", ""),
            "percent2" => number_format($percent2, 2, ",", "."),
            "sk_wp" => number_format($wpsisa, 0, "", ""),
            "sk_rp" => number_format($rpsisa, 0, "", ""),
            "percent3" => number_format($percent3, 2, ",", ".")
        );

        if ($kecamatan == "") {
            if ($i != 0) {

                if ($dtnamekec == $tempdtnamekec) {
                    $dtnamekec = "";

                    //hitung total jumlah jumlah sagala macem.
                } else { // ganti kecamatan
                    //tulis jumlah heula kecamatan sebelumnya.


                    $dtnamekec = $dtketetapan[$i]["KECAMATAN"];
                    $tempdtnamekec = $dtnamekec;
                    $data[] = $summarys;

                    $summarys['ketetapan_wps'] = 0;
                    $summarys['ketetapan_rps'] = 0;
                    $summarys['rbl_wps'] = 0;
                    $summarys['rbl_pokoks'] = 0;
                    $summarys['rbl_dendas'] = 0;
                    $summarys['rbl_totals'] = 0;
                    $summarys['percent1s'] = 0;
                    $summarys['rbi_wps'] = 0;
                    $summarys['rbi_pokoks'] = 0;
                    $summarys['rbi_dendas'] = 0;
                    $summarys['rbi_totals'] = 0;
                    $summarys['kom_rbi_wps'] = 0;
                    $summarys['kom_rbi_pokoks'] = 0;
                    $summarys['kom_rbi_dendas'] = 0;
                    $summarys['kom_rbi_totals'] = 0;
                    $summarys['sk_wps'] = 0;
                    $summarys['sk_rps'] = 0;
                    $summarys['percent3s'] = 0;
                }
            } else {
                $tempdtnamekec = $dtnamekec;
            }
            $summarys['ketetapan_wps'] += $dtketetapan[$i]["JML"];
            $summarys['ketetapan_rps'] += $dtketetapan[$i]["JUMLAH"];

            $summarys['rbl_wps'] += $dtBulanLalu[$i]["JML"];
            $summarys['rbl_pokoks'] += $dtBulanLalu[$i]["JUMLAH"];
            $summarys['rbl_dendas'] += $dtBulanLalu[$i]["DENDA"];
            $summarys['rbl_totals'] += $dtBulanLalu[$i]["TOTAL"];


            $summarys['rbi_wps'] += $dtBulanSekarang[$i]["JML"];
            $summarys['rbi_pokoks'] += $dtBulanSekarang[$i]["JUMLAH"];
            $summarys['rbi_dendas'] += $dtBulanSekarang[$i]["DENDA"];
            $summarys['rbi_totals'] += $dtBulanSekarang[$i]["TOTAL"];

            $summarys['kom_rbi_wps'] += $dtSampaiBulanSekarang[$i]["JML"];
            $summarys['kom_rbi_pokoks'] += $dtSampaiBulanSekarang[$i]["JUMLAH"];
            $summarys['kom_rbi_dendas'] += $dtSampaiBulanSekarang[$i]["DENDA"];
            $summarys['kom_rbi_totals'] += $dtSampaiBulanSekarang[$i]["TOTAL"];


            //$summary['percent2'] += $summary['percent2'] + $percent2;
            $summarys['sk_wps'] += $wpsisa;
            $summarys['sk_rps'] += $rpsisa;
        }
        $data[] = $tmp;

        $summary['ketetapan_wp'] += $dtketetapan[$i]["JML"];
        $summary['ketetapan_rp'] += $dtketetapan[$i]["JUMLAH"];

        $summary['rbl_wp'] += $dtBulanLalu[$i]["JML"];
        $summary['rbl_pokok'] += $dtBulanLalu[$i]["JUMLAH"];
        $summary['rbl_denda'] += $dtBulanLalu[$i]["DENDA"];
        $summary['rbl_total'] += $dtBulanLalu[$i]["TOTAL"];
        $summary['percent1'] += $percent1;

        $summary['rbi_wp'] += $dtBulanSekarang[$i]["JML"];
        $summary['rbi_pokok'] += $dtBulanSekarang[$i]["JUMLAH"];
        $summary['rbi_denda'] += $dtBulanSekarang[$i]["DENDA"];
        $summary['rbi_total'] += $dtBulanSekarang[$i]["TOTAL"];

        $summary['kom_rbi_wp'] += $dtSampaiBulanSekarang[$i]["JML"];
        $summary['kom_rbi_pokok'] += $dtSampaiBulanSekarang[$i]["JUMLAH"];
        $summary['kom_rbi_denda'] += $dtSampaiBulanSekarang[$i]["DENDA"];
        $summary['kom_rbi_total'] += $dtSampaiBulanSekarang[$i]["TOTAL"];
        $summary['percent2'] += $percent2;

        //$summay['percent2'] += $summary['percent2'] + $percent2;
        $summary['sk_wp'] += $wpsisa;
        $summary['sk_rp'] += $rpsisa;
        $summary['percent3'] += $percent3;
    }
    $summary['percent1'] = ($summary['ketetapan_rp'] != 0 && $summary['rbl_total'] != 0) ? number_format(($summary["rbl_total"] / $summary["ketetapan_rp"] * 100), 2, ',', '.') : 0;
    $summary['percent2'] = ($summary['ketetapan_rp'] != 0 && $summary['kom_rbi_total'] != 0) ? number_format(($summary["kom_rbi_total"] / $summary["ketetapan_rp"] * 100), 2, ',', '.') : 0;
    $summary['percent3'] = ($summary['ketetapan_rp'] != 0 && $summary['sk_rp'] != 0) ? number_format(($summary["sk_rp"] / $summary["ketetapan_rp"] * 100), 2, ',', '.') : 0;

    if ($kecamatan == "") {
        $data[] = $summarys;
    }

    $data[] = $summary;
    return $data;
}
function getData($where)
{
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
    $query = "SELECT count(wp_nama) AS WP, sum(PBB_TOTAL_BAYAR-PBB_DENDA) AS POKOK, sum(PBB_DENDA) AS DENDA, "
        . "sum(PBB_TOTAL_BAYAR) as TOTAL FROM PBB_SPPT {$whr}";

    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {     
        $return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
        $return["DENDA"] = ($row["DENDA"] != "") ? $row["DENDA"] : 0;
        $return["TOTAL"] = ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataTargetE2($where)
{
    global $myDBLink, $kd, $thn, $bulan;

    $myDBLink = openMysql();
    $return = array();
    $return["RP"] = 0;
    $return["WP"] = 0;
    $whr = "";
    if ($where) {
        $whr = " where {$where}";
    }
    $query = "SELECT count(wp_nama) AS WP, sum(PBB_TOTAL_BAYAR-PBB_DENDA) AS POKOK FROM PBB_SPPT {$whr}";

    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return["WP"] = ($row["WP"] != "") ? $row["WP"] : 0;
        $return["POKOK"] = ($row["POKOK"] != "") ? $row["POKOK"] : 0;
    }
    closeMysql($myDBLink);
    return $return;
}

function getDataTarget($where)
{
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

    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
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
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$speriode = @isset($_REQUEST['speriode']) ? $_REQUEST['speriode'] : "";

$eperiode = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$eperiode2 = @isset($_REQUEST['eperiode2']) ? $_REQUEST['eperiode2'] : "";
$bulan_lalu_idx = 0;

$arrBln = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
);


$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : 0;
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

$sBuku = "";

if ($kecamatan == "") {
    $data = showTableAllNew();
} else {
    $data = showTableAllNew(1, $nama);
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

$objRichText->createText(': KETETAPAN DAN REALISASI PBB TAHUN ANGGARAN ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D1:J1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': ' . $sBuku);
$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D2:J2');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(": $eperiode s/d " . $eperiode2);
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:J3');


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'BUKU');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'PERIODE');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Model E.2');

$objPHPExcel->getActiveSheet()->getStyle('C1:L3')->applyFromArray(
    array('font' => array('size' => $fontSizeHeader))
);
if ($kecamatan == '') {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('RANGKING');
    $objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A5:C5');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText($appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA']);
    $objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A5:C6')->applyFromArray(
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
if ($kecamatan == "") {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('NO');
    $objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A8:A9');

    $objRichText = new PHPExcel_RichText();
    if ($kecamatan == "") {
        $objRichText->createText('KECAMATAN');
    } else {
        $objRichText->createText('NO');
    }

    $objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
    $objRichText = new PHPExcel_RichText();

    if ($kecamatan == "") {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
    } else {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
    }

    $objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('C8:C9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('D8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('D8:E8');
    $objPHPExcel->getActiveSheet()->setCellValue('D9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('E9', 'RP');
    //$objRichText = new PHPExcel_RichText();
    //$objRichText->createText('REALISASI BULAN LALU (RP)');
    //$objPHPExcel->getActiveSheet()->getCell('F8')->setValue($objRichText);
    //$objPHPExcel->getActiveSheet()->mergeCells('F8:I8');
    //$objPHPExcel->getActiveSheet()->setCellValue('F9', 'WP');
    //$objPHPExcel->getActiveSheet()->setCellValue('G9', 'POKOK');
    //$objPHPExcel->getActiveSheet()->setCellValue('H9', 'DENDA');
    //$objPHPExcel->getActiveSheet()->setCellValue('I9', 'TOTAL');
    //$objRichText = new PHPExcel_RichText();
    //$objRichText->createText('%');
    //$objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
    //$objPHPExcel->getActiveSheet()->mergeCells('J8:J9');
    //$objRichText = new PHPExcel_RichText();
    //$objRichText->createText('REALISASI BULAN INI (RP)');
    //$objPHPExcel->getActiveSheet()->getCell('K8')->setValue($objRichText);
    //$objPHPExcel->getActiveSheet()->mergeCells('K8:N8');
    //$objPHPExcel->getActiveSheet()->setCellValue('K9', 'WP');
    //$objPHPExcel->getActiveSheet()->setCellValue('L9', 'POKOK');
    //$objPHPExcel->getActiveSheet()->setCellValue('M9', 'DENDA');
    //$objPHPExcel->getActiveSheet()->setCellValue('N9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI s/d BULAN INI (RP)');
    $objPHPExcel->getActiveSheet()->getCell('F8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('F8:I8');
    $objPHPExcel->getActiveSheet()->setCellValue('F9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('G9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('H9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('I9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('J8:J9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('SISA KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('K8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('K8:L8');
    $objPHPExcel->getActiveSheet()->setCellValue('K9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('L9', 'RP');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('M8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('M8:M9');
} else {
    $objRichText = new PHPExcel_RichText();
    if ($kecamatan == "") {
        $objRichText->createText('KECAMATAN');
    } else {
        $objRichText->createText('NO');
    }

    $objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
    $objRichText = new PHPExcel_RichText();

    if ($kecamatan == "") {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
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
    //$objRichText = new PHPExcel_RichText();
    //$objRichText->createText('REALISASI BULAN LALU (RP)');
    //$objPHPExcel->getActiveSheet()->getCell('E8')->setValue($objRichText);
    //$objPHPExcel->getActiveSheet()->mergeCells('E8:H8');
    //$objPHPExcel->getActiveSheet()->setCellValue('E9', 'WP');
    //$objPHPExcel->getActiveSheet()->setCellValue('F9', 'POKOK');
    //$objPHPExcel->getActiveSheet()->setCellValue('G9', 'DENDA');
    //$objPHPExcel->getActiveSheet()->setCellValue('H9', 'TOTAL');
    //$objRichText = new PHPExcel_RichText();
    //$objRichText->createText('%');
    //$objPHPExcel->getActiveSheet()->getCell('I8')->setValue($objRichText);
    //$objPHPExcel->getActiveSheet()->mergeCells('I8:I9');
    //$objRichText = new PHPExcel_RichText();
    //$objRichText->createText('REALISASI BULAN INI (RP)');
    //$objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
    //$objPHPExcel->getActiveSheet()->mergeCells('J8:M8');
    //$objPHPExcel->getActiveSheet()->setCellValue('J9', 'WP');
    //$objPHPExcel->getActiveSheet()->setCellValue('K9', 'POKOK');
    //$objPHPExcel->getActiveSheet()->setCellValue('L9', 'DENDA');
    //$objPHPExcel->getActiveSheet()->setCellValue('M9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI s/d BULAN INI (RP)');
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
    $objRichText->createText('SISA KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('J8:K8');
    $objPHPExcel->getActiveSheet()->setCellValue('J9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('K9', 'RP');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('L8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('L8:L9');
}


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$end = "";
if ($kecamatan == '') {
    $end = "A8:M9";
} else {
    $end = "A8:L9";
}
$objPHPExcel->getActiveSheet()->getStyle($end)->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(8);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 0;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

if ($kecamatan == "") {

    $noKec = 1;
    foreach ($data as $buffer) {
        $objPHPExcel->getActiveSheet()->getRowDimension(10 + $no)->setRowHeight(18);
        // data rekap per kelurahan
        $dtnamekec = $buffer['namekec'];
        if ($no != 0) {

            if ($dtnamekec == $tempdtnamekec) {
                $dtnamekec = "";
                //hitung total jumlah jumlah sagala macem.
            } else { // ganti kecamatan
                //tulis jumlah heula kecamatan sebelumnya.
                $dtnamekec = $buffer['namekec'];
                $tempdtnamekec = $dtnamekec;
            }
        } else {
            $tempdtnamekec = $dtnamekec;
        }

        if ($buffer['name'] == "JUMLAH") { //summary per kelurahan
            $objPHPExcel->getActiveSheet()->mergeCells('B' . (10 + $no) . ':C' . (10 + $no));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['ketetapan_rps']);
            //$objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_wps']);
            //$objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_pokoks']);
            //$objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_dendas']);
            //$objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['rbl_totals']);
            //$objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent1s']);
            //$objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_wps']);
            //$objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_pokoks']);
            //$objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_dendas']);
            //$objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['rbi_totals']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['kom_rbi_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['kom_rbi_pokoks']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['kom_rbi_dendas']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['kom_rbi_totals']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent2s']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['sk_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['sk_rps']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['percent3s']);
            $objPHPExcel->getActiveSheet()->getStyle('C' . (10 + $no))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        } else if ($buffer['name'] == 'TOTAL') { //summary tota

            $objPHPExcel->getActiveSheet()->mergeCells('B' . (10 + $no) . ':C' . (10 + $no));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['ketetapan_rp']);
            //$objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_wp']);
            //$objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_pokok']);
            //$objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_denda']);
            //$objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['rbl_total']);
            //$objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent1']);
            //$objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_wp']);
            //$objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_pokok']);
            //$objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_denda']);
            //$objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['kom_rbi_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['kom_rbi_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['kom_rbi_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['kom_rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent2']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['sk_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['sk_rp']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['percent3']);
        } else {
            $nomor = ($dtnamekec != "") ? $noKec : "";
            if ($dtnamekec != "") {
                $noKec++;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), $nomor);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $dtnamekec);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_wp'])->getStyle('C' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['ketetapan_rp'])->getStyle('D' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_wp'])->getStyle('E' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_pokok'])->getStyle('F' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_denda'])->getStyle('G' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['rbl_total'])->getStyle('H' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent1'])->getStyle('I' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_wp'])->getStyle('J' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_pokok'])->getStyle('K' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_denda'])->getStyle('L' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['rbi_total'])->getStyle('M' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['kom_rbi_wp'])->getStyle('N' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['kom_rbi_pokok'])->getStyle('O' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['kom_rbi_denda'])->getStyle('P' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['kom_rbi_total'])->getStyle('Q' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent2'])->getStyle('R' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['sk_wp'])->getStyle('S' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['sk_rp'])->getStyle('T' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['percent3'])->getStyle('U' . (10 + $no))->applyFromArray($noBold);
        }

        $no++;
    }
} else {
    foreach ($data as $buffer) {
        $objPHPExcel->getActiveSheet()->getRowDimension(10 + $no)->setRowHeight(18);

        if ($buffer['name'] == 'JUMLAH') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), "");
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $buffer['ketetapan_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_rp']);
            //$objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['rbl_wp']);
            //$objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_pokok']);
            //$objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_denda']);
            //$objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_total']);
            //$objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['percent1']);
            //$objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['rbi_wp']);
            //$objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_pokok']);
            //$objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_denda']);
            //$objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['kom_rbi_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['kom_rbi_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['kom_rbi_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['kom_rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['percent2']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['sk_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['sk_rp']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['percent3']);
        } else {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), $no + 1);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $buffer['ketetapan_wp'])->getStyle('C' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_rp'])->getStyle('D' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['rbl_wp'])->getStyle('E' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_pokok'])->getStyle('F' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_denda'])->getStyle('G' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_total'])->getStyle('H' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['percent1'])->getStyle('I' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['rbi_wp'])->getStyle('J' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_pokok'])->getStyle('K' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_denda'])->getStyle('L' . (10 + $no))->applyFromArray($noBold);
            //$objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_total'])->getStyle('M' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['kom_rbi_wp'])->getStyle('N' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['kom_rbi_pokok'])->getStyle('O' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['kom_rbi_denda'])->getStyle('P' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['kom_rbi_total'])->getStyle('Q' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['percent2'])->getStyle('R' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['sk_wp'])->getStyle('S' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['sk_rp'])->getStyle('T' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['percent3'])->getStyle('U' . (10 + $no))->applyFromArray($noBold);
        }
        $no++;
    }
}

$tblEnd = "";
if ($kecamatan == '') {
    $tblEnd = "A10:M";
} else {
    $tblEnd = "A10:L";
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle($tblEnd . (9 + count($data)))->applyFromArray(
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
header('Content-Disposition: attachment;filename="realisasiV2_pbb.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
