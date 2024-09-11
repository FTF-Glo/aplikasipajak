<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'keberatan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    $C_HOST_PORT = $_REQUEST['C_HOST_PORT'];
    $C_USER = $_REQUEST['C_USER'];
    $C_PWD = $_REQUEST['C_PWD'];
    $C_DB = $_REQUEST['C_DB'];
    $minimum_njoptkp = $_REQUEST['minimum_njoptkp'];
    $minimum_sppt_pbb_terhutang = $_REQUEST['minimum_sppt_pbb_terhutang'];
    
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
    if(!empty($_REQUEST['spop']) && !empty($_REQUEST['nomorSK']) && !empty($_REQUEST['tanggalSK']) && !empty($_REQUEST['nop']) && !empty($_REQUEST['tahun'])){

        $bOK = execute();
        if(!$bOK){
                $respon['respon'] = false;
				$respon['message'] = mysqli_error($DBLink);
        }else{
				$respon['respon'] = true;
                $respon['message'] = "sukses: ".$_REQUEST['spop'];
        }
		echo json_encode($respon);exit;
    }else{
            echo "No Action!";
    }
        
   function execute(){
        global $DBLink;

        $sid 		  			= $_REQUEST['spop'];
        $tahun 		 	 		= $_REQUEST['tahun'];
        $nop 		 	 		= $_REQUEST['nop'];
        $vObjection                             = array();
        $getObjection 				= getObjection($sid);
        
        $vObjection['CPM_OP_LUAS_TANAH']        = $getObjection['CPM_OB_LUAS_TANAH'];
        $vObjection['CPM_NJOP_TANAH']	  	= $getObjection['CPM_OB_NJOP_TANAH_APP'];
        $vObjection['CPM_NJOP_BANGUNAN']   	= $getObjection['CPM_OB_NJOP_BANGUNAN'];
        $tagihan                                = hitung($vObjection);
        if(!$tagihan) return false;
        
        $vPBBSPPT['CPM_OT_ZONA_NILAI']          = $getObjection['CPM_OB_ZNT_CODE'];
        $vPBBSPPT['CPM_OP_KELAS_TANAH']         = $getObjection['CPM_OB_KELAS_TANAH'];
        $vPBBSPPT['CPM_NJOP_TANAH']             = $tagihan['CPM_NJOP_TANAH'];
        $vPBBSPPT['CPM_OP_KELAS_TANAH']         = $tagihan['CPM_OP_KELAS_TANAH'];
        
        $bOK = updateSPPTFinal($nop,$vPBBSPPT);
        if(!$bOK) return false;
        
        $vPBBSPPT = array();
        $vPBBSPPT['OP_NJOP_BUMI']               = $tagihan['CPM_NJOP_TANAH'];
        $vPBBSPPT['OP_NJOP']                    = $tagihan['OP_NJOP'];
        $vPBBSPPT['OP_NJKP']                    = $tagihan['OP_NJKP'];
        $vPBBSPPT['OP_NJOPTKP']                 = $tagihan['OP_NJOPTKP'];
        $vPBBSPPT['OP_TARIF']                   = $tagihan['OP_TARIF'];
        $vPBBSPPT['SPPT_PBB_HARUS_DIBAYAR']     = $tagihan['SPPT_PBB_HARUS_DIBAYAR'];
        $vPBBSPPT['OP_KELAS_BUMI']              = $tagihan['CPM_OP_KELAS_TANAH'];
        $vPBBSPPT['SPPT_PBB_PENGURANGAN']       = "0";
        $vPBBSPPT['SPPT_PBB_PERSEN_PENGURANGAN']= "0";
                
        $bOK = updateGatewayCurrent($nop,$vPBBSPPT);
        if(!$bOK) return false;

        #############################################
        ############Proses Update PBB_SPPT###########
       $vPBBSPPT = array();
       $vPBBSPPT['OP_NJOP_BUMI']               = $tagihan['CPM_NJOP_TANAH'];
       $vPBBSPPT['OP_NJOP']                    = $tagihan['OP_NJOP'];
       $vPBBSPPT['OP_NJKP']                    = $tagihan['OP_NJKP'];
       $vPBBSPPT['OP_NJOPTKP']                 = $tagihan['OP_NJOPTKP'];
       $vPBBSPPT['SPPT_PBB_HARUS_DIBAYAR']     = $tagihan['SPPT_PBB_HARUS_DIBAYAR'];
       $bOK = updateGateWayPBBSPPT($nop,$tahun,$vPBBSPPT);
       if(!$bOK) return false;
        
        #Update No SK dan Tanggal SK
        $sql = "UPDATE cppmod_pbb_service_objection SET CPM_OB_SK_NUMBER= '".$_REQUEST['nomorSK']."', CPM_OB_SK_DATE = '".$_REQUEST['tanggalSK']."' WHERE CPM_OB_SID='".$_REQUEST['spop']."'";
        $bOK = mysqli_query($DBLink, $sql);
        if(!$bOK) return false;
        
        return true;
        #############################################
        
   }
   
   function getObjection($sid){
        global $DBLink;
        $qGetObjection = "SELECT CPM_OB_LUAS_TANAH, CPM_OB_NJOP_TANAH_APP, CPM_OB_LUAS_BANGUNAN, CPM_OB_NJOP_BANGUNAN, CPM_OB_ZNT_CODE, CPM_OB_KELAS_TANAH FROM cppmod_pbb_service_objection WHERE CPM_OB_SID = '$sid'";
        $res = mysqli_query($DBLink, $qGetObjection);
        if(!$res){
                echo mysqli_error($DBLink);
                echo $qGetObjection;
        }
        return mysqli_fetch_array($res);	
   }
   
   function updateGateWayPBBSPPT($nop, $tahun, $aValue) {
        global $DBLink, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB;

        $LDBLink = mysqli_connect($C_HOST_PORT,$C_USER,$C_PWD,$C_DB) or die(mysqli_error($DBLink));
        //mysql_select_db($C_DB,$LDBLink);

        $last_key = end(array_keys($aValue));
        $query = "UPDATE PBB_SPPT SET ";

        foreach ($aValue as $key => $value) {
                $query .= "$key='$value'";
                if ($key != $last_key) {
                        $query .= ", ";
                }
        }

        $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";
        // echo $query;exit;
        $bOK = mysqli_query($LDBLink, $query);

        mysqli_close($LDBLink);

        return $bOK;
    }

    function updateGatewayCurrent($nop, $aValue) 
    {
           
        global $DBLink;

        $last_key = end(array_keys($aValue));
        $query = "UPDATE cppmod_pbb_sppt_current SET ";

        foreach ($aValue as $key => $value) {
                $query .= "$key='$value'";
                if ($key != $last_key) {
                        $query .= ", ";
                }
        }

        $query .= " WHERE NOP='$nop'";
        
        return  mysqli_query($DBLink, $query);
    }

    function updateSPPTFinal($nop, $aValue) 
    {
           
        global $DBLink;

        $last_key = end(array_keys($aValue));
        $query = "UPDATE cppmod_pbb_sppt_final SET ";

        foreach ($aValue as $key => $value) {
                $query .= "$key='$value'";
                if ($key != $last_key) {
                        $query .= ", ";
                }
        }

        $query .= " WHERE CPM_NOP='$nop'";
        
        $bOK = mysqli_query($DBLink, $query);
        if(!$bOK) return false;
        
        $query =  str_replace ( "cppmod_pbb_sppt_final" , "cppmod_pbb_sppt_susulan" , $query);
        
        return mysqli_query($DBLink, $query);
    }
    
    function hitung($aValue) {
    
        global $DBLink, $minimum_njoptkp, $minimum_sppt_pbb_terhutang;
        $NJOP_TANAH_M2 = (int)($aValue['CPM_NJOP_TANAH']/$aValue['CPM_OP_LUAS_TANAH'])/1000;
        
        $query = "SELECT CPM_KELAS FROM cppmod_pbb_kelas_bumi WHERE CPM_NILAI_BAWAH <= $NJOP_TANAH_M2 AND CPM_NILAI_ATAS >= $NJOP_TANAH_M2 AND CPM_KELAS <> 'XXX'";
        
        $res = mysqli_query($DBLink, $query);
        if(!$res){
                echo mysqli_error($DBLink);
                echo $query;
                return false;
        }
        
        $dataKelas = mysqli_fetch_array($res);
        $aValue['CPM_OP_KELAS_TANAH'] = $dataKelas['CPM_KELAS'];
        
        $NJOPTKP = $minimum_njoptkp;
        $minPBBHarusBayar = $minimum_sppt_pbb_terhutang;


        $NJOP = $aValue['CPM_NJOP_TANAH'] + $aValue['CPM_NJOP_BANGUNAN'];
        
        if($NJOP > $NJOPTKP)
            $NJKP = $NJOP - $NJOPTKP;
        else $NJKP = 0;

        $aValue['OP_NJOP'] = $NJOP;
        $aValue['OP_NJKP'] = $NJKP;
        $aValue['OP_NJOPTKP'] = $NJOPTKP;

        $cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                        CPM_TRF_NILAI_BAWAH <= " . $NJKP . " AND
                        CPM_TRF_NILAI_ATAS >= " . $NJKP; 
        $resTarif = mysqli_query($DBLink, $cari_tarif);
        if(!$resTarif){
                echo mysqli_error($DBLink);
                echo $cari_tarif;
                return false;
        }
        
        $dataTarif = mysqli_fetch_array($resTarif);
        $op_tarif = $dataTarif['CPM_TRF_TARIF'];
        $aValue['OP_TARIF'] = $op_tarif;
        $PBB_HARUS_DIBAYAR = $NJKP * ($op_tarif / 100);

        if($PBB_HARUS_DIBAYAR < $minPBBHarusBayar)
            $PBB_HARUS_DIBAYAR = $minPBBHarusBayar;
        $aValue['SPPT_PBB_HARUS_DIBAYAR'] = number_format($PBB_HARUS_DIBAYAR,0,'','');
        
        return $aValue;
    }
?>