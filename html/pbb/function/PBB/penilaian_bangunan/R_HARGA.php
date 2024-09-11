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

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User	 	= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$perpage 	= $appConfig['ITEM_PER_PAGE'];
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kota           = $appConfig['KODE_KOTA'];
$ftahun          = @isset($_REQUEST['ftahun']) ? $_REQUEST['ftahun'] : $appConfig['tahun_tagihan'];
$fkdgroup        = @isset($_REQUEST['fkdgroup']) ? $_REQUEST['fkdgroup'] : '01';

?>
<!---------------code------------->
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
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

	function iniAngka(evt) {
		var charCode = (evt.which) ? evt.which : event.keyCode
		//alert(charCode);
		if (charCode >= 48 && charCode <= 57 || charCode == 8 || charCode == 46)
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
		//CPM_KODE diganti jadi CPM_KODE_GROUP
		$sqlTampil = "SELECT CPM_KODE_GROUP, MAX(CPM_KODE_GROUP)+1 as tCPM_KODE FROM cppmod_pbb_resource_harga group by CPM_KODE_GROUP;";
		$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
		while ($result && $r = mysqli_fetch_assoc($result)) { ?>if('<?php echo $r['CPM_KODE_GROUP']; ?>' && a == <?php echo $r['CPM_KODE_GROUP']; ?>) {
			val = <?php echo isset($r['tCPM_KODE']) ? $r['tCPM_KODE'] : ''; ?> + nT;
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
		var b = confirm("Anda akan menghapus resource " + a + " ?");
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
			//v = document.getElementById("id[0]").value;
			//v = parseInt(v, 10) + 5;
			v = eval(document.getElementById("kode_lokasi[]").value) + 5;
		} else if (y != 0) {
			v++;
		}
		if (v < 10) {
			v = "00" + v;
		} else if (v < 100) {
			v = "0" + v;
		}
		var row = document.getElementById("tableAdd").insertRow(n);
		row.insertCell(0).innerHTML = "<input class=\"form-control\" name='id[" + id + "]' type='hidden' id='id[" + id + "]' readonly='true' value='" + v + "'><input class=\"form-control\" name='kode_lokasi[" + id + "]' type='hidden' id='kode_lokasi[" + id + "]' maxlength='4' onkeypress='return iniAngka(event)' /><input class=\"form-control\" name='tahun[" + id + "]' type='text' id='tahun[" + id + "]' maxlength='4' onkeypress='return iniAngka(event)' />";
		row.insertCell(1).innerHTML = "<select class=\"form-control\" name='kode_group[" + id + "]' id='kode_group[" + id + "]' onchange='showSub(this.value,this.id)'><option value='0'>Pilih...</option><?php
																																																																			$sqlTampil = 'SELECT * FROM cppmod_pbb_resource_group order by CPM_NAMA asc;';
																																																																			$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
																																																																			while ($r = mysqli_fetch_assoc($result)) { ?><option value='<?php echo $r['CPM_KODE']; ?>'><?php echo $r['CPM_NAMA']; ?></option><?php } ?></select>";
		row.insertCell(2).innerHTML = "<span id='showSub[" + id + "]'></span>";
		row.insertCell(3).innerHTML = "<input class=\"form-control\" name='harga[" + id + "]' type='text' id='harga[" + id + "]'  maxlength='9' onkeypress='return iniAngka(event)' />";
		//row.insertCell(4).innerHTML = "<input class=\"form-control\" name='status[" + id + "]' type='radio' value='1' /> 1 <input name='status[" + id + "]' type='radio' value='0' checked='checked' />  0";
		n++;
		id++;
		y++;
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
		var iId = id.substring(11, le - 1);
		var k_res = document.getElementById("kode_resource[" + iId + "]");
		if (k_res != "" && k_res != null) {
			k_res.name = "none[" + iId + "]";
			k_res.id = "none[" + iId + "]";
			k_res.style.display = "none";
		}
		<?php
		$sqlTampil = 'SELECT CPM_KODE_GROUP FROM `cppmod_pbb_resource_item` group by CPM_KODE_GROUP;';
		$dbSpec->sqlQuery($sqlTampil, $result);
		while ($r = mysqli_fetch_assoc($result)) { ?>if(document.getElementById(id).value == "<?php echo $r['CPM_KODE_GROUP']; ?>") {
			document.getElementById("showSub[" + iId + "]").innerHTML = "<select name='kode_resource[" + iId + "]' id='kode_resource[" + iId + "]' ><option value='0'>Pilih...</option><?php
																																																												$sqlTampil2 = "SELECT * FROM `cppmod_pbb_resource_item` where CPM_KODE_GROUP='$r[CPM_KODE_GROUP]' order by CPM_NAMA asc;";
																																																												$query = mysqli_query($DBLink, $sqlTampil2);
																																																												while ($re = mysqli_fetch_assoc($query)) { ?><option value='<?php echo $re['CPM_KODE']; ?>'><?php echo $re['CPM_NAMA']; ?></option><?php } ?></select>";
		} else <?php } ?> {
		document.getElementById("showSub[" + iId + "]").innerHTML = "<p style='width:350px'>Resource item belum terdaftarkan di Resource group ini!</p>";
	}


		/*	<select name="kode_resource[]" id="kode_resource[]" >
				<option value="0">Pilih...</option>
				<?php
				$sqlTampil = "SELECT * FROM cppmod_pbb_resource_item order by CPM_NAMA asc;";
				$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
				while ($r = mysqli_fetch_assoc($result)) { ?>
				<option value="<?php echo $r['CPM_KODE']; ?>"><?php echo $r['CPM_NAMA']; ?></option>
				<?php } ?>
				</select>*/
	}

	function changeGroupFilter(group) {
		$('#form1').submit();
	}
</script>
<div class="col-md-12">
	<h3>SISTEM PENILAIAN BANGUNAN <br />
		(TABEL RESOURCE HARGA) </h3>
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

	function getResourceGroup()
	{
		global $dbSpec;
		$sqlTampil = "SELECT CPM_KODE,CPM_NAMA FROM cppmod_pbb_resource_group
                    WHERE CPM_KODE IN('01', '02','03','04','05','06','07','08','09','10','11') ORDER BY CPM_KODE";
		$bOK       = $dbSpec->sqlQuery($sqlTampil, $result);
		$arr = array();

		while ($r = mysqli_fetch_assoc($result)) {
			$arr[$r['CPM_KODE']] = $r['CPM_NAMA'];
		}
		return $arr;
	}

	$arrReource = getResourceGroup();

	if (!empty($_REQUEST['btTambah'])) {
		$jArray = count($_REQUEST['kode_lokasi']);
		for ($i = 0; $i < $jArray; $i++) {

			$kode_lokasi   = $_REQUEST['kode_lokasi'][$i];
			$tahun          = $_REQUEST['tahun'][$i];
			$kode_group    = $_REQUEST['kode_group'][$i];
			$kode_resource = $_REQUEST['kode_resource'][$i];
			$harga 		   = $_REQUEST['harga'][$i];
			// $status 	   = $_REQUEST['status'][$i]; 

			if (!empty($kode_lokasi) and !empty($tahun) and !empty($kode_group) and !empty($kode_resource) and !empty($harga)) {
				$propinsi = substr($kode_lokasi, 0, 2);
				$kabkota = substr($kode_lokasi, 2, 2);

				$sql = "SELECT * FROM
						cppmod_pbb_resource_harga AS A
						LEFT JOIN cppmod_pbb_resource_group B ON B.CPM_KODE = A.CPM_KODE_GROUP
						LEFT JOIN cppmod_pbb_resource_item C ON C.CPM_KODE_GROUP = A.CPM_KODE_GROUP 
					WHERE 
					A.CPM_KD_PROPINSI = '$propinsi'
					and
					A.CPM_KD_DATI2 = '$kabkota'
					and
					A.CPM_TAHUN = '$tahun'
					and
					A.CPM_KODE_GROUP = '$kode_group'
					and 
					A.CPM_KODE_RESOURCE = '$kode_resource'

					 ";
				// echo $sql;
				$dbSpec->sqlQuery($sql, $xx);
				$count = mysqli_num_rows($xx);
				if ($count <= 0) {

					$sqlTampil = "INSERT INTO cppmod_pbb_resource_harga (CPM_KD_PROPINSI, CPM_KD_DATI2, CPM_TAHUN, CPM_KODE_GROUP, CPM_KODE_RESOURCE, CPM_HARGA) VALUES ('$propinsi', '$kabkota', '$tahun', '$kode_group', '$kode_resource', '$harga');";
					// echo $sqlTampil;
					// exit;
					$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
				} else {
					$d = mysqli_fetch_array($xx);
					// echo "<pre>";
					// print_r($d);

					// var_dump($d);
					$bOK = false;
					$msg = "
	                	Data Dengan <br> 
	                	Group  : $d[8] <br> 
	                	Resource  : $d[CPM_NAMA]` <br> 
	                	Harga : $harga <br>
	                	Tahun : $tahun <br> 
	                	telah ada di database, silahkan coba lagi .
	      		          	";
				}
			}
		}
		// var_dump($xx);
		// var_dump($d);
		// print_r($d);
		// exit;
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data ditambahkan !</b>";
		} else {
			echo $msg . " " . mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btEdit'])) {

		$jArray = count($_REQUEST['kode_lokasi']);

		for ($i = 0; $i < $jArray; $i++) {
			$id = $_REQUEST['id'];
			$req = explode("+", $_REQUEST['req'][$i]);
			$kdLok		= $req[0];
			$thn		= $req[1];
			$kdGroup    = $req[2];
			$kdResource = $req[3];
			$harga 		= $req[4];

			$kode_lokasi   = $_REQUEST['kode_lokasi'][$i];
			$tahun 		   = $_REQUEST['tahun'][$i];
			$kode_group    = $_REQUEST['kode_group'][$i];
			$kode_resource = $_REQUEST['kode_resource'][$i];
			$harga_edited  = $_REQUEST['harga'][$i];
			$sqlTampil = "UPDATE cppmod_pbb_resource_harga SET
					CPM_HARGA = '$harga_edited'
					WHERE CONCAT(CPM_KD_PROPINSI,CPM_KD_DATI2) = '$kdLok'
					AND CPM_TAHUN = '$thn'
					AND CPM_KODE_GROUP = '$kdGroup'
					AND CPM_KODE_RESOURCE = '$kdResource'";

			$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
		}
		if ($bOK) {
			echo "<b>" . ($bOK - 1) . " data diubah!</b>";
		} else {
			echo mysqli_error($DBLink);
		}
	} elseif (!empty($_REQUEST['btHapus'])) {

		if (!empty($_REQUEST['id']))
			$jArray = count($_REQUEST['id']);
		else
			$jArray = count($_REQUEST['kdLok']);

		if ($jArray == 0) {
			$jArray = 1;
		}
		for ($i = 0; $i < $jArray; $i++) {
			$id = $_REQUEST['id'][$i];
			if (!empty($id)) {
				$req = explode("+", $id);
				$kdLok		= $req[0];
				$thn		= $req[1];
				$kdGroup    = $req[2];
				$kdResource = $req[3];
				$harga	    = $req[4];
			} else {
				$kdLok		= $_REQUEST['kdLok'];
				$thn		= $_REQUEST['thn'];
				$kdGroup    = $_REQUEST['kdGroup'];
				$kdResource = $_REQUEST['kdResource'];
				$harga	    = $_REQUEST['harga'];
			}
			if (!empty($id) || (!empty($_REQUEST['kdLok']))) {
				$sqlTampil = "DELETE FROM cppmod_pbb_resource_harga WHERE CONCAT(CPM_KD_PROPINSI,CPM_KD_DATI2) = '$kdLok'
		AND CPM_TAHUN = '$thn'
		AND CPM_KODE_GROUP = '$kdGroup'
		AND CPM_KODE_RESOURCE = '$kdResource'";

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
					<?php
					echo "<div class=\"row\">
							<div class=\"col-md-2\">
								<div class=\"form-group\">
									<label>Filter : Tahun</label>
									<input type=\"text\" class=\"form-control\" name=\"ftahun\" value=\"" . $ftahun . "\" size=\"5\" maxlength=\"4\" />
								</div>
							</div>
							<div class=\"col-md-3\">
								<div class=\"form-group\">
									<label>Resource Group</label>
									<select name=\"fkdgroup\" class=\"form-control\" id=\"fkdgroup\" onchange='changeGroupFilter(this);'>";
					foreach ($arrReource as $key => $val)
						echo "<option value='" . $key . "' " . ((isset($fkdgroup) && $fkdgroup == $key) ? "selected" : "") . ">" . $val . "</option>";
					echo "			</select>
								</div>
							</div>
						</div>";
					?>
					<div class="row">
						<div class="col-md-12">
							<button name="tambahData" class="btn btn-primary btn-orange" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
							<button name="editData" class="btn btn-primary btn-blue" type="submit" id="editData" value="Ubah">Ubah</button>
							<button name="btHapus" class="btn btn-primary bg-maka" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
						</div>
						<div class="col-md-12" style="margin-top: 15px;">
							<div class="table-responsive">
								<table class="table table-bordered">
									<tr>
										<th scope="col"><button class="btn btn-primary btn-orange" onclick="Check()" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
										<th scope="col">NO</th>
										<th scope="col">TAHUN</th>
										<th scope="col">RESOURCE GROUP </th>
										<th scope="col">RESOURCE ITEM </th>
										<th scope="col">HARGA<br>(Rp 1.000)</th>
										<th scope="col">PROSES</th>
									</tr>
									<?php
									$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
									$sqlTampil = "SELECT
                    CONCAT(
                            A.CPM_KD_PROPINSI,
                            A.CPM_KD_DATI2
                    ) AS CPM_KODE_LOKASI,
                    A.*, B.CPM_NAMA AS NAMAGROUP, C.CPM_NAMA AS NAMARESOURCE FROM cppmod_pbb_resource_harga A
            LEFT JOIN cppmod_pbb_resource_group B ON A.CPM_KODE_GROUP=B.CPM_KODE
            LEFT JOIN cppmod_pbb_resource_item C ON A.CPM_KODE_RESOURCE=C.CPM_KODE AND A.CPM_KODE_GROUP=C.CPM_KODE_GROUP 
            WHERE A.CPM_TAHUN='" . $ftahun . "' AND A.CPM_KODE_GROUP='" . $fkdgroup . "' order by A.CPM_TAHUN desc, A.CPM_KODE_GROUP, A.CPM_KODE_RESOURCE LIMIT " . $hal . "," . $perpage;

									$bOK       = $dbSpec->sqlQuery($sqlTampil, $result);
									$sqlCount  = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_pbb_resource_harga WHERE CPM_TAHUN='" . $ftahun . "' AND CPM_KODE_GROUP='" . $fkdgroup . "' ";
									$totalrows = getTotalRows($sqlCount);

									$no = $hal;
									$i = 0;
									while ($r = mysqli_fetch_assoc($result)) {
										$no++;
									?>
										<tr>
											<td><input name="id[]" type="checkbox" id="id[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_LOKASI'] . "+" . $r['CPM_TAHUN'] . "+" . $r['CPM_KODE_GROUP'] . "+" . $r['CPM_KODE_RESOURCE']; ?>" /></td>
											<td><?php echo $no; ?></td>
											<td><?php echo $r['CPM_TAHUN']; ?></td>
											<td><?php echo $r['NAMAGROUP']; ?></td>
											<td><?php echo $r['NAMARESOURCE']; ?></td>
											<td><?php echo $r['CPM_HARGA']; ?></td>
											<td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&editData=1&kdLok=$r[CPM_KODE_LOKASI]&thn=$r[CPM_TAHUN]&kdGroup=$r[CPM_KODE_GROUP]&kdResource=$r[CPM_KODE_RESOURCE]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['NAMARESOURCE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f&btHapus=1&kdLok=$r[CPM_KODE_LOKASI]&thn=$r[CPM_TAHUN]&kdGroup=$r[CPM_KODE_GROUP]&kdResource=$r[CPM_KODE_RESOURCE]"); ?>')">Hapus</a></td>
										</tr>
									<?php $i++;
									} ?>
									<tr>
										<td colspan="7" align="center"><?php echo paging($totalrows); ?></td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	<?php } else if (!empty($_REQUEST['tambahData'])) { ?>
		<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
			<button class="btn btn-primary bg-maka" type="button" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
			<div class="row" style="margin-top: 15px;">
				<div class="col-md-12">
					<div class="table-responsive">
						<table class="table table-bordered" id="tableAdd">
							<tr>
								<th colspan="4">Tambah Data </th>
							</tr>
							<tr>
								<td>TAHUN</td>
								<td>RESOURCE GROUP</td>
								<td>RESOURCE ITEM</td>
								<td>HARGA</td>
							</tr>
							<?php for ($i = 0; $i < 5; $i++) { ?>
								<tr>
									<input name="kode_lokasi[]" class="form-control" type="hidden" id="kode_lokasi[]" value="<?php echo $kota; ?>" maxlength="4" />
									<td><input name="tahun[]" type="text" class="form-control" id="tahun[<?php echo $i; ?>]" maxlength="4" size="5" value="<?php echo $ftahun; ?>" onkeypress="return iniAngka(event)" /></td>
									<td>
										<select name="kode_group[]" class="form-control" id="kode_group[<?php echo $i; ?>]" onchange="showSub(this.value,this.id)">
											<option value="0">Pilih...</option>
											<?php
											foreach ($arrReource as $key => $val) {
												echo '<option value="' . $key . '">' . $val . '</option>';
											}
											?>
										</select>
									</td>
									<td><span id="showSub[<?php echo $i; ?>]"></span></td>
									<td><input class="form-control" name="harga[]" type="text" id="harga[<?php echo $i; ?>]" maxlength="9" size="7" onkeypress="return iniAngka(event)" /></td>
								</tr>
							<?php } ?>
							<tr>
								<td></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>
									<div align="right"> <button class="btn btn-primary bg-maka" name="btTambah" type="submit" id="btTambah" value="Simpan">Simpan</button></div>
								</td>

							</tr>
						</table>
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
								<td width="15%">TAHUN</td>
								<td>RESOURCE GROUP</td>
								<td>RESOURCE ITEM</td>
								<td width="15%">HARGA</td>
							</tr>
							<?php
							// print_r($_REQUEST);
							$jArray = count($_REQUEST['id']);

							if ($jArray == 0) {
								$jArray = 1;
							}

							for ($i = 0; $i < $jArray; $i++) {
								$id = $_REQUEST['id'][$i];
								if (empty($id)) {
									$kdLok		= $_REQUEST['kdLok'];
									$thn		= $_REQUEST['thn'];
									$kdGroup	= $_REQUEST['kdGroup'];
									$kdResource	= $_REQUEST['kdResource'];
									$harga		= $_REQUEST['harga'];
								} else {
									$req = explode("+", $id);
									$kdLok		= $req[0];
									$thn		= $req[1];
									$kdGroup    = $req[2];
									$kdResource = $req[3];
								}

								$sqlTampil = "SELECT A.*, B.CPM_NAMA AS NAMAGROUP, C.CPM_NAMA AS NAMARESOURCE FROM cppmod_pbb_resource_harga A
                    LEFT JOIN cppmod_pbb_resource_group B ON A.CPM_KODE_GROUP=B.CPM_KODE
                    LEFT JOIN cppmod_pbb_resource_item C ON A.CPM_KODE_RESOURCE=C.CPM_KODE AND A.CPM_KODE_GROUP=C.CPM_KODE_GROUP
                    WHERE CONCAT(A.CPM_KD_PROPINSI, A.CPM_KD_DATI2)='$kdLok' AND A.CPM_TAHUN='$thn' 
                    AND A.CPM_KODE_GROUP='$kdGroup' AND A.CPM_KODE_RESOURCE='$kdResource';";

								$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
								$r = mysqli_fetch_assoc($result);
							?>
								<tr>
									<input name="req[<?php echo $i; ?>]" type="hidden" value="<?php echo $kdLok . "+" . $thn . "+" . $kdGroup . "+" . $kdResource; ?>" />
									<input name="kode_lokasi[<?php echo $i; ?>]" type="hidden" id="kode_lokasi[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_LOKASI']; ?>" maxlength="4" onkeypress="return iniAngka(event)" />
									<td><input name="tahun[<?php echo $i; ?>]" type="text" id="tahun[<?php echo $i; ?>]" value="<?php echo $r['CPM_TAHUN']; ?>" maxlength="4" size="5" readonly=\"true\" /></td>
									<td>
										<input name="kode_group[<?php echo $i; ?>]" type="hidden" id="kode_group[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_GROUP']; ?>" />
										<input name="nama_group[<?php echo $i; ?>]" type="text" id="nama_group[<?php echo $i; ?>]" value="<?php echo $r['NAMAGROUP']; ?>" size="30" readonly=\"true\" />
									</td>
									<td>
										<input name="kode_resource[<?php echo $i; ?>]" type="hidden" id="kode_resource[<?php echo $i; ?>]" value="<?php echo $r['CPM_KODE_RESOURCE']; ?>" />
										<input name="nama_resource[<?php echo $i; ?>]" type="text" id="nama_resource[<?php echo $i; ?>]" value="<?php echo $r['NAMARESOURCE']; ?>" size="30" readonly=\"true\" />
									</td>
									<td><input name="harga[<?php echo $i; ?>]" type="text" id="harga[<?php echo $i; ?>]" value="<?php echo $r['CPM_HARGA']; ?>" maxlength="9" size="7" onkeypress="return iniAngka(event)" /></td>
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