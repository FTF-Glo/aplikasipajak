<?php
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

if ($mode == 'cetak_ulang')
	$sql = "SELECT A.WP_NAMA, A.WP_ALAMAT, A.WP_KELURAHAN, A.WP_RT, A.WP_RW, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS,
					   A.SPPT_TANGGAL_JATUH_TEMPO, IFNULL(A.PAYMENT_FLAG,0) AS PAYMENT_FLAG,
					   REPLACE(REPLACE(REPLACE(FORMAT(A.SPPT_PBB_HARUS_DIBAYAR,0),',','#'),'.',','),'#','.') SPPT_PBB_HARUS_DIBAYAR,
					   REPLACE(REPLACE(REPLACE(FORMAT(PBB_DENDA,0),',','#'),'.',','),'#','.') SPPT_DENDA,
					   REPLACE(REPLACE(REPLACE(FORMAT(PBB_TOTAL_BAYAR,0),',','#'),'.',','),'#','.') TOTAL_TAGIHAN_VIEW,
					   PBB_TOTAL_BAYAR TOTAL_TAGIHAN,
                                           IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
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
					   (FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR)+SPPT_PBB_HARUS_DIBAYAR) TOTAL_TAGIHAN,
                                           DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s') AS PAYMENT_PAID
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
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
