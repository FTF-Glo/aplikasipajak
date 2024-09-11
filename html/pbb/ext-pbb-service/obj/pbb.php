<?php
Class PBB{	        

	static public function getDataWPPBB($pConnSw,$pWPID,&$status){
		$data = array();
		$query = "select * from cppmod_pbb_wajib_pajak where CPM_WP_ID='{$pWPID}'";
		// echo $query;exit;
		try {
			$stmt = $pConnSw->prepare($query);
			$stmt->execute();					
			$status = ($stmt->rowCount()>0) ? 1 : 0;
			$data = $stmt->fetch(PDO::FETCH_OBJ);
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		return $data;
	}      

	static public function checkTaxPBB($pConnSw,$pNOP,&$status){
		$data = array();
		$query = "select CPM_NOP from cppmod_pbb_sppt_final where CPM_NOP='{$pNOP}'
				union all
				select CPM_NOP from cppmod_pbb_sppt_susulan where CPM_NOP='{$pNOP}'";
		// echo $query;exit;
		try {
			$stmt = $pConnSw->prepare($query);
			$stmt->execute();					
			$status = ($stmt->rowCount()>0) ? 1 : 0;
			$data = $stmt->fetch(PDO::FETCH_OBJ);
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		return $data;
	}
	
	static public function checkDebtPBB($pConnGw,$pNOP,$pThn,&$status){
		$data = array();
		$query = "
		SELECT
			A.nop,
			sum(A.SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
			sum(B.PBB_DENDA) AS DENDA
		FROM
			PBB_SPPT A
		LEFT JOIN PBB_DENDA B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK AND (
			A.PAYMENT_FLAG != '1'
			OR A.PAYMENT_FLAG IS NULL
		)
		where
		A.nop = '{$pNOP}'
		AND A.sppt_tahun_pajak = '{$pThn}'
		group by A.nop
		";
		try {
			$stmt = $pConnGw->prepare($query);
			$stmt->execute();					
			$status = ($stmt->rowCount()>0) ? 1 : 0;
			$data = $stmt->fetch(PDO::FETCH_OBJ);
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	
	static public function getDataOPPBB($pConnSw,$pNOP,&$status){
		$data = array();
		$query = "
		select a.CPM_SPPT_DOC_ID,a.CPM_NOP,a.CPM_NOP_BERSAMA,a.CPM_OP_ALAMAT,a.CPM_OP_NOMOR,b.CPC_TK_KABKOTA,c.CPC_TKC_KECAMATAN,d.CPC_TKL_KELURAHAN,a.CPM_OP_RT,a.CPM_OP_RW,a.CPM_WP_STATUS,a.CPM_WP_PEKERJAAN,a.CPM_WP_NAMA,a.CPM_WP_ID,a.CPM_WP_ALAMAT,a.CPM_WP_KELURAHAN,a.CPM_WP_RT,a.CPM_WP_RW,a.CPM_WP_PROPINSI,a.CPM_WP_KOTAKAB,a.CPM_WP_KECAMATAN,a.CPM_WP_KODEPOS,a.CPM_WP_NO_KTP,a.CPM_WP_NO_HP,a.CPM_OT_LATITUDE,a.CPM_OT_LONGITUDE,a.CPM_OT_ZONA_NILAI,a.CPM_OT_JENIS,a.CPM_OT_PENILAIAN_TANAH,a.CPM_OT_PAYMENT_SISTEM,a.CPM_OT_PAYMENT_INDIVIDU,a.CPM_OP_JML_BANGUNAN,a.CPM_PP_TIPE,a.CPM_PP_NAMA,a.CPM_PP_DATE,a.CPM_OPR_TGL_PENDATAAN,a.CPM_OP_SKET,a.CPM_OP_FOTO,a.CPM_OP_LUAS_TANAH,a.CPM_OP_KELAS_TANAH,a.CPM_NJOP_TANAH,a.CPM_OP_LUAS_BANGUNAN,a.CPM_OP_KELAS_BANGUNAN,a.CPM_NJOP_BANGUNAN,'' CPM_SPPT_THN_PENETAPAN,'' REFF
		from cppmod_pbb_sppt a
		left join cppmod_tax_kabkota b on a.CPM_OP_KOTAKAB=b.CPC_TK_ID
		left join cppmod_tax_kecamatan c on a.CPM_OP_KECAMATAN=c.CPC_TKC_ID
		left join cppmod_tax_kelurahan d on a.CPM_OP_KELURAHAN=d.CPC_TKL_ID
		where a.CPM_NOP='{$pNOP}'
		union all
		select a.CPM_SPPT_DOC_ID,a.CPM_NOP,a.CPM_NOP_BERSAMA,a.CPM_OP_ALAMAT,a.CPM_OP_NOMOR,b.CPC_TK_KABKOTA,c.CPC_TKC_KECAMATAN,d.CPC_TKL_KELURAHAN,a.CPM_OP_RT,a.CPM_OP_RW,a.CPM_WP_STATUS,a.CPM_WP_PEKERJAAN,a.CPM_WP_NAMA,a.CPM_WP_ID,a.CPM_WP_ALAMAT,a.CPM_WP_KELURAHAN,a.CPM_WP_RT,a.CPM_WP_RW,a.CPM_WP_PROPINSI,a.CPM_WP_KOTAKAB,a.CPM_WP_KECAMATAN,a.CPM_WP_KODEPOS,a.CPM_WP_NO_KTP,a.CPM_WP_NO_HP,a.CPM_OT_LATITUDE,a.CPM_OT_LONGITUDE,a.CPM_OT_ZONA_NILAI,a.CPM_OT_JENIS,a.CPM_OT_PENILAIAN_TANAH,a.CPM_OT_PAYMENT_SISTEM,a.CPM_OT_PAYMENT_INDIVIDU,a.CPM_OP_JML_BANGUNAN,a.CPM_PP_TIPE,a.CPM_PP_NAMA,a.CPM_PP_DATE,a.CPM_OPR_TGL_PENDATAAN,a.CPM_OP_SKET,a.CPM_OP_FOTO,a.CPM_OP_LUAS_TANAH,a.CPM_OP_KELAS_TANAH,a.CPM_NJOP_TANAH,a.CPM_OP_LUAS_BANGUNAN,a.CPM_OP_KELAS_BANGUNAN,a.CPM_NJOP_BANGUNAN,a.CPM_SPPT_THN_PENETAPAN,'_FINAL' REFF
		from cppmod_pbb_sppt_final a
		left join cppmod_tax_kabkota b on a.CPM_OP_KOTAKAB=b.CPC_TK_ID
		left join cppmod_tax_kecamatan c on a.CPM_OP_KECAMATAN=c.CPC_TKC_ID
		left join cppmod_tax_kelurahan d on a.CPM_OP_KELURAHAN=d.CPC_TKL_ID
		where a.CPM_NOP='{$pNOP}'
		union all
		select a.CPM_SPPT_DOC_ID,a.CPM_NOP,a.CPM_NOP_BERSAMA,a.CPM_OP_ALAMAT,a.CPM_OP_NOMOR,b.CPC_TK_KABKOTA,c.CPC_TKC_KECAMATAN,d.CPC_TKL_KELURAHAN,a.CPM_OP_RT,a.CPM_OP_RW,a.CPM_WP_STATUS,a.CPM_WP_PEKERJAAN,a.CPM_WP_NAMA,a.CPM_WP_ID,a.CPM_WP_ALAMAT,a.CPM_WP_KELURAHAN,a.CPM_WP_RT,a.CPM_WP_RW,a.CPM_WP_PROPINSI,a.CPM_WP_KOTAKAB,a.CPM_WP_KECAMATAN,a.CPM_WP_KODEPOS,a.CPM_WP_NO_KTP,a.CPM_WP_NO_HP,a.CPM_OT_LATITUDE,a.CPM_OT_LONGITUDE,a.CPM_OT_ZONA_NILAI,a.CPM_OT_JENIS,a.CPM_OT_PENILAIAN_TANAH,a.CPM_OT_PAYMENT_SISTEM,a.CPM_OT_PAYMENT_INDIVIDU,a.CPM_OP_JML_BANGUNAN,a.CPM_PP_TIPE,a.CPM_PP_NAMA,a.CPM_PP_DATE,a.CPM_OPR_TGL_PENDATAAN,a.CPM_OP_SKET,a.CPM_OP_FOTO,a.CPM_OP_LUAS_TANAH,a.CPM_OP_KELAS_TANAH,a.CPM_NJOP_TANAH,a.CPM_OP_LUAS_BANGUNAN,a.CPM_OP_KELAS_BANGUNAN,a.CPM_NJOP_BANGUNAN,a.CPM_SPPT_THN_PENETAPAN,'_SUSULAN' REFF
		from cppmod_pbb_sppt_susulan a
		left join cppmod_tax_kabkota b on a.CPM_OP_KOTAKAB=b.CPC_TK_ID
		left join cppmod_tax_kecamatan c on a.CPM_OP_KECAMATAN=c.CPC_TKC_ID
		left join cppmod_tax_kelurahan d on a.CPM_OP_KELURAHAN=d.CPC_TKL_ID
		where a.CPM_NOP='{$pNOP}'
		";
		try {
			$stmt = $pConnSw->prepare($query);
			$stmt->execute();					
			$status = ($stmt->rowCount()>0) ? 1 : 0;
			$data = $stmt->fetch(PDO::FETCH_OBJ);
			
			$query2 = "select * from cppmod_pbb_sppt_ext{$data->REFF} where CPM_SPPT_DOC_ID='{$data->CPM_SPPT_DOC_ID}'";
			$stmt2 = $pConnSw->prepare($query2);
			$stmt2->execute();					
			$data->EXT = $stmt2->fetch(PDO::FETCH_OBJ);
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	
	static public function getDataOPChangePBB($pConnSw,$pConnGw,$pNOP,&$status){
		$data = array();
		$query = "
		SELECT A.CPM_SPPT_DOC_ID,A.CPM_NOP, A.CPM_OP_ALAMAT, A.CPM_OP_NOMOR, A.CPM_OP_RT, A.CPM_OP_RW, A.CPM_OP_KELURAHAN, A.CPM_OP_KECAMATAN, A.CPM_OP_KOTAKAB, A.CPM_OP_LUAS_TANAH, A.CPM_OP_LUAS_BANGUNAN, A.CPM_WP_NAMA, A.CPM_WP_ALAMAT, A.CPM_WP_RT, A.CPM_WP_RW, A.CPM_WP_KELURAHAN, A.CPM_WP_KECAMATAN, A.CPM_WP_PROPINSI, A.CPM_WP_KOTAKAB, A.CPM_WP_NO_HP,
			B.CPC_TKL_KELURAHAN AS KELURAHAN_OP,
			C.CPC_TKC_KECAMATAN AS KECAMATAN_OP,
			D.CPC_TK_KABKOTA AS KABKOTA_OP,
			E.CPC_TP_PROPINSI AS PROPINSI_OP,
			SUBSTRING(A.CPM_OP_KOTAKAB,1,2) AS ID_PROPINSI_OP,
			A.CPM_WP_KELURAHAN AS KELURAHAN_WP,
			A.CPM_WP_KECAMATAN AS KECAMATAN_WP,
			A.CPM_WP_KOTAKAB AS KABKOTA_WP,
			A.CPM_WP_PROPINSI AS PROPINSI_WP,
			A.CPM_SPPT_THN_PENETAPAN, A.CPM_OT_JENIS,
			A.CPM_WP_NO_KTP,A.REFF
			FROM 
			(
			SELECT CPM_SPPT_DOC_ID,CPM_NOP, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_SPPT_THN_PENETAPAN,
			CPM_OP_KOTAKAB, CPM_WP_NAMA, CPM_WP_ALAMAT, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN, CPM_WP_PROPINSI, CPM_WP_KOTAKAB,CPM_WP_NO_HP,CPM_OT_JENIS, CPM_OP_LUAS_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_WP_NO_KTP,'_FINAL' REFF FROM cppmod_pbb_sppt_final
			WHERE CPM_NOP='{$pNOP}'
			UNION ALL 
			SELECT CPM_SPPT_DOC_ID,CPM_NOP, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_SPPT_THN_PENETAPAN,
			CPM_OP_KOTAKAB, CPM_WP_NAMA, CPM_WP_ALAMAT, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN, CPM_WP_PROPINSI, CPM_WP_KOTAKAB,CPM_WP_NO_HP,CPM_OT_JENIS, CPM_OP_LUAS_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_WP_NO_KTP,'_SUSULAN' REFF FROM cppmod_pbb_sppt_susulan
			WHERE CPM_NOP='{$pNOP}'
			) A 
			LEFT JOIN cppmod_tax_kelurahan B ON A.CPM_OP_KELURAHAN=B.CPC_TKL_ID
			LEFT JOIN cppmod_tax_kecamatan C ON C.CPC_TKC_ID=A.CPM_OP_KECAMATAN
			LEFT JOIN cppmod_tax_kabkota D ON D.CPC_TK_ID=A.CPM_OP_KOTAKAB
			LEFT JOIN cppmod_tax_propinsi E ON E.CPC_TP_ID=SUBSTRING(A.CPM_OP_KOTAKAB,1,2)
		";
		try {
			$stmt = $pConnSw->prepare($query);
			$stmt->execute();					
			$status = ($stmt->rowCount()>0) ? 1 : 0;
			$data = $stmt->fetch(PDO::FETCH_OBJ);
			$data->JNS_BERKAS = 5;
			$data->THN_BERLAKU = date("Y")+1;
			$data->TGL_SURAT_MASUK = date("d-m-Y");
			$data->LON = "";
			$data->LAT = "";
			$data->ATTACHMENT = "";
			
			$query2 = "select * from cppmod_pbb_sppt_ext{$data->REFF} where CPM_SPPT_DOC_ID='{$data->CPM_SPPT_DOC_ID}'";
			$stmt2 = $pConnSw->prepare($query2);
			$stmt2->execute();					
			$data->EXT = PBB::getSPPTEXT($pConnSw,$data->REFF,$data->CPM_SPPT_DOC_ID,$row);			
			$data->BILL = PBB::getBill($pConnGw,$pNOP,$data->CPM_SPPT_THN_PENETAPAN,$row);	
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;		
	}
	
	static public function getSPPTEXT($pConnSw,$pReff,$pDocId,&$row){
		$data = array();
		$query = "select CPM_SPPT_DOC_VERSION,CPM_OP_NUM,cpm_op_penggunaan,CPM_OP_LUAS_BANGUNAN
		from cppmod_pbb_sppt_ext{$pReff} where CPM_SPPT_DOC_ID='{$pDocId}'";
		try {
			$stmt2 = $pConnSw->prepare($query);
			$stmt2->execute();			
			$row = ($stmt2->rowCount()>0) ? 1 : 0;
			foreach($stmt2->fetchAll(PDO::FETCH_OBJ) as $dt){
				$data[] = $dt;
			}
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	
	static public function getBill($pConnGw,$pNOP,$pThn,&$row){
		$data = new \stdClass;
		$data->NOP = "";
		$data->TAHUN = "";
		$data->TAGIHAN = "";
		$data->STATUS_PEMBAYARAN = "";
		$data->TGL_PEMBAYARAN = "";
		$query = "SELECT NOP, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, PAYMENT_FLAG, PAYMENT_PAID FROM PBB_SPPT WHERE NOP = '{$pNOP}' AND SPPT_TAHUN_PAJAK = '{$pThn}'";
		try {
			$stmt = $pConnGw->prepare($query);
			$stmt->execute();					
			$row = ($stmt->rowCount()>0) ? 1 : 0;
			$tmp = $stmt->fetch(PDO::FETCH_OBJ);
			if(!empty($tmp)){
				$data->NOP = $tmp->NOP;
				$data->TAHUN = $tmp->SPPT_TAHUN_PAJAK;
				$data->TAGIHAN = $tmp->SPPT_PBB_HARUS_DIBAYAR;
				$data->STATUS_PEMBAYARAN = $tmp->PAYMENT_FLAG;
				$data->TGL_PEMBAYARAN = $tmp->PAYMENT_PAID;
			}
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	
	static public function dataChangePBB($pConnSw,$pConnGw,$pReq,&$status){
		try {
			require_once "cppmod_pbb_services.php";
			require_once "cppmod_pbb_service_change.php";
			require_once "cppmod_pbb_service_change_ext.php";
			
			$data = new \stdClass;
			$tServ = new cppmod_pbb_services();
			$tServCh = new cppmod_pbb_service_change();
			$tServChExt = new cppmod_pbb_service_change_ext();
			$tServ->setVariableParams($pReq);
			$tServCh->setVariableParams($pReq);
			$tServ->setConn($pConnSw);
			$tServCh->setConn($pConnSw);
			$tServChExt->setConn($pConnSw);
			$r1 = $tServ->checkDataChange();
			
			if($r1->status==1){
				$conf = common::GetModuleConfig($pConnSw,'mLkt');
				$num = common::generateNumber($pConnSw,$conf,$pReq->params->JNS_BERKAS);
				
				$tmp = explode('/',$num);
				$tData = new \stdClass;
				$tData->id = $num;
				$tData->num = $tmp[0];
				$tData->uname = $pReq->user;
				$tData->tgl_masuk = date("Y-m-d",strtotime($pReq->params->TGL_SURAT_MASUK));
				
				$tDataChange = PBB::getDataOPChangePBB($pConnSw,$pConnGw,$pReq->params->CPM_NOP,$status);
				$tDataChange->JNS_BERKAS = $pReq->params->JNS_BERKAS;
				$tDataChange->THN_BERLAKU = $pReq->params->THN_BERLAKU;
				$tDataChange->TGL_SURAT_MASUK = $pReq->params->TGL_SURAT_MASUK;
				$tDataChange->LON = $pReq->params->LON;
				$tDataChange->LAT = $pReq->params->LAT;
				$tDataChange->ATTACHMENT = $pReq->params->ATTACHMENT;
				$tDataChange->USER = $pReq->user;
				
				if(empty($tDataChange->BILL->TAGIHAN)){
					$tResCtr = common::updCtrSrvNum($pConnSw,$tData);
					$tResSv  = $tServ->saveService($num,$tDataChange);
					if($tResSv->rc==1){
						$tResSvCh = $tServCh->saveServiceChange($num);
						if($tResSvCh->rc==1){
							if(!empty($pReq->params->EXT)){
								$tRes = array();$tMsg = array();$a=0;
								$tCounter = count($pReq->params->EXT);
								foreach($pReq->params->EXT as $var){
									$tResSvChExt = $tServChExt->saveServiceChangeExt($pReq->params->CPM_NOP,$var->CPM_OP_NUM,$var->CPM_OP_LUAS_BANGUNAN);
									$tRes[$a] = $tResSvChExt->rc;
									$tMsg[$a] = $tResSvChExt->msg." [num:{$var->CPM_OP_NUM}]";
									$a++;
								}
								
								$status = $tResSvCh->rc;
								$data->rc = $tResSvCh->rc;
								$data->cmp_id = $num;
								$data->msg = implode(", ", $tMsg);						
							}
							else {
								$status = $tResSvCh->rc;
								$data->rc = $tResSvCh->rc;
								$data->cmp_id = $num;
								$data->msg = $tResSvCh->msg;
							}
						}
						else {
							$status = $tResSvCh->rc;
							$data->rc = $tResSvCh->rc;
							$data->cmp_id = $num;
							$data->msg = $tResSvCh->msg;
						}
					}
					else {
						common::delCtrSrvNum($pConnSw,$num);
						$status = $tResSv->rc;
						$data->rc = $tResSv->rc;
						$data->cmp_id = $num;
						$data->msg = $tResSv->msg;
					}
				}
				else {
					$status = 0;
					$data->rc = 5;
					$data->msg = "Perubahan data gagal disimpan karena masih ada tunggakan sebesar Rp ".number_format($tDataChange->BILL->TAGIHAN,0,',','.');
				}
				
				return $data;
			}
			else {
				return $r1;
			}
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
	}
	
	static public function updateOPPBBTematik_PG($pConnSW,$pConnPG,$pKelID,&$status){
		$data = new \stdClass;
		$data->NOP = "";
		$status = 1;			
		
		//UPDATE-SW
		$query = "SELECT A.CPM_NOP AS NOP, A.CPM_OT_JENIS AS JNS_TANAH, A.CPM_OP_KELAS_TANAH AS KLS_TANAH, B.cpm_op_penggunaan AS JNS_BNG, 
					A.CPM_OP_KELAS_BANGUNAN AS KLS_BNG, A.CPM_OT_ZONA_NILAI AS ZNT, A.CPM_OT_PAYMENT_INDIVIDU AS NILAI_INDIVIDU
					FROM cppmod_pbb_sppt_final A, cppmod_pbb_sppt_ext_final B
					WHERE
					A.CPM_SPPT_DOC_ID = B.CPM_SPPT_DOC_ID AND
					A.CPM_OP_KELURAHAN = '{$pKelID}'";

		try {
			$stmt = $pConnSW->prepare($query);
			$stmt->execute();					
			$row = ($stmt->rowCount()>0) ? 1 : 0;
			foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row){				
				$querySW = "update tematik 
							set jenis_tanah=".$row->JNS_TANAH.", 
							kelas_tanah='".$row->KLS_TANAH."',
							jenis_bangunan=".$row->JNS_BNG.",
							kelas_bangunan='".$row->KLS_BNG."',
							znt='".$row->ZNT."',
							nilai_individu=".$row->NILAI_INDIVIDU."
							where
							nop='".$row->ZNT."'";
				try{
					$stmt = $pConnPG->prepare($query);
					$stmt->execute();										
				}catch(Exception $e) {
					echo 'Exception -> ';
					var_dump($e->getMessage());
					die();
				}
			}			
			/*
			$tmp = $stmt->fetch(PDO::FETCH_OBJ);
			print_r($tmp);
			if(!empty($tmp)){
				$data->NOP = $tmp->nop;
			}
			*/ 
		}catch(Exception $e) {
			echo 'Exception -> ';
			var_dump($e->getMessage());
			die();
		}
		
		return $data;
	}
	
}
?>
