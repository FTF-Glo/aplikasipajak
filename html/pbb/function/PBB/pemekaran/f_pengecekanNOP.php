<?php
session_start();
if($_SESSION["sessIDPemekaran"]==$_REQUEST['ID']){
	$action = $_REQUEST['action'];
	$kota   = $_SESSION['sessIDPemekaranKodeKota'];
	$start  = $_REQUEST['jtStartIndex'];
	$limit  = $_REQUEST['jtPageSize'];

	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
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
		$sql    = "SELECT COUNT(*) FROM `cppmod_pbb_perubahan_nop` WHERE STATUS = '1'";
		$result = mysqli_query($DBLink, $sql);
		$row    = mysqli_fetch_array($result);
		$TotalRecordCount = $row[0];
				 
		$sql  = "SELECT 
		IF(A.JENIS='1', 'Pindah kelurahan keseluruhan ke kecamatan', 
		IF(A.JENIS='2','Pindah blok keseluruhan ke kelurahan lain',
		IF(A.JENIS='3','Gabung Beberapa Blok','Pindah NOP Ke Blok Lain'))) AS JENIS,
		A.NOP_LAMA, 
		A.NOP_BARU,  
		(SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = LEFT(A.NOP_LAMA,7)) AS KECAMATAN_LAMA,  
		(SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = LEFT(A.NOP_LAMA,10)) AS KELURAHAN_LAMA,
		(SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = LEFT(A.NOP_BARU,7)) AS KECAMATAN_BARU,  
		(SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = LEFT(A.NOP_BARU,10)) AS KELURAHAN_BARU,
		A.TGL_UPDATE
FROM cppmod_pbb_perubahan_nop A WHERE STATUS = '1' LIMIT $start, $limit";
		$data = queryOpen($DBLink, $sql);
		
		// foreach ($data['JENIS'] as $d) {
		// 	if ($d == "1") {
		// 		# code...
		// 	}
		// }
		echo json_encode(
				array('Result' => 'OK', 
					  'Records' => $data, 
					  'TotalRecordCount' => $TotalRecordCount)
			 );
	}
} 
else  echo "DENIED";
?>