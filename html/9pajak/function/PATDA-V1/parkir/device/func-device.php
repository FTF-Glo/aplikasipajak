<?php
$DIR = "PATDA-V1";
$modul = "parkir";

require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/op/class-op.php");

if (isset($_SESSION['npwpd']) && !empty($_SESSION['npwpd'])) $npwpd = $_SESSION['npwpd'];

$pajak = new Pajak();
$op = new ObjekPajak();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$value = $op->get_last_profil((isset($npwpd) ? $npwpd : ''), (isset($nop) ? $nop : ''));

$list_nop = isset($npwpd) ? $op->get_list_nop($npwpd) : array();

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">

<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="inc/select2/js/select2.full.min.js"></script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/device/func-device.js"></script>
<script type="text/javascript" src="function/<?php echo $DIR ?>/op.js"></script>

<form class="cmxform" autocomplete="off" id="form-device" method="post" action="function/<?php echo "{$DIR}/{$modul}"; ?>/device/svc-device.php?param=<?php echo base64_encode($json->encode(array("a" => $op->_a, "m" => $op->_m))) ?>">
	<input type="hidden" name="url" value="main.php?<?php echo $_SERVER['QUERY_STRING'] ?>">
	<input type="hidden" name="param" id="param" value="<?php echo  base64_encode("a=" . $op->_a . "&m=" . $op->_m . "&f=" . $op->_f) ?>">
	<input type="hidden" name="function" id="function">
	<input type="hidden" name="PROFIL[CPM_ID]" id="CPM_ID" value="<?php echo $value['CPM_ID'] ?>">
	<input type="hidden" name="PROFIL[CPM_AUTHOR]" value="<?php echo $data->uname; ?>">

	<div class="container lm-container">
		<div class="row">
			<div id="subMenu" align="center" class="col-md-12 subtitle lm-title">
				<b>PENGATURAN DEVICE</b>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<?php if (!empty($npwpd)): ?>
						<label>NPWPD <b class="isi">*</b></label>
						<input type="text" name="PROFIL[CPM_NPWPD]" id="CPM_NPWPD" class="form-control" value="<?php echo Pajak::formatNPWPD($value['CPM_NPWPD']) ?>" readonly>
						<?php
						if (empty($_SESSION['npwpd'])):
							$prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $f);
						?>
							<input type="button" class="btn btn-primary lm-btn" style="margin-top: 10px" value="Cari NPWPD Lainnya" onclick="location.href='<?php echo $prm ?>'">
						<?php endif; ?>
					<?php else: ?>
						<label>NPWPD <b class="isi">*</b></label>
						<input type="hidden" id="TBLJNSPJK" value="RESTORAN">
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
					<textarea name="PROFIL[CPM_ALAMAT_WP]" id="CPM_ALAMAT_WP" class="form-control" rows="3" style="min-width: 100%" readonly placeholder="Alamat Wajib Pajak"><?php echo $value['CPM_ALAMAT_WP'] ?></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>NOP <b class="isi">*</b></label>
					<?php if (empty($value['pajak']['CPM_ID'])): ?>
						<select name="PROFIL[CPM_NOP]" id="CPM_NOP" class="form-control" onchange="javascript:selectOP()">
							<?php
							if (count($list_nop) == 0) echo "<option value=''>NOP Tidak tersedia</option>";
							else echo (empty($nop)) ? "<option value='' selected disabled>Pilih NOP</option>" : "";

							foreach ($list_nop as $list) {
								echo "<option value='{$list['CPM_NOP']}' " . ($nop == $list['CPM_NOP'] ? 'selected' : '') . ">{$list['CPM_NOP']}</option>";
							}

							?>
						</select>

						<?php
						if (count($list_nop) == 0 && !empty($npwpd)):
							$ff = str_replace('Tapbox', 'Lapor', $f);
							$addOp = substr($ff, 0, strlen($ff) - 1) . 'OP' . substr($ff, -1);
							$prm = 'main.php?param=' . base64_encode('a=' . $a . '&m=' . $m . '&f=' . $addOp . '&npwpd=' . $npwpd . '&nop=') . '#CPM_TELEPON_WP';
						?>
							<input type="button" class="btn btn-primary lm-btn" style="margin-top: 10px" value="Tambah NOP" onclick="location.href='<?php echo $prm ?>'">
						<?php endif; ?>

					<?php else: ?>
						<input type="text" name="PROFIL[CPM_NOP]" id="CPM_NOP" class="form-control" value="<?php echo $value['CPM_NOP'] ?>" readonly>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Nama Objek Pajak <b class="isi">*</b></label>
					<input type="text" name="PROFIL[CPM_NAMA_OP]" id="CPM_NAMA_OP" class="form-control" value="<?php echo $value['CPM_NAMA_OP'] ?>" readonly placeholder="Nama Objek Pajak">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Alamat Objek Pajak <b class="isi">*</b></label>
					<textarea name="PROFIL[CPM_ALAMAT_OP]" id="CPM_ALAMAT_OP" class="form-control" rows="3" style="min-width: 100%" readonly placeholder="Alamat Objek Pajak"><?php echo $value['CPM_ALAMAT_OP'] ?></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Device ID<b class="isi">*</b></label>
					<select class="form-control" multiple="multiple" name="PROFIL[CPM_DEVICE_ID][]" id="CPM_DEVICE_ID">
						<?php
						$deviceId = explode(';', $value['CPM_DEVICE_ID']);
						foreach ($deviceId as $d) {
							echo empty($d) ? '' : '<option selected="selected">' . $d . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="row" class="button-area">
			<div class="col-md-12" align="center">
				<input type="reset" value="Reset" class="btn btn-secondary lm-btn">
				<input type="button" id="btn-submit" class="btn btn-primary lm-btn" action="update_device" value="Simpan Perubahan">
			</div>
		</div>
	</div>
</form>