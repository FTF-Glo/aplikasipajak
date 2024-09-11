<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'perubahan_znt_massal', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/central/user-central.php");

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



$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);
$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab    = $q->tab;
$uname  = $q->u;
$uid    = $q->uid;




$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig   = $User->GetModuleConfig($m);
$appConfig  = $User->GetAppConfig($a);
$thn_tagihan = $appConfig['tahun_tagihan'];
// echo $a;

/* inisiasi parameter */
$kc 	= @isset($_REQUEST['kecamatanOP_multi']) ? $_REQUEST['kelurahanOP_multi'] : "";
$kl 	= @isset($_REQUEST['kelurahanOP_multi']) ? $_REQUEST['kelurahanOP_multi'] : "";
$znt 	= @isset($_REQUEST['kd_znt_baru']) ? $_REQUEST['kd_znt_baru'] : "";


$query = "SELECT CPM_NIR FROM cppmod_pbb_znt WHERE CPM_KODE_ZNT = '$znt' and CPM_KODE_LOKASI = '$kl'  and CPM_TAHUN = '$thn_tagihan' LIMIT 1 ";
$res = mysqli_query($DBLink, $query);
if (mysqli_num_rows($res)>0){
	$data = mysqli_fetch_assoc($res);
	echo json_encode(array("NIR"=>$data['CPM_NIR']));
}else{
	echo json_encode(array("NIR"=>"0","query"=>$query));
}

?>