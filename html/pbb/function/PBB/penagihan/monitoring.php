<?php
// prevent direct access
if (!isset($data)) {
	return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig = $User->GetAppConfig($application);

//prevent access to not accessible module
if (!$bOK) {
	return false;
}


if (!isset($opt)) {
?>
	<link href="view/PBB/monitoring/monitoring.css?v0001" rel="stylesheet" type="text/css" />

	<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>

	<script>
		$(document).ready(function() {
			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})

		$(function() {
			$("#jatuh-tempo1-1").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#jatuh-tempo2-1").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#jatuh-tempo1-2").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#jatuh-tempo2-2").datepicker({
				dateFormat: "yy-mm-dd"
			});
		});

		function onSubmit(sts) {
			var tempo1 = $("#jatuh-tempo1-" + sts).val();
			var tempo2 = $("#jatuh-tempo2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var nop = $("#nop-" + sts).val();
			//var status = $("#sel-status").val();
			var nama = $("#wp-name-" + sts).val();
			//nama = nama.replace(" ","%20");
			var jmlBaris = $("#jml-baris").val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();
			//var par = "&t1="+tempo1+"&t2="+tempo1+"&th="+tahun+"&n="+nop+"&st="+status+"&j="+jmlBaris;
			$("#monitoring-content-" + sts).html("loading ...");


			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/monitoring/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
				na: nama,
				t1: tempo1,
				t2: tempo2,
				th: tahun,
				n: nop,
				st: sts,
				kc: kc,
				kl: kl
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
			var tempo1 = $("#jatuh-tempo1-" + sts).val();
			var tempo2 = $("#jatuh-tempo2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var nop = $("#nop-" + sts).val();
			var nama = $("#wp-name-" + sts).val();
			//nama = nama.replace(" ","%20");
			var jmlBaris = $("#jml-baris").val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();

			if (sts == 1)
				$("#loadlink1").show();
			else
				$("#loadlink2").show();

			$.ajax({
				type: "POST",
				url: "./view/PBB/monitoring/svc-countforlink.php",
				data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl,
				success: function(msg) {
					var sumOfPage = Math.ceil(msg / 25000);
					var strOfLink = ""
					if (msg > 0)
						strOfLink += '<a href="view/PBB/monitoring/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
					else {
						for (var page = 1; page <= sumOfPage; page++) {
							strOfLink += '<a href="view/PBB/monitoring/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&p=' + page + '">' + nmfile + page + '</a><br/>';
						}
					}
					$("#contentLink").html(strOfLink);
					$("#cBox").css("display", "block");

					if (sts == 1)
						$("#loadlink1").hide();
					else
						$("#loadlink2").hide();
				}
			});
		}


		function setPage(pg, sts) {
			var tempo1 = $("#jatuh-tempo1-" + sts).val();
			var tempo2 = $("#jatuh-tempo2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var nop = $("#nop-" + sts).val();
			//var status = $("#sel-status").val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();
			var nama = $("#wp-name-" + sts).val();
			//nama = nama.replace(" ","%20");
			//var jmlBaris = $("#jml-baris").val();
			//console.log(pg);
			$("#monitoring-content-" + sts).html("loading ...");
			$("#monitoring-content-" + sts).load("view/PBB/monitoring/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>&p=" + pg, {
				na: nama,
				t1: tempo1,
				t2: tempo2,
				th: tahun,
				n: nop,
				st: sts,
				kc: kc,
				kl: kl
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
			//	$("select#kecamatan-3").change(function(){
			//		showKelurahan(3);
			//	})	
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

		function showBulan(id) {
			var bulan = Array("Semua", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			var options = '';
			for (var i = 0; i < 13; i++) {
				options += '<option value="' + i + '">' + bulan[i] + '</option>';
				$("select#" + id).html(options);
			}
		}

		function showModelE2() {
			var tahun = $("#tahun-pajak-3").val();
			var kecamatan = $("#kecamatan-3").val();
			var namakec = $("#kecamatan-3 option:selected").text();
			var sts = 1;

			$("#monitoring-content-3").html("loading ...");
			$("#monitoring-content-3").load("function/PBB/penagihan/svc-monitoring.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>", {
				th: tahun,
				st: sts,
				kc: kecamatan,
				n: namakec,
				target_ketetapan: 'semua'
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}


		function excelModelE2() {
			var tahun = $("#tahun-pajak-3").val();
			var kecamatan = $("#kecamatan-3").val();
			var namakec = $("#kecamatan-3 option:selected").text();
			var sts = 1;

			window.open("function/PBB/penagihan/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>" + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&target_ketetapan=semua");
		}

		$(document).ready(function() {
			showKecamatanAll();
			showBulan("bulan-1");
			showBulan("bulan-2");
			$('#tabs').tabs({
				select: function(event, ui) { // select event
					$(ui.tab); // the tab selected
					if (ui.index == 2) {
						//showModelE2();
					}
				}
			});
		});
	</script>

	<body>
		<div class="col-md-12">
			<div id="div-search">
				<div id="tabs">
					<!--    <ul>
        <li><a href="#tabs-3">Model E.2</a></li>
    </ul>-->
					<div id="tabs-3">
						<fieldset>
							<form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
								<table width="100%" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td width="80">Tahun&nbsp;Pajak </td>
										<td width="3">:</td>
										<td width="61">
											<select name="tahun-pajak-3" id="tahun-pajak-3">
												<option value="">Semua</option>
												<?php
												$thn = date("Y");
												$thnTagihan = $appConfig['tahun_tagihan'];
												for ($t = $thn; $t > ($thn - 9); $t--) {
													if ($t == $thnTagihan) {
														echo "<option value=\"$t\" selected>$t</option>";
													} else
														echo "<option value=\"$t\">$t</option>";
												}
												?>
											</select>
										</td>
										<td width="1">&nbsp;</td>
										<td width="69">Kecamatan</td>
										<td width="3">:</td>
										<td width="138"><select name="kecamatan-3" id="kecamatan-3">
											</select></td>
										<td width="380">
											<input type="button" name="button3" id="button" value="Submit" onClick="showModelE2()" />
											<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="excelModelE2()" />
										</td>
										<td width="5">&nbsp;</td>
										<td width="126"></td>
									</tr>
								</table>
								<input type="hidden" id="export_e2" />
							</form>
						</fieldset>
						<div id="monitoring-content-3" class="monitoring-content"></div>
					</div>

				</div>
			</div>
		</div>
	<?php
}
	?>
	<div class="col-md-12">
		<div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
			<div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
				<div style="float: left;">
					<span style="font-size: 12px;">Link Download</span>
				</div>
				<div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
			</div>
			<div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
		</div>
	</div>
	</body>

	<script language="javascript">
		$(document).ready(function() {
			var tahun = $("#tahun-pajak-1").val();


			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})
	</script>