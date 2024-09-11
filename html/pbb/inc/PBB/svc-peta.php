<?

define("SPOP_DBHOST", "192.168.168.192");   // database host
define("SPOP_DBNAME", "VSI_SWITCHER_DEVEL");   // database name
define("SPOP_DBUSER", "sw_user_devel"); // database username to connect to database
define("SPOP_DBPWD", "sw_pwd_devel");   // database password to connect to database for supplied username
define("LOG_DMS_FILENAME", "/tmp/" . strftime("%Y%m%d", time()) . "-DEVEL-peta.log");

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");

require_once($sRootPath . "inc/PBB/dbFinalSppt.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, SPOP_DBHOST, SPOP_DBUSER, SPOP_DBPWD, SPOP_DBNAME, true);
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

error_reporting(E_ALL);
ini_set("display_errors", 1);

$nop = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$sQ = "SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'";
$bOK = false;

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . basename(__FILE__) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, LOG_DMS_FILENAME);
if ($result = mysqli_query($DBLink, $sQ)) {
    $nRes = mysqli_num_rows($result);
    if ($nRes > 0) {
        $bOK = true;
        while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
            $res[] = $row;
        }
    }
} else {
    echo mysqli_error($result);
}

if ($bOK) {
//    echo "<pre>";
//    print_r($res);
//    echo "</pre>";

    $nama = $res[0]['CPM_WP_NAMA'];
    $alamat = $res[0]['CPM_OP_ALAMAT'] . " " . $res[0]['CPM_OP_NOMOR'];
    $njop_tanah = $res[0]['CPM_NJOP_TANAH'];
    $foto = $res[0]['CPM_OP_FOTO'];
    $sket = $res[0]['CPM_OP_SKET'];
    echo "{\"NAMA\":\"$nama\",\"ALAMAT\":\"$alamat\",\"NJOP_TANAH\":\"$njop_tanah\",\"FOTO\":\"$foto\",\"SKET\":\"$sket\"}";
}
?>