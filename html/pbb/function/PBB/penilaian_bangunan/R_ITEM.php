<?php
//ini_set("display_errors",1); error_reporting(E_ALL);
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

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User	 	= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$perpage 	= $appConfig['ITEM_PER_PAGE'];
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
?>
<!---------------code------------->
<script type="text/javascript">
	var url = "<?php echo "main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $f); ?>";
	var page = "<?php echo $page; ?>";

	function setPage(np) {
		if (np == 1) {
			if (page == '') {
				page = 2;
			} else {
				page++;
			}
		} else
			page--;

		window.open(url + "&page=" + page, "_parent");
	}

	function DelAll() {
		var b = confirm("Apakah anda yakin menghapus dengan mode All?");
		if (b == false) {
			return false;
		} else {
			return true;
		}
	}
	var x = 0;

	function jumlahElement() {
		n2 = document.getElementById("form2").elements.length;
		n2 = (n2 - 2) / 4;
		return n2;
	}

	function getDataSama(b) {
		var idB = document.getElementById(b);
		var b = idB.value;
		var bId = idB.id;
		var brek = 0;
		for (i = 0; i < jumlahElement(); i++) {
			idA = document.getElementById("kode_group[" + i + "]");
			a = idA.value;
			aId = idA.id;
			if (a == b && aId != bId && brek != 1) {
				x++;
				brek = 1;
			}
		}
		return x;
	}

	function kdProses(a, b) {
		nT = getDataSama(b);
		var c = b.length;
		b = b.substring(11, c - 1);
		<?php
		$sqlTampil = "SELECT CPM_KODE_GROUP, MAX(CPM_KODE)+1 as tCPM_KODE FROM cppmod_pbb_resource_item group by CPM_KODE_GROUP;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) { ?>
			if (a == <?php echo $r['CPM_KODE_GROUP']; ?>) {
				val = <?php echo $r['tCPM_KODE']; ?> + nT;
				if (val < 10) {
					val = "0" + val;
				}
				document.getElementById("kode[" + b + "]").value = val;
			} else <?php } ?> {
			val = 1 + nT;
			if (val < 10) {
				val = "0" + val;
			}
			document.getElementById("kode[" + b + "]").value = val;
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
	var id = 5;

	function addRows() {
		var row = document.getElementById("tableAdd").insertRow(n);
		row.insertCell(0).innerHTML = "<select name='kode_group[]' id='kode_group[" + id + "]' onchange='kdProses(this.value,this.id)'><option value='0'>Pilih...</option><?php
																																																								$sqlTampil = 'SELECT * FROM cppmod_pbb_resource_group;';
																																																								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																																								while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r[CPM_KODE]; ?>'><?php echo $r[CPM_NAMA]; ?></option><?php } ?></select>";
		row.insertCell(1).innerHTML = "<input name='kode[]' type='text' id='kode[" + id + "]' readonly='true' />";
		row.insertCell(2).innerHTML = "<input name='nama[]' type='text' id='nama[]' maxlength='255' />";
		row.insertCell(3).innerHTML = "<input name='satuan[]' type='text' id='satuan[]' maxlength='255' />";
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
</script>

<div class="col-md-12">
	<h3>SISTEM PENILAIAN BANGUNAN <br />
		(TABEL RESOURCE ITEM) </h3>
	<?php
	function nmKodeGroup($kode_Group)
	{
		global $dbSpec;
		$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group where CPM_KODE='$kode_Group';";
		$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
		$re = mysqli_fetch_assoc($result);
		return $re['CPM_NAMA'];
	}

	function paging($totalrows)
	{
		global $a, $m, $f, $page, $perpage;

		$html = "<div>";
		$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

		if ($page != 1) {
			// $page--;
			$html .= "&nbsp;<a onclick=\"setPage('0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows) {
			// $page++;
			$html .= "&nbsp;<a onclick=\"setPage('1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}

	function getTotalRows($query)
	{
		global $dbSpec;
		// echo $query;
		$bOK = $dbSpec->sqlQuery($query, $res);
		if ($bOK === false) {
			echo $query . "<br>";
			echo mysqli_error($DBLink);
		}
		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
	}

	if (!empty($_REQUEST['btTambah'])) {
		$jArray = count($_REQUEST['nama']);
		for ($i = 0; $i < $jArray; $i++) {
			$kode_group = $_REQUEST['kode_group'][$i];
			$kode = $_REQUEST['kode'][$i];
			$nama = $_REQUEST['nama'][$i];
			$satuan = $_REQUEST['satuan'][$i];
			if (!empty($nama) and !empty($satuan)) {
				$sqlTampil = "INSERT INTO cppmod_pbb_resource_item (CPM_KODE_GROUP, CPM_KODE, CPM_NAMA, CPM_SATUAN) VALUES ('$kode_group', '$kode', '$nama', '$satuan');";
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
			$kode_group = $_REQUEST['kode_group'][$i];
			$kode = $_REQUEST['kode'][$i];
			$nama = $_REQUEST['nama'][$i];
			$satuan = $_REQUEST['satuan'][$i];
			$sqlTampil = "UPDATE cppmod_pbb_resource_item SET CPM_NAMA = '$nama', CPM_SATUAN = '$satuan' WHERE CPM_KODE ='$kode' and CPM_KODE_GROUP ='$kode_group';";
			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data diubah!</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btHapus'])) {

		$jArray = count($_REQUEST['kode_group']);
		if ($jArray == 0) {
			$jArray = 1;
		}
		for ($i = 0; $i < $jArray; $i++) {
			$kode = $_REQUEST['KODE'][$i];
			$kode_group = $_REQUEST['kode_group'][$i];
			if (empty($kode) and empty($kode_group)) {
				$kode = $_REQUEST['KODE2'];
				$kode_group = $_REQUEST['kode_group2'];
			}
			//if(empty($kode) and !empty($kode_group)){ continue; }
			if (!empty($kode) and !empty($kode_group)) {
				$sqlTampil = "DELETE FROM cppmod_pbb_resource_item WHERE CPM_KODE ='$kode' and CPM_KODE_GROUP ='$kode_group';";
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
			<div class="col-md-12">
				<button name="tambahData" class="btn btn-primary btn-orange mb5" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
				<button name="editData" class="btn btn-primary btn-blue mb5" type="submit" id="editData" value="Ubah">Ubah</button>
				<button name="btHapus" class="btn btn-primary bg-maka mb5" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
			</div>
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-bordered">
						<tr>
							<th scope="col"><button onclick="Check()" class="btn btn-primary btn-orange mb5" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
							<th scope="col">NO</th>
							<th scope="col"> GROUP</th>
							<th scope="col">KODE</th>
							<th scope="col">NAMA</th>
							<th scope="col">SATUAN</th>
							<th scope="col">PROSES</th>
						</tr>
						<?php
						$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
						$sqlTampil = "SELECT * FROM cppmod_pbb_resource_item order by CPM_KODE_GROUP,CPM_KODE asc LIMIT " . $hal . "," . $perpage;
						$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
						$sqlCount  = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_pbb_resource_item";
						$totalrows = getTotalRows($sqlCount);
						// echo $totalrows."test";
						$no = $hal;
						$i = 0;
						// $no=0;
						while ($r = mysqli_fetch_assoc($result)) {
							$no++;
						?>
							<tr>
								<td><input name="KODE[<?php echo $i; ?>]" type="checkbox" id="KODE[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE']; ?>" />
									<input name="kode_group[<?php echo $i; ?>]" type="hidden" id="kode_group[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_GROUP']; ?>" />
								</td>
								<td><?php echo $no; ?></td>
								<td><?php echo nmKodeGroup($r['CPM_KODE_GROUP']); ?></td>
								<td><?php echo $r['CPM_KODE']; ?></td>
								<td><?php echo $r['CPM_NAMA']; ?></td>
								<td><?php echo $r['CPM_SATUAN']; ?></td>
								<td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kode_group2=$r[CPM_KODE_GROUP]&kode2=$r[CPM_KODE]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE_GROUP'] . "-" . $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kode_group2=$r[CPM_KODE_GROUP]&KODE2=$r[CPM_KODE]"); ?>')">Hapus</a></td>
							</tr>
						<?php $i++;
						} ?>
						<tr>
							<td colspan="9" align="center"><?php echo paging($totalrows); ?></td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	<?php } else if (!empty($_REQUEST['tambahData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<button type="button" class="btn btn-primary bg-maka mb5" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
			<!--<input name="" type="button" value="but" onclick="jumlahElement()" />-->
			<div class="table-responsive">
				<table class="table table-bordered" id="tableAdd">
					<tr>
						<th colspan="4">Tambah Data </th>
					</tr>
					<tr>
						<td>Kode (Group) :</td>
						<td width="144">Kode :</td>
						<td>Nama :</td>
						<td>Satuan :</td>
					</tr>
					<tr>
						<td>
							<select name="kode_group[]" class="form-control" id="kode_group[0]" onchange="kdProses(this.value,this.id)">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
								<?php } ?>
							</select>
						</td>
						<td><input name="kode[]" class="form-control" type="text" id="kode[0]" readonly="true" /></td>
						<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						<td><input name="satuan[]" class="form-control" type="text" id="satuan[]" maxlength="255" /></td>
					</tr>
					<tr>
						<td><select name="kode_group[]" class="form-control" id="kode_group[1]" onchange="kdProses(this.value,this.id)">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
								<?php } ?>
							</select></td>
						<td><input name="kode[]" class="form-control" type="text" id="kode[1]" readonly="true" /></td>
						<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						<td><input name="satuan[]" class="form-control" type="text" id="satuan[]" maxlength="255" /></td>
					</tr>
					<tr>
						<td><select name="kode_group[]" class="form-control" id="kode_group[2]" onchange="kdProses(this.value,this.id)">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
								<?php } ?>
							</select></td>
						<td><input name="kode[]" class="form-control" type="text" id="kode[2]" readonly="true" /></td>
						<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						<td><input name="satuan[]" class="form-control" type="text" id="satuan[]" maxlength="255" /></td>
					</tr>
					<tr>
						<td><select name="kode_group[]" class="form-control" id="kode_group[3]" onchange="kdProses(this.value,this.id)">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
								<?php } ?>
							</select></td>
						<td><input name="kode[]" class="form-control" type="text" id="kode[3]" readonly="true" /></td>
						<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						<td><input name="satuan[]" class="form-control" type="text" id="satuan[]" maxlength="255" /></td>
					</tr>
					<tr>
						<td><select name="kode_group[]" class="form-control" id="kode_group[4]" onchange="kdProses(this.value,this.id)">
								<option value="0">Pilih...</option>
								<?php
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group;";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								while ($r = mysqli_fetch_assoc($result)) { ?>
									<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
								<?php } ?>
							</select></td>
						<td><input name="kode[]" class="form-control" type="text" id="kode[4]" readonly="true" /></td>
						<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						<td><input name="satuan[]" class="form-control" type="text" id="satuan[]" maxlength="255" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td></td>
						<td>
							<div align="right">
								<button name="btTambah" type="submit" id="btTambah" value="Simpan" class="btn btn-primary bg-maka">Simpan</button>
							</div>
						</td>
					</tr>
				</table>
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
								<td width="144">Kode (Group) : </td>
								<td width="230">Kode : </td>
								<td>Nama : </td>
								<td width="144">Satuan : </td>
							</tr>
							<?php
							$jArray = count($_REQUEST['kode_group']);
							if ($jArray == 0) {
								$jArray = 1;
							}
							for ($i = 0; $i < $jArray; $i++) {
								$kode = isset($_REQUEST['KODE'][$i]) ? $_REQUEST['KODE'][$i] : '';
								$kode_group = $_REQUEST['kode_group'][$i];
								if (empty($kode) and empty($kode_group)) {
									$kode = $_REQUEST['kode2'];
									$kode_group = $_REQUEST['kode_group2'];
								}
								if (empty($kode) and !empty($kode_group)) {
									continue;
								}
								$sqlTampil = "SELECT * FROM cppmod_pbb_resource_item where CPM_KODE='$kode' and  CPM_KODE_GROUP='$kode_group';";
								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								$r = mysqli_fetch_assoc($result);
							?>
								<tr>
									<td width="144"><?php
															$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group where CPM_KODE='$r[CPM_KODE_GROUP]';";
															$bOK2 = $dbSpec->sqlQuery($sqlTampil, $result);
															$re = mysqli_fetch_assoc($result); ?>
										<input name="nama_group[]" class="form-control" type="text" id="nama_group[]" value="<?php echo $re['CPM_NAMA']; ?>" readonly="true" />
										<input name="kode_group[]" type="hidden" id="kode_group[]" value="<?php echo $re['CPM_KODE']; ?>" />
									</td>
									<td width="230"><input name="kode[]" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo $r['CPM_KODE']; ?>" /></td>
									<td width="144"><input name="nama[]" class="form-control" type="text" id="nama[]" value="<?php echo $r['CPM_NAMA']; ?>" maxlength="255" /></td>
									<td><input name="satuan[]" type="text" class="form-control" id="satuan[]" value="<?php echo $r['CPM_SATUAN']; ?>" maxlength="255" /></td>
								</tr>
							<?php } ?>
						</table>
						<div style="float: right">
							<button name="btEdit" class="btn btn-primary bg-maka" type="submit" id="btEdit" value="Simpan">Simpan</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php } ?>
</div>