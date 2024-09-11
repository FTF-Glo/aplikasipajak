<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");

$json 		= new Services_JSON();
$response 	= array();

function delDataKejaksaan($arrNomor){
	global $db_host, $db_name, $db_user, $db_pwd;
	
	$GWDBLink = mysqli_connect($db_host,$db_user,$db_pwd,$db_name) or die(mysqli_error($DBLink));
	//mysql_select_db($db_name,$GWDBLink);
	foreach($arrNomor as $nomor){
		$query 	= "DELETE FROM SURAT_PENGANTAR_KEJAKSAAN WHERE SPK_NOMOR = '".$nomor."' ";
		$res 	= mysqli_query($GWDBLink, $query);
	}
	// echo $query;
	return $res;
}

$arrNomor 	= explode(',',$_REQUEST['nomor']);
$db_host 	= $_REQUEST['GW_DBHOST'];
$db_name 	= $_REQUEST['GW_DBNAME'];
$db_user 	= $_REQUEST['GW_DBUSER'];
$db_pwd 	= $_REQUEST['GW_DBPWD'];

$delDataKejaksaan = false;
$delDataKejaksaan = delDataKejaksaan($arrNomor);

// $response['r'] = true;
// $response['errstr'] = $_REQUEST;
$response['delDataKejaksaan'] = $delDataKejaksaan;

$val = $json->encode($response);
echo $val;

?>