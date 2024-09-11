<?php
require_once ('inc/report/driver-list.php');
global $data,$appDbLink,$application;

$uid = $data->uid;
$uname = $data->uname;
$ppid = $data->ppid;

$arAppConfig = $User->GetAppConfig($application);
$arModuleConfig = $User->GetModuleConfig($module);
//$module = $User->GetModuleName($module);

// deprecated 03/01 - title header
// function displayHeader(){
	// echo '<table width="100%"><tr class="transparent"><td class="transparent" valign="top"><div style="font-size:18pt;">Listrik Pra Bayar</div><div style="font-size:14pt;">Konfigurasi</div></td><th  width="500" align="right">&nbsp;</th></tr></table><br/>';
// }

function getConfigPrint(&$dataConnect,$ppid,$module,$appDbLink) {
	$OK = false;
	$dataConnect = array();
	$i = 0;
	//PP.donasi.kodewilayah
	$configCode = $ppid.".PP.voucher.PC.print.printer";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['printer'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','Epson LX-300+ II')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['printer'] = "Epson LX-300+ II";
			$OK = true;
		}
	}
	$configCode = $ppid.".PP.voucher.PC.print.driver";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['driver'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','epson')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['driver'] = "epson";
			$OK = true;
		}
	}
	$configCode = $ppid.".PP.voucher.PC.print.copy";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['copy'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','1')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['copy'] = "1";
			$OK = true;
		}
	}
	/*
	$configCode = $ppid.".PP.voucher.PC.print.monthReport";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	while ($row = mysqli_fetch_array($result)) {
		$dataConnect['mounthReport'] = $row['C_R_VALUE'];
		$OK = true;
	}*/
	$configCode = $ppid.".PP.voucher.PC.print.a4";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['a4'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','1')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['a4'] = "1";
			$OK = true;
		}
	}
	$configCode = $ppid.".PP.voucher.PC.print.autoprint";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['autoprint'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','0')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['autoprint'] = "0";
			$OK = true;
		}
	}
	mysqli_free_result($result);
	
	return $OK;
}

function displayPrintForm($module,$application,$enable=true,$printer=null,$copy=null,$monthReport=null,$a4=null,$ap=null,$driver=null) {
	global $function;
	$disabled ='';
	$checkedmr ='';
	$checkeda4 ='';
	$autoprint ='';
	if (!$enable) {
		$disabled = "disabled='disabled'";
	}
	if ($monthReport) {
		$checkedmr = 'checked="checked"';
	}
	if ($a4) {
		$checkeda4 = 'checked="checked"';
	}
	if ($ap) {
		$autoprint = 'checked="checked"';
	}
	$url64 = base64_encode("a=$application&m=$module&f=$function");

	
	echo "<h2>Pencetakan :</h2> \n";
	echo "<form id='form-Print' name='form-Print' method='post' action='main.php?param=$url64'>\n";
	echo "<table cellpadding='4' cellspacing='2' border='0' class='transparent'>";
	echo "<tr><td>Printer</td><td>:</td><td><select id='printersList' name='printersList' $disabled onchange=\"this.form.printer.value=this.options[this.selectedIndex].value;\"></select><input type='hidden' name='printer' id='printer' value='$printer' $disabled disableSelection='false' /><input type='button' name='btnSave' id='btnSave' value='Test Print' onclick='testPrint()'/></td></tr>\n";
	echo "<tr><td>Driver</td><td>:</td><td><select id='driver' name='driver' $disabled>";
	$driverList=getPrinterDriverList();
	$n=count($driverList);
	for($i=0;$i<$n;$i++){
		echo "<option value='".$driverList[$i][0]."'";
		if($driverList[$i][0]==$driver){
			echo " selected";
		}
		echo ">".$driverList[$i][1]."</option>";
	}
	echo "</select></td></tr>\n";
	echo "<tr><td>Jumlah Copy Rekap</td><td>:</td><td><input type='text' name='copy' id='copy' value='$copy' $disabled disableSelection='false' /></td></tr>\n";
	echo "<tr><td>Cetak Daftar/Rekap pakai A4</td><td>:</td><td><input type='checkbox' name='reportA4' id='reportA4' $checkeda4 $disabled /></td></tr>\n";
	echo "<tr><td>Cetak Langsung</td><td>:</td><td><input type='checkbox' name='auto-print' id='auto-print' $autoprint $disabled /></td></tr>\n";
	echo "<tr><td colspan='3' align='left'><input type='submit' name='btnSave' id='btnSave' value='Simpan' />";
	echo "</td></tr>\n";
	echo "</table>";
	 echo " <applet name='jZebra' code='jzebra.PrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>\n";
    echo "  <param name='printer' value='$printer'>\n";
    echo "  <param name='sleep' value='200'>\n";
    echo " </applet>\n";
	echo "\n<script language='JavaScript' src='function/voucher/voucher-config.js'></script>\n";
	 echo "  <script>\n";

   
	echo "   function monitorFinding2() {\n";
	echo "   var applet = document.jZebra;\n";
	echo "   if (applet != null) {\n";
	echo "      if (!applet.isDoneFinding()) {\n";
	echo "         window.setTimeout('monitorFinding2()', 100);\n";
	echo "      } else {\n";
	echo "      	   var listing = applet.getPrinters();\n";
	echo "      	   var printers = listing.split(',');\n";
	echo "             var printerslist=document.getElementById('printersList');\n";
	echo "      	   for(var i in printers){\n";
	echo "      	   	   printerslist.options[i]=new Option(printers[i]);\n";
	echo "             	   if(printers[i]=='".str_replace("\\","\\\\",$printer)."')\n";
	echo "             	      document.getElementById('printersList').selectedIndex=i;\n";
    echo "             	   }\n";
	echo "			   document.getElementById('printer').value=printerslist.options[printerslist.selectedIndex].value;\n";
	echo "      }\n";
	echo "   } else {\n";
    echo "           alert('Applet not loaded!');\n";
    echo "       }\n";
    echo "     }\n";
	echo "	findPrinters('".str_replace("\\","\\\\",$printer)."');\n";
    echo " </script>\n";
}

function displayPrintFormWithData($ppid,$module,$application,$appDbLink) {
	if (getConfigPrint($data,$ppid,$module,$appDbLink)) {
		displayPrintForm($module,$application,true,$data['printer'],$data['copy'],$data['monthReport'],$data['a4'],$data['autoprint'],$data['driver']);
	} else {
		displayPrintForm($module,$application,true);
	}
}

function updatePrint($ppid,$module,$appDbLink,$printer,$copy,$monthReport,$a4,$autoprint,$driver) {
	//querying
	$configCode = $ppid.".PP.voucher.PC.print.printer";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($printer),
		mysql_real_escape_string($printer)
	);
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.voucher.PC.print.driver";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($driver),
		mysql_real_escape_string($driver)
	);
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.voucher.PC.print.copy";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($copy),
		mysql_real_escape_string($copy)
	);
	$result = mysqli_query($appDbLink, $query);
	/*
	$configCode = $ppid.".PP.voucher.PC.print.monthReport";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($monthReport),
		mysql_real_escape_string($monthReport)
	);*/
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.voucher.PC.print.a4";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($a4),
		mysql_real_escape_string($a4)
	);
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.voucher.PC.print.autoprint";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($autoprint),
		mysql_real_escape_string($autoprint)
	);
	
	$result = mysqli_query($appDbLink, $query);
	//echo mysqli_error($DBLink);

	return (mysql_affected_rows() == 1);
}

function isUpdatePrintRequest() {
	return (@isset($_REQUEST['printer']));
}

function getConfigOther(&$dataConnect,$ppid,$module,$appDbLink) {
	$OK = false;
	$dataConnect = array();
	$i = 0;
	//PP.donasi.kodewilayah
	$configCode = $ppid.".PP.voucher.PC.other.profit";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	while ($row = mysqli_fetch_array($result)) {
		$dataConnect['profit'] = $row['C_R_VALUE'];
		$OK = true;
	}
	return $OK;
}

function displayOtherForm($module,$application,$enable=true,$profit=null) {
	global $function;
	$disabled ='';
	if (!$enable) {
		$disabled = "disabled='disabled'";
	}
	$url64 = base64_encode("a=$application&m=$module&f=$function");
	echo "<h2>Lain-lain :</h2> \n";
	echo "<form id='form-Other' name='form-Other' method='post' action='main.php?param=$url64'>\n";
	echo "<table cellpadding='4' cellspacing='2' border='0' class='transparent'>";
	echo "<tr><td>Keutungan Tiap Item</td><td>:</td><td><input type='text' name='profit' id='profit' value='$profit' $disabled /></td></tr>\n";
	echo "<tr><td colspan='3' align='right'><input type='submit' name='btnSave' id='btnSave' value='Simpan' /></td></tr>\n";
	echo "</table>";
}

function displayOtherFormWithData($ppid,$module,$application,$appDbLink) {
	if (getConfigOther($data,$ppid,$module,$appDbLink)) {
		displayOtherForm($module,$application,true,$data['profit']);
	} else {
		displayOtherForm($module,$application,true);
	}
}

function updateOther($ppid,$module,$appDbLink,$profit) {
	//querying
	$configCode = $ppid.".PP.voucher.PC.other.profit";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($profit),
		mysql_real_escape_string($profit)
	);
	$result = mysqli_query($appDbLink, $query);
	echo mysqli_error($appDbLink);
	return (mysql_affected_rows() == 1);
}

function isUpdateOtherRequest() {
	return  @isset($_REQUEST['profit']);
}
if(isset($data)){
	// deprecated 03/01 - title header
	// displayHeader();
	if (isUpdatePrintRequest()) {
		$mr=@isset($_REQUEST['monthReport']) ? 1:'';
		$a4=@isset($_REQUEST['reportA4']) ? 1:'';
		$autoprint=@isset($_REQUEST['auto-print']) ? 1:'';
		updatePrint($ppid,$module,$appDbLink,$_REQUEST['printer'],$_REQUEST['copy'],$mr,$a4,$autoprint,$_REQUEST['driver']);
		displayPrintFormWithData($ppid,$module,$application,$appDbLink);
	} else {
		displayPrintFormWithData($ppid,$module,$application,$appDbLink);
	}
	if (isUpdateOtherRequest()) {
		updateOther($ppid,$module,$appDbLink,$_REQUEST['profit']);
		displayOtherFormWithData($ppid,$module,$application,$appDbLink);
	} else {
		displayOtherFormWithData($ppid,$module,$application,$appDbLink);
	}
} else {
	echo "Session Expire";
}

?>