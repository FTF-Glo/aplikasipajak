<?php
$uid     	  = $data->uid;
$driver       = $_REQUEST['driver'];
$printersList = mysqli_escape_string($DBLink, $_REQUEST['printersList']);

if ($printersList) {
	$sql = "DELETE FROM `cppmod_pbb_user_printer` WHERE (`CPM_UID`='$uid') AND (`CPM_MODULE`='$m')";
	mysqli_query($DBLink, $sql);
	$sql = "INSERT INTO `cppmod_pbb_user_printer` (`CPM_UID`, `CPM_MODULE`, `CPM_PRINTERNAME`, `CPM_DRIVER`) VALUES 
		        ('$uid', '$m', '$printersList', '$driver')";
	mysqli_query($DBLink, $sql);
}

$sql     = "SELECT * FROM `cppmod_pbb_user_printer` WHERE CPM_UID = '$uid' AND CPM_MODULE = '$m'";
$result  = mysqli_query($DBLink, $sql);
$row     = mysqli_fetch_array($result);

$printer = str_replace("\\", "\\\\", $row['CPM_PRINTERNAME']);
$driver  = $row['CPM_DRIVER'];
$param   = "a=$a&m=$m&f=$f";
$param   = base64_encode($param);
?>

<div class="col-md-12">
	<form action="main.php?param=<?php echo $param ?>=" method="post" name="form_config">
		<table class="transparent" border="0" cellpadding="4" cellspacing="2">
			<tr>
				<td>Printer</td>
				<td>:</td>
				<td>
					<select id="printersList" name="printersList"></select>
					<input name="printer" id="printer" value="Microsoft XPS Document Writer" type="hidden">
				</td>
			</tr>
			<tr>
				<td>Driver</td>
				<td>:</td>
				<td>
					<select id="driver" name="driver">
						<option value="other" <?php if ($driver == 'other') echo 'selected' ?>>Other</option>
						<option value="epson" <?php if ($driver == 'epson') echo 'selected' ?>>Epson</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="3" align="left">
					<applet name="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" height="0" width="0">
						<param name="printer" value="EPSON LX-300+ /II">
						<param name="sleep" value="200">
					</applet>
					<input name="btnSave" id="btnSave" value="Simpan" type="submit">
				</td>
			</tr>
		</table>
	</form>
</div>

<script>
	function listPrinter() {
		var applet = document.jZebra;
		if (applet != null) {
			if (!applet.isDoneFinding()) window.setTimeout('listPrinter()', 1000);
			else {
				var listing = applet.getPrinters();
				var printers = listing.split(',');
				var printerslist = document.getElementById('printersList');
				for (var i in printers) {
					printerslist.options[i] = new Option(printers[i]);
					if (printers[i] == '<?php echo $printer ?>')
						document.getElementById('printersList').selectedIndex = i;
				}
				document.getElementById('printer').value = printerslist.options[printerslist.selectedIndex].value;
			}
		} else alert('Applet not loaded!');
	}
	listPrinter();
</script>