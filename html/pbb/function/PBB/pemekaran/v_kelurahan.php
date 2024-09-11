<?php
	$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemekaran', '', dirname(__FILE__))) . '/';
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

	require_once( $sRootPath .'/inc/payment/inc-payment-db-c.php');
	require_once( $sRootPath .'/inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    //session_start();
	$sessID = md5("pEmEKarAN".date('YmdHis'));
	$_SESSION["sessIDPemekaran"] = $sessID;
	
    $action      = $_REQUEST['action'];
	$appConfig   = $User->GetAppConfig($application);
	$kdKota      = $appConfig["KODE_KOTA"]; $_SESSION["sessIDPemekaranKodeKota"] = $kdKota;
    
	
	if(($action!='form') && ($_REQUEST['kdkec']!='')){ 
		$kdKec = $_REQUEST['kdkec'];
		$nmKec = $_REQUEST['nmkec'];
		$_SESSION["sessIDPemekaranKodeKec"]  = $kdKec;
		$_SESSION["sessNMPemekaranKodeKec"]  = $nmKec;
	}
	else { 
		$kdKec = $_SESSION["sessIDPemekaranKodeKec"];
		$nmKec = $_SESSION["sessNMPemekaranKodeKec"];
	}
	
	
	echo '<script src="jtable/jquery.min.js" type="text/javascript"></script>';
	
	// LOAD KECAMATAN
	$arrKec  = " var arrKec = new Array(); ";
	$sql     = " SELECT CPC_TKC_ID `kode`, CPC_TKC_KECAMATAN `nama` 
				 FROM cppmod_tax_kecamatan 
				 WHERE CPC_TKC_KKID = '$kdKota' ORDER BY nama ";
	$result  = mysqli_query($DBLink, $sql);
	while($row = mysqli_fetch_array($result)){
		$arrKec .= " arrKec.push({ kode : '".$row['kode']."', nama: '".$row['nama']."'});";
	}
	
	// LOAD SEKTOR
	$arSektor = " var arrSektor = new Array(); ";
	$sql      = " SELECT * FROM `cppmod_pbb_jns_sektor` ";
	$result   = mysqli_query($DBLink, $sql);
	while($row = mysqli_fetch_array($result)){
		$arSektor .= " arrSektor.push({ kode : '".$row['CPC_KD_SEKTOR']."', nama: '".$row['CPC_NM_SEKTOR']."'});";
	}
	
	echo "<script>$arSektor $arrKec</script> ";
	//echo $arSektor  . $arrKec;
	
	if($action=='form') include('v_kelurahan_form.php');
	else include('v_kelurahan_list.php');
?>

