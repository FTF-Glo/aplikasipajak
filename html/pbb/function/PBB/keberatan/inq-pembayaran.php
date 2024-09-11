<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
    
    $C_HOST_PORT = $_REQUEST['C_HOST_PORT'];
    $C_USER = $_REQUEST['C_USER'];
    $C_PWD = $_REQUEST['C_PWD'];
    $C_DB = $_REQUEST['C_DB'];
    
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
    if(!empty($_REQUEST['nspop']) && !empty($_REQUEST['nomorSK']) && !empty($_REQUEST['tanggalSK']) && !empty($_REQUEST['nop']) && !empty($_REQUEST['tahun'])){
        
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

        $nop 		= $_REQUEST['nop'];
        $tahun 		= $_REQUEST['tahun'];
        $vCurrent 	= array();
        //$vPBBSPPT 	= array();
        ##########Proses Update Current#############
        #Ambil data pengurangan
        $getReduce = getReduce($_REQUEST['nop']);
        
        #Update Current
        $vCurrent['SPPT_PBB_PERSEN_PENGURANGAN'] 	= $getReduce['CPM_PNG_PERSEN'];
        $vCurrent['SPPT_PBB_PENGURANGAN']			= $getReduce['CPM_PNG_NILAI'];
        $vCurrent['SPPT_PBB_HARUS_DIBAYAR']			= $getReduce['CPM_SPPT_DUE'] - $getReduce['CPM_PNG_NILAI'];
        $bOK = updateGatewayCurrent($nop,$vCurrent);
        if(!$bOK) return false;

        #############################################
        ############Proses Update PBB_SPPT###########
        $vPBBSPPT['SPPT_PBB_HARUS_DIBAYAR'] = $vCurrent['SPPT_PBB_HARUS_DIBAYAR'];
        $bOK = updateGateWayPBBSPPT($nop,$tahun,$vPBBSPPT);
        
        if(!$bOK) return false;
        
        #Update No SK dan Tanggal SK
        $sql = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_SK_NUMBER= '".$_REQUEST['nomorSK']."', CPM_RE_SK_DATE = '".$_REQUEST['tanggalSK']."'WHERE CPM_RE_SID='".$_REQUEST['nspop']."'";
        $bOK = mysqli_query($DBLink, $sql);
        if(!$bOK) return false;
        
        return true;
        #############################################
        
   }
   function getReduce($nop){
            global $DBLink;
            $qGetReduce = "SELECT * FROM cppmod_pbb_services a, cppmod_pbb_sppt_pengurangan b WHERE a.CPM_OP_NUMBER = b.CPM_PNG_NOP AND CPM_OP_NUMBER = '$nop'";
            $res = mysqli_query($DBLink, $qGetReduce);
            if(!$res){
                    echo mysqli_error($DBLink);
                    echo $qGetReduce;
            }
            return mysqli_fetch_array($res);	
   }
   
   function updateGateWayPBBSPPT($nop, $tahun, $aValue) {
            global $DBLink, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB;
            
            $LDBLink = mysqli_connect($C_HOST_PORT,$C_USER,$C_PWD,$C_DB) or die(mysqli_error($DBLink));
            //mysql_select_db($C_DB,$LDBLink);

            $last_key = end(array_keys($aValue));
            $query = "UPDATE pbb_sppt SET ";

            foreach ($aValue as $key => $value) {
                    $query .= "$key='$value'";
                    if ($key != $last_key) {
                            $query .= ", ";
                    }
            }

            $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";
            
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
?>