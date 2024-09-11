<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
	require_once('../../../inc/report_stts/eng-report.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	require_once('../../../inc/payment/sayit.php');
	function getLuas($ssbid, $no){
		global $DBLink, $DBConn;
		
		SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
		
		$sql       = "SELECT CPM_OP_LUAS_TANAH, CPM_OP_LUAS_BANGUN FROM cppmod_ssb_doc WHERE CPM_SSB_ID = '$ssbid'";
		$result    = mysqli_query($DBLink, $sql);
		$row 	   = mysqli_fetch_array($result);
		$hasil     = $row['CPM_OP_LUAS_TANAH'].",".$row['CPM_OP_LUAS_BANGUN'];
		$pilihluas = explode(",",$hasil);
		
		return $pilihluas[$no];
		
	}	
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	$sql     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'TEMPAT_PEMBAYARAN'";
	$result  = mysqli_query($DBLink, $sql);
	$row 	 = mysqli_fetch_array($result);
	$tempat_bayar = $row['CTR_AC_VALUE'];
	
	require_once('connectDB_GW.php');
	require_once('queryOpen.php');
	SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);
	// die(var_dump(GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME));
	$templatePrint 	= "templatePrint.xml";
	$driver			= $_REQUEST['driver'];
	$re 			= new reportEngine($templatePrint,$driver);
	
	$nop   = $_REQUEST['nop'];
	$tahun = $_REQUEST['year'];
	$mode  = $_REQUEST['mode'];
	$uname  = $_REQUEST['uname'];
	$tgl  = $_REQUEST['tgl'];
        
	if($mode=='cetak_ulang')
		$sql = "SELECT * 
				FROM   ssb A 
				WHERE  A.op_nomor = '$nop' ORDER BY saved_date DESC LIMIT 1";
	else{
				
                $sql = "SELECT * FROM   ssb A WHERE  A.op_nomor = '$nop' ORDER BY saved_date DESC LIMIT 1";
        }
		$tmp = explode("-", $tgl);
        $tgleng = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
	$result = mysqli_query($DBLink2, $sql);
	
	if($row = mysqli_fetch_array($result)){
		
		$nilai = $row['bphtb_dibayar'];
		//$denda = $row['SPPT_DENDA'];
		$total = $nilai;
		$ssbid = $row['id_switching'];
		$tgl_trans = date("Y-m-d H:i:s");
		$settlement = date("Ymd");
		if($mode!='cetak_ulang'){
//                        $payment_date = date('Y-m-d H:i:s');
//                        if($tgl != date('d-m-Y')) $payment_date = $tgl.' 00:00:00';
                            
			$sql = "UPDATE ssb SET payment_flag='1', 
						   payment_paid='".$tgleng." 12:00:00',
                           payment_offline_user_id='$uname',
						   payment_offline_paid='$tgl_trans',
						   payment_bank_code = '9996471',
						   bphtb_collectible = '".$nilai."',
						   payment_settlement_date = '".$settlement."'
					WHERE id_switching = '$ssbid'";
			mysqli_query($DBLink2,$sql);		
		}
		
		$templatePrintValue['TEMPAT_BAYAR'] 		= $tempat_bayar;
		$templatePrintValue['TGL_BAYAR'] 		    = $tgl;
		$templatePrintValue['USER_LOKET'] 		    = $uname;
		$templatePrintValue['WP_NAMA'] 		        = $row['wp_nama'];
		$templatePrintValue['WP_ALAMAT'] 		    = $row['wp_alamat'];
		$templatePrintValue['OP_ALAMAT'] 		    = $row['op_letak'];
		$templatePrintValue['WP_RT_RW'] 	        = $row['wp_rt']."/".$row['wp_rw'];
		$templatePrintValue['OP_RT_RW'] 	        = $row['op_rt']."/".$row['op_rw'];
		$templatePrintValue['WP_KEL'] 				= $row['wp_kelurahan'];
		$templatePrintValue['OP_KEL'] 				= $row['op_kelurahan'];
		$templatePrintValue['WP_KEC'] 				= $row['wp_kecamatan'];
		$templatePrintValue['OP_KEC'] 				= $row['op_kecamatan'];
		$templatePrintValue['WP_KAB'] 				= $row['wp_kabupaten'];
		$templatePrintValue['OP_KAB'] 				= $row['op_kabupaten'];
		
		$templatePrintValue['NOPNPWP'] 			    = substr($row['op_nomor'],0,2).'.'.substr($row['op_nomor'],2,2).'.'.substr($row['op_nomor'],4,3).'.'.substr($row['op_nomor'],7,3).'.'.substr($row['op_nomor'],10,3).'-'.substr($row['op_nomor'],13,4).'.'.substr($row['op_nomor'],17,1);
		$templatePrintValue['TRAN_AMOUNT_TEXT'] 	= number_format($nilai,2,',','.');
		
		$templatePrintValue['TOT_TRAN_AMOUNT_TEXT']     = number_format($total,2,',','.');
		$templatePrintValue['TERBILANG'] 	   			= strtoupper(SayInIndonesian(number_format(intval($total), 0, ',', '')));
		$templatePrintValue['LT'] 				= getLuas($ssbid,0)." m2";
		$templatePrintValue['LB'] 				= getLuas($ssbid,1)." m2";
		//number_format($nilai_pd,2,',','.')
		echo "<pre>";die(print_r($templatePrintValue));
		$re->ApplyTemplateValue($templatePrintValue);
		$re->Print2TXT($printValue);
		// $re->Print2File('');
		
		// header('Content-type: text/xml');
		// header('Content-Disposition: attachment; filename="text.xml"');
		// echo $printValue;
		// $re->Print2File('./tes.txt');
		// die();
		echo base64_encode($printValue);
	}
?>