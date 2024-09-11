<?php
//session_start();

// error_reporting(E_ERROR);
// ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'simulasi-ketetapan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
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

$a 		= $q->a;
$m 		= $q->m;
$n 		= $q->n;
$tab 		= $q->tab;
$uname 	= $q->u;
$uid 		= isset($q->uid) ? $q->uid : '';

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

/*proses simpan / update znt */
if (isset($_REQUEST['action'])) {
	$response['msg'] = 'Proses data berhasil.';

	//print_r($_POST);
	//exit;

	$nop1 				= $_REQUEST['nop1'];
	$nop2 				= $_REQUEST['nop2'];
	//$old_znt 			= $_REQUEST['old_znt'];
	$new_znt 			= $_REQUEST['new_znt'];
	$no_doc 				= $_REQUEST['no_doc'];
	$tgl_pendataan 	= $_REQUEST['tgl_pendataan'];
	$nip_pendata 		= $_REQUEST['nip_pendata'];
	$tgl_pemeriksaan 	= $_REQUEST['tgl_pemeriksaan'];
	$nip_pemeriksa 	= $_REQUEST['nip_pemeriksa'];

	if ($_REQUEST['action'] == 'btn-save') {
		$listNOP = getListNOP($nop1, $nop2);
		$c = count($listNOP);
		if ($c > 0) {
			// $valZNT['KD_ZNT_LAMA'] 	= $old_znt;
			$valZNT['KD_ZNT_BARU'] 	= $new_znt;
			$valZNT['STATUS'] 		= 0;
			$valZNT['TGL_INPUT'] 	= date("Y-m-d H:i:s");

			$counter = 0;
			foreach ($listNOP as $val) {
				$valZNT['KD_ZNT_LAMA'] 	= getZNT($val);
				$valZNT['NOP'] = $val;
				$addTempZNTMassal = addToTempZNTMassal($valZNT);
				$counter++;
			}

			$valDoc['DOK_NOMOR'] 			= $no_doc;
			$valDoc['DOK_TGL_PENDATAAN'] 	= $tgl_pendataan;
			$valDoc['DOK_NIP_PENDATA'] 		= $nip_pendata;
			$valDoc['DOK_TGL_PEMERIKSAAN'] 	= $tgl_pemeriksaan;
			$valDoc['DOK_NIP_PEMERIKSA'] 	= $nip_pemeriksa;
			$valDoc['DOK_TGL_PEREKAMAN'] 	= date("Y-m-d");
			$valDoc['DOK_NIP_PEREKAMAN'] 	= $uname;

			$bOK = addToDocumentZNTMassal($valDoc);

			if ($bOK) {
				$bOK = updateFinal();
				if ($bOK) {
					$bOK = updateSusulan();
					if ($bOK) {
						$response['msg'] = 'Data berhasil diproses sejumlah : ' . $counter;
					} else {
						$response['msg'] = 'Gagal update ZNT : ERR02';
					}
				} else {
					$response['msg'] = 'Gagal update ZNT : ERR01';
				}
			} else {
				$response['msg'] = 'Gagal input dokumen';
			}
		} else {
			$response['msg'] = 'Data tidak ditemukan';
		}
	}
	exit($json->encode($response));
}

function getZNT($nop)
{
	global $DBLink, $appConfig;
	$nop = trim($nop);
	$query = "SELECT CPM_OT_ZONA_NILAI FROM cppmod_pbb_sppt_final WHERE CPM_NOP = '$nop' and CPM_SPPT_TEMP_STATUS <> 1 ";
	$res = mysqli_query($DBLink, $query);
	// echo mysqli_num_rows($res);

	if (mysqli_num_rows($res) == 0) {
		$query = "SELECT CPM_OT_ZONA_NILAI FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP = '$nop' and CPM_SUSULAN_TEMP_STATUS <> 1 ";
		$res = mysqli_query($DBLink, $query);
		if (mysqli_num_rows($res) < 1) {
			return false;
		}
	}

	if ($res == false) {
		return false;
	} else {
		$data = mysqli_fetch_assoc($res);
		return $data['CPM_OT_ZONA_NILAI'];
	}
}

function getListNOP($nop1, $nop2)
{
	global $DBLink, $appConfig, $old_znt;

	$thn_tagihan = $appConfig['tahun_tagihan'];

	$query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_final WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' and CPM_SPPT_TEMP_STATUS <> 1 
	-- AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL) AND CPM_OT_ZONA_NILAI = '{$old_znt}'
		UNION
		SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' and CPM_SUSULAN_TEMP_STATUS <> 1 ";
	// AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL) AND CPM_OT_ZONA_NILAI = '{$old_znt}'";
	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
	if (!$res) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	} else {
		$data = array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row['CPM_NOP'];
		}
		return $data;
	}
}

function updateFinal()
{
	global $DBLink, $appConfig, $nop1, $nop2, $old_znt, $new_znt;

	$thn_tagihan = $appConfig['tahun_tagihan'];

	$querydata = "INSERT INTO cppmod_pbb_sppt_final_tempt SELECT * FROM cppmod_pbb_sppt_final where CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}'";
	$resdata = mysqli_query($DBLink, $querydata);

	if ($resdata) {
		$queryupdatedata = "UPDATE cppmod_pbb_sppt_final set CPM_SPPT_TEMP_STATUS = 1 where CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}'";
		$resupdatedata = mysqli_query($DBLink, $queryupdatedata);
	}

	$query = "UPDATE cppmod_pbb_sppt_final_tempt SET CPM_OT_ZONA_NILAI = '{$new_znt}' WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' ";
	// -- AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL)";
	// AND CPM_OT_ZONA_NILAI = '{$old_znt}' ";
	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
	if (!$res) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}

	return $res;
}

function updateSusulan()
{
	global $DBLink, $appConfig, $nop1, $nop2, $old_znt, $new_znt;

	$thn_tagihan = $appConfig['tahun_tagihan'];

	$querydata = "INSERT INTO cppmod_pbb_sppt_susulan_temp SELECT * FROM cppmod_pbb_sppt_susulan where CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}'";
	$resdata = mysqli_query($DBLink, $querydata);

	if ($resdata) {
		$queryupdatedata = "UPDATE cppmod_pbb_sppt_susulan set CPM_SUSULAN_TEMP_STATUS = 1 where CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}'";
		$resupdatedata = mysqli_query($DBLink, $queryupdatedata);
	}

	$query = "UPDATE cppmod_pbb_sppt_susulan_temp SET CPM_OT_ZONA_NILAI = '{$new_znt}' WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' ";
	// AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL)";
	// -- AND CPM_OT_ZONA_NILAI = '{$old_znt}' ";
	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
	if (!$res) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}

	return $res;
}

function addToTempZNTMassal($post)
{
	global $DBLink;

	foreach ($post as $key => $val) {
		$val = mysqli_real_escape_string($DBLink, trim($val));
		$colName[] = $key;
		$colVal[] = "'$val'";
	}
	$colName = implode(',', $colName);
	$colVal = implode(',', $colVal);
	$query = "INSERT INTO cppmod_pbb_temp_znt_massal (" . $colName . ") VALUES(" . $colVal . ")";
	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
	if (!$res) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}

	return $res;
}


function addToDocumentZNTMassal($post)
{
	global $DBLink;

	foreach ($post as $key => $val) {
		$val = mysqli_real_escape_string($DBLink, trim($val));
		$colName[] = $key;
		$colVal[] = "'$val'";
	}

	$colName = implode(',', $colName);
	$colVal = implode(',', $colVal);
	$query = "INSERT INTO cppmod_pbb_dokumen_znt_massal_temp (" . $colName . ") VALUES(" . $colVal . ")";
	// echo $query; exit;

	$res = mysqli_query($DBLink, $query);
	if (!$res) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}

	return $res;
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

$cityID = $appConfig['KODE_KOTA'];
$cityName = $appConfig['NAMA_KOTA'];
$optionCityOP = "<option valued=$cityID>$cityName</option>";

$provID = $appConfig['KODE_PROVINSI'];
$provName = $appConfig['NAMA_PROVINSI'];
$optionProvOP = "<option valued=$provID>$provName</option>";

$hiddenIdInput = $nomor = '';
$kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;

$kecOP = getKecamatan('', $cityID);

$optionKecOP = "<option value=''>Kecamatan</option>";
foreach ($kecOP as $row) {
	$optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
}
$optionKelOP = "<option value=''>Kelurahan</option>";


$html = "
<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab0\">
	<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
		<!--<input type=\"hidden\" name=\"provid\" id=\"provid\" size=\"26\" value=\"" . $provID . "\"/>
		<input type=\"hidden\" name=\"cityid\" id=\"cityid\" size=\"26\" value=\"" . $cityID . "\"/>-->
		<div class=\"row\">
			<div class=\"col-md-12\" id=\"info_lengkap\">
				<!-- <tr>
					<td width=\"39%\"><label for=\"provinsiOP\">Provinsi</label></td>
					<td width=\"60%\">
						<select name=\"propinsiOP\" id=\"propinsiOP\" style=\"width:150px\">$optionProvOP</select>
					</td>
				</tr>
				<tr>
					<td width=\"39%\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
					<td width=\"60%\">
						<select name=\"kabupatenOP\" id=\"kabupatenOP\" style=\"width:150px\">$optionCityOP</select>
					</td>
				</tr>
				-->
				<strong><font size=\"+1\">DATA OBJEK PAJAK</font></strong><hr/>
				<div class=\"col-md-12\">
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"kecamatanOP\" style=\"margin-top: 7px\">Kecamatan</label>
						</div>
						<div class=\"col-md-5\">
							<select name=\"kecamatanOP\" class=\"form-control\" id=\"kecamatanOP\">" . $optionKecOP . "</select>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"kelurahanOP\" style=\"margin-top: 7px\">" . $appConfig['LABEL_KELURAHAN'] . "</label>
						</div>
						<div class=\"col-md-5\">
							<select name=\"kelurahanOP\" class=\"form-control\" id=\"kelurahanOP\">" . $optionKelOP . "</select>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"blok\" style=\"margin-top: 7px\">Blok</label>
						</div>
						<div class=\"col-md-3\">
							<input type=\"text\" class=\"form-control\" name=\"blok\" id=\"blok\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"3\" placeholder=\"Blok\"/>
						</div>
					</div>
					<!--
					<tr>
						<td width=\"39%\">Kode ZNT Asal</td>
						<td width=\"60%\">
							<input type=\"text\" name=\"kd_znt_lama\" id=\"kd_znt_lama\" maxlength=\"2\" size=\"5\" placeholder=\"ZNT\"/>
						</td>
					</tr>
					-->
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"kd_znt_baru\" style=\"margin-top: 7px\">Kode ZNT Baru</label>
						</div>
						<div class=\"col-md-3\">
							<input type=\"text\" class=\"form-control\" name=\"kd_znt_baru\" id=\"kd_znt_baru\" maxlength=\"2\" size=\"5\" placeholder=\"ZNT\"/>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 30px\">
						<div class=\"col-md-3\">
							<label for=\"no_urut1\" style=\"margin-top: 7px\">No Urut</label>
						</div>
						<div class=\"col-md-2\">
							<input type=\"text\" class=\"form-control\" name=\"no_urut1\" id=\"no_urut1\" maxlength=\"4\" size=\"5\"/>
						</div>
						<div class=\"col-md-1\">
							<input type=\"text\" class=\"form-control\" name=\"jenis1\" id=\"jenis1\" maxlength=\"1\" size=\"2\"/>
						</div>
						<div class=\"col-md-1\" style=\"margin-top: 7px; text-align: center\">
							s/d
						</div>
						<div class=\"col-md-2\">
							<input type=\"text\" class=\"form-control\" name=\"no_urut2\" id=\"no_urut2\" maxlength=\"4\" size=\"5\"/>
						</div>
						<div class=\"col-md-1\">
							<input type=\"text\" class=\"form-control\" name=\"jenis2\" id=\"jenis2\" maxlength=\"1\" size=\"2\"/>
						</div>
					</div>
					<strong><font size=\"+1\">DATA DOKUMEN</font></strong><hr/>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"no_doc\" style=\"margin-top: 7px\">Nomor Dokumen</label>
						</div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" name=\"no_doc\" id=\"no_doc\" size=\"26\" placeholder=\"Nomor Dokumen\"/>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"tgl_pendataan\" style=\"margin-top: 7px\">Tanggal Pendataan</label>
						</div>
						<div class=\"col-md-3\">
							<input type=\"text\" class=\"form-control\" name=\"tgl_pendataan\" id=\"tgl_pendataan\" size=\"10\" placeholder=\"Tanggal\"/>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"nip_pendata\" style=\"margin-top: 7px\">NIP Pendata</label>
						</div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" name=\"nip_pendata\" id=\"nip_pendata\" size=\"26\" placeholder=\"NIP Pendata\"/>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-3\">
							<label for=\"no_doc\" style=\"margin-top: 7px\">Tanggal Pemeriksaan</label>
						</div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" name=\"tgl_pemeriksaan\" id=\"tgl_pemeriksaan\" size=\"10\" placeholder=\"Tanggal\"/>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 20px\">
						<div class=\"col-md-3\">
							<label for=\"nip_pemeriksa\" style=\"margin-top: 7px\">NIP Pemeriksa</label>
						</div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" name=\"nip_pemeriksa\" id=\"nip_pemeriksa\" size=\"26\" placeholder=\"NIP Pemeriksa\"/>
						</div>
					</div>
					<div class=\"row\" style=\"margin-bottom: 10px\">
						<div class=\"col-md-6\">
							<input type=\"submit\" class=\"btn btn-primary btn-orange\" name=\"btn-save\" id=\"btn-save\" value=\"Ubah ZNT\" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>";
echo $html;
?>

<script>
	$(document).ready(function() {

		$('#tgl_pendataan').datepicker({
			dateFormat: 'yy-mm-dd'
		});
		$('#tgl_pemeriksaan').datepicker({
			dateFormat: 'yy-mm-dd'
		});

		$('.tab0 #kecamatanOP').change(function() {
			if ($(this).val() == '') {
				var msg = '<option value>Kelurahan</option>';
				$('.tab0 #kelurahanOP').html(msg);
			} else {
				$.ajax({
					type: 'POST',
					url: './function/PBB/loket/svc-search-city.php',
					data: 'type=3&id=' + $(this).val(),
					success: function(msg) {
						var opt = '<option value>Kelurahan</option>';
						opt += msg;
						$('.tab0 #kelurahanOP').html(opt);
					}
				});
			}
		});

		$('.tab0 #btn-save').click(function(e) {
			e.preventDefault();

			var $btn = $(this);
			var $kec = $('.tab0 #kecamatanOP');
			var $kel = $('.tab0 #kelurahanOP');
			var $blok = $('.tab0 #blok');
			// var $old_znt  		= $('.tab0 #kd_znt_lama');
			var $new_znt = $('.tab0 #kd_znt_baru');
			var $no_urut1 = $('.tab0 #no_urut1');
			var $jenis1 = $('.tab0 #jenis1');
			var $no_urut2 = $('.tab0 #no_urut2');
			var $jenis2 = $('.tab0 #jenis2');
			var $no_doc = $('.tab0 #no_doc');
			var $tgl_pendataan = $('.tab0 #tgl_pendataan');
			var $nip_pendata = $('.tab0 #nip_pendata');
			var $tgl_pemeriksaan = $('.tab0 #tgl_pemeriksaan');
			var $nip_pemeriksa = $('.tab0 #nip_pemeriksa');

			if ($kec.val() == '') {
				$kec.focus();
				alert('Silakan pilih kecamatan');
				return false;
			} else if ($kel.val() == '') {
				$kel.focus();
				alert('Silakan pilih kelurahan');
				return false;
			} else if ($blok.val() == '') {
				$blok.focus();
				alert('Silakan isi blok');
				return false;
				// } else if($old_znt.val() == ''){
				// 	$old_znt.focus();
				// 	alert('Silakan isi ZNT lama');
				// 	return false;
				// }
			} else if ($new_znt.val() == '') {
				$new_znt.focus();
				alert('Silakan isi ZNT baru');
				return false;
			} else if ($no_urut1.val() == '') {
				$no_urut1.focus();
				alert('Silakan isi nomor urut 1');
				return false;
			} else if ($jenis1.val() == '') {
				$jenis1.focus();
				alert('Silakan isi jenis NOP 1');
				return false;
			} else if ($no_urut2.val() == '') {
				$no_urut2.focus();
				alert('Silakan nomor urut 2');
				return false;
			} else if ($jenis2.val() == '') {
				$jenis2.focus();
				alert('Silakan isi jenis NOP 2');
				return false;
			} else if ($no_doc.val() == '') {
				$no_doc.focus();
				alert('Silakan isi nomor dokumen');
				return false;
			} else if ($tgl_pendataan.val() == '') {
				$tgl_pendataan.focus();
				alert('Silakan isi tanggal pendataan');
				return false;
			} else if ($nip_pendata.val() == '') {
				$nip_pendata.focus();
				alert('Silakan isi nip pendata');
				return false;
			} else if ($tgl_pemeriksaan.val() == '') {
				$tgl_pemeriksaan.focus();
				alert('Silakan isi tanggal pendataan');
				return false;
			} else if ($nip_pemeriksa.val() == '') {
				$nip_pemeriksa.focus();
				alert('Silakan isi nip pendata');
				return false;
			}

			var idkel = $kel.val();
			var blok = $blok.val();
			var no_urut1 = $no_urut1.val();
			var jenis1 = $jenis1.val();
			var no_urut2 = $no_urut2.val();
			var jenis2 = $jenis2.val();
			var nop1 = idkel + blok + no_urut1 + jenis1;
			var nop2 = idkel + blok + no_urut2 + jenis2;
			var ask = 'Apakah anda yakin untuk mengubah ZNT untuk NOP ' + nop1 + ' sampai dengan ' + nop2 + '?';
			if (confirm(ask) === false) return false;

			//$btn.attr('disabled', true);
			$.ajax({
				url: './view/PBB/simulasi-ketetapan/svc-perubahan-znt-massal.php',
				dataType: "json",
				data: {
					action: $(this).attr('id'),
					nop1: nop1,
					nop2: nop2,
					// old_znt:$old_znt.val(),
					new_znt: $new_znt.val(),
					no_doc: $no_doc.val(),
					tgl_pendataan: $tgl_pendataan.val(),
					nip_pendata: $nip_pendata.val(),
					tgl_pemeriksaan: $tgl_pemeriksaan.val(),
					nip_pemeriksa: $nip_pemeriksa.val(),
					q: '<?php echo $_REQUEST['q'] ?>'
				},
				success: function(data) {
					alert(data.msg);
					$btn.removeAttr('disabled');
					if (data.msg != "Data tidak ditemukan") {
						setTabs(0);
					}
				},
				error: function(xhr, status, error) {
					var err = eval("(" + xhr.responseText + ")");
					alert(err.Message);
				},
			});
			/*$.ajax({
				type: 'POST',
				url: './view/PBB/simulasi-ketetapan/svc-perubahan-znt-massal.php',
				dataType: 'json',
				data: {
					action: $(this).attr('id'),
					nop1: nop1,
					nop2: nop2,
					// old_znt:$old_znt.val(),
					new_znt: $new_znt.val(),
					no_doc: $no_doc.val(),
					tgl_pendataan: $tgl_pendataan.val(),
					nip_pendata: $nip_pendata.val(),
					tgl_pemeriksaan: $tgl_pemeriksaan.val(),
					nip_pemeriksa: $nip_pemeriksa.val(),
					q: '<?php //echo $_REQUEST['q'] 
							?>'
				},
				error: function(xhr, status, error) {
					var err = eval("(" + xhr.responseText + ")");
					alert(err.Message);
				},
				success: function(res) {
					alert(res.msg);
					$btn.removeAttr('disabled');
					if (res.msg != "Data tidak ditemukan") {
						setTabs(0);
					}
				}
			});*/
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