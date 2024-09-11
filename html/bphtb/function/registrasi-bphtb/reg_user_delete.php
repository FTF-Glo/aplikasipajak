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
		$userId=mysqli_real_escape_string($DBLink, $userId);
		$pwd=mysqli_real_escape_string($DBLink, $pwd);
		$nm_lengkap=mysqli_real_escape_string($DBLink, $nm_lengkap);
		$keterangan="aktif";
		$sqlSimpan="DELETE FROM tbl_reg_user_bphtb where userId='$userId'";
		//echo $sqlSimpan.$sqlSimpan2;
		$bOK = $dbSpec->sqlQuery($sqlSimpan, $result); 
		$bOK = $Setting->DeleteUser($idUser);
		if($bOK){
			echo "Berhasil didelete..";
		}else{
			echo "Gagal di delete..";
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
					return false
				}
				else
				{
					if(confirm("Yakin Mau diHapus?"))
						return true;
					else
						return false;
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
					<td><input type="text" name="nm_lengkap" size="40" readonly value="<?php echo $dataTampil["nm_lengkap"]?>"></td>
				</tr>
				<tr>
					<td colspan="2"><input type="hidden" name="id"  value="<?php echo $uuid?>"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Delete" name="simpan">&nbsp;&nbsp;<input type="button" value="Batal" onclick="history.back()"></td>
				</tr>
				</table></div>
			</form>


			<?php
			}
		}
}
?>