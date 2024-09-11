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

	function prosesDel(a, a_link) {
		var b = confirm("Anda akan menghapus kode " + a + " ?");
		if (b == false) {
			return false;
		} else {
			window.open(a_link, "_parent");
			return true;
		}
	}
	var n = 7;
	var y = 0;
	var id = 5;

	function addRows() {
		if (y == 0) {
			v = document.getElementById("kode_lokasi[0]").value;
			v = parseInt(v, 10) + 5;
		} else if (y != 0) {
			v++;
		}
		if (v < 10) {
			v = "0" + v;
		}
		var row = document.getElementById("tableAdd").insertRow(n);
		row.insertCell(0).innerHTML = "<input name='kode_lokasi[]' type='text' id='kode_lokasi[" + id + "]' maxlength='4' onkeypress='return iniAngka(event)' />";
		row.insertCell(1).innerHTML = "<input name='tahun[]' type='text' id='tahun[" + id + "]' maxlength='4' onkeypress='return iniAngka(event)' />";
		row.insertCell(2).innerHTML = "<select name='kode_bangunan[]' id='kode_bangunan[" + id + "]' ><option value='0'>Pilih...</option><?php
																																													$sqlTampil = 'SELECT * FROM cppmod_pbb_bangunan order by CPM_KODE asc;';
																																													$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																													while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r[CPM_KODE]; ?>'><?php echo $r[CPM_KODE]; ?></option><?php } ?></select>";
		row.insertCell(3).innerHTML = "<select name='kode_pekerjaan[]' id='kode_pekerjaan[" + id + "]' onchange='showKegiatan(this.value,this.id)' ><option value='0'>Pilih...</option><?php
																																																													$sqlTampil = 'SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;';
																																																													$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																																													while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r[CPM_KODE]; ?>'><?php echo $r[CPM_NAMA]; ?></option><?php } ?></select>";
		row.insertCell(4).innerHTML = "<span id='showKegiatan[" + id + "]'></span>";
		row.insertCell(5).innerHTML = "<input name='harga[]' type='text' id='harga[" + id + "]' onkeyup='kdProses(this.id)' maxlength='9' onkeypress='return iniAngka(event)'/>";


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

	function showKegiatan(v, id) {
		var le = id.length;
		var iId = id.substring(15, le - 1);
		<?php
		$sqlTampil = 'SELECT CPM_KODE_PEKERJAAN FROM `cppmod_pbb_kegiatan` group by CPM_KODE_PEKERJAAN;';
		$dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) { ?>if(document.getElementById(id).value == "<?php echo $r[CPM_KODE_PEKERJAAN]; ?>") {
			document.getElementById("showKegiatan[" + iId + "]").innerHTML = "<select name='kode_kegiatan[" + iId + "]' id='kode_kegiatan[" + iId + "]' ><option value='0'>Pilih...</option><?php
																																																														$sqlTampil2 = "SELECT * FROM `cppmod_pbb_kegiatan` where CPM_KODE_PEKERJAAN='$r[CPM_KODE_PEKERJAAN]';";
																																																														$query = mysqli_query($DBLink, $sqlTampil2);
																																																														while ($re = mysqli_fetch_assoc($query)) { ?><option value='<?php echo $re[CPM_KODE]; ?>'><?php echo $re[CPM_NAMA]; ?></option><?php } ?></select>";
		} else <?php } ?> {
		document.getElementById("showKegiatan[" + iId + "]").innerHTML = "<p style='width:350px'>Kegiatan belum terdaftarkan di pekerjaan ini!</p>";
	}

	}

	function kdProses(a) {
		var c = a.length;
		b = a.substring(6, c - 1);
		var k1 = document.getElementById("kode_lokasi[" + b + "]").value;
		var k2 = document.getElementById("tahun[" + b + "]").value;
		var k3 = document.getElementById("kode_bangunan[" + b + "]").value;
		var k4 = document.getElementById("kode_pekerjaan[" + b + "]").value;
		var k5 = document.getElementById("kode_kegiatan[" + b + "]").value;
		if (k1 == 0) {
			alert("Isi kolom kode_lokasi!");
			document.getElementById(a).value = "";
		} else if (k2 == 0) {
			alert("Isi kolom tahun!");
			document.getElementById(a).value = "";
		} else if (k3 == 0) {
			alert("Isi kolom kode_bangunan!");
			document.getElementById(a).value = "";
		} else if (k4 == 0) {
			alert("Isi kolom kode_pekerjaan!");
			document.getElementById(a).value = "";
		} else if (k5 == 0) {
			alert("Isi kolom kode_kegiatan!");
			document.getElementById(a).value = "";
		}
		kt = k1 + k2 + k3 + k4 + k5;
		<?php
		$sqlTampil = "SELECT * FROM cppmod_pbb_bangunan_kegiatan_harga ;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) {
			$kodeT = $r['CPM_KODE_LOKASI'] . $r['CPM_TAHUN'] . $r['CPM_KODE_BANGUNAN'] . $r['CPM_KODE_PEKERJAAN'] . $r['CPM_KODE_KEGIATAN']; ?>
			kodeT = "<?php echo $kodeT; ?>";
			if (kodeT == kt) {
				alert("Urutan kode sudah terdaftar!");
				document.getElementById(a).value = "";
			}
		<?php } ?>

	}
</script>
<div align="left">
	<h3>SISTEM PENILAIAN BANGUNAN <br />
		(TABEL BANGUNAN KEGIATAN HARGA) </h3>
	<br />
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
		for ($i = 0; $i < $jArray; $i++) {
			$kode_lokasi = $_REQUEST['kode_lokasi'][$i];
			$tahun = $_REQUEST['tahun'][$i];
			$kode_bangunan = $_REQUEST['kode_bangunan'][$i];
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$harga = $_REQUEST['harga'][$i];
			if (!empty($kode_lokasi) and !empty($tahun) and !empty($kode_bangunan) and !empty($kode_pekerjaan) and !empty($kode_kegiatan) and !empty($harga)) {
				$sqlTampil = "INSERT INTO cppmod_pbb_bangunan_kegiatan_harga (CPM_KODE_LOKASI, CPM_TAHUN, CPM_KODE_BANGUNAN, CPM_KODE_PEKERJAAN, CPM_KODE_KEGIATAN, CPM_HARGA) VALUES ('$kode_lokasi', '$tahun', '$kode_bangunan', '$kode_pekerjaan', '$kode_kegiatan', '$harga');";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
			}
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btEdit'])) {
		$jArray = count($_REQUEST['kode_lokasi']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode_lokasi = $_REQUEST['kode_lokasi'][$i];
			$tahun = $_REQUEST['tahun'][$i];
			$kode_bangunan = $_REQUEST['kode_bangunan'][$i];
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			$harga = $_REQUEST['harga'][$i];
			$sqlTampil = "UPDATE cppmod_pbb_bangunan_kegiatan_harga SET 
	CPM_HARGA='$harga'
	WHERE 
	CPM_KODE_LOKASI ='$kode_lokasi' and
	CPM_TAHUN ='$tahun' and
	CPM_KODE_BANGUNAN ='$kode_bangunan' and
	CPM_KODE_PEKERJAAN ='$kode_pekerjaan' and
	CPM_KODE_KEGIATAN ='$kode_kegiatan'	;";
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
			$kode_bangunan = $_REQUEST['kode_bangunan'][$i];
			$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
			$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
			if (empty($kode_lokasi) and empty($tahun) and empty($kode_bangunan) and empty($kode_pekerjaan) and empty($kode_kegiatan)) {
				$kode_lokasi = $_REQUEST['kode_lokasi2'];
				$tahun = $_REQUEST['tahun2'];
				$kode_bangunan = $_REQUEST['kode_bangunan2'];
				$kode_pekerjaan = $_REQUEST['kode_pekerjaan2'];
				$kode_kegiatan = $_REQUEST['kode_kegiatan2'];
			}
			//echo $kode_lokasi."|".$tahun."|".$kode_bangunan."|".$kode_pekerjaan."|".$kode_kegiatan;
			if (!empty($kode_lokasi) and !empty($tahun) and !empty($kode_bangunan) and !empty($kode_pekerjaan) and !empty($kode_kegiatan)) {
				$sqlTampil = "DELETE FROM cppmod_pbb_bangunan_kegiatan_harga WHERE 
		CPM_KODE_LOKASI ='$kode_lokasi' and 
		CPM_TAHUN ='$tahun' and 
		CPM_KODE_BANGUNAN ='$kode_bangunan' and 
		CPM_KODE_PEKERJAAN ='$kode_pekerjaan' and 
		CPM_KODE_KEGIATAN ='$kode_kegiatan' ;";
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
			<input name="tambahData" type="submit" id="tambahData" value="Tambah Data" />
			<input name="editData" type="submit" id="editData" value="Ubah" />
			<input name="btHapus" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus" />
			<table width="" border="1" cellspacing="0" cellpadding="3">
				<tr>
					<th scope="col"><input onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck" /></th>
					<th scope="col">NO</th>
					<th scope="col">KODE LOKASI </th>
					<th scope="col">TAHUN</th>
					<th scope="col">KODE BANGUNAN</th>
					<th scope="col">KODE PEKERJAAN</th>
					<th scope="col">KODE KEGIATAN</th>
					<th scope="col">HARGA</th>
					<th scope="col">PROSES</th>
				</tr>
				<?php
				$sqlTampil = "SELECT * FROM cppmod_pbb_bangunan_kegiatan_harga order by CPM_KODE_LOKASI, CPM_TAHUN, CPM_KODE_BANGUNAN, CPM_KODE_PEKERJAAN, CPM_KODE_KEGIATAN asc;";
				$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
				$i = 0;
				while ($r = mysqli_fetch_assoc($result)) {
					$no++;
				?>
					<tr>
						<td>
							<input name="kode_lokasi[<?php echo $i; ?>]" type="checkbox" id="kode_lokasi[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_LOKASI']; ?>" />
							<input name="tahun[<?php echo $i; ?>]" type="hidden" id="tahun[<?php echo $i; ?>]" value="<?php echo $r['CPM_TAHUN']; ?>" />
							<input name="kode_bangunan[<?php echo $i; ?>]" type="hidden" id="kode_bangunan[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_BANGUNAN']; ?>" />
							<input name="kode_pekerjaan[<?php echo $i; ?>]" type="hidden" id="kode_pekerjaan[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_PEKERJAAN']; ?>" />
							<input name="kode_kegiatan[<?php echo $i; ?>]" type="hidden" id="kode_kegiatan[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_KEGIATAN']; ?>" />
						</td>
						<td><?php echo $no; ?></td>
						<td><?php echo $r['CPM_KODE_LOKASI']; ?></td>
						<td><?php echo $r['CPM_TAHUN']; ?></td>
						<td><?php echo $r['CPM_KODE_BANGUNAN']; ?></td>
						<td><?php echo nmKodePekerjaan($r['CPM_KODE_PEKERJAAN']); ?></td>
						<td><?php echo nmKodeKegiatan($r['CPM_KODE_PEKERJAAN'], $r['CPM_KODE_KEGIATAN']); ?></td>
						<td><?php echo $r['CPM_HARGA']; ?></td>
						<td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kode_lokasi2=$r[CPM_KODE_LOKASI]&tahun2=$r[CPM_TAHUN]&kode_bangunan2=$r[CPM_KODE_BANGUNAN]&kode_pekerjaan2=$r[CPM_KODE_PEKERJAAN]&kode_kegiatan2=$r[CPM_KODE_KEGIATAN]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kode_lokasi2=$r[CPM_KODE_LOKASI]&tahun2=$r[CPM_TAHUN]&kode_bangunan2=$r[CPM_KODE_BANGUNAN]&kode_pekerjaan2=$r[CPM_KODE_PEKERJAAN]&kode_kegiatan2=$r[CPM_KODE_KEGIATAN]"); ?>')">Hapus</a></td>
					</tr>
				<?php $i++;
				} ?>
			</table>
		</form>
	<?php } else if (!empty($_REQUEST['tambahData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<input name="" type="button" value="Tambah Baris" onclick="addRowsMulti()" />
			<table width="900" border="0" cellpadding="3" cellspacing="0" id="tableAdd">
				<tr>
					<th colspan="6">Tambah Data </th>
				</tr>
				<tr>
					<td width="144">Kode Lokasi </td>
					<td width="144">Tahun</td>
					<td width="144">Kode Bangunan </td>
					<td width="144">Kode Pekerjaan </td>
					<td width="144">Kode Kegiatan </td>
					<td width="144">Harga</td>
				</tr>
				<?php for ($i = 0; $i < 5; $i++) { ?>
					<tr>
						<td><input name="kode_lokasi[]" type="text" id="kode_lokasi[<?php echo $i; ?>]" maxlength="4" onkeypress="return iniAngka(event)"></td>
						<td><input name="tahun[]" type="text" id="tahun[<?php echo $i; ?>]" maxlength="4" onkeypress="return iniAngka(event)" /></td>
						<td>
							<select name="kode_bangunan[]" id="kode_bangunan[<?php echo $i; ?>]">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_bangunan order by CPM_KODE asc;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_KODE']; ?></option>
								<?php } ?>
							</select></td>
						<td>
							<select name="kode_pekerjaan[]" id="kode_pekerjaan[<?php echo $i; ?>]" onchange="showKegiatan(this.value,this.id)">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan order by CPM_NAMA asc;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
								<?php } ?>
							</select></td>
						<td><span id="showKegiatan[<?php echo $i; ?>]"></span></td>
						<td><input name="harga[]" type="text" id="harga[<?php echo $i; ?>]" onkeyup="kdProses(this.id)" maxlength="9" onkeypress="return iniAngka(event)" /></td>
					</tr>
				<?php } ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>
						<div align="right">
							<input name="btTambah" type="submit" id="btTambah" value="Simpan" />
						</div>
					</td>
				</tr>
			</table>
		</form>
	<?php } else if (!empty($_REQUEST['editData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<table width="416" border="0" cellpadding="3" cellspacing="0">
				<tr>
					<th colspan="6">Ubah Data </th>
				</tr>
				<tr>
					<td>Kode Lokasi </td>
					<td>Tahun</td>
					<td>Kode Bangunan </td>
					<td>Kode Pekerjaan </td>
					<td>Kode Kegiatan </td>
					<td>Harga</td>
				</tr>
				<?php
				$jArray = count($_REQUEST['tahun']);
				if ($jArray == 0) {
					$jArray = 1;
				}
				for ($i = 0; $i < $jArray; $i++) {
					$kode_lokasi = $_REQUEST['kode_lokasi'][$i];
					$tahun = $_REQUEST['tahun'][$i];
					$kode_bangunan = $_REQUEST['kode_bangunan'][$i];
					$kode_pekerjaan = $_REQUEST['kode_pekerjaan'][$i];
					$kode_kegiatan = $_REQUEST['kode_kegiatan'][$i];
					if (empty($kode_lokasi) and !empty($tahun) and !empty($kode_bangunan) and !empty($kode_pekerjaan) and !empty($kode_kegiatan)) {
						continue;
					}
					if (empty($kode_lokasi) and empty($tahun) and empty($kode_bangunan) and empty($kode_pekerjaan) and empty($kode_kegiatan)) {
						$kode_lokasi = $_REQUEST['kode_lokasi2'];
						$tahun = $_REQUEST['tahun2'];
						$kode_bangunan = $_REQUEST['kode_bangunan2'];
						$kode_pekerjaan = $_REQUEST['kode_pekerjaan2'];
						$kode_kegiatan = $_REQUEST['kode_kegiatan2'];
					}
					$sqlTampil = "SELECT * FROM cppmod_pbb_bangunan_kegiatan_harga where 
	CPM_KODE_LOKASI='$kode_lokasi' and CPM_TAHUN='$tahun' and CPM_KODE_BANGUNAN='$kode_bangunan' and CPM_KODE_PEKERJAAN='$kode_pekerjaan' and CPM_KODE_KEGIATAN='$kode_kegiatan' ;";
					$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
					$r = mysqli_fetch_assoc($result);
				?>
					<tr>
						<td width="105"><input name="kode_lokasi[]" type="text" id="kode_lokasi[]" value="<?php echo $r['CPM_KODE_LOKASI']; ?>" maxlength="4" readonly="true" onkeypress="return iniAngka(event)" /></td>
						<td width="105"><input name="tahun[]" type="text" id="tahun[]" value="<?php echo $r['CPM_TAHUN']; ?>" maxlength="4" readonly="true" onkeypress="return iniAngka(event)" /></td>
						<td width="105"><input name="kode_bangunan[]" type="text" id="kode_bangunan[]" value="<?php echo $r['CPM_KODE_BANGUNAN']; ?>" readonly="true" /></td>
						<td width="105"><?php $sqlTampil = "SELECT * FROM cppmod_pbb_pekerjaan where CPM_KODE='$r[CPM_KODE_PEKERJAAN]';";
												$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
												$re = mysqli_fetch_assoc($result); ?>
							<input name="nama_pekerjaan[]" type="text" id="nama_pekerjaan[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
							<input name="kode_pekerjaan[]" type="hidden" id="kode_pekerjaan[]" value="<?php echo $re['CPM_KODE']; ?>" /></td>
						<td width="105"><?php $sqlTampil = "SELECT * FROM cppmod_pbb_kegiatan where CPM_KODE='$r[CPM_KODE_KEGIATAN]';";
												$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
												$re = mysqli_fetch_assoc($result); ?>
							<input name="nama_kegiatan[]" type="text" id="nama_kegiatan[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
							<input name="kode_kegiatan[]" type="hidden" id="kode_kegiatan[]" value="<?php echo $re['CPM_KODE']; ?>" /></td>
						<td width="290"><input name="harga[]" type="text" id="harga[]" value="<?php echo $r['CPM_HARGA']; ?>" maxlength="9" onkeypress="return iniAngka(event)" /></td>
					</tr>
				<?php } ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><input name="btEdit" type="submit" id="btEdit" value="Simpan" /></td>
				</tr>
			</table>
		</form>
	<?php } ?>
</div>