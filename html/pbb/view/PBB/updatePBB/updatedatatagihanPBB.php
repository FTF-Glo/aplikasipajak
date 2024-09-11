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
	<link href="view/PBB/updatePBB/monitoring.css" rel="stylesheet" type="text/css" />

	<!-- <link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/> -->
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<!--<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>-->
	<script type="text/javascript" src="view/PBB/updatePBB/pembayaran.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>

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
			var nop = $("#nop-" + sts).val();
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
				n: nop,
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
			var nop = $("#nop-" + sts).val();
			var nama = $("#wp-name-" + sts).val();
			nama = nama.replace(" ", "%20");
			var jmlBaris = $("#jml-baris").val();
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
				na: nama,
				n: nop,
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
			var nop = $("#nop-" + sts).val();
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-searchspptfinal.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'24','uid':'$uid'}"); ?>", {
				n: nop,
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
			var nop = $("#nop-" + sts).val();
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-pembatalan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'25','uid':'$uid'}"); ?>", {
				n: nop,
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
			var nop = $("#nop-" + sts).val();
			$("#monitoring-content-" + sts).html("loading ...");

			var svc = "";
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-penerbitan-sppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'26','uid':'$uid'}"); ?>", {
				n: nop,
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
			var nop = $("#nop-" + sts).val();
			//var status = $("#sel-status").val();
			var nama = $("#wp-name-" + sts).val();
			nama = nama.replace(" ", "%20");
			//var jmlBaris = $("#jml-baris").val();
			$("#monitoring-content-" + sts).html("loading ...");
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-search.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','srch':'$srch'}"); ?>&p=" + pg, {
				na: nama,
				n: nop,
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
			$("#monitoring-content-" + sts).load("view/PBB/updatePBB/svc-update-jatuh-tempo.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','GW_DBHOST':" . GW_DBHOST . ",'GW_DBNAME':" . GW_DBNAME . ",'GW_DBUSER':" . GW_DBUSER . ",'GW_DBPWD':" . GW_DBPWD . ",'GW_DBPORT':" . GW_DBPORT . "}"); ?>", {
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
				data: "q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','GW_DBHOST':" . GW_DBHOST . ",'GW_DBNAME':" . GW_DBNAME . ",'GW_DBUSER':" . GW_DBUSER . ",'GW_DBPWD':" . GW_DBPWD . ",'GW_DBPORT':" . GW_DBPORT . "}"); ?>",
				success: function(msg) {
					alert("Berhasil");
				}
			});

		}

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

		$(function() {
			$("#tabs").tabs();
			$("#jatuh-tempo").datepicker({
				dateFormat: "yy-mm-dd"
			});
			$("#tgl_jatuh_tempo").datepicker({
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
					<li><a href="#tabs-4">Update Data</a></li>
					<!-- <li><a href="#tabs-5">Kembalikan Data Ke Pendataan</a></li> -->
					<!-- <li><a href="#tabs-6">Update Tanggal Jatuh Tempo</a></li> -->
					<li><a href="#tabs-7">Penundaan Tanggal Jatuh Tempo</a></li>
				</ul>
				<div id="tabs-4">
					<fieldset>
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
										<input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSearch (4)" />
										<!--<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(1)"/>-->
										<span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
									</td>
								</tr>
							</table>
						</form>
					</fieldset>
					<div id="monitoring-content-4" class="monitoring-content">
					</div>
				</div>
				<!-- <div id="tabs-5">
        <fieldset>
            <form id="TheForm-2" method="post" action="view/PBB/updatePBB/svc-export.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" target="TheWindow">
                <table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                        <td width="73">NOP </td>
                        <td width="3">:</td>
                        <td width="60"><input type="text" id="nop-5" name="nop-5" size="30" maxlength="18"></td>
                        <td width="180">
                            <input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSearchDataSPPTFinal(5)"/>
                            <input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="toExcel(1)"/>
                            <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
                        </td>    
                    </tr>
                </table>
            </form>
        </fieldset>
        <div id="monitoring-content-5" class="monitoring-content"></div>
    </div> -->
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
					<?php echo displayPenundaanJatuhTempo(); ?>
				</div>
			</div>
		</div>
	<?php
}

function displayPenundaanJatuhTempo()
{
	global $appConfig;
	$html = "
		<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
		<div id=\"main-content\" class=\"tab1\">
			<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
			<table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
				<tr>
				  <td width=\"39%\">Tahun Pajak</td>
				  <td width=\"60%\">
					<input type=\"text\" name=\"tahun\" id=\"tahun\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"Tahun\" />
				  </td>
				</tr>
				<tr>
				  <td width=\"39%\">Tanggal Jatuh Tempo Baru</td>
				  <td width=\"60%\">
					<input type=\"text\" name=\"tgl_jatuh_tempo\" id=\"tgl_jatuh_tempo\" size=\"20\" placeholder=\"Tanggal Jatuh Tempo\"/>
				  </td>
				</tr>
				<tr>
				  <td width=\"\">NOP</td>
				  <td width=\"\" colspan=\"4\">
					<input type=\"text\" name=\"nop\" id=\"nop\" size=\"30\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"18\" placeholder=\"Masukan NOP\"/>
				  </td>
				</tr>
				<tr>
				  <td colspan=\"2\" valign=\"middle\">&nbsp;<hr/></td>
				</tr>
				<tr>
				  <td colspan=\"2\" align=\"center\" valign=\"middle\">
					<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Ubah Jatuh Tempo\" />&nbsp;
				  </td>
				</tr>
			</table>
			</form>
		</div>";
	return $html;
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

			$('#tabs-7 #btn-save').click(function() {
				var $btn = $(this);
				var thn = $('#tabs-7 #tahun').val();

				var $nop = $('#tabs-7 #nop');
				var nop = $nop.val();
				var $tgl_jatuh_tempo = $('#tabs-7 #tgl_jatuh_tempo');

				if ($tgl_jatuh_tempo.val() == '') {
					$tgl_jatuh_tempo.focus();
					alert('Silakan isi jatuh tempo');
					return false;
				}

				if ($.trim($nop.val()).length != 18) {
					$nop.focus();
					alert('Silakan Isi NOP (18 Karakter).');
					return false;
				} else {
					if (confirm('Apakah anda yakin untuk mengubah jatuh tempo untuk \nNOP ' + nop + ' ini ?') === false) return false;
				}

				$btn.attr('disabled', true);
				$.ajax({
					type: 'POST',
					url: './view/PBB/updatePBB/svc-jatuh-tempo.php',
					dataType: 'json',
					data: {
						action: $(this).attr('id'),
						nop: nop,
						thn: thn,
						tgl_jatuh_tempo: $tgl_jatuh_tempo.val(),
						appID: '<?php echo $area; ?>',
						modID: '<?php echo $m ?>',
						uID: '<?php echo $uid; ?>'
					},
					success: function(res) {
						alert(res.msg);
						$btn.removeAttr('disabled');
						setTabs(0);
					}
				});
			});

			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})
	</script>