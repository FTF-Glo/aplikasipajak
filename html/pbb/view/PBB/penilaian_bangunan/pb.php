<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>

<?php
//ini_set("display_errors",1); error_reporting(E_ALL);
if ($data) {
	$uid = $data->uid;

	$bOk = $User->GetModuleInArea($uid, $area, $moduleIds);
	if (!$bOK) {
		return false;
	}
	$appConfig = $User->GetAppConfig($a);
	$perpage = $appConfig['ITEM_PER_PAGE'];
	//$fungsi=$func[0]['id'];
	//$fungsi2=$func[1]['id'];
	//$sRootPath = str_replace('\\', '/', str_replace('/view/penilaian_bangunan', '', dirname(__FILE__))).'/';
	//require_once($sRootPath."view/penilaian_bangunan/FClass.php");
	/*main.php?param=<?php echo base64_encode("a=$a&m=$m&btHapus=1&kode=$r[CPM_KODE]");?>*/
?>
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
		var n = 7;
		var y = 0;

		function addRows() {
			if (y == 0) {
				v = eval(document.getElementById("kode[]").value) + 5;
			} else if (y != 0) {
				v++;
			}
			var row = document.getElementById("tableAdd").insertRow(n);
			row.insertCell(0).innerHTML = "<input name='kode[]' class=\"form-control\" type='text' id='kode[]' value='" + v + "' readonly='true' />";
			row.insertCell(1).innerHTML = "<input name='nama[]' class=\"form-control\" type='text' id='nama[]' maxlength='255' />";
			n++;
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

		function setPage(page) {
			if (page == 1) page++;
			else page--;

			$('#form1').submit();
			// var url = $(this).attr("href"); /* <-- added var url THIS */

			// e.preventDefault();

			// $("#div").load(url);
			//alert(page);
			//$("#left").tabs( "option", "ajaxOptions", { async: false, data: {page:page} } );
			//$("#left").load();
		}
	</script>
	<div class="col-md-12">
		<h3>SISTEM PENILAIAN BANGUNAN <br /> (TABEL RESOURCE GROUP) </h3>
		<?php
		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
		if (!empty($_REQUEST['btTambah'])) {
			$jArray = count($_REQUEST['nama']);
			for ($i = 0; $i < $jArray; $i++) {
				$kode = $_REQUEST['kode'][$i];
				$nama = $_REQUEST['nama'][$i];
				if (!empty($nama)) {
					$sqlTampil = "INSERT INTO cppmod_pbb_resource_group (CPM_KODE, CPM_NAMA) VALUES ('$kode', '$nama');";
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
				$sqlTampil = "UPDATE cppmod_pbb_resource_group SET CPM_NAMA = '$nama' WHERE CPM_KODE ='$kode';";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
			}
			if ($bOK) {
				echo "<b>" . ($bOK - 1) . " data diubah!</b>";
			} else {
				echo mysqli_error($DBLink);
			}
		} elseif (!empty($_REQUEST['btHapus'])) {
			$jArray = count($_REQUEST['KODE']);
			if ($jArray == 0) {
				$jArray = 1;
			}
			for ($i = 0; $i < $jArray; $i++) {
				$kode = $_REQUEST['KODE'][$i];
				if (empty($kode)) {
					$kode = $_REQUEST['KODE2'];
				}
				$sqlTampil = "DELETE FROM cppmod_pbb_resource_group WHERE CPM_KODE ='$kode';";
				$bOK += $dbSpec->sqlQuery($sqlTampil, $result);
			}
			if ($bOK) {
				echo "<b>" . ($bOK - 1) . " data dihapus!</b>"; //dikurangi 2 karena ada variabel $bOK di modul
			} else {
				echo mysqli_error($DBLink);
			}
		}
		//echo "<br>"; print_r($_REQUEST);
		if (empty($_REQUEST['tambahData']) and empty($_REQUEST['editData'])) { ?>
			<form id="form1" name="form1" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$f"); ?>">
				<div class="row">
					<div class="col-md-12">
						<button name="tambahData" class="btn btn-primary btn-orange mb5" type="submit" id="tambahData" value="Tambah Data">Tambah Data</button>
						<button name="editData" class="btn btn-primary btn-blue mb5" type="submit" id="editData" value="Ubah">Ubah</button>
						<button name="btHapus" class="btn btn-primary bg-maka mb5" onclick="return DelAll();" type="submit" id="btHapus" value="Hapus">Hapus</button>
					</div>
					<div class="col-md-12">
						<div class="table-responsive">
							<table class="table table-bordered">
								<tr>
									<th scope="col"><button onclick="Check()" class="btn btn-primary btn-orange" type="button" value="Pilih Semua" id="tombolCheck">Pilih Semua</button></th>
									<th scope="col">NO</th>
									<th scope="col">KODE</th>
									<th scope="col">NAMA</th>
									<th scope="col">PROSES</th>
								</tr>
								<?php
								$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
								$sqlTotalRows	= "SELECT * FROM cppmod_pbb_resource_group order by CPM_KODE asc ";
								$row			= $dbSpec->sqlQuery($sqlTotalRows, $res);
								$totalrows		= mysqli_num_rows($res);

								$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
								$sqlTampil	= "SELECT * FROM cppmod_pbb_resource_group order by CPM_KODE asc ";
								if ($perpage) {
									$sqlTampil .= " ";
									// $sqlTampil .= " LIMIT $hal, $perpage ";
								}

								$bOK		= $dbSpec->sqlQuery($sqlTampil, $result);
								$n = 0;
								$no = 0;
								while ($r = mysqli_fetch_assoc($result)) {
									$no++;
								?>
									<tr>
										<td><input name="KODE[]" type="checkbox" id="KODE[<?php echo $n; ?>]" value="<?php echo $r['CPM_KODE']; ?>" /></td>
										<td><?php echo $no; ?></td>
										<td><?php echo $r['CPM_KODE']; ?></td>
										<td><?php echo $r['CPM_NAMA']; ?></td>
										<td><a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&editData=1&KODE2=$r[CPM_KODE]"); ?>">Ubah</a> | <a href="#" onclick="prosesDel('<?php echo $r['CPM_KODE']; ?>','main.php?param=<?php echo base64_encode("a=$a&m=$m&btHapus=1&KODE2=$r[CPM_KODE]"); ?>')">Hapus</a></td>
									</tr>
								<?php $n++;
								} ?>
							</table>
						</div>
					</div>
				</div>
			</form>
			<?php //echo paging(); 
			?>
		<?php } else if (!empty($_REQUEST['tambahData'])) { ?>
			<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m"); ?>">
				<?php
				$sqlTampil = "SELECT max(CPM_KODE) as tKode FROM cppmod_pbb_resource_group;";
				$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
				$r = mysqli_fetch_assoc($result);
				$tKode = $r['tKode'];
				$tKode++;
				?><button type="button" class="btn btn-primary bg-maka mb15" value="Tambah Baris" onclick="addRowsMulti()">Tambah Baris</button>
				<div class="table-responsive">
					<table class="table table-bordered" id="tableAdd">
						<tr>
							<th colspan="2">Tambah Data </th>
						</tr>
						<tr>
							<td width="20%">Kode : </td>
							<td>Nama : </td>
						</tr>
						<tr>
							<td><input name="kode[]" class="form-control" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo ($tKode < 10) ? '0' . ($tKode++) : ($tKode++); ?>"></td>
							<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						</tr>
						<tr>
							<td><input name="kode[]" class="form-control" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo ($tKode < 10) ? '0' . ($tKode++) : ($tKode++); ?>"></td>
							<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						</tr>
						<tr>
							<td><input name="kode[]" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo ($tKode < 10) ? '0' . ($tKode++) : ($tKode++); ?>"></td>
							<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						</tr>
						<tr>
							<td><input name="kode[]" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo ($tKode < 10) ? '0' . ($tKode++) : ($tKode++); ?>"></td>
							<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						</tr>
						<tr>
							<td><input name="kode[]" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo ($tKode < 10) ? '0' . ($tKode++) : ($tKode++); ?>"></td>
							<td><input name="nama[]" class="form-control" type="text" id="nama[]" maxlength="255" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<div align="right">
									<button class="btn btn-primary bg-maka" name="btTambah" type="submit" id="btTambah" value="Simpan">Simpan</button>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</form>
		<?php } else if (!empty($_REQUEST['editData'])) { ?>
			<form id="form2" name="form2" method="post" action="main.php?param=<?php echo base64_encode("a=$a&m=$m"); ?>">
				<div class="row">
					<div class="col-md-12">
						<label>Ubah Data</label><br />
						<div class="table-responsive">
							<table class="table table-bordered">
								<tr>
									<td width="20%">Kode : </td>
									<td>Nama : </td>
								</tr>
								<?php
								$jArray = count($_REQUEST['KODE']);
								if ($jArray == 0) {
									$jArray = 1;
								}

								for ($i = 0; $i < $jArray; $i++) {
									$kode = $_REQUEST['KODE'][$i];
									if (empty($kode)) {
										$kode = $_REQUEST['KODE2'];
									}
									$sqlTampil = "SELECT * FROM cppmod_pbb_resource_group where CPM_KODE='$kode';";
									$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
									$r = mysqli_fetch_assoc($result);
								?>
									<tr>
										<td>
											<input name="kode[]" class="form-control" type="text" id="kode[]" readonly="true" value="<?php echo $r['CPM_KODE']; ?>" />
										</td>
										<td>
											<input name="nama[]" class="form-control" type="text" id="nama[]" value="<?php echo $r['CPM_NAMA']; ?>" maxlength="255" />
										</td>
									</tr>
								<?php
								}
								?>
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
<?php }

function paging()
{
	global $page, $totalrows, $appConfig, $perpage;

	$defaultPage = 1;
	$html = "<div>";
	$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
	$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

	if ($page != 1) {
		//$page--;
		$html .= "&nbsp;<a onclick=\"setPage('0')\"><span id=\"navigator-left\"></span></a>";
	}
	if ($rowlast < $totalrows) {
		//$page++;
		$html .= "&nbsp;<a onclick=\"setPage('1')\"><span id=\"navigator-right\"></span></a>";
	}
	$html .= "</div>";
	return $html;
}

?>