<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemekaran', '', dirname(__FILE__))) . '/';
if($_SESSION["sessIDPemekaran"]==$_REQUEST['ID']){
	$action = $_REQUEST['action'];
	$kota   = $_SESSION['sessIDPemekaranKodeKota'];
	$start  = $_REQUEST['jtStartIndex'];
	$limit  = $_REQUEST['jtPageSize'];

	require_once( $sRootPath .'inc/payment/inc-payment-db-c.php');
	require_once( $sRootPath .'inc/payment/db-payment.php');
	require_once('queryOpen.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

	if($action=='iNSERT'){
		$sql = "DELETE FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '".$kota.$_REQUEST['kdkec']."'";
		mysqli_query($DBLink, $sql);
		
		$sql = "INSERT INTO `cppmod_tax_kecamatan` (`CPC_TKC_ID`, `CPC_TKC_KKID`, `CPC_TKC_KECAMATAN`, `CPC_TKC_URUTAN`) 
				VALUES ('".$kota.$_REQUEST['kdkec']."', '".$kota."', '".$_REQUEST['nmkec']."', NULL)";
		mysqli_query($DBLink, $sql);
	}
	else if($action=='dELETE'){
		$sql = "DELETE FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '".$_REQUEST['kdkec']."'";
		mysqli_query($DBLink, $sql);	
	}
	else {
		$sql    = "SELECT COUNT(*) FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_KKID = '$kota' ";
		$result = mysqli_query($DBLink, $sql);
		$row    = mysqli_fetch_array($result);
		$TotalRecordCount = $row[0];
				 
		$sql  = "SELECT A.CPC_TKC_ID, A.CPC_TKC_KECAMATAN, B.TAG
				 FROM cppmod_tax_kecamatan A LEFT JOIN 
					 (SELECT DISTINCT SUBSTR(ZZ.CPM_NOP,1,7) KEC, '1' TAG FROM cppmod_pbb_generate_nop ZZ 
					  WHERE SUBSTR(ZZ.CPM_NOP,1,4) = '$kota') B ON A.CPC_TKC_ID = B.KEC
				 WHERE A.CPC_TKC_KKID = '$kota' LIMIT $start, $limit";
		$data = queryOpen($DBLink, $sql);
		echo json_encode(
				array('Result' => 'OK', 
					  'Records' => $data, 
					  'TotalRecordCount' => $TotalRecordCount)
			 );
	}
} 
else  echo "DENIED";
?>