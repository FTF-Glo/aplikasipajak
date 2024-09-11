<?php
namespace Core;

use \Services_JSON;

Class Request{
	public static function data($id=""){
		return empty($id)? (object) $_REQUEST : (isset($_REQUEST[$id])?$_REQUEST[$id]:null);
	}
	
	public static function json($id=""){
		$tJson = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);		
		$req = file_get_contents('php://input');
		$res = $tJson->decode($req);
		
		return empty($id)? $res : (isset($res->$id)?$res->$id:null);
	}
	
	public static function action(){
		$action = explode(".",$_POST['action']);
		if(count($action)!=2){
			exit('{"Result":"ERROR","Message":"Action is not valid!"}');
		}
		return $action[1];
	}
	
	public static function type($type=''){
		return empty($type)? $_SERVER['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'] === strtoupper($type);
	}
	
}
	
?>
