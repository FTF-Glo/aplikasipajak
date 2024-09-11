<?php
namespace Model;

use \PDO;
use \Config\ConfigDB;
use \Services_JSON;

class BPHTBModel extends ConfigDB{	 
	private $json;
	
	public function __construct(){
		$this->json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
		$this->getConnGwBPHTB();
		$this->getConnSwBPHTB();
	}
    
	public function getSwitchingID($pTRX_ID){
		$result = array('exist' => 0, 'data' => null);
		$query = "
				SELECT 
					id_switching AS ID
				FROM ssb
				WHERE
				id_ssb = '{$pTRX_ID}'";
		
		try {
			$stmt = $this->connGW->prepare($query);
			$stmt->execute();		
			if($stmt->rowCount() > 0){
				$result['exist'] = $stmt->rowCount();
				$result['data'] = $stmt->fetch(PDO::FETCH_OBJ);								
			}			
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $this->json->encode($result);
	}      			

	public function getProfile($pNOP,$pSwitchingID){
		$result = array('exist' => 0, 'data' => null);
		$query = "
				SELECT 
					wp_noktp AS NIK, 
					wp_nama AS NAMA, 
					op_letak AS ALAMAT, 
					op_kelurahan AS KELURAHAN_OP,
					op_kecamatan AS KECAMATAN_OP,
					op_kabupaten AS KOTA_OP,
					payment_flag AS FLAG,
					bphtb_dibayar AS PEMBAYARAN,
					DATE_FORMAT(payment_paid,'%d%m%Y') AS TANGGAL_PEMBAYARAN
				FROM ssb
				WHERE
				op_nomor LIKE '{$pNOP}%' AND
				id_switching = '{$pSwitchingID}'";
		
		try {
			$stmt = $this->connGW->prepare($query);
			$stmt->execute();		
			if($stmt->rowCount() > 0){
				$result['exist'] = $stmt->rowCount();
				$result['data'] = $stmt->fetch(PDO::FETCH_OBJ);								
			}			
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $this->json->encode($result);
	}      			

	public function getDataOP($pSwitchingID){
		$result = array('exist' => 0, 'data' => null);
		$query = "
				SELECT 
					CPM_OP_LUAS_TANAH AS LUASTANAH,
					CPM_OP_LUAS_BANGUN AS LUASBANGUNAN
				FROM CPPMOD_SSB_DOC
				WHERE
				CPM_SSB_ID = '{$pSwitchingID}'";
		
		try {
			$stmt = $this->connSW->prepare($query);
			$stmt->execute();		
			if($stmt->rowCount() > 0){
				$result['exist'] = $stmt->rowCount();
				$result['data'] = $stmt->fetch(PDO::FETCH_OBJ);								
			}			
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $this->json->encode($result);
	}      			
	
	public function getValidate($pSwitchingID){
		$result = array('exist' => 0, 'data' => null);
		$query = "
				SELECT 
					MAX(CPM_TRAN_STATUS) AS STATUS
				FROM CPPMOD_SSB_TRANMAIN
				WHERE
				CPM_TRAN_SSB_ID = '{$pSwitchingID}'";
		
		try {
			$stmt = $this->connSW->prepare($query);
			$stmt->execute();		
			if($stmt->rowCount() > 0){
				$result['exist'] = $stmt->rowCount();
				$result['data'] = $stmt->fetch(PDO::FETCH_OBJ);								
			}			
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $this->json->encode($result);
	}      			
	
	public function insertDataBPN($fields, $values){
		$query = "INSERT INTO `TBL_BPN` ";
		$query.= "(".implode(',',$fields).") VALUES";
		$query.= "('".implode("','",$values)."')";
		
		
		$result = false;
		try {
			$stmt	= $this->connSW->prepare($query);
			$result = $stmt->execute();		
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $result;
	}      			

	public function insertPPAT($fields, $values){
		$query = "INSERT INTO `TBL_PPAT_BPN` ";
		$query.= "(".implode(',',$fields).") VALUES";
		$query.= "('".implode("','",$values)."')";
		
		$result = false;
		try {
			$stmt	= $this->connSW->prepare($query);
			$result = $stmt->execute();		
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $result;
	}      				
}
?>
