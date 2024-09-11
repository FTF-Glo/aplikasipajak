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

// error_reporting(E_ALL);
// ini_set('display_errors', 1);


$myDBLink = "";

function headerRangkingRealisasi($mod, $nama)
{
	global $appConfig;
	$model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod == 0) {
		$dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
	<tr>
		<th colspan=\"15\"><b>{$dl}<b></td>
	</tr>
	<tr>
		<th width=\"28\" align=\"center\">NO</td>
		<th width=\"117\" align=\"center\">{$model}</td>
		<th width=\"150\" align=\"center\">JUMLAH KETETAPAN</td>
		<th width=\"150\" align=\"center\">JUMLAH REALISASI</td>
		<th width=\"150\" align=\"center\">PERSENTASE (%)</td>
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


function getData($mod)
{
	global $DBLink, $kd, $kecamatan, $kelurahan, $thn, $bulan, $kab, $s, $qBuku;
	if ($mod == 0) $kec =  getKecamatan($kab);
	else {
		if ($kelurahan)
			$kec = getKelurahan($kelurahan);
		else
			$kec = getKelurahan($kecamatan);
	}
	$c = count($kec);
	$data = array();
	for ($i = 0; $i < $c; $i++) {
		$ketetapan = getKetetapan($kec[$i]["id"]);
		$realisasi = getRealisasi($kec[$i]["id"]);

		$data[$i]["name"] 		= $kec[$i]["name"];
		$data[$i]["id"] 		= $kec[$i]["id"];
		$data[$i]["ketetapan"] 	= $ketetapan['KETETAPAN'];
		$data[$i]["realisasi"] 	= $realisasi['REALISASI'];
		if ($data[$i]["ketetapan"] != 0 && $data[$i]["realisasi"] != 0) {
			$data[$i]["persentase"] = (($realisasi['REALISASI'] / $ketetapan['KETETAPAN']) * 100);
		} else {
			$data[$i]["persentase"] = 0;
		}
	}

	return $data;
}

function getDataAll()
{
	global $myDBLink, $kd, $thn, $bulan, $kecamatan, $kelurahan, $kab, $speriode, $eperiode, $qBuku;

	$myDBLink = openMysql();
	$return = array();
	$whr = "";
	//$srckel=
	if ($thn != "") {
		$whr .= " AND PBB.SPPT_TAHUN_PAJAK=" . $thn . " ";
	}
	if ($kecamatan != "") {
		$kec = " AND PBB.OP_KECAMATAN_KODE=" . $kecamatan . " ";
	}

	if ($kelurahan != "") {
		$kel = " AND KEL.CPC_TKL_ID=" . $kelurahan . " ";
	}

	if ($speriode != "") {
		$whr .= " AND PBB.PAYMENT_PAID >= '" . $speriode . " 00:00:00' ";
	}

	if ($eperiode != "") {
		$whr .= " AND PBB.PAYMENT_PAID <= '" . $eperiode . " 23:59:59' ";
	}


	if ($kecamatan != "") {
		$query = "SELECT
						ID,
						KELURAHAN,
						sum( JML ) AS JUMLAH_KETETAPAN,
						SUM( JUMLAH ) AS JUMLAH_REALISASI,
						ROUND((SUM( JUMLAH )/sum( JML )*100),2) AS PERC1
					FROM
						(
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							0 JML,
							0 JUMLAH
						FROM
							cppmod_tax_kelurahan KEL 
						WHERE
							KEL.CPC_TKL_KCID = '" . $kecamatan . "'
						GROUP BY
							KEL.CPC_TKL_ID UNION ALL
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							SUM( PBB.SPPT_PBB_HARUS_DIBAYAR ) AS JML,
							0 AS JUMLAH
						FROM
							cppmod_tax_kelurahan KEL
							JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE 
						WHERE
							KEL.CPC_TKL_KCID = '" . $kecamatan . "' {$whr} $qBuku
							AND PBB.SPPT_TAHUN_PAJAK = 2019 
							
						GROUP BY
							KEL.CPC_TKL_ID UNION ALL
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							0 JML,
							SUM( PBB.PBB_TOTAL_BAYAR ) AS JUMLAH
						FROM
							cppmod_tax_kelurahan KEL
							JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE 
						WHERE
							PBB.PAYMENT_FLAG = '1' 
							AND KEL.CPC_TKL_KCID = '" . $kecamatan . "' {$whr} $qBuku
							AND PBB.SPPT_TAHUN_PAJAK = 2019 
							
						GROUP BY
							KEL.CPC_TKL_ID 
						) y 
					GROUP BY
						ID 
					ORDER BY
						PERC1 DESC";
		// echo $query.'<br/>';
		// exit;
	} else if ($kelurahan != "") {
		$query = "SELECT
						ID,
						KELURAHAN,
						sum( JML ) AS JUMLAH_KETETAPAN,
						SUM( JUMLAH ) AS JUMLAH_REALISASI,
						ROUND((SUM( JUMLAH )/sum( JML )*100),2) AS PERC1
					FROM
						(
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							0 JML,
							0 JUMLAH
						FROM
							cppmod_tax_kelurahan KEL 
						WHERE
							1 = 1 $kel
						GROUP BY
							KEL.CPC_TKL_ID UNION ALL
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							SUM( PBB.SPPT_PBB_HARUS_DIBAYAR ) AS JML,
							0 AS JUMLAH
						FROM
							cppmod_tax_kelurahan KEL
							JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE 
						WHERE
							1 = 1 {$whr} $kel $qBuku
							AND PBB.SPPT_TAHUN_PAJAK = 2019 
							
						GROUP BY
							KEL.CPC_TKL_ID UNION ALL
						SELECT
							KEL.CPC_TKL_ID AS ID,
							KEL.CPC_TKL_KELURAHAN AS KELURAHAN,
							0 JML,
							SUM( PBB.PBB_TOTAL_BAYAR ) AS JUMLAH
						FROM
							cppmod_tax_kelurahan KEL
							JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE 
						WHERE
							PBB.PAYMENT_FLAG = '1' 
							{$whr} $kel $qBuku
							AND PBB.SPPT_TAHUN_PAJAK = 2019 
							
						GROUP BY
							KEL.CPC_TKL_ID 
						) y 
					GROUP BY
						ID 
					ORDER BY
						PERC1 DESC";
	} else {
		$query = "SELECT
						ID,
						KECAMATAN,
						sum( JML ) AS JUMLAH_KETETAPAN,
						SUM( JUMLAH ) AS JUMLAH_REALISASI,
						ROUND((SUM( JUMLAH )/sum( JML )*100),2) AS PERC1
					FROM
						(
						SELECT
							KEC.CPC_TKC_ID AS ID,
							KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
							0 JML,
							0 JUMLAH
						FROM
							cppmod_tax_kecamatan KEC 
						GROUP BY
							KEC.CPC_TKC_ID 
						UNION ALL
						SELECT
							KEC.CPC_TKC_ID AS ID,
							KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
							SUM( PBB.SPPT_PBB_HARUS_DIBAYAR ) AS JML,
							0 AS JUMLAH
						FROM
							cppmod_tax_kecamatan KEC
							JOIN PBB_SPPT PBB ON KEC.CPC_TKC_ID = PBB.OP_KECAMATAN_KODE 
						WHERE
							1 = 1 
							AND PBB.SPPT_TAHUN_PAJAK = 2019 {$whr} $qBuku
							
						GROUP BY
							KEC.CPC_TKC_ID UNION ALL
						SELECT
							KEC.CPC_TKC_ID AS ID,
							KEC.CPC_TKC_KECAMATAN AS KECAMATAN,
							0 JML,
							SUM( PBB.PBB_TOTAL_BAYAR ) AS JUMLAH
						FROM
							cppmod_tax_kecamatan KEC
							JOIN PBB_SPPT PBB ON KEC.CPC_TKC_ID = PBB.OP_KECAMATAN_KODE 
						WHERE
							PBB.PAYMENT_FLAG = '1' 
							AND PBB.SPPT_TAHUN_PAJAK = 2019 {$whr} $qBuku
							
						GROUP BY
							KEC.CPC_TKC_ID 
						) y 
					GROUP BY
						ID 
					ORDER BY
						PERC1 DESC";
	}
	//echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		if ($kecamatan != "") {
			$return[$i]["KECAMATAN"]    = ($row["KELURAHAN"] != "") ? $row["KELURAHAN"] : "";
		} else if ($kelurahan != "") {
			$return[$i]["KECAMATAN"]    = ($row["KELURAHAN"] != "") ? $row["KELURAHAN"] : "";
		} else {
			$return[$i]["KECAMATAN"]    = ($row["KECAMATAN"] != "") ? $row["KECAMATAN"] : "";
		}
		$return[$i]["JUMLAH_KETETAPAN"] 			= ($row["JUMLAH_KETETAPAN"] != "") ? $row["JUMLAH_KETETAPAN"] : 0;
		$return[$i]["JUMLAH_REALISASI"] 		= ($row["JUMLAH_REALISASI"] != "") ? $row["JUMLAH_REALISASI"] : 0;
		if ($row["PERC1"] > 100) {
			$row["PERC1"] = 100;
		}
		$return[$i]["PERC1"] 		= ($row["PERC1"] != "") ? $row["PERC1"] : 0;

		$i++;
	}


	closeMysql($myDBLink);
	return $return;
}

function showTableAll($mod = 0, $nama = "")
{
	global $eperiode, $jml_d;
	//$eperiode = 1;

	//$dt = getKetetapan($mod);
	// var_dump($dt);exit;
	//$dt1 = getBulanLalu($mod);
	//$dt2 = getBulanSekarang($mod);
	$dtall = array();
	// if ($eperiode == 1)
	// $dtall = getSampaiBulanSekarang($mod);
	// else {
	//     foreach ($dt1 as $key => $row) {
	//         $dtall[$key]["WP"] = $row["WP"] + $dt2[$key]["WP"];
	//         $dtall[$key]["POKOK"] = $row["POKOK"] + $dt2[$key]["POKOK"];
	//         $dtall[$key]["DENDA"] = $row["DENDA"] + $dt2[$key]["DENDA"];
	//         $dtall[$key]["TOTAL"] = $row["TOTAL"] + $dt2[$key]["TOTAL"];
	//     }
	// }

	//	$dtsisa = getSisaSampaiBulanSekarang($mod);
	//$dtsisa = getSisaKetetapan($mod);
	// $c = count($dt);
	//by 35u


	// echo "ini<br>";
	// echo $jml_d;
	// echo "<br>";

	$data = getDataAll();
	$c = count($data);
	//echo $c;
	$html = "";
	$a = 1;
	$html = headerRangkingRealisasi($mod, $nama);

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

	// echo "<pre>";
	// print_r($dt);
	// echo "</pre>";
	$kecamatan = null;
	$no = 1;

	for ($i = 0; $i < $c; $i++) {

		$html .= "<tr>";
		$html .= "<td>" . $no . "</td>";
		//if ( $kecamatan == "" ) {
		if (!empty($data[$i]['KECAMATAN'])) {
			$html .= "<td>" . $data[$i]['KECAMATAN'] . "</td>";
		} else {
			$html .= "<td>" . $data[$i]['KELURAHAN'] . "</td>";
		}

		$html .= "<td align=\"right\">" . number_format($data[$i]['JUMLAH_KETETAPAN'], 0, ",", ".") . "</td>
				<td align=\"right\">" . number_format($data[$i]['JUMLAH_REALISASI'], 0, ",", ".") . "</td>
				<td align=\"right\">" . $data[$i]['PERC1'] . "</td>
			</tr> 
			</tbody>";


		$summary['ketetapan_rp'] += $data[$i]['JUMLAH_KETETAPAN'];
		$summary['rbl_total'] += $data[$i]['JUMLAH_REALISASI'];

		//$summary['percent2'] += $summary['percent2'] + $percent2;
		// $summary['sk_wp'] += $wpsisa;
		// $summary['sk_rp'] += $rpsisa;
		//$summary['percent3'] += $summary['percent3'] + $percent3;

		$no++;
	}

	$summary['percent1'] = ($summary['ketetapan_rp'] != 0 && $summary['rbl_total'] != 0) ? ($summary["rbl_total"] / $summary["ketetapan_rp"] * 100) : 0;
	$html .= " <tr>
            <td align=\"right\"> </td>
            <td>" . $summary['name'] . "</td>
            <td align=\"right\">" . number_format($summary['ketetapan_rp'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['rbl_total'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['percent1'], 2, ',', '.') . "</td>
          </tr>";

	return $html . "</table>";
}

function showTable($mod = 0, $nama = "")
{
	global $eperiode;
	$dt 		= getData($mod);
	// print_r($dt); exit;
	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$a = 1;
	$summary = array('name' => 'TOTAL', 'sum_ketetapan' => 0, 'sum_realisasi' => 0, 'sum_persen' => 0);
	$html .= headerRangkingRealisasi($mod, $nama);
	for ($i = 0; $i < $c; $i++) {
		$dtname 		= $dt[$i]["name"];
		$ketetapan	 	= $dt[$i]["ketetapan"];
		$realisasi 		= $dt[$i]["realisasi"];
		$persentase		= $dt[$i]["persentase"];

		$html .= " <tr>
	            <td align=\"right\">" . $a . "</td>
	            <td align=\"left\">" . $dtname . "</td>
	            <td align=\"right\">" . number_format($ketetapan, 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($realisasi, 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($persentase, 2, ',', '.') . "</td>
	          </tr>";

		$summary['sum_ketetapan']	+= $ketetapan;
		$summary['sum_realisasi'] 	+= $realisasi;
		$summary['sum_persen'] 		+= $persentase;

		$a++;
	}

	$html .= " 
		<tr>
            <td align=\"right\"> </td>
            <td>" . $summary['name'] . "</td>
            <td align=\"right\">" . number_format($summary['sum_ketetapan'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['sum_realisasi'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['sum_persen'], 2, ',', '.') . "</td>
          </tr>";

	return $html . "</table>";
}

function getKetetapan($kel)
{
	global $myDBLink, $kd, $bulan, $appConfig, $where;

	$myDBLink 			= openMysql();
	$db_gw 				= $appConfig['GW_DBNAME'];
	$thn 				= $appConfig['tahun_tagihan'];
	$query = "SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) AS KETETAPAN
				FROM
					{$db_gw}.PBB_SPPT
				WHERE SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%' $where  ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);
	$return["KETETAPAN"] = ($row["KETETAPAN"] != "") ? $row["KETETAPAN"] : 0;
	closeMysql($myDBLink);
	return $return;
}

function getRealisasi($kel)
{
	global $myDBLink, $kd, $bulan, $appConfig, $where;

	$myDBLink 			= openMysql();
	$db_gw 				= $appConfig['GW_DBNAME'];
	$thn 				= $appConfig['tahun_tagihan'];
	$query = "SELECT SUM(PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.PBB_SPPT
				WHERE PAYMENT_FLAG = '1' 
				AND PAYMENT_PAID LIKE '$thn%'
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%' $where";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_fetch_assoc($res);
	$return["REALISASI"] = ($row["REALISASI"] != "") ? $row["REALISASI"] : 0;
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
$buku  				= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 			= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 			= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : $appConfig['tahun_tagihan'];
$nama 				= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$eperiode 			= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan 	= @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$qBuku = "";

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
// echo $where;

// exit();

if ($kecamatan == "" && $kelurahan == "") {
	echo showTableAll();
} else {
	echo showTableAll(1, $nama);
}
