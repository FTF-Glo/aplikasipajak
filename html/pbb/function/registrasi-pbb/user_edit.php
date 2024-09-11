<?php
require_once("inc/PBB/dbUtils.php");
$dbUtils = new DbUtils($dbSpec);
if ($data) {
	//var_dump($_REQUEST);
	$uid = $data->uid;
	/* ------------Setting each city/town for all--------------------- */
	$arConfig = $User->GetAreaConfig($area);
	$AreaPajak = $arConfig["AreaPajak"];
	$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $AreaPajak));
	$aKelurahan = $dbUtils->getKelOnKota($AreaPajak);
	/*-----------------------------------------------------------------*/
	$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'registrasi-pbb', '', dirname(__FILE__))) . '/';
	require_once($sRootPath . "inc/registrasi/inc-registrasi.php");
	require_once($sRootPath . "inc/payment/uuid.php");
	//Simpan data form
	if (isset($simpan)) {
		$idUser = htmlentities($idUser, ENT_QUOTES);
		$status = "0";

		$userId = mysqli_real_escape_string($dbSpec->getDBLink(), $userId);
		$pwd = mysqli_real_escape_string($dbSpec->getDBLink(), $pwd);
		$nm_lengkap = mysqli_real_escape_string($dbSpec->getDBLink(), $nm_lengkap);
		$keterangan = "aktif";
		$sqlSimpan = "UPDATE tbl_reg_user_pbb SET nm_lengkap='$nm_lengkap',nip='$nip',no_ktp='$no_ktp',kecamatan='$kecamatan',kelurahan='$kelurahan',no_hp='$no_hp',email='$email' ,jabatan='$role' where userId='$userId'";
		$bOK = $dbSpec->sqlQuery($sqlSimpan, $result);
		$bOK2 = $Setting->ChangeRole($idUser, $arConfig["pbbApp"], $role);
		if ($bOK && $bOK2) {
			echo "Berhasil disimpan..";
		} else {
			echo "Gagal disimpan..";
		}
		$url64 = base64_encode("a=$a&m=$m&f=f429");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>\n";
		echo " <img src='image/icon/wait.gif' alt=''></img>  Tunggu beberapa saat...";
	}
}

if (isset($_REQUEST["dataId"])) {
	$sqlCari = "SELECT * FROM tbl_reg_user_pbb WHERE id='" . $_REQUEST["dataId"] . "' ORDER BY id";
	//var_dump($sqlCari);
	if ($result = mysqli_query($appDbLink, $sqlCari)) {
		if ($dataTampil = mysqli_fetch_array($result)) {

			$bKelurahan = $dbUtils->getKelurahan(null, array("CPC_TKL_KCID" => $dataTampil['kecamatan']));

?>
			<script language="JavaScript">
				function cekform() {
					if (document.formReg.userId.value == "") {
						alert("Mohon perikasa Nama ID");
						return false;
					} else if (document.formReg.nm_lengkap.value == "") {
						alert("Mohon periksa Nama Lengkap");
						return false;
					} else if (document.formReg.nip.value == "") {
						alert("Mohon periksa NIP");
						return false;
					} else if (document.formReg.no_ktp.value == "") {
						alert("Mohon periksa NO.KTP");
						return false;
					} else if (document.formReg.kota.value == "pilih") {
						alert("Mohon periksa Kota");
						return false;
					} else if (document.formReg.kecamatan.value == "pilih") {
						alert("Mohon periksa Kecamatan");
						return false;
					} else if (document.formReg.kelurahan.value == "pilih") {
						alert("Mohon periksa Kelurahan");
						return false;
					} else if (document.formReg.email.value == "") {
						alert("Mohon periksa email");
						return false;
					} else {
						return true;
					}
				}
			</script>
			<form method="POST" class="col-md-12" action="main.php?param=<?=base64_encode("a=$a&m=$m&f=$f")?>" name="formReg" id="formReg" onSubmit="return cekform();">
				<input type="hidden" name="idUser" readonly value="<?=$dataTampil["ctr_u_id"]?>">

				<table border="0" cellspacing="1" cellpadding="1">
					<tr>
						<th colspan="2" align="left">Form Data Pengguna</th>
					</tr>
					<tr>
						<td>Nama ID</td>
						<td><input type="text" name="userId" class="form-control" size="10" id="userId" readonly value="<?=$dataTampil["userId"]?>">
							<div id="loading"></div>
						</td>
					</tr>
					<tr>
						<td>Nama Lengkap</td>
						<td><input type="text" name="nm_lengkap" class="form-control" size="20" value="<?=$dataTampil["nm_lengkap"]?>"></td>
					</tr>
					<tr>
						<td>NIP</td>
						<td><input type="text" name="nip" class="form-control" size="15" value="<?=$dataTampil["nip"]?>"></td>
					</tr>
					<tr>
						<td>No.KTP</td>
						<td><input type="text" name="no_ktp" class="form-control" size="17" value="<?=$dataTampil["no_ktp"]?>"></td>
					</tr>
					<tr>
						<td>Kecamatan</td>
						<td>
							<select name="kecamatan" id="kecamatan" class="form-control" onchange="showKel(this)">
								<option value="pilih" selected>Pilih...</option>
								<?php
								foreach ($aKecamatan as $row)
									echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($dataTampil["kecamatan"]) && $dataTampil["kecamatan"] == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Kelurahan</td>
						<td>
							<div id="sKel">
								<select name="kelurahan" id="kelurahan" class="form-control">
									<option value="pilih">Pilih...</option>
									<?php foreach ($bKelurahan as $row2) {
										echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($dataTampil["kelurahan"]) && $dataTampil["kelurahan"] == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
									} ?>
								</select>
							</div>

						</td>
					</tr>
					<tr>
						<td valign="top">Hak Akses</td>
						<td>
							<select name="role" id="role" class="form-control">
								<option value="-1" selected>Pilih...</option>
								<?php
								$QryKc = "SELECT * FROM cppmod_pbb_role_module WHERE CTR_RM_ID != '-1'";
								$bOK = $dbSpec->sqlQuery($QryKc, $result);
								while ($KeyRole = mysqli_fetch_array($result)) {
									echo "\n\t\t\t\t<option value='" . $KeyRole['CTR_RM_ID'] . "' " . ((isset($dataTampil["jabatan"]) && $dataTampil["jabatan"] == $KeyRole['CTR_RM_ID']) ? "selected" : "") . ">" . $KeyRole['CTR_RM_NAME'] . "</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2"><b>Dibawah ini salah satunya harus diisi</b></td>
					</tr>
					<tr>
						<td>No. Handphone</td>
						<td><input type="text" name="no_hp" class="form-control" size="15" value="<?=$dataTampil["no_hp"]?>"></td>
					</tr>
					<tr>
						<td>Email</td>
						<td><input type="text" name="email" class="form-control" size="15" value="<?=$dataTampil["email"]?>">&nbsp;&nbsp;<font size="1" color="#FF0000">*utamakan email diisi</font>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="hidden" name="id" value="<?=$uuid ?>"></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="submit" value="Simpan" name="simpan">&nbsp;&nbsp;<input type="reset" value="Kosongkan"></td>
					</tr>
				</table>
			</form>


			<script type="text/javascript">
				function showKel(x) {
					var val = x.value;

					<?php foreach ($aKecamatan as $row) { ?>
						if (val == "<?=$row['CPC_TKC_ID'] ?>") {
							document.getElementById('sKel').innerHTML = "<?php
																						echo "<select name='kelurahan' id='kelurahan'><option value='pilih'>Pilih...</option>";
																						foreach ($aKelurahan as $row2) {
																							if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
																								echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kelurahan) && $kelurahan == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
																							}
																						}
																						echo "</select>"; ?>";
						}
					<?php } ?>

				}
			</script>
<?php
		}
	}
}
?>