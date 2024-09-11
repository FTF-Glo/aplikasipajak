<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'splitNOP', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);


function number_pad($number,$n) {
	return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}

/*function newNOP($nop) {
	global $DBLink;
	
	$qry = "INSERT INTO cppmod_ssb_nop_split (CPM_SSB_SP_NOP, CPM_SSB_SP_NO) VALUES ('".$nop."','1')";
	
	$result = mysqli_query($qry,$DBLink);
}

function updateCounter($nop) {
	global $DBLink;
	
	$qry = "UPDATE cppmod_ssb_nop_split SET CPM_SSB_SP_NO = CPM_SSB_SP_NO + 1 WHERE CPM_SSB_SP_NOP ='".$nop."'";
	
	$result = mysqli_query($qry,$DBLink);
}

function getNOPSplit($nop,&$snop) {
	global $DBLink;
	$nop = str_replace("_", "", $nop);

	if (strlen($nop) == 18) {
		$qry = "SELECT * FROM cppmod_ssb_nop_split WHERE CPM_SSB_SP_NOP ='".$nop."'";
	
		$result = mysqli_query($qry,$DBLink);
	
		while ($row = mysqli_fetch_assoc($result)) {
			updateCounter($row["CPM_SSB_SP_NOP"]);
			$snop = number_pad($row["CPM_SSB_SP_NO"],3);
			return true;	
		};
		newNOP($nop);
		$snop = "001";
		return true;
	}
	return false;
}*/
// function updateCounter($nop) {
	// global $DBLink;
	
	// $qry = "INSERT INTO cppmod_ssb_nop_split (CPM_SSB_SP_NOP, CPM_SSB_SP_NO) VALUES ('".$nop."','1')";
	
	// $result = mysqli_query($qry,$DBLink);
// }

function getNOPSplit($nop,&$snop) {
	global $DBLink;
	$nop = str_replace("_", "", $nop);

	if (strlen($nop) == 18) {
		$qry = "SELECT * FROM cppmod_ssb_nop_split WHERE CPM_SSB_SP_NOP ='".$nop."'";
		//echo $qry;exit();
		$result = mysqli_query($DBLink, $qry);
		if(mysqli_num_rows($result)>0){
			while ($row = mysqli_fetch_assoc($result)) {
				//updateCounter($nop);
				$snop = number_pad($row["CPM_SSB_SP_NO"]+1,3);
				return true;	
			}
		}else{
			//updateCounter($nop);
			$snop = "001";
			return true;
		}
		
		//updateCounter($nop);
		
		//return true;
	}
	//return false;
}

$Response = array();
$Response["success"] = false;
$Response["nopsplit"] = ""; 
$oRequest = "";

//if(isset($_POST["req"])){ 

	$req = base64_decode($_REQUEST["req"]);
	$oRequest = $json->decode($req);

	if(getNOPSplit($oRequest->n,$snop)) {
		$Response['found'] = true;
		$Response["success"] = true;
		$Response["nopsplit"] = $snop; 
	} else {
		$Response["message"] = "NOP kurang dari 18 karakter !";
	}
	
//}

echo $json->encode($Response);

?>