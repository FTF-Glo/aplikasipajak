<?php
require_once("inc/PBB/dbUtils.php");
$dbUtils = new DbUtils($dbSpec);
if ($data) {
	$uid = $data->uid;

	// get module
	$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
	if (!$bOK) {
		return false;
	}

	/* ------------Setting each city/town for all--------------------- */
	$arConfig = $User->GetAreaConfig($area);
	$AreaPajak = $arConfig["AreaPajak"];

	$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $AreaPajak));
	$aKelurahan = $dbUtils->getKelOnKota($AreaPajak);
	/*-----------------------------------------------------------------*/


	//uuid
	function c_uuid($sDelim = '')
	{
		// The field names refer to RFC 4122 section 4.1.2
		return sprintf(
			'%04x%04x%s%04x%s%03x4%s%04x%s%04x%04x%04x',
			mt_rand(0, 65535),
			mt_rand(0, 65535), // 32 bits for "time_low"
			$sDelim,
			mt_rand(0, 65535), // 16 bits for "time_mid"
			$sDelim,
			mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
			$sDelim,
			bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
			$sDelim,
			// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
			// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
			// 8 bits for "clk_seq_low"
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535) // 48 bits for "node"
		);
	} // end of c_uuid

	$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Registrasi', '', dirname(__FILE__))) . '/';

	//Simpan data form
	if (isset($simpan)) {
		$idUser = "u" . $Setting->GetNextUserId();
		$idUser = htmlentities($idUser, ENT_QUOTES);
		$status = "0";
		$keterangan = "aktif";
		$userId = mysqli_real_escape_string($dbSpec->getDBLink(), $userId);
		$pwd = mysqli_real_escape_string($dbSpec->getDBLink(), $pwd);
		$nm_lengkap = mysqli_real_escape_string($dbSpec->getDBLink(), $nm_lengkap);
		$sqlSimpan = "INSERT INTO tbl_reg_user_pbb SET uuid='$id',userId='$userId',password='$pwd',nm_lengkap='$nm_lengkap',nip='$nip',no_ktp='$no_ktp',kota='$AreaPajak',kelurahan='$kelurahan',kecamatan='$kecamatan',no_hp='$no_hp',jabatan='$role',email='$email',status='$status',keterangan='$keterangan',areapajak='$AreaPajak',ctr_u_id='$idUser'";
		$username1 = "SELECT userId FROM tbl_reg_user_pbb WHERE userId='$userId'";
		$username2 = "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$userId'";
		$check_1 = $dbSpec->sqlQuery($username1, $result);
		$check_2 = $dbSpec->sqlQuery($username2, $result);
		$check_for_username = mysqli_num_rows($result);
		$check_for_username2 = mysqli_num_rows($result);
		if (stristr($userId, "'")) {
			echo "\n<script>alert('Maaf, Nama ID teridentifikasi mengandung tanda kutip. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang valid!')</script>";
			$url64 = base64_encode("a=$a&m=$m");
			echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
			echo "\n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...\n";
		} else if ($check_for_username || $check_for_username2) {
			echo "\n<script>alert('Maaf, Nama ID teridentifikasi bahwa sudah terpakai. Silahkan ulangi pendaftaran, harap gunakan Nama ID yang lain!')</script>";
			$url64 = base64_encode("a=$a&m=$m");
			echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
			echo "\n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...\n";
		} else {
			$bOK = $dbSpec->sqlQuery($sqlSimpan, $result);
			if ($bOK) {
				$bOK = $Setting->InsertUser($idUser, $userId, $pwd, 0, 0, 0, $arConfig["userTheme"]);
				if ($bOK) {
					echo "\nBerhasil disimpan..";
					//                                    require_once($sRootPath."view/Registrasi/notifikasi_email.php");

					$Setting->ChangeRole($idUser, $arConfig["pbbApp"], $role);
				} else {
					echo "\nGagal disimpan..";
				}
			} else {
				echo "\nGagal disimpan..";
			}
			$url64 = base64_encode("a=$a&m=$m");
			echo "\n<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
			echo " \n<img src='image/icon/wait.gif' alt=''></img>\nTunggu beberapa saat...\n";
		}
	}

?>
	<script type="text/javascript" src="view/Registrasi/js/config.js"></script>
	<div class="col-md-12">
		<div class="box box-default">
			<div class="box-header">
				<h3 class="box-title">Form Data Pengguna</h3>
			</div>
			<div class="box-body">
				<form method="POST" action="main.php?param=<?php echo base64_encode("a=$a&m=$m") ?>" name="formReg" id="formReg" onSubmit="return cekformpbb();">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="">Nama ID</label>
								<input type="text" class="form-control" name="userId" id="userId">
								<label style="margin-top: 10px;">
									<input type="button" id="tombol" class="btn btn-primary btn-orange" value="cek ketersediaan" onclick="cek_id(); return false;">
									<div id="loading"></div>
								</label>
							</div>
							<input type="hidden" name="a" value="<?php echo $a; ?>" id="a"><input type="hidden" name="m" value="<?php echo $m; ?>" id="m">
							<script type="text/javascript" src="jquery-1.4.2.min.js"></script>
							<script type="text/javascript">
								function cek_id() {
									$("#loading").html('<img src=image/icon/loadinfo.net.gif></img><font size=1>memeriksa..</font>');
									id = $("#userId").val();
									a = $("#a").val();
									m = $("#m").val();
									$.post('view/Registrasi/cek_id_pbb.php', {
										userId: id,
										app: a,
										mod: m
									}, function(hasil) {
										$("#loading").html(hasil);
									});
								}
							</script>
							<div class="form-group">
								<label for="">Password</label>
								<input type="password" class="form-control" name="pwd">
							</div>
							<div class="form-group">
								<label for="">Nama Lengkap</label>
								<input type="text" class="form-control" name="nm_lengkap" size="40">
							</div>
							<div class="form-group">
								<label for="">NIP</label>
								<input type="text" class="form-control" name="nip" size="25">
							</div>
							<div class="form-group">
								<label for="">No.KTP</label>
								<input type="text" class="form-control" name="no_ktp" size="25">
							</div>
							<div class="form-group">
								<label for="">Kecamatan</label>
								<select name="kecamatan" id="kecamatan" class="form-control" onchange="showKel(this)">
									<option value="pilih" selected>Pilih...</option>
									<?php
									foreach ($aKecamatan as $row)
										echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($OP_KECAMATAN) && $OP_KECAMATAN == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
									?>
								</select>
							</div>
							<div class="form-group">
								<label for="">Kelurahan</label>
								<div id="sKel">
									<select name="kelurahan" class="form-control" id="kelurahan">
										<option value="pilih">Pilih...</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="">Hak Akses</label>
								<select name="role" id="role" class="form-control">
									<option value="-1" selected>Pilih...</option>
									<?php
									$QryKc = "SELECT * FROM cppmod_pbb_role_module WHERE CTR_RM_ID != '-1'";
									$bOK = $dbSpec->sqlQuery($QryKc, $result);
									while ($KeyRole = mysqli_fetch_array($result)) {
										echo "\n\t\t\t\t<option value='" . $KeyRole['CTR_RM_ID'] . "'>" . $KeyRole['CTR_RM_NAME'] . "</option>";
									}
									?>
								</select>
							</div>
							<div class="form-group">
								<label for=""><b>Dibawah ini salah satunya harus diisi</b></label>
							</div>
							<div class="form-group">
								<label for="">No. Handphone</label>
								<input type="text" class="form-control" name="no_hp">
							</div>
							<div class="form-group">
								<label for="">Email</label>
								<input type="text" class="form-control" name="email">
								<font size="1" color="#FF0000">*utamakan email diisi</font>
							</div>
							<input type="hidden" name="id" value="<?php echo c_uuid(); ?>">
							<div class="box-tools">
								<input type="submit" value="Simpan" class="btn btn-primary btn-orange" name="simpan">
								<input type="reset" class="btn btn-primary btn-blue" value="Batalkan">
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		function showKel(x) {
			var val = x.value;

			<?php foreach ($aKecamatan as $row) { ?>
				if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
					document.getElementById('sKel').innerHTML = "<?php
																				echo "<select name='kelurahan' id='kelurahan' class='form-control'><option value='pilih'>Pilih...</option>";
																				foreach ($aKelurahan as $row2) {
																					if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
																						echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kelurahan) && $kelurahan == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . "</option>";
																					}
																				}
																				echo "</select>"; ?> ";
				}
			<?php } ?>

		}
	</script>
<?php
}
?>