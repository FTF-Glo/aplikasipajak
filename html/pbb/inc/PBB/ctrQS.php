<?php  
//if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");

class centralQS{
	private $DSQuery = NULL;	
	
        private $ServerAddress;
        private $ServerPort;
        private $ServerTimeOut;
        private $json;
        private $LoadedKey;
	public function __construct($ServerAddress, $ServerPort, $ServerTimeOut, $LoadedKey , $json){
		$this->ServerAddress = $ServerAddress;
		$this->ServerPort = $ServerPort;
		$this->ServerTimeOut = $ServerTimeOut;
		$this->json = $json;
		$this->LoadedKey = $LoadedKey;
	}
	
	public function SqlExec($arData, $fName, &$arResult) {
		$arResult = null;
		$bOk = false;				
                $send = array();
                
		foreach($arData as $key => $value){
			$send[$key] = addslashes($value);			
		}		
                $req = array();
                $req['PAN'] = '11000';
                $req['f'] = $fName;
                $req['i'] = $send;
                $req['LOADEDKEY']= $this->LoadedKey;
                $req['UID']= '000000000175';
                $sResp = "";
                $sRequestStream = $this->json->encode($req); 
                $bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sRequestStream, $sResp);
                $sResp = rtrim($sResp, END_OF_MSG);
                if ($bOK == 0) {
                    $data = $this->json->decode($sResp);
                    if($data->RC=="0000"){											
                            if(!isset($data->o[0]->EFFECTED_ROWS) OR ($data->o[0]->EFFECTED_ROWS==null)){
                                    $bOk=true;
                                    $o = $this->json->decode($data->o);
                                    $nRes=count($o); 
                                    for ($i=0;$i<$nRes;$i++){
                                            foreach($o[$i] as $key => $value){
                                                    $arResult[$i][$key] = $value;
                                            }
                                    }
                            }else{				
                                    if($data->o[0]->EFFECTED_ROWS==0){		
                                            $bOk=false;
                                            //$arResult['F']=$fname;
                                            $arResult['RC']="1000";
                                            $arResult['E']="No Effected Row";								
                                    }else{			
                                            $bOk=true;			
                                            //$arResult['F']=$fname;
                                            $arResult['RC']="0000";
                                            $arResult['E']="Success";										
                                    }				
                            }				
                    }else{
                            //$arResult['F']=$fname;
                            $arResult['RC']=$data->RC;
                            $arResult['E']=$data->E;
                    }
                }		
		return $bOk;
	}	
}

?>