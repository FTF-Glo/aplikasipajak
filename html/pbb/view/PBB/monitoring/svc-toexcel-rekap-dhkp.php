<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
//error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap DHKP " . date('d-m-Y') . ".xls");

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

ini_set('memory_limit', '500M');

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

function getData()
{
	global $DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $qBuku, $appConfig;

	if ($kelurahan == "") {
		$filter = $kecamatan;
	} else {
		$filter = $kelurahan;
	}

	$filStsPenetapan = "";
	if ($stsPenetapan == "1") {
		$filStsPenetapan = "AND FLAG_SUSULAN = 1";
	} else if ($stsPenetapan == "0") {
		$filStsPenetapan = "AND (FLAG_SUSULAN <> 1 OR FLAG_SUSULAN IS NULL)";
	}

	if ($thn == $appConfig['tahun_tagihan']) {
		$table = 'cppmod_pbb_sppt_current';
	} else {
		$table = "cppmod_pbb_sppt_cetak_$thn";
	}
	// $myDBLink 	= openMysql();
	$query 		= "SELECT
					*
				FROM
					{$table}
				WHERE
				SPPT_TAHUN_PAJAK = '{$thn}' $qBuku
				AND NOP LIKE '{$filter}%' 
				$filStsPenetapan ";
	// echo $query.'<br/>'; exit;
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data	= array();
	$i 		= 0;
	while ($row = mysqli_fetch_assoc($res)) {
		// echo "<pre>";
		// print_r($row); exit;
		$data[$i]["NOP"]			= ($row["NOP"] != "") ? $row["NOP"] : "";
		$data[$i]["NAMA"]			= ($row["WP_NAMA"] != "") ? $row["WP_NAMA"] : "";
		$data[$i]["ALAMAT"]			= ($row["WP_ALAMAT"] != "") ? $row["WP_ALAMAT"] : "";
		$data[$i]["RT"]				= ($row["WP_RT"] != "") ? $row["WP_RT"] : "";
		$data[$i]["RW"]				= ($row["WP_RW"] != "") ? $row["WP_RW"] : "";
		$data[$i]["KLS_BUMI"]		= ($row["OP_KELAS_BUMI"] != "") ? $row["OP_KELAS_BUMI"] : "";
		$data[$i]["KLS_BANGUNAN"]	= ($row["OP_KELAS_BANGUNAN"] != "") ? $row["OP_KELAS_BANGUNAN"] : "";
		$data[$i]["LUAS_BUMI"]		= ($row["OP_LUAS_BUMI"] != "") ? $row["OP_LUAS_BUMI"] : 0;
		$data[$i]["LUAS_BANGUNAN"]	= ($row["OP_LUAS_BANGUNAN"] != "") ? $row["OP_LUAS_BANGUNAN"] : 0;
		$data[$i]["TAGIHAN"]		= ($row["SPPT_PBB_HARUS_DIBAYAR"] != "") ? $row["SPPT_PBB_HARUS_DIBAYAR"] : 0;
		$i++;
	}
	// closeMysql($myDBLink);

	return $data;
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
$perpage	= $appConfig['ITEM_PER_PAGE'];

// print_r($appConfig);EXIT;
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec	= @isset($_REQUEST['nkec']) ? $_REQUEST['nkec'] : "";
$namaKel	= @isset($_REQUEST['nkel']) ? $_REQUEST['nkel'] : "";
$stsPenetapan	= @isset($_REQUEST['stsPenetapan']) ? $_REQUEST['stsPenetapan'] : "";

$buku 				= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";

// echo $stsPenetapan; exit;
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
$data = getData();

// print_r($data);exit;
$sumRows = count($data);

$html = '
	<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
		<tr>
			<th colspan="11" align="center">REKAP DHKP TAHUN ' . $thn . '</th>
		</tr>
		<tr>
			<th colspan="11" align="center">KECAMATAN : ' . $namaKec . '</th>
		</tr>
		<tr>
			<th colspan="11" align="center">KELURAHAN : ' . $namaKel . '</th>
		</tr>
		<tr>
			<th align="center">NO</th>
			<th align="center">NOP</th>
			<th align="center">NAMA</th>
			<th align="center">ALAMAT<br />WAJIB PAJAK</th>
			<th align="center">RT</th>
			<th align="center">RW</th>
			<th align="center">KELAS<br />TANAH</th>
			<th align="center">KELAS<br />BANGUNAN</th>
			<th align="center">LUAS<br />BUMI</th>
			<th align="center">LUAS<br />BANGUNAN</th>
			<th align="center">TAGIHAN</th>
		</tr>
';

$row = 1;
$summary = array('S_LS_BUMI' => 0, 'S_LS_BANGUNAN' => 0, 'S_TAGIHAN' => 0);
for ($i = 0; $i < $sumRows; $i++) {
	$html .= '
		<tr>
			<td>' . $row . '</td>
			<td style=\'mso-number-format:"\@";\'> ' . $data[$i]['NOP'] . '</td>
			<td>' . $data[$i]['NAMA'] . '</td>
			<td>' . $data[$i]['ALAMAT'] . '</td>
			<td>' . $data[$i]['RT'] . '</td>
			<td>' . $data[$i]['RW'] . '</td>
			<td> ' . $data[$i]['KLS_BUMI'] . '</td>
			<td> ' . $data[$i]['KLS_BANGUNAN'] . '</td>
			<td>' . $data[$i]['LUAS_BUMI'] . '</td>
			<td>' . $data[$i]['LUAS_BANGUNAN'] . '</td>
			<td>' . number_format($data[$i]['TAGIHAN'], '0', '', ',') . '</td>
		</tr>';

	$row++;

	$summary['S_LS_BUMI'] 		+= $data[$i]['LUAS_BUMI'];
	$summary['S_LS_BANGUNAN'] 	+= $data[$i]['LUAS_BANGUNAN'];
	$summary['S_TAGIHAN'] 		+= $data[$i]['TAGIHAN'];
}

$html .= '
	<tr>
		<td colspan="8">TOTAL</td>
		<td>' . $summary['S_LS_BUMI'] . '</td>
		<td>' . $summary['S_LS_BANGUNAN'] . '</td>
		<td>' . number_format($summary['S_TAGIHAN'], '0', '', ',') . '</td>
	</tr>
</table>';

echo $html;
exit();
