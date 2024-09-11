<?php
//session_start();
$DIR = "PATDA-V1";
$modul = "payment";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
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
<link href="inc/select2/css/select2.min.css" rel="stylesheet" type="text/css">
<script language="javascript" src="inc/js/autoNumeric.min.js"></script>
<script language="javascript" src="inc/js/jquery.validate.min.js"></script>

<div class="container lm-container">
	<div class="row">
		<div id="subMenu" class="col-md-12 subtitle lm-title" align="center">
			<b>FORM PEMBAYARAN</b>
		</div>
	</div>
	<form id="form-inquiry" method="post">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>Kode Verifikasi<b class="isi">*</b></label>
					<input type="text" name="payment_code" id="payment_code" class="form-control" autocomplete="off" placeholder="Masukan kode verifikasi">
				</div>
			</div>
		</div>
		<div id="area_identity">
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group" style="display: flex; justify-content: center;">
					<button type="button" id="btnInquiry" class="btn btn-primary lm-btn" style="margin:10px;">Inquiry</button>
				</div>
			</div>
		</div>
	</form>
	<form id="form-bayar" method="post">
		<div class="row">
			<div class="col-md-6">
				<div class="lm-subtitle" style="font-size: large !important">Inquiry</div>
				<hr />
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Tanggal Jatuh Tempo</label>
							<span id="expired_date" style="display: block">0000-00-00</span>
						</div>

					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Tagihan</label>
							<span id="simpatda_dibayar" style="display: block">0</span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Biaya Denda</label>
							<span id="patda_denda" style="display: block">0</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Biaya Admin</label>
							<span id="patda_admin_gw" style="display: block">0</span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Total Tagihan</label>
							<span id="patda_total_bayar" style="display: block">0</span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="lm-subtitle" style="font-size: large !important">Pembayaran</div>
				<hr />
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label> Tanggal </label>
							<input id="payment_paid" class="form-control" style="width: 80%; display: inline-block" readonly="readonly" type="text" size="9" maxlength="10" value="<?php echo date("Y-m-d") ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label> Jumlah </label>
							<input name="jml-bayar" value="0" id="jml-bayar" class="form-control" style="text-align:right" readonly="readonly" type="text">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6" style="display:none;">
						<div class="form-group">
							<label>Uang </label>
							<input name="jml-uang" value="0" id="jml-uang" class="form-control" style="text-align:right" type="text">
						</div>
					</div>
					<div class="col-md-6" style="display:none;">
						<div class="form-group">
							<label>Kembali </label>
							<input name="jml-kembali" value="0" id="jml-kembali" class="form-control" style="text-align:right" readonly="readonly" type="text">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group" style="display: flex; justify-content: right;">
							<button type="button" id="btnBayar" class="btn btn-primary lm-btn" style="margin:10px">Verifikasi</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script>
	var APP = '<?php echo $a?>';
	var UID = '<?php echo $uid?>';
</script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/payment.js"></script>

