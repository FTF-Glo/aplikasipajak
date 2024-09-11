<?php
//ini_set("display_errors",1); error_reporting(E_ALL);
if (!isset($data)) {
	die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig['terminalColumn'])) {
	$terminalColumn = $arAreaConfig['terminalColumn'];
	$accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
	if (!$accessible) {
		echo "Illegal access";
		return;
	}
}

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$User	 	= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$tahun		= $appConfig['tahun_tagihan'];
?>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="function/PBB/dbkb/dbkb_sk.js"></script>
<center>
	<!-- jZebra applet-->
	<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
		<param name='printer' id='printer' value='LQ-2190'>
		<param name='sleep' value='200'>
	</applet>
	<!-- end jZebra-->

	<font size="4"><b>Klik Cetak SK DBKB untuk mencetak SK DBKB</b></font> <br><br>
	<!-- <table style="border: 1px solid;" width="200" height="50"> -->
	<!-- <tr> -->
	<!-- <td align="center"> -->
	<input type="button" style="width: 170px; heigth: 35px;" class="btn btn-primary btn-orange" name="cetak_sk" value="Cetak SK DBKB (pdf)" id="cetak_sk" onclick="printToPDF()" />
	<!-- </td> -->
	<!-- </tr> -->
	<!-- </table><br /> -->
	<!-- <table style="border: 1px solid;" width="200" height="50"> -->
	<!-- <tr> -->
	<!-- <td align="center"> -->
	<input type="button" style="width: 170px; heigth: 35px;" class="btn btn-primary btn-blue" name="cetak_sk_printer" value="Cetak SK DBKB (print)" id="cetak_sk_printer" onclick="printToPrinter()" />
	<!-- </td> -->
	<!-- </tr> -->
	<!-- </table> -->
</center>

<input type="hidden" value="<?php echo $a ?>" id="appID">

<script type="text/javascript">
	function printToPDF() {
		var params = {
			appId: '<?php echo $a; ?>',
			uname: '<?php echo $uname; ?>'
		};
		console.log("print ...");
		params = Base64.encode(Ext.encode(params));
		window.open('./function/PBB/dbkb/dbkb_sk_print_svc.php?q=' + params, '_newtab');
	}
</script>