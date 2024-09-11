<?php

class SvcWP {

    private $dbSpec = null;

    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }

    //DATABASE FUNCTION
    public function getWP($noktp) {
        $ktp = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($noktp));

        $query = "SELECT * FROM cppmod_pbb_wajib_pajak WHERE CPM_WP_ID='$ktp'";
        
        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }
}

require_once("../payment/db-payment.php");
require_once("../payment/inc-payment-db-c.php");
require_once("../central/dbspec-central.php");
require_once("../payment/json.php");

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
$svcWP = new SvcWP($dbSpec);

//variable for input program: NOP dan ZNT
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$noktp = $prm->noktp;

$wp = $svcWP->getWP($noktp);
if(/*is_numeric($wp) && */count($wp) > 0) {
    $response = array();
    $response['r'] = true;
    $response['CPM_WP_ID'] = $wp[0]['CPM_WP_ID'];
    $response['CPM_WP_STATUS'] = $wp[0]['CPM_WP_STATUS'];
    $response['CPM_WP_PEKERJAAN'] = $wp[0]['CPM_WP_PEKERJAAN'];
    $response['CPM_WP_NAMA'] = $wp[0]['CPM_WP_NAMA'];
    $response['CPM_WP_ALAMAT'] = $wp[0]['CPM_WP_ALAMAT'];
    $response['CPM_WP_KELURAHAN'] = $wp[0]['CPM_WP_KELURAHAN'];
    $response['CPM_WP_RT'] = $wp[0]['CPM_WP_RT'];
    $response['CPM_WP_RW'] = $wp[0]['CPM_WP_RW'];
    $response['CPM_WP_PROPINSI'] = $wp[0]['CPM_WP_PROPINSI'];
    $response['CPM_WP_KOTAKAB'] = $wp[0]['CPM_WP_KOTAKAB'];
    $response['CPM_WP_KECAMATAN'] = $wp[0]['CPM_WP_KECAMATAN'];
    $response['CPM_WP_KODEPOS'] = $wp[0]['CPM_WP_KODEPOS'];
    $response['CPM_WP_NO_HP'] = $wp[0]['CPM_WP_NO_HP'];
}
else {
    $response = array();
    $response['r'] = false;
}


$val = $json->encode($response);
echo $val;
