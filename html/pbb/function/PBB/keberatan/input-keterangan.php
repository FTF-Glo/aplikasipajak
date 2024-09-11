<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'keberatan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
    
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
    if(!empty($_REQUEST['nspop']) && !empty($_REQUEST['ket'])){
        
        $bOK = execute();
        if(!$bOK){
                $respon['respon'] = false;
				$respon['message'] = mysqli_error($DBLink);
        }else{
				$respon['respon'] = true;
                $respon['message'] = "sukses";
				
        }
		echo json_encode($respon);exit;
    }else{
            echo "No Action!";
    }
        
   function execute(){
        global $DBLink;

        $id 		= $_REQUEST['nspop'];
        
        #Update No SK dan Tanggal SK
        $sql = "UPDATE cppmod_pbb_service_objection SET CPM_OB_NOTICE = '".$_REQUEST['ket']."' WHERE CPM_OB_SID='".$_REQUEST['nspop']."'";
        //echo $sql;
		$bOK = mysqli_query($DBLink, $sql);
        if(!$bOK) return false;
        
        return true;
   }
?>