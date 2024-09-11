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
function iniAngka(evt){
         var charCode = (evt.which) ? evt.which : event.keyCode
         if ( charCode >= 48 && charCode <= 57 || charCode==8 )
            return true;

         return false;
      }
function DelAll(){
	var b=confirm("Apakah anda yakin menghapus dengan mode All?");
	if(b==false){
		return false;
	}else{
		return true;
	}
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
var idA=5;
function addRows(){
	var row=document.getElementById("tableAdd").insertRow(n);
	row.insertCell(0).innerHTML="<input name='kode[]' type='text' id='kode["+idA+"]' value='' readonly='true'/>";
	row.insertCell(1).innerHTML="<input name='jpb[]' type='text' id='jpb["+idA+"]' value='' onkeyup='kdProses(this.id)' maxlength='5' onkeypress='return iniAngka(event)' />";
	row.insertCell(2).innerHTML="<input name='tipe[]' type='text' id='tipe["+idA+"]' onkeyup='kdProses(this.id)' maxlength='5' onkeypress='return iniAngka(event)' />";
	row.insertCell(3).innerHTML="<input name='lantai_min[]' type='text' id='lantai_min["+idA+"]' onkeyup='kdProses(this.id)' maxlength='4' onkeypress='return iniAngka(event)'/>";
	row.insertCell(4).innerHTML="<input name='lantai_max[]' type='text' id='lantai_max["+idA+"]' onkeyup='cekKd(this.id)' maxlength='4' onkeypress='return iniAngka(event)'/>";
	
	n++;
	idA++;
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
function kdProses(a){
	var str=a.substring(0,3);
	var c = a.length;
	var va=document.getElementById(a).value; 
	var ta=document.getElementById(a); 
	var ia=parseInt(va,10);
	if(ia<1){ta.value="";}else if(ia<10){ta.value="0"+ia;}else if(ta.value.substring(0,1)=="0"){ta.value=ta.value.substring(1,255);}
	
	if(str=="jpb"){
		id=a.substring(4,c-1);
	}else if(str=="tip"){
		id=a.substring(5,c-1);
	}else if(str=="lan"){
		id=a.substring(11,c-1);
	}

	var k1=parseInt(document.getElementById("jpb["+id+"]").value,10);
	var k2=parseInt(document.getElementById("tipe["+id+"]").value,10); if(k2<10){ k2="00"+k2; }else if(k2<100){ k2="0"+k2; }
	var k3=parseInt(document.getElementById("lantai_min["+id+"]").value,10);
	kt = k1+"_"+k3+"_"+k2;
	document.getElementById("kode["+id+"]").value=kt;
}
function jumlahElement(){
	n2 = document.getElementById("form2").elements.length;
	n2 = (n2-2)/5;
	return n2;
}
function cekKd(a){
	var c = a.length;
	var id=a.substring(11,c-1);
	var kode=document.getElementById("kode["+id+"]");
	var kodev=kode.value;
	var kodeid=kode.id;
	<?php
	$sqlTampil="SELECT * FROM cppmod_pbb_bangunan ;";
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	while($r=mysqli_fetch_assoc($result)){ $kodeT=$r[CPM_KODE]; ?> 
	kodeT="<?php echo $kodeT; ?>";
	if(kodeT==kodev){ alert("Urutan kode sudah terdaftar!"); document.getElementById(a).value=""; }
	<?php } ?>
	for(i=0;i<jumlahElement();i++){
	var kode2=document.getElementById("kode["+i+"]");
	var kode2v=kode2.value;
	var kode2id=kode2.id;
	if(kode2v==kodev && kode2id!=kodeid && kode2v!=""){ alert("Urutan kode sedang diinput!"); document.getElementById(a).value=""; }
	}
}
</script>
<div align="left">
<h3>SISTEM PENILAIAN BANGUNAN <br /> 
(TABEL BANGUNAN) </h3>
<br />
<?php
if(!empty($_REQUEST['btTambah'])){  

	$jArray = count($_REQUEST['kode']);
	for($i=0;$i<$jArray;$i++){
		$kode = $_REQUEST['kode'][$i]; 
		$jpb = $_REQUEST['jpb'][$i]; //if($jpb<10){ $jpb="0".$jpb;}
		$tipe = $_REQUEST['tipe'][$i]; 
		$lantai_min = $_REQUEST['lantai_min'][$i];
		$lantai_max = $_REQUEST['lantai_max'][$i]; 
		if(!empty($kode) and !empty($jpb) and !empty($lantai_min) and !empty($lantai_max) ){
			$sqlTampil="INSERT INTO cppmod_pbb_bangunan (CPM_KODE, CPM_JPB, CPM_TIPE, CPM_LANTAI_MIN, CPM_LANTAI_MAX) VALUES ('$kode', '$jpb', '$tipe', '$lantai_min', '$lantai_max');";
			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
	}
	if($bOK){
		echo"<b>".($bOK-1)." data ditambahkan !</b>";
	}else{ echo mysqli_error($DBLink); }
	
}elseif(!empty($_REQUEST['btEdit'])){  
	$jArray = count($_REQUEST['kodeId']);
	for($i=0;$i<$jArray;$i++){
	$kodeId = $_REQUEST['kodeId'][$i];
	$kode = $_REQUEST['kode'][$i];
	$jpb = $_REQUEST['jpb'][$i];
	$tipe = $_REQUEST['tipe'][$i];
	$lantai_min = $_REQUEST['lantai_min'][$i];
	$lantai_max = $_REQUEST['lantai_max'][$i];
	
	$sqlTampil="UPDATE cppmod_pbb_bangunan SET 
	CPM_KODE='$kode', 
	CPM_JPB='$jpb', 
	CPM_TIPE='$tipe', 
	CPM_LANTAI_MIN='$lantai_min', 
	CPM_LANTAI_MAX='$lantai_max' 
	WHERE CPM_KODE ='$kodeId' ;";
		$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
	}
	if($bOK){
		echo"<b>".($bOK-1)." data diubah!</b>";
	}else{ echo mysqli_error($DBLink); }
	
}elseif(!empty($_REQUEST['btHapus'])){  
	$jArray = count($_REQUEST['kode']);
	if($jArray==0){
	$jArray=1;
	}
	for($i=0;$i<$jArray;$i++){
		$kode = $_REQUEST['kode'][$i];
		if(empty($kode)){
		$kode=$_REQUEST['kode2'];
		}
		if(!empty($kode)){
		$sqlTampil="DELETE FROM cppmod_pbb_bangunan WHERE CPM_KODE ='$kode' ;";
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
    <th width="68" scope="col"><input onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck" /></th>
	<th width="24" scope="col">NO</th>
    <th width="46" scope="col">KODE</th>
    <th width="54" scope="col">JPB </th>
    <th width="65" scope="col">TIPE</th>
    <th width="119" scope="col">LANTAI MIN</th>
    <th width="106" scope="col">LANTAI MAX</th>
    <th width="74" scope="col">PROSES</th>
  </tr>
<?php 
		$sqlTampil="SELECT * FROM cppmod_pbb_bangunan order by CPM_KODE ASC;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while($r=mysqli_fetch_assoc($result)) {
		$no++; ?>  
  <tr>
    <td><input name="kode[]" type="checkbox" id="kode[<?php echo $n;?>]" value="<?php echo $r['CPM_KODE']; ?>" /></td>
	<td><?php echo $no; ?></td>
    <td><?php echo $r['CPM_KODE']; ?></td>
    <td><?php echo $r['CPM_JPB']; ?></td>
    <td><?php echo $r['CPM_TIPE'];?></td>
    <td><div align="right"><?php echo $r['CPM_LANTAI_MIN']; ?></div></td>
    <td><div align="right"><?php echo $r['CPM_LANTAI_MAX'];?></div></td>
    <td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kode2=$r[CPM_KODE]");?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kode2=$r[CPM_KODE]");?>')">Hapus</a></td>
  </tr>
<?php } ?>  
</table>
</form>
<?php }else if(!empty($_REQUEST['tambahData'])){ ?>
<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">
<input name="" type="button" value="Tambah Baris" onclick="addRowsMulti()" />
<table width="617" border="0" cellpadding="3" cellspacing="0" id="tableAdd">
  <tr>
    <th colspan="5">Tambah Data</th>
  </tr>
  <tr>
    <td width="144">Kode</td>
    <td width="144">JPB</td>
    <td width="93">Tipe</td>
    <td width="110">Lantai Min </td>
    <td width="96">Lantai Max </td>
  </tr>
  <?php for($i=0; $i<5; $i++){ 
  		$tKode++;
		if($tKode<10){
		$tKode="0".$tKode;
		} ?>
  <tr>
    <td><input name="kode[]" type="text" id="kode[<?php echo $i; ?>]" value="" readonly="true"></td>
    <td><input name="jpb[]" type="text" id="jpb[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" value="" maxlength="5" onkeypress="return iniAngka(event)">
	<!--<select name="jpb[]" id="jpb[<?php echo $i; ?>]" onchange="kdProses(this.id)" >
	  <option value="0">Pilih...</option>
<?php   
		$sqlTampil="SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while($r=mysqli_fetch_assoc($result)){ ?>
	  <option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
<?php } ?>
	</select>--></td>
    <td><input name="tipe[]" type="text" id="tipe[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" maxlength="5" onkeypress="return iniAngka(event)" /></td>
    <td><input name="lantai_min[]" type="text" id="lantai_min[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" maxlength="4" onkeypress="return iniAngka(event)"/></td>
    <td><input name="lantai_max[]" type="text" id="lantai_max[<?php echo $i; ?>]" onkeyup="cekKd(this.id)" maxlength="4" onkeypress="return iniAngka(event)"/></td>
  </tr>
  <?php } ?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><div align="right"><input name="btTambah" type="submit" id="btTambah" value="Simpan" /></div></td>
  </tr>
</table>
</form>
<?php }else if(!empty($_REQUEST['editData'])){?>
<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">
<table width="677" border="0" cellpadding="3" cellspacing="0">
  <tr>
    <th colspan="5">Ubah Data </th>
    </tr>
  <tr>
    <td>Kode</td>
    <td>JPB</td>
    <td>Tipe</td>
    <td>Lantai Min </td>
    <td>Lantai Max </td>
  </tr>
	<?php
	$jArray = count($_REQUEST['kode']);
	if($jArray==0){  $jArray=1; }
	for($i=0;$i<$jArray;$i++){
	$kode = $_REQUEST['kode'][$i]; 
	if(empty($kode)){ 
	$kode=$_REQUEST['kode2']; 
	}
	$sqlTampil="SELECT * FROM cppmod_pbb_bangunan where CPM_KODE='$kode' ;";
	$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	$r=mysqli_fetch_assoc($result);
	?>
  <tr>
    <td width="105"><input name="kode[]" type="text" id="kode[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE']; ?>" readonly="true" />
	<input name="kodeId[]" type="hidden" id="kodeId[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE']; ?>" /></td>
    <td width="105"><input name="jpb[]" type="text" id="jpb[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" value="<?php echo $r['CPM_JPB']; ?>" maxlength="5" onkeypress="return iniAngka(event)" /></td>
    <td width="105"><input name="tipe[]" type="text" id="tipe[<?php echo $i; ?>]" onkeyup="kdProses(this.id)"  value="<?php echo $r['CPM_TIPE']; ?>" maxlength="5" onkeypress="return iniAngka(event)" /></td>
    <td width="105"><input name="lantai_min[]" type="text" id="lantai_min[<?php echo $i; ?>]" onkeyup="kdProses(this.id)"  value="<?php echo $r['CPM_LANTAI_MIN']; ?>" maxlength="4" onkeypress="return iniAngka(event)" /></td>
    <td width="290"><input name="lantai_max[]" type="text" id="lantai_max[<?php echo $i; ?>]"  value="<?php echo $r['CPM_LANTAI_MAX']; ?>" maxlength="4" onkeypress="return iniAngka(event)" /></td>
  </tr>
  <?php } ?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><input name="btEdit" type="submit" id="btEdit" value="Simpan" /></td>
  </tr>
</table>
</form>
<?php }?>
</div>
