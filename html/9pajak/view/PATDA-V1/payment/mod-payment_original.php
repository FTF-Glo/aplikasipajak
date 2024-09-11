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
<br/>
<table class="main" width="1200" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="center" class="subtitle" colspan="2"><b>FORM PEMBAYARAN</b></td>
	</tr>         
	<tr valign="top">
		<td width="45%" >
			<form id="form-inquiry" method="post">
				<table border="0" width="100%" align="center" class="child" style="padding:10px">
					<tr>
						<td width="25%">Kode Verifikasi<b class="isi">*</b></td>
						<td width="75%"> : 
							<input type="text" name="payment_code" id="payment_code" style="width:200px" autocomplete="off" placeholder="masukan kode verifikasi">
						</td>
					</tr>
				</table>
				
				<div id="area_identity">					
				</div>
				
			</form>
		</td>
		
		<td width="55%" style="border-left:2px solid #CCC">
			<form id="form-bayar" method="post">
				<table border="0" width="100%">
					<tr valign="top">
						<td width="55%">
							<table width="100%" border="0" align="center" class="" style="padding:10px">                    
								<tr>
									<td colspan="3"><b>Inquiry :</b></td>
								</tr>
								<tr valign="top">
									<td width="40%">Tanggal Jatuh Tempo</td>
									<td width="60%"> : <span id="expired_date">0000-00-00</span></td>
								</tr>
								<tr>
									<td>Tagihan</td>
									<td> : <span id="simpatda_dibayar">0</span></td>
								</tr>
								<tr>
									<td>Biaya Denda</td>
									<td> : <span id="patda_denda">0</span></td>
								</tr>
								<tr>
									<td>Biaya Admin</td>
									<td> : <span id="patda_admin_gw">0</span></td>
								</tr>
								<tr>
									<td>Total Tagihan</td>
									<td> : <span id="patda_total_bayar">0</span></td>
								</tr>
							</table>
						</td>
						
						<td width="45%">
							<table width="100%" border="0" cellpadding="4" cellspacing="1">
								<tr>
									<td colspan="3"><b>Pembayaran :</b></td>
								</tr>
								<tr>
									<td width="50"> Tanggal </td>
									<td width="2">:</td>
									<td width="20"><input id="payment_paid" readonly="readonly" type="text" size="9" maxlength="10" value="<?php echo date("Y-m-d")?>"></td>
								</tr>
								<tr>
									<td> Jumlah </td>
									<td>:</td>
									<td><input name="jml-bayar" value="0" id="jml-bayar" style="text-align:right" readonly="readonly" type="text"></td>
								</tr>
								<tr style="display:none;">
									<td>Uang </td>
									<td>:</td>
									<td><input name="jml-uang" value="0" id="jml-uang" style="text-align:right" type="text"></td>
								</tr>
								<tr style="display:none;">
									<td>Kembali </td>
									<td>:</td>
									<td>
										<input name="jml-kembali" value="0" id="jml-kembali" style="text-align:right" readonly="readonly" type="text">
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
	<tr>
		<td align="right" style="border-top:2px #CCC solid">
			<button type="button" id="btnInquiry" class="button" style="margin:10px">Inquiry</button>
		</td>
		<td align="right" style="border-top:2px #CCC solid">
			<button type="button" id="btnBayar" class="button" style="margin:10px">Verifikasi</button>
		</td>
	</tr>
</table>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script>
	var APP = '<?php echo $a?>';
	var UID = '<?php echo $uid?>';
</script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/payment.js"></script>

