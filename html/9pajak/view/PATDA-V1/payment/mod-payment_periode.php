<?php
session_start();
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
				<table border="0" align="center" class="child" style="padding:10px">
					<tr valign="top">
						<td width="25%">NPWPD <b class="isi">*</b></td>
						<td width="75%"> : 
							<select name="npwpd" id="npwpd" style="width: 306px;"></select>
						</td>
					</tr>
					<tr>
						<td>Jenis Pajak <b class="isi">*</b></td>
						<td> :
							<select name="simpatda_type" id="simpatda_type" style="width:306px">
								<option selected value =''>Pilih Jenis Pajak</option>
								<?php
								foreach ($list_simpatda_type as $x => $y) {
									echo "<option value='{$x}' ".($simpatda_type == $x? 'selected':'').">".STR_PAD($x,2,0,STR_PAD_LEFT)." - ".strtoupper($y)."</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Tahun Pajak <b class="isi">*</b></td>
						<td> : 
							<select name="simpatda_tahun_pajak" id="simpatda_tahun_pajak">
								<option selected value =''>Pilih Tahun Pajak</option>
								<?php
								for ($th = date("Y"); $th >= date("Y")-5; $th--) {
									echo "<option value='{$th}' ".($simpatda_tahun_pajak == $th? 'selected':'').">{$th}</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Bulan Pajak <b class="isi">*</b></td>
						<td> : 
							<select name="simpatda_bulan_pajak" id="simpatda_bulan_pajak">
								<option selected value =''>Pilih Bulan Pajak</option>
								<?php
								foreach ($list_simpatda_bulan_pajak as $x => $y) {
									echo "<option value='{$x}' ".($simpatda_bulan_pajak == $x? 'selected':'').">".strtoupper($y)."</option>";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td width="250">Kode Bayar<b class="isi">*</b></td>
						<td> : 
							<select name="payment_code" id="payment_code" style="width:305px"></select>
						</td>
					</tr>
					<tr>
						<td>Kode Area <b class="isi">*</b></td>
						<td> : 
							<select name="area_code" id="area_code">
								<?php
								foreach ($list_area_code as $x => $y) {
									echo "<option value='{$x}'>".strtoupper($y)."</option>";
								}
								?>
							</select>
							<br/><br/>
						</td>
					</tr>
				</table>
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
								<tr>
									<td>Uang </td>
									<td>:</td>
									<td><input name="jml-uang" value="0" id="jml-uang" style="text-align:right" type="text"></td>
								</tr>
								<tr>
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
			<button type="button" id="btnBayar" class="button" style="margin:10px">Bayar</button>
		</td>
	</tr>
</table>
<script language="javascript" src="inc/select2/js/select2.full.min.js"></script>
<script>
	var APP = '<?php echo $a?>';
	var UID = '<?php echo $uid?>';
</script>
<script type="text/javascript" src="function/<?php echo "{$DIR}/{$modul}"; ?>/payment.js"></script>

