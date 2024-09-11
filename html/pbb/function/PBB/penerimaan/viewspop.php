<?php

//echo mysqli_error($DBLink); 
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (!isset($data)) {
	die("Forbidden direct access");
}

if (!$User) {
	die("Access not permitted");
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if (!$bOK) {
	die("Function access not permitted");
}
require_once("inc/payment/uuid.php");
require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbSpptExt.php");
require_once("inc/PBB/dbFinalSppt.php");
require_once("inc/PBB/dbSpptHistory.php");
require_once("function/PBB/gwlink.php");
require_once("inc/PBB/dbUtils.php");
require_once("inc/PBB/dbServices.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbServices = new DbServices($dbSpec);

if (isset($tglPenerimaan)) {

	$tglPenerimaan = date('Y-m-d', strtotime($tglPenerimaan));

	$query = sprintf(
		"INSERT INTO cppmod_pbb_sppt_penerimaan (
			CPM_NOP, 
			CPM_TAHUN, 
			CPM_TANGGAL_PENERIMAAN, 
			CPM_NAMA_PENERIMA, 
			CPM_KONTAK_PENERIMA)
			VALUES ('%s','%s','%s','%s','%s')",
		$CPM_NOP,
		$CPM_TAHUN,
		$tglPenerimaan,
		$namaPenerima,
		$noPenerima
	) .
		sprintf(
			" ON DUPLICATE KEY 
			UPDATE 
			CPM_TANGGAL_PENERIMAAN = '%s', 
			CPM_NAMA_PENERIMA = '%s', 
			CPM_KONTAK_PENERIMA = '%s'",
			$tglPenerimaan,
			$namaPenerima,
			$noPenerima
		);
	$dbSpec->sqlQuery($query, $res);
}
if (isset($nop) && isset($tahun)) {
	$query = sprintf("SELECT A.*, B.* FROM cppmod_pbb_sppt_current A LEFT JOIN cppmod_pbb_sppt_penerimaan B ON A.NOP = B.CPM_NOP
			WHERE A.NOP = '%s' AND A.SPPT_TAHUN_PAJAK = '%s'", $nop, $tahun);
	$dbSpec->sqlQueryRow($query, $docVal);
	foreach ($docVal[0] as $key => $value) {
		if (is_numeric($key)) continue;
		$$key = $value;
	}


	$date = new DateTime($docVal[0]['CPM_TANGGAL_PENERIMAAN']);
	$docVal[0]['CPM_TANGGAL_PENERIMAAN'] = $date->format('d-m-Y');
}

$aOPKabKota = $dbUtils->getKabKota($docVal[0]['OP_KOTAKAB_KODE']);
$aOPKecamatan = $dbUtils->getKecamatan($docVal[0]['OP_KECAMATAN_KODE']);
$aOPKelurahan = $dbUtils->getKelurahan($docVal[0]['OP_KELURAHAN_KODE']);

echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">";

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script src=\"function/PBB/loket/jquery.validate.min.js\"></script>";
?>

<script type="text/javascript">
	$(document).ready(function() {

		$("input:submit, input:button").button();

		$("#form-penerimaan").validate({
			rules: {
				tglPenerimaan: "required",
				namaPenerima: "required",
				noPenerima: "required"
			},
			messages: {
				tglPenerimaan: "Wajib diisi",
				namaPenerima: "Wajib diisi",
				noPenerima: "Wajib diisi"
			}
		});
	});
</script>
<?php
include("viewtmp.php");