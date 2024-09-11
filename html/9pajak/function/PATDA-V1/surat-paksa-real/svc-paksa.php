<?php

session_start();
$DIR = "PATDA-V1";
$modul = "surat-paksa-real";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-message.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-paksa.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
if (isset($_REQUEST['param'])) {
    $q = $json->decode(base64_decode($_REQUEST['param']));

    $_REQUEST['a'] = $q->a;
    $_REQUEST['m'] = $q->m;
    $_REQUEST['mod'] = $q->mod;
    $_REQUEST['f'] = $q->f;
}
$lapor = new Paksa();
if (isset($_POST['function'])) {
    $lapor->$_POST['function']();
}
if (isset($_REQUEST['param'])) {
    if (!in_array(array("print_teguran"), $_POST['function'])) {
        $lapor->redirect();
    }
}
?>
