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
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"15\"><b>{$dl}<b></td></tr>
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

	$querybefore = "SELECT * FROM $tablebefore ";
	$query = "SELECT * FROM $table ";

	$qBuku = null;

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
		$query .= " WHERE " . $qbuku;
		$querybefore .= " WHERE " . $qbuku;
	}

	$query .= "ORDER BY FLAG ASC, NOP ASC";
	$querybefore .= "ORDER BY FLAG ASC, NOP ASC";

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

	$resd = mysqli_query($DBLink, $query);
	if ($resd === false) {
		echo mysqli_error($DBLink);
		exit();
	} else {
		if (mysqli_num_rows($resd) > 0) {
			while ($res = mysqli_fetch_array($resd)) {
				$pembanding = $res['SPPT_PBB_HARUS_DIBAYAR'];
				if (isset($res['OP_NJKP_TEMP']) && $res['OP_NJKP_TEMP'] != null && $res['OP_NJKP_TEMP'] != 0) {
					$pembanding = $res['OP_NJKP_TEMP'];
				}

				if ($pembanding >= 0 && $pembanding <= 100000) {
					$jumlahbuku1++;
					$totalbuku1 += $pembanding;
					$luasbumi1 += $res['OP_LUAS_BUMI'];
					$luasbangunan1 += $res['OP_LUAS_BANGUNAN'];
				} else if ($pembanding >= 100001 && $pembanding <= 500000) {
					$jumlahbuku2++;
					$totalbuku2 += $pembanding;
					$luasbumi2 += $res['OP_LUAS_BUMI'];
					$luasbangunan2 += $res['OP_LUAS_BANGUNAN'];
				} else if ($pembanding >= 500001 && $pembanding <= 2000000) {
					$jumlahbuku3++;
					$totalbuku3 += $pembanding;
					$luasbumi3 += $res['OP_LUAS_BUMI'];
					$luasbangunan3 += $res['OP_LUAS_BANGUNAN'];
				} else if ($pembanding >= 2000001 && $pembanding <= 5000000) {
					$jumlahbuku4++;
					$totalbuku4 += $pembanding;
					$luasbumi4 += $res['OP_LUAS_BUMI'];
					$luasbangunan4 += $res['OP_LUAS_BANGUNAN'];
				} else if ($pembanding >= 5000001 && $pembanding <= 999999999999999) {
					$jumlahbuku5++;
					$totalbuku5 += $pembanding;
					$luasbumi5 += $res['OP_LUAS_BUMI'];
					$luasbangunan5 += $res['OP_LUAS_BANGUNAN'];
				}
			}
		}

		$before = mysqli_query($DBLink, $querybefore);

		if ($before != null && $before != false && mysqli_num_rows($before) > 0) {
			while ($resb = mysqli_fetch_array($before)) {
				$pembanding = $resb['SPPT_PBB_HARUS_DIBAYAR'];
				if (isset($resb['OP_NJKP_TEMP']) && $resb['OP_NJKP_TEMP'] != null && $resb['OP_NJKP_TEMP'] != 0) {
					$pembanding = $resb['OP_NJKP_TEMP'];
				}

				if ($pembanding >= 0 && $pembanding <= 100000) {
					$jumlahbukubefore1++;
					$totalbukubefore1 += $pembanding;
				} else if ($pembanding >= 100001 && $pembanding <= 500000) {
					$jumlahbukubefore2++;
					$totalbukubefore2 += $pembanding;
				} else if ($pembanding >= 500001 && $pembanding <= 2000000) {
					$jumlahbukubefore3++;
					$totalbukubefore3 += $pembanding;
				} else if ($pembanding >= 2000001 && $pembanding <= 5000000) {
					$jumlahbukubefore4++;
					$totalbukubefore4 += $pembanding;
				} else if ($pembanding >= 5000001 && $pembanding <= 999999999999999) {
					$jumlahbukubefore5++;
					$totalbukubefore5 += $pembanding;
				}
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
		for ($x = 0; $x < count($result); $x++) {
			if (empty($checkall) || (!empty($checkall) && isset($penan[$no]))) {
	?>
				<tr>
					<td>Buku <?php echo $no; ?></td>
					<td><?php echo $result[$x]['jumlahbukubefore']; ?></td>
					<td><?php echo $result[$x]['totalbukubefore']; ?></td>
					<td>Buku <?php echo $no; ?></td>
					<td><?php echo $result[$x]['jumlahbuku']; ?></td>
					<td><?php echo $result[$x]['totalbuku']; ?></td>
					<td><?php echo $result[$x]['luasbumi']; ?></td>
					<td><?php echo $result[$x]['luasbangunan']; ?></td>
					<td><?php echo $result[$x]['kenaikan']; ?></td>
				</tr>
	<?php
			}

			$no++;
		}
	}
	?>
</table>
<?php