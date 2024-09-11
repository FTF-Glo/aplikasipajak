<?php
if ($data) {
	$uid = $data->uid;
	
	// get module
	$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
	if (!$bOK) {
		return false;
	}

//Tulis di sini
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';

if(isset($submit)){
$tampil="form belum dibuat";
}
?>
<br>
<form method="POST" action="main.php?param=<?php echo base64_encode("a=$a&m=$m")?>">
<input type="submit" value="Add new configuration" name="submit">	
</form>
<?php echo  $tampil;?>
<br><br>
<table width="50%" cellspacing="1" cellpadding="1" border="0" bgcolor="#FF9900">
	<tr>
		<th align="center"><b>ID</b></th>
		<th align="center"><b>NAME</b></th>
		<th align="center"><b>VALUE</b></th>
		<th align="center"><b>AKSI</b></th>
	</tr>
<?php
$queryView="SELECT *FROM TBL_CONFIG_USER_REG";
$bOk=$dbSpec->sqlQuery($queryView, $result);
if($bOk){
			while($dataTampil=mysqli_fetch_array($result))
			{
				?>
					<tr>
					<td align="center"><?php echo  $dataTampil['ID_CONFIG'];?></td>
					<td><?php echo  $dataTampil['NAME'];?></td>
					<td><?php echo  $dataTampil['VALUE'];?></td>
					<td align="center">
					<a href='#'><img src="./image/icon/pencil_16.png" alt="EDIT" title="EDIT"></img></a>&nbsp;&nbsp;
					<a href='#'><img src="./image/icon/cross.png" alt="DELETE" title="DELETE"></img></a>
					</td>
					</tr>
				<?php
			}
}
?>
</table>
<?php
}
?>