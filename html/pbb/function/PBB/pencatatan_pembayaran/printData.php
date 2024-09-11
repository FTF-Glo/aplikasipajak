<?php 
	require_once('../../../inc/report_stts/eng-report.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	$sql     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'TEMPAT_PEMBAYARAN'";
	$result  = mysqli_query($DBLink, $sql);
	$row 	 = mysqli_fetch_array($result);
	$tempat_bayar = $row['CTR_AC_VALUE'];
	
	require_once('connectDB_GW.php');
	require_once('queryOpen.php');
	SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

	$templatePrint 	= "templatePrint.xml";
	$driver			= $_REQUEST['driver'];
	$re 			= new reportEngine($templatePrint,$driver);

	$nop   = $_REQUEST['nop'];
	$tahun = $_REQUEST['year'];
	$mode  = $_REQUEST['mode'];
	$uname  = $_REQUEST['uname'];
	$tgl  = $_REQUEST['tgl'];
        
	if($mode=='cetak_ulang')
		$sql = "SELECT A.NOP,
					   A.SPPT_TAHUN_PAJAK, 
					   A.WP_NAMA, 
					   A.OP_KECAMATAN,
					   A.OP_KELURAHAN,
					   A.SPPT_TANGGAL_JATUH_TEMPO,
					   A.OP_LUAS_BUMI,
					   A.OP_LUAS_BANGUNAN,
					   A.SPPT_PBB_HARUS_DIBAYAR,
					   A.PBB_DENDA SPPT_DENDA,
                                           IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID				   
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
	else{
                $tmp = explode("-", $tgl);
                $tgl = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
                $sql = "SELECT A.NOP,
					   A.SPPT_TAHUN_PAJAK, 
					   A.WP_NAMA, 
					   A.OP_KECAMATAN,
					   A.OP_KELURAHAN,
					   A.SPPT_TANGGAL_JATUH_TEMPO,
					   A.OP_LUAS_BUMI,
					   A.OP_LUAS_BANGUNAN,
					   A.SPPT_PBB_HARUS_DIBAYAR,
					   @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'".$tgl."')/30) dendaBulan,
					   @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
					   @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
					   FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR) SPPT_DENDA,
                       CONCAT('".$tgl."',DATE_FORMAT(NOW(),' %H:%i:%s')) AS PAYMENT_PAID
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
        }
	$result = mysqli_query($DBLink, $sql);
	
	if($row = mysqli_fetch_array($result)){
		
		$nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
		$denda = $row['SPPT_DENDA'];
		$total = $nilai + $denda;
		
		if($mode!='cetak_ulang'){
			// $tmp = explode("-", $tgl);
			// $tgl = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
			//d42ng; penambahan kolom PAYMENT_OFFLINE_FLAG, PAYMENT_OFFLINE_PAID; 20170306
			$tgls=date_create($tgl);
			$flag='';
			if(date_format($tgls,"d-m-Y")!=date("d-m-Y")){
				$flag='1';
			}else{
				$flag='0';
			}
//                        $payment_date = date('Y-m-d H:i:s');
//                        if($tgl != date('d-m-Y')) $payment_date = $tgl.' 00:00:00';
                            
			$sql = "UPDATE `PBB_SPPT` SET `PAYMENT_FLAG`='1', 
						   `PAYMENT_PAID`='".$row['PAYMENT_PAID']."', 
						   `PBB_DENDA`='$denda', 
						   `PBB_TOTAL_BAYAR`='$total',
                           `PAYMENT_OFFLINE_USER_ID`='$uname',
						   `PAYMENT_OFFLINE_PAID`=now(),
						   `PAYMENT_OFFLINE_FLAG`='$flag' 
					WHERE (`NOP`='$nop') AND (`SPPT_TAHUN_PAJAK`='$tahun')";
			mysqli_query($DBLink, $sql);		
		}
		
		$templatePrintValue['TEMPAT_BAYAR'] 		= $tempat_bayar;
		$templatePrintValue['THN_BAYAR'] 			= $row['SPPT_TAHUN_PAJAK'];
		$templatePrintValue['THN_DARI'] 			= date('Y');
		$templatePrintValue['SUBJECT_NAME'] 		= $row['WP_NAMA'];
		$templatePrintValue['OBJECT_KECAMATAN'] 	= $row['OP_KECAMATAN'];
		$templatePrintValue['OBJECT_KELURAHAN'] 	= $row['OP_KELURAHAN'];
		$templatePrintValue['NOPNPWP'] 				= substr($row['NOP'],0,2).'.'.substr($row['NOP'],2,2).'.'.substr($row['NOP'],4,3).'.'.substr($row['NOP'],7,3).'.'.substr($row['NOP'],10,3).'-'.substr($row['NOP'],13,4).'.'.substr($row['NOP'],17,1);
		$templatePrintValue['TRAN_AMOUNT_TEXT'] 	= number_format($nilai,2,',','.');;
		$templatePrintValue['JTHTMP'] 				= $row['SPPT_TANGGAL_JATUH_TEMPO'];
		$templatePrintValue['TGL_BAYAR'] 			= $row['PAYMENT_PAID'];
		$templatePrintValue['LT'] 					= $row['OP_LUAS_BUMI'];
		$templatePrintValue['LB'] 					= $row['OP_LUAS_BANGUNAN'];
		$templatePrintValue['TOT_TRAN_AMOUNT_TEXT'] = number_format($total,2,',','.');
		
		//number_format($nilai_pd,2,',','.')
		
		for($i=1; $i<=24; $i++){
			$totalBulan = $nilai + (((2/100) * $nilai)*$i);
			$templatePrintValue["BAYAR_PLUS_DENDA_$i"] = number_format($totalBulan,2,',','.');
		}
		
		$re->ApplyTemplateValue($templatePrintValue);
		$re->Print2TXT($printValue);
		echo base64_encode($printValue);
	}
?>