<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'core/json.php';
require_once 'obj/pbb.php';
require_once 'obj/configVPOS.php';
require_once 'obj/user.php';
require_once 'obj/log.php';
require_once 'core/db.php';
require_once 'core/api.php';

$api 		= new api();
$message 	= array();
$json 		= new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$user		= $api->getDataUser();

$jsonReq = file_get_contents('php://input');
$pReq = json_decode($jsonReq);

if(!empty($pReq)){
    if(!empty($user)){
        if(cekIP()){ //Cek IP
            if(($pReq->uid == $user['uid']) && ($pReq->pass == $user['pass'])){//Cek User & Password		
                if ($pReq->fn=="getTagihanSPPT"){
                    $data = $api->getTagihanSPPT();
                    if (!empty($data)){
                        $api->addToLog();
                        $message["code"] 	= "0";
                        $message["data"] 	= $data;
                        $message['params'] 	= $pReq;
                    } else {
                        $api->addToLog();
                        $message["code"] 	= "1";
                        $message["message"] = "Data tidak ditemukan.";
                        $message['params'] 	= $pReq;
                    }
                } elseif($pReq->fn=="getDaftarTagihanSPPT"){
                    $data = $api->getDaftarTagihanSPPT();
                    if (!empty($data)){
                        $api->addToLog();
                        $message["code"] 	= "0";
                        $message["data"] 	= $data;
                        $message['params'] 	= $pReq;
                    } else {
                        $api->addToLog();
                        $message["code"] 	= "1";
                        $message["message"] = "Data tidak ditemukan.";
                        $message['params'] 	= $pReq;
                    }                    
                }  elseif($pReq->fn=="getRealisasiSPPT"){
                    $data = $api->getRealisasiSPPT();
                    if (!empty($data)){
                        $api->addToLog();
                        $message["code"] 	= "0";
                        $message["data"] 	= $data;
                        $message['params'] 	= $pReq;
                    } else {
                        $api->addToLog();
                        $message["code"] 	= "1";
                        $message["message"] = "Data tidak ditemukan.";
                        $message['params'] 	= $pReq;
                    }                    
                } 
                else {
                    $api->addToLog();
                    $message["code"] 	= "1";
                    $message["message"] = "Ada kesalahan parameter.";
                    $message['params'] 	= $pReq;
                }

            } else {
                $api->addToLog();
                $message["code"] 	= "2";
                $message["message"] = "Password salah.";
                $message['params'] 	= $pReq;
            }
        } else {
            $api->addToLog();
            $message["code"] 	= "4";
            $message["message"] = "Akses ditolak.";
            $message['params'] 	= $pReq;
        }
    } else {
        $api->addToLog();
        $message["code"] 	= "3";
        $message["message"] = "User tidak terdaftar";
        $message['params'] 	= $pReq;
    }
    
}

function cekIP(){
	global $user,$api;
	
	$conf = $api->getConfig();
	if(isset($conf[0]['CTR_AC_VALUE']) && $conf[0]['CTR_AC_VALUE']=='1'){
		if(strpos($user['ip'], $_SERVER['REMOTE_ADDR']) !== false){
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}
   

header('Content-type: application/json; charset=utf-8');
echo $json->encode($message);
?>
