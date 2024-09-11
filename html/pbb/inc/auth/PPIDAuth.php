<?php

class PPIDAuth extends AuthBase {
	
	// render input
	function element() {
		return array(
			array("label" => "LOGIN", "id" => "mac", "input" => "<input type='hidden' id='mac' name='mac' value='' autocomplete='off'></input>")
		);
	}
	
	// authentication process
	function auth(&$input, &$arResponse) {
		$usr = $input["usr"];
		$pwd = $input["pwd"];		
		$mac = $input["mac"];
		//var_dump($input);
		$mac=explode(",",$mac);
		
		$arResponse = $this->checkPPID($usr, $pwd,$mac);
		//var_dump($arResponse);
		$input["ppid"]=$arResponse["ppid"];
		//var_dump($arResponse);
		if (isset($arResponse["error"])) {
			// have error message, login failed
			return false;
		}
		return true;
	}
	
	// login behaviour for username & password 
	function checkPPID($usr, $pwd,$mac) {
		global $User, $Session;
		$ppid="";
		$sUID = '';
		$aResponse = array();
		$auth = $User->IsAuthUser($usr, $pwd, $sUID);
		//echo "$mac";
		$remoteAddr = $_SERVER['REMOTE_ADDR'];		
		if ($auth) {
				$loginable = true;
				if($User->IsO2wUser($sUID)){
					if(!$User->getUserPPID($sUID,$ppid)){
						$aResponse["error"] = "User '$usr' Tidak Bisa Login<br /> atau Loket $ppid di blok";
						$loginable = false;
					}
				}
				//var_dump($ppid);
				if($ppid!=""){
					if ($loginable) {
						$cid=$User->getCIDFromPPID($ppid);
						$aResponse['ppid'] = $ppid;
						$aResponse['cid'] = $cid;
						$activate="";
						if($User->isPPIDUseActivation($ppid,$activate)){
							if(is_null($activate) || trim($activate)==""){
								//register Mac Address
								$bOK=$User->registerPPIDMAC($ppid,$mac);
								if($bOK)
									$User->ActivatePPID($ppid);
								else
									$aResponse["error"] = "Gagal Mendeteksi Sistem Keamanan, Silahkan Kontak Helpdesk";
							}else{
								//Cek Mac Address
								if($User->isEmptyPPIDMAC($ppid)){
									$bOK=$User->registerPPIDMAC($ppid,$mac);
									if(!$bOK)
										$aResponse["error"] = "Gagal Mendeteksi Sistem Keamanan, Silahkan Kontak Helpdesk";
								}else{
									if(!$User->isValidPPIDMAC($ppid,$mac)){
										$aResponse["error"] = "User $usr Sudah diaktivasi di Komputer Lain";
										
									}
								}
							}
						}
						else{
							if(is_null($activate) || trim($activate)==""){
								$bOK=$User->ActivatePPID($ppid);
							}
						}
					}
				}else{
					$loginable=true;
				}
		} 
		
		return $aResponse;
	}
}

?>
