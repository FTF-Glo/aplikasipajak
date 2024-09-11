<?php
//session_start(); //session already started
$DIR = "PATDA-V1";
$modul = "payment";

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

<link href="inc/<?php echo $DIR; ?>/frmStyleSimpatda.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>
<script language="javascript" src="inc/js/jquery-ui.js"></script>
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>

<table class="main" width="600" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="center" class="subtitle" colspan="2"><b>FORM CETAK ULANG</b></td>
	</tr>         
	<tr valign="top">
		<td width="45%" >
			<form id="form-cetakulang" method="post">
				<table border="0" width="100%" align="center" class="child" style="padding:10px">
					<tr>
						<td width="25%">Kode Verifikasi<b class="isi">*</b></td>
						<td width="75%"> : 
							<input type="text" name="payment_code" id="payment_code" autocomplete="off" style="width:200px" placeholder="masukan kode verifikasi">
						</td>
					</tr>
				</table>
				
				<div id="area_identity">
				</div>
			</form>
		</td>
	</tr>
	<tr>
		<td align="right" style="border-top:2px #CCC solid">
			<button type="button" id="btnCetak" class="button" style="margin:10px">Cetak Kwitansi</button>
		</td>
	</tr>
</table>

<script>
	var APP = '<?php echo $a?>';
	var UID = '<?php echo $uid?>';
</script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/payment.js"></script>
