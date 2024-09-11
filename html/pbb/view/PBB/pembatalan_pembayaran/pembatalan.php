<?php
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);

// var_dump($appConfig);
$uid     = $data->uid;
$uname     = $data->uname;

$sql     = "SELECT * FROM `CPPMOD_PBB_USER_PRINTER` WHERE CPM_UID = '$uid' AND CPM_MODULE = '$m'";
$result  = mysqli_query($DBlink, $sql);
$row     = mysqli_fetch_array($DBlink, $result);

$printer = str_replace("\\", "\\\\", $row['CPM_PRINTERNAME']);
$driver  = $row['CPM_DRIVER'];
// $urlCekTagihan = 'http://'.$_SERVER['HTTP_HOST'].'/portlet/';
$urlCekTagihan = $appConfig['PORTLET_LINK'];

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
			<table border="0" cellpadding="4" cellspacing="1" width="820px">
				<tr>
					<td colspan="4" style="background-color:transparent;"><b>Pencarian</b></td>
				</tr>
				<tr>
					<td style="background-color:transparent;" width="25%"><input name="radiogroup" value="0" id="radiogroup0" checked type="radio"> Kode Bayar </td>
					<td style="background-color:transparent;" width="10%"><input name="kode_bayar" id="kode_bayar" maxlength="32" size="32" type="text" placeholder="Kode Bayar"></td>
				</tr>
				<tr>
					<td style="background-color:transparent;" width="25%"><input name="radiogroup" value="1" id="radiogroup0" type="radio"> NOP </td>
					<!-- <td style="background-color:transparent;" width="10%"><input name="radiogroup" value="1" id="radiogroup1" style="display:none" type="radio"><span style="display:none">NPWP</span> </td> -->
					<td style="background-color:transparent;" width="10%"><input name="nop" id="nop" maxlength="32" size="32" type="text" placeholder="NOP"></td>
					<td style="background-color:transparent;" width="25%">
						Tahun : <select name="year" id="year">
							<?php
							for ($i = date('Y'); $i >= 1995; $i--) {
								echo "<option>$i</option>";
							}
							?>
						</select>
					</td>
					<td style="background-color:transparent;" width="60%" colspan="2">
						<input name="inquiry" value="Inquiry" id="inquiry" onclick="inquiryPembatalan()" type="button">
						<!-- <input name="lihat-daftar" value="Lihat Daftar Tagihan" onclick="openListTag('<?php echo $urlCekTagihan; ?>');" type="button"> -->
						<input value="<?= $driver ?>" name="driver" id="driver" type="hidden">
						<input value="kode_bayar" name="mode" id="mode" type="hidden">
					</td>
				</tr>
			</table>
		</form>
	</div>

	<!-- BODY -->

	<div id="body-tax" name="body-tax">
		<table border="0" cellpadding="0" cellspacing="0" width="820px">
			<tbody>
				<tr>
					<td colspan="3" style="background-color:transparent;">
						<font color="#999999"><b id="nm_pajak">Nama Kolektor</b></font><b><b></b></b>
					</td>
					<td colspan="3" style="background-color:transparent;" width="40%">
						<font color="#999999"><b>Tanggal Pembayaran<b></b></b></font>
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
					<td colspan="10" style="background-color:transparent;"><b>Pembatalan</b></td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td style="background-color:transparent;" width="15%"> Tanggal </td>
					<td style="background-color:transparent;" width="2%">:</td>
					<td style="background-color:transparent;" width="50%"><input class="srcTgl" name="tgl-batal" id="tgl-batal" readonly="readonly" type="text" size="9" maxlength="10" value="<?php echo $tgl; ?>"></td>
					<td style="background-color:transparent;"><input name="cancel" value="Batal" id="cancel" onclick="pembatalan()" type="button" disabled></td>
				</tr>

				<tr>
					<td style="background-color:transparent;" width="15%">Keterangan</td>
					<td style="background-color:transparent;">:</td>
					<td style="background-color:transparent;" width="15%"><textarea name="keterangan" id="keterangan" cols="35" rows="5"></textarea></td>
				</tr>
			</tbody>
		</table>

	</div>
	<br><br>
	<div id="tab-result"></div>
</div>
<!-- applet name="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" height="0" width="0">
	<param name="printer" id="printer" value="<?= $printer ?>">
	<param name="sleep" value="200">
</applet -->

<script language="javascript" src="view/PBB/pembatalan_pembayaran/pembatalan.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		document.getElementById("nop").disabled = true;
		document.getElementById("year").disabled = true;
		document.getElementById("cancel").disabled = true;
		$(".srcTgl").datepicker({
			dateFormat: 'dd-mm-yy',
			timeFormat: "hh:mm:ss",
			maxDate: new Date()
		});
		$('input[type="radio"]').click(function() {
			clearInput();
			document.getElementById("cancel").disabled = true;
			document.getElementById("kode_bayar").value = "";
			document.getElementById("nop").value = "";
			var inputValue = $(this).attr("value");
			if (inputValue == "0") {
				$("#nm_pajak").html("Nama Kolektor");
				document.getElementById("nop").disabled = true;
				document.getElementById("year").disabled = true;
				document.getElementById("kode_bayar").disabled = false;
				document.getElementById("mode").value = "kode_bayar";
			} else {
				$("#nm_pajak").html("Nama Wajib Pajak");
				document.getElementById("nop").disabled = false;
				document.getElementById("year").disabled = false;
				document.getElementById("kode_bayar").disabled = true;
				document.getElementById("mode").value = "nop";
			}
		});
		// if (condition) {

		// }
	});
</script>