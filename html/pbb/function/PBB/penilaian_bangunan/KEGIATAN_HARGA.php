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
		if (charCode >= 48 && charCode <= 57 || charCode == 8)
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
		var c = a.length;
		b = a.substring(6, c - 1);
		var k1 = document.getElementById("kode_lokasi[" + b + "]").value;
		var k2 = document.getElementById("tahun[" + b + "]").value;
		var k3 = document.getElementById("kode_pekerjaan[" + b + "]").value;
		var k4 = document.getElementById("kode_kegiatan[" + b + "]").value;
		if (k1 == 0 || k1 == null) {
			alert("Isi kolom kode_lokasi!");
			document.getElementById(a).value = "";
		} else if (k2 == 0) {
			alert("Isi kolom tahun!");
			document.getElementById(a).value = "";
		} else if (k3 == 0) {
			alert("Isi kolom kode_pekerjaan!");
			document.getElementById(a).value = "";
		} else if (k4 == 0) {
			alert("Isi kolom kode_kegiatan!");
			document.getElementById(a).value = "";
		}
		var kt = (k1 + k2 + k3 + k4);
		<?php
		$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan_harga ;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) {
			$kodeT = $r['CPM_KD_PROPINSI'] . $r['CPM_KD_DATI2'] . $r['CPM_TAHUN'] . $r['CPM_KODE_PEKERJAAN'] . $r['CPM_KODE_KEGIATAN']; ?>
			var kodeT = "<?php echo $kodeT; ?>";
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
		row.insertCell(0).innerHTML = "<input class=\"form-control\" name='kode_lokasi[]' type='text' id='kode_lokasi[" + id + "]' maxlength='4' onkeypress='return iniAngka(event)' />";
		row.insertCell(1).innerHTML = "<input class=\"form-control\" name='tahun[]' type='text' id='tahun[" + id + "]' maxlength='4' onkeypress='return iniAngka(event)' >";
		row.insertCell(2).innerHTML = "<select class=\"form-control\" name='kode_pekerjaan[]' id='kode_pekerjaan[" + id + "]' onchange='showSub(this.value,this.id)' ><option value='0'>Pilih...</option><?php
																																																																			$sqlTampil = 'SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;';
																																																																			$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																																																			while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r['CPM_KODE']; ?>'><?php echo $r['CPM_NAMA']; ?></option><?php } ?></select>";
		row.insertCell(3).innerHTML = "<span id='showSub[" + id + "]'></span>";
		row.insertCell(4).innerHTML = "<input class=\"form-control\" name='harga[]' type='text' id='harga[" + id + "]' onkeyup='kdProses(this.id)' maxlength='9' onkeypress='return iniAngka(event)'/>";

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


		/*	<select name="kode_kegiatan[]" id="kode_kegiatan[<?php echo $i; ?>]" >
				<option value="0">Pilih...</option>
				<?php
				$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan order by CPM_NAMA asc;";
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
		(TABEL PBB KEGIATAN HARGA) </h3>
	<?php
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

		$jArray = count($_REQUEST['kode_lokasi']);
		$bOK = 0;
		for ($i = 0; $i < $jArray; $i++) {
			$kode_lokasi = str_pad($_REQUEST['kode_lokasi'][$i], 4, 0, STR_PAD_RIGHT);
			$provinsi = substr($kode_lokasi, 0, 2);
			$dati2 = substr($kode_lokasi, 2, 2);

			$tahun = $_REQUEST['tahun'][$i];
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$harga = $_REQUEST['harga'][$i];
			if (!empty($kode_lokasi) and !empty($tahun) and !empty($kode_pekerjaan) and !empty($kode_kegiatan) and !empty($harga)) {
				$sqlTampil = "INSERT INTO cppmod_pbb_kegiatan_harga (CPM_KD_PROPINSI, CPM_KD_DATI2, CPM_TAHUN, CPM_KODE_PEKERJAAN, CPM_KODE_KEGIATAN, CPM_HARGA) VALUES ('$provinsi','$dati2', '$tahun', '$kode_pekerjaan', '$kode_kegiatan', '$harga');";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result) ? 1 : 0;
			}
		}
		if ($bOK) {
			echo "<b>" . ($bOK) . " data ditambahkan !</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btEdit'])) {
		$jArray = count($_REQUEST['kode_lokasi']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode_lokasi = str_pad($_REQUEST['kode_lokasi'][$i], 4, 0, STR_PAD_RIGHT);
			$provinsi = substr($kode_lokasi, 0, 2);
			$dati2 = substr($kode_lokasi, 2, 2);

			$tahun = $_REQUEST['tahun'][$i];
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$harga = $_REQUEST['harga'][$i];
			$sqlTampil = "UPDATE cppmod_pbb_kegiatan_harga SET CPM_HARGA='$harga' WHERE 
		
		CPM_KD_PROPINSI = '$provinsi' and
		CPM_KD_DATI2 = '$dati2' and
		CPM_TAHUN ='$tahun' and  
		CPM_KODE_PEKERJAAN ='$kode_pekerjaan' and  
		CPM_KODE_KEGIATAN ='$kode_kegiatan'  
		;";
			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data diubah!</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btHapus'])) {

		$jArray = count($_REQUEST['tahun']);
		if ($jArray == 0) {
			$jArray = 1;
		}
		for ($i = 0; $i < $jArray; $i++) {
			$kode_lokasi = $_REQUEST['kode_lokasi'][$i];
			$tahun = $_REQUEST['tahun'][$i];
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];

			if (empty($kode_lokasi) and empty($tahun) and empty($kode_pekerjaan) and empty($kode_kegiatan)) {
				$kode_lokasi = $_REQUEST['kode_lokasi2'];
				$tahun = $_REQUEST['tahun2'];
				$kode_pekerjaan = $_REQUEST['kode_pekerjaan2'];
				$kode_kegiatan = $_REQUEST['kode_kegiatan2'];
			}

			if (!empty($kode_lokasi) and !empty($tahun) and !empty($kode_pekerjaan) and !empty($kode_kegiatan)) {

				$kode_lokasi = str_pad($kode_lokasi, 4, 0, STR_PAD_RIGHT);
				$provinsi = substr($kode_lokasi, 0, 2);
				$dati2 = substr($kode_lokasi, 2, 2);

				$sqlTampil = "DELETE FROM cppmod_pbb_kegiatan_harga WHERE 
			
			CPM_KD_PROPINSI = '$provinsi' and
			CPM_KD_DATI2 = '$dati2' and
			
			CPM_TAHUN ='$tahun' AND  CPM_KODE_PEKERJAAN ='$kode_pekerjaan' AND CPM_KODE_KEGIATAN ='$kode_kegiatan' ;";
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
	if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) { ?>
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
								<th scope="col">NO KEGIATAN HARGA</th>
								<th scope="col">KODE LOKASI </th>
								<th scope="col">TAHUN</th>
								<th scope="col"> PEKERJAAN </th>
								<th scope="col"> KEGIATAN </th>
								<th scope="col">HARGA</th>
								<th scope="col">PROSES</th>
							</tr>
							<?php
							$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan_harga order by CPM_KD_PROPINSI, CPM_KD_DATI2, CPM_TAHUN, CPM_KODE_PEKERJAAN, CPM_KODE_KEGIATAN asc LIMIT 10;";
							// echo $sqlTampil;
							$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
							$i = 0;
							$no = 0;
							while ($r = mysqli_fetch_assoc($result)) {
								$no++;
							?>
								<tr>
									<td><input name="kode_lokasi[<?php echo $i; ?>]" type="checkbox" id="kode_lokasi[]" value="<?php echo $r['CPM_KD_PROPINSI'] . $r['CPM_KD_DATI2']; ?>" />
										<input name="tahun[<?php echo $i; ?>]" type="hidden" id="tahun[]" value="<?php echo $r['CPM_TAHUN']; ?>" />
										<input name="kode_pekerjaan[<?php echo $i; ?>]" type="hidden" id="kode_pekerjaan[]" value="<?php echo $r['CPM_KODE_PEKERJAAN']; ?>" />
										<input name="kode_kegiatan[<?php echo $i; ?>]" type="hidden" id="kode_kegiatan[]" value="<?php echo $r['CPM_KODE_KEGIATAN']; ?>" />
									</td>
									<td><?php echo $no; ?></td>
									<td><?php echo $r['CPM_KD_PROPINSI'] . $r['CPM_KD_DATI2']; ?></td>
									<td><?php echo $r['CPM_TAHUN']; ?></td>
									<td><?php echo nmKodePekerjaan($r['CPM_KODE_PEKERJAAN']); ?></td>
									<td><?php echo nmKodeKegiatan($r['CPM_KODE_PEKERJAAN'], $r['CPM_KODE_KEGIATAN']); ?></td>
									<td><?php echo $r['CPM_HARGA']; ?></td>
									<td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kode_lokasi2={$r['CPM_KD_PROPINSI']}{$r['CPM_KD_DATI2']}&tahun2=$r[CPM_TAHUN]&kode_pekerjaan2=$r[CPM_KODE_PEKERJAAN]&kode_kegiatan2=$r[CPM_KODE_KEGIATAN]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kode_lokasi2={$r['CPM_KD_PROPINSI']}{$r['CPM_KD_DATI2']}&tahun2=$r[CPM_TAHUN]&kode_pekerjaan2=$r[CPM_KODE_PEKERJAAN]&kode_kegiatan2=$r[CPM_KODE_KEGIATAN]"); ?>')">Hapus</a></td>
								</tr>
							<?php $i++;
							} ?>
						</table>
					</div>
				</div>
			</div>
		</form>
	<?php } else if (!empty($_REQUEST['tambahData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<button class="btn btn-primary bg-maka" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
			<div class="row" style="margin-top: 15px;">
				<div class="col-md-12">
					<label>Tambah Data</label>
					<div class="table-responsive">
						<table class="table table-bordered" id="tableAdd">
							<tr>
								<td width="85">KODE LOKASI </td>
								<td width="144">TAHUN</td>
								<td width="144"> PEKERJAAN </td>
								<td width="165"> KEGIATAN </td>
								<td width="64">HARGA</td>
							</tr>
							<?php for ($i = 0; $i < 5; $i++) { ?>
								<tr>
									<td><input class="form-control" name="kode_lokasi[]" type="text" id="kode_lokasi[<?php echo $i; ?>]" maxlength="4" onkeypress="return iniAngka(event)" /></td>
									<td><input class="form-control" name="tahun[]" type="text" id="tahun[<?php echo $i; ?>]" maxlength="4" onkeypress="return iniAngka(event)"></td>
									<td>
										<select class="form-control" name="kode_pekerjaan[]" id="kode_pekerjaan[<?php echo $i; ?>]" onchange="showSub(this.value,this.id)">
											<option value="0">Pilih...</option>
											<?php
											$sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;";
											$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
											while ($r = mysqli_fetch_assoc($result)) { ?>
												<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
											<?php } ?>
										</select>
									</td>
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
									<td><input class="form-control" name="harga[]" type="text" id="harga[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" maxlength="9" onkeypress="return iniAngka(event)" /></td>
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
	<?php } else if (!empty($_REQUEST['editData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<div class="row">
				<div class="col-md-12">
					<label>Ubah Data</label><br />
					<div class="table-responsive">
						<table class="table table-bordered">
							<tr>
								<td>KODE LOKASI </td>
								<td>TAHUN</td>
								<td> PEKERJAAN </td>
								<td> KEGIATAN </td>
								<td>HARGA</td>
							</tr>
							<?php
							$jArray = count($_REQUEST['tahun']);

							if ($jArray == 0) {
								$jArray = 1;
							}
							for ($i = 0; $i < $jArray; $i++) {
								$kode_lokasi = isset($_REQUEST['kode_lokasi'][$i]) ? $_REQUEST['kode_lokasi'][$i] : '';
								$tahun = $_REQUEST['tahun'][$i];
								$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
								$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
								if (empty($kode) and empty($kode_pekerjaan)) {
									$kode_lokasi = $_REQUEST['kode_lokasi2'];
									$tahun = $_REQUEST['tahun2'];
									$kode_pekerjaan = $_REQUEST['kode_pekerjaan2'];
									$kode_kegiatan = $_REQUEST['kode_kegiatan2'];
								}
								if (empty($kode_lokasi) and !empty($tahun)) {
									continue;
								}

								$kode_lokasi = str_pad($kode_lokasi, 4, 0, STR_PAD_RIGHT);
								$provinsi = substr($kode_lokasi, 0, 2);
								$dati2 = substr($kode_lokasi, 2, 2);

								$sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan_harga where 
	
				CPM_KD_PROPINSI = '$provinsi' and
				CPM_KD_DATI2 = '$dati2' and
						
				CPM_TAHUN='$tahun' and  
				CPM_KODE_PEKERJAAN='$kode_pekerjaan' and  
				CPM_KODE_KEGIATAN='$kode_kegiatan'  
				;";



								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								$r = mysqli_fetch_assoc($result);
							?>
								<tr>
									<td width="158"><input class="form-control" name="kode_lokasi[]" type="text" id="kode_lokasi[]" value="<?php echo $r['CPM_KD_PROPINSI'] . $r['CPM_KD_DATI2']; ?>" maxlength="4" readonly="true" onkeypress="return iniAngka(event)" /></td>
									<td width="144"><input class="form-control" name="tahun[]" type="text" id="tahun[]" value="<?php echo $r['CPM_TAHUN']; ?>" maxlength="4" readonly="true" onkeypress="return iniAngka(event)" /></td>
									<td width="144">
										<input class="form-control" name="kode_pekerjaanview[]" type="text" id="kode_pekerjaanview[]" value="<?php echo nmKodePekerjaan($r['CPM_KODE_PEKERJAAN']); ?>" disabled="disabled" />
										<input name="kode_pekerjaan[]" type="hidden" id="kode_pekerjaan[]" value="<?php echo $r['CPM_KODE_PEKERJAAN']; ?>" />
									</td>
									<td width="26">
										<input class="form-control" name="kode_kegiatanView[]" type="text" id="kode_kegiatanView[]" value="<?php echo nmKodeKegiatan($r['CPM_KODE_PEKERJAAN'], $r['CPM_KODE_KEGIATAN']); ?>" disabled="disabled" />
										<input name="kode_kegiatan[]" type="hidden" id="kode_kegiatan[]" value="<?php echo $r['CPM_KODE_KEGIATAN']; ?>" />
									</td>
									<td width="140"><input class="form-control" name="harga[]" type="text" id="harga[]" value="<?php echo $r['CPM_HARGA']; ?>" maxlength="9" onkeypress="return iniAngka(event)" /></td>
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