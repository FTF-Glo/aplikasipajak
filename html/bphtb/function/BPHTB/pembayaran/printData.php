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
	function getpelaporan_ke($noktp){
		global $DBLink2, $DBConn;

		SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
		$query = "SELECT * FROM ssb WHERE wp_noktp = '" . $noktp . "' and PAYMENT_FLAG = 1 and year(payment_paid) = year(now()) ORDER BY pelaporan_ke DESC limit 1";
		$resBE = mysqli_query($DBLink2, $query);
		$pelaporan_ke = '';
		while ($dtBE = mysqli_fetch_array($resBE)) {
			$pelaporan_ke = $dtBE['pelaporan_ke'];
		}

		$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM ssb WHERE wp_noktp = '" . $noktp . "' and PAYMENT_FLAG = 1 and year(payment_paid) = year(now()) ORDER BY pelaporan_ke";
		$resBE = mysqli_query($DBLink2, $query);
		$num_rows  = mysqli_num_rows($resBE);
        $pelaporan_ke = ($pelaporan_ke != 0) ? $pelaporan_ke: 1;
        $jumlah = (int)$pelaporan_ke > $num_rows ? $pelaporan_ke : $num_rows;
        $reutrn = $jumlah + 1;
        return $reutrn;
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
				WHERE  A.op_nomor = '$nop' OR A.payment_code = '$nop' OR A.payment_code=REPLACE('$nop','-','') ORDER BY saved_date DESC LIMIT 1";
	else{
				
                $sql = "SELECT * FROM   ssb A WHERE  A.op_nomor = '$nop' OR A.payment_code = '$nop' OR A.payment_code=REPLACE('$nop','-','') ORDER BY saved_date DESC LIMIT 1";
        }
	$tmp = explode("-", $tgl);
    $tgleng = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
	$result = mysqli_query($DBLink2, $sql);
	
	if($row = mysqli_fetch_array($result)){
		
		$nilai = $row['bphtb_dibayar'];
		$getpelaporan_ke = getpelaporan_ke($row['wp_noktp']);
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
						   pelaporan_ke = '".$getpelaporan_ke."',
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
		$templatePrintValue['KODE_BAYAR'] = $row['payment_code'];
		//number_format($nilai_pd,2,',','.')
		// echo "<pre>";die(print_r($templatePrintValue));
		// $re->ApplyTemplateValue($templatePrintValue);
		// $re->Print2TXT($printValue);
		// $re->Print2File('');
		
		// header('Content-type: text/xml');
		// header('Content-Disposition: attachment; filename="text.xml"');
		// echo $printValue;
		// $re->Print2File('./tes.txt');
		// die();
		// echo base64_encode($printValue);


		$NEW_TEMPLATE = '<table class="print" style="width: 100%;border-collapse: collapse;" cellpadding="7" border="1"><tr><td colspan="2" style="padding:0.6em;text-align: center;"><div style="display: flex;"><img src="../../../style/default/logo.png" alt="logon lamteng" height="130px"><div style="margin-left: 1em;"><p style="font-size: 16pt;font-weight: bold;text-transform: uppercase;">Bukti Pembayaran Bea Perolehan Hak atas Tanah dan Bangunan</p><p style="font-size: 14pt;display: block;text-transform: uppercase">Badan Pengelolaan Pajak dan Retribusi Daerah Kabupaten Lampung Selatan</p></div></div></td></tr><tr><td style="padding:0.6em"><span><strong>User Loket</strong></span><p>__USERLOKET__</p></td><td style="padding:0.6em"><span><strong>Tanggal Pembayaran</strong></span><p>__TGLBAYAR__</p></td></tr><tr><td style="padding:0.6em"><strong>NOP</strong></td><td style="padding:0.6em">__NOPNPWP__</td></tr><tr><td style="padding:0.6em"><strong>Nama Wajib Pajak</strong></td><td style="padding:0.6em">__WPNAMA__</td></tr><tr><td style="padding:0.6em"><strong>Alamat Wajib Pajak</strong></td><td style="padding:0.6em">__WPALAMAT__, RT/RW __WPRTRW__, __WPKEL__, __WPKEC__</td></tr><tr><td style="padding:0.6em"><strong>Alamat Objek Pajak</strong></td><td style="padding:0.6em">__OPALAMAT__, RT/RW __OPRTRW__, __OPKEL__, __OPKEC__</td></tr><tr><td style="padding:0.6em"><strong>Luas Tanah</strong></td><td style="padding:0.6em">__LT__</td></tr><tr><td style="padding:0.6em"><strong>Luas Bangunan</strong></td><td style="padding:0.6em">__LB__</td></tr><tr><td style="padding:0.6em"><strong>Kode Bayar</strong></td><td style="padding:0.6em"><strong>__KODEBAYAR__</strong></td></tr><tr><td style="padding:0.6em"><p>Biaya Tagihan</p><p><strong>Rp.__TOTTRANAMOUNTTEXT__</strong></p></td><td style="padding:0.6em"><p>Bea Perolehan Hak atas Tanah dan Bangunan yang harus dibayar</p><p><strong>Rp.__TRANAMOUNTTEXT__</strong></p><span>(__TERBILANG__)</span></td></tr><tr><td colspan="2"><p style="font-size: 12pt;display: block;text-align: center;color:gray"><i>Terima Kasih atas Pembayarannya - Pajak Anda turut membangun daerah</i></p></td></tr></table>';

		foreach ($templatePrintValue as $key => $value) {
			$NEW_TEMPLATE = str_replace('__'.str_replace('_', '', $key).'__', $value, $NEW_TEMPLATE);
		}

		require_once '../../../inc/tcpdf/tcpdf.php';
		// die(var_dump(PDF_CREATOR));
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('FTF');
		$pdf->SetTitle('Cetak Pembayaran');
		$pdf->SetSubject($templatePrintValue['NOPNPWP']);
		$pdf->SetKeywords('pembayaran');

		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// ---------------------------------------------------------

		// set font
		// $pdf->SetFont('times', 'BI', 20);

		// add a page
		$pdf->AddPage();

		// print a block of text using Write()
		// $pdf->WriteHTML(0, $NEW_TEMPLATE, '', 0, 'C', true, 0, false, false, 0);
		// die($NEW_TEMPLATE);
		$pdf->writeHTML($NEW_TEMPLATE, true, false, true, false, '');
		// ---------------------------------------------------------
		ob_clean();

		//Close and output PDF document
		$pdf->Output('PEMBAYARAN.pdf', 'I');

		//============================================================+
		// END OF FILE
		//============================================================+
	}
?>