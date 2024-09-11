<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'core/json.php';
require_once 'obj/common.php';
require_once 'obj/pbb.php';
require_once 'obj/configVPOS.php';
require_once 'obj/user.php';
require_once 'obj/log.php';
require_once 'core/db.php';
require_once 'core/api.php';

$jsonReq = file_get_contents('php://input');
$pReq = json_decode($jsonReq);

$api 		= new api($pReq);
$message 	= array();
$json 		= new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if(!empty($pReq)){
	if(method_exists($api,$pReq->fn)){ //cek method exists
		$data = $api->{$pReq->fn}(&$message);
		if (!empty($data)){
			$api->addToLog();
		} else {
			$api->addToLog();
		}						
	}
	else {
		$api->addToLog();
		$message["rc"] 	= "4";
		$message['result'] = null;
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
