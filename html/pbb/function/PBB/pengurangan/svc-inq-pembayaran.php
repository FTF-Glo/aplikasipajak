<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
    require_once($sRootPath."inc/payment/json.php");
    
    $C_HOST_PORT = $_REQUEST['C_HOST_PORT'];
    $C_USER = $_REQUEST['C_USER'];
    $C_PWD = $_REQUEST['C_PWD'];
    $C_DB = $_REQUEST['C_DB'];
    
    $json = new Services_JSON();
    
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
    if(!empty($_REQUEST['nspop']) && !empty($_REQUEST['nop']) && !empty($_REQUEST['tahun'])){
        
	$bOK = pembayaran('PAYMENT_FLAG', $_REQUEST['nop'], $_REQUEST['tahun']);
        if($bOK){
            if($bOK['PAYMENT_FLAG'] == '1'){
                $respon['respon'] = '1';
                $respon['message'] = "sukses";
                echo $json->encode($respon);exit;
            }else{
                $respon['respon'] = '0';
                $respon['message'] = "sukses";
                echo $json->encode($respon);exit;
            }
        }else{
            $respon['respon'] = '-1';
            $respon['message'] = mysqli_error($DBLink);
            echo $json->encode($respon);exit;
        }
	echo $json->encode($respon);exit;
    }else{
        $respon['respon'] = -1;
	$respon['message'] = mysqli_error($DBLink);
	echo $json->encode($respon);exit;
    }
   
   function pembayaran($field, $nop, $tahun) {
        global $DBLink, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB;

        $LDBLink = mysqli_connect($C_HOST_PORT,$C_USER,$C_PWD,$C_DB);
        //mysql_select_db($C_DB,$LDBLink);
		
        $nop = mysqli_real_escape_string($LDBLink, trim($nop));
        $field = mysqli_real_escape_string($LDBLink, trim($field));
        $tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
        $query = "SELECT $field FROM PBB_SPPT WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";
		
        $res 	= mysqli_query($LDBLink, $query);
        $row 	= mysqli_fetch_array($res);
        return $row;//[$field];
    } 
?>