<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_khusus_2016', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/uuid.php");
	require_once($sRootPath."inc/central/user-central.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$appConfig 	= $User->GetAppConfig('aPBB');
	$dbhost 	= $appConfig['GW_DBHOST'];
	$dbuser 	= $appConfig['GW_DBUSER'];
	$dbpwd 		= $appConfig['GW_DBPWD'];
	$dbname 	= $appConfig['GW_DBNAME'];
	//akses database gateway devel
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost,$dbuser,$dbpwd,$dbname);
	
	$nop 				= $_POST['nop'];
	$statusPersetujuan 	= $_POST['statusPersetujuan'];
	$respon				= array();
	
    if(!empty($nop) && !empty($statusPersetujuan)){
		
		
		$sql2  = "UPDATE PBB_SPPT_PENAGIHAN_KHUSUS SET STATUS_PERSETUJUAN = $statusPersetujuan WHERE NOP = $nop";
		$res2  = mysqli_query($DBLinkLookUp, $sql2);
		
		if( $res2){
			$respon['respon'] 		= true;
			$respon['message'] 		= 'success';
			echo json_encode($respon);exit;
		} else {
			echo $sql2;
			echo "Terjadi kesalahan query";
		}
    }else{
        echo "Missing nop parameter!";
    }

?>