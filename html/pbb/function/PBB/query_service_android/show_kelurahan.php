<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	
	$field  = $_REQUEST['field'];
	$value  = $_REQUEST['value'];
	
	if($field) $where = " WHERE $field LIKE '$value' ";
		
	$sql  = "SELECT CPC_TKL_ID,CPC_TKL_KCID,CPC_TKL_KELURAHAN FROM `cppmod_tax_kelurahan` $where ";
	$data = queryOpen($sql);

	echo json_encode($data);
?>