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
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script>
		var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
		var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
		var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
		var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
		var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
		var LBL_KEL = '<?php echo $appConfig['LABEL_KELURAHAN']; ?>';

		function getHistoryPelayananNOP(sts) {
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();

			$("#monitoring-content-" + sts).html("loading ...");
			$("#monitoring-content-" + sts).load("view/PBB/rekonsiliasi/svc-history-pelayanan-nop.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>", {
				nop1: nop1,
				nop2: nop2,
				nop3: nop3,
				nop4: nop4,
				nop5: nop5,
				nop6: nop6,
				nop7: nop7,
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

		function onSubmit(sts) {
			var tahun = $("#tahun-pajak-" + sts).val();
			var bulan = $("#bulan-" + sts).val();

			$("#monitoring-content-" + sts).html("loading ...");
			$("#monitoring-content-" + sts).load("view/PBB/rekonsiliasi/svc-pendapatan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>", {
				th: tahun,
				bln: bulan,
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

		function toExcel(sts) {
			var tahun = $("#tahun-pajak-" + sts).val();
			var bulan = $("#bulan-" + sts).val();

			window.open("view/PBB/rekonsiliasi/svc-toexcel-pendapatan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>&th=" + tahun + "&bulan=" + bulan);
		}

		function tampilPiutang(sts) {
			var tempo1 = "";
			var tempo2 = "";
			var nop = "";
			var nama = "";
			var jmlBaris = "";
			var kc = "";
			var kl = "";
			var tagihan = "";

			var tahun = $("#tahun-pajak-" + sts).val();
			var stsPembayaran = $("#status-" + sts).val();

			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/rekonsiliasi/svc-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
				na: nama,
				t1: tempo1,
				t2: tempo2,
				th: tahun,
				n: nop,
				st: sts,
				stp: stsPembayaran,
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

		function toExcelPiutang(sts) {
			var nmfileAll = '<?php echo date('yymdhmi'); ?>';
			var nmfile = nmfileAll + '-part-';
			var tempo1 = "";
			var tempo2 = "";
			var nop = "";
			var nama = "";
			var jmlBaris = "";
			var kc = "";
			var kl = "";
			var tagihan = "";
			var tahun = $("#tahun-pajak-" + sts).val();
			var stsPembayaran = $("#status-" + sts).val();

			$("#loadlink1").show();
			$.ajax({
				type: "POST",
				url: "./view/PBB/rekonsiliasi/svc-countforlink.php",
				data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'2','uid':'$uid'}"); ?>" + "&stp=" + stsPembayaran + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
				success: function(msg) {
					var sumOfPage = Math.ceil(msg / 20000);
					var strOfLink = "";
					if (msg < 20000)
						strOfLink += '<a href="view/PBB/rekonsiliasi/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&stp=' + stsPembayaran + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
					else {
						for (var page = 1; page <= sumOfPage; page++) {
							strOfLink += '<a href="view/PBB/rekonsiliasi/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&stp=' + stsPembayaran + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=' + page + '">' + nmfile + page + '</a><br/>';
						}
					}
					$("#contentLink").html(strOfLink);
					$("#cBox").css("display", "block");
					$("#loadlink").hide();
				}
			});
		}

		function setPage(pg, sts) {
			var tempo1 = "";
			var tempo2 = "";
			var nop = "";
			var nama = "";
			var jmlBaris = "";
			var kc = "";
			var kl = "";
			var tagihan = "";
			var tahun = $("#tahun-pajak-" + sts).val();
			var stsPembayaran = $("#status-" + sts).val();

			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/rekonsiliasi/svc-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
				na: nama,
				t1: tempo1,
				t2: tempo2,
				th: tahun,
				n: nop,
				st: sts,
				stp: stsPembayaran,
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

		$(function() {
			$("#tabs").tabs();
		});

		function showBulan(id) {
			var bulan = Array("Semua", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			var options = '';
			for (var i = 0; i < 13; i++) {
				options += '<option value="' + i + '">' + bulan[i] + '</option>';
				$("select#" + id).html(options);
			}
		}

		$(document).ready(function() {
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
		<div id="div-search">
			<div id="tabs">
				<ul>
					<li><a href="#tabs-1">Pendapatan</a></li>
					<li><a href="#tabs-2">Piutang Setelah Pengalihan</a></li>
					<li><a href="#tabs-3">History Pelayanan NOP</a></li>
				</ul>
				<div id="tabs-1">
					<fieldset>
						<form id="TheForm-1" method="post" action="#" target="TheWindow">
							<div class="row mb5">
								<div class="col-md-1" style="margin-top: 7px;">Tahun : </div>
								<div class="col-md-2">
									<select class="form-control" style="width:100%;" name="tahun-pajak-1" id="tahun-pajak-1">
										<?php
										$thn = date("Y");
										$thnTagihan = $appConfig['tahun_tagihan'];
										echo "<option value=\"\" selected>Semua</option>";
										for ($t = $thn; $t > ($thn - 22); $t--) {
											if ($t == $thnTagihan) {
												echo "<option value=\"$t\">$t</option>";
											} else
												echo "<option value=\"$t\">$t</option>";
										}
										?>
									</select>
								</div>
								<div class="col-md-1" style="margin-top: 7px; ">Bulan : </div>
								<div class="col-md-2">
									<select class="form-control" name="bulan-1" id="bulan-1">
										<?php
										$thn = date("Y");
										$bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
										//echo "<option value=\"-1\">Semua</option>";
										for ($b = 0; $b < 12; $b++) {
											echo "<option value=\"" . ($b + 1) . "\">" . $bulan[$b] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="col-md-3" style="margin-top: 3px;">
									<input type="button" class="btn btn-primary" name="button2" id="button2" value="Tampilkan" onClick="onSubmit (1)" />
									<input type="button" class="btn btn-primary btn-blue" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(1)" />
								</div>
							</div>
						</form>
					</fieldset>
					<div id="monitoring-content-1" class="monitoring-content">
					</div>
				</div>
				<div id="tabs-2">
					<fieldset>
						<form id="TheForm-2" method="post" action="#" target="TheWindow">
							<div class="row mb5">
								<div class="col-md-1" style="margin-top: 7px;">Tahun Pajak : </div>
								<div class="col-md-1">
									<select class="form-control" name="tahun-pajak-2" id="tahun-pajak-2">
										<?php
										$thn = date("Y");
										$thnTagihan = $appConfig['tahun_tagihan'];
										echo "<option value=\"\" selected>Semua</option>";
										for ($t = $thn; $t > ($thn - 22); $t--) {
											if ($t == $thnTagihan) {
												echo "<option value=\"$t\">$t</option>";
											} else
												echo "<option value=\"$t\">$t</option>";
										}
										?>
									</select>
								</div>
								<div class="col-md-1" style="margin-top: 7px;">Status : </div>
								<div class="col-md-1">
									<select class="form-control" name="status-2" id="status-2">
										<option value="0">Semua</option>
										<option value="1">Sudah Bayar</option>
										<option value="2">Belum Bayar</option>
									</select>
								</div>
								<div class="col-md-3" style="margin-top: 3px;">
									<input type="button" class="btn btn-primary btn-orange" name="button2" id="button2" value="Tampilkan" onClick="tampilPiutang(2)" />
									<input type="button" class="btn btn-primary btn-blue" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcelPiutang(2)" />
									<span id="loadlink" style="font-size: 10px; display: none;">Loading...</span>
								</div>
							</div>
						</form>
					</fieldset>
					<div id="monitoring-content-2" class="monitoring-content">
					</div>
				</div>

				<div id="tabs-3">
					<fieldset>
						<form id="TheForm-3" method="post" action="#" target="TheWindow">
							<div class="row mb5">
								<div class="col-md-1" style="margin-top: 7px;">NOP : </div>
								<div class="col-md-5">
									<div class="col-md-1" style="padding: 0">
										<input type="text" class="form-control nop-input-1" style="padding: 6px;" name="nop-3-1" id="nop-3-1" placeholder="PR">
									</div>
									<div class="col-md-1" style="padding: 0">
										<input type="text" class="form-control nop-input-2" maxlength="2" style="padding: 6px;" name="nop-3-2" id="nop-3-2" placeholder="DTII">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control nop-input-3" maxlength="3" style="padding: 6px;" name="nop-3-3" id="nop-3-3" placeholder="KEC">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control nop-input-4" maxlength="3" style="padding: 6px;" name="nop-3-4" id="nop-3-4" placeholder="KEL">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control nop-input-5" maxlength="3" style="padding: 6px;" name="nop-3-5" id="nop-3-5" placeholder="BLOK">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control nop-input-6" maxlength="4" style="padding: 6px;" name="nop-3-6" id="nop-3-6" placeholder="NO.URUT">
									</div>
									<div class="col-md-2" style="padding: 0">
										<input type="text" class="form-control nop-input-7" maxlength="1" style="padding: 6px;" name="nop-3-7" id="nop-3-7" placeholder="KODE">
									</div>
									<!--<input type="text" name="nop-3" id="nop-3" maxlength="18" placeholder="Masukan NOP">-->
								</div>
								<div class="col-md-2" style="margin-top: 3px;">
									<input class="btn btn-primary btn-orange" type="button" name="button2" id="button2" value="Tampilkan" onClick="getHistoryPelayananNOP(3)" />
								</div>
							</div>
							<script>
								$(".nop-input-1").on("keyup", function() {
									var len = $(this).val().length;
									let nopLengkap = $(this).val();
									
									if(!$(".nop-input-2").val()) $(".nop-input-2").val(nopLengkap.substr(2, 2));
									if(!$(".nop-input-3").val()) $(".nop-input-3").val(nopLengkap.substr(4, 3));
									if(!$(".nop-input-4").val()) $(".nop-input-4").val(nopLengkap.substr(7, 3));
									if(!$(".nop-input-5").val()) $(".nop-input-5").val(nopLengkap.substr(10, 3));
									if(!$(".nop-input-6").val()) $(".nop-input-6").val(nopLengkap.substr(13, 4));
									if(!$(".nop-input-7").val()) $(".nop-input-7").val(nopLengkap.substr(17, 1));
									if(len > 2) $(this).val(nopLengkap.substr(0, 2));
									if (len == 2) {
										$(".nop-input-2").focus();
									}
								});

								$(".nop-input-2").on("keyup", function() {
									var len = $(this).val().length;

									if (len == 2) {
										$(".nop-input-3").focus();
									}
								});

								$(".nop-input-3").on("keyup", function() {
									var len = $(this).val().length;

									if (len == 3) {
										$(".nop-input-4").focus();
									}
								});

								$(".nop-input-4").on("keyup", function() {
									var len = $(this).val().length;

									if (len == 3) {
										$(".nop-input-5").focus();
									}
								});

								$(".nop-input-5").on("keyup", function() {
									var len = $(this).val().length;

									if (len == 3) {
										$(".nop-input-6").focus();
									}
								});

								$(".nop-input-6").on("keyup", function() {
									var len = $(this).val().length;

									if (len == 4) {
										$(".nop-input-7").focus();
									}
								});

								$(".nop-input-7").on("keyup", function() {
									var len = $(this).val().length;

									if (len == 1) {
										getHistoryPelayananNOP(3);
									}
								});
							</script>
						</form>
					</fieldset>
					<div id="monitoring-content-3" class="monitoring-content">
					</div>
				</div>

			</div>
		</div>
	<?php
}
	?>

	<div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
		<div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
			<div style="float: left;">
				<span style="font-size: 12px;">Link Download</span>
			</div>
			<div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
		</div>
		<div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
	</div>
	</body>

	<script language="javascript">
		$(document).ready(function() {
			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})
	</script>