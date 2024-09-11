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
	function DelAll() {
		var b = confirm("Apakah anda yakin menghapus dengan mode All?");
		if (b == false) {
			return false;
		} else {
			return true;
		}
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
	var y = 0;
	var id = 5;

	function addRows() {
		if (y == 0) {
			v = document.getElementById("kode[]").value;
			v = parseInt(v, 10) + 5;
		} else if (y != 0) {
			v++;
		}
		if (v < 10) {
			v = "0" + v;
		}
		var row = document.getElementById("tableAdd").insertRow(n);
		row.insertCell(0).innerHTML = "<input name='kode[]' class=\"form-control\" type='text' id='kode[]' value='" + v + "' readonly='true'>";
		row.insertCell(1).innerHTML = "<input name='nama[]' class=\"form-control\" type='text' id='nama[]' maxlength='255' />";
		row.insertCell(2).innerHTML = "<input name='enabled[" + id + "]' type='radio' value='1' /> Ya <input name='enabled[" + id + "]' type='radio' value='0' checked='checked' />  Tidak";
		n++;
		y++;
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
</script>
<div class="col-md-12">
	<h3>SISTEM PENILAIAN BANGUNAN <br />
		(TABEL PEKERJAAN) </h3>
	<?php
	if (!empty($_REQUEST['btTambah'])) {
		$jArray = count($_REQUEST['kode']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode = $_REQUEST['kode'][$i];
			$nama = $_REQUEST['nama'][$i];
			$enabled = $_REQUEST['enabled'][$i];
			if (!empty($nama)) {
				$sqlTampil = "INSERT INTO cppmod_pbb_pekerjaan (CPM_KODE, CPM_NAMA, CPM_ENABLED) VALUES ('$kode', '$nama', '$enabled');";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
			}
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btEdit'])) {
		$jArray = count($_REQUEST['kode']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode = $_REQUEST['kode'][$i];
			$nama = $_REQUEST['nama'][$i];
			$enabled = $_REQUEST['enabled'][$i];
			$sqlTampil = "UPDATE cppmod_pbb_pekerjaan SET CPM_NAMA='$nama', CPM_ENABLED='$enabled' WHERE CPM_KODE ='$kode' ;";
			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data diubah!</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btHapus'])) {
		$jArray = count($_REQUEST['kode']);
		if ($jArray == 0) {
			$jArray = 1;
		}
		for ($i = 0; $i < $jArray; $i++) {
			$kode = $_REQUEST['kode'][$i];
			if (empty($kode)) {
				$kode = $_REQUEST['kode2'];
			}
			if (!empty($kode)) {
				$sqlTampil = "DELETE FROM cppmod_pbb_pekerjaan WHERE CPM_KODE ='$kode' ;";
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
				<div class="col-md-12">
					<div class="table-responsive">
						<table class="table table-bordered">
							<tr>
								<th scope="col"><button class="btn btn-primary btn-orange" onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
								<th scope="col">NO</th>
								<th scope="col">KODE</th>
								<th scope="col">NAMA </th>
								<th scope="col">ENABLED</th>
								<th scope="col">PROSES</th>
							</tr>
							<?php
							$sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan order by CPM_KODE asc;";
							$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
							$no = 0;
							while ($r = mysqli_fetch_assoc($result)) {
								$no++;
							?>
								<tr>
									<td><input name="kode[]" type="checkbox" id="kode[<?php echo $n; ?>]" value="<?php echo $r['CPM_KODE']; ?>" /></td>
									<td><?php echo $no; ?></td>
									<td><?php echo $r['CPM_KODE']; ?></td>
									<td><?php echo $r['CPM_NAMA']; ?></td>
									<td><?php echo $r['CPM_ENABLED']; ?></td>
									<td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kode2=$r[CPM_KODE]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kode2=$r[CPM_KODE]"); ?>')">Hapus</a></td>
								</tr>
							<?php } ?>
						</table>
					</div>
				</div>
			</div>
		</form>
	<?php } else if (!empty($_REQUEST['tambahData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<?php
			$sqlTampil = "SELECT max(CPM_KODE) as tKode FROM cppmod_pbb_pekerjaan;";
			$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
			$r = mysqli_fetch_assoc($result);
			$tKode = $r['tKode'];
			?>
			<button class="btn btn-primary bg-maka mb15" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
			<div class="row">
				<div class="col-md-12">
					<label>Tambah Data</label><br />
					<div class="table-responsive">
						<table class="table table-bordered" id="tableAdd">
							<tr>
								<td width="20%">Kode</td>
								<td>Nama</td>
								<td width="20%">Enabled</td>
							</tr>
							<?php for ($i = 0; $i < 5; $i++) {
								$tKode++;
								if ($tKode < 10) {
									$tKode = "0" . $tKode;
								} ?>
								<tr>
									<td><input name="kode[]" class="form-control" type="text" id="kode[]" value="<?php echo $tKode; ?>" readonly="true"></td>
									<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
									<td><input name="enabled[<?php echo $i; ?>]" type="radio" value="1" /> Ya
										<input name="enabled[<?php echo $i; ?>]" type="radio" value="0" checked="checked" /> Tidak
									</td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<div style="float: right;">
						<button class="btn btn-primary bg-maka" name="btTambah" type="submit" id="btTambah" value="Simpan">Simpan</button>
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
								<td width="20%">Kode </td>
								<td width="105">Nama</td>
								<td width="20%">Enabled</td>
							</tr>
							<?php
							$jArray = count($_REQUEST['kode']);
							if ($jArray == 0) {
								$jArray = 1;
							}
							for ($i = 0; $i < $jArray; $i++) {
								$kode = $_REQUEST['kode'][$i];
								if (empty($kode)) {
									$kode = $_REQUEST['kode2'];
								}
								$sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan where CPM_KODE='$kode' ;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								$r = mysqli_fetch_assoc($result);
							?>
								<tr>
									<td width="105"><input name="kode[]" class="form-control" type="text" id="kode[]" value="<?php echo $r['CPM_KODE']; ?>" readonly="true" /></td>
									<td width="105"><input name="nama[]" class="form-control" type="text" id="nama[]" value="<?php echo $r['CPM_NAMA']; ?>" maxlength="255" /></td>
									<td width="290"><input name="enabled[<?php echo $i; ?>]" type="radio" value="1" <?php echo ($r['CPM_ENABLED'] == 1) ? "checked='checked'" : ""; ?> /> Ya
										<input name="enabled[<?php echo $i; ?>]" type="radio" value="0" <?php echo ($r['CPM_ENABLED'] == 0) ? "checked='checked'" : ""; ?> /> Tidak
									</td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<div style="float: right; margin-top: 15px;">
						<button name="btEdit" class="btn btn-primary bg-maka" type="submit" id="btEdit" value="Simpan">Simpan</button>
					</div>
				</div>
			</div>
		</form>
	<?php } ?>
</div>