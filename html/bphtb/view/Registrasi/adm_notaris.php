<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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

	$Qry="SELECT * FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";

	$bOK=$dbSpec->sqlQuery($Qry,$result);
	$Key=mysqli_fetch_array($result);

	// die(var_dump($bOK));

	$IdKK=$Key['CPC_TK_ID'];
	$NameKK=$Key['CPC_TK_KABKOTA'];
	/*-----------------------------------------------------------------*/
	
	$NotarisUserId=isset($NotarisUserId)?$NotarisUserId:"";
	$NotarisStatus=isset($NotarisStatus)?$NotarisStatus:"";
	$NotarisAct=isset($NotarisAct)?intval($NotarisAct):"";
	$dataId=isset($dataId)?$dataId:"";


	$sRootPath = str_replace('\\', '/', str_replace('view'.DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';
	require_once($sRootPath."inc/registrasi/inc-registrasi.php");
	require_once($sRootPath."inc/payment/uuid.php");
	require_once "Mail.php";

	echo '<form method="POST" action="main.php?param='.base64_encode("a=$a&m=$m").'" name="formC" id="formC">';
	echo '<table border="0" cellspacing="1" cellpadding="1">';
	echo '<tr>';
	echo '	<th colspan="5" align="left">Pencarian Pengguna</th>';
	echo '</tr>';
	echo '<tr>';
	echo '	<td>Nama Pengguna</td>';
	echo '	<td><input type="text" name="NotarisUserId" value="'.$NotarisUserId.'" id="NotarisUserId"></td>';
	echo '	<td>Status</td>';
	echo '	<td>';
	echo '		<select name="NotarisStatus" id="NotarisStatus">';
	echo '			<option value="" selected>Pilih</option>';
	echo '			<option value="0" '.($NotarisStatus=="0"?"selected":"").'>Belum Validasi</option>';
	echo '			<option value="1" '.($NotarisStatus=="1"?"selected":"").'>Belum Notifikasi</option>';
	echo '			<option value="2" '.($NotarisStatus=="2"?"selected":"").'>Diterima (Aktif)</option>';
	echo '			<option value="3" '.($NotarisStatus=="3"?"selected":"").'>Ditolak</option>';
	echo '			<option value="4" '.($NotarisStatus=="4"?"selected":"").'>Diblokir (Tidak Aktif)</option>';
	echo '		</select>';
	echo '	</td>';
	echo '	<td><input type="submit" value="Cari" name="submit" id="submit"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form><br>';
	// die(var_dump($dataId));
	if($NotarisUserId!="" || $NotarisStatus!=""){
		
		if($dataId!="" && $NotarisAct!=""){
			$sQuery= "SELECT * FROM tbl_reg_user_notaris WHERE id='$dataId'";
			if($result=mysqli_query($appDbLink, $sQuery)){
				if($row=mysqli_fetch_array($result)){
					if($NotarisAct==1){//resent email
							$from = TAX_MAIL_NOTIFICATION_FROM;
							$to = $row["email"];
							$subject = TAX_MAIL_NOTIFICATION_NOTARIS_SUBJECT;
							$body = str_replace("<id>",$row["uuid"],TAX_MAIL_NOTIFICATION_NOTARIS_CONTENT);
							$host = TAX_MAIL_NOTIFICATION_HOST;
							$port = TAX_MAIL_NOTIFICATION_PORT;
							$username = TAX_MAIL_NOTIFICATION_USER;
							$password = TAX_MAIL_NOTIFICATION_PASSWD;

							$headers = array ('From' => $from,'To' => $to,'Subject' => $subject);
							$smtp = Mail::factory('smtp',
							array ('host' => $host,'port' => $port,'auth' => TAX_MAIL_NOTIFICATION_USEAUTH,'username' => $username,'password' => $password));
							$mail = $smtp->send($to, $headers, $body);

							if (PEAR::isError($mail)) {


							} else {

								echo "Email notifikasi telah berhasil dikirimkan...<br> Tunggu beberapa saat... \n";
							}
					
					}else if($NotarisAct==2){//accept sent email
						$idUser = $row["uuid"];
						$idUser = htmlentities($idUser, ENT_QUOTES);
						$keterangan="approve";
						$status=2;
						$nameUser=addslashes($row["userId"]);
						$sqlUpdate="UPDATE tbl_reg_user_notaris SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
						$pwdUser =$row["password"];
						$email1=$row["email"];
						$bOK=$Setting->InsertUser($idUser, $nameUser, $pwdUser, 0, 0,0,$arConfig["userTheme"]);
						$bOK = mysqli_query($appDbLink, $sqlUpdate);
						if ($bOK) {
						
									require_once($sRootPath."view/Registrasi/konfirmasi_kedua.php");
									$notarisApp=$arConfig["notarisApp"];
									$NotarisRole=$arConfig["notarisRole"];
									$Setting->ChangeRole($idUser,$notarisApp,$NotarisRole);
									echo "<div>Berhasil di-approve...</div>\n";
									echo "Tunggu beberapa saat...\n";

						} else {
									echo "<div>Gagal di-approve...</div>\n";
						}
					}else if($NotarisAct==3){//reject
						$keterangan="reject";
						$status=3;
						$sqlUpdate="UPDATE tbl_reg_user_notaris SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
						$bOK = mysqli_query($appDbLink, $sqlUpdate,$appDbLink);
						if ($bOK) {

									echo "<div>Berhasil di-reject...</div>\n";

						}else {
									echo "<div>Gagal di-reject...</div>\n";
						}
					}else if($NotarisAct==4){//blok user
						$keterangan="block";
						$status=4;
						$sqlUpdate="UPDATE tbl_reg_user_notaris SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
						$bOK = mysqli_query($appDbLink, $sqlUpdate);
						if ($bOK) {

									$sqlUpdate="UPDATE central_user SET CTR_U_BLOCKED=1 WHERE CTR_U_UID='".$row["userId"]."'";
									$bOK = mysqli_query($appDbLink, $sqlUpdate);
									if ($bOK) {

												echo "<div>Berhasil di-block...</div>\n";

									}else {
												echo "<div>Gagal di-block...</div>\n";
									}

						}else {
									echo "<div>Gagal di-block...</div>\n";
						}
					}else if($NotarisAct==5){//unblock user
						$keterangan="approve";
						$status=2;
						$sqlUpdate="UPDATE tbl_reg_user_notaris SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
						$bOK = mysqli_query($appDbLink, $sqlUpdate);
						if ($bOK) {

									$sqlUpdate="UPDATE central_user SET CTR_U_BLOCKED=0 WHERE CTR_U_UID='".$row["userId"]."'";
									$bOK = mysqli_query($appDbLink, $sqlUpdate);
									if ($bOK) {

												echo "<div>Berhasil di-unblock...</div>\n";

									}else {
												echo "<div>Gagal di-unblock...</div>\n";
									}

						}else {
									echo "<div>Gagal di-unblock...</div>\n";
						}
					}
				}
			}
		}
		echo "<table border='0' cellspacing='1' cellpadding='1' bgcolor='#FF9900' width='100%'>
						<tr>
						<th>NO.</th>
						<th>USER ID</th>
						<th>PASSWORD</th>
						<th>NAMA LENGKAP</th>
						<th>EMAIL</th>
						<th>NO TELEPON/HP</th>
						<th>JALAN</th>
						<th>KOTA</th>
						<th>NO IDENTITAS</th>
						<th>AKSI</th>
						</tr>";
		// new aldes
		$IdKK = $AreaPajak;
		$sQuery= "SELECT * FROM tbl_reg_user_notaris WHERE areapajak='$IdKK'";
		// new aldes
		$appDbLink = $DBLink;
		if($NotarisUserId!="") $sQuery.=" AND userId LIKE ('%".mysqli_escape_string($appDbLink, $NotarisUserId)."%')";
		// print_r($NotarisStatus);
		if($NotarisStatus!="") $sQuery.=" AND status = '".$NotarisStatus."'";
		$sQuery.=" order by ID";
		// die(var_dump($DBLink));
		// echo $sQuery;
		if($result=mysqli_query($appDbLink, $sQuery)){
			$jumlah=mysqli_num_rows($result);
			if($jumlah>0)
				{
					$no=0;
					while($row=mysqli_fetch_array($result)){
					$no++;
					$action="";
					if($row['status']==0){
								$action.="
								<a href='main.php?param=".base64_encode("a=$a&m=$m&dataId=".$row['id']."&NotarisAct=1&NotarisStatus=$NotarisStatus&NotarisUserId=$NotarisUserId")."'><img src='./image/icon/email_go.png' title='kirim ulang email validasi' alt='kirim ulang email validasi'></a>";
							}
					else if($row['status']==1){
								$action.="<a href='main.php?param=".base64_encode("a=$a&m=$m&dataId=".$row['id']."&NotarisAct=2&NotarisStatus=$NotarisStatus&NotarisUserId=$NotarisUserId")."'><img src='./image/icon/accept.png' title='DITERIMA' alt='DITERIMA'></a>&nbsp;<a href='main.php?param=".base64_encode("a=$a&m=$m&dataId=".$row['id']."&NotarisAct=3&NotarisStatus=$NotarisStatus&NotarisUserId=$NotarisUserId")."'><img src='./image/icon/reject.png' title='DITOLAK' alt='DITOLAK'></a>";
							}
					else if($row['status']==2){
								$action.="<a href='main.php?param=".base64_encode("a=$a&m=$m&dataId=".$row['id']."&NotarisAct=4&NotarisStatus=$NotarisStatus&NotarisUserId=$NotarisUserId")."'><img src='./image/icon/delete.png' height='15' width='15' alt='BLOKIR' title='BLOKIR'></img></a>";
							}
					else if($row['status']==3){
								$action.="status :telah ditolak";
							}
					else if($row['status']==4){
								$action.="<a href='main.php?param=".base64_encode("a=$a&m=$m&dataId=".$row['id']."&NotarisAct=5&NotarisStatus=$NotarisStatus&NotarisUserId=$NotarisUserId")."'><img src='./image/icon/accept.png' height='15' width='15' alt='AKTIFKAN' title='AKTIFKAN'>";
							}
	
					echo "	<tr>
						<td align='center'>".$no."</td>
						<td align='center'>".addslashes($row['userId'])."</td>
						<td align='center'>".md5($row['password'])."</td>
						<td align='center'>".$row['nm_lengkap']."</td>
						<td align='center'>".$row['email']."</td>
						<td align='center'>".$row['no_tlp']."</td>
						<td align='center'>".$row['almt_jalan']."</td>
						<td align='center'>".$row['almt_kota']."</td>
						<td align='center'>".$row['no_identitas']."</td>
						<td align='center'>
						$action
						</td>
						</tr>
						";
					
					}
				}
				else
				{
					echo "<td align='center' colspan='10'>No Records Found</td>";
				}
		}
		echo "</table>";
	}
	
}
?>