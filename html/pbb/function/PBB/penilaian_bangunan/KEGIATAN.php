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
}
/*$f=$func[2][id]; <a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">This</a>*/
?>
<!-------------------------------code------------->

<script type="text/javascript">
function DelAll(){
	var b=confirm("Apakah anda yakin menghapus dengan mode All?");
	if(b==false){
		return false;
	}else{
		return true;
	}
}
var x=0;
function jumlahElement(){
	n2 = document.getElementById("form2").elements.length;
	n2 = (n2-2)/3;
	return n2;
}
function getDataSama(b){
	var idB = document.getElementById(b);
	var b = idB.value;
	var bId = idB.id;
	var brek=0;
	for(i=0;i<jumlahElement();i++){
	idA=document.getElementById("kode_pekerjaan["+i+"]");
	a=idA.value;
	aId=idA.id;
		if(a==b && aId!=bId && brek!=1){
		 x++;
		 brek=1;
		}
	}
	return x;
}
function kdProses(a,b){
	nT=getDataSama(b);
	var c = b.length;
	b=b.substring(15,c-1);
	<?php
	$sqlTampil="SELECT CPM_KODE_PEKERJAAN, MAX(CPM_KODE)+1 as tCPM_KODE FROM cppmod_pbb_kegiatan group by CPM_KODE_PEKERJAAN;";
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	while($r=mysqli_fetch_assoc($result)){ ?>if(a==<?php echo $r['CPM_KODE_PEKERJAAN']; ?>){
	val=<?php echo $r['tCPM_KODE']; ?>+nT;
		if(val<10){ val="0"+val; }
	document.getElementById("kode["+b+"]").value=val;
	} else <?php } ?>{ 
	val=1+nT;
		if(val<10){ val="0"+val; }
	document.getElementById("kode["+b+"]").value=val; }
}
function prosesDel(a,a_link){
	var b=confirm("Anda akan menghapus kode "+a+" ?");
	if(b==false){
		return false;
	}else{
		window.open(a_link,"_parent");
		return true;
	}
}
var n=7;
var id=5;
function addRows(){
	var row=document.getElementById("tableAdd").insertRow(n);
	row.insertCell(0).innerHTML="<select name='kode_pekerjaan[]' id='kode_pekerjaan["+id+"]' onchange='kdProses(this.value,this.id)'><option value='0'>Pilih...</option><?php $sqlTampil='SELECT * FROM cppmod_pbb_pekerjaan;';
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	while($r=mysqli_fetch_assoc($result)){ ?><option value='<?php echo $r[CPM_KODE]; ?>'><?php echo $r[CPM_NAMA]; ?></option><?php } ?></select>";
	row.insertCell(1).innerHTML="<input name='kode[]' type='text' id='kode["+id+"]'  readonly='true'>";
	row.insertCell(2).innerHTML="<input name='nama[]' type='text' id='nama[]' maxlength='255' />";
	
	
	n++;
	id++;
}
function addRowsMulti(){
	for(i=0;i<5;i++){
		addRows();
	}
}
function Check(){
         allCheckList = document.getElementById("form1").elements;
         jumlahCheckList = allCheckList.length;
         if(document.getElementById("tombolCheck").value == "Pilih Semua"){
            for(i = 0; i < jumlahCheckList; i++){
                allCheckList[i].checked = true;
            }
            document.getElementById("tombolCheck").value = "Batal Pilih Semua";
         }else{
            for(i = 0; i < jumlahCheckList; i++){
                allCheckList[i].checked = false;
         }
            document.getElementById("tombolCheck").value = "Pilih Semua";
         }
}
</script>
<div align="left">
<h3>SISTEM PENILAIAN BANGUNAN <br /> 
(TABEL KEGIATAN) </h3>
<br />
<?php
function nmKodePekerjaan($kode_pekerjaan){
	global $dbSpec;
	$sqlTampil="SELECT * FROM cppmod_pbb_pekerjaan where CPM_KODE='$kode_pekerjaan';";
	$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
	$re=mysqli_fetch_assoc($result);  
	return $re['CPM_NAMA'];
}
if(!empty($_REQUEST['btTambah'])){  
	$jArray = count($_REQUEST['nama']);
	for($i=0;$i<$jArray;$i++){
		$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i]; 
		$kode = $_REQUEST['kode'][$i]; 
		$nama = $_REQUEST['nama'][$i];
		if(!empty($kode_pekerjaan) and !empty($kode) and !empty($nama)){
	$sqlTampil="INSERT INTO cppmod_pbb_kegiatan (CPM_KODE_PEKERJAAN, CPM_KODE, CPM_NAMA) VALUES ('$kode_pekerjaan', '$kode', '$nama');";
			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
	}
	if($bOK){
		echo"<b>".($bOK-1)." data ditambahkan !</b>";
	}else{ echo mysqli_error($DBLink); }

}elseif(!empty($_REQUEST['btEdit'])){  

	$jArray = count($_REQUEST['kode']);
	for($i=0;$i<$jArray;$i++){
		$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
		$kode = $_REQUEST['kode'][$i];
		$nama = $_REQUEST['nama'][$i];
		$sqlTampil="UPDATE cppmod_pbb_kegiatan SET CPM_NAMA='$nama' WHERE CPM_KODE_PEKERJAAN ='$kode_pekerjaan' and  CPM_KODE ='$kode' ;";
		$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
	}
	if($bOK){
		echo"<b>".($bOK-1)." data diubah!</b>";
	}else{ echo mysqli_error($DBLink); }

}elseif(!empty($_REQUEST['btHapus'])){  

	$jArray = count($_REQUEST['kode_pekerjaan']);
	if($jArray==0){ $jArray=1; }
	for($i=0;$i<$jArray;$i++){
		$kode = $_REQUEST['kode'][$i];
		$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i]; 
		if(empty($kode) and empty($kode_pekerjaan)){
		$kode=$_REQUEST['kode2'];
		$kode_pekerjaan=$_REQUEST['kode_pekerjaan2'];
		}
		if(!empty($kode) and !empty($kode_pekerjaan)){
		$sqlTampil="DELETE FROM cppmod_pbb_kegiatan WHERE CPM_KODE ='$kode' AND CPM_KODE_PEKERJAAN ='$kode_pekerjaan';";
		$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
	}
	if($bOK){
		echo"<b>".($bOK-1)." data dihapus!</b>";
	}else{ echo mysqli_error($DBLink); }

}
//echo "<br>"; print_r($_REQUEST);
if(empty($_REQUEST['tambahData']) and empty($_REQUEST['editData']) ){ ?>
<form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">
	<input name="tambahData" type="submit" id="tambahData" value="Tambah Data" />
	<input name="editData" type="submit" id="editData" value="Ubah" />
	<input name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus" />
<table width="" border="1" cellspacing="0" cellpadding="3">
  <tr>
    <th scope="col"><input onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck" /></th>
    <th scope="col">NO</th>
    <th scope="col"> PEKERJAAN </th>
    <th scope="col">KODE</th>
    <th scope="col">NAMA </th>
    <th scope="col">PROSES</th>
  </tr>
<?php 
		$sqlTampil="SELECT * FROM cppmod_pbb_kegiatan order by CPM_KODE_PEKERJAAN,CPM_KODE asc;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		$i=0;
		while($r=mysqli_fetch_assoc($result)) {
		$no++;
?>  
  <tr>
    <td><input name="kode[<?php echo $i;?>]" type="checkbox" id="kode[]" value="<?php echo $r['CPM_KODE']; ?>" />
	<input name="kode_pekerjaan[<?php echo $i;?>]" type="hidden" id="kode_pekerjaan[]" value="<?php echo $r['CPM_KODE_PEKERJAAN']; ?>" /></td>
    <td><?php echo $no; ?></td>
    <td><?php echo nmKodePekerjaan($r['CPM_KODE_PEKERJAAN']); ?></td>
    <td><?php echo $r['CPM_KODE']; ?></td>
    <td><?php echo $r['CPM_NAMA']; ?></td>
    <td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kode2=$r[CPM_KODE]&kode_pekerjaan2=$r[CPM_KODE_PEKERJAAN]");?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kode2=$r[CPM_KODE]&kode_pekerjaan2=$r[CPM_KODE_PEKERJAAN]");?>')">Hapus</a></td>
  </tr>
<?php $i++; } ?>  
</table>
</form>
<?php }else if(!empty($_REQUEST['tambahData'])){ ?>
<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">
<input name="" type="button" value="Tambah Baris" onclick="addRowsMulti()" />
<table width="415" border="0" cellpadding="3" cellspacing="0" id="tableAdd">
  <tr>
    <th colspan="3">Tambah Data </th>
  </tr>
  <tr>
    <td>Kode Pekerjaan </td>
    <td>Kode</td>
    <td width="105">Nama</td>
  </tr>
  <?php for($i=0; $i<5; $i++){ ?>
  <tr>
    <td><select name="kode_pekerjaan[]" id="kode_pekerjaan[<?php echo $i; ?>]" onchange="kdProses(this.value,this.id)">
	  <option value="0">Pilih...</option>
<?php   
		$sqlTampil="SELECT * FROM cppmod_pbb_pekerjaan;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while($r=mysqli_fetch_assoc($result)){ ?>
	  <option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
<?php } ?>
	</select></td>
    <td><input name="kode[]" type="text" id="kode[<?php echo $i; ?>]"  readonly="true"></td>
    <td><input name="nama[]" type="text" id="nama[]" maxlength="255" /></td>
  </tr>
  <?php } ?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><div align="right">
      <input name="btTambah" type="submit" id="btTambah" value="Simpan" />
    </div></td>
  </tr>
</table>
</form>
<?php }else if(!empty($_REQUEST['editData'])){?>
<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">
<table width="450" border="0" cellpadding="3" cellspacing="0">
  <tr>
    <th colspan="3">Ubah Data </th>
    </tr>
	<tr>
	  <td width="164">Kode Pekerjaan </td>
      <td width="144">Kode </td>
      <td width="144">Nama</td>
	</tr>
	<?php
	$jArray = count($_REQUEST['kode_pekerjaan']);
	if($jArray==0){ $jArray=1; }
	for($i=0;$i<$jArray;$i++){
	$kode = $_REQUEST['kode'][$i]; 
	$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i]; 
	if(empty($kode) and empty($kode_pekerjaan)){ 
	$kode=$_REQUEST['kode2']; 
	$kode_pekerjaan=$_REQUEST['kode_pekerjaan2']; 
	}
	if(empty($kode) and !empty($kode_pekerjaan)){ continue; }
	$sqlTampil="SELECT * FROM cppmod_pbb_kegiatan where CPM_KODE='$kode' and  CPM_KODE_PEKERJAAN='$kode_pekerjaan';";
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	$r=mysqli_fetch_assoc($result);
	?>
	<tr>
	  <td><?php   
	$sqlTampil="SELECT * FROM cppmod_pbb_pekerjaan where CPM_KODE='$r[CPM_KODE_PEKERJAAN]';";
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	$re=mysqli_fetch_assoc($result); ?>
        <input name="nama_pekerjaan[]" type="text" id="nama_pekerjaan[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
        <input name="kode_pekerjaan[]" type="hidden" id="kode_pekerjaan[]" value="<?php echo $re['CPM_KODE']; ?>" /></td>
    <td><input name="kode[]" type="text" id="kode[]" value="<?php echo $r['CPM_KODE']; ?>" readonly="true" /></td>
    <td width="144"><input name="nama[]" type="text" id="nama[]"  value="<?php echo $r['CPM_NAMA']; ?>" maxlength="255" /></td>
	</tr>
	<?php } ?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><div align="right"><input name="btEdit" type="submit" id="btEdit" value="Simpan" /></div></td>
  </tr>
</table>
</form>
<?php }?>
</div>
