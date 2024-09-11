<?php
	
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'loket', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/c8583.php");
    require_once($sRootPath."inc/payment/comm-central.php");
    require_once($sRootPath."inc/payment/json.php");
	
	require_once($sRootPath . "inc/payment/ctools.php");
	require_once($sRootPath . "inc/payment/db-payment.php");
	require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
	require_once($sRootPath . "inc/central/dbspec-central.php");
	
    if(!empty($_POST['arrSvcId'])){
        SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
		$arrSvcId = explode(",", $_POST['arrSvcId']);
		
		if($_POST['task'] == 'delete'){
	        for($index=0; $index<count($arrSvcId); $index++){
	            $sql1 = "DELETE FROM cppmod_pbb_services WHERE CPM_ID='".$arrSvcId[$index]."'";
				
	            $result1 = mysqli_query($DBLink, $sql1);            
	            
	            if(!$result1){
	                echo mysqli_error($DBLink);
	                exit(1);
	            }
	        }
	    }else if($_POST['task'] == 'send'){
                for($index=0; $index<count($arrSvcId); $index++){
	            $sql1 = "SELECT CPM_TYPE FROM cppmod_pbb_services WHERE CPM_ID='".$arrSvcId[$index]."'";
                    
                    $result1 = mysqli_query($DBLink, $sql1);        
	            
	            if(!$result1){
	                echo mysqli_error($DBLink);
	                exit(1);
	            }
                    
                    while($row = mysqli_fetch_assoc($result1)) {
                        if($row['CPM_TYPE'] == '7')
                            $sql1 = "UPDATE cppmod_pbb_services SET CPM_STATUS=3 WHERE CPM_ID='".$arrSvcId[$index]."'";
                        else $sql1 = "UPDATE cppmod_pbb_services SET CPM_STATUS=1 WHERE CPM_ID='".$arrSvcId[$index]."'";

                        $result1 = mysqli_query($DBLink, $sql1);        

                        if(!$result1){
                            echo mysqli_error($DBLink);
                            exit(1);
                        }
                    }
                    
	        }
	    }else if($_POST['task'] == 'return'){
                for($index=0; $index<count($arrSvcId); $index++){
	            $sql1 = "SELECT CPM_TYPE FROM cppmod_pbb_services WHERE CPM_ID='".$arrSvcId[$index]."'";
                    
                    $result1 = mysqli_query($DBLink, $sql1);        
	            
	            if(!$result1){
	                echo mysqli_error($DBLink);
	                exit(1);
	            }
                    
                    while($row = mysqli_fetch_assoc($result1)) {
                        $sql1 = "UPDATE cppmod_pbb_services SET CPM_STATUS=0 WHERE CPM_ID='".$arrSvcId[$index]."'";

                        $result1 = mysqli_query($DBLink, $sql1);        

                        if(!$result1){
                            echo mysqli_error($DBLink);
                            exit(1);
                        }
                    }
                    
	        }
	    }else{
	        echo "No Action!";
	    }
    }else{
        echo "No Action!";
    }
?>