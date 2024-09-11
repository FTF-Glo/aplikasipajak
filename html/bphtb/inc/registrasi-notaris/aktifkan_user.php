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



if(isset($dataId) || isset($nameUser) || isset($pwdUser) || isset($email)){
		//$kpd=$email;
		//$subject="test";
		//$pesan="test";
		//$kirim=mail($kpd,$subject,$pesan);
		$keterangan="approve";
		$status=2;
		$nameUser=addslashes($nameUser);
		$sqlUpdate="UPDATE tbl_reg_user_notaris SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
		$sqlUbah="UPDATE central_user SET CTR_U_BLOCKED='0' WHERE CTR_U_UID='$nameUser'";
		$bOK2= $dbSpec->sqlQuery($sqlUbah, $result);
		$bOK = $dbSpec->sqlQuery($sqlUpdate, $result);
		//$jumlah=mysqli_num_rows($result);

		if ($bOK || $bOK) {
					echo "<div>Berhasil diaktifkan...</div>\n";
		} else {
					echo "<script language='javascript'>alert('Gagal diaktifkan!.')</script>";
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