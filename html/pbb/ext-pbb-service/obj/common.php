<?php
Class Common{
	static public $app = 'aPBB';		
	static public $stsProcess = array(
		1 => "OP Baru",
		2 => "Pemecahan",
		3 => "Penggabungan",
		4 => "Mutasi",
		5 => "Perubahan Data",
		6 => "Pembatalan",
		7 => "Salinan",
		8 => "Penghapusan",
		9 => "Pengurangan",
		10 => "Keberatan"
	);
	static public function GetModuleConfig($pConn,$moduleId) {	
		$data = new \stdClass;
		$query = "select * from central_module_config where CTR_CFG_MID = '" . $moduleId . "' " . 
				"order by CTR_CFG_MKEY asc";
		try {
			$stmt = $pConn->prepare($query);
			$stmt->execute();					
			foreach($stmt->fetchAll(PDO::FETCH_OBJ) as $dt){
				$data->{$dt->CTR_CFG_MKEY} = $dt->CTR_CFG_MVALUE;
			}
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	static public function getLastNumber($pConn,$suffix){
		$data = new \stdClass;
		$query = "select max(CPM_NO) as CPM_NO from cppmod_pbb_generate_service_number where CPM_ID like '%{$suffix}'";
		try {
			$stmt = $pConn->prepare($query);
			$stmt->execute();					
			$data = $stmt->fetch(PDO::FETCH_OBJ);
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	static public function generateNumber($pConn,$arConfig,$jnsBerkas){
		$numberSuffix = array();		
		$numberSuffix[1] = $arConfig->FORMAT_NOMOR_BERKAS_OPBARU;
		$numberSuffix[2] = $arConfig->FORMAT_NOMOR_BERKAS_PEMECAHAN;
		$numberSuffix[3] = $arConfig->FORMAT_NOMOR_BERKAS_PENGGABUNGAN;
		$numberSuffix[4] = $arConfig->FORMAT_NOMOR_BERKAS_MUTASI;
		$numberSuffix[5] = $arConfig->FORMAT_NOMOR_BERKAS_PERUBAHAN;
		$numberSuffix[6] = $arConfig->FORMAT_NOMOR_BERKAS_PEMBATALAN;
		$numberSuffix[7] = $arConfig->FORMAT_NOMOR_BERKAS_SALINAN;
		$numberSuffix[8] = $arConfig->FORMAT_NOMOR_BERKAS_PENGHAPUSAN;
		$numberSuffix[9] = $arConfig->FORMAT_NOMOR_BERKAS_PENGURANGAN;
		$numberSuffix[10] = $arConfig->FORMAT_NOMOR_BERKAS_KEBERATAN;
		$numberSuffix[11] = $arConfig->FORMAT_NOMOR_BERKAS_SKNJOP;
		$lastNumber = common::getLastNumber($pConn,$numberSuffix[$jnsBerkas]);
		$newNumber = $lastNumber->CPM_NO+1;
		return $newNumber.$numberSuffix[$jnsBerkas];
	}
	static public function updCtrSrvNum($pConn,$pData){
		$query = "INSERT INTO cppmod_pbb_generate_service_number (CPM_ID, CPM_NO, CPM_CREATOR, CPM_DATE_CREATED) VALUES ('{$pData->id}', '{$pData->num}','{$pData->uname}', '{$pData->tgl_masuk}')";
		try {
			$stmt = $pConn->prepare($query);
			 return $stmt->execute();
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
			return false;
		}
	}
	static public function delCtrSrvNum($pConn,$pNum){
		$query = "delete from cppmod_pbb_generate_service_number CPM_ID='{$pNum}'";
		try {
			$stmt = $pConn->prepare($query);
			 return $stmt->execute();
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
			return false;
		}
	}
}
?>
