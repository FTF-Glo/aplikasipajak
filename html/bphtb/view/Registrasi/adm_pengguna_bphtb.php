<?php
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
        //echo $AreaPajak;
	/*-----------------------------------------------------------------*/
	$Qry="SELECT * FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";
        //echo $Qry;
	$bOK=$dbSpec->sqlQuery($Qry,$result);
	$Key=mysqli_fetch_array($result);

	$IdKK=$Key['CPC_TK_ID'];
	$NameKK=$Key['CPC_TK_KABKOTA'];
        //echo $IdKK;
	/*-----------------------------------------------------------------*/
	$txtCari=isset($txtCari)?mysqli_real_escape_string($DBLink, $txtCari):"";
	$act=isset($act)?intval($act):"";
	if(isset($dataId) && $act!="" && isset($nameUser))
	{
			if($act==1){
				$status="1";
				$keterangan="blokir";
				$nameUser=addslashes($nameUser);
				$sqlUpdate="UPDATE tbl_reg_user_bphtb SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
				$sqlUbah="UPDATE central_user SET CTR_U_BLOCKED='1' WHERE CTR_U_UID='$nameUser'";
				$bOK = $dbSpec->sqlQuery($sqlUpdate, $result); 
				$bOK2 = $dbSpec->sqlQuery($sqlUbah, $result); 
				if($bOK||$bOK2){
					echo "Berhasil diblokir..";
				}
				else{
					echo "Gagal diblokir..";
				}
				$url64 = base64_encode("a=$a&m=$m");
				echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
				echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
			}else if($act==2){
				$status="0";
				$keterangan="aktif";
				$nameUser=addslashes($nameUser);
				$sqlUpdate="UPDATE tbl_reg_user_bphtb SET status='$status',keterangan='$keterangan' WHERE id='$dataId' AND areapajak='$IdKK'";
				$sqlUbah="UPDATE central_user SET CTR_U_BLOCKED='0' WHERE CTR_U_UID='$nameUser'";
				$bOK = $dbSpec->sqlQuery($sqlUpdate, $result); 
				$bOK2 = $dbSpec->sqlQuery($sqlUbah, $result); 
				if($bOK||$bOK2){
					echo "Berhasil diunblokir..";
				}
				else{
					echo "Gagal diunblokir..";
				}
				$url64 = base64_encode("a=$a&m=$m");
				echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
				echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
			}else if($act==3){
				$status="0";
				$keterangan="aktif";
				$nameUser=addslashes($nameUser);
				$sqlUpdate="DELETE FROM tbl_reg_user_bphtb WHERE id='$dataId' AND areapajak='$IdKK'";
				$bOK = $dbSpec->sqlQuery($sqlUpdate, $result); 
				$bOK2 = $Setting->DeleteUser($uuid);
				if($bOK||$bOK2){
					echo "Berhasil Delete..";
				}
				else{
					echo "Gagal di Delete..";
				}
				$url64 = base64_encode("a=$a&m=$m");
				echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
				echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
			}
	}

	echo '<form method="POST" action="main.php?param='.base64_encode("a=$a&m=$m").'">';
	echo '<table>';
	echo '<tr>';
	echo '	<td>Pencarian</td>';
	echo '	<td><input type="text" name="txtCari" size="25" value="'.$txtCari.'"></td>';
	echo '	<td><input type="submit" name="submit" value="Search pengguna"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form><br>';
	echo '<table width="100%" cellspacing="1" cellpadding="1" border="0" bgcolor="#FF9900">';
	echo '<tr>';
	echo '	<th align="center"><b>NO.</b></th>';
	echo '	<th align="center"><b>USER ID</b></th>';
	echo '	<th align="center"><b>PASSWORD</b></th>';
	echo '	<th align="center"><b>NAMA LENGKAP</b></th>';
	echo '	<th align="center"><b>EMAIL</b></th>';
	echo '	<th align="center"><b>NO TELEPON/HP</b></th>';
	echo '	<th align="center"><b>SEBAGAI</b></th>';
	echo '	<th align="center"><b>NIP</b></th>';
	echo '	<th align="center"><b>NO IDENTITAS/KTP</b></th>';
	echo '	<th align="center"><b>STATUS</b></th>';
	echo '	<th align="center"><b>AKSI</b></th>';
	echo '</tr>';
	if($txtCari!="")
		$sqlCari="SELECT * FROM tbl_reg_user_bphtb WHERE userId LIKE ('%$txtCari%') AND areapajak='$IdKK' ORDER BY id";
	else
		$sqlCari="SELECT * FROM tbl_reg_user_bphtb WHERE areapajak='$AreaPajak' ORDER BY id";
		
	// echo $sqlCari;
	if($result=mysqli_query($DBLink, $sqlCari)){
		$jumlah=mysqli_num_rows($result);
		if($jumlah>0)
		{
			$no=0;
			while($dataTampil=mysqli_fetch_array($result))
			{
				$no++;
				echo '<tr>';
				echo '<td align="center">'.$no.'</td>';
				echo '<td align="center">'.addslashes($dataTampil['userId']).'</td>';
				echo '<td align="center">'.md5($dataTampil['password']).'</td>';
				echo '<td align="center">'.$dataTampil['nm_lengkap'].'</td>';
				echo '<td align="center">'.$dataTampil['email'].'</td>';
				echo '<td align="center">'.$dataTampil['no_hp'].'</td>';
				echo '<td align="center">'.$dataTampil['jabatan'].'</td>';
				echo '<td align="center">'.$dataTampil['nip'].'</td>';
				echo '<td align="center">'.$dataTampil['no_ktp'].'</td>';
				echo '<td align="center">'.($dataTampil['status']==0?"Aktif":"diBlok").'</td>';
				echo '<td align="center">';
				/*
				if($dataTampil['status']==0)
					echo "<a href=\"main.php?param=".base64_encode("a=$a&m=$m&dataId=".$dataTampil['id']."&act=1&nameUser=".$dataTampil['userId']."&uuid=".$dataTampil['uuid'])."\" onClick=\"Cek=confirm('Apakah Anda yakin?');if(!Cek){return false;}\"><img src=\"./image/icon/delete.png\" alt=\"BLOK\" title=\"BLOK\"></img></a>";
				else
					echo "<a href=\"main.php?param=".base64_encode("a=$a&m=$m&dataId=".$dataTampil['id']."&act=1&nameUser=".$dataTampil['userId']."&uuid=".$dataTampil['uuid'])."\" onClick=\"Cek=confirm('Apakah Anda yakin?');if(!Cek){return false;}\"><img src=\"./image/icon/accept.png\" alt=\"UNBLOK\" title=\"UNBLOK\"></img></a>";
				//echo "<a href=\"main.php?param=".base64_encode("a=$a&m=$m&dataId=".$dataTampil['id']."&act=3&nameUser=".$dataTampil['userId']."&uuid=".$dataTampil['uuid'])."\" onClick=\"Cek=confirm('Apakah Anda yakin?');if(!Cek){return false;}\"><img src=\"./image/icon/cross.png\" alt=\"HAPUS\" title=\"HAPUS\"></img></a>";
				*/
				echo printOption(array("dataId"=>$dataTampil['id'],"nameUser"=>$dataTampil['userId'],"uuid"=>$dataTampil['uuid']));
				echo '</td>';
				echo '</tr>';
				
			}
		}
		else
		{
			echo "<tr><td align='center' colspan='11'>No Records Found</td></tr>";
		}
	}	
	echo '</table>';
}