<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' , '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
// require_once($sRootPath . "inc/PBB/dbMonitoring.php");

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

// var_dump($_REQUEST);
$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);


$params     = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p 		    = base64_decode($params);

$json 	= new Services_JSON();
$prm 	= $json->decode($p);

$kelurahan  = $prm->kelurahan;
$blok       = $prm->blok;
$no_urut1   = $prm->no_urut1;
$no_urut2   = $prm->no_urut2;
$susulan    = $prm->susulan;


$result = array();
$result['result']="failure";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$nop1 = $kelurahan.$blok.$no_urut1;
$nop2 = $kelurahan.$blok.$no_urut2;
$table = ($susulan == 1)? "cppmod_pbb_sppt_susulan":"cppmod_pbb_sppt_final";
$query = "SELECT * FROM {$table} WHERE CPM_NOP LIKE '$nop1%'";
// echo $query;exit;
$res = mysqli_query($DBLink, $query);
// var_dump($res);exit;
if ($res === false) {
        $result['result']=mysqli_error($DBLink);
        $result['msg'] = "NOP Tidak Ditemukan!!";
        echo $json->encode($result);
        exit();
}

$data = array();
$i=0;
$nop = "";
while ($row = mysqli_fetch_assoc($res)) {
    // var_dump($row);exit;
    $nop .= $row['CPM_NOP'].",";
}

if ($nop != "") {
    $result['result'] = "success";
    $result['msg'] = substr($nop, 0, -1);    
}else{
    $result['result'] = "failure";
    $result['msg'] = "NOP Tidak Ditemukan!!";
}
 
        // $result['query'] = $query; 
echo  $json->encode($result);

// var_dump(substr($nop, 0, -1));exit;
?>
