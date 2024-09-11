<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	
	$field  = $_REQUEST['field'];
	$value  = $_REQUEST['value'];
	
	if($field) $where = " WHERE $field LIKE '$value' ";
		
	$sql  = "SELECT CPC_TKC_ID,CPC_TKC_KKID,CPC_TKC_KECAMATAN FROM `cppmod_tax_kecamatan` $where ";
	$data = queryOpen($sql);

	echo json_encode($data);
?>