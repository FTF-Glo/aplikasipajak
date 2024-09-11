<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'nop'.DIRECTORY_SEPARATOR.'wp', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
	if(!empty($_POST['wpid'])){
		
		$data = getNop($_POST['wpid']);
		// print_r($data);

		if(!$data){
            $respon['respon'] = false;
		}else{
			$respon['respon'] = $data;
		}
		echo json_encode($respon);exit;
    }else{
        echo "No Action!";
    }
	
	function getNop($wpid){
		global $DBLink;
		$sql = "SELECT CPM_NOP, CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_sppt_final WHERE CPM_WP_NO_KTP = '".$_POST['wpid']."'
					UNION
					SELECT CPM_NOP, CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_sppt WHERE CPM_WP_NO_KTP = '".$_POST['wpid']."'
					UNION
					SELECT CPM_NOP, CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION FROM cppmod_pbb_sppt_susulan WHERE CPM_WP_NO_KTP = '".$_POST['wpid']."'";
		
		$res 	= mysqli_query($DBLink, $sql);
		$data = array();
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$i]["CPM_NOP"]				= $row["CPM_NOP"];
			$data[$i]["CPM_SPPT_DOC_ID"]		= $row["CPM_SPPT_DOC_ID"];
			$data[$i]["CPM_SPPT_DOC_VERSION"]	= $row["CPM_SPPT_DOC_VERSION"];
			$i++;
		}
		return $data;
	}
?>