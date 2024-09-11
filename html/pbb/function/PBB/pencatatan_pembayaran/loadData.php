<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pencatatan_pembayaran', '', dirname(__FILE__))) . '/';
require_once($sRootPath . 'inc/payment/inc-payment-db-c.php');
require_once($sRootPath . 'inc/payment/db-payment.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

require_once 'PencatatanPembayaran.php';

$core = new PencatatanPembayaran();

$nop1  = $core->request($_REQUEST, 'nop1');
$nop2  = $core->request($_REQUEST, 'nop2');
$nop3  = $core->request($_REQUEST, 'nop3');
$nop4  = $core->request($_REQUEST, 'nop4');
$nop5  = $core->request($_REQUEST, 'nop5');
$nop6  = $core->request($_REQUEST, 'nop6');
$nop7  = $core->request($_REQUEST, 'nop7');
$tahun = $core->request($_REQUEST, 'year');
$mode  = $core->request($_REQUEST, 'mode');
$tgl   = $core->request($_REQUEST, 'tgl');

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

$data = $core->inquiry($nop, $tahun);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);

exit;
// aldes


if ($mode == 'cetak_ulang') {
	$sql = "SELECT 
			A.WP_NAMA, 
			A.WP_ALAMAT, 
			A.WP_KELURAHAN, 
			A.WP_RT, 
			A.WP_RW, 
			A.WP_KECAMATAN, 
			A.WP_KOTAKAB, 
			A.WP_KODEPOS,
			A.SPPT_TANGGAL_JATUH_TEMPO,
			IFNULL(A.PAYMENT_FLAG, 0) AS PAYMENT_FLAG,
			IFNULL(A.SPPT_PBB_HARUS_DIBAYAR, 0) AS SPPT_PBB_HARUS_DIBAYAR,
			IFNULL(A.PBB_DENDA, 0) AS SPPT_DENDA,
			IFNULL(A.PBB_TOTAL_BAYAR, 0) AS TOTAL_TAGIHAN_VIEW,";
}


if ($mode == 'cetak_ulang')
	$sql = "SELECT A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.WP_RT, A.WP_RW, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS,
					A.SPPT_TANGGAL_JATUH_TEMPO, IFNULL(A.PAYMENT_FLAG,0) AS PAYMENT_FLAG,
					REPLACE(REPLACE(REPLACE(FORMAT(A.SPPT_PBB_HARUS_DIBAYAR,0),',','#'),'.',','),'#','.') SPPT_PBB_HARUS_DIBAYAR,
					REPLACE(REPLACE(REPLACE(FORMAT(PBB_DENDA,0),',','#'),'.',','),'#','.') SPPT_DENDA,
					REPLACE(REPLACE(REPLACE(FORMAT(PBB_TOTAL_BAYAR,0),',','#'),'.',','),'#','.') TOTAL_TAGIHAN_VIEW,
					PBB_TOTAL_BAYAR TOTAL_TAGIHAN, IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID
				FROM   PBB_SPPT A
				WHERE  SUBSTR(A.NOP, 1, 2) = '$nop1' AND SUBSTR(A.NOP, 3, 2) = '$nop2' AND SUBSTR(A.NOP, 5, 3) = '$nop3' AND SUBSTR(A.NOP, 8, 3) = '$nop4' AND SUBSTR(A.NOP, 11, 3) = '$nop5' AND SUBSTR(A.NOP, 14, 4) = '$nop6' AND SUBSTR(A.NOP, 18, 1) = '$nop7' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
else {
	$tmp = explode("-", $tgl);
	$tgl = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
	$sql = "SELECT A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.WP_RT, A.WP_RW, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS,
				A.SPPT_TANGGAL_JATUH_TEMPO, IFNULL(A.PAYMENT_FLAG,0) AS PAYMENT_FLAG,
				REPLACE(REPLACE(REPLACE(FORMAT(A.SPPT_PBB_HARUS_DIBAYAR,0),',','#'),'.',','),'#','.') SPPT_PBB_HARUS_DIBAYAR,
				@dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'" . $tgl . "')/30) dendaBulan,
				@dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
				@dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
				REPLACE(REPLACE(REPLACE(FORMAT(FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR),0),',','#'),'.',','),'#','.') SPPT_DENDA,
				REPLACE(REPLACE(REPLACE(FORMAT((FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR)+SPPT_PBB_HARUS_DIBAYAR),0),',','#'),'.',','),'#','.') TOTAL_TAGIHAN_VIEW,		
				(FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR)+SPPT_PBB_HARUS_DIBAYAR) TOTAL_TAGIHAN, DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s') AS PAYMENT_PAID
				FROM   PBB_SPPT A
				WHERE  SUBSTR(A.NOP, 1, 2) = '$nop1' AND SUBSTR(A.NOP, 3, 2) = '$nop2' AND SUBSTR(A.NOP, 5, 3) = '$nop3' AND SUBSTR(A.NOP, 8, 3) = '$nop4' AND SUBSTR(A.NOP, 11, 3) = '$nop5' AND SUBSTR(A.NOP, 14, 4) = '$nop6' AND SUBSTR(A.NOP, 18, 1) = '$nop7' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
}
$data = queryOpen($DBLink2, $sql);
echo json_encode($data);
?>
<?php
/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once('../../../inc/payment/inc-payment-db-c.php');
require_once('../../../inc/payment/db-payment.php');
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

$nop   = $_REQUEST['nop'];
$tahun = $_REQUEST['year'];
$mode  = $_REQUEST['mode'];
$tgl  = $_REQUEST['tgl'];

if ($mode == 'cetak_ulang') {
	$sql = "SELECT A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.WP_RT, A.WP_RW, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS, A.SPPT_TANGGAL_JATUH_TEMPO, IFNULL(A.PAYMENT_FLAG,0) AS PAYMENT_FLAG, REPLACE(REPLACE(REPLACE(FORMAT(A.SPPT_PBB_HARUS_DIBAYAR,0),',','#'),'.',','),'#','.') SPPT_PBB_HARUS_DIBAYAR, REPLACE(REPLACE(REPLACE(FORMAT(PBB_DENDA,0),',','#'),'.',','),'#','.') SPPT_DENDA, REPLACE(REPLACE(REPLACE(FORMAT(PBB_TOTAL_BAYAR,0),',','#'),'.',','),'#','.') TOTAL_TAGIHAN_VIEW, PBB_TOTAL_BAYAR TOTAL_TAGIHAN, IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID FROM PBB_SPPT A WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
} else {
	$tmp = explode("-", $tgl);
	$tgl = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
	$sql = "SELECT A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.WP_RT, A.WP_RW, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS, A.SPPT_TANGGAL_JATUH_TEMPO, IFNULL(A.PAYMENT_FLAG,0) AS PAYMENT_FLAG, REPLACE(REPLACE(REPLACE(FORMAT(A.SPPT_PBB_HARUS_DIBAYAR,0),',','#'),'.',','),'#','.') SPPT_PBB_HARUS_DIBAYAR, @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'" . $tgl . "')/30) dendaBulan, @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1, @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2, REPLACE(REPLACE(REPLACE(FORMAT(FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR),0),',','#'),'.',','),'#','.') SPPT_DENDA, REPLACE(REPLACE(REPLACE(FORMAT((FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR)+SPPT_PBB_HARUS_DIBAYAR),0),',','#'),'.',','),'#','.') TOTAL_TAGIHAN_VIEW, (FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR)+SPPT_PBB_HARUS_DIBAYAR) TOTAL_TAGIHAN, DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s') AS PAYMENT_PAID FROM   PBB_SPPT A WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
}
$data = mysqli_query($DBLink2, $sql);
echo json_encode($data);
*/
