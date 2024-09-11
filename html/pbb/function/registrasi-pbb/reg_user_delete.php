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


$func=$func[0][id];
$kata="pencetak tagihan";
if(isset($dataId)||isset($nameUser))
{
		$nameUser=addslashes($nameUser);
		$sqlHapus="DELETE FROM tbl_reg_user_pbb WHERE id='$dataId' AND kota='$IdKK'";
		$sqlDelete="DELETE FROM central_user WHERE CTR_U_UID='$nameUser'";
		$bOK = $dbSpec->sqlQuery($sqlHapus, $result); 
		$bOK2 = $dbSpec->sqlQuery($sqlDelete, $result); 
		if($bOK||$bOK2){
			echo "\nBerhasil dihapus..";
		}
		else{
			echo "\nGagal dihapus..";
		}
		$url64 = base64_encode("a=$a&m=$m&f=$func");
		echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
		echo "\n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...";
}
else
{
	echo "\n<script>alert('Id tidak teridentifikasi')</script>";
}
?>