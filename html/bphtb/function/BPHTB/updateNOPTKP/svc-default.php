<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'updateNOPTKP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);


function getConfigValue ($key) {
	global $DBLink,$idapp;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '".$idapp."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function searchTrsExpGateway($idsw,$dt) {
	$dbName = getConfigValue('BPHTBDBNAME');
	$dbHost = getConfigValue('BPHTBHOSTPORT');
	$dbPwd = getConfigValue('BPHTBPASSWORD');
	$dbTable = getConfigValue('BPHTBTABLE');
	$dbUser = getConfigValue('BPHTBUSERNAME');
	// Connect to lookup database
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	$qry = "SELECT wp_noktp, op_nomor, if ((date(expired_date) < date('{$dt}')),1,0) AS EXPRIRE 
			  FROM ssb WHERE payment_flag = 0 and id_switching = '{$idsw}'";
	$res = mysqli_query($DBLinkLookUp, $qry);
	if ( $res === false ){
		print_r(mysqli_error($DBLinkLookUp)); 
		return 0;
	}
	$row = mysqli_fetch_assoc($res);
	if ($row['EXPRIRE'] == "1") {
		return true;
	}
	return false;		 
}

function getNOP ($noktp,$date) {
	global $DBLink;

	$N1= getConfigValue('NPOPTKP_STANDAR');
	$N2= getConfigValue('NPOPTKP_WARIS');
	$day = getConfigValue("BATAS_HARI_NPOPTKP");
	$dbLimit = getConfigValue('TENGGAT_WAKTU');
 	
	$qry = "SELECT *  FROM cppmod_ssb_doc WHERE CPM_WP_NOKTP ='{$noktp}' AND CPM_SSB_CREATED < '{$date}' ORDER BY CPM_SSB_CREATED DESC";

//print_r($qry); 
	$res = mysqli_query($DBLink, $qry);
	$lp = getConfigValue('LIMIT_PROGRESIF');
	$ak = 0;
	if (mysqli_num_rows ($res)) {
		while ($row = mysqli_fetch_assoc($res)) {
			if (!searchTrsExpGateway($row["CPM_SSB_ID"],$date)) {
				$ak += $row['CPM_SSB_AKUMULASI'];
				if ($ak >= $lp) return 0 ;
			}
		};
	}
	
	return  getConfigValue('NPOPTKP_STANDAR');
}

$Response = array();
$Response["success"] = false;
$Response["found"] = false; 
$oRequest = "";
//print_r($_REQUEST);
if(@isset($_REQUEST["swt"])){ 
	/*$req = $_REQUEST["req"];
	$oRequest = $json->decode(base64_decode($req));
	$idapp = base64_decode($oRequest->axx);

	if(getNOKTP ($oRequest->noktp,$oRequest->n)) {
		$Response['found'] = true;
	}
	$Response["success"] = true;*/
	$nop = $_REQUEST["nop"]; 
	$noktp = $_REQUEST["noktp"];
	$swt = $_REQUEST["swt"];
	$dt = $_REQUEST["dt"];
	$idapp =  $_REQUEST["ap"];
	$Response['noptkp']=getNOP($noktp,$dt);
	
	echo $json->encode($Response);
}


?>
