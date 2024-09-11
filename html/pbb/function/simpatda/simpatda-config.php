<?php
require_once ('inc/payment/json.php');
require_once ('inc/report/driver-list.php');
global $data,$appDbLink,$application;

$uid = $data->uid;
$uname = $data->uname;
$ppid = $data->ppid;

$arAppConfig = $User->GetAppConfig($application);
$arModuleConfig = $User->GetModuleConfig($module);
$cid =  $User->getCIDFromPPID($ppid);
//$module = $User->GetModuleName($module);

//////////////////
// MAIN PROGRAM //
//////////////////

if (isUpdatePrintRequest()) {
	$a4=@isset($_REQUEST['reportA4']) ? 1:'';
	$kabkot=@isset($_REQUEST['kabkot']) ? $_REQUEST['kabkot']:0;
	updatePrint($ppid,$module,$appDbLink,$_REQUEST['printer'],$_REQUEST['copy'],$a4,$kabkot,$_REQUEST['driver']);
}
displayPrintFormWithData($ppid,$module,$application,$appDbLink);


//LOCAL FUNCTION
function getConfigPrint(&$dataConnect,$ppid,$module,$appDbLink) {
	$OK = false;
	$dataConnect = array();
	$i = 0;
	//PP.donasi.kodewilayah
	$configCode = $ppid.".PP.simpatda.PC.print.printer";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['printer'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','Epson LX-300+ /II')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['printer'] = "Epson LX-300+ /II";
			$OK = true;
		}
	}
	$configCode = $ppid.".PP.simpatda.PC.print.driver";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY = '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['driver'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('$configCode','other')");
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['driver'] = "other";
			$OK = true;
		}		
	}
	$configCode = $ppid.".PP.simpatda.PC.print.copy";
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
	$configCode = $ppid.".PP.simpatda.PC.print.a4";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['a4'] = $row['C_R_VALUE'];
		$OK = true;
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE)	VALUES('%s','0')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['a4'] = "0";
			$OK = true;
		}
	}
	mysqli_free_result($result);
	
	$configCode = $ppid.".PP.simpatda.PC.kabkot";
	$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
	$result = mysqli_query($appDbLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$dataConnect['kabkot'] = $row['C_R_VALUE'];
		$OK = true;
		
	}else{
		$query = sprintf( "INSERT INTO c_registry(C_R_KEY,C_R_VALUE) VALUES('%s','0')", mysql_real_escape_string($configCode));
		if(mysqli_query($appDbLink, $query)){
			$dataConnect['kabkot'] = "0";
			$OK = true;
		}
	}
	mysqli_free_result($result);
	return $OK;
}
function getDataTestPrint(&$testPrintValue) {
    global $cid,$appDbLink,$uname;
    $query = sprintf( "SELECT * FROM csccore_bank A, csccore_bank_downline B
        WHERE A.CSC_B_ID = B.CSC_B_ID AND B.CSC_D_ID = '%s'
        GROUP BY A.CSC_B_ID LIMIT 0,1", mysql_real_escape_string($cid));
    $result = mysqli_query($appDbLink, $query);
    while ($row = mysqli_fetch_array($result)) {
         $testPrintValue['BANK_NAME'] = strtoupper($row['CSC_B_NAME']);
    }
    $testPrintValue['OPERATOR'] = strtoupper($uname);
    mysqli_free_result($result);
}
function displayPrintForm($module,$application,$enable=true,$printer=null,$copy=null,$a4=null,$kabkot,$driver=null) {
    global $function;
    $disabled ='';
    // $checkedmr ='';
    $checkeda4 ='';
    $file='';
    if (!$enable) {
            $disabled = "disabled='disabled'";
    }
    // if ($monthReport) {
            // $checkedmr = 'checked="checked"';
    // }
    if ($a4) {
            $checkeda4 = 'checked="checked"';
    }
	if ($kabkot==0) $checked0 = 'checked';
	if ($kabkot==1) $checked1 = 'checked';
    $url64 = base64_encode("a=$application&m=$module&f=$function");
	?>
	
    <form id='form-Print' name='form-Print' method='post' action='main.php?param=<?php echo $url64?>' onsubmit="return testField()">
    <table cellpadding='4' cellspacing='2' border='0' class='transparent'>
    <tr><td>Kabupaten / Kota</td><td>:</td><td>
    <input type='radio' name='kabkot' id='kabkot0' value='0' <?php echo $checked0?> /> Kabupaten
    <input type='radio' name='kabkot' id='kabkot1' value='1' <?php echo $checked1?> /> Kota</td></tr>
    <?
	echo "<tr><td>Printer</td><td>:</td><td><select id='printersList' name='printersList' $disabled onchange=\"this.form.printer.value=this.options[this.selectedIndex].value;\"></select><input type='hidden' name='printer' id='printer' value='$printer' $disabled /><input type='button' name='btnSave' id='btnSave' value='Test Print' onClick='testPrint()'/></td></tr>\n";
	echo "<tr><td>Driver</td><td>:</td><td><select id='driver' name='driver' $disabled>";
	$driverList=getPrinterDriverList();
	$n=count($driverList);
	for($i=0;$i<$n;$i++){
		echo "<option value='".$driverList[$i][0]."'";
		if($driverList[$i][0]==$driver){
			echo "selected";
		}
		echo ">".$driverList[$i][1]."</option>";
	}
	echo "</select></td></tr>\n";
	?>
    <tr><td>Jumlah Copy Rekap</td><td>:</td><td><input type='text' name='copy' id='copy' value='<?php echo $copy?>' <?php echo $disabled?> disableSelection="false" /></td></tr>
    <tr><td>Cetak Daftar/Rekap pakai A4</td><td>:</td><td><input type='checkbox' name='reportA4' id='reportA4' <?php echo $checkeda4?> <?php echo $disabled?> /></td></tr>
    <tr><td colspan='3' align='left'>
		<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
			<param name='printer' value='<?php echo $printer?>'>
			<param name='sleep' value='200'>
		</applet>
		<input type='submit' name='btnSave' id='btnSave' value='Simpan'/>
    </td></tr>
    </table>
	</form>
	<?
	echo "\n<script language='JavaScript' src='function/simpatda/simpatda-config.js'></script>\n";
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
		displayPrintForm($module,$application,true,$data['printer'],$data['copy'],$data['a4'],$data['kabkot'],$data['driver']);
	} else {
		displayPrintForm($module,$application,true);
	}
}

function updatePrint($ppid,$module,$appDbLink,$printer,$copy,$a4,$kabkot,$driver) {
	//querying
	$configCode = $ppid.".PP.simpatda.PC.print.printer";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($printer),
		mysql_real_escape_string($printer)
	);
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.simpatda.PC.print.copy";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($copy),
		mysql_real_escape_string($copy)
	);
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.simpatda.PC.print.driver";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($driver),
		mysql_real_escape_string($driver)
	);
	$result = mysqli_query($appDbLink, $query);
	$configCode = $ppid.".PP.simpatda.PC.print.a4";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($a4),
		mysql_real_escape_string($a4)
	);
	$result = mysqli_query($appDbLink, $query);
	
	$configCode = $ppid.".PP.simpatda.PC.kabkot";
	$query = sprintf("INSERT INTO c_registry (C_R_KEY,C_R_VALUE) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE C_R_VALUE = '%s'",
		mysql_real_escape_string($configCode),
		mysql_real_escape_string($kabkot),
		mysql_real_escape_string($kabkot)
	);
	$result = mysqli_query($appDbLink, $query);
	
	echo mysqli_error($DBLink);

	return (mysql_affected_rows() == 1);
}

function isUpdatePrintRequest() {
	return  @isset($_REQUEST['printer'])
		&& @isset($_REQUEST['copy'])
		//&& @isset($_REQUEST['monthReport'])
		//&& @isset($_REQUEST['reportA4'])
		;
}


function isUpdateOtherRequest() {
	return  @isset($_REQUEST['limit'])
		&& @isset($_REQUEST['areaCode'])
		&& @isset($_REQUEST['idpel'])
		//&& @isset($_REQUEST['reportA4'])
		;
}

?>