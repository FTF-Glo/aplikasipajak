<?php
//session_start(); //session already started
$DIR = "PATDA-V1";
$modul = "payment_backdate";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/class-message.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-payment.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$Payment = new Payment();
extract($Payment->dataForm());
?>

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>

<div class="container lm-container">
	<div class="row">
		<div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
			<b>FORM CETAK ULANG</b>
		</div>
	</div>
	<form id="form-cetakulang" method="post">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Kode Bayar<b class="isi">*</b></label>
					<input type="text" name="payment_code" id="payment_code" class="form-control" autocomplete="off" placeholder="Masukan kode bayar">
				</div>
			</div>
		</div>

		<div id="area_identity">
		</div>
	</form>
	<div class="row">
		<div class="col-md-12">
			<div class="form-group" style="display: flex; justify-content: center">
				<button type="button" id="btnCetak" class="btn btn-primary lm-btn">Cetak Kwitansi</button>
			</div>
		</div>
	</div>
</div>

<script>
	var APP = '<?php echo $a ?>';
	var UID = '<?php echo $uid ?>';
</script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/payment.js"></script>