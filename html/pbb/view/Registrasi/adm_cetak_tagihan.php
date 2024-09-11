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

	$Qry="SELECT *FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";
	$bOK=$dbSpec->sqlQuery($Qry,$result);
	$Key=mysqli_fetch_array($result);

	$IdKK=$Key['CPC_TK_ID'];
	$NameKK=$Key['CPC_TK_KABKOTA'];
	/*-----------------------------------------------------------------*/


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

	if(isset($simpan))
		{
			$arConfig=$User->GetAreaConfig($area);
			$Csc_cdID=$arConfig["csc_cdID"];
			$UserRoleModule=$arConfig["userRoleModule"];
			$idUser = "u_PBB" . $Setting->GetNextUserPBB();
			$idUser = htmlentities($idUser, ENT_QUOTES);
			$status="0";
			$keterangan="aktif";
			$tglJam=date("d-m-Y H:i:s");
			$userId=mysql_real_escape_string($userId);
			$nm_lengkap=mysql_real_escape_string($nm_lengkap);
			$almt=mysql_real_escape_string($almt);
			$pwd=mysql_real_escape_string($pwd);
			$sqlSimpan="INSERT INTO TBL_REG_USER_CETAK_TAGIHAN SET uuid='$id',userId='$userId',password='$pwd',nm_lengkap='$nm_lengkap',nip='$nip',no_ktp='$no_ktp',kota='$kota',kelurahan='$kelurahan',kecamatan='$kecamatan',no_hp='$no_hp',email='$email',status='$status',keterangan='$keterangan'";
			$sqlSave="INSERT INTO CPCCORE_USER SET CPC_U_ID='$idUser',CPC_U_UID='$userId',CPC_U_PWD=md5('$pwd')";
			$sqlSave2="INSERT INTO CPCCORE_PAYMENT_POINT_USER_BLOCK SET CPC_PPUB_ID='$id',CPC_PPUB_UID='$idUser',CPC_PPUB_BLOCKED='0',CPC_PPUB_ISLOGIN='0'";
			$sqlSave3="INSERT INTO csccore_central_downline SET CSC_CD_ID='$id',CSC_CD_NAME='$nm_lengkap',CSC_CD_ADDRESS='$almt',CSC_CD_PHONE='$no_hp',CSC_CD_PIC_NAME='$nm_lengkap',CSC_CD_PIC_PHONE='$no_hp',CSC_CD_TERMINAL_TYPE='6015',CSC_CD_REGISTERED='$tglJam',CSC_CD_ISBLOCKED='$status'";
			$sqlSave4="csccore_down_central_downline SET CSC_DCD_CID='$Csc_cdID',CSC_DCD_DID='$id'";
			$sqlSave5="INSERT INTO CPCCORE_USER_ROLE_USER_MODULE SET CPC_URUM_UID='$idUser',CPC_URUM_M2MID='$UserRoleModule'";
		
			$username1 = "SELECT userId FROM TBL_REG_USER_CETAK_TAGIHAN WHERE userId='$userId'";
			$username2 ="SELECT CPC_U_UID FROM CPCCORE_USER WHERE CPC_U_UID='$userId'";
			$check_1=$dbSpec->sqlQuery($username1, $result);
			$check_2=$dbSpec->sqlQuery($username2, $result);
			$check_for_username=mysqli_num_rows($result);
			$check_for_username2=mysqli_num_rows($result);
			if(stristr($userId,"'"))
			{
				echo "\n<script>alert('Maaf, Nama ID teridentifikasi mengandung tanda kutip. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang valid!')</script>";
				$url64 = base64_encode("a=$a&m=$m");
				echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
				echo "\n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...";
			}
			else if($check_for_username||$check_for_username2){
				echo "\n<script>alert('Maaf, Nama ID teridentifikasi bahwa sudah terpakai. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang lain!')</script>";
				$url64 = base64_encode("a=$a&m=$m");
				echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
				echo "\n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...";
			}		
			else
			{
				$bOK = $dbSpec->sqlQuery($sqlSimpan, $result); 
				$bOK1 = $dbSpec->sqlQuery($sqlSave, $result);
				$bOK2 = $dbSpec->sqlQuery($sqlSave2, $result);
				$bOK3 = $dbSpec->sqlQuery($sqlSave3, $result);
				$bOK4 = $dbSpec->sqlQuery($sqlSave4, $result);
				$bOK5 = $dbSpec->sqlQuery($sqlSave5, $result);
				if($bOK || $bOK1 || $bOK2 || $bOK3 || $bOK4)
				{
					echo "\nBerhasil disimpan..";
					require_once($sRootPath."Registrasi/emailNotifikasi.php");
				}
				else
				{
					echo "\nGagal disimpan..";
				}
				$url64 = base64_encode("a=$a&m=$m");
				echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
				echo "\n<img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";	
			}
		
		}
?>

	<script type="text/javascript" src="view/Registrasi/js/config.js"></script>
	<form method="POST" action="main.php?param=<?php echo base64_encode("a=$a&m=$m")?>" name="formReg" id="formReg" onSubmit="return cekformpencetak();">
		<table border="0" cellspacing="1" cellpadding="1">
		<tr>
			<th colspan="2" align="left">Form Data Pengguna</th>
		</tr>
		<tr>
			<td>Nama ID</td>
			<td><input type="text" name="userId" id="userId">&nbsp;&nbsp;<input type="button" id="tombol" value="cek ketersediaan" onclick="cek_id(); return false;">&nbsp;<div id="loading"></div>
			<input type="hidden" name="a" value="<?php echo  $a;?>" id="a"><input type="hidden" name="m" value="<?php echo  $m;?>" id="m">
			<script type="text/javascript" src="jquery-1.4.2.min.js"></script> 
			<script type="text/javascript">  
				function cek_id(){  
					$("#loading").html('<img src=image/icon/loadinfo.net.gif></img><font size=1>memeriksa..</font>');  
					id=$("#userId").val();
					a=$("#a").val();
					m=$("#m").val();
					$.post('view/Registrasi/cek_id_cetak_tagihan.php', {userId:id,app:a,mod:m}, function(hasil){  
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
			<td>Alamat</td>
			<td><textarea name="almt" rows="5" cols="45"></textarea></td>
		</tr>
		<tr>
			<td>Kecamatan</td>
			<td>
			<select name="kecamatan" id="kecamatan">
				<option value="pilih" selected>Pilih...</option>
				<?php
				$QryKc="SELECT *FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID='$AreaPajak'";
				$bOK=$dbSpec->sqlQuery($QryKc,$result);
				while($KeyKc=mysqli_fetch_array($result))
				{
					echo "\n\t\t\t\t<option value='".$KeyKc['CPC_TKC_KECAMATAN']."'>".$KeyKc['CPC_TKC_KECAMATAN']."</option>";
				}
				?>
			</select>
			</td>
		</tr>
		<tr>
			<td>Kelurahan</td>
			<td>
			<select name="kelurahan" id="kelurahan">
				<option value="pilih" selected>Pilih...</option>
				<?php
				$QryKcB="SELECT *FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID='$AreaPajak'";
				$bOK=$dbSpec->sqlQuery($QryKcB,$result);
				$KeyKcB=mysqli_fetch_array($result);
				$KeyKcA=$KeyKcB['CPC_TKC_ID'];

				$QryKl="SELECT *FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID='$KeyKcA'";
				$bOK=$dbSpec->sqlQuery($QryKl,$result);
				while($KeyKl=mysqli_fetch_array($result))
				{
					echo "\n\t\t\t\t<option value='".$KeyKl['CPC_TKL_KELURAHAN']."'>".$KeyKl['CPC_TKL_KELURAHAN']."</option>";
				}
			

				?>
			</select>
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
			<td>Email</td>
			<td><input type="text" name="email">&nbsp;&nbsp;<font size="1" color="#FF0000">*utamakan email diisi</font></td>
		</tr>
		<tr>
			<td colspan="2"><input type="hidden" name="id" value="<?php echo  "PBB".c_uuid();?>"><input type="hidden" name="kota" value="<?php echo  $NameKK;?>"></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
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