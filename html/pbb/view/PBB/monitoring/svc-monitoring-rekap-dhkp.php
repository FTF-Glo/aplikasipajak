<?php
session_start();
ini_set('memory_limit', '1024M');
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

require_once('classCetakDHKP.php');
require_once('classCetakTandaTerimaSppt.php');

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

function headerRekapPokok($mod, $nama)
{
	global $appConfig, $thn;
	// print_r($appConfig);
	$model = ($mod == 0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod == 0) {
		$dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table class=\"table table-bordered table-striped table-hover\">
	<tr>
		<th colspan=12><b>REKAP DHKP TAHUN " . $thn . "</td>
	</tr>
	<tr>
		<th width=10>NO</td>
		<th width=30>NOP</td>
		<th>NAMA</td>
		<th>ALAMAT WAJIB PAJAK</td>
		<th width=10>RT</td>
		<th width=10>RW</td>
		<th width=10>KELAS TANAH</td>
		<th width=10>KELAS BANGUNAN</td>
		<th width=80>LUAS BUMI</td>
		<th width=50>LUAS BANGUNAN</td>
		<th>TAGIHAN</td>
	</tr>";
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

function getData($mod)
{
	global $DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $appConfig, $qBuku;

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
					SPPT_PBB_HARUS_DIBAYAR > 0 
					AND SPPT_TAHUN_PAJAK = '{$thn}' $qBuku
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

function showTable($mod = 0, $nama = "")
{
	global $eperiode;
	$dt 			= getData($mod);

	$c 			= count($dt);
	$html 		= '<div class="tbl-monitoring">';
	$a = 1;
	$summary = array('sum_luas_bumi' => 0, 'sum_luas_bangunan' => 0, 'sum_tagihan' => 0);
	$html .= headerRekapPokok($mod, $nama);
	for ($i = 0; $i < $c; $i++) {
		$html .=
			"<tr class=tright>
					<td>" . $a . "</td>
					<td>" . $dt[$i]['NOP'] . "</td>
					<td class=tleft>" . $dt[$i]['NAMA'] . "</td>
					<td class=tleft>" . $dt[$i]['ALAMAT'] . "</td>
					<td class=tcenter>" . $dt[$i]['RT'] . "</td>
					<td class=tcenter>" . $dt[$i]['RW'] . "</td>
					<td class=tcenter>" . $dt[$i]['KLS_BUMI'] . "</td>
					<td class=tcenter>" . $dt[$i]['KLS_BANGUNAN'] . "</td>
					<td>" . number_format($dt[$i]['LUAS_BUMI'], 0, '', ',') . "</td>
					<td>" . number_format($dt[$i]['LUAS_BANGUNAN'], 0, '', ',') . "</td>
					<td>" . number_format($dt[$i]['TAGIHAN'], 0, '', ',') . "</td>
				</tr>";

		$summary['sum_luas_bumi']		+= $dt[$i]["LUAS_BUMI"];
		$summary['sum_luas_bangunan'] 	+= $dt[$i]["LUAS_BANGUNAN"];
		$summary['sum_tagihan'] 		+= $dt[$i]["TAGIHAN"];

		$a++;
	}

	$html .= " 
		<tr>
            <td align=\"right\"> </td>
            <td colspan=\"7\">TOTAL</td>
			<td align=\"right\">" . number_format($summary['sum_luas_bumi'], 0, '', ',') . "</td>
            <td align=\"right\">" . number_format($summary['sum_luas_bangunan'], 0, '', ',') . "</td>
            <td align=\"right\">" . number_format($summary['sum_tagihan'], 0, '', ',') . "</td>
        </tr>";

	return $html . "</table>";
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

// echo '<pre>';
// print_r($_REQUEST);

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$kd 				= $appConfig['KODE_KOTA'];
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 			= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 			= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama 				= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$eperiode 			= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan 	= @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$stsPenetapan 		= @isset($_REQUEST['stsPenetapan']) ? $_REQUEST['stsPenetapan'] : "";
$buku 				= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";

$cekHalaman 			= @isset($_REQUEST['cekHalaman']) ? $_REQUEST['cekHalaman'] : "";
$dariHalaman 			= @isset($_REQUEST['dariHalaman']) ? $_REQUEST['dariHalaman'] : null;
$sampaiHalaman 			= @isset($_REQUEST['sampaiHalaman']) ? $_REQUEST['sampaiHalaman'] : null;

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

// aldes
$namaKel 				= @isset($_REQUEST['nn']) ? $_REQUEST['nn'] : "";
$cetakNew 				= @isset($_REQUEST['cetakNew']) ? $_REQUEST['cetakNew'] : "";
$cetakTandaTerimaSppt	= @isset($_REQUEST['cetakTandaTerimaSppt']) ? $_REQUEST['cetakTandaTerimaSppt'] : "";
$displayHtml			= @isset($_REQUEST['displayHtml']) ? $_REQUEST['displayHtml'] : "";
$nop					= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";

if($cetakNew || $cekHalaman) {
	if($cetakTandaTerimaSppt) {
		$cetakTandaTerimaSppt = new CetakTandaTerimaSppt($DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $appConfig, $qBuku, $nama, $namaKel, ($buku == 0 ? 12345 : $buku), $nop);
		
		if($displayHtml == "true") $cetakTandaTerimaSppt->formatHtml();
		
		echo $cetakTandaTerimaSppt->getHtmlHead();
		echo $cetakTandaTerimaSppt->getData();
		echo $cetakTandaTerimaSppt->getHtmlFoot();
		exit;
	}else {
		$cetakDHKP = new CetakDHKP($DBLink, $kecamatan, $thn, $kelurahan, $stsPenetapan, $appConfig, $qBuku, $nama, $namaKel, ($buku == 0 ? 12345 : $buku), $nop);
        if ($cekHalaman) {
			$cetakDHKP->getQuery();
			echo $cetakDHKP->totalPages;
			exit;
		}

		$cetakDHKP->setFromPage($dariHalaman)->setToPage($sampaiHalaman);
		
		if($displayHtml == "true") $cetakDHKP->formatHtml();
		
		echo $cetakDHKP->getHtmlHead();
		echo $cetakDHKP->getData();
		echo $cetakDHKP->getHtmlFoot();
	}
	exit;
}


// var_dump($_REQUEST);exit();
// echo $qBuku;exit();
if ($kecamatan == "") {
	echo showTable();
} else {
	echo showTable(1, $nama);
}
