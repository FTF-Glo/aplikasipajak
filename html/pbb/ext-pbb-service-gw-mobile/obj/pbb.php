<?php
include_once 'pbb_sppt.php';

Class PBB{		
    static public function getTagihanSPPT($pConn){
		//get json data from form
        $tJson = file_get_contents('php://input');
        $tReq = json_decode($tJson);
        $tNOP = !empty($tReq->nop) ? trim($tReq->nop) : '';
        $tTahunPajak = !empty($tReq->thnpajak) ? trim($tReq->thnpajak) : '';

        try {
			$tPbbSppt = new PBB_SPPT;
			$tData = $tPbbSppt->getTagihanSPPT($pConn,$tNOP,$tTahunPajak);
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $tData;
    }

    static public function getDaftarTagihanSPPT($pConn){
		//get json data from form
        $tJson = file_get_contents('php://input');
        $tReq = json_decode($tJson);
        $tNOP = !empty($tReq->nop) ? trim($tReq->nop) : '';

        try {
			$tPbbSppt = new PBB_SPPT;
			$tData = $tPbbSppt->getDaftarTagihanSPPT($pConn,$tNOP);
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $tData;
    }
    
    static public function getRealisasiSPPT($pConn){
		//get json data from form
        $tJson = file_get_contents('php://input');
        $tReq = json_decode($tJson);
        
        $tTahunPajak = !empty($tReq->tahunPajak) ? trim($tReq->tahunPajak) : '';
        $tTahunBayar = !empty($tReq->tahunBayar) ? trim($tReq->tahunBayar) : '';
        $tBulanBayar = !empty($tReq->bulanBayar) ? trim($tReq->bulanBayar) : '';

        try {
			$tPbbSppt = new PBB_SPPT;
			$tData = $tPbbSppt->getRealisasiSPPT($pConn,$tTahunPajak,$tTahunBayar,$tBulanBayar);
        }catch(Exception $e) {
            echo 'Exception -> ';
            var_dump($e->getMessage());
            die();
        }
        return $tData;
    }

}
?>
