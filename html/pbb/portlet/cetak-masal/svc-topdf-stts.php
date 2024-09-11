<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'portlet'.DIRECTORY_SEPARATOR.'cetak-masal', '', dirname(__FILE__))).'/';

date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath."inc/payment/ctools.php"); 
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/error-messages.php"); 

require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/nid.php");
require_once($sRootPath."inc/payment/uuid.php");
// var_dump(ONPAYS_DBNAME);
// exit;
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[". strftime("%Y%m%d%H%M%S", time()) ."][". (basename(__FILE__)) .":". __LINE__ ."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/* inisiasi parameter */
if(isset($_REQUEST['q'])){
	$uid = c_uuid();
	$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] :"";
	$q = base64_decode($q);
	$q = $json->decode($q);
	$q->kd_kel 	= "123";
	$q->blok 	= "123";
	$q->blok2 	= "123";
	$q->group_id = $_REQUEST['group_id'];
	$q->tahun 	= $_REQUEST['thn'];
	$a = "aPBB";
	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	// $arConfig 	= $User->GetModuleConfig($m);
	$appConfig 	= $User->GetAppConfig($a);
	
	// print_r($appConfig);
	// exit;
	$C_HOST_PORT 	= $appConfig['GW_DBHOST'];
	$C_USER 		= $appConfig['GW_DBUSER'];
	$C_PWD 			= $appConfig['GW_DBPWD'];
	$C_DB 			= $appConfig['GW_DBNAME'];
	$SW_DB = $appConfig['ADMIN_SW_DBNAME'];

	SCANPayment_ConnectToDB($DBLinkGW, $DBConnGW, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB);
	
	$where = "";
	$query = "SELECT COUNT(*) AS TOTAL FROM {$C_DB}.PBB_SPPT {$where}";
	// echo $query;
	// exit;
	$res = mysqli_query($DBLinkGW, $query) or die(mysqli_error($DBLink));
	$data = mysqli_fetch_array($res);
	// echo($query);exit;
	if($data['TOTAL'] == 0) exit('NOP tidak ditemukan.');
	
	/*insert to table download*/
	$param['CPM_ID'] = "'{$uid}'";
	$param['CPM_GROUP_ID'] = "'{$q->group_id}'";
	$param['CPM_TAHUN'] = date("Y");
	// $param['CPM_BUKU'] = "'{$q->buku}'";
	$param['CPM_SIZE'] = "'-'";
	$param['CPM_JUMLAH_NOP'] = "'-'";
	$param['CPM_STATUS'] = "'0'";
	$param['CPM_DATETIME'] = 'NOW()';
	
	$fields = implode(',',array_keys($param));
	$values = implode(',',array_values($param)); 
	$query = "INSERT INTO {$SW_DB}.cppmod_pbb_stts_download_collective ({$fields}) VALUES ({$values})";	
	mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
	/*end insert*/
	
	// $url = "http://localhost//api-topdf-stts.php";
	// $url = "http://pajakonline.pandeglangkab.go.id/portlet/cetak-masal/api-topdf-stts.php";
	// $url = "http://localhost/portlet/cetak-masal/api-topdf-stts.php";
	// $url = "";
	// var_dump($url);
	// exit;
	$url = $appConfig['URL_DOWNLOAD_COLLECTIVE'];
	// http://192.168.26.112/pbb/sukabumi_briva/portlet/portlet.php#stts
	// var_dump($url);
	// $url = $appConfig['URL_SVC_CETAK_MASAL_STTS'];
	$vars = (array) $q;
	// var_dump($vars);
	// exit;
	
	$postData = http_build_query($vars);
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_POST, 1);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt( $ch, CURLOPT_HEADER, 0);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	
	$response = curl_exec($ch);
	// echo json_encode($response);
	// echo "mantap";
	//echo $json->encode(array('msg'=>$response));
	/* end kirim*/
}
?>
