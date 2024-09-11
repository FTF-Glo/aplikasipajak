<?php
class RekapPelayanan
{
	public $label = 'Rekap Pelayanan';
	private $appConfig;

	public function __construct($appConfig)
	{
		$this->appConfig = $appConfig;
	}

	public function getKecamatan($idKec = '', $idKab = "")
	{
		global $DBLink;

		$qwhere = "";
		if ($idKab) {
			$qwhere = " WHERE CPC_TKC_KKID='$idKab'";
		} else if ($idKec) {
			$qwhere = " WHERE CPC_TKC_ID='$idKec'";
		}

		$qry = "select * from cppmod_tax_kecamatan " . $qwhere;
		$res = mysqli_query($DBLink, $qry);
		if (!$res) {
			echo $qry . "<br>";
			echo mysqli_error($DBLink);
		} else {
			$data = array();
			while ($row = mysqli_fetch_assoc($res)) {
				$tmp = array(
					'id' => $row['CPC_TKC_ID'],
					'pid' => $row['CPC_TKC_KKID'],
					'name' => $row['CPC_TKC_KECAMATAN']
				);
				$data[] = $tmp;
			}
			return $data;
		}
	}

	public function getKelurahan($idKel = '', $idKec = "")
	{
		global $DBLink;

		$qwhere = "";
		if ($idKec) {
			$qwhere = " WHERE CPC_TKL_KCID='$idKec'";
		} else if ($idKel) {
			$qwhere = " WHERE CPC_TKL_ID='$idKel'";
		}

		$qry = "select * from cppmod_tax_kelurahan " . $qwhere;
		$res = mysqli_query($DBLink, $qry);
		if (!$res) {
			echo $qry . "<br>";
			echo mysqli_error($DBLink);
		} else {
			$data = array();
			while ($row = mysqli_fetch_assoc($res)) {
				$tmp = array(
					'id' => $row['CPC_TKL_ID'],
					'pid' => $row['CPC_TKL_KCID'],
					'name' => $row['CPC_TKL_KELURAHAN']
				);
				$data[] = $tmp;
			}
			return $data;
		}
	}

	public function getJenisBerkas()
	{
		global $DBLink;

		$qry = "SELECT * FROM cppmod_pbb_services_type WHERE SERVICE_TYPE_ID IN ('1','2','4','5')";
		$res = mysqli_query($DBLink, $qry);
		if (!$res) {
			echo $qry . "<br>";
			echo mysqli_error($DBLink);
		} else {
			$data = array();
			while ($row = mysqli_fetch_assoc($res)) {
				$tmp = array(
					'id' => $row['SERVICE_TYPE_ID'],
					'desc' => $row['SERVICE_TYPE_DESC'],
				);
				$data[] = $tmp;
			}
			return $data;
		}
	}

	public function printForm()
	{
		$optionjnsBerkas = null;
		$optionThn = null;
		$jnsBerkas = $this->getJenisBerkas();

		foreach ($jnsBerkas as $row) {
			$optionjnsBerkas .= "<option value=" . $row['id'] . ">" . $row['desc'] . "</option>";
		}
		$thnTagihan = $this->appConfig['tahun_tagihan'];
		for ($i = 0; $i <= 5; $i++) {
			$optionThn .= "<option value=" . ($thnTagihan - $i) . ">" . ($thnTagihan - $i) . "</option>";
		}
		echo '
			<div class=row>
				<div class="col-md-12">
					<form name="form-penerimaan" id="form-penerimaan" method="post" action="">
						<div class="row">
							<div class="col-md-2">
								<div class="form-group">
									<label for="jnsBerkas">Jenis Pelayanan</label>
									<select name="jnsBerkas" class="form-control" id="jnsBerkas">' . $optionjnsBerkas . '</select>
								</div>
							</div>
							<div class="col-md-5 row">
								<div class="col-md-4">
									<div class="form-group">
										<label for="thn">Tahun Pajak</label>
										<select name="thn" class="form-control" id="thn">' . $optionThn . '</select>
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<label for="monitoring-rekap-pelayanan-from-date">Tgl Pelayanan</label>
										<div class=row>
											<div class="col-md-5" style="padding-right:0">
												<input type="text" class="form-control" id="monitoring-rekap-pelayanan-from-date" size="10" value="' . date('Y-m-d') . '">
											</div>
											<div class="col-md-2" style="margin-top:5px;padding-left:0;padding-right:0;text-align:center">s/d</div>
											<div class="col-md-5" style="padding-left:0">
												<input type="text" class="form-control" id="monitoring-rekap-pelayanan-to-date" size="10" value="' . date('Y-m-d') . '">
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select id="kecamatan-rekap-pelayanan" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Desa: </label>
                                    <select id="kelurahan-rekap-pelayanan" class="form-control"></select>
                                </div>
                            </div>
							<div class="col-md-12" style="margin-bottom:25px;text-align:right">
								<button type="button" name="btn-cari" class="btn btn-primary btn-orange" id="btn-cari">Cari</button>
								<button type="button" name="btn-cetak-xls" class="btn btn-primary btn-blue" id="btn-cetak-xls">Cetak xls</button>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<link href="view/PBB/monitoring/monitoring.css?v0001" rel="stylesheet" type="text/css">
								<div id="frame-tbl-monitoring-rekap-pelayanan" class="monitoring-content">
									
								</div>
								<label style="margin: 10px 0;">Total data : <span id="table-rekap-pelayanan-totalRows">0</span></label>
							</div>
						</div>
					</form>
				</div>
			</div>';

		echo '
				<script>
					$("select#kecamatan-rekap-pelayanan").change(function () {
						showKelurahan("rekap-pelayanan");
					})
				</script>
			';
	}
}


?>
<style>
	.ui-datepicker-title {
		color: #000 !important
	}
</style>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script>
	$(document).ready(function() {

		function getDate(element) {
			var date;
			try {
				date = $.datepicker.parseDate(dateFormat, element.value);
			} catch (error) {
				date = null;
			}
			return date;
		}

		var dateFormat = "yy-mm-dd",
			from_date = $("#tabs-22 #monitoring-rekap-pelayanan-from-date").datepicker({
				dateFormat: dateFormat,
				changeYear: true,
				changeMonth: true,
				showButtonPanel: true
			}).on("change", function() {
				to_date.datepicker("option", "minDate", getDate(this));
			}),
			to_date = $("#tabs-22 #monitoring-rekap-pelayanan-to-date").datepicker({
				dateFormat: dateFormat,
				changeYear: true,
				changeMonth: true,
				showButtonPanel: true
			}).on("change", function() {
				from_date.datepicker("option", "maxDate", getDate(this));
			});

		$('#tabs-22 #btn-cari').click(function() {
			var $btn = $(this);
			var jnsBerkas = $('#tabs-22 #jnsBerkas').val();
			var thn = $('#tabs-22 #thn').val();

			var kecamatan = $("#kecamatan-rekap-pelayanan").val();
			var kelurahan = $("#kelurahan-rekap-pelayanan").val();

			if (jnsBerkas == '1') var svcLink = './view/PBB/monitoring/svc-monitoring-rekap-pelayanan-opbaru.php';
			else if (jnsBerkas == '2') var svcLink = './view/PBB/monitoring/svc-monitoring-rekap-pelayanan-pemecahan.php';
			else if (jnsBerkas == '4') var svcLink = './view/PBB/monitoring/svc-monitoring-rekap-pelayanan-mutasi.php';
			else if (jnsBerkas == '5') var svcLink = './view/PBB/monitoring/svc-monitoring-rekap-pelayanan-perubahan.php';

			var postData = {
				action: $(this).attr('id'),
				jnsBerkas: jnsBerkas,
				thn: thn,
				fromDate: from_date.val(),
				toDate: to_date.val(),
				kecamatan: kecamatan,
				kelurahan: kelurahan,
				q: '<?php echo base64_encode("{'a':'$a', 'm':'$m', 'tab':'20', 'n':'1', 'u':'$data->uname'}") ?>'
			};
			// alert(jnsBerkas);
			$("#frame-tbl-monitoring-rekap-pelayanan").html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
			$btn.attr('disabled', true).val('Loading...');
			$.ajax({
				type: 'POST',
				url: svcLink,
				dataType: 'json',
				data: postData,
				success: function(res) {
					$btn.removeAttr('disabled').val('Cari');
					$('#frame-tbl-monitoring-rekap-pelayanan').html(res.table);
					$('#table-rekap-pelayanan-totalRows').html(res.totalRows);

					if (postData.action == 'btn-cari') {
						if ($.trim(res.table) == "") alert('data tidak ditemukan!');
					}
				},
				error: function(res) {
					console.log('Error:', res);
					$btn.removeAttr('disabled').val('Cari');
				}
			});
		});

		$('#tabs-22 #btn-cetak-xls, #tabs-22 #btn-cetak-pdf').click(function() {
			var $btn = $(this);
			var jnsBerkas = $('#tabs-22 #jnsBerkas').val();
			var thn = $('#tabs-22 #thn').val();
			var kecamatan = $("#kecamatan-rekap-pelayanan").val();
			var kelurahan = $("#kelurahan-rekap-pelayanan").val();

			var postData = {
				thn: thn,
				fromDate: from_date.val(),
				toDate: to_date.val(),
				jnsBerkas: jnsBerkas,
				kecamatan: kecamatan,
				kelurahan: kelurahan,
				q: '<?php echo base64_encode("{'a':'$a', 'm':'$m', 'tab':'22', 'n':'1', 'u':'$data->uname'}") ?>'
			};

			var url = '';
			if ($btn.attr('id') == 'btn-cetak-xls') {
				if (jnsBerkas == '1') url = 'svc-toexcel-rekap-pelayanan-opbaru.php';
				else if (jnsBerkas == '2') url = 'svc-toexcel-rekap-pelayanan-pemecahan.php';
				else if (jnsBerkas == '4') url = 'svc-toexcel-rekap-pelayanan-mutasi.php';
				else if (jnsBerkas == '5') url = 'svc-toexcel-rekap-pelayanan-perubahan.php';
			} else if ($btn.attr('id') == 'btn-cetak-pdf') url = 'svc-topdf-rekap-pelayanan.php';

			post('view/PBB/monitoring/' + url, postData);
		});
	});

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