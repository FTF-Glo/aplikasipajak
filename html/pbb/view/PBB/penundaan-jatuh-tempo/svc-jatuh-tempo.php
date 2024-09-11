<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penundaan-jatuh-tempo', '', dirname(__FILE__))) . '/';
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
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$find 	= @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab 	= $q->tab;
$uname 	= $q->u;
$uid 	= $q->uid;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);


/*proses simpan / delete pembentukkan */
if (isset($_POST['action'])) {
	// print_r($_REQUEST);exit();

	$response['msg'] = 'Proses data berhasil.';

	$kec = $_POST['kec'];
	$kel = $_POST['kel'];
	$nop1 = $_POST['nop1'];
	$nop2 = $_POST['nop2'];
	$nop3 = $_POST['nop3'];
	$nop4 = $_POST['nop4'];
	$nop5 = $_POST['nop5'];
	$nop6 = $_POST['nop6'];
	$nop7 = $_POST['nop7'];
	$nopto = $nop1 . "" . $nop2 . "" . $nop3 . "" . $nop4 . "" . $nop5 . "" . $nop6 . "" . $nop7;
	$thn = $_POST['thn'];
	$csv = $_POST['csv'];
	$tgl_jatuh_tempo = $_POST['tgl_jatuh_tempo'];
	$csv = preg_replace("/\d+/", "'$0'", $csv);
	if ($_POST['action'] == 'btn-save') {
		$GW_DBHOST = $appConfig['GW_DBHOST'];
		$GW_DBUSER = $appConfig['GW_DBUSER'];
		$GW_DBPWD = $appConfig['GW_DBPWD'];
		$GW_DBNAME = $appConfig['GW_DBNAME'];
		$SW_DBNAME = $appConfig['ADMIN_SW_DBNAME'];
		$GWDBLink = mysqli_connect($GW_DBHOST, $GW_DBUSER, $GW_DBPWD, $GW_DBNAME) or die(mysqli_error($DBLink));
		//mysql_select_db($GW_DBNAME,$GWDBLink);
		// var_dump($_REQUEST); exit();
		if (empty($nopto)) {
			if ($csv == true) {

				$where = " NOP IN ({$csv}) ";
			} else {

				$nop = $appConfig['KODE_KOTA'];
				$nop = empty($kel) ? $nop : $kel;
				$nop = empty($kec) ? $nop : $kec;
				$where = " NOP LIKE '{$nop}%' ";
			}
		} else {
			$where = " SUBSTR(NOP, 1, 2) = '{$nop1}' AND SUBSTR(NOP, 3, 2) = '{$nop2}' AND SUBSTR(NOP, 5, 3) = '{$nop3}' AND SUBSTR(NOP, 8, 3) = '{$nop4}' AND SUBSTR(NOP, 11, 3) = '{$nop5}' AND SUBSTR(NOP, 14, 4) = '{$nop6}' AND SUBSTR(NOP, 18, 1) = '{$nop7}' ";
		}
		$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";
		$qBuku = "";
		if ($buku != 0) {
			switch ($buku) {
				case 1:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
					break;
				case 12:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
					break;
				case 123:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
					break;
				case 1234:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
					break;
				case 12345:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
					break;
				case 2:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
					break;
				case 23:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
					break;
				case 234:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
					break;
				case 2345:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
					break;
				case 3:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
					break;
				case 34:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
					break;
				case 345:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
					break;
				case 4:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
					break;
				case 45:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
					break;
				case 5:
					$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
					break;
			}
		}

		#update data pbb sppt (GW)
		$param = array(
			"SPPT_TANGGAL_JATUH_TEMPO = '{$tgl_jatuh_tempo}'"
		);

		$sets = implode(',', $param);
		if ($thn < $appConfig['tahun_tagihan']) {
			$query1 = "UPDATE {$SW_DBNAME}.cppmod_pbb_sppt_cetak_{$thn} SET {$sets} WHERE SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} ;";
		} else {
			$query1 = "UPDATE {$SW_DBNAME}.cppmod_pbb_sppt_current SET {$sets} WHERE SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} ;";
		}
		$query = "update PBB_SPPT set {$sets} where SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) $qBuku;";
		// echo $query."<br>";
		// echo $query1;
		// exit;
		$res = mysqli_query($GWDBLink, $query);
		$res1 = mysqli_query($GWDBLink, $query1);

		if ($res) {
			$query = "select count(*) as TOTAL from PBB_SPPT where SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL $qBuku)";
			// echo $query;exit();
			$res = mysqli_query($GWDBLink, $query);
			$data = mysqli_fetch_assoc($res);
			$response['msg'] = 'Data tahun ' . $thn . ' berhasil diproses sejumlah : ' . $data['TOTAL'];
		} else {
			$response['msg'] = 'Data gagal diproses';
		}
	}

	exit($json->encode($response));
}


/*form penbentukan */
function getKecamatan($idKec = '', $idKab = "")
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
			$digit3 = " - " . substr($row['CPC_TKC_ID'], 4, 3);
			$tmp = array(
				'id' => $row['CPC_TKC_ID'],
				'pid' => $row['CPC_TKC_KKID'],
				'name' => $row['CPC_TKC_KECAMATAN'] . $digit3
			);
			$data[] = $tmp;
		}
		return $data;
	}
}

function getKelurahan($idKel = '', $idKec = "")
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
			$digit3 = " - " . substr($row['CPC_TKL_ID'], 7, 3);
			$tmp = array(
				'id' => $row['CPC_TKL_ID'],
				'pid' => $row['CPC_TKL_KCID'],
				'name' => $row['CPC_TKL_KELURAHAN'] . $digit3
			);
			$data[] = $tmp;
		}
		return $data;
	}
}

function getTahun($awal = 0, $akhir = 0)
{
	$awal = $awal == 0 ? date('Y') - 5 : $awal;
	$akhir = $akhir == 0 ? date('Y') : $akhir;

	$optTahun = "";
	for ($x = $akhir; $x >= $awal; $x--) {
		$optTahun .= "<option value='{$x}'>{$x}</option>";
	}
	return $optTahun;
}

$cityID = $appConfig['KODE_KOTA'];
$cityName = $appConfig['NAMA_KOTA'];
$optionCityOP = "<option valued=$cityID>$cityName</option>";

$provID = $appConfig['KODE_PROVINSI'];
$provName = $appConfig['NAMA_PROVINSI'];
$optionProvOP = "<option valued=$provID>$provName</option>";

$hiddenIdInput = $nomor = '';
$kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;

$kecOP = getKecamatan('', $cityID);

$optionKecOP = "<option value=''>Semua Kecamatan</option>";
foreach ($kecOP as $row) {
	$optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
}
$optionKelOP = "<option value=''>Semua Kelurahan</option>";


$html = "
<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab1\">
	<form name=\"form-penerimaan\" nilai='btn-save' id=\"form-penerimaan\" method=\"post\" action=\"\">
		<div class=\"row\">
			<div class=\"col-md-12\">
				<h3>DATA OBJEK PAJAK</h3>
			</div>
		</div>
		<div id=\"info_lengkap\">
			<div class=\"row mb5\">
				<div class=\"col-md-3\">
					<label for=\"provinsiOP\">Provinsi</label>
				</div>
				<div class=\"col-md-3\">
					<select class=\"form-control\" name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
				</div>
			</div>
			<div class=\"row mb5\">
				<div class=\"col-md-3\">
					<label for=\"kabupatenOP\">Kabupaten/Kota</label>
				</div>
				<div class=\"col-md-3\">
					<select class=\"form-control\" name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
				</div>
			</div>
			<div class=\"row mb5\">
				<div class=\"col-md-3\">
					<label for=\"kecamatanOP\">Kecamatan</label>
				</div>
				<div class=\"col-md-3\">
					<select class=\"form-control\" name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
				</div>
			</div>
			<div class=\"row mb5\">
				<div class=\"col-md-3\">
					<label for=\"kelurahanOP\">" . $appConfig['LABEL_KELURAHAN'] . "</label>
				</div>
				<div class=\"col-md-3\">
					<select class=\"form-control\" name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
				</div>
			</div>
			<div class=\"row mb5\">
				<div class=\"col-md-3\">
					<label>Buku</label>
				</div>
				<div class=\"col-md-3\">
					<select class=\"form-control\" name=\"buku\" id=\"buku\">
						<option value=''>Pilih Buku</option>
						<option value='1'>Buku 1</option>
						<option value='12'>Buku 2</option>
						<option value='123'>Buku 3</option>
						<option value='1234'>Buku 4</option>
						<option value='12345'>Buku 5</option>
					</select>
				</div>
			</div>
			<div class=\"row mb5\">
				<div class=\"col-md-3\">
					<label>Tahun Pajak</label>
				</div>
				<div class=\"col-md-1\">
					<input class=\"form-control\" type=\"text\" name=\"tahun-1\" id=\"tahun-1\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"Tahun\" />
				</div>
				<div class=\"col-md-1\" style=\"text-align: center; margin-top: 7px;\">s/d</div>
				<div class=\"col-md-1\">
					<input class=\"form-control\" type=\"text\" name=\"tahun-2\" id=\"tahun-2\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"Tahun\" />
				</div>
			</div>
		</div>
		<div class=\"row mb5\">
			<div class=\"col-md-3\">
				<label>Tanggal Jatuh Tempo Baru</label>
			</div>
			<div class=\"col-md-3\">
				<input class=\"form-control\" type=\"text\" name=\"tgl_jatuh_tempo\" id=\"tgl_jatuh_tempo\" size=\"20\" readonly placeholder=\"Tanggal Jatuh Tempo\"/>
			</div>
		</div>
		<div class=\"row mb5\">
			<div class=\"col-md-3\">
				<label>NOP</label>
			</div>
			<div class=\"col-md-5\">
				<div class=\"col-md-1\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-1\" style=\"padding: 6px;\" name=\"nop-1\" id=\"nop-1\" onkeypress=\"return iniAngka(event, this)\" placeholder=\"PR\" readonly>
				</div>
				<div class=\"col-md-1\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-2\" style=\"padding: 6px;\" name=\"nop-2\" id=\"nop-2\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"fa-rotate-270\" placeholder=\"DTII\" readonly>
				</div>
				<div class=\"col-md-2\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-3\" style=\"padding: 6px;\" name=\"nop-3\" id=\"nop-3\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"3\" placeholder=\"KEC\" readonly>
				</div>
				<div class=\"col-md-2\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-4\" style=\"padding: 6px;\" name=\"nop-4\" id=\"nop-4\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"3\" placeholder=\"KEL\" readonly>
				</div>
				<div class=\"col-md-2\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-5\" style=\"padding: 6px;\" name=\"nop-5\" id=\"nop-5\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"3\" placeholder=\"BLOK\" readonly>
				</div>
				<div class=\"col-md-2\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-6\" style=\"padding: 6px;\" name=\"nop-6\" id=\"nop-6\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"4\" placeholder=\"NO.URUT\" readonly>
				</div>
				<div class=\"col-md-2\" style=\"padding: 0\">
					<input type=\"text\" class=\"form-control nop-input-7\" style=\"padding: 6px;\" name=\"nop-7\" id=\"nop-7\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"1\" placeholder=\"KODE\" readonly>
				</div>
				<!--<input type=\"text\" class=\"form-control\" name=\"nop\" id=\"nop\" size=\"30\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"18\" placeholder=\"Centang untuk memasukan NOP\" readonly/>-->
			</div>
			<div class=\"col-md-2\" style=\"margin-top: 7px\">
				<input type=\"checkbox\" id=\"only_nop\"> <label for=\"only_nop\">Proses NOP ini saja.</label>
			</div>
		</div>
		<div class=\"row mb5\">
			<div class=\"col-md-3\">
				<label>File CSV</label>
			</div>
			<div class=\"col-md-2\">
				<input id=\"csv\" type=\"file\"><input type=\"hidden\" name=\"output\" id=\"out\"><br>
				<div style=\"width:auto; height:20px;\"><output id=\"result\"></output></div>
			</div>
		</div>
		<div class=\"row mb5\">
			<div class=\"col-md-2\">
				<input type=\"submit\" class=\"btn btn-primary orange-btn\" name=\"btn-save\" id=\"btn-save\" value=\"Ubah Jatuh Tempo\" />
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
		</script>
	</form>
</div>";
echo $html;
?>
<script type="text/javascript">
	var fileInput = document.getElementById("csv");

	readFile = function() {
		var reader = new FileReader();
		reader.onload = function() {
			document.getElementById('result').innerHTML = reader.result;
			document.getElementById('out').value = reader.result;
		};
		// start reading the file. When it is done, calls the onload event defined above.
		reader.readAsBinaryString(fileInput.files[0]);
	};

	fileInput.addEventListener('change', readFile);
</script>

<script>
	$(document).ready(function() {

		$('#tgl_jatuh_tempo').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
		});

		$('.tab1 #kecamatanOP').change(function() {
			if ($(this).val() == '') {
				var msg = '<option value>Semua Kelurahan</option>';
				$('.tab1 #kelurahanOP').html(msg);
			} else {
				$.ajax({
					type: 'POST',
					url: './function/PBB/loket/svc-search-city.php',
					data: 'type=3&id=' + $(this).val(),
					success: function(msg) {
						var opt = '<option value>Semua Kelurahan</option>';
						opt += msg;
						$('.tab1 #kelurahanOP').html(opt);
					}
				});
			}
		});

		$('#only_nop').click(function() {
			if ($(this).is(':checked')) {
				$('#info_lengkap').hide();
				//$('.tab1 #nop').removeAttr('readonly').attr('placeholder', 'Masukkan NOP');
				$('.tab1 #nop-1').removeAttr('readonly');
				$('.tab1 #nop-2').removeAttr('readonly');
				$('.tab1 #nop-3').removeAttr('readonly');
				$('.tab1 #nop-4').removeAttr('readonly');
				$('.tab1 #nop-5').removeAttr('readonly');
				$('.tab1 #nop-6').removeAttr('readonly');
				$('.tab1 #nop-7').removeAttr('readonly');
			} else {
				//$('.tab1 #nop').attr('readonly', 'readonly').attr('placeholder', 'Centang untuk memasukkan NOP').val('');
				$('.tab1 #nop-1').attr('readonly', 'readonly').val('');
				$('.tab1 #nop-2').attr('readonly', 'readonly').val('');
				$('.tab1 #nop-3').attr('readonly', 'readonly').val('');
				$('.tab1 #nop-4').attr('readonly', 'readonly').val('');
				$('.tab1 #nop-5').attr('readonly', 'readonly').val('');
				$('.tab1 #nop-6').attr('readonly', 'readonly').val('');
				$('.tab1 #nop-7').attr('readonly', 'readonly').val('');
				$('#info_lengkap').show();
			}
		});

		// $('.tab1 #btn-save').click(function(){

		// });
		$("#form-penerimaan").submit(function(e) {
			e.preventDefault();
			// alert ('123');
			// return false;

			var $btn = $(this);
			var kec = $('.tab1 #kecamatanOP').val();
			var kel = $('.tab1 #kelurahanOP').val();
			var thn1 = $('.tab1 #tahun-1').val();
			var thn2 = $('.tab1 #tahun-2').val();
			var buku = $('.tab1 #buku').val();
			var out = $('.tab1 #out').val();
			// alert(out);
			var nop1 = $('.tab1 #nop-1').val();
			var nop2 = $('.tab1 #nop-2').val();
			var nop3 = $('.tab1 #nop-3').val();
			var nop4 = $('.tab1 #nop-4').val();
			var nop5 = $('.tab1 #nop-5').val();
			var nop6 = $('.tab1 #nop-6').val();
			var nop7 = $('.tab1 #nop-7').val();
			var nopto = nop1 + '' + nop2 + '' + nop3 + '' + nop4 + '' + nop5 + '' + nop6 + '' + nop7;
			var nop1l = $.trim(nop1).length;
			var nop2l = $.trim(nop2).length;
			var nop3l = $.trim(nop3).length;
			var nop4l = $.trim(nop4).length;
			var nop5l = $.trim(nop5).length;
			var nop6l = $.trim(nop6).length;
			var nop7l = $.trim(nop7).length;
			var nopt = parseInt(nop1l) + parseInt(nop2l) + parseInt(nop3l) + parseInt(nop4l) + parseInt(nop5l) + parseInt(nop6l) + parseInt(nop7l);
			//var nop = $nop.val();
			var $tgl_jatuh_tempo = $('.tab1 #tgl_jatuh_tempo');
			var only_nop = $('#only_nop').is(':checked');

			var intThn = parseInt(thn2) - parseInt(thn1);

			if ($tgl_jatuh_tempo.val() == '') {
				$tgl_jatuh_tempo.focus();
				alert('Silakan isi jatuh tempo');
				return false;
			}

			if (only_nop) {
				if (nopt != 18) {
					$nop1.focus();
					alert('Silakan Isi NOP (18 Karakter).');
					return false;
				} else {
					if (confirm('Apakah anda yakin untuk mengubah jatuh tempo untuk \nNOP ' + nopto + ' ini ?') === false) return false;
				}
			} else {
				nop1 = '';
				nop2 = '';
				nop3 = '';
				nop4 = '';
				nop5 = '';
				nop6 = '';
				nop7 = '';
				var nmprop = $('.tab1 #propinsiOP option:selected').text();
				var nmkab = $('.tab1 #kabupatenOP option:selected').text();
				var nmkec = $('.tab1 #kecamatanOP option:selected').text();
				var nmkel = $('.tab1 #kelurahanOP option:selected').text();
				var ask = 'Apakah anda yakin untuk mengubah jatuh tempo untuk';
				ask += '\nPropinsi : ' + nmprop;
				ask += '\nKabupaten : ' + nmkab;
				ask += '\nKecamatan : ' + nmkec;
				ask += '\nKelurahan : ' + nmkel;
				if (out != '') {
					ask += '\nNOP dari CSV : ' + out;
				}
				if (confirm(ask) === false) return false;
			}

			for (let index = 0; index < intThn + 1; index++) {
				$btn.attr('disabled', true);
				$.ajax({
					type: 'POST',
					url: './view/PBB/penundaan-jatuh-tempo/svc-jatuh-tempo.php',
					dataType: 'json',
					data: {
						// action:$(this).attr('id'),
						action: $(this).attr('nilai'),
						kec: kec,
						kel: kel,
						nop1: nop1,
						nop2: nop2,
						nop3: nop3,
						nop4: nop4,
						nop5: nop5,
						nop6: nop6,
						nop7: nop7,
						thn: thn1,
						buku: buku,
						csv: out,
						tgl_jatuh_tempo: $tgl_jatuh_tempo.val(),
						q: '<?php echo $_REQUEST['q'] ?>'
					},
					success: function(res) {
						alert(res.msg);
						$btn.removeAttr('disabled');
						setTabs(0);
					}
				});
				thn1++;
			}
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