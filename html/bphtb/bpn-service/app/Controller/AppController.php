<?php
namespace Controller;

use \Core\Helper;
use \Core\Request;
use \Model\BPHTBModel;
use \Model\PBBModel;
use \Services_JSON;

define("PBB_CONN", "- SET - t-t - from PBB-- and -SISMI-o get data from SISMIOP

class AppController{
	private $json;
	private $bphtbModel;

    public function __construct(){		
        $this->json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
        $this->bphtbModel = new BPHTBModel;
        $this->pbbModel = new PBBModel; 
    }
    
    public function index(){
		echo 'BPHTB & PBB Services';
	}
	
    public function inqueryBPHTB(){	
		$params = Request::json();		 
		$result = $this->getProfile($params);
		Helper::echoResponse($this->json->encode($result));
	}
	
    public function inquiryPBB(){	
		$params = Request::json();		 
		if (PBB_CONN=="-
			$result = $this->getInquiryPBB($params);
		}else{
			$result = $this->getInquiryPBBSismiop($params);
		}
		
		Helper::echoResponse($this->json->encode($result));
	}
	
	public function getProfile($pJson){
		$result = array('respon_code' => 'NOP yang diinputkan salah/tidak sesuai');
		$result1 = array('respon_code' => 'NOP yang diinputkan salah/tidak sesuai');
		$result2 = array('respon_code' => 'NTPD yang diinputkan salah/tidak sesuai');


		// validasi nop & kode bayar
		if(!$this->isValidNOP($pJson->NOP) && !$this->isValidNTPD($pJson->NTPD)){
			return array('respon_code' => 'Data yang diinputkan salah/tidak sesuai karena keduanya salah');
			exit();
		}

		// validasi nop
		if(!$this->isValidNOP($pJson->NOP)){
			return $result1;
			exit();
		}

		// validasi nop
		if(!$this->isValidNTPD($pJson->NTPD)){
			return $result2;
			exit();
		}

		$tSwitching = $this->json->decode($this->bphtbModel->getSwitchingID($pJson->NTPD));		
		if($tSwitching->exist){
			$tValidate = $this->json->decode($this->bphtbModel->getValidate($tSwitching->data->ID));		
			if($tValidate->exist > 0){			
				$tProfile = $this->json->decode($this->bphtbModel->getProfile($pJson->NOP, $tSwitching->data->ID));			
				$tObjekPajak = $this->json->decode($this->bphtbModel->getDataOP($tSwitching->data->ID));			
				
				$tmp = array();		
				if($tProfile->exist){
					$tmp['NOP'] = $pJson->NOP;
					$tmp['NIK'] = $tProfile->data->NIK;
					$tmp['NAMA'] = $tProfile->data->NAMA;
					$tmp['ALAMAT'] = $tProfile->data->ALAMAT;
					$tmp['KELURAHAN_OP'] = $tProfile->data->KELURAHAN_OP;
					$tmp['KECAMATAN_OP'] = $tProfile->data->KECAMATAN_OP;
					$tmp['KOTA_OP'] = $tProfile->data->KOTA_OP;
					$tmp['LUASTANAH'] = ($tObjekPajak->exist ? (float)$tObjekPajak->data->LUASTANAH : 0);
					$tmp['LUASBANGUNAN'] = ($tObjekPajak->exist ? (float)$tObjekPajak->data->LUASBANGUNAN : 0);
					$tmp['PEMBAYARAN'] = ($tProfile->data->FLAG ? (float)$tProfile->data->PEMBAYARAN : 0) ;
					$tmp['STATUS'] = ($tProfile->data->FLAG ? 'Y' : 'T') ;
					if($tProfile->data->FLAG &&$tProfile->data->TANGGAL_PEMBAYARAN != null){
						$tmp['TANGGAL_PEMBAYARAN'] = Helper::convertDate($tProfile->data->TANGGAL_PEMBAYARAN);
					}else{
						$tmp['TANGGAL_PEMBAYARAN'] = '00/00/0000';
					}
					$tmp['NTPD'] = $pJson->NTPD;
					$tmp['JENISBAYAR'] = ($tProfile->data->FLAG ? 'L' : 'H') ;
					
					$result['respon_code'] = 'OK';		
					$result['result'] = $tmp;		
				}
			}else{return $result1;}									
	}else{return $result2;}
						
		return $result;
	}

	public function isValidNTPD($ntpd){		
		if(strlen($ntpd) != 8 || !is_numeric($ntpd)){
			return false;
		}
		return true;
	}

	public function isValidNOP($nop){		
		if(strlen($nop) != 18 || !is_numeric($nop)){
			return false;
		}
		return true;
	}

	public function getInquiryPBB($pJson){
		$result = array('respon_code' => 'NOP yang diinputkan salah/tidak sesuai');
					
				$tProfile = $this->json->decode($this->pbbModel->getProfile($pJson->NOP));			

				// var_dump($tProfile);		
				
				$tmp = array();		
				if($tProfile->exist){
					$tmp['NOP'] 				= $pJson->NOP; // gw
					$tmp['NIK'] 				= $tProfile->data->ID_WP; // ID_WP GW						
					$tmp['NAMA_WP'] 			= $tProfile->data->WP_NAMA; // WP_NAMA GW
					$tmp['ALAMAT_OP'] 			= $tProfile->data->OP_ALAMAT; // OP_alamat
					$tmp['KECAMATAN_OP'] 		= $tProfile->data->OP_KECAMATAN; // OP_KECAMATAN
					$tmp['KELURAHAN_OP'] 		= $tProfile->data->OP_KELURAHAN; // OP_KELURAHAN
					$tmp['KOTA_KAB_OP'] 			= $tProfile->data->OP_KOTAKAB; //OP_KATAKAB
					$tmp['LUAS_TANAH_OP'] 		= ($tProfile->data->OP_LUAS_BUMI ? (float)$tProfile->data->OP_LUAS_BUMI : 0); // OP_LUAS_BUMI
					$tmp['LUAS_BANGUNAN_OP'] 	= ($tProfile->data->OP_LUAS_BANGUNAN ? (float)$tProfile->data->OP_LUAS_BANGUNAN : 0); //OP_LUAS_BANGUNAN
					$tmp['NJOP_TANAH_OP'] 		= ($tProfile->data->OP_NJOP_BUMI ? (float)$tProfile->data->OP_NJOP_BUMI : 0); //OP_NJOP
					$tmp['NJOP_BANGUNAN_OP'] 	= ($tProfile->data->OP_NJOP_BANGUNAN ? (float)$tProfile->data->OP_NJOP_BANGUNAN : 0) ; //OP_NJOP_BANGUNAN
					$tmp['STATUS_TUNGGAKAN']  	= ceil(((int)$tProfile->data->JML_TAGIHAN_LUNAS/(int) $tProfile->data->JML_TAHUN_TAGIHAN)*100) . '% Lunas';//
					
					$result['respon_code'] = 'OK';		
					$result['result'] = $tmp;		
				}
		
		return $result;
	}

	public function getInquiryPBBSismiop($pJson){
		$result = array('respon_code' => 'NOP yang diinputkan salah/tidak sesuai');		
		
		$options = array('http' => array(
			'method'  => 'POST',
			'content' => $this->json->encode($pJson),
			'header'=>  "Content-Type: application/json\r\n" .
						"Accept: application/json\r\n"
			)
		);
			
		// get data from Query Service
		$url = "http://192.168.26.112/bphtb/pekanbaru/inc/PBB/svc-inquirypbb-bpn.php";
		$context  = stream_context_create( $options );
					
		$response =  file_get_contents( $url, false, $context );	
		$buffer = $this->json->decode($response);
		
		// if success
		if($buffer->RC == 0000){
			$arrObject =  $this->json->decode($buffer->o,true);

			$totalThnTagihan = count($arrObject);
			$data = null;
			
			if($totalThnTagihan != 0){
				$ctLunas = 0;
				for($ctr=0; $ctr<count($arrObject); $ctr++ ){
					//print_r($arrObject[$ctr]);
					$strO = $this->json->encode($arrObject[$ctr]);
					$tmp = (array)$this->json->decode($strO,true);

					// counting tagihan lunas
					// STATUS_TUNGGAKAN : 100% Lunas
					// STATUS_TUNGGAKAN : Belum Lunas
					if($tmp['STATUS_TUNGGAKAN'] == '100% Lunas'){
						$ctLunas++;
					}
				}			
				
				// jika semua data lunas, maka ambil data pertama
				$strO = $this->json->encode($arrObject[0]);
				$buffer = (array)$this->json->decode($strO,true);			
				$data = array(
							'NOP' => $buffer['NOP'],
							'NIK' => $buffer['NIK'],
							'NAMA_WP' => $buffer['NAMA_WP'],
							'ALAMAT_OP' => $buffer['ALAMAT_OP'],
							'KECAMATAN_OP' => $buffer['KECAMATAN_OP'],
							'KELURAHAN_OP' => $buffer['KELURAHAN_OP'],
							'KOTA_KAB_OP' => $buffer['KOTA_KAB_OP'],
							'LUAS_TANAH_OP' => (float)$buffer['LUAS_TANAH_OP'],
							'LUAS_BANGUNAN_OP' => (float)$buffer['LUAS_BANGUNGAN_OP'],
							'NJOP_TANAH_OP' => (float)$buffer['NJOP_TANAH_OP'],
							'NJOP_BANGUNAN_OP' => (float)$buffer['NJOP_BANGUNAN_OP'],
							'STATUS_TUNGGAKAN' => ceil(($ctLunas/$totalThnTagihan)*100) . '% Lunas',							
							);
				
			}
			
			$result['respon_code'] = 'OK';		
			$result['result'] = $data;		
		}
		
		return $result;
	}
	
    public function addDataBPN(){	
		$pJsonReq	= file_get_contents('php://input');	
		//convert json string to array		
		$arrReq	= get_object_vars($this->json->decode($pJsonReq));		

		//get fields
		$fields = array_keys($arrReq); 
		//get values
		$values = array();
		foreach($arrReq as $val){
			$values[] = str_replace("'","",sprintf("%s",$val));
		}

		//save data
		$result = $this->bphtbModel->insertDataBPN($fields, $values);
		
		//build response		
		if($result){
			$response = array('status' => 1);		
		}else{
			$response = array('status' => 0);		
		}
		
		// return
		Helper::echoResponse($this->json->encode($response));
	}	
	
    public function getPPAT(){	
		$pJsonReq	= file_get_contents('php://input');	


		$options = array('http' => array(
			'method'  => 'POST',
			'content' => $pJsonReq,
			'header'=>  "Content-Type: application/json\r\n" .
						"Accept: application/json\r\n"
			)
		);
			
		// get data PPAT
		$url = "https://bphtbcianjurkab.-.id/bpn-service/GetPPAT";
		$context  = stream_context_create( $options );					
		$response =  file_get_contents( $url, false, $context );	
		
		$objResponse	= $this->json->decode($response);		
		if($objResponse->respon_code == 'OK'){
			for($idx=0; $idx<count($objResponse->result); $idx++){
				$arrReq	= get_object_vars($objResponse->result[$idx]);		
				//get fields
				$fields = array_keys($arrReq); 
				//get values
				$values = array();
				foreach($arrReq as $val){
					$values[] = str_replace("'","",sprintf("%s",$val));
				}
				//save data
				$result = $this->bphtbModel->insertPPAT($fields, $values);
				if($result)
					echo 'Data berhasil disimpan.<br/>';
				else
					echo 'Data gagal disimpan.<br/>';
			}
		}
	}	
	
}
