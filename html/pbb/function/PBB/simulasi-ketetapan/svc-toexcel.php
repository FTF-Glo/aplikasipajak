<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'simulasi-ketetapan', '', dirname(__FILE__))) . '/';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap Buku.xls");

//error_reporting(E_ALL);

//date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");


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

$arrType = array(
	1 => "OP Baru",
	2 => "Pemecahan",
	3 => "Penggabungan",
	4 => "Mutasi",
	5 => "Perubahan Data",
	6 => "Pembatalan",
	7 => "Duplikat",
	8 => "Penghapusan",
	9 => "Pengurangan",
	10 => "Keberatan"
);

function headerMonitoringE2($mod, $nama)
{
	global $appConfig;
	$model = ($mod == 0) ? "KECAMATAN" : strtoupper($_REQUEST['LBL_KEL']);
	$dl = "";
	if ($mod == 0) {
		$dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "
		<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
		<tr>
			<td colspan=\"15\"><b>{$dl}<b></td>
		</tr>
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
			<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN LALU</td>
			<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
			<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN INI</td>
			<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI S/D BULAN  INI</td>
			<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
			<td colspan=\"2\" width=\"137\" align=\"center\">SISA     KETETAPAN</td>
			<td rowspan=\"2\" width=\"56\" align=\"center\">SISA  %</td>
		</tr>
		<tr>
			<td align=\"center\">WP</td>
			<td align=\"center\">Rp</td>
			<td align=\"center\">WP</td>
			<td align=\"center\">Rp</td>
			<td align=\"center\">WP</td>
			<td align=\"center\">Rp</td>
			<td align=\"center\">WP</td>
			<td align=\"center\">Rp</td>
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
	$myDBLink = mysqli_connect($host . ":" . $port, $user, $pass, $dbname);
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

function convertDate($date, $delimiter = '-')
{
	if ($date == null || $date == '') return '';

	$tmp = explode($delimiter, $date);
	return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}

function getData($where = '')
{
	global $DBLink, $tahun, $buku, $checkall, $tahunConfig;

	if (empty($tahun)) {
		$tahun = $tahunConfig;
	}

	$whereClause = array();
	$where = " ";

	if ($tahun == $tahunConfig) {
		$tahunbefore = $tahun - 1;
		$table = 'cppmod_pbb_sppt_current';
		$tablebefore = "cppmod_pbb_sppt_cetak_" . ($tahunbefore);
	} else {
		$table = "cppmod_pbb_sppt_cetak_$tahun";
		$tablebefore = "cppmod_pbb_sppt_cetak_$tahun";
	}

	$querybefore = "SELECT count(SPPT_PBB_HARUS_DIBAYAR) as 'jumlah', SUM(SPPT_PBB_HARUS_DIBAYAR) as 'total_sppt', SUM(OP_LUAS_BUMI) as 'total_luas_bumi', SUM(OP_LUAS_BANGUNAN) as 'total_luas_bangunan' FROM $tablebefore ";

	$query = "SELECT count(SPPT_PBB_HARUS_DIBAYAR) as 'jumlah', SUM(CASE WHEN OP_NJKP_TEMP = 0 THEN SPPT_PBB_HARUS_DIBAYAR ELSE OP_NJKP_TEMP END) as 'total_sppt', SUM(OP_LUAS_BUMI) as 'total_luas_bumi', SUM(OP_LUAS_BANGUNAN) as 'total_luas_bangunan' FROM $table ";

	$qBuku = null;
	$querys = null;

	if ($buku != 0) {
		switch ($buku) {
			case 1:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000 ";
				break;
			case 12:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 ";
				break;
			case 123:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
				break;
			case 1234:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 12345:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 2:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000 ";
				break;
			case 23:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
				break;
			case 234:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 2345:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 3:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000 ";
				break;
			case 34:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 345:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 4:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000 ";
				break;
			case 45:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
			case 5:
				$qBuku = " SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999 ";
				break;
		}
	}

	if ($qBuku != null) {
		$querys .= " WHERE " . $qBuku;
	}

	$querybefore1  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 0 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 100000 " . $querys;
	$querybefore2  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 100001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 500000 " . $querys;
	$querybefore3  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 500001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 2000000 " . $querys;
	$querybefore4  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 2000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 5000000 " . $querys;
	$querybefore5  = $querybefore . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 5000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 999999999999999 " . $querys;

	$query1  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 0 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 100000 " . $querys;
	$query2  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 100001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 500000 " . $querys;
	$query3  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 500001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 2000000 " . $querys;
	$query4  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 2000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 5000000 " . $querys;
	$query5  = $query . " WHERE IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) >= 5000001 and IF(OP_NJKP_TEMP=0, SPPT_PBB_HARUS_DIBAYAR, OP_NJKP_TEMP) <= 999999999999999 " . $querys;

	$ress = null;

	$jumlahbuku1 = 0;
	$jumlahbuku2 = 0;
	$jumlahbuku3 = 0;
	$jumlahbuku4 = 0;
	$jumlahbuku5 = 0;

	$totalbuku1 = 0;
	$totalbuku2 = 0;
	$totalbuku3 = 0;
	$totalbuku4 = 0;
	$totalbuku5 = 0;

	$jumlahbukubefore1 = 0;
	$jumlahbukubefore2 = 0;
	$jumlahbukubefore3 = 0;
	$jumlahbukubefore4 = 0;
	$jumlahbukubefore5 = 0;

	$totalbukubefore1 = 0;
	$totalbukubefore2 = 0;
	$totalbukubefore3 = 0;
	$totalbukubefore4 = 0;
	$totalbukubefore5 = 0;

	$kenaikan1 = 0;
	$kenaikan2 = 0;
	$kenaikan3 = 0;
	$kenaikan4 = 0;
	$kenaikan5 = 0;

	$luasbumi1 = 0;
	$luasbumi2 = 0;
	$luasbumi3 = 0;
	$luasbumi4 = 0;
	$luasbumi5 = 0;

	$luasbangunan1 = 0;
	$luasbangunan2 = 0;
	$luasbangunan3 = 0;
	$luasbangunan4 = 0;
	$luasbangunan5 = 0;

	$resdbefore = mysqli_query($DBLink, $querybefore1);

	if ($resdbefore != false) {
		$jresdbefore = mysqli_num_rows($resdbefore);
		if ($jresdbefore > 0) {
			while ($hresdbefore = mysqli_fetch_array($resdbefore)) {
				$jumlahbukubefore1 = $hresdbefore["jumlah"];
				$luasbumi1 = $hresdbefore["total_luas_bumi"];
				$luasbangunan1 = $hresdbefore["total_luas_bangunan"];
				$totalbukubefore1 = $hresdbefore["total_sppt"] == null ? 0 : $hresdbefore["total_sppt"];
			}
		}
	}


	$resdbefore = mysqli_query($DBLink, $querybefore2);

	if ($resdbefore != false) {
		$jresdbefore = mysqli_num_rows($resdbefore);
		if ($jresdbefore > 0) {
			while ($hresdbefore = mysqli_fetch_array($resdbefore)) {
				$jumlahbukubefore2 = $hresdbefore["jumlah"];
				$luasbumi2 = $hresdbefore["total_luas_bumi"];
				$luasbangunan2 = $hresdbefore["total_luas_bangunan"];
				$totalbukubefore2 = $hresdbefore["total_sppt"] == null ? 0 : $hresdbefore["total_sppt"];
			}
		}
	}

	$resdbefore = mysqli_query($DBLink, $querybefore3);

	if ($resdbefore != false) {
		$jresdbefore = mysqli_num_rows($resdbefore);
		if ($jresdbefore > 0) {
			while ($hresdbefore = mysqli_fetch_array($resdbefore)) {
				$jumlahbukubefore3 = $hresdbefore["jumlah"];
				$luasbumi3 = $hresdbefore["total_luas_bumi"];
				$luasbangunan3 = $hresdbefore["total_luas_bangunan"];
				$totalbukubefore3 = $hresdbefore["total_sppt"] == null ? 0 : $hresdbefore["total_sppt"];
			}
		}
	}

	$resdbefore = mysqli_query($DBLink, $querybefore4);

	if ($resdbefore != false) {
		$jresdbefore = mysqli_num_rows($resdbefore);
		if ($jresdbefore > 0) {
			while ($hresdbefore = mysqli_fetch_array($resdbefore)) {
				$jumlahbukubefore4 = $hresdbefore["jumlah"];
				$luasbumi4 = $hresdbefore["total_luas_bumi"];
				$luasbangunan4 = $hresdbefore["total_luas_bangunan"];
				$totalbukubefore4 = $hresdbefore["total_sppt"] == null ? 0 : $hresdbefore["total_sppt"];
			}
		}
	}

	$resdbefore = mysqli_query($DBLink, $querybefore5);

	if ($resdbefore != false) {
		$jresdbefore = mysqli_num_rows($resdbefore);
		if ($jresdbefore > 0) {
			while ($hresdbefore = mysqli_fetch_array($resdbefore)) {
				$jumlahbukubefore5 = $hresdbefore["jumlah"];
				$luasbumi5 = $hresdbefore["total_luas_bumi"];
				$luasbangunan5 = $hresdbefore["total_luas_bangunan"];
				$totalbukubefore5 = $hresdbefore["total_sppt"] == null ? 0 : $hresdbefore["total_sppt"];
			}
		}
	}

	$resd = mysqli_query($DBLink, $query1);

	if ($resd != false) {
		$jresd = mysqli_num_rows($resd);
		if ($jresd > 0) {
			while ($hresd = mysqli_fetch_array($resd)) {
				$jumlahbuku1 = $hresd["jumlah"];
				$luasbumi1 = $hresd["total_luas_bumi"];
				$luasbangunan1 = $hresd["total_luas_bangunan"];
				$totalbuku1 = $hresd["total_sppt"] == null ? 0 : $hresd["total_sppt"];
			}
		}
	}

	$resd = mysqli_query($DBLink, $query2);

	if ($resd != false) {
		$jresd = mysqli_num_rows($resd);
		if ($jresd > 0) {
			while ($hresd = mysqli_fetch_array($resd)) {
				$jumlahbuku2 = $hresd["jumlah"];
				$luasbumi2 = $hresd["total_luas_bumi"];
				$luasbangunan2 = $hresd["total_luas_bangunan"];
				$totalbuku2 = $hresd["total_sppt"] == null ? 0 : $hresd["total_sppt"];
			}
		}
	}

	$resd = mysqli_query($DBLink, $query3);

	if ($resd != false) {
		$jresd = mysqli_num_rows($resd);
		if ($jresd > 0) {
			while ($hresd = mysqli_fetch_array($resd)) {
				$jumlahbuku3 = $hresd["jumlah"];
				$luasbumi3 = $hresd["total_luas_bumi"];
				$luasbangunan3 = $hresd["total_luas_bangunan"];
				$totalbuku3 = $hresd["total_sppt"] == null ? 0 : $hresd["total_sppt"];
			}
		}
	}

	$resd = mysqli_query($DBLink, $query4);

	if ($resd != false) {
		$jresd = mysqli_num_rows($resd);
		if ($jresd > 0) {
			while ($hresd = mysqli_fetch_array($resd)) {
				$jumlahbuku4 = $hresd["jumlah"];
				$luasbumi4 = $hresd["total_luas_bumi"];
				$luasbangunan4 = $hresd["total_luas_bangunan"];
				$totalbuku4 = $hresd["total_sppt"] == null ? 0 : $hresd["total_sppt"];
			}
		}
	}

	$resd = mysqli_query($DBLink, $query5);

	if ($resd != false) {
		$jresd = mysqli_num_rows($resd);
		if ($jresd > 0) {
			while ($hresd = mysqli_fetch_array($resd)) {
				$jumlahbuku5 = $hresd["jumlah"];
				$luasbumi5 = $hresd["total_luas_bumi"];
				$luasbangunan5 = $hresd["total_luas_bangunan"];
				$totalbuku5 = $hresd["total_sppt"] == null ? 0 : $hresd["total_sppt"];
			}
		}
	}

	$ress[0]["totalbuku"] = $totalbuku1;
	$ress[1]["totalbuku"] = $totalbuku2;
	$ress[2]["totalbuku"] = $totalbuku3;
	$ress[3]["totalbuku"] = $totalbuku4;
	$ress[4]["totalbuku"] = $totalbuku5;

	$ress[0]["jumlahbuku"] = $jumlahbuku1;
	$ress[1]["jumlahbuku"] = $jumlahbuku2;
	$ress[2]["jumlahbuku"] = $jumlahbuku3;
	$ress[3]["jumlahbuku"] = $jumlahbuku4;
	$ress[4]["jumlahbuku"] = $jumlahbuku5;

	$ress[0]["totalbukubefore"] = $totalbukubefore1;
	$ress[1]["totalbukubefore"] = $totalbukubefore2;
	$ress[2]["totalbukubefore"] = $totalbukubefore3;
	$ress[3]["totalbukubefore"] = $totalbukubefore4;
	$ress[4]["totalbukubefore"] = $totalbukubefore5;

	$ress[0]["jumlahbukubefore"] = $jumlahbukubefore1;
	$ress[1]["jumlahbukubefore"] = $jumlahbukubefore2;
	$ress[2]["jumlahbukubefore"] = $jumlahbukubefore3;
	$ress[3]["jumlahbukubefore"] = $jumlahbukubefore4;
	$ress[4]["jumlahbukubefore"] = $jumlahbukubefore5;

	$ress[0]["luasbumi"] = $luasbumi1;
	$ress[1]["luasbumi"] = $luasbumi2;
	$ress[2]["luasbumi"] = $luasbumi3;
	$ress[3]["luasbumi"] = $luasbumi4;
	$ress[4]["luasbumi"] = $luasbumi5;

	$ress[0]["luasbangunan"] = $luasbangunan1;
	$ress[1]["luasbangunan"] = $luasbangunan2;
	$ress[2]["luasbangunan"] = $luasbangunan3;
	$ress[3]["luasbangunan"] = $luasbangunan4;
	$ress[4]["luasbangunan"] = $luasbangunan5;

	$ress[0]["kenaikan"] = ($totalbukubefore1 > 0) ? ((($totalbuku1 - $totalbukubefore1) / $totalbukubefore1) * 100) : 0;
	$ress[1]["kenaikan"] = $totalbukubefore2 > 0 ? ((($totalbuku2 - $totalbukubefore2) / $totalbukubefore2) * 100) : 0;
	$ress[2]["kenaikan"] = $totalbukubefore3 > 0 ? ((($totalbuku3 - $totalbukubefore3) / $totalbukubefore3) * 100) : 0;
	$ress[3]["kenaikan"] = $totalbukubefore4 > 0 ? ((($totalbuku4 - $totalbukubefore4) / $totalbukubefore4) * 100) : 0;
	$ress[4]["kenaikan"] = $totalbukubefore5 > 0 ? ((($totalbuku5 - $totalbukubefore5) / $totalbukubefore5) * 100) : 0;

	return $ress;
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

// print_r($_REQUEST); exit;

$buku = $_REQUEST['buku'];
$tahun = $_REQUEST['tahun'];
$tahunConfig = $_REQUEST['tahunConfig'];
$checkall = $_REQUEST['checkall'];

if (empty($tahun)) {
	$tahun = $tahunConfig;
}

$tahunbefore = $tahun - 1;

$result = getData();

$penan = null;

if (!empty($checkall)) {
	$checkall = explode(",", $checkall);
	for ($i = 0; $i < count($checkall); $i++) {
		$penan[$checkall[$i]] = $checkall[$i];
	}
}
?>
<style type="text/css">
	table {
		margin: 20px auto;
		border-collapse: collapse;
	}

	table th,
	table td {
		border: 1px solid #3c3c3c;
		padding: 3px 8px;

	}

	a {
		background: blue;
		color: #fff;
		padding: 8px 10px;
		text-decoration: none;
		border-radius: 2px;
	}
</style>
<?php
//header("Content-type: application/vnd-ms-excel");
//header("Content-Disposition: attachment; filename=Rekap Buku.xls");
?>
<h3 style="text-align: center;">Rekap Buku</h3>
<table border="1">
	<tr>
		<th colspan="3"><?php echo $tahunbefore; ?></th>
		<th colspan="6"><?php echo $tahun; ?></th>
	</tr>
	<tr>
		<th>Buku</th>
		<th>Jumlah</th>
		<th>Total</th>
		<th>Buku</th>
		<th>Jumlah</th>
		<th>Total</th>
		<th>Luas Bumi</th>
		<th>Luas Bangunan</th>
		<th>% Kenaikan</th>
	</tr>
	<?php
	$no = 1;
	if ($result != null && count($result) > 0) {
		$totaljumlahbukubefore = 0;
		$totalallbukubefore = 0;
		$totaljumlahbuku = 0;
		$totalallbuku = 0;
		$totalluasbumi = 0;
		$totalluasbangunan = 0;
		$totalkenaikan = 0;
		for ($x = 0; $x < count($result); $x++) {
			if (empty($checkall) || (!empty($checkall) && isset($penan[$no]))) {
	?>
				<tr>
					<td align="left">Buku <?php echo $no; ?></td>
					<td align="left"><?php echo ($result[$x]['jumlahbukubefore'] > 0 ? number_format($result[$x]['jumlahbukubefore'], '0', '', ',') : 0); ?></td>
					<td align="left"><?php echo ($result[$x]['totalbukubefore'] > 0 ? number_format($result[$x]['totalbukubefore'], '0', '', ',') : 0); ?></td>
					<td align="left">Buku <?php echo $no; ?></td>
					<td align="left"><?php echo ($result[$x]['jumlahbuku'] > 0 ? number_format($result[$x]['jumlahbuku'], '0', '', ',') : 0); ?></td>
					<td align="left"><?php echo ($result[$x]['totalbuku'] > 0 ? number_format($result[$x]['totalbuku'], '0', '', ',') : 0); ?></td>
					<td align="left"><?php echo ($result[$x]['luasbumi'] > 0 ? number_format($result[$x]['luasbumi'], '0', '', ',') : 0); ?></td>
					<td align="left"><?php echo ($result[$x]['luasbangunan'] > 0 ? number_format($result[$x]['luasbangunan'], '0', '', ',') : 0); ?></td>
					<td align="left"><?php echo ($result[$x]['kenaikan'] > 0 ? number_format($result[$x]['kenaikan'], '0', '', ',') : 0); ?></td>
				</tr>
		<?php
				$totaljumlahbukubefore += $result[$x]['jumlahbukubefore'];
				$totalallbukubefore += $result[$x]['totalbukubefore'];
				$totaljumlahbuku += $result[$x]['jumlahbuku'];
				$totalallbuku += $result[$x]['totalbuku'];
				$totalluasbumi += $result[$x]['luasbumi'];
				$totalluasbangunan += $result[$x]['luasbangunan'];
				$totalkenaikan += $result[$x]['kenaikan'];
			}

			$no++;
		}
		?>
		<tr>
			<td align="left">Total</td>
			<td align="left"><?php echo ($totaljumlahbukubefore > 0 ? number_format($totaljumlahbukubefore, '0', '', ',') : 0); ?></td>
			<td align="left"><?php echo ($totalallbukubefore > 0 ? number_format($totalallbukubefore, '0', '', ',') : 0); ?></td>
			<td align="left">&nbsp;</td>
			<td align="left"><?php echo ($totaljumlahbuku > 0 ? number_format($totaljumlahbuku, '0', '', ',') : 0); ?></td>
			<td align="left"><?php echo ($totalallbuku > 0 ? number_format($totalallbuku, '0', '', ',') : 0); ?></td>
			<td align="left"><?php echo ($totalluasbumi > 0 ? number_format($totalluasbumi, '0', '', ',') : 0); ?></td>
			<td align="left"><?php echo ($totalluasbangunan > 0 ? number_format($totalluasbangunan, '0', '', ',') : 0); ?></td>
			<td align="left"><?php echo ($totalkenaikan > 0 ? number_format($totalkenaikan, '0', '', ',') : 0); ?></td>
		</tr>
	<?php
	}
	?>
</table>
<?php