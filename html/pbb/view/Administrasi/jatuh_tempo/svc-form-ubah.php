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

if ($_POST['id'] != '') {
	$query = "SELECT * FROM cppmod_pbb_tgl_jatuh_tempo WHERE CPM_TGL_ID = '" . $_POST['id'] . "' ";
	$res = mysqli_query($DBLink, $query);
	$row = mysqli_fetch_object($res);
	$rowsData = "
		<div id=\"dialog-form\" title=\"Ubah Tanggal Jatuh Tempo\">
		  <form>
			<fieldset>
				<label for=\"ID\">ID</label>
				<input type=\"text\" readonly name=\"id\" id=\"id\" value=\"" . $row->CPM_TGL_ID . "\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"tgl_awal\">Tanggal Penetapan Awal</label>
				<input type=\"text\" name=\"tgl_awal_penetapan\" id=\"tgl_awal_penetapan\" value=\"" . $row->CPM_TGL_PENETAPAN_AWAL . "\" maxlength=\"5\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"tgl_akhir\">Tanggal Penetapan Akhir</label>
				<input type=\"text\" name=\"tgl_akhir_penetapan\" id=\"tgl_akhir_penetapan\" value=\"" . $row->CPM_TGL_PENETAPAN_AKHIR . "\" maxlength=\"5\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"tgl_jatuh_tempo\">Tanggal Jatuh Tempo</label>
				<input type=\"text\" name=\"tgl_jatuh_tempo\" id=\"tgl_jatuh_tempo\" value=\"" . $row->CPM_TGL_JATUH_TEMPO . "\" maxlength=\"5\" class=\"text ui-widget-content ui-corner-all\">
			</fieldset>
		  </form>
		</div>
		<script>
			$(document).ready(function(){
				$( \"#tgl_awal_penetapan\" ).datepicker({dateFormat:'mm-dd'});
				$( \"#tgl_akhir_penetapan\" ).datepicker({dateFormat:'mm-dd'});
				$( \"#tgl_jatuh_tempo\" ).datepicker({dateFormat:'mm-dd'});
			})";
	$response['id'] 	= $row->CPM_TGL_ID;
	$response['table'] 	= $rowsData;
}
exit($json->encode($response));
