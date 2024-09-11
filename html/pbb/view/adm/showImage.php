<html>

<head>
<?php

// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'adm', '', dirname(__FILE__))).'/';
$sRootPath = "../../";
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-dms-c.php");
require_once($sRootPath."inc/central/setting-central.php");

$DBLink = NULL;
$DBConn = NULL;
$userModuleView = null;
$grantedModule = false;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_DMS_FILENAME);
	exit(1);
}

$Setting = new SCANCentralSetting(DEBUG, LOG_DMS_FILENAME, $DBLink);

if (!file_exists($sRootPath."chosenStyle")) {
	$fStyle = fopen($sRootPath."chosenStyle", "w");
	fputs($fStyle, "vpos");
	fclose($fStyle);
}

$fStyle = fopen($sRootPath."chosenStyle", "r");
$appsSettingId = fgets($fStyle, 1024);
fclose($fStyle);

$arSetting = null;
$bOK = $Setting->GetApplicationSetting($arSetting, $appsSettingId);
if ($bOK) {
	$title = $arSetting["title"];
	$stylePath = $arSetting["stylePath"];
	// trim slash
	$stylePath = str_replace("/", "", $stylePath);
	$stylePath = $sRootPath . "style/" . $stylePath;

	echo "<title>$title</title>";
	echo "<link rel='stylesheet' href='$stylePath/style.css' type='text/css'/>";
	echo "<link rel='shortcut icon' href='$stylePath/favicon.ico'/>";
?>
	<script type='text/javascript'>
		function clickImage(imageFile) {
			parent.opener.document.getElementById('imageFunc').value = imageFile;
			parent.opener.document.getElementById('imageSrcFunc').src = "image/" + imageFile;
			self.close();
		}
	</script>
</head>

<body>
	<div class='spacer10'></div>
	<table cellpadding='3' cellspacing='3'>
<?php
}
	$handle = opendir($sRootPath."image/icon/");
	
	function validImage($file) {
		$file == strtolower($file);
		$indexDot = strrpos($file, ".");
		$ext = substr($file, $indexDot + 1);
		
		$arExt = array("gif", "jpg", "png", "bmp", "jpeg");
		return in_array($ext ,$arExt);
	}

	$arImage = array();
	$i = 0;
    while (false !== ($file = readdir($handle))) {
		if (validImage($file)) {
			$arImage[$i] = $file;
			$i++;
		}
	}
	
	// sort filename
	asort($arImage);
	foreach ($arImage as $file) {
		echo "<tr>";
		echo "<td bgcolor='white'>$file</td>";
		echo "<td width='100px' align='center' bgcolor='white'>";
		echo "<a href='#' onClick='clickImage(\"$file\")'>";
		echo "<img src='".$sRootPath."image/icon/$file' alt='' border='0'></img>";
		echo "</a>";
		echo "</td>";
		echo "</tr>";
	}
	
	closedir($handle);
?>	
	</table>
</body>

</html>
