<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_khusus_2016', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/uuid.php");
	require_once($sRootPath."inc/central/user-central.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$appConfig 	= $User->GetAppConfig('aPBB');
	$dbhost 	= $appConfig['GW_DBHOST'];
	$dbuser 	= $appConfig['GW_DBUSER'];
	$dbpwd 		= $appConfig['GW_DBPWD'];
	$dbname 	= $appConfig['GW_DBNAME'];
	//akses database gateway devel
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost,$dbuser,$dbpwd,$dbname);
	
	$nop 		= $_POST['nop'];
	$listTahun 	= $_POST['listTahun'];
	$sts		= $_POST['sts'];
	// $arrTahun	= explode(',',$listTahun);
	$respon		= array();
	
	$totalBulanPajak = 24;
    $denda = 2;
    if(!empty($nop)){
		$arrTagihan = getTagihan($nop, $listTahun);
                $res1 = true;
		if($res1){
			if($sts == 2) {
				$sql2  = "UPDATE PBB_SPPT_PENAGIHAN_KHUSUS SET TGL_SP2 = now(), STATUS_SP = 0, TAHUN_SP2 = '{$arrTagihan['tahun']}', KETETAPAN_SP2 = '{$arrTagihan['total']}', STATUS_PERSETUJUAN = 0 WHERE NOP = $nop";
			} else if($sts == 3){
				$sql2  = "UPDATE PBB_SPPT_PENAGIHAN_KHUSUS SET TGL_SP3 = now(), STATUS_SP = 0, TAHUN_SP3 = '{$arrTagihan['tahun']}', KETETAPAN_SP3 = '{$arrTagihan['total']}', STATUS_PERSETUJUAN = 0 WHERE NOP = $nop";
			}
			$res2  = mysqli_query($DBLinkLookUp, $sql2);
		}
		
		if($res2){
			$respon['respon'] 		= true;
			$respon['message'] 		= 'success';
			echo json_encode($respon);exit;
		} else {
			echo $sql2;
			echo "Terjadi kesalahan query";
		}
    }else{
        echo "Missing nop parameter!";
    }
	
	function getTagihan($nop, $listTahun) {
		global $DBLinkLookUp;	

		$qry = "SELECT SPPT_PBB_HARUS_DIBAYAR,SPPT_TAHUN_PAJAK,SPPT_TANGGAL_JATUH_TEMPO FROM PBB_SPPT WHERE NOP = '".$nop."' AND SPPT_TAHUN_PAJAK='".$listTahun."' AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) ORDER BY SPPT_TAHUN_PAJAK";

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