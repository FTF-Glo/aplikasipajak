<?php
Class PBB_SPPT{
	protected $NOP;
	protected $SPPT_TAHUN_PAJAK;
	protected $SPPT_TANGGAL_JATUH_TEMPO;
	protected $SPPT_PBB_HARUS_DIBAYAR;
	protected $WP_NAMA;
	protected $WP_TELEPON;
	protected $WP_NO_HP;
	protected $WP_ALAMAT;
	protected $WP_RT;
	protected $WP_RW;
	protected $WP_KELURAHAN;
	protected $WP_KECAMATAN;
	protected $WP_KOTAKAB;
	protected $WP_KODEPOS;
	protected $SPPT_TANGGAL_TERBIT;
	protected $SPPT_TANGGAL_CETAK;
	protected $OP_LUAS_BUMI;
	protected $OP_LUAS_BANGUNAN;
	protected $OP_KELAS_BUMI;
	protected $OP_KELAS_BANGUNAN;
	protected $OP_NJOP_BUMI;
	protected $OP_NJOP_BANGUNAN;
	protected $OP_NJOP;
	protected $OP_NJOPTKP;
	protected $OP_NJKP;
	protected $PAYMENT_FLAG;
	protected $PAYMENT_PAID;
	protected $PAYMENT_REF_NUMBER;
	protected $PAYMENT_BANK_CODE;
	protected $PAYMENT_SW_REFNUM;
	protected $PAYMENT_GW_REFNUM;
	protected $PAYMENT_SW_ID;
	protected $PAYMENT_MERCHANT_CODE;
	protected $PAYMENT_SETTLEMENT_DATE;
	protected $PBB_COLLECTIBLE;
	protected $PBB_DENDA;
	protected $PBB_ADMIN_GW;
	protected $PBB_MISC_FEE;
	protected $PBB_TOTAL_BAYAR;
	protected $OP_ALAMAT;
	protected $OP_RT;
	protected $OP_RW;
	protected $OP_KELURAHAN;
	protected $OP_KECAMATAN;
	protected $OP_KOTAKAB;
	protected $OP_KELURAHAN_KODE;
	protected $OP_KECAMATAN_KODE;
	protected $OP_KOTAKAB_KODE;
	protected $OP_PROVINSI_KODE;
	protected $TGL_STPD;
	protected $TGL_SP1;
	protected $TGL_SP2;
	protected $TGL_SP3;
	protected $STATUS_SP;
	protected $STATUS_CETAK;
	protected $WP_PEKERJAAN;
	protected $PAYMENT_OFFLINE_USER_ID;
	protected $PAYMENT_OFFLINE_FLAG;
	protected $PAYMENT_OFFLINE_PAID;
	protected $ID_WP;

	protected function getNop(){
		return $this->NOP;
	}

	protected function setNop($NOP){
		$this->NOP=$NOP;
	}
	
	protected function getSppt_tahun_pajak(){
		return $this->SPPT_TAHUN_PAJAK;
	}

	protected function setSppt_tahun_pajak($SPPT_TAHUN_PAJAK){
		$this->SPPT_TAHUN_PAJAK=$SPPT_TAHUN_PAJAK;
	}

	protected function getSppt_tanggal_jatuh_tempo(){
		return $this->SPPT_TANGGAL_JATUH_TEMPO;
	}

	protected function setSppt_tanggal_jatuh_tempo($SPPT_TANGGAL_JATUH_TEMPO){
		$this->SPPT_TANGGAL_JATUH_TEMPO=$SPPT_TANGGAL_JATUH_TEMPO;
	}

	protected function getSppt_pbb_harus_dibayar(){
		return $this->SPPT_PBB_HARUS_DIBAYAR;
	}

	protected function setSppt_pbb_harus_dibayar($SPPT_PBB_HARUS_DIBAYAR){
		$this->SPPT_PBB_HARUS_DIBAYAR=$SPPT_PBB_HARUS_DIBAYAR;
	}

	protected function getWp_nama(){
		return $this->WP_NAMA;
	}

	protected function setWp_nama($WP_NAMA){
		$this->WP_NAMA=$WP_NAMA;
	}

	protected function getWp_telepon(){
		return $this->WP_TELEPON;
	}

	protected function setWp_telepon($WP_TELEPON){
		$this->WP_TELEPON=$WP_TELEPON;
	}

	protected function getWp_no_hp(){
		return $this->WP_NO_HP;
	}

	protected function setWp_no_hp($WP_NO_HP){
		$this->WP_NO_HP=$WP_NO_HP;
	}

	protected function getWp_alamat(){
		return $this->WP_ALAMAT;
	}

	protected function setWp_alamat($WP_ALAMAT){
		$this->WP_ALAMAT=$WP_ALAMAT;
	}

	protected function getWp_rt(){
		return $this->WP_RT;
	}

	protected function setWp_rt($WP_RT){
		$this->WP_RT=$WP_RT;
	}

	protected function getWp_rw(){
		return $this->WP_RW;
	}

	protected function setWp_rw($WP_RW){
		$this->WP_RW=$WP_RW;
	}

	protected function getWp_kelurahan(){
		return $this->WP_KELURAHAN;
	}

	protected function setWp_kelurahan($WP_KELURAHAN){
		$this->WP_KELURAHAN=$WP_KELURAHAN;
	}

	protected function getWp_kecamatan(){
		return $this->WP_KECAMATAN;
	}

	protected function setWp_kecamatan($WP_KECAMATAN){
		$this->WP_KECAMATAN=$WP_KECAMATAN;
	}

	protected function getWp_kotakab(){
		return $this->WP_KOTAKAB;
	}

	protected function setWp_kotakab($WP_KOTAKAB){
		$this->WP_KOTAKAB=$WP_KOTAKAB;
	}

	protected function getWp_kodepos(){
		return $this->WP_KODEPOS;
	}

	protected function setWp_kodepos($WP_KODEPOS){
		$this->WP_KODEPOS=$WP_KODEPOS;
	}

	protected function getSppt_tanggal_terbit(){
		return $this->SPPT_TANGGAL_TERBIT;
	}

	protected function setSppt_tanggal_terbit($SPPT_TANGGAL_TERBIT){
		$this->SPPT_TANGGAL_TERBIT=$SPPT_TANGGAL_TERBIT;
	}

	protected function getSppt_tanggal_cetak(){
		return $this->SPPT_TANGGAL_CETAK;
	}

	protected function setSppt_tanggal_cetak($SPPT_TANGGAL_CETAK){
		$this->SPPT_TANGGAL_CETAK=$SPPT_TANGGAL_CETAK;
	}

	protected function getOp_luas_bumi(){
		return $this->OP_LUAS_BUMI;
	}

	protected function setOp_luas_bumi($OP_LUAS_BUMI){
		$this->OP_LUAS_BUMI=$OP_LUAS_BUMI;
	}

	protected function getOp_luas_bangunan(){
		return $this->OP_LUAS_BANGUNAN;
	}

	protected function setOp_luas_bangunan($OP_LUAS_BANGUNAN){
		$this->OP_LUAS_BANGUNAN=$OP_LUAS_BANGUNAN;
	}

	protected function getOp_kelas_bumi(){
		return $this->OP_KELAS_BUMI;
	}

	protected function setOp_kelas_bumi($OP_KELAS_BUMI){
		$this->OP_KELAS_BUMI=$OP_KELAS_BUMI;
	}

	protected function getOp_kelas_bangunan(){
		return $this->OP_KELAS_BANGUNAN;
	}

	protected function setOp_kelas_bangunan($OP_KELAS_BANGUNAN){
		$this->OP_KELAS_BANGUNAN=$OP_KELAS_BANGUNAN;
	}

	protected function getOp_njop_bumi(){
		return $this->OP_NJOP_BUMI;
	}

	protected function setOp_njop_bumi($OP_NJOP_BUMI){
		$this->OP_NJOP_BUMI=$OP_NJOP_BUMI;
	}

	protected function getOp_njop_bangunan(){
		return $this->OP_NJOP_BANGUNAN;
	}

	protected function setOp_njop_bangunan($OP_NJOP_BANGUNAN){
		$this->OP_NJOP_BANGUNAN=$OP_NJOP_BANGUNAN;
	}

	protected function getOp_njop(){
		return $this->OP_NJOP;
	}

	protected function setOp_njop($OP_NJOP){
		$this->OP_NJOP=$OP_NJOP;
	}

	protected function getOp_njoptkp(){
		return $this->OP_NJOPTKP;
	}

	protected function setOp_njoptkp($OP_NJOPTKP){
		$this->OP_NJOPTKP=$OP_NJOPTKP;
	}

	protected function getOp_njkp(){
		return $this->OP_NJKP;
	}

	protected function setOp_njkp($OP_NJKP){
		$this->OP_NJKP=$OP_NJKP;
	}

	protected function getPayment_flag(){
		return $this->PAYMENT_FLAG;
	}

	protected function setPayment_flag($PAYMENT_FLAG){
		$this->PAYMENT_FLAG=$PAYMENT_FLAG;
	}

	protected function getPayment_paid(){
		return $this->PAYMENT_PAID;
	}

	protected function setPayment_paid($PAYMENT_PAID){
		$this->PAYMENT_PAID=$PAYMENT_PAID;
	}

	protected function getPayment_ref_number(){
		return $this->PAYMENT_REF_NUMBER;
	}

	protected function setPayment_ref_number($PAYMENT_REF_NUMBER){
		$this->PAYMENT_REF_NUMBER=$PAYMENT_REF_NUMBER;
	}

	protected function getPayment_bank_code(){
		return $this->PAYMENT_BANK_CODE;
	}

	protected function setPayment_bank_code($PAYMENT_BANK_CODE){
		$this->PAYMENT_BANK_CODE=$PAYMENT_BANK_CODE;
	}

	protected function getPayment_sw_refnum(){
		return $this->PAYMENT_SW_REFNUM;
	}

	protected function setPayment_sw_refnum($PAYMENT_SW_REFNUM){
		$this->PAYMENT_SW_REFNUM=$PAYMENT_SW_REFNUM;
	}

	protected function getPayment_gw_refnum(){
		return $this->PAYMENT_GW_REFNUM;
	}

	protected function setPayment_gw_refnum($PAYMENT_GW_REFNUM){
		$this->PAYMENT_GW_REFNUM=$PAYMENT_GW_REFNUM;
	}

	protected function getPayment_sw_id(){
		return $this->PAYMENT_SW_ID;
	}

	protected function setPayment_sw_id($PAYMENT_SW_ID){
		$this->PAYMENT_SW_ID=$PAYMENT_SW_ID;
	}

	protected function getPayment_merchant_code(){
		return $this->PAYMENT_MERCHANT_CODE;
	}

	protected function setPayment_merchant_code($PAYMENT_MERCHANT_CODE){
		$this->PAYMENT_MERCHANT_CODE=$PAYMENT_MERCHANT_CODE;
	}

	protected function getPayment_settlement_date(){
		return $this->PAYMENT_SETTLEMENT_DATE;
	}

	protected function setPayment_settlement_date($PAYMENT_SETTLEMENT_DATE){
		$this->PAYMENT_SETTLEMENT_DATE=$PAYMENT_SETTLEMENT_DATE;
	}

	protected function getPbb_collectible(){
		return $this->PBB_COLLECTIBLE;
	}

	protected function setPbb_collectible($PBB_COLLECTIBLE){
		$this->PBB_COLLECTIBLE=$PBB_COLLECTIBLE;
	}

	protected function getPbb_denda(){
		return $this->PBB_DENDA;
	}

	protected function setPbb_denda($PBB_DENDA){
		$this->PBB_DENDA=$PBB_DENDA;
	}

	protected function getPbb_admin_gw(){
		return $this->PBB_ADMIN_GW;
	}

	protected function setPbb_admin_gw($PBB_ADMIN_GW){
		$this->PBB_ADMIN_GW=$PBB_ADMIN_GW;
	}

	protected function getPbb_misc_fee(){
		return $this->PBB_MISC_FEE;
	}

	protected function setPbb_misc_fee($PBB_MISC_FEE){
		$this->PBB_MISC_FEE=$PBB_MISC_FEE;
	}

	protected function getPbb_total_bayar(){
		return $this->PBB_TOTAL_BAYAR;
	}

	protected function setPbb_total_bayar($PBB_TOTAL_BAYAR){
		$this->PBB_TOTAL_BAYAR=$PBB_TOTAL_BAYAR;
	}

	protected function getOp_alamat(){
		return $this->OP_ALAMAT;
	}

	protected function setOp_alamat($OP_ALAMAT){
		$this->OP_ALAMAT=$OP_ALAMAT;
	}

	protected function getOp_rt(){
		return $this->OP_RT;
	}

	protected function setOp_rt($OP_RT){
		$this->OP_RT=$OP_RT;
	}

	protected function getOp_rw(){
		return $this->OP_RW;
	}

	protected function setOp_rw($OP_RW){
		$this->OP_RW=$OP_RW;
	}

	protected function getOp_kelurahan(){
		return $this->OP_KELURAHAN;
	}

	protected function setOp_kelurahan($OP_KELURAHAN){
		$this->OP_KELURAHAN=$OP_KELURAHAN;
	}

	protected function getOp_kecamatan(){
		return $this->OP_KECAMATAN;
	}

	protected function setOp_kecamatan($OP_KECAMATAN){
		$this->OP_KECAMATAN=$OP_KECAMATAN;
	}

	protected function getOp_kotakab(){
		return $this->OP_KOTAKAB;
	}

	protected function setOp_kotakab($OP_KOTAKAB){
		$this->OP_KOTAKAB=$OP_KOTAKAB;
	}

	protected function getOp_kelurahan_kode(){
		return $this->OP_KELURAHAN_KODE;
	}

	protected function setOp_kelurahan_kode($OP_KELURAHAN_KODE){
		$this->OP_KELURAHAN_KODE=$OP_KELURAHAN_KODE;
	}

	protected function getOp_kecamatan_kode(){
		return $this->OP_KECAMATAN_KODE;
	}

	protected function setOp_kecamatan_kode($OP_KECAMATAN_KODE){
		$this->OP_KECAMATAN_KODE=$OP_KECAMATAN_KODE;
	}

	protected function getOp_kotakab_kode(){
		return $this->OP_KOTAKAB_KODE;
	}

	protected function setOp_kotakab_kode($OP_KOTAKAB_KODE){
		$this->OP_KOTAKAB_KODE=$OP_KOTAKAB_KODE;
	}

	protected function getOp_provinsi_kode(){
		return $this->OP_PROVINSI_KODE;
	}

	protected function setOp_provinsi_kode($OP_PROVINSI_KODE){
		$this->OP_PROVINSI_KODE=$OP_PROVINSI_KODE;
	}

	protected function getTgl_stpd(){
		return $this->TGL_STPD;
	}

	protected function setTgl_stpd($TGL_STPD){
		$this->TGL_STPD=$TGL_STPD;
	}

	protected function getTgl_sp1(){
		return $this->TGL_SP1;
	}

	protected function setTgl_sp1($TGL_SP1){
		$this->TGL_SP1=$TGL_SP1;
	}

	protected function getTgl_sp2(){
		return $this->TGL_SP2;
	}

	protected function setTgl_sp2($TGL_SP2){
		$this->TGL_SP2=$TGL_SP2;
	}

	protected function getTgl_sp3(){
		return $this->TGL_SP3;
	}

	protected function setTgl_sp3($TGL_SP3){
		$this->TGL_SP3=$TGL_SP3;
	}

	protected function getStatus_sp(){
		return $this->STATUS_SP;
	}

	protected function setStatus_sp($STATUS_SP){
		$this->STATUS_SP=$STATUS_SP;
	}

	protected function getStatus_cetak(){
		return $this->STATUS_CETAK;
	}

	protected function setStatus_cetak($STATUS_CETAK){
		$this->STATUS_CETAK=$STATUS_CETAK;
	}

	protected function getWp_pekerjaan(){
		return $this->WP_PEKERJAAN;
	}

	protected function setWp_pekerjaan($WP_PEKERJAAN){
		$this->WP_PEKERJAAN=$WP_PEKERJAAN;
	}

	protected function getPayment_offline_user_id(){
		return $this->PAYMENT_OFFLINE_USER_ID;
	}

	protected function setPayment_offline_user_id($PAYMENT_OFFLINE_USER_ID){
		$this->PAYMENT_OFFLINE_USER_ID=$PAYMENT_OFFLINE_USER_ID;
	}

	protected function getPayment_offline_flag(){
		return $this->PAYMENT_OFFLINE_FLAG;
	}

	protected function setPayment_offline_flag($PAYMENT_OFFLINE_FLAG){
		$this->PAYMENT_OFFLINE_FLAG=$PAYMENT_OFFLINE_FLAG;
	}

	protected function getPayment_offline_paid(){
		return $this->PAYMENT_OFFLINE_PAID;
	}

	protected function setPayment_offline_paid($PAYMENT_OFFLINE_PAID){
		$this->PAYMENT_OFFLINE_PAID=$PAYMENT_OFFLINE_PAID;
	}

	protected function getId_wp(){
		return $this->ID_WP;
	}

	protected function setId_wp($ID_WP){
		$this->ID_WP=$ID_WP;
	}
	
    public function getTagihanSPPT($pConn, $pNOP, $pTahunPajak){
		$tQuery = "SELECT PAYMENT_FLAG, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_JATUH_TEMPO
                    FROM PBB_SPPT WHERE NOP = '{$pNOP}' AND SPPT_TAHUN_PAJAK = '{$pTahunPajak}' AND PAYMENT_FLAG= '0'";					

        try {
            $stmt = $pConn->prepare($tQuery);
            $stmt->execute();
            
            $tData = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
                $tData = array(
                    'statusPembayaran'=>$row['PAYMENT_FLAG'],
                    'tagihanPajak'=>$row['SPPT_PBB_HARUS_DIBAYAR'],
                    'tanggalJatuhTempo'=>$row['SPPT_TANGGAL_JATUH_TEMPO']
                );
            }
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $tData;
    }    			

    public function getDaftarTagihanSPPT($pConn, $pNOP){
		$tQuery = "SELECT SPPT_TAHUN_PAJAK, PAYMENT_FLAG, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_JATUH_TEMPO
                    FROM PBB_SPPT WHERE NOP = '{$pNOP}' AND PAYMENT_FLAG= '0'";		
                    
        try {
            $stmt = $pConn->prepare($tQuery);
            $stmt->execute();
            
            $tAllData = array();
            $tData = array();
            $tCtr = 0;
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
                $tData = array(
                    'tahunPajak'=>$row['SPPT_TAHUN_PAJAK'],
                    'statusPembayaran'=>$row['PAYMENT_FLAG'],
                    'tagihanPajak'=>$row['SPPT_PBB_HARUS_DIBAYAR'],
                    'tanggalJatuhTempo'=>$row['SPPT_TANGGAL_JATUH_TEMPO']
                );
                $tAllData[$tCtr++] = $tData;
            }
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $tAllData;
    }    			    
    
    public function getRealisasiSPPT($pConn, $pTahunPajak, $pTahunBayar, $pBulanBayar){
        if($pTahunPajak == ''){
            $where = " WHERE EXTRACT(YEAR FROM PAYMENT_PAID) = '{$pTahunBayar}' AND EXTRACT(MONTH FROM PAYMENT_PAID) = '{$pBulanBayar}' AND PAYMENT_FLAG = '1'";            
        }else{
            $where = " WHERE SPPT_TAHUN_PAJAK = '{$pTahunPajak}' AND EXTRACT(YEAR FROM PAYMENT_PAID) = '{$pTahunBayar}' AND EXTRACT(MONTH FROM PAYMENT_PAID) = '{$pBulanBayar}' AND PAYMENT_FLAG = '1'";            
        }
        
		$tQuery = "SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) AS BAYAR, SUM(PBB_DENDA) AS DENDA, SUM(PBB_TOTAL_BAYAR) AS TOTALBAYAR FROM PBB_SPPT ".$where;					

        try {
            $stmt = $pConn->prepare($tQuery);
            $stmt->execute();
            
            $tData = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
                $tData = array(
                    'pendapatan'=>$row['BAYAR'],
                    'denda'=>$row['DENDA'],
                    'totalPendapatan'=>$row['TOTALBAYAR']
                );
            }
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $tData;
    }    			    
}
?>
