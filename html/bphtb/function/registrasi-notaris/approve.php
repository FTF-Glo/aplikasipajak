<?php
if (!isset($data)) {
	die();
}
// var_dump();
// if ($_SERVER["REQUEST_METHOT"] == "POST" && isset($_POST["submit"])) {
// 	var_dump("sukses");
// 	// die;
// }

// function updateData()
// {
// 	return true;
// }
// // Langkah 4: Panggil Fungsi Simpan dari Form
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
// 	$nama = $_POST['namalk'];
// 	$email = $_POST['email'];

// 	// Panggil fungsi simpan
// 	if (updateData($nama, $email, $conn)) {
// 		echo "<script>
//             $(document).ready(function() {
//                 $('#modal" . $_POST['id'] . "').modal('hide');
//             });
//         </script>";
// 	} else {
// 		echo "Error: Data gagal disimpan.";
// 	}
// }


// NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig["terminalColumn"])) {
	$terminalColumn = $arAreaConfig["terminalColumn"];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if (!$accessible) {
		echo "Illegal access";
		return;
	}
}

/* ------------Setting each city/town for all--------------------- */
$arConfig = $User->GetAreaConfig($area);
$AreaPajak = $arConfig["AreaPajak"];

$Qry = "SELECT *FROM cppmod_tax_kabkota WHERE CPC_TK_ID='$AreaPajak'";
$bOK = $dbSpec->sqlQuery($Qry, $result);
// $result = mysqli_query($DBLink, $Qry);
$Key = mysqli_fetch_array($result);

$IdKK = $Key['CPC_TK_ID'];
$NameKK = $Key['CPC_TK_KABKOTA'];
/*-----------------------------------------------------------------*/


$func = $func[6]["id"];
// var_dump($func);
// die;
echo "<div class='content-wrapper' style='padding-top:0px;padding-bottom:0px'>		
	  <div class='spacer10'></div>
	  <div class='subTitle'>Sudah diterima (aktif)</div>
	  <div class='spacer5'></div>";
echo "<table width='100%' cellspacing='1' cellpadding='1' border='0' bgcolor='#FF9900'>";
echo "
			<tr>
				<th align='center'><b>NO</b></th>
				<th align='center'><b>USER ID</b></th>
				<th align='center'><b>PASSWORD</b></th>
				<th align='center'><b>NAMA LENGKAP</b></th>
				<th align='center'><b>EMAIL</b></th>
				<th align='center'><b>NO TELEPON/HP</b></th>
				<th align='center'><b>JALAN</b></th>
				<th align='center'><b>KOTA</b></th>
				<th align='center'><b>NO IDENTITAS</b></th>
				<!-- <th align='center'><b>STATUS</b></th> -->
				<!-- <th align='center'><b>Keterangan</b></th> -->
				<th align='center'><b>AKSI</b></th>
			</tr>

						";

$sqlCek1 = "SELECT * FROM tbl_reg_user_notaris WHERE status='2' AND areapajak='$IdKK' ORDER BY id DESC";
// $result = mysqli_query($DBLink, $sqlCek1);
$bOK = $dbSpec->sqlQuery($sqlCek1, $result);
$jumlah = mysqli_num_rows($result);
if ($bOK) {
	if ($jumlah > 0) {
		$no = 0;
		while ($dataTampil1 = mysqli_fetch_array($result)) {
			$tag = array("2" => "Approve");
			$no++;
?>
			<tr>
				<td align='center'><?php echo $no ?></td>
				<td align='center'><?php echo addslashes($dataTampil1['userId']); ?></td>
				<td align='center'><?php echo md5($dataTampil1['password']) ?></td>
				<td align='center'><?php echo $dataTampil1['nm_lengkap'] ?></td>
				<td align='center'><?php echo $dataTampil1['email'] ?></td>
				<td align='center'><?php echo $dataTampil1['no_tlp'] ?></td>
				<td align='center'><?php echo $dataTampil1['almt_jalan'] ?></td>
				<td align='center'><?php echo $dataTampil1['almt_kota'] ?></td>
				<td align='center'><?php echo $dataTampil1['no_identitas'] ?></td>
				<!-- <td align='center'>".$tag[$dataTampil1['status']]."</td> -->
				<!-- <td align='center'>&nbsp;</td> -->

				<td align='center'>
					<a href="#" data-toggle="modal" data-target="#modal<?= $dataTampil1['id']; ?>"><img src='./image/icon/edit.png' height='15' width='15' alt='EDIT' title='EDIT'></img></a>
					<a href="main.php?param=<?php echo base64_encode("a=$a&m=$m&f=$func&dataId=" . $dataTampil1['id'] . "&nameUser=" . addslashes($dataTampil1['userId']) . "&pwdUser=" . $dataTampil1['password'] . "&email=" . $dataTampil1['email']) ?>"><img src='./image/icon/delete.png' height='15' width='15' alt='BLOKIR' title='BLOKIR'></img></a>
				</td>
			</tr>

			<!-- Modal -->
			<div class="modal fade" id="modal<?= $dataTampil1['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<!-- <h5 class="modal-title" id="exampleModalLabel"></h5> -->
							<strong style="font-size: large;"> Edit Data Notaris</strong>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form id="formEdit<?= $dataTampil1['id']; ?>" action="" method="POST">
								<input type="hidden" class="form-control" id="id" name="id" value="<?= $dataTampil1['id']; ?>" readonly>

								<div class="form-group row">
									<label for="username" class="col-sm-2 col-form-label">Username</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="username" name="username" value="<?= $dataTampil1['userId']; ?>" readonly>
									</div>
								</div>
								<div class="form-group row">
									<label for="namalk" class="col-sm-2 col-form-label">Nama Lengkap</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="namalk" name="namalk" value="<?= $dataTampil1['nm_lengkap']; ?>">
									</div>
								</div>
								<div class="form-group row">
									<label for="email" class="col-sm-2 col-form-label">E-mail</label>
									<div class="col-sm-10">
										<input type="email" class="form-control" id="email" name="email" value="<?= $dataTampil1['email']; ?>">
									</div>
								</div>
								<div class="form-group row">
									<label for="notelp" class="col-sm-2 col-form-label">No. Telp</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="notelp" name="notelp" value="<?= $dataTampil1['no_tlp']; ?>">
									</div>
								</div>
								<div class="form-group row">
									<label for="alamat" class="col-sm-2 col-form-label">Alamat</label>
									<div class="col-sm-10">
										<textarea class="form-control" id="alamat" name="alamat" rows="3"><?= $dataTampil1['almt_jalan']; ?></textarea>
										<!-- <input type="text" class="form-control" id="alamat" name="alamat" value="<?= $dataTampil1['almt_jalan']; ?>"> -->
									</div>
								</div>
								<div class="form-group row">
									<label for="kota" class="col-sm-2 col-form-label">Kota</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="kota" name="kota" value="<?= $dataTampil1['almt_kota']; ?>">
									</div>
								</div>
							</form>

						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<input type="submit" name="submit" value="Simpan Perubahan" class="btn btn-primary"></input>
							<button type="submit" class="btn btn-primary" onclick="submitForm(<?= $dataTampil1['id'] ?>)">Simpan</button>
						</div>
					</div>
				</div>
			</div>

			<script>
				function submitForm(id) {
					var form = $('#formEdit' + id);
					// console.log("masuk");
					$.ajax({
						type: 'POST',
						url: 'function/registrasi-notaris/action/save.php',
						data: form.serialize(),
						success: function(response) {
							console.log(response);
							var res = JSON.parse(response);
							if (res.status === 'success') {
								$('#modal' + id).modal('hide');
								alert('Data berhasil disimpan');
								location.reload(); // Jika Anda ingin me-refresh halaman setelah menyimpan data
							} else {
								alert('Gagal menyimpan perubahan');
							}
						},
						error: function() {
							alert('Gagal menyimpan perubahan');
						}
					});
				}
			</script>


<?php

		}
	} else {
		echo "<tr><td colspan=10 align='center'>Maaf, data belum ada</td></tr>";
	}
}

echo "</table></div>";


?>
<!-- Button trigger modal
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
	Launch demo modal
</button> -->