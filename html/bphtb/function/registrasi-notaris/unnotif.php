<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($data)) {
	die();
}

/* ------------Setting each city/town for all--------------------- */
$arConfig=$User->GetAreaConfig($area);
$AreaPajak=$arConfig["AreaPajak"];

$IdKK="";
$NameKK="";

$Qry="SELECT * FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";
// global $DBLink;
// var_dump($DBLink);
if($result=mysqli_query($DBLink, $Qry)){	
	if($row=mysqli_fetch_array($result)){
		$IdKK=$row['CPC_TK_ID'];
		$NameKK=$row['CPC_TK_KABKOTA'];		
	}
	mysqli_free_result($result);
}else{
	echo mysqli_error();
}

/*-----------------------------------------------------------------*/

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'registrasi-notaris', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/uuid.php");

if(isset($dataId) || isset($nameUser) || isset($pwdUser)|| isset($email)){
		
		$idUser = "u" . $Setting->GetNextUserId();
		$idUser = htmlentities($idUser, ENT_QUOTES);
		$keterangan="approve";
		$status=2;
		$newuuid=c_uuid();
		$sqlUpdate="UPDATE tbl_reg_user_notaris SET uuid='$newuuid',status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
		$bOK=$Setting->InsertUser($idUser, $nameUser, $pwdUser, 0, 0,0);
		$bOK = mysqli_query($DBLink, $sqlUpdate);
		// var_dump($bOK);die;
		// $bOK = $dbSpec->sqlQuery($sqlUpdate, $result);
		if ($bOK) {
					echo "<div>Berhasil di-approve...</div>\n";
					$url64 = base64_encode("a=$a&m=$m");
					echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
					echo "Tunggu beberapa saat... <img src='image/icon/wait.gif' alt=''></img>\n";
					// require_once($sRootPath."function/registrasi-notaris/konfirmasi.php");
					$arConfig=$User->GetAreaConfig($area);
					$bphtbApp="aBPHTB";
					$NotarisRole=$arConfig["notarisRole"];
					$Setting->ChangeRole($idUser,$bphtbApp,$NotarisRole);
					// var_dump($Setting->ChangeRole($idUser,$bphtbApp,$NotarisRole));die;

		} else {
					echo "<script language='javascript'>alert('Gagal di-approve!.')</script>";
				}
}

else{
	?>
	<div class='content-wrapper' style='padding-top:0px;padding-bottom:0px'>
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

	<?php
			//var_dump($func[5]);exit();
$func=$func[5]["id"];
$sqlTampil= "SELECT * FROM tbl_reg_user_notaris WHERE status='1' AND areapajak='$IdKK' ORDER BY id DESC LIMIT 10";
// echo $sqlTampil;die;
// $bOK = $dbSpec->sqlQuery($sqlTampil, $result);
$result = mysqli_query($DBLink, $sqlTampil);
// var_dump($result);
$jumlah=mysqli_num_rows($result);
if($jumlah>0){
$no=0;

		while($dataTampil=mysqli_fetch_array($result)){
		$no++;
		
?>	
		<tr>
			<td align="center"><?php echo $no;?></td>
			<td align="center"><?php echo addslashes($dataTampil['userId']);?></td>
			<td align="center"><?php echo md5($dataTampil['password']);?></td>
			<td align="center"><?php echo$dataTampil['nm_lengkap'];?></td>
			<td align="center"><?php echo$dataTampil['email'];?></td>
			<td align="center"><?php echo$dataTampil['no_tlp'];?></td>
			<td align="center"><?php echo$dataTampil['almt_jalan'];?></td>
			<td align="center"><?php echo$dataTampil['almt_kota'];?></td>
			<td align="center"><?php echo$dataTampil['no_identitas'];?></td>
			<!-- <td align="center"><?php echo$dataTampil['status'];?></td> -->
			<!-- <td align="center"><?php//echo$dataTampil['keterangan'];?></td> -->
			<td align="center">
			<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&dataId=".$dataTampil['id']."&nameUser=".addslashes($dataTampil['userId'])."&pwdUser=".$dataTampil['password']."&email=".$dataTampil['email'])?>">
			<img src='./image/icon/accept.png' title='DITERIMA' alt='DITERIMA'></a>
			&nbsp;&nbsp;<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$func&dataId=".$dataTampil['id']."&nameUser=".addslashes($dataTampil['userId'])."&pwdUser=".$dataTampil['password']."&email=".$dataTampil['email'])?>"><img src='./image/icon/reject.png' title='DITOLAK' alt='DITOLAK'></a></td>
			<?php
				/*
				if($_GET['keterangan']="reject"){
				echo "<td align='center'><input type='image' src='./image/icon/accept.png' title='APPROVE' name='approve' value='".$dataTampil['id']."'></td>";
				}
				else { echo "<td align='center'><input type='image' src='./image/icon/block.png' title='REJECT' name='reject' value='".$dataTampil['id']."'></td>";}
				*/
			?>

		</tr>

		<?php
				}
}
else{
			echo "<tr><td colspan=10 align='center'>Maaf, data belum ada</td></tr>";
	}
?>
</table></div>
<?php
	}
?>
