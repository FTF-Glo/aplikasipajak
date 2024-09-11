<?php
if ($data) {
	$uid = $data->uid;
	
	// get module
	$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
	if (!$bOK) {
		return false;
	}
//tulis di sini

/* Setting each city/town for all */
$arConfig=$User->GetAreaConfig($area);
$AreaPajak=$arConfig["AreaPajak"];

$Qry="SELECT *FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";
$bOK=$dbSpec->sqlQuery($Qry,$result);
$Key=mysqli_fetch_array($result);

$IdKK=$Key['CPC_TK_ID'];
$NameKK=$Key['CPC_TK_KABKOTA'];
/*--------------------------------*/

function c_uuid($sDelim = '')
{
  // The field names refer to RFC 4122 section 4.1.2
  return sprintf('%04x%04x%s%04x%s%03x4%s%04x%s%04x%04x%04x',
    mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
    $sDelim,
    mt_rand(0, 65535), // 16 bits for "time_mid"
    $sDelim,
    mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
    $sDelim,
    bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
    $sDelim,
    // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
    // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
    // 8 bits for "clk_seq_low"
    mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
  );
} // end of c_uuid
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';
if(isset($submit))
	{
		$sqlSimpan = "INSERT INTO TBL_REG_USER SET uuid='$id',userId='$userId',password='$password',nm_lengkap='$nm_lengkap',email='$email',no_tlp='$no_tlp',almt_jalan='$almt_jalan',almt_kota='$almt_kota',no_identitas='$no_identitas',status='$status',keterangan='$keterangan',areapajak='$areaPajak'";
		$bOK = $dbSpec->sqlQuery($sqlSimpan, $result); 
		if($bOK){
							# Jika sukses
							$arConfig=$User->GetAreaConfig($area);
							$UrlLog=$arConfig["urlLog"];
							echo "Pendaftaran Sukses!<br>";
							//Kirim Link aktivasi
							require_once "Mail.php";

							$from = "getaufan@gmail.com";
							$to = $email;
							$subject = "Konfirmasi Pendaftaran";
							$body = "Anda telah berhasil mendaftar :-). \n Silahkan aktifkan account Anda, dengan mengklik link berikut : \n ".$UrlLog."?id=".$id." \n\n Terima kasih";
							$host = "ssl://smtp.gmail.com";
							$port = "465";
							$username = "getaufan@gmail.com";
							$password = "septaufani";
							$headers = array ('From' => $from,'To' => $to,'Subject' => $subject);
							$smtp = Mail::factory('smtp',
							array ('host' => $host,'port' => $port,'auth' => true,'username' => $username,'password' => $password));
							$mail = $smtp->send($to, $headers, $body);

							if (PEAR::isError($mail)) {

							echo("<p>" . $mail->getMessage() . "</p>");

							} else {

							echo "Email liink aktivasi account telah dikirimkan!<br>";

							}

							
				}
			else{
							# Jika gagal
							echo "<script language='javascript'>alert('Pendaftaran Gagal! Silahkan ulang kembali.')</script>";
							
				}
				$url64 = base64_encode("a=$a&m=$m");
				echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
				echo " <img src='image/icon/wait.gif' alt=''></img>Tunggu beberapa saat...";
				
	}

?>
<script language="JavaScript">
function ngecek(){

	if(document.form1.userId.value==""){
		
		alert("mohon periksa kembali Nama ID Anda.");
		return false;
	}
	else if(document.form1.password.value==""){
		
		alert("mohon periksa kembali password Anda");
		return false;
	}
	else if(document.form1.nm_lengkap.value==""){
		
		alert("mohon periksa kembali nama lengkap Anda.");
		return false;
	}
	else if(document.form1.email.value==""){
		
		alert("mohon periksa kembali email Anda.");
		return false;
	}
	else if(document.form1.no_tlp.value==""){
		
		alert("mohon periksa kembali no.HP Anda.");
		return false;
	}
	else if(document.form1.almt_jalan.value==""){
		
		alert("mohon periksa kembali Alamat Jalan Anda.");
		return false;
	}
	else if(document.form1.almt_kota.value==""){
		
		alert("mohon periksa kembali Alamat Kota Anda.");
		return false;
	}
	else if(document.form1.no_identitas.value==""){
		
		alert("mohon periksa kembali no identitas Anda.");
		return false;
	}
	else{return true;}
}
</script>
</head>
<body>
<form method="POST" action="main.php?param=<?php echo base64_encode("a=$a&m=$m")?>" id="form1" name="form1" onSubmit="return ngecek();">
	<table cellpadding='0' cellspacing='0' border='0' align="center">
	<tr>
		<td align='center' colspan='2'><img src="image/icon/headingNotaris.gif" border="0" alt=""></img></td>
	</tr>
	<tr>
		<td align='center' colspan='2'  background="image/icon/backNotaris.gif">&nbsp;</td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Nama ID</b></font></td>
		<td  background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="userId" id="userId">&nbsp;<input type="button" id="tombol" name="tombol" value="Cek ketersediaan" onclick="cekID(); return false;">&nbsp;<div id="loading"></div>
		<script type="text/javascript" src="jquery-1.4.2.min.js"></script> 
		 <script type="text/javascript">  
			function cekID(){  
				$("#loading").html("<font size='2' color='#FFFFFF'>&nbsp;memeriksa..</font>");  
				$.post('view/Registrasi/cek_idNotaris.php', $("#userId").serialize(), function(hasil){  
				$("#loading").html(hasil);  
					});  
				 }  
    </script>
		</td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Password</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="password" name="password"></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Nama Lengkap</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="nm_lengkap" size='50'></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Alamat</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;</td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Jalan</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="almt_jalan" size='50'></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Kota</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="almt_kota" size='30'></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>No.KTP</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="no_identitas" size='20'></td>
	</tr>
	<tr>
		<td colspan="2" background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Kirimkan validasi dan pemberitahuan lainnya ke:</b></font></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Email</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="email" size='25'></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>No.Handphone</b></font></td>
		<td background="image/icon/backNotaris.gif">&nbsp;<input type="text" name="no_tlp" size='20'><br>&nbsp;<font size="1" color="#FFFFFF">*salah satu dari no.hp dan email harus diisi, utamakan email diisi</font></td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;</td>
		<td background="image/icon/backNotaris.gif">
		<input type="hidden" name="id" value="<?php echo  c_uuid();?>">
		<input type="hidden" name="status" value="0">
		<input type="hidden" name="keterangan" value="non_aktif"></td>
		<input type="hidden" name="areaPajak" value="<?php echo  $IdKK;?>">
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;</td>
		<td background="image/icon/backNotaris.gif">
		&nbsp;<input id="tombol" type="submit" name='submit' value='Daftar'>&nbsp;&nbsp;<input id="tombol" type="reset" value="Reset">
		</td>
	</tr>
	<tr>
		<td background="image/icon/backNotaris.gif">&nbsp;</td>
		<td background="image/icon/backNotaris.gif">&nbsp;</td>
	</tr>
	</table>
</form><br>

<?php
}
?>