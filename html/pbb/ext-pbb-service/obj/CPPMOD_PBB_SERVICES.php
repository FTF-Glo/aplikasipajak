<?php
class cppmod_pbb_services {
	public function setConn($pConnSw){
		$this->connSw = $pConnSw;
	}
	
	public function setVariableParams($pReq){
		foreach ($pReq as $a => $b) {
            $this->$a = $b;
        }
	}
	
	public function checkDataChange(){
		$data = new \stdClass;
		$data->status = 0;
		$query = "SELECT CPM_OP_NUMBER, CPM_TYPE FROM cppmod_pbb_services WHERE CPM_OP_NUMBER='{$this->params->CPM_NOP}' AND CPM_STATUS NOT IN ('4','5','6') AND CPM_SPPT_YEAR='{$this->params->CPM_SPPT_THN_PENETAPAN}'";
		// echo $query;exit;
		try {
			$stmt = $this->connSw->prepare($query);
			$stmt->execute();	
			$row = $stmt->rowCount();			
			$data = $stmt->fetch(PDO::FETCH_OBJ);
			$data->msg = ($row>0) ? "Data sedang dalam proses ".common::$stsProcess[$data->CPM_TYPE] : "";
			$data->status = ($row>0) ? 0 : 1;
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		return $data;			
	}
	
	public function saveService($num,$pData){
		$data = new \stdClass;
		$data->status = 0;
		$pData->TGL_SURAT_MASUK = (!empty($pData->TGL_SURAT_MASUK)) ? date("Y-m-d",strtotime($pData->TGL_SURAT_MASUK)) : '';
		$pData->BILL->TGL_PEMBAYARAN = (!empty($pData->BILL->TGL_PEMBAYARAN)) ? date("Y-m-d",strtotime($pData->BILL->TGL_PEMBAYARAN)) : '';
		$query = "INSERT INTO cppmod_pbb_services (CPM_ID, CPM_TYPE, CPM_REPRESENTATIVE, CPM_WP_NAME, CPM_WP_ADDRESS, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN,	CPM_WP_KABUPATEN, CPM_WP_PROVINCE, CPM_WP_HANDPHONE, CPM_OP_NUMBER, CPM_OP_ADDRESS, CPM_OP_ADDRESS_NO, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_ATTACHMENT, CPM_RECEIVER, CPM_DATE_RECEIVE, CPM_STATUS, CPM_SPPT_DUE, CPM_SPPT_YEAR, CPM_SPPT_PAYMENT_DATE, CPM_LON, CPM_LAT, CPM_SPPT_YEAR_BERLAKU, CPM_WP_NO_KTP) VALUES ('{$num}', {$pData->JNS_BERKAS}, '{$pData->CPM_WP_NAMA}', '{$pData->CPM_WP_NAMA}', '{$pData->CPM_WP_ALAMAT}', '{$pData->CPM_WP_RT}', '{$pData->CPM_WP_RW}', '{$pData->CPM_WP_KELURAHAN}', '{$pData->CPM_WP_KECAMATAN}', '{$pData->CPM_WP_KOTAKAB}', '{$pData->CPM_WP_PROPINSI}', '{$pData->CPM_WP_NO_HP}', '{$pData->CPM_NOP}', '{$pData->CPM_OP_ALAMAT}', '{$pData->CPM_OP_NOMOR}', '{$pData->CPM_OP_RT}', '{$pData->CPM_OP_RW}', '{$pData->CPM_OP_KELURAHAN}', '{$pData->CPM_OP_KECAMATAN}', '{$pData->ATTACHMENT}', '{$pData->USER}', '{$pData->TGL_SURAT_MASUK}', '1', '{$pData->BILL->TAGIHAN}', '{$pData->CPM_SPPT_THN_PENETAPAN}', '{$pData->BILL->TGL_PEMBAYARAN}','{$pData->LON}','{$pData->LAT}','{$pData->THN_BERLAKU}','{$pData->CPM_WP_NO_KTP}')";
		// echo $query;exit;
		try {
			$stmt = $this->connSw->prepare($query);
			$res = $stmt->execute();
			$data->msg = ($res) ? "Simpan perubahan data services berhasil" : "Simpan perubahan data services gagal";
			$data->rc = ($res) ? 1 : 0;
		}catch(Exception $e) {
			echo 'Exception Services -> ';
			var_dump($e->getMessage());
			die();
		}
		return $data;				
	}
}
?>