<?php
session_start();
if($_SESSION["sessIDPemekaran"]==$_REQUEST['ID']){
	$action = $_REQUEST['action'];
	$kota   = $_SESSION['sessIDPemekaranKodeKota'];
	$kec    = $_SESSION['sessIDPemekaranKodeKec'];
	$start  = $_REQUEST['jtStartIndex'];
	$limit  = $_REQUEST['jtPageSize'];

	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	require_once('queryOpen.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

	if($action=='iNSERT'){
		$sql = "DELETE FROM `cppmod_tax_kelurahan` WHERE (`CPC_TKL_ID`='".$kec.$_REQUEST['kdkel']."')";
		mysqli_query($DBLink, $sql);
		
		$sql = "INSERT INTO `cppmod_tax_kelurahan` (`CPC_TKL_ID`, `CPC_TKL_KCID`, `CPC_TKL_KELURAHAN`, `CPC_TKL_KDSEKTOR`) 
		        VALUES ('".$kec.$_REQUEST['kdkel']."', '$kec', '".$_REQUEST['nmkel']."', '".$_REQUEST['sektor']."' ) ";
		mysqli_query($DBLink, $sql);
	}
	else if($action=='dELETE'){
		$sql = "DELETE FROM `cppmod_tax_kelurahan` WHERE (`CPC_TKL_ID`='".$_REQUEST['kdkel']."')";
		mysqli_query($DBLink, $sql);	
	}
	else {
		$sql    = "SELECT COUNT(*) FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_KCID = '$kec' ";
		$result = mysqli_query($DBLink, $sql);
		$row    = mysqli_fetch_array($result);
		$TotalRecordCount = $row[0];
				 
		$sql  = " 	SELECT A.CPC_TKL_ID, A.CPC_TKL_KELURAHAN, C.CPC_NM_SEKTOR, C.CPC_KD_SEKTOR, B.TAG, B.KEC
					FROM cppmod_tax_kelurahan A LEFT JOIN 
						 (  
							SELECT DISTINCT SUBSTR(ZZ.CPM_NOP,1,10) KEC, '1' TAG 
							FROM cppmod_pbb_generate_nop ZZ 
							  WHERE SUBSTR(ZZ.CPM_NOP,1,7) LIKE '$kec'
						 ) B ON A.CPC_TKL_ID = B.KEC LEFT JOIN
						 cppmod_pbb_jns_sektor C ON A.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
						 
					WHERE A.CPC_TKL_KCID = '$kec' LIMIT $start, $limit ";
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