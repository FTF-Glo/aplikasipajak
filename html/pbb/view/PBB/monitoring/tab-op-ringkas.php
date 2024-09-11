<?php
	class OpRingkas
	{
		public $label = 'OP Ringkas';
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

		public function printForm()
		{
			$cityID = $this->appConfig['KODE_KOTA'];
			$cityName = $this->appConfig['NAMA_KOTA'];
			$optionCityOP = "<option value=$cityID>$cityName</option>";

			$provID = $this->appConfig['KODE_PROVINSI'];
			$provName = $this->appConfig['NAMA_PROVINSI'];
			$optionProvOP = "<option value=$provID>$provName</option>";

			$hiddenIdInput = $nomor = '';
			$kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;
			$optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";

			$kecOP = $this->getKecamatan('', $cityID);
			// $kelOP = $this->getKelurahan('', $kecOP[0]['id']);

			foreach ($kecOP as $row) {
				$optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
			}
			// foreach ($kelOP as $row) {
			// 	$optionKelOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
			// }
			echo "
				<div class=\"row\">
					<div class=\"col-md-12\">
						<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
							<div class=\"row\">
								<!--div class=\"col-md-2\">
									<div class=\"form-group\">
										<label for=\"provinsiOP\">Provinsi: </label>
										<select name=\"propinsiOP\" id=\"propinsiOP\" class=\"form-control\">$optionProvOP</select>
									</div>
								</div>
								<div class=\"col-md-2\">
									<div class=\"form-group\">
										<label for=\"kabupatenOP\">Kabupaten</label>
										<select name=\"kabupatenOP\" id=\"kabupatenOP\" class=\"form-control\">$optionCityOP</select>
									</div>
								</div-->
								<div class=\"col-md-2\">
									<div class=\"form-group\">
										<label for=\"namawp\">Nama/Letak OP</label>
										<input type=\"text\" name=\"namawp\" class=\"form-control\" id=\"namawp\" size=\"20\" maxlength=\"30\" placeholder=\"Nama/Letak OP\"/>
									</div>
								</div>
								<div class=\"col-md-2\">
									<div class=\"form-group\">
										<label for=\"sertifikat\">Nomor Sertifikat</label>
										<input type=\"text\" name=\"sertifikat\" class=\"form-control\" id=\"sertifikat\" size=\"10\" maxlength=\"20\" placeholder=\"Nomor Sertifikat\"/>
									</div>
								</div>
								<div class=\"col-md-3\">
									<div class=\"form-group\">
										<label for=\"kecamatanOP\">Kecamatan</label>
										<select name=\"kecamatanOP\" class=\"form-control\" id=\"kecamatanOP\">
											<option value=''>Semua Kecamatan</option>
											$optionKecOP
										</select>
									</div>
								</div>
								<div class=\"col-md-3\">
									<div class=\"form-group\">
										<label for=\"kelurahanOP\">" . $this->appConfig['LABEL_KELURAHAN'] . ": </label>
										<select name=\"kelurahanOP\" id=\"kelurahanOP\" class=\"form-control\">
											<option value=''>Semua Kelurahan/Desa</option>
											$optionKelOP
										</select>
									</div>
								</div>
								<div class=\"col-md-2\">
									<div class=\"form-group\">
										<label for=\"Blok\">Blok</label>
										<div class=\"row\">
											<div class=\"col-md-4\" style=\"padding:0\">
												<input type=\"text\" name=\"blok_awal\" class=\"form-control\" id=\"blok_awal\" size=\"10\" maxlength=\"3\" placeholder=\"Awal\" onkeypress=\"return iniAngka(event, this)\"/>
											</div>
											<div class=\"col-md-4\" style=\"margin-top:8px;text-align:center\">s.d</div>
											<div class=\"col-md-4\" style=\"padding:0\">
												<input type=\"text\" name=\"blok_akhir\" class=\"form-control\" id=\"blok_akhir\" size=\"10\" maxlength=\"3\" placeholder=\"Akhir\" onkeypress=\"return iniAngka(event, this)\"/>
											</div>
										</div>
									</div>
								</div>
								
							</div>
							<div class=\"row\" style=\"margin-bottom: 20px\">
								<div class=\"col-md-6\">
									<div class=\"form-group\">
										<label for=\"nop\">NOP</label><br />
										<div class=\"col-md-1\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-1\" style=\"padding: 6px;\" name=\"nop-1\" id=\"nop-1\" placeholder=\"PR\">
										</div>
										<div class=\"col-md-1\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-2\" style=\"padding: 6px;\" name=\"nop-2\" id=\"nop-2\" placeholder=\"DTII\" maxlength=\"2\">
										</div>
										<div class=\"col-md-2\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-3\" style=\"padding: 6px;\" name=\"nop-3\" id=\"nop-3\" placeholder=\"KEC\" maxlength=\"3\">
										</div>
										<div class=\"col-md-2\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-4\" style=\"padding: 6px;\" name=\"nop-4\" id=\"nop-4\" placeholder=\"KEL\" maxlength=\"3\">
										</div>
										<div class=\"col-md-2\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-5\" style=\"padding: 6px;\" name=\"nop-5\" id=\"nop-5\" placeholder=\"BLOK\" maxlength=\"3\">
										</div>
										<div class=\"col-md-2\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-6\" style=\"padding: 6px;\" name=\"nop-6\" id=\"nop-6\" placeholder=\"NO.URUT\" maxlength=\"4\">
										</div>
										<div class=\"col-md-2\" style=\"padding: 0\">
											<input type=\"text\" class=\"form-control nop-input-7\" style=\"padding: 6px;\" name=\"nop-7\" id=\"nop-7\" placeholder=\"KODE\" maxlength=\"1\">
										</div>
										<!--<input type=\"text\" name=\"nop\" class=\"form-control\" id=\"nop\" size=\"30\" maxlength=\"18\" placeholder=\"NOP\"/>-->
									</div>
								</div>
								<div class=\"col-md-6\" style=\"margin-top:25px;text-align:right\">
									<button type=\"button\" name=\"btn-cari\" class=\"btn btn-primary btn-orange mb5\" id=\"btn-cari\">Cari</button>
									<button type=\"button\" name=\"btn-cetak-xls\" class=\"btn btn-primary btn-blue mb5\" id=\"btn-cetak-xls\">Cetak xls</button>
									<button type=\"button\" name=\"btn-cetak-pdf\" class=\"btn btn-primary bg-maka mb5\" id=\"btn-cetak-pdf\">Cetak pdf</button>
								</div>
							</div>
							<script>
								$(\".nop-input-1\").on(\"keyup\", function(){
										var len = $(this).val().length;

										let nopLengkap = $(this).val();
										
										if(!$(\".nop-input-2\").val()) $(\".nop-input-2\").val(nopLengkap.substr(2, 2));
										if(!$(\".nop-input-3\").val()) $(\".nop-input-3\").val(nopLengkap.substr(4, 3));
										if(!$(\".nop-input-4\").val()) $(\".nop-input-4\").val(nopLengkap.substr(7, 3));
										if(!$(\".nop-input-5\").val()) $(\".nop-input-5\").val(nopLengkap.substr(10, 3));
										if(!$(\".nop-input-6\").val()) $(\".nop-input-6\").val(nopLengkap.substr(13, 4));
										if(!$(\".nop-input-7\").val()) $(\".nop-input-7\").val(nopLengkap.substr(17, 1));
										if(len > 2) $(this).val(nopLengkap.substr(0, 2));

										if(len == 2) {
											$(\".nop-input-2\").focus();
										}
								});
			
								$(\".nop-input-2\").on(\"keyup\", function(){
										var len = $(this).val().length;
			
										if(len == 2) {
											$(\".nop-input-3\").focus();
										}
								});
			
								$(\".nop-input-3\").on(\"keyup\", function(){
										var len = $(this).val().length;
			
										if(len == 3) {
											$(\".nop-input-4\").focus();
										}
								});
			
								$(\".nop-input-4\").on(\"keyup\", function(){
										var len = $(this).val().length;
			
										if(len == 3) {
											$(\".nop-input-5\").focus();
										}
								});
			
								$(\".nop-input-5\").on(\"keyup\", function(){
										var len = $(this).val().length;
			
										if(len == 3) {
											$(\".nop-input-6\").focus();
										}
								});
			
								$(\".nop-input-6\").on(\"keyup\", function(){
										var len = $(this).val().length;
			
										if(len == 4) {
											$(\".nop-input-7\").focus();
										}
								});
			
								$(\".nop-input-7\").on(\"keyup\", function(){
										var len = $(this).val().length;
			
										if(len == 1) {
											$('#tabs-20 #btn-cari').trigger('click');
										}
								});
							</script>
							<div class=\"row\">
								<div class=\"col-md-12\">
									<link href=\"view/PBB/monitoring/monitoring.css\" rel=\"stylesheet\" type=\"text/css\">
									<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring \">
										<table width=\"100%\" class=\"table table-bordered\">
											<thead>
												<tr>
													<th width=9>No</th>
													<th width=80>Blok-NOP</th>
													<th>Letak OP<br/>Nama WP</th>
													<th width=9>RT<br/>RW</th>
													<th width=9>Kode ZNT</th>
													<th width=100>Luas Bumi<br/>Luas Bgn</th>
													<th width=100>NJOP Bumi<br/>NJOP Bgn</th>
													<th>Total NJOP</th>
													<th>Nomor Sertifikat</th>
												</tr>
											</thead>
											<tbody id=\"table-op-ringkas\" class=\"table-op-ringkas\"></tbody>
										</table>
									</div>
									<label style=\"margin: 10px 0;\">Total data : <span id=\"table-op-ringkas-totalRows\">0</span></label>
								</div>
							</div>
						</form>
					</div>
				</div>";
		}
	}
?>

<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script>
	$(document).ready(function() {
		$('#tabs-20 #kecamatanOP').change(function() {
			var kec = $(this).val();
			if(kec==''){
				$('#tabs-20 #kelurahanOP').html('<option value="">Semua Kelurahan/Desa</option>');
			}else{
				$.ajax({
					type: 'POST',
					url: './function/PBB/loket/svc-search-city.php',
					data: 'type=3&id=' + kec,
					success: function(msg) {
						opt = '<option value="">Semua Kelurahan/Desa</option>';
						opt += msg;
						$('#tabs-20 #kelurahanOP').html(opt);
					}
				});
			}
		});

		$('#tabs-20 #btn-cari').click(function() {
			var btn = $(this);
			var namawp = $('#tabs-20 #namawp').val();
			var serti = $('#tabs-20 #sertifikat').val();
			var kel = $('#tabs-20 #kelurahanOP').val();
			var blok_awal = $('#tabs-20 #blok_awal').val();
			var blok_akhir = $('#tabs-20 #blok_akhir').val();
			var nop1 = $('#tabs-20 #nop-1').val();
			var nop2 = $('#tabs-20 #nop-2').val();
			var nop3 = $('#tabs-20 #nop-3').val();
			var nop4 = $('#tabs-20 #nop-4').val();
			var nop5 = $('#tabs-20 #nop-5').val();
			var nop6 = $('#tabs-20 #nop-6').val();
			var nop7 = $('#tabs-20 #nop-7').val();

			if(blok_awal!='' && blok_akhir=='') alert('Blok Akhir');
			if(blok_akhir!='' && blok_awal=='') alert('Blok awal');

			if((blok_akhir=='' && blok_awal=='') && serti=='') alert('inputan sertifikat atau blok');
			if((blok_akhir!='' || blok_awal!='') && kel=='') alert('Pilih kelurahan atau desa');

			var postData = {
				action: $(this).attr('id'),
				nama: namawp,
				serti: serti,
				kel: kel,
				blok_awal: blok_awal,
				blok_akhir: blok_akhir,
				nop1: nop1,
				nop2: nop2,
				nop3: nop3,
				nop4: nop4,
				nop5: nop5,
				nop6: nop6,
				nop7: nop7,
				q: '<?php echo base64_encode("{'a':'$a', 'm':'$m', 'tab':'20', 'n':'1', 'u':'$data->uname'}") ?>'
			};

			btn.attr('disabled', true).val("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
			$.ajax({
				type: 'POST',
				url: './view/PBB/monitoring/svc-monitoring-op-ringkas.php',
				dataType: 'json',
				data: postData,
				success: function(res) {
					btn.removeAttr('disabled').val('Cari');
					$('#table-op-ringkas').html(res.table);
					$('#table-op-ringkas-totalRows').html(res.totalRows);

					if (postData.action == 'btn-cari') {
						if ($.trim(res.table) == "") alert('data tidak ditemukan!');
					}
				},
				error: function(res) {
					console.log('Error:', res);
					btn.removeAttr('disabled').val('Cari');
				}
			});
		});

		$('#tabs-20 #btn-cetak-xls, #tabs-20 #btn-cetak-pdf').click(function() {
			var $btn = $(this);
			var kel = $('#tabs-20 #kelurahanOP').val();
			var $blok_awal = $('#tabs-20 #blok_awal');
			var $blok_akhir = $('#tabs-20 #blok_akhir');
			//var nop = $('#tabs-20 #nop').val();
			var nop1 = $('#tabs-20 #nop-1').val();
			var nop2 = $('#tabs-20 #nop-2').val();
			var nop3 = $('#tabs-20 #nop-3').val();
			var nop4 = $('#tabs-20 #nop-4').val();
			var nop5 = $('#tabs-20 #nop-5').val();
			var nop6 = $('#tabs-20 #nop-6').val();
			var nop7 = $('#tabs-20 #nop-7').val();

			if ($.trim($blok_awal.val()) == "" || $.trim($blok_awal.val()).length < 3) {
				alert("Blok Awal harus diisi 3 karakter!");
				$blok_awal.focus();
				return false;
			}

			if ($.trim($blok_akhir.val()) == "" || $.trim($blok_akhir.val()).length < 3) {
				alert("Blok Akhir harus diisi 3 karakter!");
				$blok_akhir.focus();
				return false;
			}

			var blok_awal = kel + $blok_awal.val();
			var blok_akhir = kel + $blok_akhir.val();

			var postData = {
				blok_awal: blok_awal,
				blok_akhir: blok_akhir,
				//nop: nop,
				nop1: nop1,
				nop2: nop2,
				nop3: nop3,
				nop4: nop4,
				nop5: nop5,
				nop6: nop6,
				nop7: nop7,
				prop: $('#propinsiOP option:selected').text(),
				kota: $('#kabupatenOP option:selected').text(),
				kec: $('#kecamatanOP option:selected').text(),
				kel: $('#kelurahanOP option:selected').text(),
				kd_prop: $('#propinsiOP').val(),
				kd_kota: $('#kabupatenOP').val(),
				kd_kec: $('#kecamatanOP').val(),
				kd_kel: $('#kelurahanOP').val(),
				q: '<?php echo base64_encode("{'a':'$a', 'm':'$m', 'tab':'20', 'n':'1', 'u':'$data->uname'}") ?>'
			};

			var url = '';
			if ($btn.attr('id') == 'btn-cetak-xls') url = 'svc-toexcel-op-ringkas.php';
			else if ($btn.attr('id') == 'btn-cetak-pdf') url = 'svc-topdf-op-ringkas.php';

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