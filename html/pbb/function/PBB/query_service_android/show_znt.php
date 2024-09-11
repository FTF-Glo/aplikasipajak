<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	
	$field  = $_REQUEST['field'];
	$value  = $_REQUEST['value'];
	$start  = $_REQUEST['start']; if(!$start) $start = 0;
	$limit  = $_REQUEST['limit']; if(!$limit) $limit = 1000;
	
	$limit  = " LIMIT $start, $limit ";
	
	
	if($field) $where = " WHERE $field LIKE '$value' ";
		
	$sql  = "SELECT CPM_KODE_LOKASI, CPM_KODE_ZNT, CPM_NIR FROM `cppmod_pbb_znt` $where $limit";
	$data = queryOpen($sql);

	echo json_encode($data);
?>
