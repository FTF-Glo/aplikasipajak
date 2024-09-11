<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$DIR = "PATDA-V1";
$modul = "jalan";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul . '/lapor', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-message.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/lapor/class-lapor.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/op/class-op.php");

require_once("{$sRootPath}phpqrcode/src/Milon/Barcode/DNS2D.php");
require_once("{$sRootPath}phpqrcode/src/Milon/Barcode/QRcode.php");

use \Milon\Barcode\DNS2D;

$qrisLib = new DNS2D();
$qrisLib->setStorPath(__DIR__ . '/cache/');

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
if (isset($_GET['param'])) {
    $q = $json->decode(base64_decode($_GET['param']));

    $_REQUEST['a'] = $q->a;
    $_REQUEST['m'] = $q->m;
    $_REQUEST['mod'] = $q->mod;
    $_REQUEST['f'] = $q->f;
}
$lapor = new LaporPajak();
if (isset($_POST['function'])) {
    $funct = $_POST['function'];
    if ($_POST['function'] == "print_skpd") $lapor->$funct($lapor->id_pajak);
    else $lapor->$funct();
}
if (isset($_GET['param'])) {
    if (!in_array($_POST['function'], array("print_sptpd", "print_sspd", "print_skpd", "print_notaHitung"))) {
        $lapor->redirect();
    }
}
