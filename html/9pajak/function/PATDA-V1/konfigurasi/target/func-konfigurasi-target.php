<?php
$DIR = "PATDA-V1";
$modul = "konfigurasi/target";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");
require_once("function/{$DIR}/{$modul}/class-konfigurasi-target.php");

$pajak = new Pajak();
$konfigurasiTarget = new KonfigurasiTarget();
$list_jenis_pajak = $konfigurasiTarget->arr_pajak;

?>
<!-- <link href="inc/<?php echo $DIR; ?>/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/> -->
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>

<form class="cmxform" id="form-conf" method="post" autocomplete="off">

	<div class="container lm-container">
		<div class="row">
			<div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
				<b>Tambah Target Pajak</b>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Jenis Pajak <b class="isi">*</b></label>
					<select name="TARGET[JENIS_PAJAK]" id="JENIS_PAJAK" class="form-control" required="required">
						<?php
						if (count($list_jenis_pajak)) {
							echo '<option value="">Pilih jenis pajak</option>';
							foreach ($list_jenis_pajak as $id => $pajak) {
								echo '<option value="' . $id . '">' . $pajak . '</option>';
							}
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Tahun berlaku <b class="isi">*</b></label>
					<select name="TARGET[TAHUN_BERLAKU]" id="TAHUN_BERLAKU" class="form-control" required="required">
						<?php
						foreach (range(date('Y', strtotime('-5 years')), date('Y', strtotime('+5 years'))) as $tahun) {
							echo '<option value="' . $tahun . '"' . ($tahun == date('Y') ? ' selected' : '') . '>' . $tahun . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Jumlah Target <b class="isi">*</b></label>
					<input type="text" name="TARGET[JUMLAH]" id="JUMLAH" class="form-control" required="required">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Aktif</label>
					<div class="alert alert-info">
						<input type="checkbox" name="TARGET[AKTIF]" id="CHECK_AKTIF" checked="checked"> <label for="CHECK_AKTIF" style="margin: 0">Ya</label>
					</div>
				</div>
			</div>
		</div>
		<div class="row button-area">
			<div class="col-md-12" align="center">
				<input type="reset" value="Reset">
				<input type="submit" class="btn-submit" action="save" value="Simpan">
				<input type="hidden" name="function" id="function" value="save_target">
			</div>
		</div>
	</div>
</form>
<input type="hidden" id="formsubmiturl" value="function/<?php echo "{$DIR}/{$modul}"; ?>/svc-konfigurasi-target.php">
<script>
	$(function() {

		$('input[type="checkbox"]#CHECK_AKTIF').on('change', function() {
			var v = $(this);
			var label = $('label[for="' + v.attr('id') + '"]');
			if (v.prop('checked')) {
				label.html('Ya');
			} else {
				label.html('Tidak');
			}
		});

		$('[name="TARGET[JUMLAH]"]').autoNumeric('init');
		$('[name="TARGET[JUMLAH]"]').autoNumeric('set', 0);

		var form = $('#form-conf');
		form.on('submit', function(e) {
			e.preventDefault();

			$.post($('#formsubmiturl').val(), form.serialize(), function(res) {
				var title = res.type == 'success' ? 'Berhasil' : 'Gagal';
				var notice = new PNotify({
					title: title,
					text: res.message,
					type: res.type,
					nonblock: {
						nonblock: true
					}
				});
				notice.get().click(function() {
					notice.remove();
				});
				if (res.type == 'success') {
					$('[name="TARGET[JENIS_PAJAK]"]').prop('selectedIndex', 0);
					$('[name="TARGET[JUMLAH]"]').autoNumeric('set', 0);
				}
			}, 'json');

		});
	})
</script>