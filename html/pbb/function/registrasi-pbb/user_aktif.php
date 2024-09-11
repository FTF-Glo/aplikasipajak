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

$query="SELECT * FROM cppmod_tax_kabkota WHERE CPC_TK_ID='".$AreaPajak."'";
$bOK=$dbSpec->sqlQuery($query, $result);
$Key=mysqli_fetch_array($result);

$IdKK=$Key['CPC_TK_ID'];
$NameKK=$Key['CPC_TK_KABKOTA'];
/*-----------------------------------------------------------------*/

?>
<div class="col-md-12">
	<form method="POST" action="main.php?param=<?=base64_encode("a=$a&m=$m&f=$f")?>">
		<table>
		<tr>
			<td>Pencarian</td>
			<td><input type="text" name="txtCari" size="25"></td>
			<td><input type="submit" name="submit" value="Search pengguna"></td>
		</tr>
		</table>
	</form><br>
	<div class="table-responsive">
		<table class="table table-bordered table-striped table-hover">
			<thead>
				<tr>
					<th class=tdheader>NO.</th>
					<th class=tdheader>USER ID</th>
					<th class=tdheader>NAMA LENGKAP</th>
					<th class=tdheader>EMAIL</th>
					<th class=tdheader>NO TELEPON/HP</th>
					<th class=tdheader>SEBAGAI</th>
					<th class=tdheader>NIP</th>
					<th class=tdheader>NO IDENTITAS/KTP</th>
					<th class=tdheader>LAST ACCESS</th>
					<th class=tdheader>IP ADDRESS</th>
					<th class=tdheader>&nbsp;<br>AKSI<br>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
		<?php
		if(isset($submit))
		{
			$txtCari=mysqli_real_escape_string($dbSpec->getDBLink(), $txtCari);
			$sqlCari="SELECT A.*, B.CTR_RM_NAME FROM tbl_reg_user_pbb A LEFT JOIN
							cppmod_pbb_role_module B ON A.jabatan = B.CTR_RM_ID
							WHERE A.status='0' AND 
							A.userId LIKE ('%$txtCari%') AND A.kota='$IdKK' ORDER BY A.id";
							
			// echo $sqlCari;
				
			$bOK=$dbSpec->sqlQuery($sqlCari, $result);
			$jumlah=mysqli_num_rows($result);
			$no=0;
			if($bOK)
			{
				if($jumlah>0)
				{
					while($dataTampil=mysqli_fetch_array($result))
					{
						$no++;
					?>
						<tr>
						<td align="center"><?php echo $no;?></td>
						<td align="center"><?php echo addslashes($dataTampil['userId']);?></td>
						<td align="center"><?php echo $dataTampil['nm_lengkap'];?></td>
						<td align="center"><?php echo $dataTampil['email'];?></td>
						<td align="center"><?php echo $dataTampil['no_hp'];?></td>
						<td align="center"><?php echo $dataTampil['CTR_RM_NAME'];?></td>
						<td align="center"><?php echo $dataTampil['nip'];?></td>
						<td align="center"><?php echo $dataTampil['no_ktp'];?></td>
						<td align="center">

						<?php
							$key=array(
													"dataId"=>$dataTampil['id'],
													"nameUser"=>addslashes($dataTampil['userId']),
													"pwdUser"=>$dataTampil['password'],
													"email"=>$dataTampil['email']);
							echo printOption($key);
						?>
						</td>
						</tr>
		<?php
					}
				}
				else
				{
					echo "\n<td align='center' colspan='10'>No Records Found</td>";
				}
				
			}
			else
			{
				echo "\nMaaf, pencarian gagal";
			}
		}
		else
		{
		//aktif
		$func=$func[1]["id"];
		$sqlTampil="SELECT 
						A.*, 
						B.CTR_RM_NAME, 
						C.CTR_CUS_LASTSESSION AS LAST_ACCESS,
						C.CTR_CUS_IP AS IP
					FROM tbl_reg_user_pbb A 
					LEFT JOIN cppmod_pbb_role_module B ON A.jabatan = B.CTR_RM_ID 
					LEFT JOIN central_user_session C ON C.CTR_CUS_ID = A.ctr_u_id 
					WHERE 
						A.status='0' 
						AND A.kota='$IdKK' 
					ORDER BY A.id DESC ";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		$jumlah=mysqli_num_rows($result);
		$no=0;
		$hariini = date('Y-m-d');
				while($dataTampil=mysqli_fetch_array($result)){
					$no++;
					if(substr($dataTampil['LAST_ACCESS'],0,10)==$hariini){
						$class = 'class="bg-green"';
						$lblacc= 'Hari ini '. substr($dataTampil['LAST_ACCESS'],11);
					}else{
						$class = '';
						$lblacc= $dataTampil['LAST_ACCESS'];
					}
				?>
				<tr>
					<td align="right"><?=$no?></td>
					<td><?=addslashes($dataTampil['userId'])?></td>
					<td><?=$dataTampil['nm_lengkap']?></td>
					<td><?=$dataTampil['email']?></td>
					<td align="center"><?=$dataTampil['no_hp']?></td>
					<td><?=$dataTampil['CTR_RM_NAME']?></td>
					<td align="center"><?=$dataTampil['nip']?></td>
					<td align="center"><?=$dataTampil['no_ktp']?></td>
					<td <?=$class?>><?=$lblacc?></td>
					<td align="center"><?=$dataTampil['IP']?></td>
					<td align="center">
						<?php
							$key=array(
													"dataId"=>$dataTampil['id'],
													"nameUser"=>addslashes($dataTampil['userId']),
													"pwdUser"=>$dataTampil['password'],
													"email"=>$dataTampil['email']);
							echo printOption($key);
						?>
					</td>
				</tr>
				<?php
				}
		}
				?>
			<tbody>
		</table>
	</div>
</div>