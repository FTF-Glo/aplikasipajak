<?php 
if (!isset($data)) {
	die();
}

// NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig["terminalColumn"])) {
	$terminalColumn = $arAreaConfig["terminalColumn"];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if (!$accessible) {
		echo "Illegal access";
		return;
	}
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


//blokir
if(isset($dataId)||isset($nameUser)||isset($pwdUser)||isset($email))
{
		$status="0";
		$keterangan="aktif";
		$nameUser=addslashes($nameUser);
		$sqlUpdate="UPDATE tbl_reg_user_pbb SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND kota='$IdKK'";
		$sqlUbah="UPDATE central_user SET CTR_U_BLOCKED='0' WHERE CTR_U_UID='$nameUser'";
		$bOK = $dbSpec->sqlQuery($sqlUpdate, $result); 
		$bOK2 = $dbSpec->sqlQuery($sqlUbah, $result); 
		if($bOK||$bOK2){
			echo "\nBerhasil diaktifkan..";
		}
		else{
			echo "\nGagal diaktifkan..";
		}
		$url64 = base64_encode("a=$a&m=$m&f=$f");
		echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
		echo "\n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...\n";
}
?>
<form method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f")?>">
	<table>
	<tr>
		<td>Pencarian</td>
		<td><input type="text" name="txtCari" size="25"></td>
		<td><input type="submit" name="submit" value="Search pengguna"></td>
	</tr>
	</table>
</form><br>
<table width="100%" cellspacing="1" cellpadding="1" border="0" bgcolor="#FF9900">
	<tr>
		<th align="center"><b>NO.</b></th>
		<th align="center"><b>USER ID</b></th>
		<!--<th align="center"><b>PASSWORD</b></th>-->
		<th align="center"><b>NAMA LENGKAP</b></th>
		<th align="center"><b>EMAIL</b></th>
		<th align="center"><b>NO TELEPON/HP</b></th>
		<th align="center"><b>SEBAGAI</b></th>
		<th align="center"><b>NIP</b></th>
		<th align="center"><b>NO IDENTITAS/KTP</b></th>
		<th align="center"><b>AKSI</b></th>
	</tr>
<?php
if(isset($submit))
{
	$txtCari=mysqli_real_escape_string($dbSpec->getDBLink(), $txtCari);
	$sqlCari="SELECT *FROM tbl_reg_user_pbb WHERE userId LIKE ('%$txtCari%') AND status='1' AND kota='$IdKK' ORDER BY id";
	$bOK=$dbSpec->sqlQuery($sqlCari, $result);
	$jumlah=mysqli_num_rows($result);
	$no=0;
	if($bOK)
	{
		if($jumlah>0)
		{
			while($dataTampil=mysqli_fetch_array($result))
			{
				$no++;
			?>
				<tr>
				<td align="center"><?php echo $no;?></td>
				<td align="center"><?php echo addslashes($dataTampil['userId']);?></td>
				<!--<td align="center"><?php /*echo md5($dataTampil['password']);*/?></td>-->
				<td align="center"><?php echo$dataTampil['nm_lengkap'];?></td>
				<td align="center"><?php echo$dataTampil['email'];?></td>
				<td align="center"><?php echo$dataTampil['no_hp'];?></td>
				<td align="center"><?php echo$dataTampil['jabatan'];?></td>
				<td align="center"><?php echo$dataTampil['nip'];?></td>
				<td align="center"><?php echo$dataTampil['no_ktp'];?></td>
				<td align="center">
				<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&dataId=".$dataTampil['id']."&nameUser=".addslashes($dataTampil['userId'])."&pwdUser=".$dataTampil['password']."&email=".$dataTampil['email'])?>"><img src="./image/icon/accept.png" alt="AKTIFKAN" title="AKTIFKAN"></img></a>
				</td>
				</tr>
<?php 
			}
		}
		else
		{
			echo "\n<td align='center' colspan='10'>No Records Found</td>";
		}
		
	}
	else
	{
		echo "\nMaaf, pencarian gagal";
	}
}
else
{
$sqlTampil= "SELECT * FROM tbl_reg_user_pbb WHERE status='1' AND kota='$IdKK' ORDER BY id DESC LIMIT 10";
$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
$jumlah=mysqli_num_rows($result);
$no=0;
		while($dataTampil=mysqli_fetch_array($result)){
		$no++;
		?>
		<tr>
			<td align="center"><?php echo $no;?></td>
			<td align="center"><?php echo addslashes($dataTampil['userId']);?></td>
			<!--<td align="center"><?php /*echo md5($dataTampil['password']);*/?></td>-->
			<td align="center"><?php echo$dataTampil['nm_lengkap'];?></td>
			<td align="center"><?php echo$dataTampil['email'];?></td>
			<td align="center"><?php echo$dataTampil['no_hp'];?></td>
			<td align="center"><?php echo$dataTampil['jabatan'];?></td>
			<td align="center"><?php echo$dataTampil['nip'];?></td>
			<td align="center"><?php echo$dataTampil['no_ktp'];?></td>
			<td align="center">
			<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&dataId=".$dataTampil['id']."&nameUser=".addslashes($dataTampil['userId'])."&pwdUser=".$dataTampil['password']."&email=".$dataTampil['email'])?>"><img src="./image/icon/accept.png" alt="AKTIFKAN" title="AKTIFKAN"></img></a></td>
		</tr>
		<?php 
		}
}
		?>
