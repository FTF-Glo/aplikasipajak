<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
	if(!empty($_POST['nspop']) && !empty($_POST['nomorV']) && !empty($_POST['tanggalV']) ){
	
			$sql = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_LHP_NUMBER= '".$_POST['nomorV']."', CPM_RE_LHP_DATE = '".$_POST['tanggalV']."'WHERE CPM_RE_SID='".$_POST['nspop']."'";
			$result = mysqli_query($DBLink, $sql);
			if(!$result){
                $respon['respon'] = false;
				$respon['message'] = mysqli_error($DBLink);
			}else{
				$respon['respon'] = true;
				$respon['message'] = "sukses: ".$_REQUEST['spop'];
			}
			echo json_encode($respon);exit;
    }else{
            echo "No Action!";
    }
?>