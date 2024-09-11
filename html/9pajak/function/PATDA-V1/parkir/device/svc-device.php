<?php
$DIR = "PATDA-V1";
$modul = "parkir";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul.'/device', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/op/class-op.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$q = $json->decode(base64_decode($_REQUEST['param']));

$_REQUEST['a'] = isset($q->a)? $q->a : "";
$_REQUEST['m'] = isset($q->m)? $q->m : "";
$_REQUEST['f'] = isset($q->f)? $q->f : "";

$profil = new ObjekPajak();

if(isset($_REQUEST['function'])){
    $profil->$_REQUEST['function']();
}

if(!in_array($_REQUEST['function'], array('check_nop','delete'))) $profil->redirect(isset($_POST['url'])?$_POST['url']:'');


?>
