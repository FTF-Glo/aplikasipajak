<?php
if (!isset($data)) {
	die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig['terminalColumn'])) {
	$terminalColumn = $arAreaConfig['terminalColumn'];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if (!$accessible) {
		echo "Illegal access";
		return;
	}
}
/*$f=$func[2][id]; <a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f");?>">This</a>*/
?>
<!-------------------------------code------------->

<script type="text/javascript">
	function iniAngka(evt) {
		var charCode = (evt.which) ? evt.which : event.keyCode
		if ((charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8)
			return true;

		return false;
	}

	function DelAll() {
		var b = confirm("Apakah anda yakin menghapus dengan mode All?");
		if (b == false) {
			return false;
		} else {
			return true;
		}
	}

	function kdProses(a) {
		console.log(a);
		var c = a.length;
		var b = a.substring(7, c - 1);
		var k1 = document.getElementById("kode_pekerjaan[" + b + "]").value;
		var k2 = document.getElementById("kode_kegiatan[" + b + "]").value;
		var k3 = document.getElementById("kode_resource_group[" + b + "]").value;
		var k4 = document.getElementById("kode_resource_item[" + b + "]").value;
		if (k1 == 0) {
			alert("Isi kolom kode_pekerjaan!");
			document.getElementById(a).value = "";
		} else if (k2 == 0) {
			alert("Isi kolom kode_kegiatan!");
			document.getElementById(a).value = "";
		} else if (k3 == 0) {
			alert("Isi kolom kode_resource_group!");
			document.getElementById(a).value = "";
		} else if (k4 == 0) {
			alert("Isi kolom kode_resource_item!");
			document.getElementById(a).value = "";
		}
		kt = k1 + k2 + k3 + k4;
		<?php
		$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan_resource_volume ;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) {
			$kodeT = $r['CPM_KODE_PEKERJAAN'] . $r['CPM_KODE_KEGIATAN'] . $r['CPM_KODE_RESOURCE_GROUP'] . $r['CPM_KODE_RESOURCE_ITEM']; ?>
			kodeT = "<?php echo $kodeT; ?>";
			if (kodeT == kt) {
				alert("Urutan kode sudah terdaftar!");
				document.getElementById(a).value = "";
			}
		<?php } ?>

	}

	function prosesDel(a, a_link) {
		var b = confirm("Anda akan menghapus kode " + a + " ?");
		if (b == false) {
			return false;
		} else {
			window.open(a_link, "_parent");
			return true;
		}
	}
	var n = 6;
	var id = 5;

	function addRows() {
		var row = document.getElementById("tableAdd").insertRow(n);
		row.insertCell(0).innerHTML = "<select name='kode_pekerjaan[]' class=\"form-control\" id='kode_pekerjaan[" + id + "]' onchange='showSub(this.value,this.id)' ><option value='0'>Pilih...</option><?php
																																																																			$sqlTampil = 'SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;';
																																																																			$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																																																			while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r['CPM_KODE']; ?>'><?php echo $r['CPM_NAMA']; ?></option><?php } ?></select>";
		row.insertCell(1).innerHTML = "<span id='showSub[" + id + "]'></span>";
		row.insertCell(2).innerHTML = "<select class=\"form-control\" name='kode_resource_group[]' id='kode_resource_group[" + id + "]' onchange='showSub2(this.value,this.id)'><option value='0'>Pilih...</option><?php
																																																																						$sqlTampil = 'SELECT * FROM cppmod_pbb_resource_group order by CPM_NAMA asc;';
																																																																						$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																																																						while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r['CPM_KODE']; ?>'><?php echo $r['CPM_NAMA']; ?></option><?php } ?></select>";
		row.insertCell(3).innerHTML = "<span id='showSub2[" + id + "]'></span>";
		row.insertCell(4).innerHTML = "<input class=\"form-control\" name='volume[]' type='text' id='volume[" + id + "]' onkeyup='kdProses(this.id)' maxlength='6' onkeypress='return iniAngka(event)' />";

		n++;
		id++;
	}

	function addRowsMulti() {
		for (i = 0; i < 5; i++) {
			addRows();
		}
	}

	function Check() {
		allCheckList = document.getElementById("form1").elements;
		jumlahCheckList = allCheckList.length;
		if (document.getElementById("tombolCheck").value == "Pilih Semua") {
			for (i = 0; i < jumlahCheckList; i++) {
				allCheckList[i].checked = true;
			}
			document.getElementById("tombolCheck").value = "Batal Pilih Semua";
		} else {
			for (i = 0; i < jumlahCheckList; i++) {
				allCheckList[i].checked = false;
			}
			document.getElementById("tombolCheck").value = "Pilih Semua";
		}
	}

	function showSub(v, id) {
		var le = id.length;
		var iId = id.substring(15, le - 1);
		var k_res = document.getElementById("kode_kegiatan[" + iId + "]");
		if (k_res != "" && k_res != null) {
			k_res.name = "none[" + iId + "]";
			k_res.id = "none[" + iId + "]";
			k_res.style.display = "none";
		}
		<?php
		$sqlTampil = 'SELECT CPM_KODE_PEKERJAAN FROM `cppmod_pbb_kegiatan` group by CPM_KODE_PEKERJAAN;';
		$dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) { ?>if(document.getElementById(id).value == "<?php echo $r['CPM_KODE_PEKERJAAN']; ?>") {
			document.getElementById("showSub[" + iId + "]").innerHTML = "<select name='kode_kegiatan[" + iId + "]' id='kode_kegiatan[" + iId + "]' ><option value='0'>Pilih...</option><?php
																																																												$sqlTampil2 = "SELECT * FROM `cppmod_pbb_kegiatan` where CPM_KODE_PEKERJAAN='$r[CPM_KODE_PEKERJAAN]' order by CPM_NAMA asc;";
																																																												$query = mysqli_query($DBLink, $sqlTampil2);
																																																												while ($re = mysqli_fetch_assoc($query)) { ?><option value='<?php echo $re['CPM_KODE']; ?>'><?php echo $re['CPM_NAMA']; ?></option><?php } ?></select>";
		} else <?php } ?> {
		document.getElementById("showSub[" + iId + "]").innerHTML = "<p style='width:350px'>Kegiatan belum terdaftarkan di pekerjaan ini!</p>";
	}


	}

	function showSub2(v, id) {
		var le = id.length;
		var iId = id.substring(20, le - 1);
		var k_res = document.getElementById("kode_resource_item[" + iId + "]");
		if (k_res != "" && k_res != null) {
			k_res.name = "none[" + iId + "]";
			k_res.id = "none[" + iId + "]";
			k_res.style.display = "none";
		}
		<?php
		$sqlTampil = 'SELECT CPM_KODE_GROUP FROM `cppmod_pbb_resource_item` group by CPM_KODE_GROUP;';
		$dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) { ?>if(document.getElementById(id).value == "<?php echo $r['CPM_KODE_GROUP']; ?>") {
			document.getElementById("showSub2[" + iId + "]").innerHTML = "<select name='kode_resource_item[" + iId + "]' id='kode_resource_item[" + iId + "]' ><option value='0'>Pilih...</option><?php
																																																																$sqlTampil2 = "SELECT * FROM `cppmod_pbb_resource_item` where CPM_KODE_GROUP='$r[CPM_KODE_GROUP]' order by CPM_NAMA asc;";
																																																																$query = mysqli_query($DBLink, $sqlTampil2);
																																																																while ($re = mysqli_fetch_assoc($query)) { ?><option value='<?php echo $re['CPM_KODE']; ?>'><?php echo $re['CPM_NAMA']; ?></option><?php } ?></select>";
		} else <?php } ?> {
		document.getElementById("showSub2[" + iId + "]").innerHTML = "<p style='width:350px'>Resource item belum terdaftarkan di Resource group ini!</p>";
	}




		/*	<select name="kode_resource_item[]" id="kode_resource_item[<?php echo $i; ?>]" >
			  <option value="0">Pilih...</option>
		<?php
		$sqlTampil = "SELECT * FROM cppmod_pbb_resource_item order by CPM_NAMA asc;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) { ?>
			  <option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
		<?php } ?>
			</select>
		*/
	}
</script>
<div class="col-md-12">
	<h3>SISTEM PENILAIAN BANGUNAN <br />
		(TABEL KEGIATAN RESOURCE VOLUME) </h3>
	<br />
	<?php
	function nmKodeGroup($kode_group)
	{
		global $dbSpec;
		$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group where CPM_KODE='$kode_group';";
		$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
		$re = mysqli_fetch_assoc($result);
		return $re['CPM_NAMA'];
	}
	function nmKodeItem($kode_group, $kode_item)
	{
		global $dbSpec;
		$sqlTampil = "SELECT * FROM cppmod_pbb_resource_item where CPM_KODE_GROUP='$kode_group' and CPM_KODE='$kode_item';";
		$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
		$re = mysqli_fetch_assoc($result);
		return $re['CPM_NAMA'];
	}
	function nmKodePekerjaan($kode_pekerjaan)
	{
		global $dbSpec;
		$sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan where CPM_KODE='$kode_pekerjaan';";
		$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
		$re = mysqli_fetch_assoc($result);
		return $re['CPM_NAMA'];
	}
	function nmKodeKegiatan($kode_pekerjaan, $kode_kegiatan)
	{
		global $dbSpec;
		$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan where CPM_KODE_PEKERJAAN='$kode_pekerjaan' and CPM_KODE='$kode_kegiatan';";
		$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
		$re = mysqli_fetch_assoc($result);
		return $re['CPM_NAMA'];
	}
	if (!empty($_REQUEST['btTambah'])) {
		$jArray = count($_REQUEST['volume']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$kode_resource_group = $_REQUEST['kode_resource_group'][$i];
			$kode_resource_item = $_REQUEST['kode_resource_item'][$i];
			$volume = $_REQUEST['volume'][$i];
			if (!empty($kode_pekerjaan) and !empty($kode_kegiatan) and !empty($kode_resource_group) and !empty($kode_resource_item) and !empty($volume)) {
				$sqlTampil = "INSERT INTO cppmod_pbb_kegiatan_resource_volume (CPM_KODE_PEKERJAAN, CPM_KODE_KEGIATAN, CPM_KODE_RESOURCE_GROUP, CPM_KODE_RESOURCE_ITEM, CPM_VOLUME) VALUES ('$kode_pekerjaan', '$kode_kegiatan', '$kode_resource_group', '$kode_resource_item', '$volume');";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
			}
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btEdit'])) {

		$jArray = count($_REQUEST['kode_pekerjaan']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$kode_resource_group = $_REQUEST['kode_resource_group'][$i];
			$kode_resource_item = $_REQUEST['kode_resource_item'][$i];
			$volume = $_REQUEST['volume'][$i];
			$sqlTampil = "UPDATE cppmod_pbb_kegiatan_resource_volume SET 
			CPM_VOLUME='$volume' 
			WHERE 
			CPM_KODE_PEKERJAAN ='$kode_pekerjaan' and  
			CPM_KODE_KEGIATAN ='$kode_kegiatan' and  
			CPM_KODE_RESOURCE_GROUP ='$kode_resource_group' and  
			CPM_KODE_RESOURCE_ITEM ='$kode_resource_item'   
			;";
			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data diubah!</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btHapus'])) {

		$jArray = count($_REQUEST['kode_kegiatan']);
		if ($jArray == 0) {
			$jArray = 1;
		}
		for ($i = 0; $i < $jArray; $i++) {
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$kode_resource_group = $_REQUEST['kode_resource_group'][$i];
			$kode_resource_item = $_REQUEST['kode_resource_item'][$i];
			if (empty($kode_pekerjaan) and empty($kode_kegiatan) and empty($kode_resource_group) and empty($kode_resource_item)) {
				$kode_pekerjaan = $_REQUEST['kode_pekerjaan2'];
				$kode_kegiatan = $_REQUEST['kode_kegiatan2'];
				$kode_resource_group = $_REQUEST['kode_resource_group2'];
				$kode_resource_item = $_REQUEST['kode_resource_item2'];
			}
			//echo $kode_pekerjaan."|".$kode_kegiatan."|".$kode_resource_group."|".$kode_resource_item."<br>";
			if (!empty($kode_pekerjaan) and !empty($kode_kegiatan) and !empty($kode_resource_group) and !empty($kode_resource_item)) {
				$sqlTampil = "DELETE FROM cppmod_pbb_kegiatan_resource_volume WHERE CPM_KODE_PEKERJAAN ='$kode_pekerjaan' AND 
	CPM_KODE_KEGIATAN ='$kode_kegiatan' AND CPM_KODE_RESOURCE_GROUP ='$kode_resource_group' AND CPM_KODE_RESOURCE_ITEM ='$kode_resource_item' ;";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
			}
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data dihapus!</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	}
	//echo "<br>"; print_r($_REQUEST);
	if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) { // INDEX 
	?>
		<form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-primary btn-orange" name="tambahData" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
					<button class="btn btn-primary btn-blue" name="editData" type="submit" id="editData" value="Ubah">Ubah</button>
					<button class="btn btn-primary bg-maka" name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
				</div>
				<div class="col-md-12" style="margin-top: 15px;">
					<div class="table-responsive">
						<table class="table table-bordered">
							<tr>
								<th scope="col"><button class="btn btn-primary btn-orange" onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
								<th scope="col">NO</th>
								<th scope="col"> PEKERJAAN </th>
								<th scope="col"> KEGIATAN</th>
								<th scope="col"> RESOURCE GROUP </th>
								<th scope="col"> RESOURCE ITEM</th>
								<th scope="col">VOLUME</th>
								<th scope="col">PROSES</th>
							</tr>
							<?php
							$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan_resource_volume order by CPM_KODE_PEKERJAAN, CPM_KODE_KEGIATAN, CPM_KODE_RESOURCE_GROUP, CPM_KODE_RESOURCE_ITEM asc LIMIT 10;";
							$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
							$i = 0;
							$no = 0;
							while ($r = mysqli_fetch_assoc($result)) {
								$no++;
							?>
								<tr>
									<td>
										<input name="kode_pekerjaan[<?php echo $i; ?>]" type="checkbox" id="kode_pekerjaan[]" value="<?php echo $r['CPM_KODE_PEKERJAAN']; ?>" />
										<input name="kode_kegiatan[<?php echo $i; ?>]" type="hidden" id="kode_kegiatan[]" value="<?php echo $r['CPM_KODE_KEGIATAN']; ?>" />
										<input name="kode_resource_group[<?php echo $i; ?>]" type="hidden" id="kode_resource_group[]" value="<?php echo $r['CPM_KODE_RESOURCE_GROUP']; ?>" />
										<input name="kode_resource_item[<?php echo $i; ?>]" type="hidden" id="kode_resource_item[]" value="<?php echo $r['CPM_KODE_RESOURCE_ITEM']; ?>" />
									</td>
									<td><?php echo $no; ?></td>
									<td><?php echo nmKodePekerjaan($r['CPM_KODE_PEKERJAAN']); ?></td>
									<td><?php echo nmKodeKegiatan($r['CPM_KODE_PEKERJAAN'], $r['CPM_KODE_KEGIATAN']); ?></td>
									<td><?php echo nmKodeGroup($r['CPM_KODE_RESOURCE_GROUP']); ?></td>
									<td><?php echo nmKodeItem($r['CPM_KODE_RESOURCE_GROUP'], $r['CPM_KODE_RESOURCE_ITEM']); ?></td>
									<td><?php echo $r['CPM_VOLUME']; ?></td>
									<td><a href="main.php?param=<?php echo base64_encode("a=" . $a . "&m=" . $m . "&f=" . $f . "&editData=1&kode_pekerjaan2=" . $r['CPM_KODE_PEKERJAAN'] . "&kode_kegiatan2=" . $r['CPM_KODE_KEGIATAN'] . "&kode_resource_group2=" . $r['CPM_KODE_RESOURCE_GROUP'] . "&kode_resource_item2=" . $r['CPM_KODE_RESOURCE_ITEM']); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE_PEKERJAAN'] . "-" . $r['CPM_KODE_KEGIATAN'] . "-" . $r['CPM_KODE_RESOURCE_GROUP'] . "-" . $r['CPM_KODE_RESOURCE_ITEM']; ?>','main.php?param=<?php echo base64_encode("a=" . $a . "&m=" . $m . "&f=" . $f . "&btHapus=1&kode_pekerjaan2=" . $r['CPM_KODE_PEKERJAAN'] . "&kode_kegiatan2=" . $r['CPM_KODE_KEGIATAN'] . "&kode_resource_group2=" . $r['CPM_KODE_RESOURCE_GROUP'] . "&kode_resource_item2=" . $r['CPM_KODE_RESOURCE_ITEM']); ?>')">Hapus</a></td>
								</tr>
							<?php $i++;
							} ?>
						</table>
					</div>
				</div>
			</div>
		</form>
	<?php } else if (!empty($_REQUEST['tambahData'])) {  //TAMBAH DATA 
	?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<button class="btn btn-primary bg-maka" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
			<div class="row" style="margin-top: 15px;">
				<div class="col-md-12">
					<label>Tambah Data</label>
					<div class="table-responsive">
						<table class="table table-bordered" id="tableAdd">
							<tr>
								<td width="104">Kode Pekerjaan </td>
								<td width="97">Kode Kegiatan </td>
								<td width="143">Kode Resource Group </td>
								<td width="131">Kode Resource Item </td>
								<td width="144">Volume</td>
							</tr>
							<?php for ($i = 0; $i < 5; $i++) { ?>
								<tr>
									<td><select class="form-control" name="kode_pekerjaan[]" id="kode_pekerjaan[<?php echo $i; ?>]" onchange="showSub(this.value,this.id)">
											<option value="0">Pilih...</option>
											<?php
											$sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;";
											$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
											while ($r = mysqli_fetch_assoc($result)) { ?>
												<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
											<?php } ?>
										</select></td>
									<td><span id="showSub[<?php echo $i; ?>]"></span>
										<!--<select name="kode_kegiatan[]" id="kode_kegiatan[<?php echo $i; ?>]" >
	  <option value="0">Pilih...</option>
<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan order by CPM_NAMA asc;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
	  <option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
<?php } ?>
	</select>-->
									</td>
									<td><select class="form-control" name="kode_resource_group[]" id="kode_resource_group[<?php echo $i; ?>]" onchange="showSub2(this.value,this.id)">
											<option value="0">Pilih...</option>
											<?php
											$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group order by CPM_NAMA asc;";
											$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
											while ($r = mysqli_fetch_assoc($result)) { ?>
												<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
											<?php } ?>
										</select></td>
									<td><span id="showSub2[<?php echo $i; ?>]"></span>
										<!--<select name="kode_resource_item[]" id="kode_resource_item[<?php echo $i; ?>]" >
	  <option value="0">Pilih...</option>
<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_item order by CPM_NAMA asc;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
	  <option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
<?php } ?>
	</select>-->
									</td>
									<td><input class="form-control" name="volume[]" type="text" id="volume[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" maxlength="6" onkeypress="return iniAngka(event)" /></td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<div style="float: right;">
						<button name="btEdit" class="btn btn-primary bg-maka" type="submit" id="btEdit" value="Simpan">Simpan</button>
					</div>
				</div>
			</div>
		</form>
	<?php } else if (!empty($_REQUEST['editData'])) { //EDIT DATA 
	?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<div class="row">
				<div class="col-md-12">
					<label>Ubah Data</label><br />
					<div class="table-responsive">
						<table class="table table-bordered">
							<tr>
								<td>Kode Pekerjaan </td>
								<td>Kode Kegiatan </td>
								<td>Kode Resource Group </td>
								<td>Kode Resource Item </td>
								<td>Volume</td>
							</tr>
							<?php
							$jArray = count($_REQUEST['kode_kegiatan']);
							if ($jArray == 0) {
								$jArray = 1;
							}
							for ($i = 0; $i < $jArray; $i++) {
								$kode_pekerjaan = isset($_REQUEST['kode_pekerjaan'][$i]) ? $_REQUEST['kode_pekerjaan'][$i] : '';
								$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
								$kode_resource_group = $_REQUEST['kode_resource_group'][$i];
								$kode_resource_item = $_REQUEST['kode_resource_item'][$i];
								if (empty($kode_pekerjaan) and empty($kode_kegiatan) and empty($kode_resource_group) and empty($kode_resource_item)) {
									$kode_pekerjaan = $_REQUEST['kode_pekerjaan2'];
									$kode_kegiatan = $_REQUEST['kode_kegiatan2'];
									$kode_resource_group = $_REQUEST['kode_resource_group2'];
									$kode_resource_item = $_REQUEST['kode_resource_item2'];
								}
								if (empty($kode_pekerjaan) and !empty($kode_kegiatan) and !empty($kode_resource_group) and !empty($kode_resource_item)) {
									continue;
								}
								$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan_resource_volume where 
	CPM_KODE_PEKERJAAN ='$kode_pekerjaan' AND 
	CPM_KODE_KEGIATAN ='$kode_kegiatan' AND 
	CPM_KODE_RESOURCE_GROUP ='$kode_resource_group' AND 
	CPM_KODE_RESOURCE_ITEM ='$kode_resource_item' ;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								$r = mysqli_fetch_assoc($result);
							?>
								<tr>
									<td width="144"><?php $sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan where CPM_KODE='$r[CPM_KODE_PEKERJAAN]';";
															$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
															$re = mysqli_fetch_assoc($result); ?>
										<input class="form-control" name="nama_pekerjaan[]" type="text" id="nama_pekerjaan[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
										<input name="kode_pekerjaan[]" type="hidden" id="kode_pekerjaan[]" value="<?php echo $re['CPM_KODE']; ?>" />
									</td>
									<td width="144"><?php $sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan where CPM_KODE_PEKERJAAN='$r[CPM_KODE_PEKERJAAN]' and CPM_KODE='$r[CPM_KODE_KEGIATAN]';";
															$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
															$re = mysqli_fetch_assoc($result); ?>
										<input class="form-control" name="nama_kegiatan[]" type="text" id="nama_kegiatan[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
										<input name="kode_kegiatan[]" type="hidden" id="kode_kegiatan[]" value="<?php echo $re['CPM_KODE']; ?>" />
									</td>
									<td width="144"><?php $sqlTampil = "SELECT * FROM cppmod_pbb_resource_group where CPM_KODE='$r[CPM_KODE_RESOURCE_GROUP]';";
															$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
															$re = mysqli_fetch_assoc($result); ?>
										<input class="form-control" name="nama_resource_group[]" type="text" id="nama_resource_group[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
										<input name="kode_resource_group[]" type="hidden" id="kode_resource_group[]" value="<?php echo $re['CPM_KODE']; ?>" />
									</td>
									<td width="144"><?php $sqlTampil = "SELECT * FROM cppmod_pbb_resource_item where CPM_KODE_GROUP='$r[CPM_KODE_RESOURCE_GROUP]' and CPM_KODE='$r[CPM_KODE_RESOURCE_ITEM]';";
															$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
															$re = mysqli_fetch_assoc($result); ?>
										<input class="form-control" name="nama_resource_item[]" type="text" id="nama_resource_item[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
										<input name="kode_resource_item[]" type="hidden" id="kode_resource_item[]" value="<?php echo $re['CPM_KODE']; ?>" />
									</td>
									<td width="144"><input name="volume[]" class="form-control" type="text" id="volume[]" value="<?php echo $r['CPM_VOLUME']; ?>" maxlength="6" onkeypress="return iniAngka(event)" /></td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<div style="float: right;">
						<button name="btEdit" class="btn btn-primary bg-maka" type="submit" id="btEdit" value="Simpan">Simpan</button>
					</div>
				</div>
			</div>
		</form>
	<?php } ?>
</div>