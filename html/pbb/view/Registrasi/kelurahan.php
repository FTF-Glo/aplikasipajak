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
$tampil="";

error_reporting(E_ALL);
ini_set("display_errors", 1); 
print_r($_REQUEST);
if(@isset($_REQUEST['data_type'])){
	$data_type = $_REQUEST['data_type'];
	switch($data_type){
			case "kelurahan": 
				
						if($data_type=="Bandung")
						{
							//$arConfig=$User->GetAreaConfig($_REQUEST['area']);
							//$Bandung=$arConfig["Bandung"];
							//$Bdg = split(",",$Bandung);
							/*$Bdg[1]=substr($Bandung,0,7);
							$Bdg[2]=substr($Bandung,9,6);
							$loop=0;
							while($loop<=2){
							$loop++;
							*/
							//$tampil= "<option value=".$Bdg.">".$Bdg."</option>"
						
							//}
							
						}
						else if($data_type=="Subang")
						{
						//	$arConfig=$User->GetAreaConfig($_REQUEST['area']);
							/*
							$Subang=$arConfig["Subang"];
							$Sbg[1]=substr($Subang,0,11);
							$Sbg[2]=substr($Subang,12,8);
							$loop=0;
							while($loop<=2){
							$loop++;
							
							$tampil.= "<option value=".$Sbg[$loop].">".$Sbg[$loop]."</option>";
							}
							*/
						}
						
			break;
			default :
			
						$arConfig=$User->GetAreaConfig($_REQUEST['area']);
						print_r($arConfig);
						$data_type=$arConfig["$data_type"];
						/*
						$Kot=split(",",$data_type);
						list($Kot1,$Kot2,$Kot3,$Kot4)=$Kot=split(",",$data_type);
						$Kota1=$Kot1;
						$Kota2=$Kot2;
						$Kota3=$Kot3;
						$Kota4=$Kot4;
						$Kota=$Kota1."".$Kota2."".$Kota3."".$Kota4;
						echo $Kota;
						
						$Kot[1]=substr($data_type,0,7);
						$Kot[2]=substr($data_type,8,6);
						$Kot[3]=substr($data_type,15,5);
						$Kot[4]=substr($data_type,21,6);
						$loop=0;
						while($loop<=3){
						$loop++;
						*/
						//print_r($Kot);
						//echo"<select>";
						//echo "<option value='".$Kota1."'>".$Kota1."</option>";
						//echo "	<option value='".$Kot2."'>".$Kot2."</option>";
						//echo "	<option value='".$Kot3."'>".$Kot3."</option>";
						//echo "	<option value='".$Kot4."'>".$Kot4."</option>";
						//echo"</select>";
						//}
	}

	//$response =split(",",$data_type); // siapkan respon yang nanti akan di convert menjadi JSON
	//die(json_encode($response)); // convert variable respon menjadi JSON, lalu tampilkan 

//echo $tampil;
}

?>