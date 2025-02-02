<?php
session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "/tran-hotel";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul . $submodul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-tran-hotel.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$monitoring = new TransaksiHotel();
// Add : parameter dateStart, dateEnd, noTran on getResume() function [OST - 14/05/2018] 
$funct = $_REQUEST['function'];
$monitoring->$funct($_REQUEST['dateStart'],$_REQUEST['dateEnd'],$_REQUEST['noTran']);

?>
