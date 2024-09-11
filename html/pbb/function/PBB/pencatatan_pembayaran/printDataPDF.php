<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pencatatan_pembayaran', '', dirname(__FILE__))) . '/';
require_once($sRootPath . 'inc/report_stts/eng-report.php');
require_once($sRootPath . 'inc/payment/inc-payment-db-c.php');
require_once($sRootPath . 'inc/payment/db-payment.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

// $sql     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'TEMPAT_PEMBAYARAN'";
// $result  = mysqli_query($DBLink, $sql);
// $row 	 = mysqli_fetch_array($result);
// $tempat_bayar = $row['CTR_AC_VALUE'];

require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

require_once 'PencatatanPembayaran.php';

$core = new PencatatanPembayaran();

$nop1    = $core->request($_REQUEST, 'nop1');
$nop2    = $core->request($_REQUEST, 'nop2');
$nop3    = $core->request($_REQUEST, 'nop3');
$nop4    = $core->request($_REQUEST, 'nop4');
$nop5    = $core->request($_REQUEST, 'nop5');
$nop6    = $core->request($_REQUEST, 'nop6');
$nop7    = $core->request($_REQUEST, 'nop7');
$tahun   = $core->request($_REQUEST, 'year');
$mode    = $core->request($_REQUEST, 'mode');
$tgl     = $core->request($_REQUEST, 'tgl');
$catatan = $core->request($_REQUEST, 'catatan');

if (
	$nop1 === null ||
	$nop2 === null ||
	$nop3 === null ||
	$nop4 === null ||
	$nop5 === null ||
	$nop6 === null ||
	$nop7 === null
) {
	$data = array();
	echo json_encode($data);
	exit;
}

$nop = $nop1 . $nop2 . $nop3 . $nop4 . $nop5 . $nop6 . $nop7;
$_tgl = $tgl ? explode('-', $tgl) : null;
$tgl = $_tgl ? "{$_tgl[2]}-{$_tgl[1]}-{$_tgl[0]} " . date('H:i:s') : date('Y-m-d H:i:s');

$data = $core->pay($nop, $tahun, $tgl, $mode);
echo $data;
exit;
// aldes





/** USANG */

if ($mode == 'cetak_ulang')
	$sql = "SELECT A.NOP,
						A.SPPT_TAHUN_PAJAK, 
						A.WP_NAMA, 
						A.OP_KECAMATAN,
						A.OP_KELURAHAN,
						A.SPPT_TANGGAL_JATUH_TEMPO,
						A.OP_LUAS_BUMI,
						A.OP_LUAS_BANGUNAN,
						A.SPPT_PBB_HARUS_DIBAYAR,
						A.PBB_DENDA SPPT_DENDA, IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID				   
					FROM   PBB_SPPT A
					WHERE  SUBSTR(A.NOP, 1, 2) = '$nop1' AND SUBSTR(A.NOP, 3, 2) = '$nop2' AND SUBSTR(A.NOP, 5, 3) = '$nop3' AND SUBSTR(A.NOP, 8, 3) = '$nop4' AND SUBSTR(A.NOP, 11, 3) = '$nop5' AND SUBSTR(A.NOP, 14, 4) = '$nop6' AND SUBSTR(A.NOP, 18, 1) = '$nop7' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
else {
	$tmp = explode("-", $tgl);
	$tgl = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
	$sql = "SELECT A.NOP,
						A.SPPT_TAHUN_PAJAK, 
						A.WP_NAMA, 
						A.OP_KECAMATAN,
						A.OP_KELURAHAN,
						A.SPPT_TANGGAL_JATUH_TEMPO,
						A.OP_LUAS_BUMI,
						A.OP_LUAS_BANGUNAN,
						A.SPPT_PBB_HARUS_DIBAYAR,
						@dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'" . $tgl . "')/30) dendaBulan,
						@dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
						@dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
						FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR) SPPT_DENDA,
											CONCAT('" . $tgl . "',DATE_FORMAT(NOW(),' %H:%i:%s')) AS PAYMENT_PAID
					FROM   PBB_SPPT A
					WHERE  SUBSTR(A.NOP, 1, 2) = '$nop1' AND SUBSTR(A.NOP, 3, 2) = '$nop2' AND SUBSTR(A.NOP, 5, 3) = '$nop3' AND SUBSTR(A.NOP, 8, 3) = '$nop4' AND SUBSTR(A.NOP, 11, 3) = '$nop5' AND SUBSTR(A.NOP, 14, 4) = '$nop6' AND SUBSTR(A.NOP, 18, 1) = '$nop7' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
}

$result = mysqli_query($DBLink2, $sql);
if ($row = mysqli_fetch_array($result)) {
	$nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
	$denda = $row['SPPT_DENDA'];
	$total = $nilai + $denda;

	if ($mode != 'cetak_ulang') {
		$sql = "UPDATE `PBB_SPPT` SET `PAYMENT_FLAG`='1', 
							`PAYMENT_PAID`='" . $row['PAYMENT_PAID'] . "', 
							`PBB_DENDA`='$denda', 
							`PBB_TOTAL_BAYAR`='$total', `PAYMENT_OFFLINE_USER_ID`='$uname' 
						WHERE (`NOP`='$nop') AND (`SPPT_TAHUN_PAJAK`='$tahun')";
		$res = mysqli_query($DBLink2, $sql);
	}

	echo '0000';
} else {
	echo '0001';
}

/*
require_once('../../../inc/report_stts/eng-report.php');
require_once('../../../inc/payment/inc-payment-db-c.php');
require_once('../../../inc/payment/db-payment.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$sql     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'TEMPAT_PEMBAYARAN'";
$result  = mysqli_query($DBLink, $sql);
$row 	 = mysqli_fetch_array($result);
$tempat_bayar = $row['CTR_AC_VALUE'];

require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

$nop   = $_REQUEST['nop'];
$tahun = $_REQUEST['year'];
$mode  = $_REQUEST['mode'];
$uname  = $_REQUEST['uname'];
$tgl  = $_REQUEST['tgl'];

if ($mode == 'cetak_ulang')
	$sql = "SELECT A.NOP,
					   A.SPPT_TAHUN_PAJAK, 
					   A.WP_NAMA, 
					   A.OP_KECAMATAN,
					   A.OP_KELURAHAN,
					   A.SPPT_TANGGAL_JATUH_TEMPO,
					   A.OP_LUAS_BUMI,
					   A.OP_LUAS_BANGUNAN,
					   A.SPPT_PBB_HARUS_DIBAYAR,
					   A.PBB_DENDA SPPT_DENDA,
                                           IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID				   
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
else {
	$tmp = explode("-", $tgl);
	$tgl = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
	$sql = "SELECT A.NOP,
					   A.SPPT_TAHUN_PAJAK, 
					   A.WP_NAMA, 
					   A.OP_KECAMATAN,
					   A.OP_KELURAHAN,
					   A.SPPT_TANGGAL_JATUH_TEMPO,
					   A.OP_LUAS_BUMI,
					   A.OP_LUAS_BANGUNAN,
					   A.SPPT_PBB_HARUS_DIBAYAR,
					   @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'" . $tgl . "')/30) dendaBulan,
					   @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
					   @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
					   FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR) SPPT_DENDA,
                                           CONCAT('" . $tgl . "',DATE_FORMAT(NOW(),' %H:%i:%s')) AS PAYMENT_PAID
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
}
$result = mysqli_query($DBLink2, $sql);

if ($row = mysqli_fetch_array($result)) {

	$nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
	$denda = $row['SPPT_DENDA'];
	$total = $nilai + $denda;

	if ($mode != 'cetak_ulang') {
		$sql = "UPDATE `PBB_SPPT` SET `PAYMENT_FLAG`='1', 
						   `PAYMENT_PAID`='" . $row['PAYMENT_PAID'] . "', 
						   `PBB_DENDA`='$denda', 
						   `PBB_TOTAL_BAYAR`='$total',
                                                   `PAYMENT_OFFLINE_USER_ID`='$uname' 
					WHERE (`NOP`='$nop') AND (`SPPT_TAHUN_PAJAK`='$tahun')";
		$res = mysqli_query($DBLink2, $sql);
	}
	echo '0000';
} else {
	echo '0001';
}*/
