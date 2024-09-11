<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/uuid.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    //akses database gateway devel
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_REQUEST['dbhost'],$_REQUEST['dbuser'],$_REQUEST['dbpwd'],$_REQUEST['dbname']);
	
	$nop_array 	= (isset($_REQUEST['nop_array']) && $_REQUEST['nop_array']!='') ? $_REQUEST['nop_array'] : false;
	$uuid		= c_uuid();
	$respon		= array();

	if(!$nop_array){
		$nop 		= $_REQUEST['nop'];
		$listTahun 	= $_REQUEST['listTahun'];
		$thn        = $_REQUEST['thn'];
		//print_r($listTahun);exit;
		$arrTahun	= explode(',',$listTahun);
		
		$totalBulanPajak = 24;
		$denda = 2;
		if(!empty($nop) && !empty($thn) && !empty($listTahun) && !empty($_REQUEST['dbhost']) && !empty($_REQUEST['dbuser']) && !empty($_REQUEST['dbpwd']) && !empty($_REQUEST['dbname'])){
			
			// $listTagihan = array();
			// $sumTagihan = 0;
			// foreach($arrTahun as $tahun){
			// 	//INSERT INTO PBB_SPPT_TAHUN_PENAGIHAN
			// 	$sql1    	= "INSERT INTO PBB_SPPT_TAHUN_PENAGIHAN VALUES ('{$uuid}','{$nop}','{$tahun}')";
			// 	$res1    	= mysql_query($sql1,$DBLinkLookUp);
			// 	// $sumTagihan += getNilaiTagihan($nop,$tahun);	
			// 	$listTagihan[] = getNilaiTagihan($nop,$tahun);
			// }
			// $sumTagihan = array_sum($listTagihan);
			// $sumTagihan = getTotalTagihan($nop);
			$arrTagihan = getTagihan($nop);
			$res1 = true;
			if($res1) {
				$sql2  = "INSERT INTO PBB_SPPT_PENAGIHAN (ID, NOP, TAHUN, TGL_SP1, TAHUN_SP1, STATUS_SP, KETETAPAN_SP1) 
							VALUES ('{$uuid}','{$nop}','{$thn}',now(),'{$arrTagihan['tahun']}','0','{$arrTagihan['total']}')
					ON DUPLICATE KEY UPDATE TGL_SP1=now(), TAHUN_SP1='{$arrTagihan['tahun']}',KETETAPAN_SP1='{$arrTagihan['total']}';";
				$res2  = mysqli_query($DBLinkLookUp, $sql2);
			}
			if($res1 && $res2){
				$respon['totaltagihan'] = $sumTagihan;
				$respon['listtagihan']  = $listTagihan;
				$respon['respon'] 		= true;
				$respon['message'] 		= 'success';
				echo json_encode($respon);exit;
			} else {
				echo $sql1;
				echo $sql2;
				echo "Terjadi kesalahan query";
			}
		}else{
			echo "Missing nop parameter!";
		}
	}else{
		$nop_array = explode(',',$nop_array);
		foreach ($nop_array as $r) {
			$np = explode('_',$r);
			$nop = $np[0];
			$thn = $np[1];
			$arrTagihan = getTagihan($nop);
			$sql0 = "INSERT INTO PBB_SPPT_PENAGIHAN (ID, NOP, TAHUN, TGL_SP1, TAHUN_SP1, STATUS_SP, KETETAPAN_SP1) 
					VALUES ('{$uuid}','{$nop}','{$thn}',now(),'{$arrTagihan['tahun']}','0','{$arrTagihan['total']}')
					ON DUPLICATE KEY UPDATE TGL_SP1=now(), TAHUN_SP1='{$arrTagihan['tahun']}',KETETAPAN_SP1='{$arrTagihan['total']}';";
			$res0 = mysqli_query($DBLinkLookUp, $sql0);
			
			if(!$res0){
				echo $sql0;
				echo "Terjadi kesalahan query " . mysqli_error($DBLinkLookUp);
				exit;
			}
		}
		$respon['respon'] = true;
		echo json_encode($respon);
		exit;
	}
	
	function getNilaiTagihan($nop,$tahun) {
		global $DBLinkLookUp;	

		$qry = "SELECT SPPT_PBB_HARUS_DIBAYAR FROM PBB_SPPT WHERE NOP = '".$nop."' and SPPT_TAHUN_PAJAK = '$tahun'";
		 

		$res = mysqli_query($DBLinkLookUp, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['SPPT_PBB_HARUS_DIBAYAR'];
		}
	}
        
    function getTotalTagihan($nop) {
		global $DBLinkLookUp;	

		$qry = "SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) AS TOTALTAGIHAN FROM PBB_SPPT WHERE NOP = '".$nop."' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL)";
		

		$res = mysqli_query($DBLinkLookUp, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['TOTALTAGIHAN'];
		}
	}
        
    function getTagihan($nop) {
		global $DBLinkLookUp;	

		$qry = "SELECT SPPT_PBB_HARUS_DIBAYAR,SPPT_TAHUN_PAJAK,SPPT_TANGGAL_JATUH_TEMPO FROM PBB_SPPT WHERE NOP = '".$nop."' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ORDER BY SPPT_TAHUN_PAJAK";

		$res = mysqli_query($DBLinkLookUp, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
                $arrayResult = array();
                $total = 0;
                $tahun = array();
                
		while ($row = mysqli_fetch_assoc($res)) {
                    $denda = getPenalty($row['SPPT_PBB_HARUS_DIBAYAR'],$row['SPPT_TANGGAL_JATUH_TEMPO']);
                    $total += $row['SPPT_PBB_HARUS_DIBAYAR']+$denda;
                    $tahun[]= $row['SPPT_TAHUN_PAJAK'];
                    
                    
		}
                $arrayResult['total'] = $total;
                $arrayResult['tahun'] = join(',',$tahun);
                
                return $arrayResult;
	}

	function getPenalty($pbbHarusDibayar, $jatuhTempo){
		global $totalBulanPajak, $denda;
		
		$penalty = 0;

		$month = ceil(countDay($jatuhTempo,date('Y-m-d'))/30);
		if($month > $totalBulanPajak){
			$month = $totalBulanPajak;
		}
		$penalty = $denda * $month * $pbbHarusDibayar / 100;

		return round($penalty);
	}

	function countDay($s_day, $e_day){
		$startTimeStamp = strtotime($s_day);
		$endTimeStamp = strtotime($e_day);

		if($startTimeStamp > $endTimeStamp)
			return 0;

		$timeDiff = abs($endTimeStamp - $startTimeStamp);

		$numberDays = $timeDiff/86400;  // 86400 seconds in one day

		//convert to integer
		$numberDays = intval($numberDays);

		return $numberDays;
	}
?>