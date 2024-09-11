<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

function deleteNotaris($id, &$err) {
    global $DBLink;
    $query = "DELETE FROM cppmod_ssb_doc WHERE CPM_SSB_ID ='" . mysqli_real_escape_string($DBLink, $id) . "'";
    $res = mysqli_query($DBLink, $query);

    if ($res === false) {
        $err = mysqli_error($DBLink);
        return false;
    }
    return true;
}

$arrResult = array();
$arrResult ["success"] = true;
$arrResult ["message"] = "Penghapusan telah dilakukan";

$ids = @isset($_REQUEST["ids"]) ? $json->decode($_REQUEST["ids"]) : "";
$usr = @isset($_REQUEST["usr"]) ? base64_decode($_REQUEST["usr"]) : "";

if ($ids != "") {
    $couIds = count($ids);
    for ($i = 0; $i < $couIds; $i++) {
        
        $get = "select CPM_OP_NOMOR,CPM_WP_NAMA,CPM_SSB_AUTHOR
                from cppmod_ssb_doc 
                where CPM_SSB_ID ='" . mysqli_real_escape_string($DBLink, $ids[$i]) . "'";
        $getData = mysqli_query($DBLink, $get);
        while($setData = mysqli_fetch_array($getData)){
            $cpm_op_nomor = $setData['CPM_OP_NOMOR'];
            $cpm_wp_nama = $setData['CPM_WP_NAMA'];
            $cpm_ssb_author = $setData['CPM_SSB_AUTHOR'];
        }
        
        if (!deleteNotaris($ids[$i], $err)) {
            $arrResult ["success"] = false;
            $arrResult ["message"] = $err;
            $sResponse = $json->encode($arrResult);
            echo $sResponse;
            exit();
        } else {
            
            $log_delete = "insert into cppmod_ssb_log(
                                    CPM_SSB_ID,
                                    CPM_SSB_LOG_ACTOR,
                                    CPM_SSB_LOG_ACTION,
                                    CPM_OP_NOMOR,
                                    CPM_WP_NAMA,
                                    CPM_SSB_AUTHOR)
                            values ('" . mysqli_real_escape_string($DBLink, $ids[$i]) . "',
                                    '" . mysqli_real_escape_string($DBLink, $usr) . "',                                   
                                    '" . mysqli_real_escape_string($DBLink, 9) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_op_nomor) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_wp_nama) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_ssb_author) . "')";
           mysqli_query($DBLink, $log_delete);
        }
    }
}


$sResponse = $json->encode($arrResult);
echo $sResponse;
?>
