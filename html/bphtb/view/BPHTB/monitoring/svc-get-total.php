<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

#if($_POST['ajax']!=1){ exit;}

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);

function getConfigValue ($id,$key) {
	global $DBLink;	
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

$a = isset($_POST['a'])?$_POST['a']:'';
if($a=='') exit;
$DbName = getConfigValue($a,'BPHTBDBNAME');
$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
$DbTable = getConfigValue($a,'BPHTBTABLE');
$DbUser = getConfigValue($a,'BPHTBUSERNAME');
SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);

$query = "SELECT SUM(bphtb_dibayar) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1";
$res = mysqli_query($LDBLink, $query);

$json = new Services_JSON();

$ret['success'] = false;
$ret['data']['total_transaksi'] = 0;
$ret['data']['jml_transaksi'] = 0;

if($row=mysqli_fetch_assoc($res)) {
	$ret['success'] = true;
	$ret['data']['total_transaksi'] = number_format($row['total_transaksi']);
	$ret['data']['jml_transaksi'] = $row['jml_transaksi'];
	echo $json->encode($ret);
}

?>
