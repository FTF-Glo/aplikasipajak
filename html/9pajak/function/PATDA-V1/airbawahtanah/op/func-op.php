<?php
$DIR = "PATDA-V1";
$modul = "airbawahtanah";

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
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/op/func-op.js?=v.0.2.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js?=v.0.2.js"></script>

<form class="cmxform" autocomplete="off" id="form-op" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/op/svc-op.php?param=<?php echo base64_encode($json->encode(array("a" => $op->_a, "m" => $op->_m))) ?>">
	<input type="hidden" name="url" value="main.php?<?php echo $_SERVER['QUERY_STRING'] ?>">
	<input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=" . $op->_a . "&m=" . $op->_m . "&f=" . $op->_f) ?>">
	<input type="hidden" name="function" id="function" value="save">
	<input type="hidden" name="PROFIL[CPM_ID]" id="CPM_ID" value="<?php echo $value['CPM_ID'] ?>">
	<input type="hidden" name="PROFIL[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">

	<div class="container lm-container">
		<div class="row">
			<div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
				<b>PROFIL PAJAK AIR BAWAH TANAH</b>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>NPWPD <b class="isi">*</b></label>
					<?php if (!empty($npwpd)) : ?>
						<input type="text" name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" value="<?php echo Pajak::formatNPWPD($value['CPM_NPWPD']) ?>" readonly>
						<?php
						if (empty($_SESSION['npwpd'])) :
							$prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
						?>
							<input type="button" class="btn btn-info lm-btn" style="margin-top: 10px" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
						<?php endif; ?>
					<?php else : ?>
						<input type="hidden" id="TBLJNSPJK" value="AIRBAWAHTANAH">
						<select name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" style="width: 90%"></select>
						<label id="loading"></label>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Nama Wajib Pajak <b class="isi">*</b></label>
					<input type="text" name="PROFIL[CPM_NAMA_WP]" id="CPM_NAMA_WP" class="form-control" value="<?php echo $value['CPM_NAMA_WP'] ?>" readonly placeholder="Nama Wajib Pajak">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Alamat Wajib Pajak <b class="isi">*</b></label>
					<textarea name="PROFIL[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" style="min-width: 100%" rows="3" readonly placeholder="Alamat Wajib Pajak"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label>Telepon</label>
					<input type="text" name="PROFIL[CPM_TELEPON_WP]" id="CPM_TELEPON_WP" class="form-control" value="<?php echo $value['CPM_TELEPON_WP'] ?>" readonly placeholder="Telepon Wajib Pajak">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label>Kecamatan Wajib Pajak <b class="isi">*</b></label>
					<input type="text" name="PROFIL[CPM_KECAMATAN_WP]" id="CPM_KECAMATAN_WP" class="form-control" value="<?php echo $value['CPM_KECAMATAN_WP'] ?>" readonly placeholder="Kecamatan Wajib Pajak">
					<?php
					if (
						!empty($npwpd) &&
						(empty($value['CPM_KECAMATAN_WP']) ||
							empty($value['CPM_KELURAHAN_WP'])
						)
					) :
						$prm = 'main.php?param=' .
							base64_encode('a=' . $a . '&m=mPatdaPelayananRegWP&mod=&f=fPatdaPelayananRegWp&id=' . $npwpd . '&s=1&i=1');
					?>
						<a href="<?php echo $prm ?>" class="btn btn-info lm-btn" style="margin-top: 10px" target="_blank" title="setelah data WP diubah, refresh halaman ini (F5)">Ubah data WP</a>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label>Kelurahan Wajib Pajak <b class="isi">*</b></label>
					<input type="text" name="PROFIL[CPM_KELURAHAN_WP]" id="CPM_KELURAHAN_WP" class="form-control" value="<?php echo $value['CPM_KELURAHAN_WP'] ?>" readonly placeholder="Kelurahan Wajib Pajak">
				</div>
			</div>
		</div>

		<?php if (isset($npwpd)) : ?>
			<?php if (isset($nop)) : ?>
				<div class="row">
					<div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
						<b>DATA OBJEK PAJAK</b>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>NOP <b class="isi">*</b></label>
							<input type="text" name="PROFIL[CPM_NOP]" id="CPM_NOP" class="form-control" value="<?php echo $value['CPM_NOP'] ?>" readonly placeholder="by sistem">
							<label id="loading"></label>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Nama Objek Pajak <b class="isi">*</b></label>
							<input type="text" name="PROFIL[CPM_NAMA_OP]" id="CPM_NAMA_OP" class="form-control" value="<?php echo $value['CPM_NAMA_OP'] ?>" placeholder="Nama Objek Pajak">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Alamat Objek Pajak <b class="isi">*</b></label>
							<textarea name="PROFIL[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" class="form-control" style="min-width: 100%" rows="3" placeholder="Alamat Objek Pajak"><?php echo $value['CPM_ALAMAT_OP'] ?></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Telepon</label>
							<input type="text" name="PROFIL[CPM_TELEPON_OP]" id="CPM_TELEPON_OP" class="form-control" value="<?php echo $value['CPM_TELEPON_OP'] ?>" placeholder="Telepon Objek Pajak">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Kecamatan <b class="isi">*</b></label>
							<select name="PROFIL[CPM_KECAMATAN_OP]" id="CPM_KECAMATAN_OP" class="form-control" style="width:100%" data-kel="<?php echo $value['CPM_KELURAHAN_OP'] ?>">
								<option></option>
								<?php
								if (count($kecamatan) > 0) {
									foreach ($kecamatan as $kec) {
										echo '<option value="' . $kec->CPM_KEC_ID . '" ' . ($value['CPM_KECAMATAN_OP'] == $kec->CPM_KEC_ID ? 'selected' : '') . '>' . $kec->CPM_KECAMATAN . '</option>';
									}
								}
								?>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Kelurahan <b class="isi">*</b></label>
							<select name="PROFIL[CPM_KELURAHAN_OP]" id="CPM_KELURAHAN_OP" class="form-control" style="width:100%">
								<option></option>
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Rekening Pajak <b class="isi">*</b></label>
							<select class="form-control" name="PROFIL[CPM_REKENING]" id="CPM_REKENING">
								<?php
								foreach ($value['ARR_REKENING'] as $gol) {
									echo ($value['CPM_REKENING'] == $gol['kdrek']) ? "<option value='{$gol['kdrek']}' selected>{$gol['kdrek']} - {$gol['nmrek']}</option>" : "<option value='{$gol['kdrek']}'>{$gol['kdrek']} - {$gol['nmrek']}</option>";
								}
								?>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Peruntukan air <b class="isi">*</b></label>
							<?php if (!empty($value['CPM_PERUNTUKAN_AIR'])) { ?>
								<select class="form-control" name="PAJAK[CPM_PERUNTUKAN]" id="CPM_PERUNTUKAN">
									<option value="Sosial / Non Niaga" <?php echo ($value['CPM_PERUNTUKAN_AIR'] == "Sosial / Non Niaga") ? "selected" : ""; ?>>Sosial / Non Niaga</option>
									<option value="Niaga Kecil" <?php echo ($value['CPM_PERUNTUKAN_AIR'] == "Niaga Kecil") ? "selected" : ""; ?>>Niaga Kecil</option>
									<option value="Industri Kecil dan Menengah" <?php echo ($value['CPM_PERUNTUKAN_AIR'] == "Industri Kecil dan Menengah") ? "selected" : ""; ?>>Industri Kecil dan Menengah</option>
									<option value="Niaga Besar" <?php echo ($value['CPM_PERUNTUKAN_AIR'] == "Niaga Besar") ? "selected" : ""; ?>>Niaga Besar</option>
									<option value="Industri Besar" <?php echo ($value['CPM_PERUNTUKAN_AIR'] == "Industri Besar") ? "selected" : ""; ?>>Industri Besar</option>
									<option value="Air Minum Dalam Kemasan" <?php echo ($value['CPM_PERUNTUKAN_AIR'] == "Air Minum Dalam Kemasan") ? "selected" : ""; ?>>Air Minum Dalam Kemasan</option>
								</select>
							<?php } else { ?>
								<select class="form-control" name="PAJAK[CPM_PERUNTUKAN]" id="CPM_PERUNTUKAN">
									<option value="Sosial / Non Niaga">Sosial / Non Niaga</option>
									<option value="Niaga Kecil">Niaga Kecil</option>
									<option value="Industri Kecil dan Menengah">Industri Kecil dan Menengah</option>
									<option value="Niaga Besar">Niaga Besar</option>
									<option value="Industri Besar">Industri Besar</option>
									<option value="Air Minum Dalam Kemasan">Air Minum Dalam Kemasan</option>
								</select>
							<?php } ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Penggunaan Tappingbox</label>
							<div style="display: block">
								<label for="tapping1" style="font-weight: normal;"><input type="radio" name="PROFIL[tapping]" id="tapping1" value="1" checked> Menggunakan Tappingbox &nbsp;</label>
								<label for="tapping2" style="font-weight: normal;"><input type="radio" name="PROFIL[tapping]" id="tapping2" value="0"> Tidak Menggunakan Tappingbox</label>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Longtitude <b class="isi"></b></label>
							<input type="text" name="PROFIL[longitude]" id="longitude" class="form-control" value="<?php echo $value['longitude'] ?>" placeholder="Longtitude">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Latitude <b class="isi"></b></label>
							<div>
								<input type="text" name="PROFIL[latitude]" id="latitude" class="form-control" style="width: 90%; display: inline-block" value="<?php echo $value['latitude'] ?>" placeholder="Latitude">
								<span style="display: inline">(-)</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row button-area" style="margin: 0 0 30px 0">
					<div class="col-md-12" align="center">
						<input type="reset" value="Reset">
						<?php if (!empty($nop)) : ?>
							<input type="submit" id="btn-submit" action="save" value="Simpan Perubahan">
							<input type="button" value="Tambah Objek Pajak" id="btn-addOp" onclick="javascript:addOp()">
							<?php $check_nop = 0; ?>
						<?php else : ?>
							<input type="submit" id="btn-submit" name="btn-submit" action="save" value="Simpan Baru">
							<?php $check_nop = 1; ?>
						<?php endif; ?>
						<input type="hidden" name="check_nop" id="check_nop" value="<?php echo $check_nop ?>">
					</div>
				</div>
			<?php else : ?>
				<div class="row button-area" style="margin: 0 0 30px 0">
					<div class="col-md-12" align="center">
						<input type="button" value="Tambah Objek Pajak" id="btn-addOp" onclick="javascript:addOp()">
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php echo $op->grid_table(isset($npwpd) ? $npwpd : ''); ?>
	</div>


</form>