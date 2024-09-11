<?php /*
if(!isset($data)){
	die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if(isset($arAreaConfig['terminalColumn'])){
	$terminalColumn = $arAreaConfig['terminalColumn'];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if(!$accessible){
		echo"Illegal access";
		return;
	}
} */?>
<!-------------------------------code------------->
<?php
function getForm($width,$f_nilai_pajak,$f_noptk,$f_pengurangan,$f_bphtb){
  if ($f_pengurangan<0) {$min=$f_pengurangan; $f_pengurangan=0; }
  //$form = "<div style='background:#FF0000;margin:10px 0px 10px 0px'>";
  $form = "<table width='$width' border='0' cellspacing='0' cellpadding='1' class=''>";
  $form.="<tr><td colspan='2'><font size='-4'>&nbsp;</font></td></tr>";
  //$form.="<tr><td colspan='2'><h4>Form Update</h4></td></tr>";
  $form.="<tr><td width='17%'>&nbsp;&nbsp;Nilai Perolehan OP (NIlai Pajak)</td><td width='83%'>:&nbsp;<input name='f_nilai_pajak' type='text' id='f_nilai_pajak' value='".$f_nilai_pajak."' /></td></tr>";
  $form.="<tr><td>&nbsp;&nbsp;NOPTKP</td><td>:&nbsp;<input name='f_noptk' type='text' id='f_noptk' value='".$f_noptk."' /></td></tr>";
  $form.="<tr><td>&nbsp;&nbsp;Nilai Pajak - NOPTKP</td><td>:&nbsp;<input name='f_pengurangan' type='text' id='f_pengurangan' value='".$f_pengurangan."' />";
  $form.=(isset($min))?" * Hasil pengurangan = ".$min." sehingga di jadikan 0":"";
  $form.="</td></tr>";
  $form.="<tr><td>&nbsp;&nbsp;BPHTB (5%)</td><td>:&nbsp;<input name='f_bphtb' type='text' id='f_bphtb' value='".$f_bphtb."' /></td></tr>";
  $form.="<tr><td>&nbsp;</td><td>&nbsp;&nbsp;
  <input type='submit' name='bt_submit' id='submit' value='Submit' />&nbsp;<input type=\"submit\" name=\"bt_update\" id=\"button\" value=\"Default\" /></td></tr>";
  $form.="<tr><td colspan='2'><font size='-4'>&nbsp;</font></td></tr>";
  $form.="</table>";
  //$form.="</div>";
  return $form;	
}
function cekUpdate($_REQ){
	$up_no_ktp = $_REQ['up_no_ktp'];
	$up_nop = $_REQ['up_nop'];
	$tgl_lapor_sebelumnya = $_REQ['tgl_lapor_sebelumnya'];
	$tgl_lapor_skr = $_REQ['tgl_lapor_skr'];
	$tgl_skr = $_REQ['tgl_skr'];
	$exp_data_sebelumnya = $_REQ['exp_data_sebelumnya'];
	$up_noptk = $_REQ['up_noptk'];
	$up_akumulasi = $_REQ['up_akumulasi'];
	$up_jenis_hak = $_REQ['up_jenis_hak'];
	
	if(!empty($exp_data_sebelumnya) and !empty($tgl_lapor_sebelumnya)){
	  $sql_length="select TIMESTAMPDIFF(day,'$tgl_lapor_sebelumnya','$tgl_lapor_skr') length";
	  $qu_length=mysqli_query($sql_length) or die("#er03 cekUpdate()".mysqli_error());
	  $r_l=mysqli_fetch_assoc($qu_length);
	  if($tgl_skr > $exp_data_sebelumnya or $r_l['length']>90){
		  if($up_noptk==0){
			  switch ($up_jenis_hak){
			  case 1:
				$up_noptk=60000000;
			  break;
			  case 2:
				$up_noptk=60000000;
			  break;
			  case 3:
				$up_noptk=60000000;
			  break;
			  case 4:
				$up_noptk=300000000;
			  break;
			  case 5:
				$up_noptk=60000000;
			  break;
			  case 6:
				$up_noptk=300000000;
			  break;
			  case 7:
				$up_noptk=60000000;
			  break;
			  case 8:
				$up_noptk=60000000;
			  break;
			  case 9:
				$up_noptk=60000000;
			  break;
			  case 10:
				$up_noptk=60000000;
			  break;
			  case 11:
				$up_noptk=60000000;
			  break;
			  case 12:
				$up_noptk=60000000;
			  break;
			  case 13:
				$up_noptk=60000000;
			  break;
			  case 14:
				$up_noptk=60000000;
			  break;
			  case 15:
				$up_noptk=60000000;
			  break;
			  case 16:
				$up_noptk=60000000;
			  break;
			  case 17:
				$up_noptk=60000000;
			  break;
			  default:
				$up_noptk=60000000;
			  }
		  }
		$bphtb = ($up_akumulasi - $up_noptk)*5/100;
		if ($bphtb<0) $bphtb=0; //jika nilai pajak atau akumulasi kurang dari nilai NOPTK maka di anggap BPHTB 0
		//echo "bphtb: ".$bphtb."<br> up_noptk: ".$up_noptk;
		//return getForm("100%",$up_akumulasi,$up_noptk,$up_akumulasi-$up_noptk,$bphtb);
		$res['nilai_pajak'] = $up_akumulasi;
		$res['noptk'] = $up_noptk;
		$res['pengurangan'] = $up_akumulasi-$up_noptk;
		$res['bphtb'] = $bphtb;
	  }else{
		$msg = "Tanggal sekarang harus lebih dari 7 hari setelah tanggal expayer data sebelumnya, ";
		$msg .= "atau jarak antara tanggal laporan data sekarang dan data sebelumnya harus 90 hari";
		$res['msg'] = $msg;
	  }
	}else{ 
		$res['msg'] = "Data sebelumnya tidak terperoleh !";
	}
	$res['noKTP'] = $up_no_ktp;
	$res['nop'] = $up_nop;
	$res = json_encode($res);
	$res = base64_encode($res);
	return $res;
}
function prosesUpdate($req){
	global $dbSpec;
	$bphtb = $req['f_bphtb'];
	$up_noptk = $req['f_noptk'];
	$up_no_ktp = $req['up_no_ktp'];
	$up_nop = $req['up_nop'];
  	echo "<pre>"; print_r($req); echo"</pre>";
	$sqlTampil="UPDATE VSI_SWITCHER_DEVEL.cppmod_ssb_doc SET CPM_OP_BPHTB_TU='$bphtb', CPM_OP_NPOPTKP='$up_noptk' WHERE CPM_WP_NOKTP='$up_no_ktp' AND CPM_OP_NOMOR='$up_nop' ";
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	$DbHost="192.168.30.2:7306";  $DbUser="root"; $DbPwd="rahasia"; $DbName="bphtb";
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName);
	$sql="UPDATE bphtb.ssb SET bphtb_dibayar='$bphtb' where wp_noktp = '$up_no_ktp' and op_nomor='$up_nop'";
	$qu=mysqli_query($sql,$LDBLink) or die("#er 02".mysqli_error());		
	
	//if($bOK and $qu) echo"<b style='color:red;'>Data berhasil di update</b>";
}
function req_noKTP_NOP($req,&$req_noktp,&$req_nop){
	$req = str_replace(" ","",$req);
	$req = trim($req);
	$exp = explode(",",$req);
	//echo "<pre>"; print_r($exp); echo "</pre>";
	if(count($exp)>1){
		$req_noktp = $exp[0];
		$req_nop = $exp[1];
	}else{
		$req_noktp = $exp[0];
		$req_nop = $exp[0];
	}
}
?>