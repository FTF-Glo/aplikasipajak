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


$func=$func[6][id];
echo "<div class='spacer10'></div>
	  <div class='subTitle'>Sudah diterima (aktif)</div>
	  <div class='spacer5'></div>";
echo "<table width='100%' cellspacing='1' cellpadding='1' border='0' bgcolor='#FF9900'>";
echo "
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
				<!-- <th align='center'><b>STATUS</b></th> -->
				<!-- <th align='center'><b>Keterangan</b></th> -->
				<th align='center'><b>AKSI</b></th>
			</tr>

						";

		$sqlCek1="SELECT * FROM TBL_REG_USER_NOTARIS WHERE status='2' AND areapajak='$IdKK' ORDER BY id DESC LIMIT 10";
		$bOK = $dbSpec->sqlQuery($sqlCek1, $result);
		$jumlah=mysqli_num_rows($result);
		if($bOK){
			if($jumlah>0){
				$no=0;
				while($dataTampil1=mysqli_fetch_array($result)){
				$tag=array("2"=>"Approve");
				$no++;
				?>
						<tr>
							<td align='center'><?php echo  $no?></td>
							<td align='center'><?php echo  addslashes($dataTampil1['userId']);?></td>
							<td align='center'><?php echo  md5($dataTampil1['password'])?></td>
							<td align='center'><?php echo  $dataTampil1['nm_lengkap']?></td>
							<td align='center'><?php echo  $dataTampil1['email']?></td>
							<td align='center'><?php echo  $dataTampil1['no_tlp']?></td>
							<td align='center'><?php echo  $dataTampil1['almt_jalan']?></td>
							<td align='center'><?php echo  $dataTampil1['almt_kota']?></td>
							<td align='center'><?php echo  $dataTampil1['no_identitas']?></td>
							<!-- <td align='center'>".$tag[$dataTampil1['status']]."</td> -->
							<!-- <td align='center'>&nbsp;</td> -->
							
							<td align='center'>
							<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$func&dataId=".$dataTampil1['id']."&nameUser=".addslashes($dataTampil1['userId'])."&pwdUser=".$dataTampil1['password']."&email=".$dataTampil1['email'])?>"><img src='./image/icon/delete.png' height='15' width='15' alt='BLOKIR' title='BLOKIR'></img></a>
							</td>
						</tr>
						<?
				}
			}
			else{
				echo "<tr><td colspan=10 align='center'>Maaf, data belum ada</td></tr>";
				}
		}
			
echo "</table>";
?>
