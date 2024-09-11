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

if(isset($dataId) || isset($nameUser) || isset($pwdUser) || isset($email)){
		//$kpd=$email;
		//$subject="test";
		//$pesan="test";
		//$kirim=mail($kpd,$subject,$pesan);
		$keterangan="reject";
		$status=2;
		$sqlUpdate="UPDATE TBL_REG_USER SET status='$status',keterangan='$keterangan' WHERE id='$dataId'";
		$bOK = $dbSpec->sqlQuery($sqlUpdate, $result);
		//$jumlah=mysqli_num_rows($result);

		if ($bOK) {
					echo "<div>Berhasil di-reject...</div>\n";
		} else {
					echo "<script language='javascript'>alert('Gagal di-reject!.')</script>";
				}
		/*
		if($kirim){
						echo "<div>Berhasil dikonfirmasi email...</div>\n";
					}
					else{echo "<div>Gagal dikonfirmasi email...</div>\n";}
		*/
		$url64 = base64_encode("a=$a&m=$m");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
		echo "Tunggu beberapa saat... <img src='image/icon/wait.gif' alt=''></img>\n";
}

?>