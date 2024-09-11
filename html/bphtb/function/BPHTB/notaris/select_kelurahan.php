<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");


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

$value = $_POST['value_'];
$sts = $_POST['sts_'];

$query = "SELECT cppmod_tax_kelurahan2.* FROM cppmod_tax_kelurahan2
                JOIN cppmod_tax_kecamatan2 ON cppmod_tax_kecamatan2.CPC_TKC_ID = cppmod_tax_kelurahan2.CPC_TKL_KCID
                WHERE cppmod_tax_kecamatan2.CPC_TKC_KECAMATAN ='{$value}'";
$res = mysqli_query($DBLink, $query);

$return .="<option disabled>-- Pilih Kelurahan --</option>";
while ($row = mysqli_fetch_assoc($res)) {
    $selected = '';

    $value = strtoupper(str_replace(" ","",$value));
    $kelrhn = strtoupper(str_replace(" ","",$row['CPC_TKL_KELURAHAN']));
    $return .="<option value=\"{$row['CPC_TKL_KELURAHAN']}\" {$selected}>{$row['CPC_TKL_KELURAHAN']}</option>";
}
$data = $return;
// jQuery wants JSON data
echo $json->encode($data);

?>
