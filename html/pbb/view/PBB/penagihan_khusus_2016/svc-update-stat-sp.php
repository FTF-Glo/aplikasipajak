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
		
	$nop 			= $_POST['nop'];
	$statsp1		= $_POST['statsp1'];
	$keterangan		= $_POST['keterangan'];
	$sts			= $_POST['sts'];
	if($sts==6)
		$fieldKeterangan = "KETERANGAN_SP1";
	else if($sts==7)
		$fieldKeterangan = "KETERANGAN_SP2";
	else if($sts==8)
		$fieldKeterangan = "KETERANGAN_SP3";
	
    if(!empty($nop) && !empty($keterangan) && !empty($nop)){
		
		$sql1  = "UPDATE PBB_SPPT_PENAGIHAN_KHUSUS SET STATUS_SP = '$statsp1', $fieldKeterangan = '$keterangan' WHERE NOP = '$nop'";
		$res1  = mysqli_query($DBLinkLookUp, $sql1);
		
		if($res1){
			$respon['respon'] 		= true;
			$respon['message'] 		= 'success';
			// $respon['q']			= $sql1;
			echo json_encode($respon);exit;
		} else {
			echo $sql1;
			echo "Terjadi kesalahan query";
		}
    }else{
        echo "Missing nop parameter!";
    }

?>