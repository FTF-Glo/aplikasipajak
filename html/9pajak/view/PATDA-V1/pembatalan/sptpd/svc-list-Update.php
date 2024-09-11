<?php

session_start();
$DIR = "PATDA-V1";
$modul = "pembatalan";
$submodul = "/sptpd";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul .$submodul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-message.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-admin.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
//SCANPayment_ConnectToDB($DBLink, $DBConn, GATWAY_DBHOST, GATWAY_DBUSER, GATWAY_DBPWD, GATWAY_DBNAME);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$message = new Message();
$message->show();
$monitoring = new MonitoringPajak();
$tmp = 0;
if (isset($_POST['patdaId'])) {
    $tmp = $monitoring->grid_data_delete($_POST['patdaId'],$_POST['Itab'], $_POST['a'], $_POST['ket']);
    $monitoring->grid_data_update($_POST['patdaId'],$_POST['Itab'],$tmp);
    
}
?>

