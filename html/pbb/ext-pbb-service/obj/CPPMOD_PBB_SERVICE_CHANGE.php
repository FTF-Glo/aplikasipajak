<?php
class cppmod_pbb_service_change {
	public function setConn($pConnSw){
		$this->connSw = $pConnSw;
	}
	
	public function setVariableParams($pReq){
		foreach ($pReq as $a => $b) {
            $this->$a = $b;
        }
	}
		
	public function saveServiceChange($pNum){
		$data = new \stdClass;
		$data->status = 0;
		$query = "INSERT INTO cppmod_pbb_service_change 
                    SELECT 
                    '{$pNum}', '', '{$this->params->CPM_SPPT_THN_PENETAPAN}', TBL.* 
                    FROM 
                    (
						SELECT 						CPM_SPPT_DOC_ID,CPM_SPPT_DOC_VERSION,CPM_SPPT_DOC_AUTHOR,CPM_SPPT_DOC_CREATED,CPM_NOP,CPM_NOP_BERSAMA,CPM_OP_ALAMAT,CPM_OP_NOMOR,CPM_OP_KELURAHAN,CPM_OP_RT,CPM_OP_RW,CPM_OP_KECAMATAN,CPM_OP_KOTAKAB,CPM_WP_STATUS,CPM_WP_PEKERJAAN,CPM_WP_NAMA,CPM_WP_ID,CPM_WP_ALAMAT,CPM_WP_KELURAHAN,CPM_WP_RT,CPM_WP_RW,CPM_WP_PROPINSI,CPM_WP_KOTAKAB,CPM_WP_KECAMATAN,CPM_WP_KODEPOS,CPM_WP_NO_KTP,'{$this->params->CPM_WP_NO_HP}' CPM_WP_NO_HP,'{$this->params->LAT}' CPM_OT_LATITUDE,'{$this->params->LON}' CPM_OT_LONGITUDE,CPM_OT_ZONA_NILAI,'{$this->params->CPM_OT_JENIS}' CPM_OT_JENIS,CPM_OT_PENILAIAN_TANAH,CPM_OT_PAYMENT_SISTEM,CPM_OT_PAYMENT_INDIVIDU,CPM_OP_JML_BANGUNAN,CPM_PP_TIPE,CPM_PP_NAMA,CPM_PP_DATE,CPM_OPR_TGL_PENDATAAN,CPM_OPR_NAMA,CPM_OPR_NIP,CPM_PJB_TGL_PENELITIAN,CPM_PJB_NAMA,CPM_PJB_NIP,CPM_OP_SKET,CPM_OP_FOTO,'{$this->params->CPM_OP_LUAS_TANAH}' CPM_OP_LUAS_TANAH,CPM_OP_KELAS_TANAH,CPM_NJOP_TANAH,'{$this->params->CPM_OP_LUAS_BANGUNAN}' CPM_OP_LUAS_BANGUNAN,CPM_OP_KELAS_BANGUNAN,CPM_NJOP_BANGUNAN,CPM_SPPT_THN_PENETAPAN
						FROM cppmod_pbb_sppt_final
						WHERE CPM_NOP = '{$this->params->CPM_NOP}' 
						UNION ALL
						SELECT 					CPM_SPPT_DOC_ID,CPM_SPPT_DOC_VERSION,CPM_SPPT_DOC_AUTHOR,CPM_SPPT_DOC_CREATED,CPM_NOP,CPM_NOP_BERSAMA,CPM_OP_ALAMAT,CPM_OP_NOMOR,CPM_OP_KELURAHAN,CPM_OP_RT,CPM_OP_RW,CPM_OP_KECAMATAN,CPM_OP_KOTAKAB,CPM_WP_STATUS,CPM_WP_PEKERJAAN,CPM_WP_NAMA,CPM_WP_ID,CPM_WP_ALAMAT,CPM_WP_KELURAHAN,CPM_WP_RT,CPM_WP_RW,CPM_WP_PROPINSI,CPM_WP_KOTAKAB,CPM_WP_KECAMATAN,CPM_WP_KODEPOS,CPM_WP_NO_KTP,'{$this->params->CPM_WP_NO_HP}' CPM_WP_NO_HP,'{$this->params->LAT}' CPM_OT_LATITUDE,'{$this->params->LON}' CPM_OT_LONGITUDE,CPM_OT_ZONA_NILAI,'{$this->params->CPM_OT_JENIS}' CPM_OT_JENIS,CPM_OT_PENILAIAN_TANAH,CPM_OT_PAYMENT_SISTEM,CPM_OT_PAYMENT_INDIVIDU,CPM_OP_JML_BANGUNAN,CPM_PP_TIPE,CPM_PP_NAMA,CPM_PP_DATE,CPM_OPR_TGL_PENDATAAN,CPM_OPR_NAMA,CPM_OPR_NIP,CPM_PJB_TGL_PENELITIAN,CPM_PJB_NAMA,CPM_PJB_NIP,CPM_OP_SKET,CPM_OP_FOTO,'{$this->params->CPM_OP_LUAS_TANAH}' CPM_OP_LUAS_TANAH,CPM_OP_KELAS_TANAH,CPM_NJOP_TANAH,'{$this->params->CPM_OP_LUAS_BANGUNAN}' CPM_OP_LUAS_BANGUNAN,CPM_OP_KELAS_BANGUNAN,CPM_NJOP_BANGUNAN,CPM_SPPT_THN_PENETAPAN
						FROM cppmod_pbb_sppt_susulan 
						WHERE CPM_NOP = '{$this->params->CPM_NOP}'
					) TBL ";
		// echo $query;exit;
		try {
			$stmt = $this->connSw->prepare($query);
			$res = $stmt->execute();
			$data->msg = ($res) ? "Simpan perubahan data service change berhasil" : "Simpan perubahan data service change gagal";
			$data->rc = ($res) ? 1 : 0;
		}catch(Exception $e) {
			echo 'Exception Service Change -> ';
			var_dump($e->getMessage());
			die();
		}
		return $data;				
	}
}
?>