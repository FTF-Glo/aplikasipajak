<style>
	.tabel-pemekaran {
		/* border: 1px solid #E78F08; */
		margin-top: 10px;
		
	}

	.tabel-pemekaran td {
		height: 30px;
		padding: 5px 5px 5px 10px;
		width: 50%;
	}

	.tabel-pemekaran div {
		width: 150px;
		float: left;
		padding-top: 3px;
	}

	.tabel-pemekaran input[type=text] {
		width: 50px;
		height: 25px;
	}

	.tabel-pemekaran select {
		width: 200px;
		height: 25px;
	}

	.head {
		background: #333;
		color: #fff;
		font-weight: bold;
		font-size: 15px;
		text-align: center;
	}

	.foot {
		background: #333;
		text-align: right;
	}

	.tabel-pemekaran select,
	tabel-pemekaran input {
		font-family: Calibri, Arial, Verdana;
		font-size: 12px;
	}

	.overlay {
		position: fixed;
		width: 100%;
		height: 100%;
		z-index: 1000;
		left: 0;
		display: none;
		background: #666;
		opacity: 0.6;
		text-align: center;
	}
</style>
<script language="javascript" src="jquery-1.9.1.min.js"></script>
<script language="javascript" src="jquery.mambo.min.js"></script>
<script>
	<?php echo  $arrKec . $arrKel ?>

	function loadKecamatan() {
		$('#kec_lama').find('option').remove();
		$('#kec_baru').find('option').remove();
		$('#kec_lama').append("<option value='0'>-- PILIH KECAMATAN --</option>")
		$('#kec_baru').append("<option value='0'>-- PILIH KECAMATAN --</option>")
		$.each(arrKec, function(key, value) {
			$('#kec_lama').append("<option value='" + value['kode'] + "'>" + value['nama'] + "</option>")
			$('#kec_baru').append("<option value='" + value['kode'] + "'>" + value['nama'] + "</option>")
		});
	}

	function loadKelurahan(id) {
		var kec = $('#kec_' + id).val();
		$('#kel_' + id).find('option').remove();
		$('#kel_' + id).append("<option value='0'>-- PILIH KELURAHAN --</option>");

		var kelurahan = $.grep(arrKel, function(v) {
			return v.kd_kec == kec;
		});

		if (id == 'baru' && $('#jenis:checked').val() == '1') {
			kelurahan = $.grep(arrKel, function(v) {
				return v.kd_kec == kec && v.jumlah == 0;
			})
		}

		$.each(kelurahan, function(key, value) {
			$('#kel_' + id).append("<option value='" + value['kode'] + "'>" + value['nama'] + "</option>")
		});
	}

	function changeRadio() {
		var selected = $('#jenis:checked').val();
		$('#kel_lama').prop('disabled', true);
		$('#kel_lama').css("background-color", "#ccc");
		$('#kel_baru').prop('disabled', true);
		$('#kel_baru').css("background-color", "#ccc");
		$('#blok_awal_lama').prop('disabled', true);
		$('#blok_awal_lama').css("background-color", "#ccc");
		$('#blok_akhir_lama').prop('disabled', true);
		$('#blok_akhir_lama').css("background-color", "#ccc");
		$('#blok_baru').prop('disabled', true);
		$('#blok_baru').css("background-color", "#ccc");
		$('#urut_awal_lama').prop('disabled', true);
		$('#urut_awal_lama').css("background-color", "#ccc");
		$('#urut_akhir_lama').prop('disabled', true);
		$('#urut_akhir_lama').css("background-color", "#ccc");
		if (selected == 1) {
			$('#kel_lama').prop('disabled', false);
			$('#kel_lama').css("background-color", "#fff");
			$('#kel_baru').prop('disabled', false);
			$('#kel_baru').css("background-color", "#fff");
		} else if (selected == 2) {
			$('#kel_lama').prop('disabled', false);
			$('#kel_lama').css("background-color", "#fff");
			$('#kel_baru').prop('disabled', false);
			$('#kel_baru').css("background-color", "#fff");
			$('#blok_awal_lama').prop('disabled', false);
			$('#blok_awal_lama').css("background-color", "#fff");
			$('#blok_akhir_lama').prop('disabled', false);
			$('#blok_akhir_lama').css("background-color", "#fff");
		} else if (selected == 3) {
			$('#kel_lama').prop('disabled', false);
			$('#kel_lama').css("background-color", "#fff");
			$('#kel_baru').prop('disabled', false);
			$('#kel_baru').css("background-color", "#fff");
			$('#blok_awal_lama').prop('disabled', false);
			$('#blok_awal_lama').css("background-color", "#fff");
			$('#blok_akhir_lama').prop('disabled', false);
			$('#blok_akhir_lama').css("background-color", "#fff");
			$('#blok_baru').prop('disabled', false);
			$('#blok_baru').css("background-color", "#fff");
		} else if (selected == 4) {
			$('#kel_lama').prop('disabled', false);
			$('#kel_lama').css("background-color", "#fff");
			$('#kel_baru').prop('disabled', false);
			$('#kel_baru').css("background-color", "#fff");
			$('#blok_awal_lama').prop('disabled', false);
			$('#blok_awal_lama').css("background-color", "#fff");
			$('#blok_baru').prop('disabled', false);
			$('#blok_baru').css("background-color", "#fff");
			$('#urut_awal_lama').prop('disabled', false);
			$('#urut_awal_lama').css("background-color", "#fff");
			$('#urut_akhir_lama').prop('disabled', false);
			$('#urut_akhir_lama').css("background-color", "#fff");
		}
		loadKelurahan('lama');
		loadKelurahan('baru');
	}

	function showOverlay() {
		var topOverlay = $('#menu').height() + $('#header').height();
		$(".overlay").css('top', topOverlay + 'px');
		$(".overlay").css('display', 'block');
	}

	function hideOverlay() {
		$(".overlay").css('display', 'none');
	}

	function Proses() {
		if (!$('#kec_lama').prop('disabled') && (!$('#kec_lama').val() || $('#kec_lama').val() == '0'))
			alert('Pilih Kecamatan Lama');
		else if (!$('#kec_baru').prop('disabled') && (!$('#kec_baru').val() || $('#kec_baru').val() == '0'))
			alert('Pilih Kecamatan Baru');
		else if (!$('#kel_lama').prop('disabled') && (!$('#kel_lama').val() || $('#kel_lama').val() == '0'))
			alert('Pilih Kelurahan Lama');
		else if (!$('#kel_baru').prop('disabled') && (!$('#kel_baru').val() || $('#kel_baru').val() == '0'))
			alert('Pilih Kelurahan Baru');
		else if (!$('#blok_awal_lama').prop('disabled') && !$('#blok_awal_lama').val())
			alert('Isi Blok Lama');
		else if (!$('#blok_baru').prop('disabled') && !$('#blok_baru').val())
			alert('Isi Blok Baru');
		else if (!$('#urut_awal_lama').prop('disabled') && !$('#urut_awal_lama').val())
			alert('Isi Urut Lama');
		else sendProcessCount();
	}

	function sendProcessCount() {
		var r = confirm("Anda akan merubah data ?");
		if (r == true) sendProcess();
	}


	function sendProcess() {
		Ext.Ajax.request({
			url: 'function/PBB/pemekaran/pemekaran.php',
			params: {
				jenis: $('#jenis:checked').val(),
				kec_lama: $('#kec_lama').val(),
				kec_baru: $('#kec_lama').val(),
				kel_lama: $('#kel_lama').val(),
				kel_baru: $('#kel_baru').val(),
				blok_baru: $('#blok_baru').val(),
				kel_baru: $('#kel_baru').val(),
				blok_awal_lama: $('#blok_awal_lama').val(),
				blok_akhir_lama: $('#blok_akhir_lama').val(),
				urut_awal_lama: $('#urut_awal_lama').val(),
				urut_akhir_lama: $('#urut_akhir_lama').val(),
				author: '<?= $uname ?>'
			},
			timeout: 80000,
			success: function(res) {
				window.location = 'main.php?param=<?php echo $param ?>&idexec=' + (res.responseText)
			},
			failure: function() {
				alert('Connection Error !!');
			}
		});
	}
</script>

<div class="col-md-12">
	<table width='100%' cellpadding='0' cellspacing='0' class='tabel-pemekaran'>
		<tr>
			<td colspan='2' class='ui-widget-header'>PEMEKARAN WILAYAH <br> <?php echo  $nm_kota ?> / <?php echo  $nm_prov ?> </td>
		</tr>
		<tr>
			<td><input type="radio" value="1" name="jenis" id="jenis" onclick="changeRadio()" checked="checked" /> Pindah kelurahan keseluruhan ke kecamatan</td>
			<td><input type="radio" value="3" name="jenis" id="jenis" onclick="changeRadio()" /> Gabung Beberapa Blok</td>
		</tr>
		<tr>
			<td><input type="radio" value="2" name="jenis" id="jenis" onclick="changeRadio()" /> Pindah blok keseluruhan ke kelurahan lain</td>
			<td><input type="radio" value="4" name="jenis" id="jenis" onclick="changeRadio()" /> Pindah NOP Ke Blok Lain</td>
		</tr>
		<tr>
			<td class='ui-widget-header'>WILAYAH LAMA</td>
			<td class='ui-widget-header'>WILAYAH BARU</td>
		</tr>
		<tr>
			<td>
				<div>Kecamatan</div> <select name="kec_lama" id="kec_lama" onchange="loadKelurahan('lama')"></select>
			</td>
			<td>
				<div>Kecamatan</div> <select name="kec_lama" id="kec_baru" onchange="loadKelurahan('baru')"></select>
			</td>
		</tr>
		<tr>
			<td>
				<div>Kelurahan</div> <select name="kel_lama" id="kel_lama"></select>
			</td>
			<td>
				<div>Kelurahan</div> <select name="kel_baru" id="kel_baru"></select>
			</td>
		</tr>
		<tr>
			<td>
				<div>Blok Awal / Blok Akhir</div>
				<input type="text" name="blok_awal_lama" id="blok_awal_lama" /> s/d <input type="text" name="blok_akhir_lama" id="blok_akhir_lama" />
			</td>
			<td>
				<div>Blok</div> <input type="text" name="blok_baru" id="blok_baru" />
			</td>
		</tr>
		<tr>
			<td>
				<div>Urut Awal / Urut Akhir</div>
				<input type="text" name="urut_awal_lama" id="urut_awal_lama" /> s/d <input type="text" name="urut_akhir_lama" id="urut_akhir_lama" />
			</td>
			<td></td>
		</tr>
		<tr>
			<td colspan="2" class="ui-widget-header"><input type="button" value="Proses" onclick="Proses()" /></td>
		</tr>
	</table>
	<div class="overlay" onclick="hideOverlay()">
		<img src="image/large-loading.gif" style="margin-top:15%" /><br />
		PROGRESS
	</div>
</div>
<script>
	loadKecamatan();
	changeRadio();
</script>