<?php
// prevent direct access
if (!isset($data)) {
	return;
}

$uid = $data->uid;

// get module
$bOK 		= $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig 	= $User->GetAppConfig($application);
$arConfig 	= $User->GetModuleConfig($module);
$params 	= "a=" . $application . "&m=" . $module;
$startLink0	= "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_input_jadwal']) . "\">";
$startLink1	= "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_kejaksaan']) . "\">";
$endLink 	= "</a>";

//prevent access to not accessible module
if (!$bOK) {
	return false;
}


if (!isset($opt)) {
?>
	<link href="view/PBB/monitoring/monitoring.css?v0001" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>


	<script>
		$(function() {
			$("#tgl-sp1-1").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp2-1").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp1-2").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp2-2").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp1-3").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp2-3").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp1-4").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl-sp2-4").datepicker({
				dateFormat: "yy-mm-dd"
			});
		});

		function onSubmit(sts) {
			var sp = $("#src-sp-" + sts).val();
			var nkc = $("#kecamatan-" + sts + " option:selected").text();
			var tgl1 = $("#tgl-sp1-" + sts).val();
			var tgl2 = $("#tgl-sp2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();
			var jmlBaris = $("#jml-baris").val();
			var tagihan = $("#src-tagihan-" + sts).val();
			var nop = $("#nop-" + sts).val();
			var cb_batal = $("#cb_pembatalan").val();
			var srch = $("#srch-nama-" + sts).val();
			var url = "";

			if (sts == 1)
				url = "function/PBB/penagihan/svc-laporan-proses-sp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>";
			else if (sts == 2)
				url = "function/PBB/penagihan/svc-laporan-pembatalan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'24','uid':'$uid'}"); ?>" + "&cb_batal=" + cb_batal;
			else if (sts == 3)
				url = "function/PBB/penagihan/svc-laporan-rekap-sp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>";
			else if (sts == 4)
				url = "function/PBB/penagihan/svc-laporan-proses-pengembalian-sp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'26','uid':'$uid'}"); ?>";
			else if (sts == 5)
				url = "function/PBB/penagihan/svc-surat-pemanggilan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'27','uid':'$uid'}"); ?>" + "&srch=" + srch;
			else if (sts == 6)
				url = "function/PBB/penagihan/svc-surat-kejaksaan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'28','uid':'$uid'}"); ?>" + "&srch=" + srch;

			$("#monitoring-content-" + sts).html("loading ...");

			$("#monitoring-content-" + sts).load(url, {
				t1: tgl1,
				t2: tgl2,
				th: tahun,
				n: nop,
				st: sts,
				kc: kc,
				kl: kl,
				tagihan: tagihan,
				sp: sp,
				nkc: nkc
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});

		}



		function toExcel(sts) {
			var nmfileAll = '<?php echo date('yymdhmi'); ?>';
			var nmfile = nmfileAll + '-part-';
			var t1 = $("#tgl-sp1-" + sts).val();
			var t2 = $("#tgl-sp2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var nop = $("#nop-" + sts).val();
			var jmlBaris = $("#jml-baris").val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();
			var tagihan = $("#src-tagihan-" + sts).val();
			var nkc = $("#kecamatan-" + sts + " option:selected").text();
			var sp = $("#src-sp-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var cb_batal = $("#cb_pembatalan").val();

			if (sts == 1)
				window.open("function/PBB/penagihan/svc-laporan-proses-sp-excel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>" + "&nkc=" + nkc + "&kc=" + kc + "&sp=" + sp + "&th=" + tahun + "&t1=" + t1 + "&t2=" + t2);
			else if (sts == 2)
				window.open("function/PBB/penagihan/svc-laporan-pembatalan-sppt-excel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>" + "&nkc=" + nkc + "&kc=" + kc + "&sp=" + sp + "&th=" + tahun + "&cb_batal=" + cb_batal + "&t1=" + t1 + "&t2=" + t2);
			else if (sts == 3)
				window.open("function/PBB/penagihan/svc-laporan-rekap-sp-excel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>" + "&nkc=" + nkc + "&kc=" + kc + "&sp=" + sp + "&th=" + tahun);
			else
				window.open("function/PBB/penagihan/svc-laporan-proses-pengembalian-sp-excel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); ?>" + "&nkc=" + nkc + "&kc=" + kc + "&sp=" + sp + "&th=" + tahun + "&t1=" + t1 + "&t2=" + t2);


		}

		function setPage(pg, sts) {
			var tempo1 = $("#tgl-sp1-" + sts).val();
			var tempo2 = $("#tgl-sp2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var nop = $("#nop-" + sts).val();
			var nama = $("#wp-name-" + sts).val();
			var jmlBaris = $("#jml-baris").val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();
			var tagihan = $("#src-tagihan-" + sts).val();
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/monitoring/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
				na: nama,
				t1: tempo1,
				t2: tempo2,
				th: tahun,
				n: nop,
				st: sts,
				kc: kc,
				kl: kl,
				tagihan: tagihan,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT,
				LBL_KEL: LBL_KEL
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}

		function showKelurahan(sts) {
			var id = $('select#kecamatan-' + sts).val()
			var request = $.ajax({
				url: "view/PBB/monitoring/svc-kecamatan.php",
				type: "POST",
				data: {
					id: id,
					kel: 1
				},
				dataType: "json",
				success: function(data) {
					var c = data.msg.length;
					var options = '';
					options += '<option value="">Pilih Semua</option>';
					for (var i = 0; i < c; i++) {
						options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
						$("select#kelurahan-" + sts).html(options);
					}
				}
			});
		}

		$(function() {
			$("select#kecamatan-1").change(function() {
				showKelurahan(1);
			})
			$("select#kecamatan-2").change(function() {
				showKelurahan(2);
			})
		})

		$(function() {
			$("#tabs").tabs();
		});

		function showKecamatan(sts) {
			var request = $.ajax({
				url: "view/PBB/monitoring/svc-kecamatan.php",
				type: "POST",
				data: {
					id: "<?php echo $appConfig['KODE_KOTA'] ?>"
				},
				dataType: "json",
				success: function(data) {
					var c = data.msg.length;
					var options = '';
					options += '<option value="">Pilih Semua</option>';
					for (var i = 0; i < c; i++) {
						options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
						$("select#kecamatan-" + sts).html(options);
					}
				}
			});

		}

		function showKecamatanAll() {
			var request = $.ajax({
				url: "view/PBB/monitoring/svc-kecamatan.php",
				type: "POST",
				data: {
					id: "<?php echo $appConfig['KODE_KOTA'] ?>"
				},
				dataType: "json",
				success: function(data) {
					var c = data.msg.length;
					var options = '';
					options += '<option value="">Pilih Semua</option>';
					for (var i = 0; i < c; i++) {
						options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
						$("select#kecamatan-1").html(options);
						$("select#kecamatan-2").html(options);
						$("select#kecamatan-3").html(options);
						$("select#kecamatan-4").html(options);
					}
				}
			});

		}

		$(document).ready(function() {
			showKecamatanAll();
			$('#tabs').tabs({
				select: function(event, ui) { // select event
					$(ui.tab); // the tab selected
					if (ui.index == 2) {
						//showModelE2();
					}
				}
			});

			$("#all-check-button").click(function() {
				$('.check-all').each(function() {
					this.checked = $("#all-check-button").is(':checked');
				});
			});

			$("#btnHapus-5").click(function() {
				var arrNo = [];
				var dat = "";
				x = 0;
				$("input:checkbox[name='check-all\\[\\]']").each(function() {
					if ($(this).is(":checked")) {
						dat = ($(this).val()).split("+");
						arrNo.push(dat[0]);
						x++;
					}
				});
				if (x == 0) {
					$("<div>Belum ada data yang dipilih!</div>").dialog();
				} else {
					var r = confirm("Anda yakin akan menghapus data nomor " + dat[0] + "?");
					if (r == true) {
						delDataPemanggilan(arrNo);
					}
				}


			});

			$("#btnCetak-5").click(function() {

				x = 0;

				$("input:checkbox[name='check-all\\[\\]']").each(function() {
					if ($(this).is(":checked")) {
						printSuratPemanggilan($(this).val());
						x++;
					}
				});
				if (x == 0) {
					$("<div>Belum ada data yang dipilih!</div>").dialog();
				}
			});

			function delDataPemanggilan(nomor) {
				// alert(nomor);
				$.ajax({
					type: "POST",
					url: "./function/PBB/penagihan/svc-del-datpemanggilan.php",
					data: "nomor=" + nomor + "&GW_DBHOST=<?php echo $appConfig['GW_DBHOST'] ?>&GW_DBUSER=<?php echo $appConfig['GW_DBUSER'] ?>&GW_DBPWD=<?php echo $appConfig['GW_DBPWD'] ?>&GW_DBNAME=<?php echo $appConfig['GW_DBNAME'] ?>",
					success: function(msg) {
						// alert(msg);
						console.log(msg)
						if (delDataPemanggilan = true) {
							onSubmit(5)
						}
					}
				});
			}

			function printSuratPemanggilan(id) {
				var params = {
					svcId: id,
					appId: '<?php echo $a; ?>'
				};
				console.log("print ...");
				params = Base64.encode(Ext.encode(params));
				window.open('./function/PBB/penagihan/svc-print-srtpemanggilan.php?q=' + params, '_blank');
			}

			$("#btnHapus-6").click(function() {
				var arrNo = [];
				var dat = "";
				x = 0;
				$("input:checkbox[name='check-all2\\[\\]']").each(function() {
					if ($(this).is(":checked")) {
						dat = ($(this).val()).split("+");
						arrNo.push(dat[0]);
						x++;
					}
				});
				if (x == 0) {
					$("<div>Belum ada data yang dipilih!</div>").dialog();
				} else {
					var r = confirm("Anda yakin akan menghapus data nomor " + dat[0] + "?");
					if (r == true) {
						delDataKejaksaan(arrNo);
					}
				}


			});

			$("#btnCetak-6").click(function() {

				x = 0;

				$("input:checkbox[name='check-all2\\[\\]']").each(function() {
					if ($(this).is(":checked")) {
						printSuratKejaksaan($(this).val());
						x++;
					}
				});
				if (x == 0) {
					$("<div>Belum ada data yang dipilih!</div>").dialog();
				}
			});

			$("#btnCetak2-6").click(function() {

				x = 0;

				$("input:checkbox[name='check-all2\\[\\]']").each(function() {
					if ($(this).is(":checked")) {
						printSuratKejaksaan2($(this).val());
						x++;
					}
				});
				if (x == 0) {
					$("<div>Belum ada data yang dipilih!</div>").dialog();
				}
			});

		});




		function delDataKejaksaan(nomor) {
			//alert(nop);
			$.ajax({
				type: "POST",
				url: "./function/PBB/penagihan/svc-del-datkejaksaan.php",
				data: "nomor=" + nomor + "&GW_DBHOST=<?php echo $appConfig['GW_DBHOST'] ?>&GW_DBUSER=<?php echo $appConfig['GW_DBUSER'] ?>&GW_DBPWD=<?php echo $appConfig['GW_DBPWD'] ?>&GW_DBNAME=<?php echo $appConfig['GW_DBNAME'] ?>",
				success: function(msg) {
					// alert(msg);
					console.log(msg)
					if (delDataKejaksaan = true) {
						onSubmit(6)
					}
				}
			});
		}

		function printSuratKejaksaan(id) {
			var params = {
				svcId: id,
				appId: '<?php echo $a; ?>'
			};
			console.log("print ...");
			params = Base64.encode(Ext.encode(params));
			window.open('./function/PBB/penagihan/svc-print-srtkejaksaan.php?q=' + params, '_blank');
		}

		function printSuratKejaksaan2(id) {
			var params = {
				svcId: id,
				appId: '<?php echo $a; ?>'
			};
			console.log("print ...");
			params = Base64.encode(Ext.encode(params));
			window.open('./function/PBB/penagihan/svc-print-srtkejaksaan2.php?q=' + params, '_blank');
		}
	</script>

	<body>
		<div class="col-md-12">
			<div id="div-search">
				<div id="tabs">
					<ul>
						<li><a href="#tabs-1">Progres SP</a></li>
						<li><a href="#tabs-2">SP Bermasalah</a></li>
						<li><a href="#tabs-3">Laporan Penagihan SP</a></li>
						<li><a href="#tabs-4">Progres Pengembalian SP</a></li>
						<li><a href="#tabs-5">Surat Pemanggilan</a></li>
						<li><a href="#tabs-6">Pengantar Kejaksaan</a></li>
					</ul>
					<div id="tabs-1">
						<fieldset>
							<form id="TheForm-1" method="post">
								<table width="auto" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="50">Tahun</td>
										<td width="10">:</td>
										<td width="80">
											<select name="tahun-pajak-1" id="tahun-pajak-1">
												<?php
												$thn = date("Y");
												$thnTagihan = $appConfig['tahun_tagihan'];
												echo "<option value=\"\" selected>Pilih Semua</option>";
												for ($t = $thn; $t > ($thn - 9); $t--) {
													echo "<option value=\"$t\">$t</option>";
												}
												?>
											</select>
										</td>
										<td>&nbsp;</td>
										<td width="20">SP</td>
										<td width="10">:</td>
										<td width="80"><select id="src-sp-1" name="src-sp-1">
												<!-- <option value="0" >Pilih Semua</option> -->
												<option value="1">SP1</option>
												<option value="2">SP2</option>
												<option value="3">SP3</option>
											</select></td>
										<td>&nbsp;</td>
										<td>Kecamatan </td>
										<td>:</td>
										<td width="144"><select id="kecamatan-1"></select></td>
										<!-- <td width="80"><?php //echo $appConfig['LABEL_KELURAHAN'];
															?></td>
          <td width="3">:</td>
          <td><select id="kelurahan-1"></select></td>
		  -->
										<td width="80">Tanggal SP</td>
										<td width="10">:</td>
										<td width="60"><input type="text" name="tgl-sp" id="tgl-sp1-1" size="10" /></td>
										<td width="22">s/d </td>
										<td width="85"><input type="text" name="tgl-sp2" id="tgl-sp2-1" size="10" /></td>
										<td>&nbsp;</td>
										<td width="200">
											<input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSubmit (1)" />
											<input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(1)" />
											<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
										</td>
									</tr>
									<tr>

									</tr>
								</table>
							</form>
						</fieldset>
						<div id="monitoring-content-1" class="monitoring-content">
						</div>
					</div>

					<div id="tabs-2">
						<fieldset>
							<form id="TheForm-2" method="post">
								<table width="auto" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="50">Tahun</td>
										<td width="10">:</td>
										<td width="80">
											<select name="tahun-pajak-2" id="tahun-pajak-2">
												<?php
												$thn = date("Y");
												$thnTagihan = $appConfig['tahun_tagihan'];
												echo "<option value=\"\" selected>Pilih Semua</option>";
												for ($t = $thn; $t > ($thn - 9); $t--) {
													echo "<option value=\"$t\">$t</option>";
												}
												?>
											</select>
										</td>
										<td>&nbsp;</td>
										<td width="20">SP</td>
										<td width="10">:</td>
										<td width="80"><select id="src-sp-2" name="src-sp-2">
												<option value="1">SP1</option>
												<option value="2">SP2</option>
												<option value="3">SP3</option>
											</select></td>
										<td>&nbsp;</td>
										<td>Kecamatan </td>
										<td>:</td>
										<td width="144"><select id="kecamatan-2"></select></td>
										<!-- <td width="80"><?php echo $appConfig['LABEL_KELURAHAN']; ?></td>
          <td width="3">:</td>
          <td><select id="kelurahan-1"></select></td>
		  -->
										<td>&nbsp;</td>
										<td width="80">Tanggal SP</td>
										<td width="10">:</td>
										<td width="60"><input type="text" name="tgl-sp" id="tgl-sp1-2" size="10" /></td>
										<td width="22">s/d </td>
										<td width="85"><input type="text" name="tgl-sp2" id="tgl-sp2-2" size="10" /></td>
										<td>&nbsp;</td>
										<td width="200">
											<input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSubmit (2)" />
											<input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(2)" />
											<span id="loadlink2" style="font-size: 10px; display: none;">Loading...</span>
										</td>
									</tr>
									<tr>
										<td width="50">Permasalahan</td>
										<td width="10">:</td>
										<td width="80" colspan="17"><select id="cb_pembatalan" name="cb_pembatalan">
												<option value="0">Pilih Semua</option>
												<!-- <option value="2">WP Sudah Membayar PBB</option> -->
												<option value="3">Pembatalan SPPT PBB</option>
												<option value="4">Alamat Tidak Ditemukan</option>
												<option value="5">Tanah Sengketa</option>
												<option value="6">WP Sudah Perubahan Data</option>
											</select></td>
									</tr>
								</table>
							</form>
						</fieldset>
						<div id="monitoring-content-2" class="monitoring-content">
						</div>
					</div>

					<div id="tabs-3">
						<fieldset>
							<form id="TheForm-3" method="post">
								<table width="auto" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="50">Tahun</td>
										<td width="10">:</td>
										<td width="80">
											<select name="tahun-pajak-3" id="tahun-pajak-3">
												<?php
												$thn = date("Y");
												$thnTagihan = $appConfig['tahun_tagihan'];
												echo "<option value=\"\" selected>Pilih Semua</option>";
												for ($t = $thn; $t > ($thn - 9); $t--) {
													echo "<option value=\"$t\">$t</option>";
												}
												?>
											</select>
										</td>
										<td>&nbsp;</td>
										<td width="20">SP</td>
										<td width="10">:</td>
										<td width="80"><select id="src-sp-3" name="src-sp-3">
												<!-- <option value="0" >Pilih Semua</option> -->
												<option value="1">SP1</option>
												<option value="2">SP2</option>
												<option value="3">SP3</option>
											</select></td>
										<!-- <td>&nbsp;</td>
          <td>Kecamatan </td>
          <td>:</td>
          <td width="144"><select id="kecamatan-3"></select></td> -->
										<!-- <td width="80"><?php //echo $appConfig['LABEL_KELURAHAN'];
															?></td>
          <td width="3">:</td>
          <td><select id="kelurahan-1"></select></td>
		  -->
										<td width="80">Tanggal SP</td>
										<td width="10">:</td>
										<td width="60"><input type="text" name="tgl-sp" id="tgl-sp1-3" size="10" /></td>
										<td width="22">s/d </td>
										<td width="85"><input type="text" name="tgl-sp2" id="tgl-sp2-3" size="10" /></td>
										<td>&nbsp;</td>
										<td width="200">
											<input type="button" name="button3" id="button3" value="Tampilkan" onClick="onSubmit (3)" />
											<input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(3)" />
											<span id="loadlink3" style="font-size: 10px; display: none;">Loading...</span>
										</td>
									</tr>
									<tr>

									</tr>
								</table>
							</form>
						</fieldset>
						<div id="monitoring-content-3" class="monitoring-content">
						</div>
					</div>

					<div id="tabs-4">
						<fieldset>
							<form id="TheForm-4" method="post">
								<table width="auto" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="50">Tahun</td>
										<td width="10">:</td>
										<td width="80">
											<select name="tahun-pajak-4" id="tahun-pajak-4">
												<?php
												$thn = date("Y");
												$thnTagihan = $appConfig['tahun_tagihan'];
												echo "<option value=\"\" selected>Pilih Semua</option>";
												for ($t = $thn; $t > ($thn - 9); $t--) {
													echo "<option value=\"$t\">$t</option>";
												}
												?>
											</select>
										</td>
										<td>&nbsp;</td>
										<td width="20">SP</td>
										<td width="10">:</td>
										<td width="80"><select id="src-sp-4" name="src-sp-4">
												<!-- <option value="0" >Pilih Semua</option> -->
												<option value="1">SP1</option>
												<option value="2">SP2</option>
												<option value="3">SP3</option>
											</select></td>
										<td>&nbsp;</td>
										<td>Kecamatan </td>
										<td>:</td>
										<td width="144"><select id="kecamatan-4"></select></td>
										<!-- <td width="80"><?php //echo $appConfig['LABEL_KELURAHAN'];
															?></td>
          <td width="3">:</td>
          <td><select id="kelurahan-1"></select></td>
		  -->
										<td width="80">Tanggal SP</td>
										<td width="10">:</td>
										<td width="60"><input type="text" name="tgl-sp" id="tgl-sp1-4" size="10" /></td>
										<td width="22">s/d </td>
										<td width="85"><input type="text" name="tgl-sp2" id="tgl-sp2-4" size="10" /></td>
										<td>&nbsp;</td>
										<td width="200">
											<input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSubmit (4)" />
											<input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(4)" />
											<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
										</td>
									</tr>
									<tr>

									</tr>
								</table>
							</form>
						</fieldset>
						<div id="monitoring-content-4" class="monitoring-content">
						</div>
					</div>

					<div id="tabs-5">
						<fieldset>
							<form id="TheForm-5" method="post">
								<table width="auto" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="auto"><?php echo $startLink0; ?><input type="button" value="Tambah" name="btnTambah" /><?php echo $endLink; ?></td>
										<td width="auto"><input type="button" value="Hapus" name="btnHapus-5" id="btnHapus-5" /></td>
										<td width="auto"><input type="button" value="Cetak Surat Pemanggilan" name="btnCetak-5" id="btnCetak-5" /></td>
										<td>&nbsp;</td>
										<td width="80">Pencarian</td>
										<td width="10">:</td>
										<td width="60"><input type="text" name="srch-nama-5" id="srch-nama-5" size="35" placeholder="Nomor/Nama/NOP" /></td>
										<td width="200">
											<input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSubmit (5)" />
										</td>
									</tr>
									<tr>

									</tr>
								</table>
							</form>
						</fieldset>
						<div id="monitoring-content-5" class="monitoring-content">
						</div>
					</div>

					<div id="tabs-6">
						<fieldset>
							<form id="TheForm-6" method="post">
								<table width="auto" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="auto"><?php echo $startLink1; ?><input type="button" value="Tambah" name="btnTambah" /><?php echo $endLink; ?></td>
										<td width="auto"><input type="button" value="Hapus" name="btnHapus-6" id="btnHapus-6" /></td>
										<td width="auto"><input type="button" value="Cetak Surat Pengantar" name="btnCetak-6" id="btnCetak-6" /></td>
										<td width="auto"><input type="button" value="Cetak Surat Kuasa" name="btnCetak2-6" id="btnCetak2-6" /></td>
										<td>&nbsp;</td>
										<td width="80">Pencarian</td>
										<td width="10">:</td>
										<td width="60"><input type="text" name="srch-nama-6" id="srch-nama-6" size="35" placeholder="Nomor/Nama" /></td>
										<td width="200">
											<input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSubmit (6)" />
										</td>
									</tr>
									<tr>

									</tr>
								</table>
							</form>
						</fieldset>
						<div id="monitoring-content-6" class="monitoring-content">
						</div>
					</div>

				</div>
			</div>
		</div>
	<?php
}
	?>