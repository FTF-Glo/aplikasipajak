<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/uuid.php");
	
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_POST['dbhost'],$_POST['dbuser'],$_POST['dbpwd'],$_POST['dbname']);
	
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
		
	// print_r($_POST);exit;
	
    if(!empty($nop) && !empty($keterangan) && !empty($nop) && !empty($_POST['dbhost']) && !empty($_POST['dbuser']) && !empty($_POST['dbpwd']) && !empty($_POST['dbname'])){
		
		$sql1  = "UPDATE PBB_SPPT_PENAGIHAN SET STATUS_SP = '$statsp1', $fieldKeterangan = '$keterangan' WHERE NOP = '$nop'";
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