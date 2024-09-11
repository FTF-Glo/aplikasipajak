<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'uploadberkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

$json = new Services_JSON();

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getBerkas() {
    global $DBLink;
    $id_bks = $_POST['id_bks'];
    
    $qry = "select * from cppmod_ssb_berkas WHERE CPM_BERKAS_ID = '{$id_bks} order by CPM_BERKAS_ID DESC limit 1";
    $res = mysqli_query($DBLink, $qry);
    $row = mysqli_fetch_array($res);
	
	$stringArray = explode(';',$row['CPM_BERKAS_LAMPIRAN']);
	$status="";
	if(($row['CPM_BERKAS_JNS_PEROLEHAN']==1) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==30)){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('8', $stringArray)) && (in_array('9', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==2){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('10', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if(($row['CPM_BERKAS_JNS_PEROLEHAN']==3) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==31)){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('11', $stringArray)) && (in_array('12', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==4){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('13', $stringArray)) && (in_array('14', $stringArray)) && (in_array('15', $stringArray)) && (in_array('16', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if(($row['CPM_BERKAS_JNS_PEROLEHAN']==5) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==32)){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('13', $stringArray)) && (in_array('14', $stringArray)) && (in_array('15', $stringArray)) && (in_array('16', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if(($row['CPM_BERKAS_JNS_PEROLEHAN']==6) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==10) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==11) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==12)){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('17', $stringArray)) && (in_array('18', $stringArray)) && (in_array('19', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if(($row['CPM_BERKAS_JNS_PEROLEHAN']==7) || ($row['CPM_BERKAS_JNS_PEROLEHAN']==33)){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('20', $stringArray)) && (in_array('21', $stringArray)) && (in_array('22', $stringArray)) && (in_array('23', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==8){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('24', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}
	else if($row['CPM_BERKAS_JNS_PEROLEHAN']==9){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('25', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==13){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('26', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==14){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('27', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==21){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)) && (in_array('28', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}else if($row['CPM_BERKAS_JNS_PEROLEHAN']==22){
		if((in_array('1', $stringArray)) && (in_array('2', $stringArray)) && (in_array('3', $stringArray)) && (in_array('4', $stringArray)) && (in_array('5', $stringArray)) && (in_array('6', $stringArray)) && (in_array('7', $stringArray)))
		{
			$status="1";
		}else{
			$status="0";
		}	
	}
    
    return $status;    
}

$val = getBerkas();
echo trim($val);
?>