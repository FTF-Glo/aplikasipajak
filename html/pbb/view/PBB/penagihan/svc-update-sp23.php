<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/uuid.php");

    //akses database gateway devel
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_POST['dbhost'],$_POST['dbuser'],$_POST['dbpwd'],$_POST['dbname']);
	
    $nop_array 	= (isset($_REQUEST['nop_array']) && $_REQUEST['nop_array']!='') ? $_REQUEST['nop_array'] : false;
	$nop 		= $_POST['nop'];
	// $listTahun 	= $_POST['listTahun'];
	$sts		= $_POST['sts'];
	// $arrTahun	= explode(',',$listTahun);
	$respon		= array();

    if(!$nop_array){
        $totalBulanPajak = 24;
        $denda = 2;
        if(!empty($nop) && !empty($_POST['dbhost']) && !empty($_POST['dbuser']) && !empty($_POST['dbpwd']) && !empty($_POST['dbname'])){
            $arrTagihan = getTagihan($nop);
            $res1 = true;
            if($res1){
                if($sts == 2) {
                    $sql2  = "UPDATE PBB_SPPT_PENAGIHAN SET TGL_SP2 = now(), STATUS_SP = 0, TAHUN_SP2 = '{$arrTagihan['tahun']}', KETETAPAN_SP2 = '{$arrTagihan['total']}', STATUS_PERSETUJUAN = 0 WHERE NOP = $nop";
                } else if($sts == 3){
                    $sql2  = "UPDATE PBB_SPPT_PENAGIHAN SET TGL_SP3 = now(), STATUS_SP = 0, TAHUN_SP3 = '{$arrTagihan['tahun']}', KETETAPAN_SP3 = '{$arrTagihan['total']}', STATUS_PERSETUJUAN = 0 WHERE NOP = $nop";
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
    }else{
        $nop_array = explode(',',$nop_array);
		foreach ($nop_array as $r) {
			$np = explode('_',$r);
			$nop = $np[0];
			$thn = $np[1];
			$arrTagihan = getTagihan($nop);
			if($sts == 2) {
                $sql0  = "UPDATE PBB_SPPT_PENAGIHAN SET TGL_SP2 = now(), STATUS_SP = 0, TAHUN_SP2 = '{$arrTagihan['tahun']}', KETETAPAN_SP2 = '{$arrTagihan['total']}', STATUS_PERSETUJUAN = 0 WHERE NOP = $nop";
            } else if($sts == 3){
                $sql0  = "UPDATE PBB_SPPT_PENAGIHAN SET TGL_SP3 = now(), STATUS_SP = 0, TAHUN_SP3 = '{$arrTagihan['tahun']}', KETETAPAN_SP3 = '{$arrTagihan['total']}', STATUS_PERSETUJUAN = 0 WHERE NOP = $nop";
            }
            $res0  = mysqli_query($DBLinkLookUp, $sql0);
			
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