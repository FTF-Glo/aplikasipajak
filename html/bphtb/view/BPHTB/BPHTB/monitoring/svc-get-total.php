<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

function getConfigValue ($id,$key) {
	
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}
		 
		 
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

$a = $_REQUEST['a'];

$DbName = getConfigValue($a,'BPHTBDBNAME');
$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
$DbTable = getConfigValue($a,'BPHTBTABLE');
$DbUser = getConfigValue($a,'BPHTBUSERNAME');

SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

$query = "SELECT SUM(bphtb_dibayar) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1";

$res = mysqli_query($LDBLink, $query);
if ( $res === false ){
	print_r(mysqli_error($LDBLink));
	return "Tidak Ditemukan"; 
}

$json = new Services_JSON();

while ($row=mysqli_fetch_array($res)) {
	$ret['success'] = true;
	$ret['data']['total_transaksi'] = $row['total_transaksi'];
	$ret['data']['jml_transaksi'] = $row['jml_transaksi'];
	echo $json->encode($ret);
}

?>
