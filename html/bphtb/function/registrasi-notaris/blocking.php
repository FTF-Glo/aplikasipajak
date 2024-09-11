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
// $bOK=$dbSpec->sqlQuery($Qry,$result);
$result = mysqli_query($DBLink, $Qry);
$Key=mysqli_fetch_array($result);

$IdKK=$Key['CPC_TK_ID'];
$NameKK=$Key['CPC_TK_KABKOTA'];
/*-----------------------------------------------------------------*/

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
} // end of c_uuid

if(isset($dataId) || isset($nameUser) || isset($pwdUser) || isset($email)){
		$keterangan="reject";
		$status=3;
		$newuuid=c_uuid();
		$sqlUpdate="UPDATE tbl_reg_user_notaris SET uuid='$newuuid',status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
		$bOK = $dbSpec->sqlQuery($sqlUpdate, $result);
		// $result = mysqli_query($DBLink, $sqlUpdate);
		//$jumlah=mysqli_num_rows($result);

		if ($bOK) {
					echo "<div>Berhasil ditolak...</div>\n";
		} else {
					echo "<script language='javascript'>alert('Gagal ditolak!.')</script>";
				}
		/*
		if($kirim){
						echo "<div>Berhasil dikonfirmasi email...</div>\n";
					}
					else{echo "<div>Gagal dikonfirmasi email...</div>\n";}
		*/
		$url64 = base64_encode("a=$a&m=$m");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
		echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
}

?>