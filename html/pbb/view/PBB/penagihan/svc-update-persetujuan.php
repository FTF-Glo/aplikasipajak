<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/uuid.php");

	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_POST['dbhost'],$_POST['dbuser'],$_POST['dbpwd'],$_POST['dbname']);
	
	$nop 		= $_POST['nop'];
	$statusPersetujuan = $_POST['statusPersetujuan'];
	$respon		= array();
	
    if(!empty($nop) && !empty($statusPersetujuan) && !empty($_POST['dbhost']) && !empty($_POST['dbuser']) && !empty($_POST['dbpwd']) && !empty($_POST['dbname'])){
		
		
		$sql2  = "UPDATE PBB_SPPT_PENAGIHAN SET STATUS_PERSETUJUAN = $statusPersetujuan WHERE NOP = $nop";
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