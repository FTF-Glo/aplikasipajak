<?php
session_start();
require_once("library.php");
require_once("../inc/registrasi/inc-registrasi.php");

//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1); 
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

//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1); 

$kepala=new kepala();
$kepala->ganti('Pendaftaran Notaris');
$body=new body();

if($_GET['act']=="register"){
				require_once("koneksi.php");
				
				$id=$_POST['id'];
				$userId=mysqli_real_escape_string($koneksi, $_POST['userId']);
				$password=$_POST['password'];
				$nm_lengkap=mysqli_real_escape_string($koneksi, $_POST['nm_lengkap']);
				$email=$_POST['email'];
				$no_tlp=$_POST['no_tlp'];
				$almt_jalan=$_POST['almt_jalan'];
				$almt_kota=$_POST['almt_kota'];
				$no_identitas=$_POST['no_identitas'];
				$status=$_POST['status'];
				$keterangan=$_POST['keterangan'];
				$areaPajak=$_POST['areaPajak'];
				$sqlSimpan = "INSERT INTO tbl_reg_user_notaris SET uuid='$id',userId='$userId',password='$password',nm_lengkap='$nm_lengkap',email='$email',no_tlp='$no_tlp',almt_jalan='$almt_jalan',almt_kota='$almt_kota',no_identitas='$no_identitas',status='$status',keterangan='$keterangan',areapajak='$areaPajak'";
				$sqlambilId="SELECT CPC_TK_ID FROM cppmod_tax_kabkota WHERE CPC_TK_KABKOTA='$almt_kota'";
				$check_for_username = mysqli_query($koneksi, "SELECT userId FROM tbl_reg_user_notaris WHERE userId='$userId'",$koneksi);
				$check_for_username2 = mysqli_query($koneksi, "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$userId'",$koneksi);
				if(stristr($userId,"'"))
				{
					echo "<script>alert('Maaf, Nama ID teridentifikasi mengandung tanda kutip! Silahkan ulangi pendaftaran, harap gunakan Nama ID yang valid.')</script>";
					echo "<meta http-equiv='REFRESH' content='0;url=registrasi.php'>";
					echo "<center><img src=image/icon/loadinfo.net.gif></img>&nbsp;Tunggu beberapa saat...<br>Sedang memuat halaman pendaftaran...</font></center>";
				}
				else if(mysqli_num_rows($check_for_username)||mysqli_num_rows($check_for_username2)){
					echo "<script>alert('Maaf, Nama ID teridentifikasi bahwa sudah terpakai. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang lain!')</script>";
					echo "<meta http-equiv='REFRESH' content='0;url=registrasi.php'>";
					echo "<center><img src=image/icon/loadinfo.net.gif></img>&nbsp;Tunggu beberapa saat...<br>Sedang memuat halaman pendaftaran...</font></center>";
				}
				
				else 
				{
					
					$bOK=mysqli_query($koneksi, $sqlSimpan);
					if($bOK||$bOK2){
							# Jika sukses
							echo "<center>";$body->tagAhref("registrasi.php","","->Daftar Kembali<-");echo "</center>";
							echo "<center><font size='4'>Pendaftaran Sukses!</font><br>";
                                                        echo "<font size='3'>Terimakasih telah melakukan registrasi, sebuah email telah dikirim ke ".$email.", silahkan klik pada link aktivasi untuk mengaktifkan akun anda.</font><br/>";
                                                        echo "<font size='3'>Jika email tidak ditemukan di folder <b>'Kotak Masuk'</b>, maka periksa di folder <b>'Spam'</b> pada email anda.</font></center>";
							//Kirim Link aktivasi
							//require_once "Mail.php";
							
							require_once "SMTPClient.php";
							
							$from = TAX_MAIL_NOTIFICATION_FROM;
							$fromName = TAX_MAIL_NOTIFICATION_FROM_NAME;
							$to = $email;
							$subject = TAX_MAIL_NOTIFICATION_NOTARIS_SUBJECT;
							$body = str_replace("<id>",$id,TAX_MAIL_NOTIFICATION_NOTARIS_CONTENT);
							$host = TAX_MAIL_NOTIFICATION_HOST;
							$port = TAX_MAIL_NOTIFICATION_PORT;
							$username = TAX_MAIL_NOTIFICATION_USER;
							$password = TAX_MAIL_NOTIFICATION_PASSWD;
							
							$SMTPMail = new SMTPClient ($host, $port, $username, $password, $from, $to, $subject, $body, $fromName);
							$SMTPChat = $SMTPMail->SendMail();
						}
				else{
							# Jika gagal
							echo "<script language='javascript'>alert('Pendaftaran Gagal! Silahkan ulang kembali.')</script>";
							
					}
				}

}elseif($_GET['act']=="verifyCaptcha"){
	echo ($_POST['captcha']==$_SESSION['securimage_code_value'])? 1:0;exit;
}
else{
?>
<html>
<head>
<title><?php echo $kepala->tampilJudul();?></title>
<style type='text/css'>
body{
		font-family:tahoma;
	}
table{
		margin-top:70px;
	}
#tombol{
		background-color:#94B900;
		color:#FFFFFF;
}
</style>
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
	else if(document.form1.almt_jalan.value==""){
		
		alert("mohon periksa kembali Alamat Jalan Anda.");
		return false;
	}
	else if(document.form1.almt_kota.value==""){
		
		alert("mohon periksa kembali Alamat Kota Anda.");
		return false;
	}
	else if(document.form1.no_identitas.value==""){
		
		alert("mohon periksa kembali No. KTP Anda.");
		return false;
	}
	else if(document.form1.areaPajak.value=="pilih"){
		
		alert("mohon periksa kembali Area Pajak.");
		return false;
	}
	else if(document.form1.email.value==""){
		
		alert("mohon periksa kembali email Anda.");
		return false;
	}
	else if(document.form1.captcha.value==""){		
		alert("mohon isi verification code.");
		return false;
	}else{
		return true;	
	}
	
}

function cekDaftar(){
	$.ajax({
		type : "post",
		url : "<?php echo $_SERVER['PHP_SELF'];?>?act=verifyCaptcha",
		data : "captcha="+document.form1.captcha.value,
		success:function(res){
			hasil = ngecek();
			if(hasil==true){			
				if(res==0){ 
					alert("Verification code tidak valid.")
					return false;
				}else{
					$('#form1').submit();	
				}
			}else{
				return false;	
			}
		}			
	})
	
}
</script>
</head>
<body>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>?act=register" id="form1" name="form1">
	<table cellpadding='0' cellspacing='0' border='0' align="center">
	<tr>
		<td align='center' colspan='2'>
		<?php echo $body->tagImg("image/icon/hedingNotaris.gif","0","pendaftaran notaris");?>
		</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td align='center' colspan='2'>&nbsp;</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Nama ID</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","userId","","userId","","");?>&nbsp;
		<?php echo $body->tagInput("button","tombol","","tombol","Cek ketersediaan","cekID(); return false;");?>
		&nbsp;<div id="loading"></div>
		<script type="text/javascript" src="jquery-1.4.2.min.js"></script> 
		 <script type="text/javascript">  
			function cekID(){  
				$("#loading").html("<font size='2' color='#FFFFFF'>&nbsp;memeriksa..</font>");  
				$.post('cekNamaID.php', $("#userId").serialize(), function(hasil){  
				$("#loading").html(hasil);  
					});  
				 }  
    </script>
		</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Password</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("password","password","","","","");?></td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Nama Lengkap</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","nm_lengkap","50","","","");?></td>
	</tr >
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Alamat</b></font></td>
		<td>&nbsp;</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Jalan</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","almt_jalan","50","","","");?></td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Kota</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","almt_kota","30","","","");?></td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>No.KTP</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","no_identitas","20","","","");?></td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Area</b></font></td>
		<td>&nbsp;<select name='areaPajak' id='areaPajak'>
		<option value='pilih' selected>Pilih...</option>
		<?php
		require_once("koneksi.php");
		$sqlSelect="SELECT * FROM cppmod_tax_kabkota";
		$bOK=mysqli_query($koneksi, $sqlSelect);
		while($ambil=mysqli_fetch_array($bOK)){
			echo "<option value='".$ambil['CPC_TK_ID']."'>".$ambil['CPC_TK_ID']."-".$ambil['CPC_TK_KABKOTA']."</option>";
		}
		echo "</select>";
		?>
		</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td colspan="2">&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Kirimkan validasi dan pemberitahuan lainnya ke:</b></font></td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Email</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","email","25","","","");?></td>
	</tr>
<!--	<tr background="image/icon/backNotaris.gif" valign="top">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>No.Handphone</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","no_tlp","20","","","");?><br>&nbsp;<font size="1" color="#FFFFFF">*salah satu dari no.hp dan email harus diisi, utamakan email diisi</font></td>
	</tr>-->
        <input id="" type="hidden" onclick="" value="" size="20" name="no_tlp">
    <tr background="image/icon/backNotaris.gif">
    	<td colspan="2">&nbsp;<br><br></td>
    </tr>
    <tr background="image/icon/backNotaris.gif">
		<td></td>
		<td>&nbsp;&nbsp;<img src="../captcha2.php" id="imgcaptcha"><input type="button" value="Ubah" id="tombol" onClick="$('#imgcaptcha').attr('src','../captcha2.php?id='+Math.random())"></td>
	</tr>
    <tr background="image/icon/backNotaris.gif" valign="top">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>Verification Code</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","captcha","6","","","");?></td>
	</tr>    
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;</td>
		<td>
		<?php
		$body->tagInput("hidden","id","","",c_uuid(),"");
		$body->tagInput("hidden","status","","","0","");
		$body->tagInput("hidden","keterangan","","","non_aktif","");
		?>
		</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;</td>
		<td>
        &nbsp;<input type="button" value="Daftar" id="tombol" onClick="cekDaftar()">
		&nbsp;&nbsp;<?php echo $body->tagInput("reset","","","tombol","Reset","");?>&nbsp;&nbsp;<?php echo $body->tagInput("button","kembali","","tombol","Kembali",'javascript:window,location.href="../main.php"');?>
		</td>
	</tr>
	<tr background="image/icon/backNotaris.gif">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
</form>
</body>
</html>
<?php } ?>