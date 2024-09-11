<?

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


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'registrasi-notaris', '', dirname(__FILE__))).'/';


if(isset($dataId) || isset($nameUser) || isset($pwdUser)|| isset($email)){
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
		}
		
		$idUser = "u" . $Setting->GetNextUserId();
		$idUser = htmlentities($idUser, ENT_QUOTES);
		$keterangan="approve";
		$status=2;
		$newuuid=c_uuid();
		$sqlUpdate="UPDATE TBL_REG_USER_NOTARIS SET uuid='$newuuid',status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
		$bOK=$Setting->InsertUser($idUser, $nameUser, $pwdUser, 0, 0,0);
		$bOK = $dbSpec->sqlQuery($sqlUpdate, $result);
		if ($bOK) {
					echo "<div>Berhasil di-approve...</div>\n";
					$url64 = base64_encode("a=$a&m=$m");
					echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
					echo "Tunggu beberapa saat... <img src='image/icon/wait.gif' alt=''></img>\n";
					require_once($sRootPath."function/registrasi-notaris/konfirmasi.php");
					$arConfig=$User->GetAreaConfig($area);
					$bphtbApp=$arConfig["bphtbApp"];
					$NotarisRole=$arConfig["notarisRole"];
					$Setting->ChangeRole($idUser,$bphtbApp,$NotarisRole);

		} else {
					echo "<script language='javascript'>alert('Gagal di-approve!.')</script>";
				}
}

else{
	?>
	<div class='spacer10'></div>
	<div class='subTitle'>Belum notifikasi</div>
	<div class='spacer5'></div>
	<table width="100%" cellspacing="1" cellpadding="1" border="0" bgcolor="#FF9900">
	<tr>
		<th align="center"><b>NO.</b></th>
		<th align="center"><b>USER ID</b></th>
		<th align="center"><b>PASSWORD</b></th>
		<th align="center"><b>NAMA LENGKAP</b></th>
		<th align="center"><b>EMAIL</b></th>
		<th align="center"><b>NO TELEPON/HP</b></th>
		<th align="center"><b>JALAN</b></th>
		<th align="center"><b>KOTA</b></th>
		<th align="center"><b>NO IDENTITAS</b></th>
		<!-- <th align="center"><b>Status</b></th> -->
		<!-- <th align="center"><b>Keterangan</b></th> -->
		<th align="center"><b>AKSI</b></th>
	</tr>
	<?
$func=$func[5][id];
$sqlTampil= "SELECT * FROM TBL_REG_USER_NOTARIS WHERE status='1' AND areapajak='$IdKK' ORDER BY id DESC LIMIT 10";
$bOK = $dbSpec->sqlQuery($sqlTampil, $result);

$jumlah=mysqli_num_rows($result);
if($jumlah>0){
$no=0;

		while($dataTampil=mysqli_fetch_array($result)){
		$no++;
		
?>	
		<tr>
			<td align="center"><?php echo  $no;?></td>
			<td align="center"><?php echo  addslashes($dataTampil['userId']);?></td>
			<td align="center"><?php echo  md5($dataTampil['password']);?></td>
			<td align="center"><?php echo $dataTampil['nm_lengkap'];?></td>
			<td align="center"><?php echo $dataTampil['email'];?></td>
			<td align="center"><?php echo $dataTampil['no_tlp'];?></td>
			<td align="center"><?php echo $dataTampil['almt_jalan'];?></td>
			<td align="center"><?php echo $dataTampil['almt_kota'];?></td>
			<td align="center"><?php echo $dataTampil['no_identitas'];?></td>
			<!-- <td align="center"><?php echo $dataTampil['status'];?></td> -->
			<!-- <td align="center"><?//echo$dataTampil['keterangan'];?></td> -->
			<td align="center">
			<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&dataId=".$dataTampil['id']."&nameUser=".addslashes($dataTampil['userId'])."&pwdUser=".$dataTampil['password']."&email=".$dataTampil['email'])?>">
			<img src='./image/icon/accept.png' title='DITERIMA' alt='DITERIMA'></a>
			&nbsp;&nbsp;<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$func&dataId=".$dataTampil['id']."&nameUser=".addslashes($dataTampil['userId'])."&pwdUser=".$dataTampil['password']."&email=".$dataTampil['email'])?>"><img src='./image/icon/reject.png' title='DITOLAK' alt='DITOLAK'></a></td>
			<?
				/*
				if($_GET['keterangan']="reject"){
				echo "<td align='center'><input type='image' src='./image/icon/accept.png' title='APPROVE' name='approve' value='".$dataTampil['id']."'></td>";
				}
				else { echo "<td align='center'><input type='image' src='./image/icon/block.png' title='REJECT' name='reject' value='".$dataTampil['id']."'></td>";}
				*/
			?>

		</tr>

		<?
				}
}
else{
			echo "<tr><td colspan=10 align='center'>Maaf, data belum ada</td></tr>";
	}
?>
</table>
<?
	}
?>
