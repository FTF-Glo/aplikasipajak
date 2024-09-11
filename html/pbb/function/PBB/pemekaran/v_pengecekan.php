<?php
    //session_start();
	$sessID = md5("pEmEKarAN".date('YmdHis'));
	$_SESSION["sessIDPemekaran"] = $sessID;
	
    $action      = $_REQUEST['action'];
	$appConfig   = $User->GetAppConfig($application);
	$kdKota      = $appConfig["KODE_KOTA"];
	
	$_SESSION["sessIDPemekaranKodeKota"] = $kdKota;
	
	echo '<script src="jtable/jquery.min.js" type="text/javascript"></script>';
	
	
	if($action=='form'){
		include('v_pengecekan_form.php');
	} else {
		include('v_pengecekan_list.php');
	}
?>

