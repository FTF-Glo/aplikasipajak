<?php
if ($data) {
	$uid = $data->uid;
	
	// get module
	$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
	if (!$bOK) {
		return false;
	}

/* ------------Setting each city/town for all--------------------- */
$arConfig=$User->GetAreaConfig($area);
$AreaPajak=$arConfig["AreaPajak"];

/*-----------------------------------------------------------------*/


//uuid
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
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';
//Simpan data form
if(isset($simpan))
	{
			$idUser = "u" . $Setting->GetNextUserId();
			$idUser = htmlentities($idUser, ENT_QUOTES);
			$status="0";
			$keterangan="aktif";
			$userId=mysqli_real_escape_string($DBLink, $userId);
			$pwd=mysqli_real_escape_string($DBLink, $pwd);
			$nm_lengkap=mysqli_real_escape_string($DBLink, $nm_lengkap);
			$sqlSimpan="INSERT INTO TBL_REG_USER_PBB SET uuid='$id',userId='$userId',password='$pwd',nm_lengkap='$nm_lengkap',nip='$nip',no_ktp='$no_ktp',kota='$kota',kelurahan='$kelurahan',kecamatan='$kecamatan',no_hp='$no_hp',jabatan='$jabatan',email='$email',status='$status',keterangan='$keterangan',areapajak='$AreaPajak' ";
		$username1 = "SELECT userId FROM TBL_REG_USER_PBB WHERE userId='$userId'";
		$username2 ="SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$userId'";
		$check_1=$dbSpec->sqlQuery($username1, $result);
		$check_2=$dbSpec->sqlQuery($username2, $result);
		$check_for_username=mysqli_num_rows($result);
		$check_for_username2=mysqli_num_rows($result);
		if(stristr($userId,"'"))
		{
			echo "<script>alert('Maaf, Nama ID teridentifikasi mengandung tanda kutip. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang valid!')</script>";
			$url64 = base64_encode("a=$a&m=$m");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
			echo "<img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
		}
			else if($check_for_username||$check_for_username2){
			echo "<script>alert('Maaf, Nama ID teridentifikasi bahwa sudah terpakai. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang lain!')</script>";
			$url64 = base64_encode("a=$a&m=$m");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
			echo "<img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
		}		
		else
		{
			$bOK = $dbSpec->sqlQuery($sqlSimpan, $result); 
			if($bOK){
				$bOK=$Setting->InsertUser($idUser, $userId, $pwd, 0, 0,0);
				if($bOK){
					echo "Berhasil disimpan..";
					require_once($sRootPath."view/Registrasi/notifikasi_email.php");
					if($_POST['jabatan']=="staff dispenda")
					{
						$pajakPBBApp=$arConfig["pajakPBBApp"];
						$pajakPBBStaffDispen=$arConfig["pajakPBBStaffDispen"];
						$Setting->ChangeRole($idUser,$pajakPBBApp,$pajakPBBStaffDispen);
					}
					else if($_POST['jabatan']=="pejabat dispenda")
					{
						$pajakPBBApp=$arConfig["pajakPBBApp"];
						$pajakPBBPjbtDispen=$arConfig["pajakPBBPjbtDispen"];
						$Setting->ChangeRole($idUser,$pajakPBBApp,$pajakPBBPjbtDispen);
					}
					else if($_POST['jabatan']=="petugas pendata")
					{
						$pajakPBBApp=$arConfig["pajakPBBApp"];
						$pajakPBBPendata=$arConfig["pajakPBBPendata"];
						$Setting->ChangeRole($idUser,$pajakPBBApp,$pajakPBBPendata);
					}
					else if($_POST['jabatan']=="pejabat kelurahan")
					{
						$pajakPBBApp=$arConfig["pajakPBBApp"];
						$pajakPBBPjbtLurah=$arConfig["pajakPBBPjbtLurah"];
						$Setting->ChangeRole($idUser,$pajakPBBApp,$pajakPBBPjbtLurah);
					}
				}
				else{
					echo "Gagal disimpan..";
				}
			}else{
					echo "Gagal disimpan..";
			}
			$url64 = base64_encode("a=$a&m=$m");
			//echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
			//echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
		}
			
		//}
}

?>
<script language="JavaScript">
function cekform(){
	if(document.formReg.userId.value=="")
	{
		alert("Mohon perikasa Nama ID");
		return false;
	}
	else if (document.formReg.pwd.value=="")
	{
		alert("Mohon periksa Password");
		return false;
	}
	else if (document.formReg.nm_lengkap.value=="")
	{
		alert("Mohon periksa Nama Lengkap");
		return false;
	}
	else if (document.formReg.nip.value=="")
	{
		alert("Mohon periksa NIP");
		return false;
	}
	else if (document.formReg.no_ktp.value=="")
	{
		alert("Mohon periksa NO.KTP");
		return false;
	}
	else if (document.formReg.kota.value=="pilih")
	{
		alert("Mohon periksa Kota");
		return false;
	}
	else if (document.formReg.kecamatan.value=="pilih")
	{
		alert("Mohon periksa Kecamatan");
		return false;
	}
	else if (document.formReg.kelurahan.value=="pilih")
	{
		alert("Mohon periksa Kelurahan");
		return false;
	}
	else if (document.formReg.jabatan.value=="")
	{
		alert("Mohon periksa jabatan");
		return false;
	}
	else if (document.formReg.email.value=="")
	{
		alert("Mohon periksa email");
		return false;
	}
	else
	{
		return true;
	}
}
</script>
<form method="POST" action="main.php?param=<?php echo base64_encode("a=$a&m=$m")?>" name="formReg" id="formReg" onSubmit="return cekform();">
	<table border="0" cellspacing="1" cellpadding="1">
	<tr>
		<th colspan="2" align="left">Form Data Pengguna</th>
	</tr>
	<tr>
		<td>Nama ID</td>
		<td><input type="text" name="userId" id="userId">&nbsp;&nbsp;<input type="button" id="tombol" value="cek ketersediaan" onclick="cek_id(); return false;">&nbsp;<div id="loading"></div>
		<script type="text/javascript" src="jquery-1.4.2.min.js"></script> 
		 <script type="text/javascript">  
			function cek_id(){  
				$("#loading").html('<img src=image/icon/loadinfo.net.gif></img><font size=1>memeriksa..</font>');  
				$.post('view/Registrasi/cek_id_pbb.php', $("#userId").serialize(), function(hasil){  
				$("#loading").html(hasil);  
					});  
				 }  
		</script>
		</td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="password" name="pwd"></td>
	</tr>
	<tr>
		<td>Nama Lengkap</td>
		<td><input type="text" name="nm_lengkap" size="40"></td>
	</tr>
	<tr>
		<td>NIP</td>
		<td><input type="text" name="nip" size="25"></td>
	</tr>
	<tr>
		<td>No.KTP</td>
		<td><input type="text" name="no_ktp" size="25"></td>
	</tr>
	<tr>
		<td>Kelurahan</td>
		<td><input type="text" name="kelurahan" id="kelurahan"size="25"></td>
	</tr>
	<tr>
		<td>Kecamatan</td>
		<td><input type="text" name="kecamatan" id="kecamatan"size="25"></td>
	</tr>
	<tr>
		<td>Kota/Kabupaten</td>
		<td><input type="text" name="kota" id="kota"size="25"></td>
	</tr>
	<tr>
		<td valign="top">Bekerja sebagai</td>
		<td>
		<div>
				<input type="radio" name="jabatan" value="petugas pendata">Petugas Pendata&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="jabatan" value="staff dispenda">Staff DISPENDA&nbsp;&nbsp;&nbsp;&nbsp;<br><input type="radio" name="jabatan" value="pejabat kelurahan">Pejabat Kelurahan&nbsp;&nbsp;&nbsp;<input type="radio" name="jabatan" value="pejabat dispenda">Pejabat DISPENDA&nbsp;&nbsp;&nbsp;<font size="1" color="#FF0000">*harap dipilih salah satu</font>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="2"><b>Dibawah ini salah satunya harus diisi</b></td>
	</tr>
	<tr>
		<td>No. Handphone</td>
		<td><input type="text" name="no_hp"></td>
	</tr>
	<tr>
		<td colspan="2"><b>Dibawah ini salah satunya harus diisi</b></td>
	</tr>
	<tr>
		<td>No. Handphone</td>
		<td><input type="text" name="no_hp"></td>
	</tr>
	<tr>
		<td>Email</td>
		<td><input type="text" name="email">&nbsp;&nbsp;<font size="1" color="#FF0000">*utamakan email diisi</font></td>
	</tr>
	<tr>
		<td colspan="2"><input type="hidden" name="id" value="<?php echo c_uuid();?>"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Simpan" name="simpan">&nbsp;&nbsp;<input type="reset" value="Batalkan"></td>
	</tr>
	</table>
</form>


<?php
}
?>