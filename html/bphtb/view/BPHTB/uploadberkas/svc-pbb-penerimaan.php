<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

if (!empty($_POST['arrSvcId'])) {
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    $arrSvcId = explode(",", $_POST['arrSvcId']);

    if ($_POST['task'] == 'delete') {
        for ($index = 0; $index < count($arrSvcId); $index++) {
            $sql1 = "DELETE FROM cppmod_ssb_berkas WHERE CPM_BERKAS_ID='" . $arrSvcId[$index] . "'";

            $result1 = mysqli_query($sql1);

            if (!$result1) {
                echo mysqli_error();
                exit(1);
            }
        }
    } else {
        echo "No Action!";
    }
} else {
    echo "No Action!";
}
?>