<?php
session_start();
$DIR = "PATDA-V1";
$modul = "rekap";

if (isset($_POST)) {
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
    require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
    require_once($sRootPath . "inc/payment/db-payment.php");
    require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
    require_once($sRootPath . "function/{$DIR}/class-pajak.php");
    require_once($sRootPath . "function/{$DIR}/{$modul}/class-rekap.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    $pajak = new RekapPajak();
    // echo "ok";
    // var_dump($pajak->arr_pajak[$_POST['simpatda_jenis_pajak']]);
    // die(var_dump($pajak->arr_pajak));
    // die();
    $pajak->download_excel_rekap_new();
}
?>
