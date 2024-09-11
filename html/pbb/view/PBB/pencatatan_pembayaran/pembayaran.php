<?php
$uid     = $data->uid;
$uname     = $data->uname;

$sql     = "SELECT * FROM `cppmod_pbb_user_printer` WHERE CPM_UID = '$uid' AND CPM_MODULE = '$m'";
$result  = mysqli_query($DBLink, $sql);
$row     = mysqli_fetch_array($result);

$printer = str_replace("\\", "\\\\", $row['CPM_PRINTERNAME']);
$driver  = $row['CPM_DRIVER'];
$urlCekTagihan = 'http://' . $_SERVER['HTTP_HOST'] . '/portlet/portlet.php';

$tgl = date("d-m-Y");
?>
<script language="javascript" src="jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.9.2.custom.min.js"></script>
<div class="col-md-12">

	<div id="header-tax" name="header-tax">
		<form action="" method="post" id="inqform" name="inqform">
			<input type="hidden" id="uname" name="uname" value="<?php echo $uname; ?>">
			<div class="row">
				<div class="col-md-12">
					<h4 style="border-bottom: none;">Pencarian</h4>
				</div>
			</div>
			<div class="row">
				<div class="col-md-1" style="margin-top: 7px;">
					<input name="radiogroup" value="0" id="radiogroup0" checked="" type="radio"> NOP
					<input name="radiogroup" value="1" id="radiogroup1" style="display:none" type="radio"><span style="display:none">NPWP</span>
				</div>
				<div class="col-md-5">
					<div class="form-group">
						<!--<input name="nop_npwp" id="nop_npwp" maxlength="32" size="32" type="text" value="" class="form-control" style="width:260px">-->
						<div class="col-md-1" style="padding: 0">
							<input type="text" class="form-control nop-input-1" style="padding: 6px;" name="nop_npwp-1" id="nop_npwp-1" placeholder="PR">
						</div>
						<div class="col-md-1" style="padding: 0">
							<input type="text" class="form-control nop-input-2" style="padding: 6px;" name="nop_npwp-2" id="nop_npwp-2" placeholder="DTII" maxlength="2">
						</div>
						<div class="col-md-2" style="padding: 0">
							<input type="text" class="form-control nop-input-3" style="padding: 6px;" name="nop_npwp-3" id="nop_npwp-3" placeholder="KEC" maxlength="3">
						</div>
						<div class="col-md-2" style="padding: 0">
							<input type="text" class="form-control nop-input-4" style="padding: 6px;" name="nop_npwp-4" id="nop_npwp-4" placeholder="KEL" maxlength="3">
						</div>
						<div class="col-md-2" style="padding: 0">
							<input type="text" class="form-control nop-input-5" style="padding: 6px;" name="nop_npwp-5" id="nop_npwp-5" placeholder="BLOK" maxlength="3">
						</div>
						<div class="col-md-2" style="padding: 0">
							<input type="text" class="form-control nop-input-6" style="padding: 6px;" name="nop_npwp-6" id="nop_npwp-6" placeholder="NO.URUT" maxlength="4">
						</div>
						<div class="col-md-2" style="padding: 0">
							<input type="text" class="form-control nop-input-7" style="padding: 6px;" name="nop_npwp-7" id="nop_npwp-7" placeholder="KODE" maxlength="4">
						</div>
					</div>
				</div>
				<div class="col-md-1" style="margin-top: 7px;">
					Tahun :
				</div>
				<div class="col-md-1">
					<div class="form-group">
						<select name="year" id="year" class="form-control">
							<?php
							for ($i = date('Y'); $i >= 1995; $i--) {
								echo "<option>$i</option>";
							}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<button class="btn btn-primary btn-orange" name="inquiry" value="Inquiry" id="inquiry" onclick="sendInquiry();" type="button">Inquiry</button>
					<button class="btn btn-primary btn-blue" name="lihat-daftar" value="Lihat Daftar Tagihan" onclick="openListTag('<?php echo $urlCekTagihan; ?>');" type="button">Lihat Daftar Tagihan</button>
					<input value="<?php echo  $driver ?>" name="driver" id="driver" type="hidden">
					<input value="bayar" name="mode" id="mode" type="hidden">
				</div>
			</div>
			<script>
				$(".nop-input-1").on("keyup", function() {
					var len = $(this).val().length;

					let nopLengkap = $(this).val();
					if(len > 2) $(".nop-input-2").val(nopLengkap.substr(2, 2));
					if(len > 4) $(".nop-input-3").val(nopLengkap.substr(4, 3));
					if(len > 7) $(".nop-input-4").val(nopLengkap.substr(7, 3));
					if(len > 10) $(".nop-input-5").val(nopLengkap.substr(10, 3));
					if(len > 13) $(".nop-input-6").val(nopLengkap.substr(13, 4));
					if(len > 17) $(".nop-input-7").val(nopLengkap.substr(17, 1));

					if(len > 2) $(this).val(nopLengkap.substr(0, 2));

					if (len == 2) {
						$(".nop-input-2").focus();
					}
				});

				$(".nop-input-2").on("keyup", function() {
					var len = $(this).val().length;

					if (len == 2) {
						$(".nop-input-3").focus();
					}
				});

				$(".nop-input-3").on("keyup", function() {
					var len = $(this).val().length;

					if (len == 3) {
						$(".nop-input-4").focus();
					}
				});

				$(".nop-input-4").on("keyup", function() {
					var len = $(this).val().length;

					if (len == 3) {
						$(".nop-input-5").focus();
					}
				});

				$(".nop-input-5").on("keyup", function() {
					var len = $(this).val().length;

					if (len == 3) {
						$(".nop-input-6").focus();
					}
				});

				$(".nop-input-6").on("keyup", function() {
					var len = $(this).val().length;

					if (len == 4) {
						$(".nop-input-7").focus();
					}
				});

				$(".nop-input-7").on("keyup", function() {
					var len = $(this).val().length;

					if (len == 1) {
						sendInquiry();
					}
				});
			</script>
		</form>
	</div>

	<!-- BODY -->

	<div id="body-tax" name="body-tax">
		<table border="0" cellpadding="0" cellspacing="0" width="820px">
			<tbody>
				<tr>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b>Nama Wajib Pajak</b></font><b><b></b></b>
					</td>
					<td colspan="3" style="background-color:transparent;" width="40%">
						<font color="#999999"><b>Tanggal Jatuh Tempo<b></b></b></font>
					</td>
				</tr>
				<tr>
					<td colspan="3" style="background-color:transparent;" width="20%"><span id="wp-name" name="wp-name">-</span></td>
					<td colspan="3" style="background-color:transparent;" width="20%"><span id="wp-duedate" name="wp-duedate">0000-00-00</span></td>
				</tr>
				<tr>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b>Alamat Wajib Pajak</b></font><b><b></b></b>
					</td>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b>Tagihan</b></font><b><b></b></b>
					</td>
				</tr>
				<tr>
					<td style="background-color:transparent;">Alamat</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;"><span id="wp-address" name="wp-address">-</span></td>
					<td colspan="3" style="background-color:transparent;"><span id="wp-amount" name="wp-amount">Rp.</span></td>
				</tr>
				<tr>
					<td style="background-color:transparent;">Gampong</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;"><span id="wp-kelurahan" name="wp-kelurahan">-</span></td>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b>Biaya Denda</b></font><b><b></b></b>
					</td>
				</tr>
				<tr>
					<td style="background-color:transparent;">RT/RW</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;" width="40%"><span id="wp-rtRw" name="wp-rtRw">-</span></td>
					<td colspan="3" style="background-color:transparent;"><span id="wp-penalty" name="wp-penalty">Rp.</span></td>
				</tr>
				<tr>
					<td style="background-color:transparent;">Kecamatan</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;"><span id="wp-kecamatan" name="wp-kecamatan">-</span></td>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b>Biaya Admin</b></font><b><b></b></b>
					</td>
				</tr>
				<tr>
					<td style="background-color:transparent;">Kabupaten</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;"><span id="wp-kabupaten" name="wp-kabupaten">-</span></td>
					<td colspan="3" style="background-color:transparent;"><span id="wp-admin" name="wp-admin">Rp.</span></td>
				</tr>
				<tr>
					<td style="background-color:transparent;">Kode Pos</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;"><span id="wp-kdPos" name="wp-kdPos">-</span></td>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b>Total Tagihan</b></font>
					</td>
				</tr>
				<tr>
					<td colspan="3" style="background-color:transparent;">&nbsp;</td>
					<td colspan="3" style="background-color:transparent;"><span id="wp-totalamount" name="wp-totalamount">Rp.</span></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="footer-tax" name="footer-tax">

		<table border="0" cellpadding="4" cellspacing="1" width="900px">
			<tbody>
				<tr>
					<td colspan="10" style="background-color:transparent;"><b>Pembayaran</b></td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td style="background-color:transparent;"> Tanggal </td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;" width="15%"><input class="form-control srcTgl" name="tgl-bayar" id="tgl-bayar" type="text" size="9" maxlength="10" value="<?php echo $tgl; ?>"></td>
					<td style="background-color:transparent;"> Jumlah </td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;" width="15%"><input class="form-control" name="jml-bayar" value="" id="jml-bayar" readonly="readonly" type="text"></td>
					<td style="background-color:transparent;">Uang </td>
					<td style="background-color:transparent;">:</td>
					<!--				 <td style="background-color:transparent;" width="15%"><input name="jml-uang" value="" id="jml-uang" onkeypress="//return(currencyFormatI(this,'.',event));" onselect="return(onSelectClearFormat(this, '.'))" onblur="return(currencyFormatIC(this,'.'))" onkeyup="jml();" type="text"></td>-->
					<td style="background-color:transparent;" width="15%"><input class="form-control" name="jml-uang" value="" id="jml-uang" onkeypress="return(currencyFormatI(this,'.',event));" onselect="return(onSelectClearFormat(this, '.'))" onkeyup="jml();" type="text"></td>
					<td style="background-color:transparent;">Kembali </td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;" width="15%"><input class="form-control" name="jml-kembali" value="" id="jml-kembali" readonly="readonly" type="text"></td>
					<td style="background-color:transparent;"><button class="btn btn-primary bg-maka" style="margin-left:10px" name="payment" value="Bayar" id="payment" onclick="sendBayar()" type="button" disabled>Bayar</button></td>
				</tr>
				<tr>
					<td colspan="12" style="background: transparent;">
						<div class="form-group">
							<label>Catatan/Komentar/Keterangan:</label>
							<textarea name="catatan" id="catatan" cols="30" rows="10" class="form-control"></textarea>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

	</div>
	<br><br>
	<div id="tab-result"></div>

</div>
<applet name="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" height="0" width="0">
	<param name="printer" id="printer" value="<?php echo $printer ?>">
	<param name="sleep" value="200">
</applet>

<script language="javascript" src="view/PBB/pencatatan_pembayaran/pembayaran.js?t=<?php echo date('YmdHis'); ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$(".srcTgl").datepicker({
			dateFormat: 'dd-mm-yy'
		});
	});
</script>