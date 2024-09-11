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


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'registrasi-notaris', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/registrasi/inc-registrasi.php");
if(@isset($email) && @isset($id)){
					
							//Kirim Link notifikasi
							require_once "Mail.php";
							$from = TAX_MAIL_NOTIFICATION_FROM;
							$to = $email;
							$subject = TAX_MAIL_NOTIFICATION_NOTARIS_SUBJECT;
							$body = str_replace("<id>",$id,TAX_MAIL_NOTIFICATION_NOTARIS_CONTENT);
							$host = TAX_MAIL_NOTIFICATION_HOST;
							$port = TAX_MAIL_NOTIFICATION_PORT;
							$username = TAX_MAIL_NOTIFICATION_USER;
							$password = TAX_MAIL_NOTIFICATION_PASSWD;
							$headers = array ('From' => $from,'To' => $to,'Subject' => $subject);
							$smtp = Mail::factory('smtp',
							array ('host' => $host,'port' => $port,'auth' => true,'username' => $username,'password' => $password));
							$mail = $smtp->send($to, $headers, $body);

							if (PEAR::isError($mail)) {

							/*echo("<p><font color='#FFFFFF'>" . */$mail->getMessage();/* . "</font></p>");*/

							} else {

							echo "<img src='image/icon/wait.gif' alt=''></img>Email notifikasi telah berhasil dikirimkan...";
							$url64 = base64_encode("a=$a&m=$m&f=$f");
					        echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
							echo "Tunggu beberapa saat... \n";
							}

}

	?>
	<div class='spacer10'></div>
	<div class='subTitle'>Belum Validasi</div>
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
		<th align="center"><b>NON AKTIF</b></th>
	</tr>
	<?php
$sqlTampil= "SELECT * FROM tbl_reg_user_notaris WHERE status='0' AND areapajak='$IdKK' ORDER BY id DESC LIMIT 10";
$bOK = $dbSpec->sqlQuery($sqlTampil, $result);

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
			<td align="center"><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&email=".$dataTampil['email']."&id=".$dataTampil['uuid'])?>" onClick="Cek=confirm('Apakah Anda yakin?');if(!Cek){return false;}"><img src='./image/icon/email_go.png' title='kirim ulang email validasi' alt='kirim ulang email validasi'></a></td>
		</tr>

		<?php
				}
}
else{
			echo "<tr><td colspan=10 align='center'>Maaf, data belum ada</td></tr>";
	}

?>
</table>
