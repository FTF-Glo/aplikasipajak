<?php
	require_once('../../../inc/report/eng-report.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	$sql     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'TEMPAT_PEMBAYARAN'";
	$result  = mysqli_query($sql);
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
					   A.PBB_DENDA SPPT_DENDA				   
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
	else
		$sql = "SELECT A.NOP,
					   A.SPPT_TAHUN_PAJAK, 
					   A.WP_NAMA, 
					   A.OP_KECAMATAN,
					   A.OP_KELURAHAN,
					   A.SPPT_TANGGAL_JATUH_TEMPO,
					   A.OP_LUAS_BUMI,
					   A.OP_LUAS_BANGUNAN,
					   A.SPPT_PBB_HARUS_DIBAYAR,
					   @dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'".date('Y-m-d')."')/30) dendaBulan,
					   @dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
					   @dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
					   FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR) SPPT_DENDA				   
				FROM   PBB_SPPT A
				WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
	$result = mysqli_query($sql);
	
	if($row = mysqli_fetch_array($result)){
		
		$nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
		$denda = $row['SPPT_DENDA'];
		$total = $nilai + $denda;
		
		if($mode!='cetak_ulang'){
			$sql = "UPDATE `PBB_SPPT` SET `PAYMENT_FLAG`='1', 
						   `PAYMENT_PAID`='".date('Y-m-d H:i:s')."', 
						   `PBB_DENDA`='$denda', 
						   `PBB_TOTAL_BAYAR`='$total',
                                                   `PAYMENT_OFFLINE_USER_ID`='$uname' 
					WHERE (`NOP`='$nop') AND (`SPPT_TAHUN_PAJAK`='$tahun')";
			mysqli_query($sql);		
		}
		
		$nop = $row['NOP'];
		$nop = substr($nop,0,2).'.'.substr($nop,2,2).'.'.substr($nop,4,3).'.'.substr($nop,7,3).'.'.substr($nop,10,3).'-'.substr($nop,13,4).'.'.substr($nop,17,1);
		
		$templatePrintValue['TEMPAT_BAYAR'] 		= $tempat_bayar;
		$templatePrintValue['THN_BAYAR'] 			= $row['SPPT_TAHUN_PAJAK'];;
		$templatePrintValue['THN_DARI'] 			= date('Y');
		$templatePrintValue['SUBJECT_NAME'] 		= $row['WP_NAMA'];
		$templatePrintValue['OBJECT_KECAMATAN'] 	= $row['OP_KECAMATAN'];
		$templatePrintValue['OBJECT_KELURAHAN'] 	= $row['OP_KELURAHAN'];
		$templatePrintValue['NOPNPWP'] 				= $nop;
		$templatePrintValue['TRAN_AMOUNT_TEXT'] 	= number_format($nilai,2,',','.');;
		$templatePrintValue['JTHTMP'] 				= $row['SPPT_TANGGAL_JATUH_TEMPO'];
		$templatePrintValue['TGL_BAYAR'] 			= date('Y-m-d H:i:s');
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