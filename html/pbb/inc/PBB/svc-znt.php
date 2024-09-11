<?php
define('DEBUG', true);
define('LOG_DMS_FILENAME', true);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/payment/json.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);

$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);
function getConfigValue($key)
{
    global $DBLink, $appID;
    $qry = "select * from central_app_config where CTR_AC_AID = 'aPBB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
    //mysqli_close($DBLink);
}
//$url = 'http://10.26.26.24/vmap/svc/getZNT.php';
$nop = $prm->nop;
$thn = getConfigValue('tahun_tagihan');

$sql = "SELECT CPM_KODE_ZNT, IFNULL(CPM_NIR2,CPM_NIR) AS CPM_NIR FROM (
        SELECT A.CPM_KODE_ZNT,(A.CPM_NIR * 1000) as CPM_NIR, (B.CPM_NJOP_M2 * 1000) as CPM_NIR2 FROM cppmod_pbb_znt A
        LEFT JOIN cppmod_pbb_kelas_bumi B 
        ON rpad(B.CPM_KELAS,3,' ')= rpad(A.CPM_KODE_ZNT,3,' ')
        WHERE A.CPM_KODE_LOKASI='$nop' AND A.CPM_TAHUN='$thn'
        ) TBL";

$dbSpec->sqlQueryRow($sql, $data);
$str = "";
foreach ($data as $d) {
    $d['CPM_NIR'] = number_format($d['CPM_NIR'], 0, ",", ".");
    $str .= "<option value='" . $d['CPM_KODE_ZNT'] . " - " . $d['CPM_NIR'] . "'>" . $d['CPM_KODE_ZNT'] . " - " . $d['CPM_NIR'] . "</option>";
}

echo $json->encode(array('str' => $str));
