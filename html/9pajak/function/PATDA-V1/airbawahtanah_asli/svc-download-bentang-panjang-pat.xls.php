<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set("log_errors", 1);
session_start();
if (isset($_POST)) {
    require_once("../../../inc/payment/inc-payment-db-c.php");
    require_once("../../../inc/payment/db-payment.php");
    require_once("../../../inc/phpexcel/Classes/PHPExcel.php");
    require_once("../class-pajak.php");
    require_once("cetak-excel.php");


    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    $pajak = new Cetak();
    //$pajak->download_bentang_panjang_abt();
    $pajak->download_bentang_panjang_abt1();
}
