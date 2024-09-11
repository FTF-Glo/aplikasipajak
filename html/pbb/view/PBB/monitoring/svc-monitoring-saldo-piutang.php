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

function headerPenetapan()
{
	global $appConfig, $kecamatan, $kelurahan, $namaKec, $namaKel;
	if ($kecamatan != "") {
		if ($kelurahan != "") {
			$dl = " KECAMATAN : " . strtoupper($namaKec) . "<br> " . strtoupper($appConfig['LABEL_KELURAHAN']) . " : " . strtoupper($namaKel) . "";
		} else {
			$dl = " KECAMATAN : " . strtoupper($namaKec) . "";
		}
	} else $dl = $appConfig['NAMA_KOTA'];

	$html = "<table class=\"table table-bordered table-striped table-hover\" style=\"width:700px\"><tr><th colspan=15 class=tleft>{$dl}</th></tr>
		<tr>
			<th width=10>NO</td>
			<th width=30>NOP</td>
			<th>WAJIB PAJAK</td>
			<th>TERHUTANG</td>
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
	global $DBLink;
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

function paging($totalrows)
{
	global $page, $perpage;

	$html = "<div>";
	$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
	$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

	if ($page != 1) {
		//$page--;
		$html .= "&nbsp;<a onclick=\"showPenetapanPage(" . ($page - 1) . ")\"><span id=\"navigator-left\"></span></a>";
	}
	if ($rowlast < $totalrows) {
		//$page++;
		$html .= "&nbsp;<a onclick=\"showPenetapanPage(" . ($page + 1) . ")\"><span id=\"navigator-right\"></span></a>";
	}
	$html .= "</div>";
	return $html;
}

function getCountData($where)
{
	global $myDBLink;

	$myDBLink 			= openMysql();

	$whr = "";
	if ($where) {
		$whr = " where {$where}";
	}
	$qRows = "SELECT COUNT(*) FROM (SELECT	NOP FROM pbb_sppt {$whr} GROUP BY	NOP )ZZZ ";

	$exec 		= mysqli_query($myDBLink, $qRows);
	$resCount 	= mysqli_fetch_array($exec);
	$totalrows  = $resCount[0];
	if ($exec === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	closeMysql($myDBLink);
	return $totalrows;
}

function getData($where)
{
	global $myDBLink, $perpage, $page;

	$myDBLink 			= openMysql();
	$data 				= array();
	$return				= array();
	$return["NOP"]		= "";
	$return["NAMA"]		= "";
	$return["TAGIHAN"]	= 0;
	$hal 				= (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

	$whr = "";
	if ($where) {
		$whr = " where {$where}";
	}
	$query = "SELECT NOP, WP_NAMA, SUM(SPPT_PBB_HARUS_DIBAYAR) AS TUNGGAKAN FROM pbb_sppt {$whr} GROUP BY NOP ORDER BY NOP LIMIT {$hal},{$perpage} ";
	//echo $query;exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		// print_r($row);
		$return["NOP"]		= ($row["NOP"] != "") ? $row["NOP"] : "";
		$return["NAMA"]		= ($row["WP_NAMA"] != "") ? $row["WP_NAMA"] : "";
		$return["TAGIHAN"]	= ($row["TUNGGAKAN"] != 0) ? $row["TUNGGAKAN"] : 0;
		$data[] = $return;
	}
	closeMysql($myDBLink);
	return $data;
}

function showTable()
{
	global $where, $page, $perpage;
	$data 		= getData($where);
	$totalrows 	= getCountData($where);
	$c 			= count($data);
	$html 		= '<div class="tbl-monitoring">';
	$html 		.= headerPenetapan();
	$row 			= (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$no			= ($row + 1);
	if ($c != 0) {
		for ($i = 0; $i < $c; $i++) {
			$html .= "
						<tr class=tright>
							<td>" . $no . "</td>
							<td>" . $data[$i]['NOP'] . "</td>
							<td class=tleft>" . $data[$i]['NAMA'] . "</td>
							<td>" . number_format($data[$i]['TAGIHAN'], 0, ',', '.') . "</td>
						</tr>
						";
			$no++;
		}
	} else {
		$html .= "
					<tr>
						<td colspan=4 class=tcenter>Tidak ada data.</td>
					</tr>";
	}

	return $html .= "	<tr>
							<td colspan=4 class=tcenter>
							" . paging($totalrows) . "
							</td>
						</tr></table>";
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

// print_r($appConfig);
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn1 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$thn2 		= @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : "";
$namaKec	= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel	= @isset($_REQUEST['nk']) ? $_REQUEST['nk'] : "";
//$nop	= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$nop1 = @isset($_REQUEST['nop1']) ? $_REQUEST['nop1'] : "";
$nop2 = @isset($_REQUEST['nop2']) ? $_REQUEST['nop2'] : "";
$nop3 = @isset($_REQUEST['nop3']) ? $_REQUEST['nop3'] : "";
$nop4 = @isset($_REQUEST['nop4']) ? $_REQUEST['nop4'] : "";
$nop5 = @isset($_REQUEST['nop5']) ? $_REQUEST['nop5'] : "";
$nop6 = @isset($_REQUEST['nop6']) ? $_REQUEST['nop6'] : "";
$nop7 = @isset($_REQUEST['nop7']) ? $_REQUEST['nop7'] : "";
// print_r($_REQUEST);

$arrWhere = array();
if ($kecamatan != "") {
	if ($kelurahan != "") array_push($arrWhere, "NOP like '{$kelurahan}%'");
	else array_push($arrWhere, "NOP like '{$kecamatan}%'");
}

/*if (!empty($nop)) {
	array_push($arrWhere, "NOP = '{$nop}'");
}*/
if (!empty($nop1)) array_push($arrWhere, "SUBSTR(NOP, 1, 2) = '{$nop1}'");
if (!empty($nop2)) array_push($arrWhere, "SUBSTR(NOP, 3, 2) = '{$nop2}'");
if (!empty($nop3)) array_push($arrWhere, "SUBSTR(NOP, 5, 3) = '{$nop3}'");
if (!empty($nop4)) array_push($arrWhere, "SUBSTR(NOP, 8, 3) = '{$nop4}'");
if (!empty($nop5)) array_push($arrWhere, "SUBSTR(NOP, 11, 3) = '{$nop5}'");
if (!empty($nop6)) array_push($arrWhere, "SUBSTR(NOP, 14, 4) = '{$nop6}'");
if (!empty($nop7)) array_push($arrWhere, "SUBSTR(NOP, 18, 1) = '{$nop7}'");

if ($thn1 != "" && $thn2 != "") {
	array_push($arrWhere, "SPPT_TAHUN_PAJAK >='{$thn1}' AND SPPT_TAHUN_PAJAK <='{$thn2}' ");
	array_push($arrWhere, "(PAYMENT_FLAG <> '1' OR PAYMENT_FLAG IS NULL OR (PAYMENT_FLAG ='1' AND PAYMENT_PAID > '{$thn2}-12-31 23:59:59'))");
}

$where = implode(" AND ", $arrWhere);

echo showTable();
