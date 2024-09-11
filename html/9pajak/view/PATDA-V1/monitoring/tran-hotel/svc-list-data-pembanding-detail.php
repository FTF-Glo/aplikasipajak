<?php
session_start();
$DIR = "PATDA-V1";
$modul = 'monitoring/tran-hotel';

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/monitoring/class-tran-hotel.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$lapor = new TransaksiHotel();

if($_GET["action"] == "list"){
	$lapor->grid_data_pembanding_detail();
}else if($_GET["action"] == "delete"){
	$lapor->deleteRecord($_POST["tran_code"]);
}else if($_GET["action"] == "update"){
	$lapor->updateRecord($_POST["tran_code"],str_replace(",","",$_POST["tran_amount"]));
}else if($_GET["action"] == "insert"){
	$tran_date = str_replace(array("-"," ",":"),"",$_POST["tran_date"]);
	$tran_amount = str_replace(array(",","."),"",$_POST["tran_amount"]);
	//echo $_POST["npwpd"]."-".$_POST["bill_number"]."-".$_POST["tran_desc"]."-".$_POST["tran_code"]."-".$tran_date."-".$tran_amount;
	$lapor->insertRecord($_POST["npwpd"],$_POST["bill_number"],$_POST["tran_desc"],$_POST["tran_code"],$tran_date,$tran_amount);	
}
?>
