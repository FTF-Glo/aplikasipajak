<?php

    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'keberatan', '', dirname(__FILE__))).'/';
   require_once($sRootPath . "inc/payment/constant.php");
    require_once($sRootPath . "inc/payment/comm-central.php");
    require_once($sRootPath . "inc/payment/inc-payment-c.php");
    require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
    require_once($sRootPath . "inc/payment/prefs-payment.php");
    require_once($sRootPath . "inc/payment/db-payment.php");
    require_once($sRootPath . "inc/check-session.php");
    require_once($sRootPath . "inc/payment/json.php");
    require_once($sRootPath . "inc/payment/sayit.php");
    require_once($sRootPath . "inc/central/user-central.php");
    require_once($sRootPath . "inc/central/dbspec-central.php");
    require_once($sRootPath . "function/PBB/gwlink.php");
    //require_once($sRootPath . "function/PBB/gwlink2.php");

    require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
    require_once($sRootPath . "inc/PBB/dbServices.php");
    require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
    require_once($sRootPath . "inc/PBB/dbUtils.php");



    $C_HOST_PORT = $_REQUEST['C_HOST_PORT'];
    $C_USER = $_REQUEST['C_USER'];
    $C_PWD = $_REQUEST['C_PWD'];
    $C_DB = $_REQUEST['C_DB'];
    $appConfig['minimum_njoptkp'] = $_REQUEST['minimum_njoptkp'];
    $appConfig['minimum_sppt_pbb_terhutang'] = $_REQUEST['minimum_sppt_pbb_terhutang'];
    
	$tahun = $_REQUEST['tahun'];
    
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

       // by 35utech 
    $a = "aPBB";
    $User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
    $appConfig_sw  = $User->GetAppConfig($a);
    // var_dump($appConfig_sw);
    
    // end by 35utech 


    $dbSpec = new SCANCentralDbSpecific('', '', $DBLink);
    $dbUtils = new DbUtils($dbSpec);
    
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
        global $DBLink, $appConfig, $dbUtils,$appConfig_sw;

        // $sid 		  			= $_REQUEST['spop'];
        // $tahun 		 	 		= $_REQUEST['tahun'];
        // $nop 		 	 		= $_REQUEST['nop'];
        // $vObjection             = array();
        // $getObjection 			= getObjection($sid);
        
        // $njopBumi       = $njopBangunan   = 0;
        // $luasBumi       = ($getObjection['CPM_OB_LUAS_TANAH_APP'] != NULL)? $getObjection['CPM_OB_LUAS_TANAH_APP']:$getObjection['CPM_OB_LUAS_TANAH'];
        // $luasBangunan       = ($getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != NULL)? $getObjection['CPM_OB_LUAS_BANGUNAN_APP']:$getObjection['CPM_OB_LUAS_BANGUNAN'];
        // if($luasBumi > 0) $njopBumi       = ($getObjection['CPM_OB_NJOP_TANAH_APP'] / $luasBumi) /1000;
        // if($luasBangunan > 0) $njopBangunan   = ($getObjection['CPM_OB_NJOP_BANGUNAN_APP'] / $luasBangunan) /1000;
        
        // $dataKlsTanah   = getKlsTanah($njopBumi, $tahun);
        // $dataKlsBng		= getKlsBangunan($njopBangunan, $tahun);
        
        
        // if($getObjection['CPM_OB_ZNT_CODE'] != NULL) 
        //     $vPBBSPPT['CPM_OT_ZONA_NILAI'] = $getObjection['CPM_OB_ZNT_CODE'];
        // if($getObjection['CPM_OB_LUAS_TANAH_APP'] != NULL)
        //     $vPBBSPPT['CPM_OP_LUAS_TANAH'] = $getObjection['CPM_OB_LUAS_TANAH_APP'];
        // if($getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != NULL)
        //     $vPBBSPPT['CPM_OP_LUAS_BANGUNAN'] = $getObjection['CPM_OB_LUAS_BANGUNAN_APP'];

        // $vPBBSPPT['CPM_NJOP_BANGUNAN'] = $getObjection['CPM_OB_NJOP_BANGUNAN_APP'];
        // $vPBBSPPT['CPM_NJOP_TANAH'] = $getObjection['CPM_OB_NJOP_TANAH_APP'];
            
        // $vPBBSPPT['CPM_OP_KELAS_TANAH']         = $dataKlsTanah->CPM_KELAS;
        // $vPBBSPPT['CPM_OP_KELAS_BANGUNAN']      = $dataKlsBng->CPM_KELAS;
        
        
        // $bOK = updateSPPTFinal($nop,$vPBBSPPT);
        // if(!$bOK) return false;


        
        // $penetapan = $dbUtils->selectPenetapan($nop, $appConfig,'');
        // // var_dump($penetapan);
        // // exit;
        // if(!$penetapan) return false;


        
        // if($getObjection['CPM_OB_LUAS_BANGUNAN_APP']!= NULL && $getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != $getObjection['CPM_OB_LUAS_BANGUNAN']){
        //     $vExt['CPM_PAYMENT_INDIVIDU'] = $getObjection['CPM_OB_NJOP_BANGUNAN_APP']/1000;
        //     $vExt['CPM_OP_LUAS_BANGUNAN'] = $penetapan['CPM_OP_LUAS_BANGUNAN'];
        //     $bOK = updateSPPTFinalExt($nop,$vExt);

        //     if(!$bOK) return false;
        //     // var_dump($bOK);
        //     // exit;  
        // }
       
        // $vPBBSPPT = array();
        // $vPBBSPPT['OP_LUAS_BUMI']               = $penetapan['CPM_OP_LUAS_TANAH'];
        // $vPBBSPPT['OP_LUAS_BANGUNAN']           = $penetapan['CPM_OP_LUAS_BANGUNAN'];
        // $vPBBSPPT['OP_KELAS_BUMI']              = $penetapan['CPM_OP_KELAS_TANAH'];
        // $vPBBSPPT['OP_KELAS_BANGUNAN']          = $penetapan['CPM_OP_KELAS_BANGUNAN'];
        // $vPBBSPPT['OP_NJOP_BUMI']               = $penetapan['CPM_NJOP_TANAH'];
        // $vPBBSPPT['OP_NJOP_BANGUNAN']           = $penetapan['CPM_NJOP_BANGUNAN'];        
        // $vPBBSPPT['OP_NJOP']                    = $penetapan['OP_NJOP'];
        // $vPBBSPPT['OP_NJKP']                    = $penetapan['OP_NJKP'];
        // $vPBBSPPT['OP_NJOPTKP']                 = $penetapan['OP_NJOPTKP'];
        // $vPBBSPPT['OP_TARIF']                   = $penetapan['OP_TARIF'];
        // $vPBBSPPT['SPPT_PBB_HARUS_DIBAYAR']     = $penetapan['SPPT_PBB_HARUS_DIBAYAR'];
        // $vPBBSPPT['SPPT_PBB_PENGURANGAN']       = "0";
        // $vPBBSPPT['SPPT_PBB_PERSEN_PENGURANGAN']= "0";
                
        // $bOK = updateGatewayCurrent($nop,$vPBBSPPT);

        // if(!$bOK) return false;
        
        // #############################################
        // ############Proses Update PBB_SPPT###########
        // unset($vPBBSPPT['OP_TARIF']);
        // unset($vPBBSPPT['SPPT_PBB_PENGURANGAN']);
        // unset($vPBBSPPT['SPPT_PBB_PERSEN_PENGURANGAN']);
        // $bOK = updateGateWayPBBSPPT($nop,$tahun,$vPBBSPPT);
    

        // if(!$bOK) return false;
        // var_dump($appConfig['ADMIN_SW_DBNAME']);
        // exit;
        // mysql_select_db($appConfig_sw['ADMIN_SW_DBNAME']);
        
        #Update No SK dan Tanggal SK
        $sql = "UPDATE {$appConfig_sw[ADMIN_SW_DBNAME]}.cppmod_pbb_service_objection SET CPM_OB_SK_NUMBER= '".$_REQUEST['nomorSK']."', CPM_OB_SK_DATE = '".$_REQUEST['tanggalSK']."' WHERE CPM_OB_SID='".$_REQUEST['spop']."'";
        
        // var_dump($sql);
        // exit;
        $bOK = mysqli_query($DBLink, $sql);
        // var_dump($bOK);
        //     exit;

        if(!$bOK) return false;
        

        return true;
        #############################################
        
   }
   
   function getObjection($sid){
        global $DBLink;
        $qGetObjection = "SELECT CPM_OB_ID, CPM_OB_SID, CPM_OB_ZNT_CODE, CPM_OB_LUAS_TANAH, CPM_OB_NJOP_TANAH, CPM_OB_KELAS_TANAH, CPM_OB_NJOP_TANAH_APP, CPM_OB_LUAS_BANGUNAN, CPM_OB_NJOP_BANGUNAN, CPM_OB_KELAS_BANGUNAN, CPM_OB_LUAS_TANAH_APP, CPM_OB_LUAS_BANGUNAN_APP, CPM_OB_NJOP_BANGUNAN_APP FROM cppmod_pbb_service_objection WHERE CPM_OB_SID = '$sid'";
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

        $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun' AND (PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1') ";
        
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
		// echo $query;
        
        return  mysqli_query($DBLink, $query);
    }

    function updateSPPTFinal($nop, $aValue) 
    {
           
        global $DBLink;

        $last_key = end(array_keys($aValue));
        $query = "UPDATE cppmod_pbb_sppt_final SET ";

        foreach ($aValue as $key => $value) {
                $query .= "$key='".mysql_real_escape_string($value)."'";
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
    
    function updateSPPTFinalExt($nop, $aValue) 
    {
        global $DBLink;
        
        $querySelect = "SELECT X.CPM_SPPT_DOC_ID,Y.CPM_OP_NUM FROM cppmod_pbb_sppt_final X, cppmod_pbb_sppt_ext_final Y
        WHERE X.CPM_SPPT_DOC_ID=Y.CPM_SPPT_DOC_ID AND X.CPM_NOP='$nop' AND Y.CPM_OP_NUM <> '' ORDER BY Y.CPM_OP_NUM LIMIT 0,1";
        // echo $querySelect;
        // exit;
        $res = mysqli_query($DBLink, $querySelect);
        if(!$res){
                echo mysqli_error($DBLink);
                echo $qGetObjection;
                return false;
        }
        $data = mysqli_fetch_array($res);	
        if(!empty($data)) {
            $queryUpdate = "UPDATE cppmod_pbb_sppt_ext_final SET CPM_OP_LUAS_BANGUNAN='0', CPM_PAYMENT_PENILAIAN_BGN='individu',CPM_PAYMENT_INDIVIDU='0' 
            WHERE CPM_SPPT_DOC_ID='".$data['CPM_SPPT_DOC_ID']."'";
            
            $bOK = mysqli_query($DBLink, $queryUpdate);
            if(!$bOK) return false;
            
            $queryUpdate = "UPDATE cppmod_pbb_sppt_ext_final SET CPM_OP_LUAS_BANGUNAN='".$aValue['CPM_OP_LUAS_BANGUNAN']."', CPM_PAYMENT_INDIVIDU='".$aValue['CPM_PAYMENT_INDIVIDU']."'
            WHERE CPM_SPPT_DOC_ID='".$data['CPM_SPPT_DOC_ID']."' AND CPM_OP_NUM='".$data['CPM_OP_NUM']."'";

            return mysqli_query($DBLink, $queryUpdate);
        }else{
            return true;
        }

        
    }

    function getKlsTanah($njop, $thnTagihan){
            global $data,$DBLink;	

            $query = "SELECT * FROM cppmod_pbb_kelas_bumi WHERE CPM_NILAI_BAWAH < $njop AND CPM_NILAI_ATAS >= $njop and CPM_THN_AWAL <= '".$thnTagihan."' AND CPM_THN_AKHIR >= '".$thnTagihan."' AND CPM_KELAS <> 'XXX'";
            $res = mysqli_query($DBLink, $query);
            $json = new Services_JSON();

            $dataKls =  $json->decode(mysql2json($res,"data"));	
            if(isset($dataKls->data[0])) return $dataKls->data[0];
            else return $json->decode("{ 'data': [ { 'CPM_KELAS' : 'XXX', 'CPM_THN_AWAL' : '2011', 'CPM_THN_AKHIR' : '9999', 'CPM_NILAI_BAWAH' : '0', 'CPM_NILAI_ATAS' : '999999', 'CPM_NJOP_M2' : '0' } ] }")->data[0];

    }

    function getKlsBangunan($njop, $thnTagihan){
            global $data,$DBLink;	
            $query = "SELECT * FROM cppmod_pbb_kelas_bangunan WHERE CPM_NILAI_BAWAH < $njop AND CPM_NILAI_ATAS >= $njop and CPM_THN_AWAL <= '".$thnTagihan."' AND CPM_THN_AKHIR >= '".$thnTagihan."' AND CPM_KELAS <> 'XXX'";

            $res = mysqli_query($DBLink, $query);
            $json = new Services_JSON();

            $dataKls =  $json->decode(mysql2json($res,"data"));
            if(isset($dataKls->data[0])) return $dataKls->data[0];
            else return $json->decode("{ 'data': [ { 'CPM_KELAS' : 'XXX', 'CPM_THN_AWAL' : '2011', 'CPM_THN_AKHIR' : '9999', 'CPM_NILAI_BAWAH' : '0', 'CPM_NILAI_ATAS' : '999999', 'CPM_NJOP_M2' : '0' } ] }")->data[0];
    }
    
    function mysql2json($mysql_result,$name){
        $json="{\n'$name': [\n";
        $field_names = array();
        $fields = mysqli_num_fields($mysql_result);
        for($x=0;$x<$fields;$x++){
            $field_name = mysqli_fetch_field($mysql_result);
            if($field_name){
                $field_names[$x]=$field_name->name;
            }
        }
        $rows = mysqli_num_rows($mysql_result);
        for($x=0;$x<$rows;$x++){
            $row = mysqli_fetch_array($mysql_result);
            $json.="{\n";
            for($y=0;$y<count($field_names);$y++) {
                $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
                if($y==count($field_names)-1){
                        $json.="\n";
                }
                else{
                        $json.=",\n";
                }
            }
            if($x==$rows-1){
                $json.="\n}\n";
            }
            else{
                $json.="\n},\n";
            }
        }
        $json.="]\n}";
        return($json);
    }
?>