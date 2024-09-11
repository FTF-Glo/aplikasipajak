<?php
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
}
?>
<?php
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
}
