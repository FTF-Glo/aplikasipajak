<?php
$DIR = "PATDA-V1";
$modul = "hotel";

require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");

if (isset($_SESSION['npwpd']) && !empty($_SESSION['npwpd'])) $npwpd = $_SESSION['npwpd'];

$pajak = new Pajak();
$op = new ObjekPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $op->get_last_profil((isset($npwpd) ? $npwpd : ''), (isset($nop) ? $nop : ''));

$kecamatan = $pajak->get_list_kecamatan();
?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="inc/js/jquery.number.js"></script>
<script type="text/javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/op/func-op.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

<form class="cmxform" autocomplete="off" id="form-op" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/op/svc-op.php?param=<?php echo base64_encode($json->encode(array("a" => $op->_a, "m" => $op->_m))) ?>">
	<input type="hidden" name="url" value="main.php?<?php echo $_SERVER['QUERY_STRING'] ?>">
	<input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=" . $op->_a . "&m=" . $op->_m . "&f=" . $op->_f) ?>">
	<input type="hidden" name="function" id="function" value="save">
	<input type="hidden" name="PROFIL[CPM_ID]" id="CPM_ID" value="<?php echo $value['CPM_ID'] ?>">
	<input type="hidden" name="PROFIL[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">
	<table class="main" width="900">
		<tr>
			<td colspan="2" align="center" class="subtitle"><b>PROFIL PAJAK HOTEL</b></td>
		</tr>
		<?php if (!empty($npwpd)): ?>
			<tr>
				<td width="200">NPWPD <b class="isi">*</b></td>
				<td>:
					<input type="text" name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width: 200px;" value="<?php echo Pajak::formatNPWPD($value['CPM_NPWPD']) ?>" readonly>
					<?php
					if (empty($_SESSION['npwpd'])):
						$prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
					?>
						<input type="button" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
					<?php endif; ?>
				</td>
			</tr>
		<?php else: ?>
			<tr>
				<td width="200">NPWPD <b class="isi">*</b></td>
				<td>:
					<input type="hidden" id="TBLJNSPJK" value="HOTEL">
					<select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" style="width:250px;"></select>
					<label id="loading"></label>
				</td>
			</tr>
		<?php endif; ?>

		<tr>
			<td>Nama Wajib Pajak <b class="isi">*</b></td>
			<td>: <input type="text" name="PROFIL[CPM_NAMA_WP]" id="CPM_NAMA_WP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_WP'] ?>" readonly placeholder="Nama Wajib Pajak"></td>
		</tr>
		<tr valign="top">
			<td>Alamat Wajib Pajak <b class="isi">*</b></td>
			<td>: <textarea name="PROFIL[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" cols="80" rows="3" readonly placeholder="Alamat Wajib Pajak"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea></td>
		</tr>
		<tr valign="top">
			<td>Telepon</td>
			<td>: <input type="text" name="PROFIL[CPM_TELEPON_WP]" id="CPM_TELEPON_WP" style="width: 200px;" value="<?php echo $value['CPM_TELEPON_WP'] ?>" readonly placeholder="Telepon Wajib Pajak"></td>
		</tr>
		<tr>
			<td>Kecamatan Wajib Pajak <b class="isi">*</b></td>
			<td>: <input type="text" name="PROFIL[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" style="width: 200px;" value="<?php echo $value['CPM_KECAMATAN_WP'] ?>" readonly placeholder="Kecamatan Wajib Pajak">
				<?php
				if (
					!empty($npwpd) &&
					(
						empty($value['CPM_KECAMATAN_WP']) ||
						empty($value['CPM_KELURAHAN_WP'])
					)
				) :
					$prm = 'main.php?param=' .
						base64_encode('a=' . $a . '&m=mPatdaPelayananRegWP&mod=&f=fPatdaPelayananRegWp&id=' . $npwpd . '&s=1&i=1');
				?>
					<a href="<?php echo $prm ?>" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>Kelurahan Wajib Pajak <b class="isi">*</b></td>
			<td>: <input type="text" name="PROFIL[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" style="width: 200px;" value="<?php echo $value['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak">
			</td>
		</tr>

		<?php if (isset($npwpd)): ?>

			<?php if (isset($nop)): ?>
				<tr>
					<td colspan="2" align="center" class="subtitle"><b>DATA OBJEK PAJAK</b></td>
				</tr>
				<tr>
					<td>NOP <b class="isi">*</b></td>
					<td>:
						<input type="text" name="PROFIL[CPM_NOP]" id="CPM_NOP" style="width: 200px;" value="<?php echo $value['CPM_NOP'] ?>" readonly placeholder="by sistem">
						<label id="loading"></label>
					</td>
				</tr>
				<tr>
					<td>Nama Objek Pajak <b class="isi">*</b></td>
					<td>: <input type="text" name="PROFIL[CPM_NAMA_OP]" id="CPM_NAMA_OP" style="width: 200px;" value="<?php echo $value['CPM_NAMA_OP'] ?>" placeholder="Nama Objek Pajak"></td>
				</tr>
				<tr valign="top">
					<td>Alamat Objek Pajak <b class="isi">*</b></td>
					<td>: <textarea name="PROFIL[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" cols="80" rows="3" placeholder="Alamat Objek Pajak"><?php echo $value['CPM_ALAMAT_OP'] ?></textarea></td>
				</tr>
				<tr valign="top">
					<td>Telepon</td>
					<td>: <input type="text" name="PROFIL[CPM_TELEPON_OP]" id="CPM_TELEPON_OP" style="width: 200px;" value="<?php echo $value['CPM_TELEPON_OP'] ?>" placeholder="Telepon Objek Pajak"></td>
				</tr>
				<tr>
					<td>Kecamatan <b class="isi">*</b></td>
					<td>:
						<select name="PROFIL[CPM_KECAMATAN_OP]" id="CPM_KECAMATAN_OP" style="width: 200px;" data-kel="<?php echo $value['CPM_KELURAHAN_OP'] ?>">
							<option></option>
							<?php
							if (count($kecamatan) > 0) {
								foreach ($kecamatan as $kec) {
									echo '<option value="' . $kec->CPM_KEC_ID . '" ' . ($value['CPM_KECAMATAN_OP'] == $kec->CPM_KEC_ID ? 'selected' : '') . '>' . $kec->CPM_KECAMATAN . '</option>';
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Kelurahan <b class="isi">*</b></td>
					<td>:
						<select name="PROFIL[CPM_KELURAHAN_OP]" id="CPM_KELURAHAN_OP" style="width: 200px;">
							<option></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td>Rekening Pajak <b class="isi">*</b></td>
					<td>: <select name="PROFIL[CPM_REKENING]" id="CPM_REKENING">
							<?php
							foreach ($value['ARR_REKENING'] as $gol) {
								echo ($value['CPM_REKENING'] == $gol['kdrek']) ? "<option value='{$gol['kdrek']}' selected>{$gol['kdrek']} - {$gol['nmrek']}</option>" : "<option value='{$gol['kdrek']}'>{$gol['kdrek']} - {$gol['nmrek']}</option>";
							}
							?>
						</select>
						<input type="hidden" name="PROFIL[CPM_JENIS_HOTEL]" value="" />
					</td>
				</tr>
				<tr>
					<td>Longtitude <b class="isi"></b></td>
					<td>: <input type="text" name="PROFIL[longitude]" id="longitude" style="width: 200px;" value="<?php echo $value['longitude'] ?>" placeholder="Longtitude"></td>
				</tr>
				<tr>
					<td>Latitude <b class="isi"></b></td>
					<td>: <input type="text" name="PROFIL[latitude]" id="latitude" style="width: 200px;" value="<?php echo $value['latitude'] ?>" placeholder="Latitude"> (-)</td>
				</tr>

				<!-- <tr valign="top">
					<td>Jenis Pajak <b class="isi">*</b></td>
					<td>: 
						<select name="PROFIL[CPM_JENIS_HOTEL]" id="CPM_JENIS_HOTEL">
							<?php
							$arr_jenis = array('Hotel Bintang 1' => 'Hotel Bintang 1', 'Hotel Bintang 2' => 'Hotel Bintang 2', 'Hotel Bintang 3' => 'Hotel Bintang 3', 'Hotel Melati 1' => 'Hotel Melati 1', 'Hotel Melati 2' => 'Hotel Melati 2', 'Hotel Melati 3' => 'Hotel Melati 3');
							foreach ($arr_jenis as $k => $v) {
								$sel = $value['CPM_JENIS_HOTEL'] == $k ? ' selected' : '';
								echo "<option value='{$k}'{$sel}>{$v}</option>";
							}
							?>
						</select>
					</td>
				</tr> -->
				<tr class="button-area">
					<td align="center" colspan="2">
						<input type="reset" value="Reset">
						<?php if (!empty($nop)): ?>
							<input type="button" id="btn-submit" action="save" value="Simpan Perubahan">
							<input type="button" value="Tambah Objek Pajak" id="btn-addOp" onclick="javascript:addOp()">
							<?php $check_nop = 0; ?>
						<?php else: ?>
							<input type="button" id="btn-submit" action="save" value="Simpan Baru">
							<?php $check_nop = 1; ?>
						<?php endif; ?>
						<input type="hidden" name="check_nop" id="check_nop" value="<?php echo $check_nop ?>">
					</td>
				</tr>

			<?php else: ?>
				<tr class="button-area">
					<td align="center" colspan="2">
						<input type="button" value="Tambah Objek Pajak" id="btn-addOp" onclick="javascript:addOp()">
					</td>
				</tr>
			<?php endif; ?>

		<?php endif; ?>


		<tr>
			<td colspan="2">
				<?php
				echo $op->grid_table(isset($npwpd) ? $npwpd : '');
				?>
			</td>
		</tr>
	</table>


</form>