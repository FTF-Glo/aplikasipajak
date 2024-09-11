<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    //akses database gateway devel

    if(!empty($_REQUEST['nop']) && !empty($_REQUEST['dbhost']) && !empty($_REQUEST['dbuser']) && !empty($_REQUEST['dbpwd']) && !empty($_REQUEST['dbname'])){
        //akses database gateway devel
        SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_REQUEST['dbhost'],$_REQUEST['dbuser'],$_REQUEST['dbpwd'],$_REQUEST['dbname']);
		
		$nop = $_REQUEST['nop'];
		$tahunSudahSP1 = cekTahunSudahCetakSP1($nop);
		$tahunSudahSP1 = explode(",",$tahunSudahSP1);
		$respon['tahun']   = 0;
		$respon['tagihan'] = 0;
		
        // $sql 	   = "SELECT SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR FROM VIEW_SP1 WHERE NOP='".$nop."'";
		$sql = "SELECT A.SPPT_TAHUN_PAJAK AS SPPT_TAHUN_PAJAK,
				A.SPPT_PBB_HARUS_DIBAYAR AS SPPT_PBB_HARUS_DIBAYAR
				FROM PBB_SPPT A LEFT JOIN PBB_SPPT_TAHUN_PENAGIHAN B
				ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK
				WHERE A.NOP='".$nop."' AND (A.PAYMENT_FLAG != '1' OR A.PAYMENT_FLAG IS NULL) AND B.NOP IS NULL ORDER BY A.SPPT_TAHUN_PAJAK ";

				
                
        $res       = mysqli_query($DBLinkLookUp, $sql);
		$data = array();
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			// $data[$i] = $row["SPPT_TAHUN_PAJAK"];
			$data['SPPT_TAHUN_PAJAK'][$i] 		= $row["SPPT_TAHUN_PAJAK"];
			$data['SPPT_PBB_HARUS_DIBAYAR'][$i] = $row["SPPT_PBB_HARUS_DIBAYAR"];
			$i++;
		}

		$tahunBelumSP1		     = array_diff($data['SPPT_TAHUN_PAJAK'],$tahunSudahSP1);

		$respon['tahunbelumSP1'] = array_values($tahunBelumSP1);
        $respon['tahun'] 	     = $data['SPPT_TAHUN_PAJAK'];
		$respon['tagihan']		 = $data['SPPT_PBB_HARUS_DIBAYAR'];
		$respon['message'] 		 = 'success';
		$respon['dev']			 = $nop;
		echo json_encode($respon);
    }else{
        echo "Missing nop parameter!";
    }
	
	function cekTahunSudahCetakSP1($nop){
		global $DBLinkLookUp;
		$sql  = "SELECT TAHUN_SP1 FROM PBB_SPPT_PENAGIHAN WHERE NOP = $nop";
		$res  = mysqli_query($DBLinkLookUp, $sql);
		$data = mysqli_fetch_assoc($res);
		return $data['TAHUN_SP1']; 
	}
	
	
?>