<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	require_once('connectDB_GW.php');
	require_once('queryOpen.php');
	SCANPayment_ConnectToDB($DBLink2, $DBConn2, BPHTBHOSTPORT, BPHTBUSERNAME, BPHTBPASSWORD, BPHTBDBNAME, true);
	        // var_dump($DBLink2);die();
	$nop   = $_REQUEST['nop'];
	$mode  = $_REQUEST['mode'];
	$tgl  = $_REQUEST['tgl'];
	
	if($mode=='cetak_ulang')
		$sql = "SELECT * 
				FROM   ssb A 
				WHERE  A.op_nomor = '$nop' OR A.payment_code = '$nop' OR A.payment_code=REPLACE('$nop','-','') ORDER BY saved_date DESC LIMIT 1";
	else{
		$tmp = explode("-", $tgl);
                $tgl = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
                $sql = "SELECT * FROM   ssb A WHERE  A.op_nomor = '$nop' OR A.payment_code = '$nop' OR A.payment_code=REPLACE('$nop','-','') ORDER BY saved_date DESC LIMIT 1";
        }
        // die(var_dump($sql));
	$data = queryOpen($sql, $DBLink2);
	echo json_encode($data);
?>