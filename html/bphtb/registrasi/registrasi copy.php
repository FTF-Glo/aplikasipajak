<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<?php


//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

session_start();
require_once("library.php");
// require_once("koneksi.php");
require_once("../inc/registrasi/inc-registrasi.php");
require_once "MailNotification.php";
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

if(isset($_GET['act']) && $_GET['act']=="register"){
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
				$check_for_username = mysqli_query($koneksi, "SELECT userId FROM tbl_reg_user_notaris WHERE userId='$userId'");
				$check_for_username2 = mysqli_query($koneksi, "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$userId'");
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
					
					/*$from = TAX_MAIL_NOTIFICATION_FROM;
					$fromName = TAX_MAIL_NOTIFICATION_FROM_NAME;
					$to = $email;
					$subject = TAX_MAIL_NOTIFICATION_NOTARIS_SUBJECT;
					$body = str_replace("<id>",$id,TAX_MAIL_NOTIFICATION_NOTARIS_CONTENT);
					$host = TAX_MAIL_NOTIFICATION_HOST;
					$port = TAX_MAIL_NOTIFICATION_PORT;
					$username = TAX_MAIL_NOTIFICATION_USER;
					$password = TAX_MAIL_NOTIFICATION_PASSWD;
					
					$MailNotif = new MailNotification ($host, $port, $username, $password, $from, $to, $subject, $body, $fromName);
					$MailNotif->SendMail();
					exit;*/
				
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
							
							
							$from = TAX_MAIL_NOTIFICATION_FROM;
							$fromName = TAX_MAIL_NOTIFICATION_FROM_NAME;
							$to = $email;
							$subject = TAX_MAIL_NOTIFICATION_NOTARIS_SUBJECT;
							$body = str_replace("<id>",$id,TAX_MAIL_NOTIFICATION_NOTARIS_CONTENT);
							$host = TAX_MAIL_NOTIFICATION_HOST;
							$port = TAX_MAIL_NOTIFICATION_PORT;
							$username = TAX_MAIL_NOTIFICATION_USER;
							$password = TAX_MAIL_NOTIFICATION_PASSWD;
							
							$MailNotif = new MailNotification ($host, $port, $username, $password, $from, $to, $subject, $body, $fromName);
							$MailNotif->SendMail();
						}
				else{
							# Jika gagal
							echo "<script language='javascript'>alert('Pendaftaran Gagal! Silahkan ulang kembali.')</script>";
							
					}
				}

}else if(isset($_GET['act']) && $_GET['act']=="verifyCaptcha"){
	echo ($_POST['captcha']==$_SESSION['securimage_code_value'])? 1:0;exit;
}
else{
?>
<html>
<head>
<title><?php echo $kepala->tampilJudul();?></title>
<style type='text/css'>
body{
		background-image: linear-gradient(to right bottom, #e6ebd8, #c9e3ce, #a6dbce, #84d0d8, #71c2e4, #6cc1eb, #6ac0f1, #69bff8, #52cffe, #41dfff, #46eefa, #5ffbf1);
   background-attachment: fixed;
		font-family:tahoma;
	}
table{
		margin-top:0px;
	}
#tombol{
  outline: 0;
  background: #45aba6;
  border: 0;
  padding: 15px;
  color: #FFFFFF;
  font-size: 14px;
  -webkit-transition: all 0.3 ease;
  transition: all 0.3 ease;
  cursor: pointer;
  border-radius: 10px;
}
#tombol:hover,#tombol:active,#tombol:focus {
  background: #2f7471;
}

.form-control {
    display: block;
    width: 100%;
}
.box{
background-color:white;
width: 650px;
-webkit-border-radius: 5px 5px 5px 5px;
margin: 20 auto 0;
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
	else if(document.form1.no_tlp.value==""){
		
		alert("mohon periksa kembali No. Telepon Anda.");
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
<div class="box">
<div class="card">
  <div class="card-header text-center">
    <b>Register User Notaris Baru</b>
  </div>
  
	<div class="card-body">
	<table cellpadding='0' cellspacing='0' border='0' align="center">
	<tr>
		<td colspan="3">
			 <div class="form-group row">
    <label class="col-sm-3 col-form-label"></label>
			<div class="col-sm-6">
						         <div id="loading"></div>
		<script type="text/javascript" src="jquery-1.4.2.min.js"></script> 
		 <script type="text/javascript">  
			function cekID(){  
				$("#loading").html("<font size='2' color='black'>&nbsp;memeriksa..</font>");  
				$.post('cekNamaID.php', $("#userId").serialize(), function(hasil){  
				$("#loading").html(hasil);  
					});  
				 }  
    </script>
			</div>
			</div>
		</td>
	</td>
	<tr>
		<td colspan="3">
			  <div class="form-group row">
    <label class="col-sm-3 col-form-label">Nama ID</label>
    <div class="col-sm-6">
      <?php echo $body->tagInput("text","userId","","userId","","", 'form-control');?>
    </div>
    <div class="col-sm-2">
      <?php echo $body->tagInput("button","tombol","","tombol","Cek ketersediaan","cekID(); return false;");?>	
      </div>
  </div>

		</td>
	</tr>
	<tr>
		<td colspan="3">
		<div class="form-group row">
		<label class="col-sm-3 col-form-label">Password</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("password","password","","","","",'form-control');?>
		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
    <label class="col-sm-3 col-form-label">Nama Lengkap</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","nm_lengkap","50","","","", 'form-control');?>
		</div>
		</div>
</td>
	</tr >
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">Alamat Jalan</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","almt_jalan","50","","","", 'form-control');?>
		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">Kota</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","almt_kota","30","","","",'form-control');?>		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">No. KTP</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","no_identitas","20","","","",'form-control');?>
		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">Area</label>
		<div class="col-sm-6">
		<select name='areaPajak' id='areaPajak' class="form-control">
		<option value='pilih' selected>Pilih...</option>
		<?php
		require_once("koneksi.php");
		$sqlSelect="SELECT * FROM cppmod_tax_kabkota";
		$bOK=mysqli_query($koneksi, $sqlSelect);
		while($ambil=mysqli_fetch_array($bOK)){
			echo "<option value='".$ambil['CPC_TK_ID']."'>".$ambil['CPC_TK_ID']."-".$ambil['CPC_TK_KABKOTA']."</option>";
		}
		?>
		</select>
		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">No. Telepon</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","no_tlp","20","","","","form-control");?>
		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label"></label>
		<div class="col-sm-6">
		<font size="2" color="#ADD900"><b>Email digunakan untuk validasi dan pemberitahuan</b></font>
		</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">Email</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","email","25","","","","form-control");?>
		</div>
		</div>
		</td>
	</tr>
<!--	<tr background="image/icon/backNotaris.gif" valign="top">
		<td>&nbsp;&nbsp;<font size="2" color="#ADD900"><b>No.Handphone</b></font></td>
		<td>&nbsp;<?php echo $body->tagInput("text","no_tlp","20","","","");?><br>&nbsp;<font size="1" color="#FFFFFF">*salah satu dari no.hp dan email harus diisi, utamakan email diisi</font></td>
	</tr>-->

    <tr>
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label"></label>
		<div class="col-sm-7">
		<img src="../captcha2.php" id="imgcaptcha">&nbsp;&nbsp;
		<input type="button" value="Ubah" id="tombol" onClick="$('#imgcaptcha').attr('src','../captcha2.php?id='+Math.random())">
		</div>
		</div>
		</td>
	</tr>
    <tr valign="top">
		<td colspan="3"><div class="form-group row">
		<label class="col-sm-3 col-form-label">Verification Code</label>
		<div class="col-sm-6">
		<?php echo $body->tagInput("text","captcha","6","","","","form-control");?>
		</div>
		</div>
		</td>
	</tr>    
	<tr>
		<td>&nbsp;</td>
		<td>
		<?php
		$body->tagInput("hidden","id","","",c_uuid(),"");
		$body->tagInput("hidden","status","","","0","");
		$body->tagInput("hidden","keterangan","","","non_aktif","");
		?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td class="text-center">
        &nbsp;<input type="button" value="Daftar" id="tombol" onClick="cekDaftar()">
		&nbsp;&nbsp;<?php echo $body->tagInput("reset","","","tombol","Reset","");?>&nbsp;&nbsp;<?php echo $body->tagInput("button","kembali","","tombol","Kembali",'javascript:window,location.href="../main.php"');?>
		</td>
	</tr>

	</table>
	

	</div>
  </div>
  </div>
</form>
</body>
</html>
<?php } ?>