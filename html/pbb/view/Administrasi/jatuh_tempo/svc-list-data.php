<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Administrasi' . DIRECTORY_SEPARATOR . 'jatuh_tempo', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

/* inisiasi parameter */
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

if ($q->a) {
	$a = $q->a;
} else {
	$a = $_POST['a'];
}
$m = $q->m;
$n = $q->n;
$tab 	= $q->tab;
$uname 	= $q->u;
$uid 	= $q->uid;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

// print_r($_POST); exit;

/*proses simpan / tampil data */
if (isset($_POST['action'])) {

	$id					 	= $_POST['id'];
	$tgl_awal_penetapan 	= $_POST['tgl_awal_penetapan'];
	$tgl_akhir_penetapan 	= $_POST['tgl_akhir_penetapan'];
	$tgl_jatuh_tempo	 	= $_POST['tgl_jatuh_tempo'];

	if ($_POST['action'] == 'loadData') {
		$query = "SELECT * FROM cppmod_pbb_tgl_jatuh_tempo";
		$res = mysqli_query($DBLink, $query);
		$rowsData = "";
		$no = 0;

		while ($row = mysqli_fetch_object($res)) {
			$class = ($no % 2 == 0) ? 'tdbody1' : 'tdbody2';

			$hapus = "<a href=\"javascript:void(0)\" onclick=\"javascript:hapus('{$row->CPM_TGL_ID}')\">Hapus</a>";
			$ubah = "<a href=\"javascript:void(0)\" onclick=\"javascript:ubah_form('{$row->CPM_TGL_ID}')\">Ubah</a>";
			$rowsData .= "<tr>
				<td align='center' class='" . $class . "'>" . $row->CPM_TGL_ID . "</td>
				<td class='" . $class . "'>" . $row->CPM_TGL_PENETAPAN_AWAL . "</td>
				<td class='" . $class . "'>" . $row->CPM_TGL_PENETAPAN_AKHIR . "</td>
				<td class='" . $class . "'>" . $row->CPM_TGL_JATUH_TEMPO . "</td>
				<td align='center' class='" . $class . "'>" . $ubah . "</td>
				<td align='center' class='" . $class . "'>" . $hapus . "</td>
			</tr>";
		}
		$response['totalRows'] = $no;
		$response['table'] = $rowsData;
	} elseif ($_POST['action'] == 'hapusData') {
		$query = sprintf("DELETE FROM cppmod_pbb_tgl_jatuh_tempo WHERE CPM_TGL_ID = '%s'", $id);
		if (!mysqli_query($DBLink, $query)) {
			$response['msg'] = 'Data gagal dihapus, silakan coba lagi.';
		} else {
			$response['msg'] = 'Data berhasil dihapus.';
		}
	} elseif ($_POST['action'] == 'tambahData') {
		$query = "INSERT INTO cppmod_pbb_tgl_jatuh_tempo (CPM_TGL_PENETAPAN_AWAL,CPM_TGL_PENETAPAN_AKHIR,CPM_TGL_JATUH_TEMPO)
			VALUES ('{$tgl_awal_penetapan}','{$tgl_akhir_penetapan}','{$tgl_jatuh_tempo}')
		";
		$sql = mysqli_query($DBLink, $query);

		$response['msg'] = "Data berhasil disimpan.";
	} elseif ($_POST['action'] == 'ubahData') {
		$query = "UPDATE cppmod_pbb_tgl_jatuh_tempo
					SET CPM_TGL_PENETAPAN_AWAL = '{$tgl_awal_penetapan}',
					 CPM_TGL_PENETAPAN_AKHIR = '{$tgl_akhir_penetapan}',
					 CPM_TGL_JATUH_TEMPO = '{$tgl_jatuh_tempo}'
					WHERE CPM_TGL_ID = '{$id}' ";
		// echo $query; exit;
		$sql = mysqli_query($DBLink, $query);
		$response['msg'] = "Data berhasil disimpan.";
	}
	// mysqli_close($DBLink);
	exit($json->encode($response));
}

$html = "
<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab1\">
	<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
		<div class=\"row\">
			<div class=\"col-md-4\">
				<input type=\"button\" class=\"btn btn-primary btn-orange mb15\" id=\"btn_tambah\" value=\"Tambah\" onclick=\"tambah_form()\"></input>
			</div>
		</div>
		<div class=\"row\">
			<div class=\"col-md-6\">
				<div class=\"table-responsive\">
					<table class=\"table table-bordered \">
						<thead>
							<tr>
								<td class=\"tdheader\">ID</td>
								<td class=\"tdheader\">Tanggal Penetapan Awal</td>
								<td class=\"tdheader\">Tanggal Penetapan Akhir</td>
								<td class=\"tdheader\">Tanggal Jatuh Tempo</td>
								<td colspan=\"2\" align=\"center\" class=\"tdheader\">Aksi</td>
							</tr>
						</thead>
						<tbody id=\"table-pengurangan\" class=\"table-pengurangan\"></tbody>
					</table>
				</div>
			</div>
		</div>
	</form>
</div>";
echo $html;
?>
<style>
	label,
	input {
		display: block;
	}

	input.text {
		margin-bottom: 12px;
		width: 95%;
		padding: .4em;
	}

	fieldset {
		padding: 0;
		border: 0;
		margin-top: 25px;
	}

	h1 {
		font-size: 1.2em;
		margin: .6em 0;
	}

	div#users-contain {
		width: 350px;
		margin: 20px 0;
	}

	div#users-contain table {
		margin: 1em 0;
		border-collapse: collapse;
		width: 100%;
	}

	div#users-contain table td,
	div#users-contain table th {
		border: 1px solid #eee;
		padding: .6em 10px;
		text-align: left;
	}

	.ui-dialog .ui-state-error {
		padding: .3em;
	}

	.validateTips {
		border: 1px solid transparent;
		padding: 0.3em;
	}
</style>
<div id="dialog-form" title="Ubah Tanggal Jatuh Tempo">
</div>
<div id="form-tambah-jatuhtempo" title="Tambah Tanggal Jatuh Tempo">
</div>
<script>
	$(document).ready(function() {
		$("#tgl_berlaku_awal").datepicker({
			dateFormat: 'dd-mm-yy'
		});
		$("#tgl_berlaku_akhir").datepicker({
			dateFormat: 'dd-mm-yy'
		});

		loadData();
	});

	function loadData() {
		var postData = {
			action: 'loadData',
			a: '<?php echo $a ?>',
			m: '<?php echo $m ?>',
		}
		$.ajax({
			type: 'POST',
			url: 'view/Administrasi/jatuh_tempo/svc-list-data.php',
			data: postData,
			dataType: 'json',
			success: function(res) {
				$('#table-pengurangan').html(res.table);
				$('#table-pengurangan-totalRows').html(res.totalRows);
			}
		});
	}

	function ubah(id) {
		var postData = {
			action: 'ubahData',
			id: id,
			tgl_awal_penetapan: $("#tgl_awal_penetapan").val(),
			tgl_akhir_penetapan: $("#tgl_akhir_penetapan").val(),
			tgl_jatuh_tempo: $("#tgl_jatuh_tempo").val(),
			q: '<?php echo $_REQUEST['q'] ?>'
		};
		$("<div>Apakah anda yakin untuk menyimpan data ini?</div>").dialog({
			modal: true,
			buttons: {
				Ya: function() {
					$.ajax({
						type: 'POST',
						url: 'view/Administrasi/jatuh_tempo/svc-list-data.php',
						data: postData,
						dataType: 'json',
						success: function(res) {
							// alert(res.msg);
							$("<div>" + res.msg + "</div>").dialog({
								modal: true,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
									}
								}
							});
							loadData();
						}
					});
					$(this).dialog("close");
				},
				Tidak: function() {
					$(this).dialog("close");
				}
			}
		});

	}

	function tambah_form() {
		var postData = {
			q: '<?php echo $_REQUEST['q'] ?>'
		};

		$.ajax({
			type: 'POST',
			url: 'view/Administrasi/jatuh_tempo/svc-form-tambah.php',
			data: postData,
			dataType: 'json',
			success: function(res) {
				$('#form-tambah-jatuhtempo').html(res.table);
				dialog = $('#form-tambah-jatuhtempo').dialog({
					autoOpen: false,
					height: 370,
					width: 400,
					modal: true,
					buttons: {
						Simpan: function() {
							tambah();
						},
						Keluar: function() {
							dialog.dialog('close');
						}
					}
				});
				dialog.dialog('open');
			}
		});
	}

	function tambah() {
		var postData = {
			action: 'tambahData',
			tgl_awal_penetapan: $("#tgl_awal_penetapan").val(),
			tgl_akhir_penetapan: $("#tgl_akhir_penetapan").val(),
			tgl_jatuh_tempo: $("#tgl_jatuh_tempo").val(),
			q: '<?php echo $_REQUEST['q'] ?>'
		};
		$("<div>Apakah anda yakin untuk menyimpan data ini?</div>").dialog({
			modal: true,
			buttons: {
				Ya: function() {
					$.ajax({
						type: 'POST',
						url: 'view/Administrasi/jatuh_tempo/svc-list-data.php',
						data: postData,
						dataType: 'json',
						success: function(res) {
							// alert(res.msg);
							$("<div>" + res.msg + "</div>").dialog({
								modal: true,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
									}
								}
							});
							loadData();
						}
					});
					$(this).dialog("close");
				},
				Tidak: function() {
					$(this).dialog("close");
				}
			}
		});

	}

	function ubah_form(id) {
		var postData = {
			id: id,
			q: '<?php echo $_REQUEST['q'] ?>'
		};

		$.ajax({
			type: 'POST',
			url: 'view/Administrasi/jatuh_tempo/svc-form-ubah.php',
			data: postData,
			dataType: 'json',
			success: function(res) {
				$('#dialog-form').html(res.table);
				dialog = $("#dialog-form").dialog({
					autoOpen: false,
					height: 370,
					width: 400,
					modal: true,
					buttons: {
						Simpan: function() {
							ubah(res.id);
						},
						Keluar: function() {
							dialog.dialog("close");
						}
					}
				});
				dialog.dialog("open");
			}
		});
	}

	function hapus(id) {
		var postData = {
			action: 'hapusData',
			id: id,
			q: '<?php echo $_REQUEST['q'] ?>'
		};
		$("<div>Apakah anda yakin untuk menghapus data ini?</div>").dialog({
			modal: true,
			buttons: {
				Ya: function() {
					$.ajax({
						type: 'POST',
						url: 'view/Administrasi/jatuh_tempo/svc-list-data.php',
						data: postData,
						dataType: 'json',
						success: function(res) {
							$("<div>" + res.msg + "</div>").dialog({
								modal: true,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
									}
								}
							});
							loadData();
							loadData();
						}
					});
					$(this).dialog("close");
				},
				Tidak: function() {
					$(this).dialog("close");
				}
			}
		});

	}

	function post(path, params, method) {
		method = method || "post";
		var target = '_blank';
		var form = document.createElement("form");
		form.setAttribute("method", method);
		form.setAttribute("action", path);
		form.setAttribute("target", target);

		for (var key in params) {
			if (params.hasOwnProperty(key)) {
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", key);
				hiddenField.setAttribute("value", params[key]);
				form.appendChild(hiddenField);
			}
		}
		document.body.appendChild(form);
		form.submit();
	}

	function iniAngka(evt, x) {
		var charCode = (evt.which) ? evt.which : event.keyCode;
		if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
			return true;
		} else {
			alert('Input hanya boleh angka!');
			return false;
		}
	}
</script>