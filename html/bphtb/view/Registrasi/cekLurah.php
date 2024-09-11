<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/central/session-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}



$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

//print_r($_REQUEST);

if(@isset($_REQUEST['kota']) && @isset($_REQUEST['area'])){
	$kota = ($_REQUEST['kota']);
	$area = ($_REQUEST['area']);
	if(trim($kota)=="pilih")
	{
		echo "Kota belum dipilih...";
	}
	else
	{
		if($kota=="Bandung")
		{
			echo "<tr><td>Pilih Kelurahan&nbsp;";
			echo "<select name='kelurahan' id='kelurahan'>";
			/*
			$arConfig=$User->GetAreaConfig($area);
			$Bandung=$arConfig["Bandung"];
			$KelurahanBdg=split(",",$Bandung);
			for($i=0;$i<=count($KelurahanBdg)-1;$i++){
				
				echo "<option value='".print_r($KelurahanBdg[$i])."'>".print_r($KelurahanBdg[$i])."</option>";
				
			}
			*/
			echo "<option>Ciwidey</option>";
			echo "<option>Cibiru</option>";
			echo "</select>";
			echo "</tr></td>";
		}
		if($kota=="Subang")
		{
			echo "<tr><td>Pilih Kelurahan&nbsp;";
			echo "<select name='kelurahan' id='kelurahan'>";
			$arConfig=$User->GetAreaConfig($area);
			$Subang=$arConfig["$kota"];
			$KelurahanSbg=split(",",$Subang);
			for($i=0;$i<=count($KelurahanSbg)-1;$i++){
				
				echo "<option value='".print_r($KelurahanSbg[$i])."'>".print_r($KelurahanSbg[$i])."</option>";
				
			}
			//echo "<option>Karanganyar</option>";
			//echo "<option>Cigadung</option>";
			echo "</select>";
			echo "</tr></td>";
		}
	}

}
?>