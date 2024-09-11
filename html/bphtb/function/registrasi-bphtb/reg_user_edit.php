<?php
if ($data) {
	//var_dump($_REQUEST);
	$uid = $data->uid;
	
	/* ------------Setting each city/town for all--------------------- */
	$arConfig=$User->GetAreaConfig($area);
	$AreaPajak=$arConfig["AreaPajak"];
	/*-----------------------------------------------------------------*/
	$Qry="SELECT *FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";
	$bOK=$dbSpec->sqlQuery($Qry,$result);
	$Key=mysqli_fetch_array($result);

	$IdKK=$Key['CPC_TK_ID'];
	$NameKK=$Key['CPC_TK_KABKOTA'];

	$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'registrasi-bphtb', '', dirname(__FILE__))).'/';
	require_once($sRootPath."inc/registrasi/inc-registrasi.php");
	require_once($sRootPath."inc/payment/uuid.php");
	//Simpan data form
	if(isset($simpan))
	{
		$idUser = "$id";
		$idUser = htmlentities($idUser, ENT_QUOTES);
		$status="0";
		$userId=mysqli_real_escape_string($DBLink, $userId);
		$pwd=mysqli_real_escape_string($DBLink, $pwd);
		$nm_lengkap=mysqli_real_escape_string($DBLink, $nm_lengkap);
		$keterangan="aktif";
		$sqlSimpan="UPDATE tbl_reg_user_bphtb SET nm_lengkap='$nm_lengkap',nip='$nip',no_ktp='$no_ktp',kota='$kota',kecamatan='$kecamatan',kelurahan='$kelurahan',no_hp='$no_hp',email='$email' where userId='$userId'";
		$bOK = $dbSpec->sqlQuery($sqlSimpan, $result); 
		if($bOK){
			echo "Berhasil disimpan..";
		}else{
			echo "Gagal disimpan..";
		}
		$url64 = base64_encode("a=$a&m=$m");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
		echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
		}
		
	}
	
	if(isset($_REQUEST["dataId"])){
		$sqlCari="SELECT * FROM tbl_reg_user_bphtb WHERE id='".$_REQUEST["dataId"]."' AND areapajak='$IdKK' ORDER BY id";
		//var_dump($sqlCari);
		if($result=mysqli_query($appDbLink, $sqlCari)){
			if($dataTampil=mysqli_fetch_array($result)){
			?>
			<script language="JavaScript">
			function cekform(){
				if(document.formReg.userId.value=="")
				{
					alert("Mohon perikasa Nama ID");
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
			<form method="POST" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f")?>" name="formReg" id="formReg" onSubmit="return cekform();">
				<div class='content-wrapper' style='padding-top:0px;padding-bottom:0px'>
				<table border="0" cellspacing="1" cellpadding="1">
				<tr>
					<th colspan="2" align="left">Form Data Pengguna</th>
				</tr>
				<tr>
					<td>Nama ID</td>
					<td><input type="text" name="userId" id="userId" readonly value="<?php echo $dataTampil["userId"]?>"><div id="loading"></div>
					</td>
				</tr>
				<tr>
					<td>Nama Lengkap</td>
					<td><input type="text" name="nm_lengkap" size="40" value="<?php echo $dataTampil["nm_lengkap"]?>"></td>
				</tr>
				<tr>
					<td>NIP</td>
					<td><input type="text" name="nip" size="25" value="<?php echo $dataTampil["nip"]?>"></td>
				</tr>
				<tr>
					<td>No.KTP</td>
					<td><input type="text" name="no_ktp" size="25" value="<?php echo $dataTampil["no_ktp"]?>"></td>
				</tr>
				<tr>
					<td>Kelurahan</td>
					<td><input type="text" name="kelurahan" id="kelurahan"size="25"  value="<?php echo $dataTampil["kelurahan"]?>"></td>
				</tr>
				<tr>
					<td>Kecamatan</td>
					<td><input type="text" name="kecamatan" id="kecamatan"size="25"  value="<?php echo $dataTampil["kecamatan"]?>"></td>
				</tr>
				<tr>
					<td>Kota/Kabupaten</td>
					<td><input type="text" name="kota" id="kota"size="25"  value="<?php echo $dataTampil["kota"]?>"></td>
				</tr>
				<tr>
					<td colspan="2"><b>Dibawah ini salah satunya harus diisi</b></td>
				</tr>
				<tr>
					<td>No. Handphone</td>
					<td><input type="text" name="no_hp"  value="<?php echo $dataTampil["no_hp"]?>"></td>
				</tr>
				<tr>
					<td>Email</td>
					<td><input type="text" name="email"  value="<?php echo $dataTampil["email"]?>">&nbsp;&nbsp;<font size="1" color="#FF0000">*utamakan email diisi</font></td>
				</tr>
				<tr>
					<td colspan="2"><input type="hidden" name="id"  value="<?php echo $uuid?>"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Simpan" name="simpan">&nbsp;&nbsp;<input type="reset" value="Kosongkan"></td>
				</tr>
				</table></div>
			</form>


			<?php
			}
		}
}
?>