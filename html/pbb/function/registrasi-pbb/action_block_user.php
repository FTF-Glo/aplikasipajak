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

//print_r("<pre>");
//print_r($_REQUEST);
//print_r("</pre>");

if(isset($dataId)||isset($nameUser)||isset($pwdUser)||isset($email))
{
		$status="1";
		$keterangan="blokir";
		$nameUser=addslashes($nameUser);
		$sqlUpdate="UPDATE tbl_reg_user_pbb SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND kota='$IdKK'";
		$sqlUbah="UPDATE central_user SET CTR_U_BLOCKED='1' WHERE CTR_U_UID='$nameUser'";
		$bOK = $dbSpec->sqlQuery($sqlUpdate, $result); 
		$bOK2 = $dbSpec->sqlQuery($sqlUbah, $result); 
		if($bOK||$bOK2){
			echo "\nBerhasil diblokir..";
		}
		else{
			echo "\nGagal diblokir..";
		}
		$url64 = base64_encode("a=$a&m=$m&f=f429");
                echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
                echo " \n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...\n";
}
?>