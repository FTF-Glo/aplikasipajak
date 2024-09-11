<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'dashboardPBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

function getConfigValue($id, $key)
{

	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	if (isset($iErrCode) && $iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}


	$qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

$a = null;
$month = null;
$year = null;
$date1 = null;
$date2 = null;

if (isset($_REQUEST['a'])) {
	$a = $_REQUEST['a'];
}

if (isset($_REQUEST['month'])) {
	$month = $_REQUEST['month'];
}

if (isset($_REQUEST['year'])) {
	$year = $_REQUEST['year'];
}

if (isset($_REQUEST['date1'])) {
	$date1 = $_REQUEST['date1'];
}

if (isset($_REQUEST['date2'])) {
	$date2 = $_REQUEST['date2'];
}

$DbName = getConfigValue($a, 'GW_DBNAME');
$DbHost = getConfigValue($a, 'GW_DBHOST');
$DbPwd = getConfigValue($a, 'GW_DBPWD');
$DbTable = 'PBB_SPPT'; //getConfigValue($a,'BPHTBTABLE');
$DbUser = getConfigValue($a, 'GW_DBUSER');

SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$where = " WHERE PAYMENT_FLAG = 1 AND MONTH(payment_paid) = " . $month . " AND YEAR(payment_paid) = " . $year;
if ($date1 != '' && $date2 != '') {
	$dt1 = substr($date1, 6, 4) . '-' . substr($date1, 3, 2) . '-' . substr($date1, 0, 2);
	$dt2 = substr($date2, 6, 4) . '-' . substr($date2, 3, 2) . '-' . substr($date2, 0, 2);
	$where = " WHERE PAYMENT_FLAG = 1 AND payment_paid >= '" . $dt1 . " 00:00:00' AND payment_paid <= '" . $dt2 . " 23:59:59'";
}

$query = "SELECT SUBSTR(payment_paid,1,10) as paymentpaid,sum(IFNULL(PBB_TOTAL_BAYAR,SPPT_PBB_HARUS_DIBAYAR)) as sum_sppt_dibayar FROM $DbTable $where GROUP BY SUBSTR(payment_paid,1,10)  ORDER BY payment_paid ";

$res = mysqli_query($LDBLink, $query);
if ($res === false) {
	print_r(mysqli_error($LDBLink) . $query);
	return "Tidak Ditemukan";
}

$mon = array();

$dat = array();
$v = 0;
$arrVal = array();
while ($row = mysqli_fetch_array($res)) {
	$dat["tanggal"][$v] = substr($row['paymentpaid'], 8, 2) . '-' . substr($row['paymentpaid'], 5, 2) . '-' . substr($row['paymentpaid'], 0, 4);
	$dat["bayar"][$v] = floatval($row['sum_sppt_dibayar']);
	$v++;
}
$mx = isset($arrVal) && $arrVal != null ? max($arrVal) : null;
$num_rows = mysqli_num_rows($res);

$cm = strlen($mx) - 2;
$pm = pow(10, $cm);

$ret['success'] = true;
$ret['data']['trs_bulan_ini'] = isset($dat["bayar"]) ? $dat["bayar"] : null;
$ret['data']['tgl_trs_bulan_ini'] = isset($dat["tanggal"]) ? $dat["tanggal"] : null;

$where = " WHERE PAYMENT_FLAG = 1 AND YEAR(payment_paid) = " . $year;
$query = "SELECT SUBSTR(payment_paid,1,7) as paymentpaid,sum(IFNULL(PBB_TOTAL_BAYAR,SPPT_PBB_HARUS_DIBAYAR)) as sum_sppt_dibayar, count(*) as jml_transaksi FROM $DbTable $where GROUP BY SUBSTR(payment_paid,1,7)  ORDER BY payment_paid ";

$resy = mysqli_query($LDBLink, $query);
if ($resy === false) {
	print_r(mysqli_error($DBLink) . $query);
	return "Tidak Ditemukan";
}


$mony = array();
$daty = array();
$v = 0;

$arrValy = array();
while ($rowy = mysqli_fetch_array($resy)) {
	$daty[$v]["tanggal"] = $rowy['paymentpaid'];
	$daty[$v]["bayar"] = $rowy['sum_sppt_dibayar'];
	$daty[$v]["jml_transaksi"] = $rowy['jml_transaksi'];
	$arrValy[$v] = $rowy['sum_sppt_dibayar'];
	$v++;
}

$mxy = isset($arrValy) && $arrValy != null ? max($arrValy) : null;
$num_rowsy = mysqli_num_rows($resy);

$cmy = strlen($mxy) - 2;
$pmy = pow(10, $cmy);

$jml_trs = array();
for ($c = 0; $c < 12; $c++) {
	$trs = 0;
	$mony[$c] = 0;
	$jml_trs[$c] = 0;
	for ($f = 0; $f < $num_rowsy; $f++) {
		$m = floatval(substr($daty[$f]["tanggal"], 5, 2));
		//if ($m ==($c+1)) $mony[$c] = round(floatval($daty[$f]["bayar"])/$pmy,2);
		if ($m == ($c + 1)) {
			$mony[$c] = floatval($daty[$f]["bayar"]);
			$jml_trs[$c] = floatval($daty[$f]["jml_transaksi"]);
		}
	}
}

$ret['data']['trs_tahun_ini'] = $mony;
$ret['data']['jml_transaksi'] = $jml_trs;
//tahunan awal
$year  = date("Y");
$where = "WHERE PAYMENT_FLAG = 1 AND YEAR(payment_paid) in (" . $year . ")";
$query = "SELECT payment_paid,sum(IFNULL(PBB_TOTAL_BAYAR,SPPT_PBB_HARUS_DIBAYAR)) as sum_sppt_dibayar FROM $DbTable $where GROUP BY YEAR(DATE(payment_paid))  ORDER BY payment_paid ";

$resy = mysqli_query($LDBLink, $query);
if ($resy === false) {
	print_r(mysqli_error($DBLink) . $query);
	return "Tidak Ditemukan";
}


$mony = array();
$daty = array();
$v = 0;

$arrValy = array();
while ($rowy = mysqli_fetch_array($resy)) {
	$daty[$v]["tanggal"] = $rowy['payment_paid'];
	$daty[$v]["bayar"] = $rowy['sum_sppt_dibayar'];
	$arrValy[$v] = $rowy['sum_sppt_dibayar'];
	$v++;
}

$mxy = isset($arrValy) && $arrValy != null ? max($arrValy) : null;
$num_rowsy = mysqli_num_rows($resy);

$cmy = strlen($mxy) - 2;
$pmy = pow(10, $cmy);

$thn = array();
$year = date('Y') - 11;
for ($i = 0; $i < 12; $i++) {
	$thn[$i] = $year + $i;
}

for ($c = 0; $c < 12; $c++) {
	$trs = 0;
	$mony[$c] = 0;

	for ($f = 0; $f < $num_rowsy; $f++) {
		$m = floatval(substr($daty[$f]["tanggal"], 0, 4));

		//if ($m ==($c+1)) $mony[$c] = round(floatval($daty[$f]["bayar"])/$pmy,2);
		$index = array_search($m, $thn);

		if ($index == $c) $mony[$c] = floatval($daty[$f]["bayar"]);
	}
}

$ret['data']['trs_tahunan'] = $mony;

//tahunan akhir

$where = " WHERE PAYMENT_FLAG = 1 AND DATE(payment_paid) = DATE(NOW())";
$query = "SELECT count(*) as jml_pembayar ,payment_paid,sum(IFNULL(PBB_TOTAL_BAYAR,SPPT_PBB_HARUS_DIBAYAR)) as sum_sppt_dibayar FROM $DbTable $where GROUP BY DATE(payment_paid) = DATE(NOW()) ORDER BY payment_paid ";

$resh = mysqli_query($LDBLink, $query);
if ($resh === false) {
	print_r(mysqli_error($DBLink) . $query);
	return "Tidak Ditemukan";
}

$ret['data']['trs_hari_ini'] = 0;
$ret['data']['jml_pembayar'] = 0;
$ret['data']['per'] = $pmy;
while ($rowh = mysqli_fetch_array($resh)) {
	$ret['data']['jml_pembayar'] = $rowh['jml_pembayar'];
	$ret['data']['trs_hari_ini'] = $rowh['sum_sppt_dibayar'];
}




$json = new Services_JSON();
echo $json->encode($ret);
