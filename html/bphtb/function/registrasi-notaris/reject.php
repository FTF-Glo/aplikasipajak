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
// $result = mysqli_query($DBLink, $Qry);
$bOK=$dbSpec->sqlQuery($Qry,$result);
$Key=mysqli_fetch_array($result);

$IdKK=$Key['CPC_TK_ID'];
$NameKK=$Key['CPC_TK_KABKOTA'];
/*-----------------------------------------------------------------*/


$tampil= "<div class='content-wrapper' style='padding-top:0px;padding-bottom:0px'>
<div class='spacer10'></div>
	  <div class='subTitle'>Sudah ditolak</div>
	  <div class='spacer5'></div>";
$tampil.="<table width='100%' cellspacing='1' cellpadding='1' border='0' bgcolor='#FF9900'>";
$tampil.="
			<tr>
				<th align='center'><b>NO</b></th>
				<th align='center'><b>USER ID</b></th>
				<th align='center'><b>PASSWORD</b></th>
				<th align='center'><b>NAMA LENGKAP</b></th>
				<th align='center'><b>EMAIL</b></th>
				<th align='center'><b>NO TELEPON/HP</b></th>
				<th align='center'><b>JALAN</b></th>
				<th align='center'><b>KOTA</b></th>
				<th align='center'><b>NO IDENTITAS</b></th>
			</tr>

						";

		$sqlCek1="SELECT *FROM tbl_reg_user_notaris WHERE status='3' AND areapajak='$IdKK' ORDER BY id DESC LIMIT 10";
		$bOK = $dbSpec->sqlQuery($sqlCek1, $result);
		// $result = mysqli_query($DBLink, $sqlCek1);
		$jumlah=mysqli_num_rows($result);
		if($bOK){
			if($jumlah>0){
				$no=0;
				while($dataTampil1=mysqli_fetch_array($result)){
				$tag=array("3"=>"Reject");
				$no++;
				$tampil.="
						<tr>
							<td align='center'>".$no."</td>
							<td align='center'>".$dataTampil1['userId']."</td>
							<td align='center'>".md5($dataTampil1['password'])."</td>
							<td align='center'>".$dataTampil1['nm_lengkap']."</td>
							<td align='center'>".$dataTampil1['email']."</td>
							<td align='center'>".$dataTampil1['no_tlp']."</td>
							<td align='center'>".$dataTampil1['almt_jalan']."</td>
							<td align='center'>".$dataTampil1['almt_kota']."</td>
							<td align='center'>".$dataTampil1['no_identitas']."</td>
						</tr>
							";
				}
			}
			else{
				$tampil.= "<tr><td colspan=10 align='center'>Maaf, data belum ada</td></tr>";
				}
		}
			
$tampil.="</table></div>";
?>
<div><?php echo $tampil;?></div>