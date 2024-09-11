<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Administrasi' . DIRECTORY_SEPARATOR . 'njoptkp', '', dirname(__FILE__))) . '/';
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
	$nilai_bawah 			= $_POST['nilai_bawah'];
	$nilai_atas 			= $_POST['nilai_atas'];
	$njoptkp	 			= $_POST['njoptkp'];

	if ($_POST['action'] == 'loadData') {
		$query = "SELECT * FROM cppmod_pbb_njoptkp";
		$res = mysqli_query($DBLink, $query);
		$rowsData = "";
		$no = 0;

		while ($row = mysqli_fetch_object($res)) {
			$class = ($no % 2 == 0) ? 'tdbody1' : 'tdbody2';

			$hapus 	= "<a href=\"javascript:void(0)\" onclick=\"javascript:hapus('" . $row->CPM_ID . "')\">Hapus</a>";
			$ubah 	= "<a href=\"javascript:void(0)\" onclick=\"javascript:ubah_form('" . $row->CPM_ID . "')\">Ubah</a>";

			$rowsData .= "<tr>
				<td align='center' class='" . $class . "'>" . $row->CPM_ID . "</td>
				<td align='right' class='" . $class . "'>" . $row->CPM_NILAI_BAWAH . "</td>
				<td align='right' class='" . $class . "'>" . $row->CPM_NILAI_ATAS . "</td>
				<td align='right' class='" . $class . "'>" . $row->CPM_NJOPTKP . "</td>
				<td align='center' class='" . $class . "'>" . $ubah . "</td>
				<td align='center' class='" . $class . "'>" . $hapus . "</td>
			</tr>";
		}
		$response['totalRows'] = $no;
		$response['table'] = $rowsData;
	} elseif ($_POST['action'] == 'hapusData') {
		$query = sprintf("DELETE FROM cppmod_pbb_njoptkp WHERE CPM_ID = '%s'", $id);
		// echo $query; exit;
		if (!mysqli_query($DBLink, $query)) {
			$response['msg'] = 'Data gagal dihapus, silakan coba lagi.';
		} else {
			$response['msg'] = 'Data berhasil dihapus.';
		}
	} elseif ($_POST['action'] == 'tambahData') {
		$query = "INSERT INTO cppmod_pbb_njoptkp (CPM_NILAI_BAWAH,CPM_NILAI_ATAS,CPM_NJOPTKP)
			VALUES ('{$nilai_bawah}','{$nilai_atas}','{$njoptkp}')
		";
		$sql = mysqli_query($DBLink, $query);

		$response['msg'] = "Data berhasil disimpan.";
	} elseif ($_POST['action'] == 'ubahData') {
		$query = "UPDATE cppmod_pbb_njoptkp
					SET CPM_NILAI_BAWAH = '{$nilai_bawah}',
					 CPM_NILAI_ATAS = '{$nilai_atas}',
					 CPM_NJOPTKP = '{$njoptkp}'
					WHERE CPM_ID = '{$id}' ";
		// echo $query; exit;
		$sql = mysqli_query($DBLink, $query);
		$response['msg'] = "Data berhasil disimpan.";
	}
	// mysqli_close($con);
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
					<table class=\"table table-bordered\">
						<thead>
							<tr>
								<td class=\"tdheader\">ID</td>
								<td class=\"tdheader\">Nilai Bawah</td>
								<td class=\"tdheader\">Nilai Atas</td>
								<td class=\"tdheader\">NJOPTKP</td>
								<td class=\"tdheader\" colspan=\"2\" align=\"center\">Aksi</td>
							</tr>
						</thead>
						<tbody id=\"table-njoptkp\" class=\"table-njoptkp\"></tbody>
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
</style>
<div id="form-ubah-njoptkp" title="Ubah NJOPTKP">
</div>
<div id="form-tambah-njoptkp" title="Tambah NJOPTKP">
</div>
<script>
	$(document).ready(function() {
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
			url: 'view/Administrasi/njoptkp/svc-list-data.php',
			data: postData,
			dataType: 'json',
			success: function(res) {
				$('#table-njoptkp').html(res.table);
				$('#table-njoptkp-totalRows').html(res.totalRows);
			}
		});
	}

	function tambah_form() {
		var postData = {
			q: '<?php echo $_REQUEST['q'] ?>'
		};

		$.ajax({
			type: 'POST',
			url: 'view/Administrasi/njoptkp/svc-form-tambah.php',
			data: postData,
			dataType: 'json',
			success: function(res) {
				$('#form-tambah-njoptkp').html(res.table);
				dialog = $('#form-tambah-njoptkp').dialog({
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
			nilai_bawah: $("#nilai_bawah").val(),
			nilai_atas: $("#nilai_atas").val(),
			njoptkp: $("#njoptkp").val(),
			q: '<?php echo $_REQUEST['q'] ?>'
		};
		$("<div>Apakah anda yakin untuk menyimpan data ini?</div>").dialog({
			modal: true,
			buttons: {
				Ya: function() {
					$.ajax({
						type: 'POST',
						url: 'view/Administrasi/njoptkp/svc-list-data.php',
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

	function ubah(id) {
		var postData = {
			action: 'ubahData',
			id: id,
			nilai_bawah: $("#nilai_bawah").val(),
			nilai_atas: $("#nilai_atas").val(),
			njoptkp: $("#njoptkp").val(),
			q: '<?php echo $_REQUEST['q'] ?>'
		};
		$("<div>Apakah anda yakin untuk menyimpan data ini?</div>").dialog({
			modal: true,
			buttons: {
				Ya: function() {
					$.ajax({
						type: 'POST',
						url: 'view/Administrasi/njoptkp/svc-list-data.php',
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
			url: 'view/Administrasi/njoptkp/svc-form-ubah.php',
			data: postData,
			dataType: 'json',
			success: function(res) {
				$('#form-ubah-njoptkp').html(res.table);
				dialog = $("#form-ubah-njoptkp").dialog({
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
						url: 'view/Administrasi/njoptkp/svc-list-data.php',
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
			$("<div>Input hanya boleh angka</div>").dialog({
				modal: true,
				buttons: {
					Ok: function() {
						$(this).dialog("close");
					}
				}
			});
			return false;
		}
	}
</script>