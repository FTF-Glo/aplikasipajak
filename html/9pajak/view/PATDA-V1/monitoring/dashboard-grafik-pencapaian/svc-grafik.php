<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul . DIRECTORY_SEPARATOR .'dashboard-grafik-pencapaian', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");
require_once($sRootPath . "function/{$DIR}/{$modul}/class-grafik-pencapaian.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
// $q = $json->decode(base64_decode($_REQUEST['q']));
$pajak = new Pajak();
$grafikPajak = new GrafikPencapaian();

if(isset($_GET['get_target_pajak']) && !empty($_GET['get_target_pajak'])) {
	echo $grafikPajak->get_target_pajak(false, $_GET['tahun']);
}

if(isset($_GET['get_target_pajak_tunggakan']) && !empty($_GET['get_target_pajak_tunggakan'])) {
	echo $grafikPajak->get_target_pajak_tunggakan(false, $_GET['tahun']);
}

if(isset($_GET['get_target_pajak_perbandingan']) && !empty($_GET['get_target_pajak_perbandingan'])) {
	echo $grafikPajak->get_target_pajak_perbandingan(false, $_GET['tahun']);
}

if(isset($_POST['download-excel']) && !empty($_POST['download-excel'])) {
	$grafikPajak->download_excel();
}
?>