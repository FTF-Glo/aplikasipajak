<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta');
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemeliharaan-data-piutang', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "portlet-new/Portlet.php");

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

$dbUtils = new DbUtils(null);
$portlet = new Portlet($dbUtils);

/*proses simpan / tampil data */
if (isset($_POST['action'])) {
	$response['msg'] = 'Proses data berhasil.';

	$kel          = isset($_POST['kel']) ? $_POST['kel'] : '';
	$thn_awal     = isset($_POST['thn_awal']) ? $_POST['thn_awal'] : '';
	$thn_akhir    = isset($_POST['thn_akhir']) ? $_POST['thn_akhir'] : '';
	$thn_kegiatan = isset($_POST['thn_kegiatan']) ? $_POST['thn_kegiatan'] : '';
	$nop          = isset($_POST['nop']) ? $_POST['nop'] : '';


	if ($_POST['action'] == 'btn-cari') {

		$table = 'cppmod_dafnom_op';
		$tableNOP = $appConfig['ADMIN_GW_DBNAME'] . '.pbb_sppt';
		$tablePengurangan = $appConfig['ADMIN_GW_DBNAME'] . '.pengurangan_denda';

		$join = "LEFT JOIN {$tableNOP} ON {$table}.NOP = {$tableNOP}.NOP AND {$table}.TAHUN_KEGIATAN = {$tableNOP}.SPPT_TAHUN_PAJAK";
		$join .= " LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM {$tablePengurangan} WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C ON C.NOP = {$tableNOP}.NOP AND C.TAHUN = {$tableNOP}.SPPT_TAHUN_PAJAK 
					LEFT JOIN {$tablePengurangan} ON {$tablePengurangan}.ID = C.MAX_ID_PENGURANGAN";
		
		$select = "{$tableNOP}.WP_NAMA,
					{$tableNOP}.PAYMENT_FLAG,
					{$tableNOP}.SPPT_TANGGAL_JATUH_TEMPO,
					IFNULL({$tableNOP}.SPPT_PBB_HARUS_DIBAYAR, 0) AS SPPT_PBB_HARUS_DIBAYAR,
					IFNULL({$tableNOP}.PBB_TOTAL_BAYAR, 0) AS PBB_TOTAL_BAYAR,
					IFNULL({$tablePengurangan}.NILAI, 0) AS NILAI_PENGURANGAN,
					{$table}.*";

		$query = sprintf("SELECT {$select} FROM {$table} {$join} WHERE 
		({$table}.NOP LIKE '%s' AND {$table}.TAHUN_KEGIATAN = '%s') ", $kel . "%", $thn_kegiatan);

		$query .= (!empty($nop)) ? sprintf("AND {$table}.NOP IN ('%s')", implode("','", array_map(function($value) use ($DBLink) { return mysqli_real_escape_string($DBLink, trim($value)); }, explode(',', $nop)))) : '';
		// echo $query;
		// exit;
		$res = mysqli_query($DBLink, $query);
		$rowsData = "";
		$no = 0;
		while ($row = mysqli_fetch_object($res)) {
			$getDenda = $row->PAYMENT_FLAG == 1 ? $row->PBB_TOTAL_BAYAR - $row->SPPT_PBB_HARUS_DIBAYAR : $dbUtils->getDenda($row->SPPT_TANGGAL_JATUH_TEMPO, $row->SPPT_PBB_HARUS_DIBAYAR, $portlet::PBB_ONE_MONTH, $portlet::PBB_MAXPENALTY_MONTH, $portlet::PBB_PENALTY_PERCENT_1);
			$tagihan = number_format(($getDenda - $row->NILAI_PENGURANGAN) + $row->SPPT_PBB_HARUS_DIBAYAR);

			$rowsData .= "<tr>
				<td>" . (++$no) . "</td>
				<td>{$row->NOP}<span class='nop' style='display:none'>{$row->NOP}</span></td>
				<td>{$row->WP_NAMA}</td>
				<td>{$row->ALAMAT_OP}</td>
				<td>{$tagihan}</td>
				<td><input type='number' value='{$row->KATEGORI}' class='kategori form-control' maxlength='1' min='1' max='5' size='5'></td>
				<td><input type='text' value='{$row->KETERANGAN}' class='keterangan form-control' size='50'></td>
			</tr>";
		}
		$response['totalRows'] = $no;
		$response['table'] = $rowsData;
	} elseif ($_POST['action'] == 'btn-save') {

		$lnop = explode(';', $_POST['nop']);
		$lkategori = explode(';', $_POST['kategori']);
		$lketerangan = explode(';', $_POST['keterangan']);

		$rows = array();

		$x = 0;
		foreach ($lnop as $nop) {

			if (!empty($nop)) {
				$nop = mysqli_escape_string($DBLink, $nop);
				$kategori = mysqli_escape_string($DBLink, $lkategori[$x]);
				$keterangan = mysqli_escape_string($DBLink, $lketerangan[$x]);
				$x++;

				$param = array(
					"KATEGORI = '{$kategori}'",
					"KETERANGAN = '{$keterangan}'",
					"UPDATED_AT = '". date('Y-m-d H:i:s') ."'",
				);

				$sets = implode(',', $param);
				$query = "update cppmod_dafnom_op set {$sets} where NOP='{$nop}'";
				$sql = mysqli_query($DBLink, $query);
				
				if($sql && $kategori == '4'){
					$tahun_berjalan = $appConfig['tahun_tagihan'];
					$query = "select * from cppmod_pbb_sppt_current a
							inner join gw_pbb.pbb_sppt b on a.NOP = b.NOP AND b.SPPT_TAHUN_PAJAK = '{$tahun_berjalan}' where b.NOP = '{$nop}'";
					$sql = mysqli_query($DBLink, $query);
					$check = mysqli_num_rows($sql);
					
					if($check == '0'){
						$query = "update cppmod_pbb_sppt_final set CPM_SPPT_THN_PENETAPAN = '0' where CPM_NOP='{$nop}'";
						//var_dump($query);die;
						$sql = mysqli_query($DBLink, $query);
					}
				}
			}

			$response['msg'] = "Data berhasil disimpan.";
		}
	}

	exit($json->encode($response));
}

function getKecamatan($idKec = '', $idKab = "")
{
	/** @var mysqli $DBLink */
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

function getKelurahan($idKel = '', $idKec = "")
{
	/** @var mysqli $DBLink */
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

$cityID = $appConfig['KODE_KOTA'];
$cityName = $appConfig['NAMA_KOTA'];
$optionCityOP = "<option valued=$cityID>$cityName</option>";

$provID = $appConfig['KODE_PROVINSI'];
$provName = $appConfig['NAMA_PROVINSI'];
$optionProvOP = "<option valued=$provID>$provName</option>";

$hiddenIdInput = $nomor = '';
$kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;
$optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";

$kecOP = getKecamatan('', $cityID);
$kelOP = getKelurahan('', $kecOP[0]['id']);

foreach ($kecOP as $row) {
	$optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
}
foreach ($kelOP as $row) {
	$optionKelOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
}

$html = "
<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab2\">
	<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	<div class=\"tbl-monitoring\">
		<table width=\"820\" border=\"0\" cellspacing=\"0\" cellpadding=\"8\" class=\"table table-sm table-borderless\">
			<tr><td colspan=\"7\"><strong><font size=\"+1\">DATA OBJEK PAJAK</font></strong><hr/></td></tr>
			<tr>
			  <td width=\"\"><label for=\"provinsiOP\">Provinsi</label></td>
			  <td width=\"\">
				<select class=\"form-control\" name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
			  </td>
			  
			  <td width=\"\"><label for=\"kelurahanOP\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
			  <td width=\"\" colspan=\"4\">
				<select class=\"form-control\" name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
			  </td>
			  
			</tr>
			<tr>
			  <td width=\"\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
			  <td width=\"\">
				<select class=\"form-control\" name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
			  </td>
			  
			  <td width=\"\">Blok</td>
			  <td width=\"\" colspan=\"2\">
				<input class=\"form-control\" type=\"text\" name=\"blok\" id=\"blok\" size=\"4\" maxlength=\"3\" placeholder=\"Blok\" onkeypress=\"return iniAngka(event, this)\" required=\"true\"/>
			  </td>	  
			  
			  <td width=\"\">Tahun Kegiatan</td>
			  <td width=\"\">
				<input class=\"form-control\" type=\"text\" name=\"tahun\" id=\"tahun\" size=\"5\" maxlength=\"4\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"Tahun\" />
			  </td>
			  
			</tr>
			
			<tr>
			  <td width=\"\"><label for=\"kecamatanOP\">Kecamatan</label></td>
			  <td width=\"\">
				<select class=\"form-control\" name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
			  </td>
			  
			  <td width=\"\">NOP</td>
			  <td width=\"\" colspan=\"4\">
				<textarea class=\"form-control\" style=\"width: 25em;display: inline-block\" type=\"text\" name=\"nop\" id=\"nop\" size=\"30\" placeholder=\"NOP\" rows=\"5\"/></textarea>
				<input class=\"btn btn-primary bg-orange\" type=\"button\" name=\"btn-cari\" id=\"btn-cari\" value=\"Cari\">
				<small style='display:block'>Jika NOP lebih dari satu pisahkan dengan koma (,)</small>
			  </td>
			  
			</tr>
			<tr>
				<td colspan=\"7\">
					<link href=\"view/PBB/monitoring/monitoring.css\" rel=\"stylesheet\" type=\"text/css\">
					<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">
						<table width=\"800px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"table table-sm table-bordered\">
							<thead>
								<tr>
									<th width=\"50px\">No</th>
									<th width=\"110px\">NOP</th>
									<th>Nama WP</th>
									<th width=\"420px\">Letak OP</th>
									<th>Tagihan</th>
									<th width=\"50px\">Kategori</th>
									<th width=\"460px\">Keterangan</th>
								</tr>
							</thead>
								
							<tbody id=\"table-kategori-op\" class=\"table-kategori-op\"></tbody>
						</table>
					</div>
					Total data : <span id=\"table-kategori-op-totalRows\">0</span><br/>
					<select class=\"form-control\" id=\"kategori-all\" style=\"width:40em;display:inline-block\">
						<!-- option value=\"1\">1 (OP tidak ada/tidak ditemukan)</option>
						<option value=\"2\">2 (OP Double/Double anslah)</option>
						<option value=\"3\">3 (WP tidak ada/tidak ditemukan)</option>
						<option value=\"4\">4 (OP & WP valid)</option -->

						
						<option value=\"1\">1. Objek pajak yang memiliki dua atau lebih NOP sehingga SPPT PPB-nya di terbitkan lebih dari satu kali pada tahun pajak yang sama (SPPT Dobel)</option>
						<option value=\"2\">2. Objek pajak yang telah terdaftar namun secara nyata tidak dapat di temukan lokasinya di lapangan (objek tidak ditemukan)</option>
						<option value=\"3\">3. Objek pajak yang identitas subjek pajaknya tidak jelas (subek tidak ditemukan/tidak ada)</option>
						<option value=\"4\">4. OP & WP valid</option>
						<option value=\"5\">5. Objek pajak yang lokasi dan subjek pajaknya tidak dapat teridentifikasi dengan jelas (subjek dan objek pajak tidak ditemukan/tidak ada)</option>
					</select>
					<input class=\"btn btn-primary bg-blue\" type=\"button\" value=\"Set Kategori Semua\" onclick=\"setKategoriAll()\">
				</td>
			</tr>
			<tr>
			  <td colspan=\"7\" valign=\"middle\">&nbsp;<hr/></td>
			</tr>
			<tr>
			  <td colspan=\"7\" align=\"center\" valign=\"middle\">
				<input type=\"button\" class=\"btn btn-primary bg-orange\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />&nbsp;
			  </td>
			</tr>
		</table>
	</div>
	</form>
</div>";
echo $html;
?>

<script>
	$(document).ready(function() {
		$('.tab2 #kecamatanOP').change(function() {
			$.ajax({
				type: 'POST',
				url: './function/PBB/loket/svc-search-city.php',
				data: 'type=3&id=' + $(this).val(),
				success: function(msg) {
					$('.tab2 #kelurahanOP').html(msg);
				}
			});
		});

		$('.tab2 #btn-cari, .tab2 #btn-save').click(function() {
			var $btn = $(this);
			var kel = $('.tab2 #kelurahanOP').val();
			var $blok = $('.tab2 #blok');
			var thn_awal = $('.tab2 #tahun_awal').val();
			var thn_akhir = $('.tab2 #tahun_akhir').val();
			var thn_kegiatan = $('.tab2 #tahun').val();
			var nop = $('.tab2 #nop').val();

			if ($.trim($blok.val()).length < 3 && $.trim($blok.val()).length >= 1) {
				alert("Blok harus diisi 3 karakter!");
				$blok.focus();
				return false;
			}
			kel = kel + $blok.val();
			var postData = {
				action: $(this).attr('id'),
				kel: kel,
				thn_awal: thn_awal,
				thn_akhir: thn_akhir,
				thn_kegiatan: thn_kegiatan,
				nop: nop,
				q: '<?php echo $_REQUEST['q'] ?>'
			};

			if (postData.action == 'btn-save') {
				postData.nop = '';
				postData.kategori = '';
				postData.keterangan = '';

				$('span.nop').each(function() {
					postData.nop += $(this).html() + ';';
				});

				$('input.kategori').each(function() {
					postData.kategori += $(this).val() + ';';
				});

				$('input.keterangan').each(function() {
					postData.keterangan += $(this).val() + ';';
				});
			}

			$btn.attr('disabled', true).val('Loading...');
			$.ajax({
				type: 'POST',
				url: './view/PBB/pemeliharaan-data-piutang/svc-perekaman-kategori-op.php',
				dataType: 'json',
				data: postData,
				success: function(res) {
					$btn.removeAttr('disabled').val((postData.action == 'btn-cari' ? 'Cari' : 'Simpan'));
					$('#table-kategori-op').html(res.table);
					$('#table-kategori-op-totalRows').html(res.totalRows);

					if (postData.action == 'btn-cari') {
						if ($.trim(res.table) == "") alert('data tidak ditemukan!');
					} else if (postData.action == 'btn-save') {
						alert(res.msg);
					}
				}
			});
		});

		// aldes
		$('body').on('change', 'input.kategori', function() {
			let v = $(this);
			let katAll = $('#kategori-all');
			let katAllOpt = $(`#kategori-all option[value="${v.val()}"]`);
			let ket = v.parent('td').next().find('.keterangan');
			if (katAllOpt.length) {
				ket.val(katAllOpt.text().slice(3));
			}
		})
	});

	function iniAngka(evt, x) {
		var charCode = (evt.which) ? evt.which : event.keyCode;
		if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
			return true;
		} else {
			alert('Input hanya boleh angka!');
			return false;
		}
	}

	function setKategoriAll() {
		var kat = $('#kategori-all').val();
		$('input.kategori').each(function() {
			$(this).val(kat);

			// aldes
			$(this).trigger('change');
		});

	}
</script>