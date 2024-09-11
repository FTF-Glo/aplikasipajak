<?php
set_time_limit(0);
ob_start();
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');
require_once("inc/payment/inc-payment-db-c.php");

$conn = mysqli_connect(ONPAYS_DBHOST.':'.ONPAYS_DBPORT, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
$gw_conn = mysqli_connect(ONPAYS_DBHOST.':'.ONPAYS_DBPORT, ONPAYS_DBUSER, ONPAYS_DBPWD, OTP_DBNAME);

$qry = "SELECT 
			CPM_NOP AS NOP, 
			MID(CPM_NOP,11,3) AS BLOK, 
			CPM_OT_ZONA_NILAI AS ZNT
		FROM cppmod_pbb_sppt_final
		WHERE 
			LEFT(CPM_NOP,10)='1812102010'";
$res = mysqli_query($conn, $qry);
$check = mysqli_num_rows($res);
if($check == 0) exit(' row 0');

$rows = [];

while($row = mysqli_fetch_assoc($res)) {
    $kodeKel = substr($row['NOP'],0,10) . $row['ZNT'];
    $rows[$kodeKel][] = substr($row['NOP'],10,3);
}

foreach ($rows as $key => $value) {
	$rows[$key] = array_unique($rows[$key]);
}


echo '<pre>';
print_r(json_encode($rows));
exit;

$qry = "SELECT 
			CPM_NOP AS NOP, 
			MID(CPM_NOP,11,3) AS BLOK, 
			CPM_OT_ZONA_NILAI AS ZNT
		FROM cppmod_pbb_sppt_final
		WHERE 
			LEFT(CPM_NOP,10)='1812102010'";
$res = mysqli_query($conn, $qry);
$check = mysqli_num_rows($res);
if($check == 0) exit(' row 0');

$rows = [];

while($row = mysqli_fetch_assoc($res)) {
    // $kodeKel = substr($row['NOP_BARU'],0,10);
    $rows[] = $row;
}


echo '<pre>';
print_r($rows);
exit;