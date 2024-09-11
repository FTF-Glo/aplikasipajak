<?php
if(!isset($data)){
	die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if(isset($arAreaConfig['terminalColumn'])){
	$terminalColumn = $arAreaConfig['terminalColumn'];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if(!$accessible){
		echo"Illegal access";
		return;
	}
}?>
<!-------------------------------code------------->
<?php
if(!empty($_REQUEST[fdel])){
	$id_siswa=$_REQUEST[id_siswa];
	$queri="DELETE FROM dbffsr WHERE dbffsr.id =$id_siswa";
	$qu=mysql_query($queri) or die(mysqli_error($DBLink));
	if($qu){
	?>
		<script type="text/javascript">window.open('main.php?param=<?php echo base64_encode("a=$a&m=$m")?>','_parent');</script>
	<?php } ?> 
<?php }?>

<?php
if(!empty($_REQUEST[fadd])){
	$nama=$_REQUEST[nama];
	$kelas=$_REQUEST[kelas];
	$queri="INSERT INTO  dbffsr (
	id ,
	nama ,
	kelas
	)
	VALUES (NULL ,  '$nama',  '$kelas');";
	$qu=mysql_query($queri) or die(mysqli_error($DBLink));
	if($qu){
	?>
		<script type="text/javascript">window.open('main.php?param=<?php echo base64_encode("a=$a&m=$m")?>','_parent');</script>
	<?php } ?> 
<?php }?>

<?php
if(!empty($_REQUEST[fedit])){
	$id_siswa=$_REQUEST[id_siswa];
	$nama=$_REQUEST[nama];
	$kelas=$_REQUEST[kelas];
	$queri="UPDATE VSI_SWITCHER_DEVEL.dbffsr SET nama = '$nama', kelas = '$kelas' WHERE dbffsr.id = '$id_siswa' LIMIT 1;";
	$qu=mysql_query($queri) or die(mysqli_error($DBLink));
	if($qu){
	?>
		<script type="text/javascript">window.open('main.php?param=<?php echo base64_encode("a=$a&m=$m")?>','_parent');</script>
	<?php } ?> 
<?php }?>


Fungsi 0 tidak berjalan

