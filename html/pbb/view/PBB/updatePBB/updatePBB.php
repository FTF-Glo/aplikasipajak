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
	<link href="view/PBB/updatePBB/monitoring.css?v0001" rel="stylesheet" type="text/css" />

	<!-- <link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/> -->
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<!--<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>-->
	<script type="text/javascript" src="view/PBB/updatePBB/pembayaran.js"></script>
	<!--  -->

	<script>
		var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST']; ?>';
		var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306'; ?>';
		var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME']; ?>';
		var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER']; ?>';
		var GW_DBPWD = '<?php echo $appConfig['GW_DBPWD']; ?>';
		var USER_LOGIN = '<?php echo $uname; ?>';

		$(document).ready(function() {
			$("input:submit, input:button").button();
			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})

		function onSubmit(sts) {
			var tempo1 = $("#jatuh-tempo1-" + sts).val();
			var tempo2 = $("#jatuh-tempo2-" + sts).val();
			var tahun = $("#tahun-pajak-" + sts).val();
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();
			var nama = $("#wp-name-" + sts).val();
			nama = nama.replace(" ", "%20");
			var jmlBaris = $("#jml-baris").val();
			var kc = $("#kecamatan-" + sts).val();
			var kl = $("#kelurahan-" + sts).val();
			$("#monitoring-content-" + sts).html("loading ...");


			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
				na: nama,
				t1: tempo1,
				t2: tempo2,
				th: tahun,
				n1: nop1,
				n2: nop2,
				n3: nop3,
				n4: nop4,
				n5: nop5,
				n6: nop6,
				n7: nop7,
				st: sts,
				kc: kc,
				kl: kl,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});

		}

		function onSearch(sts) {
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();
			var nama = $("#wp-name-" + sts).val();
			nama = nama.replace(" ", "%20");
			var jmlBaris = $("#jml-baris").val();
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
				na: nama,
				n1: nop1,
				n2: nop2,
				n3: nop3,
				n4: nop4,
				n5: nop5,
				n6: nop6,
				n7: nop7,
				st: sts,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT,
				USER_LOGIN: USER_LOGIN
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});

		}

		function onSearchDataSPPTFinal(sts) {
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();

			var nopGabung = nop1 + "" + nop2 + "" + nop3 + "" + nop4 + "" + nop5 + "" + nop6 + "" + nop7;

			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-searchspptfinal.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'24','uid':'$uid'}"); ?>", {
				n1: nop1,
				n2: nop2,
				n3: nop3,
				n4: nop4,
				n5: nop5,
				n6: nop6,
				n7: nop7,
				n: nopGabung,
				st: sts,
				t: <?php echo $appConfig['tahun_tagihan'] ?>,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}

		function onSearchPembatalanSPPT(sts) {
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();
			var nopGabung = nop1 + "" + nop2 + "" + nop3 + "" + nop4 + "" + nop5 + "" + nop6 + "" + nop7;
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-pembatalan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>", {
				n1: nop1,
				n2: nop2,
				n3: nop3,
				n4: nop4,
				n5: nop5,
				n6: nop6,
				n7: nop7,
				n: nopGabung,
				st: sts,
				t: <?php echo $appConfig['tahun_tagihan'] ?>,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}

		function onSearchPenerbitanSPPT(sts) {
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();
			var nopGabung = nop1 + "" + nop2 + "" + nop3 + "" + nop4 + "" + nop5 + "" + nop6 + "" + nop7;

			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-penerbitan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'26','uid':'$uid'}"); ?>", {
				n1: nop1,
				n2: nop2,
				n3: nop3,
				n4: nop4,
				n5: nop5,
				n6: nop6,
				n7: nop7,n: nopGabung,
				st: sts,
				t: <?php echo $appConfig['tahun_tagihan'] ?>,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}

		function setPage(pg, sts) {
			var nop1 = $("#nop-" + sts + "-1").val();
			var nop2 = $("#nop-" + sts + "-2").val();
			var nop3 = $("#nop-" + sts + "-3").val();
			var nop4 = $("#nop-" + sts + "-4").val();
			var nop5 = $("#nop-" + sts + "-5").val();
			var nop6 = $("#nop-" + sts + "-6").val();
			var nop7 = $("#nop-" + sts + "-7").val();
			var nopGabung = nop1 + "" + nop2 + "" + nop3 + "" + nop4 + "" + nop5 + "" + nop6 + "" + nop7;

			//var status = $("#sel-status").val();
			var nama = $("#wp-name-" + sts).val();
			nama = nama.replace(" ", "%20");
			//var jmlBaris = $("#jml-baris").val();
			$("#monitoring-content-" + sts).html("loading ...");
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>&p=" + pg, {
				na: nama,
				n1: nop1,
				n2: nop2,
				n3: nop3,
				n4: nop4,
				n5: nop5,
				n6: nop6,
				n7: nop7,n:nopGabung,
				st: sts,
				GW_DBHOST: GW_DBHOST,
				GW_DBNAME: GW_DBNAME,
				GW_DBUSER: GW_DBUSER,
				GW_DBPWD: GW_DBPWD,
				GW_DBPORT: GW_DBPORT
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}

		function setJatuhTempo() {
			var tglJatuhTempo = $("#jatuh-tempo").val();
			$("#monitoring-content-" + sts).html("loading ...");
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-update-jatuh-tempo.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','GW_DBHOST':" . $appConfig['GW_DBHOST'] . ",'GW_DBNAME':" . $appConfig['GW_DBNAME'] . ",'GW_DBUSER':" . $appConfig['GW_DBUSER'] . ",'GW_DBPWD':" . $appConfig['GW_DBPWD'] . ",'GW_DBPORT':" . $appConfig['GW_DBPORT'] . "}"); ?>", {
				tglJatuhTempo: tglJatuhTempo
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});

			$.ajax({
				type: "POST",
				url: "./view/PBB/updatePBB/svc-update-jatuh-tempo.php",
				data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','GW_DBHOST':" . $appConfig['GW_DBHOST'] . ",'GW_DBNAME':" . $appConfig['GW_DBNAME'] . ",'GW_DBUSER':" . $appConfig['GW_DBUSER'] . ",'GW_DBPWD':" . $appConfig['GW_DBPWD'] . ",'GW_DBPORT':" . $appConfig['GW_DBPORT'] . "}"); ?>",
				success: function(msg) {
					alert("Berhasil");
				}
			});

		}

		$(function() {
			$("#tabs").tabs();
			$("#jatuh-tempo").datepicker({
				dateFormat: "yy-mm-dd"
			});
		});

		function showBulan(id) {
			var bulan = Array("Semua", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			var options = '';
			for (var i = 0; i < 13; i++) {
				options += '<option value="' + i + '">' + bulan[i] + '</option>';
				$("select#" + id).html(options);
			}
		}

		function restoreDataPenghapusan(id, o) {
			var nop1 = $("#nop-" + id + "-1").val();
			var nop2 = $("#nop-" + id + "-2").val();
			var nop3 = $("#nop-" + id + "-3").val();
			var nop4 = $("#nop-" + id + "-4").val();
			var nop5 = $("#nop-" + id + "-5").val();
			var nop6 = $("#nop-" + id + "-6").val();
			var nop7 = $("#nop-" + id + "-7").val();
			var nop = nop1 + "" + nop2 + "" + nop3 + "" + nop4 + "" + nop5 + "" + nop6 + "" + nop7;
			if ($.trim(nop).length != 18) {
				alert('NOP harus diisi (18 karakter).');
				$("#nop-" + id + "-1").focus();
				return false;
			}

			if (confirm('Apakah anda yakin untuk melakukan restore data history penghapusan untuk NOP : ' + nop + ' ?') === false) return false;

			$(o).attr('disabled', true);
			$.ajax({
				type: 'POST',
				url: './view/PBB/updatePBB/svc-restore-history-penghapusan.php',
				dataType: 'json',
				data: {
					nop: nop,
					q: '<?php echo base64_encode("{'a':'$a', 'm':'$m'}") ?>'
				},
				success: function(res) {
					alert(res.msg);
					$(o).removeAttr('disabled');
					$("#nop-" + id + "-1").val('');
					$("#nop-" + id + "-2").val('');
					$("#nop-" + id + "-3").val('');
					$("#nop-" + id + "-4").val('');
					$("#nop-" + id + "-5").val('');
					$("#nop-" + id + "-6").val('');
					$("#nop-" + id + "-7").val('');
				},
				error: function(res) {
					alert('Error sintax.');
					$(o).removeAttr('disabled');
				},
			});
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
					<!-- <li><a href="#tabs-4">Update Data</a></li> -->
					<li><a href="#tabs-5">Kembalikan Data Ke Pendataan</a></li>
					<!-- <li><a href="#tabs-6">Update Tanggal Jatuh Tempo</a></li> -->
					<li><a href="#tabs-7">Restore History Penghapusan</a></li>
				</ul>
				<!-- <div id="tabs-4">
    <form id="TheForm-1" method="post" action="view/PBB/updatePBB/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
      <table width="1063" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td width="73">NOP </td>
          <td width="3">:</td>
          <td><input type="text" name="nop-4" id="nop-4" /></td>
          <td>&nbsp;</td>
          <td>Nama&nbsp;Wajib&nbsp;Pajak</td>
          <td>:</td>
          <td width="144"><input type="text" name="wp-name" id="wp-name-4" /></td>
          <td width="180">
              <input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSearch (4)"/>
              <!--<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(1)"/>
              <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
          </td>    
        </tr>
      </table>
    </form>
	<table>
		 <tr>
		  <td width="73">Tanggal Bayar </td>
		  <td width="3">:</td>
		  <td><input type="text" name="tgl-bayar" id="tgl-bayar" value="<?php echo date("Y-m-d") ?>"></td>
		 </tr>
	</table>
    <div id="monitoring-content-4" class="monitoring-content">
	</div>
    </div>-->
				<div id="tabs-5">
					<form id="TheForm-2" method="post" action="view/PBB/updatePBB/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
						<div class="row mb5">
							<div class="col-md-1" style="margin-top: 7px;">NOP:</div>
							<div class="col-md-5">
								<!--<input type="text" id="nop-5" name="nop-5" size="30" maxlength="18">-->
								<div class="col-md-1" style="padding: 0">
									<input type="text" class="form-control nop-input-1" style="padding: 6px;" name="nop-5-1" id="nop-5-1" placeholder="PR">
								</div>
								<div class="col-md-1" style="padding: 0">
									<input type="text" class="form-control nop-input-2" maxlength="2" style="padding: 6px;" name="nop-5-2" id="nop-5-2" placeholder="DTII">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-input-3" maxlength="3" style="padding: 6px;" name="nop-5-3" id="nop-5-3" placeholder="KEC">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-input-4" maxlength="3" style="padding: 6px;" name="nop-5-4" id="nop-5-4" placeholder="KEL">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-input-5" maxlength="3" style="padding: 6px;" name="nop-5-5" id="nop-5-5" placeholder="BLOK">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-input-6" maxlength="4" style="padding: 6px;" name="nop-5-6" id="nop-5-6" placeholder="NO.URUT">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-input-7" maxlength="1" style="padding: 6px;" name="nop-5-7" id="nop-5-7" placeholder="KODE">
								</div>
							</div>
							<div class="col-md-3" style="margin-top: 3px;">
								<input type="button" class="btn btn-primary btn-orange" name="button2" id="button2" value="Tampilkan" onClick="onSearchDataSPPTFinal(5)" />
								<!--<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(1)"/>-->
								<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
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
									onSearchDataSPPTFinal(5)
								}
							});
						</script>
					</form>
					<div id="monitoring-content-5" class="monitoring-content"></div>
				</div>
				<!-- <div id="tabs-6">
        <fieldset>
            <form id="TheForm-6" method="post" target="TheWindow">
                <table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                        <td width="150">Tanggal Jatuh Tempo</td>
                        <td width="3">:</td>
                        <td width="60"><input type="text" name="jatuh-tempo" id="jatuh-tempo" size="10"></td>
                        <td width="180">
                            <input type="button" name="button6" id="button6" value="Set Tanggal Jatuh Tempo" onClick="setJatuhTempo()"/>
                            <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
                        </td>    
                    </tr>
                </table>
            </form>
        </fieldset>
        <div id="monitoring-content-5" class="monitoring-content"></div>
    </div> -->
				<div id="tabs-7">

					<form id="TheForm-2" method="post" action="view/PBB/updatePBB/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
						<div class="row mb5">
							<div class="col-md-1" style="margin-top: 7px;">NOP:</div>
							<div class="col-md-5">
								<!--<input type="text" id="nop-5" name="nop-5" size="30" maxlength="18">-->
								<div class="col-md-1" style="padding: 0">
									<input type="text" class="form-control nop-inputs-1" style="padding: 6px;" name="nop-7-1" id="nop-7-1" placeholder="PR">
								</div>
								<div class="col-md-1" style="padding: 0">
									<input type="text" class="form-control nop-inputs-2" maxlength="2" style="padding: 6px;" name="nop-7-2" id="nop-7-2" placeholder="DTII">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-inputs-3" maxlength="3" style="padding: 6px;" name="nop-7-3" id="nop-7-3" placeholder="KEC">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-inputs-4" maxlength="3" style="padding: 6px;" name="nop-7-4" id="nop-7-4" placeholder="KEL">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-inputs-5" maxlength="3" style="padding: 6px;" name="nop-7-5" id="nop-7-5" placeholder="BLOK">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-inputs-6" maxlength="4" style="padding: 6px;" name="nop-7-6" id="nop-7-6" placeholder="NO.URUT">
								</div>
								<div class="col-md-2" style="padding: 0">
									<input type="text" class="form-control nop-inputs-7" maxlength="1" style="padding: 6px;" name="nop-7-7" id="nop-7-7" placeholder="KODE">
								</div>
							</div>
							<div class="col-md-3" style="margin-top: 3px;">
								<input type="button" class="btn btn-primary btn-orange" name="button2" id="button2" value="Restore" onClick="restoreDataPenghapusan(7,this)" />
								<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
							</div>
						</div>
						<script>
							$(".nop-inputs-1").on("keyup", function() {
								var len = $(this).val().length;
								let nopLengkap = $(this).val();
								
								if(!$(".nop-inputs-2").val()) $(".nop-inputs-2").val(nopLengkap.substr(2, 2));
								if(!$(".nop-inputs-3").val()) $(".nop-inputs-3").val(nopLengkap.substr(4, 3));
								if(!$(".nop-inputs-4").val()) $(".nop-inputs-4").val(nopLengkap.substr(7, 3));
								if(!$(".nop-inputs-5").val()) $(".nop-inputs-5").val(nopLengkap.substr(10, 3));
								if(!$(".nop-inputs-6").val()) $(".nop-inputs-6").val(nopLengkap.substr(13, 4));
								if(!$(".nop-inputs-7").val()) $(".nop-inputs-7").val(nopLengkap.substr(17, 1));
								if(len > 2) $(this).val(nopLengkap.substr(0, 2));
								if (len == 2) {
									$(".nop-inputs-2").focus();
								}
							});

							$(".nop-inputs-2").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 2) {
									$(".nop-inputs-3").focus();
								}
							});

							$(".nop-inputs-3").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 3) {
									$(".nop-inputs-4").focus();
								}
							});

							$(".nop-inputs-4").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 3) {
									$(".nop-inputs-5").focus();
								}
							});

							$(".nop-inputs-5").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 3) {
									$(".nop-inputs-6").focus();
								}
							});

							$(".nop-inputs-6").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 4) {
									$(".nop-inputs-7").focus();
								}
							});

							$(".nop-inputs-7").on("keyup", function() {
								var len = $(this).val().length;

								if (len == 1) {
									restoreDataPenghapusan(7, this)
								}
							});
						</script>
					</form>
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
			var tahun = $("#tahun-pajak-1").val();

			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		});
		$(document).ready(function() {
			$("#tgl-bayar").datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true
			});
		});

		function iniAngka(evt, x) {
			if ($(x).attr('readonly') == 'readonly') return false;
			var charCode = (evt.which) ? evt.which : event.keyCode;
			if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
				return true;
			} else {
				alert('Input hanya boleh angka!');
				return false;
			}
		}
	</script>