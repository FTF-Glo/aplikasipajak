<?php
namespace Model;

use \PDO;
use \Config\ConfigDB;
use \Services_JSON;

class PBBModel extends ConfigDB{	 
	private $json;
	
	public function __construct(){
		$this->json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
		$this->getConnGwPBB();
		$this->getConnSwPBB();
	}
    
	public function getProfile($pNOP){
		$result = array('exist' => 0, 'data' => null);
		$query = "
				SELECT
					( SELECT COUNT( NOP ) FROM PBB_SPPT WHERE NOP = '{$pNOP}' ) AS JML_TAHUN_TAGIHAN,
					( SELECT SUM( CASE WHEN PAYMENT_FLAG = 1 THEN 1 ELSE 0 END ) FROM PBB_SPPT WHERE NOP = '{$pNOP}' ) AS JML_TAGIHAN_LUNAS,
					NOP,
					ID_WP,
					WP_NAMA,
					OP_ALAMAT,
					OP_KELURAHAN,
					OP_KECAMATAN,
					OP_LUAS_BUMI,
					OP_LUAS_BANGUNAN,
					OP_NJOP_BUMI,
					OP_NJOP_BANGUNAN,
					OP_KOTAKAB,
					PAYMENT_FLAG 
				FROM
					PBB_SPPT 
				WHERE
					NOP = '{$pNOP}' 
				ORDER BY
					SPPT_TAHUN_PAJAK DESC 
					LIMIT 1";
		
		try {
			$stmt = $this->connGWPBB->prepare($query);
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
			$stmt = $this->connSWPBB->prepare($query);
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
	
	   				
}
?>
