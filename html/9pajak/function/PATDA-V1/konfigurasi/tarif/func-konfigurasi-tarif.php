<?php
$DIR = "PATDA-V1";
$modul = "konfigurasi/tarif";
require_once("inc/payment/json.php");
require_once("function/{$DIR}/class-pajak.php");

$pajak = new Pajak();
$rekening = $pajak->jenis_rekening();
// print_r($rekening); exit;

?>
<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery.number.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>

<form class="cmxform" id="form-conf" method="post">

	<div class="container lm-container">
		<div class="row">
			<div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
				<b>Tambah Rekening</b>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Jenis Pajak <b class="isi">*</b></label>
					<select name="REK[nmheader3]" id="nmheader3" class="form-control">
						<?php
						if (count($rekening) > 0) {

							echo '<option value="">Pilih jenis rekening</option>';
							foreach ($rekening as $rekening) {
								echo $rekening;
							}
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>Kode Rekening <b class="isi">*</b></label>
					<input type="text" name="REK[kdrek]" id="kdrek" readonly="true" class="form-control"> <input type="hidden" name="REK[nama]" id="nama" readonly="true" style="width: 200px;">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>Nama Rekening <b class="isi">*</b></label>
					<input type="text" name="REK[nmrek]" id="nmrek" class="form-control">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Tarif Pajak <b class="isi">*</b></label>
					<input type="number" name="REK[tarif1]" id="tarif1" class="form-control">
				</div>
			</div>
		</div>
		<?php
		if (isset($_REQUEST['tarif']) && $_REQUEST['tarif'] != 'reklame') {
		?>
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label>Harga Dasar</label>
						<input type="number" name="REK[tarif2]" id="tarif2" class="form-control">
					</div>
				</div>
			</div>
		<?php
		} elseif (isset($_REQUEST['tarif']) && $_REQUEST['tarif'] == 'reklame') {
		?>
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label>Tarif Kawasan (Reklame)</label>
						<input type="text" name="REK[tarif3]" id="tarif3" class="form-control">
						<input type="hidden" name="masa[1]" id="masa-1">
						<input type="hidden" name="masa[2]" id="masa-2">
						<input type="hidden" name="masa[3]" id="masa-3">
						<input type="hidden" name="masa[4]" id="masa-4">
						<input type="hidden" name="masa[5]" id="masa-5">
						<input type="hidden" name="masa[6]" id="masa-6">
					</div>
				</div>
			</div>
		<?php
		}
		?>
		<div class="row button-area">
			<div class="col-md-12" align="center">
				<input type="reset" value="Reset">
				<input type="submit" class="btn-submit" action="save" value="Simpan">
				<input type="hidden" name="function" id="function" value="save_tarif">
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
	$(document).ready(function() {

		$('#nmheader3').change(function() {
			$.ajax({
				type: "POST",
				dataType: 'json',
				url: "function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php",
				data: {
					'function': 'get_no_rek',
					'CPM_KD_ID': $(this).val()
				},
				async: false,
				success: function(data) {
					// console.log(data.nama);
					// $('#kelurahan-{$this->_i}').html('<option value=\'\'>Semua</option>'+html);
					$('#kdrek').val(data.nilai);
					$('#nama').val(data.nama);
				}
			});
		});

		$('#form-conf').submit(function(e) {
			e.preventDefault();
			if ($('#kdrek').val() != '' && $('#nmrek').val() != '' && $('#nmheader3').val() != '' && $('#tarif1').val() != '') {
				$.post('function/<?php echo "{$DIR}/{$modul}"; ?>/svc-konfigurasi-tarif-new.php', $(this).serialize(), function(data) {

				}, 'json');
				alert("data tersimpan");
				window.location = 'main.php?param=<?php echo base64_encode("a={$_REQUEST['a']}&m={$_REQUEST['m']}&i='2'") ?>';
			} else {
				alert('Isian dengan tanda bintang harus diisi!');
			}
		})
	});
</script>