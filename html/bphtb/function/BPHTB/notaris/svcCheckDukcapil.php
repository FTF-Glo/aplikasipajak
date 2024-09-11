<?php
/* 
Nama File 							: -
Deskripsi File 						: -
Nama Developer (email) 				: Jajang Apriansyah (jajang@vsi.co.id)
Tanggal Development					: 06/15/2015
Tanggal Revisi (list) + Perubahan	: -
*/

// date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$a			= $_REQUEST['appID'];
$noKTP		= $_REQUEST['noKTP'];
// $noKTP		= "3203285310810001";

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink 			= $User->GetDbConnectionFromApp($a);
$appConfig 			= $User->GetAppConfig($a);
$dbSpec 			= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);
$json 				= new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$listIP 		= getListIP();
$count			= count($listIP);

$i		= 0;
$data 	= "";
do {
	$resDukcapil = getDataDukcapil($resData,$listIP[$i]['IP_SVC_DUKCAPIL']);
	$i++;
} while((!$resData) && ($i<=$count));

if($resDukcapil){
	if($resData['SUCCESS']==1){
		$data 	= $resData['DATA']; 
		$res 	= 1;
		$dat 	= $data;
		$msg 	= "Pengambilan data berhasil, apakah mau diterapkan kedalam form?";
		postRespon($res,$dat,$msg);
	} else {
		$res 	= 0;
		$dat 	= "NULL";
		$msg 	= $resData['MESSAGE'];
		postRespon($res,$dat,$msg);
	}
} else {
	$res = 0;
	$dat = "NULL";
	$msg = "Gagal koneksi ke Dukcapil";
	postRespon($res,$dat,$msg);
}

function getListIP(){
	global $DBLink;
	
	$query = "SELECT * FROM daftar_ip_svc_dukcapil ORDER BY ID_SVC_DUKCAPIL";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		mysqli_error($DBLink);
		exit();
	}

	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res)){
		$data[$i]["ID_SVC_DUKCAPIL"] 	= $row["ID_SVC_DUKCAPIL"];
		$data[$i]["IP_SVC_DUKCAPIL"] 	= $row["IP_SVC_DUKCAPIL"];
		$i++;
	}
	
	return $data;
}


function postRespon($res,$dat,$msg){
	global $json;
	
	$response 		 = array();
	$response['res'] = $res;
	$response['dat'] = $dat;
	$response['msg'] = $msg;
	$val = $json->encode($response);
	echo $val; exit;
}

// function checkConnection($url){
	// global $noKTP, $json;
	// $ch = curl_init();
	// curl_setopt_array($ch, array(
		// CURLOPT_RETURNTRANSFER 	=> true,
		// CURLOPT_URL 			=> $url.'?nik='.$noKTP,
		// CURLOPT_TIMEOUT 		=> 2
	// ));
	// $response = curl_exec($ch);
	// curl_close($ch);
	// $resData = json_decode($response, true);
	// print_r($resData); exit;
	// if($resData['SUCCESS']==0) {
		// return true;
	// } else {
		// return false;
	// }
// }

function getDataDukcapil(&$resData, $url){
	global $noKTP, $json;
	$data = array('nik'=>$noKTP);
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER 	=> true,
		// CURLOPT_URL 			=> $url.'?nik='.$noKTP,
		CURLOPT_URL 			=> $url,
		CURLOPT_POST 			=> true,
		CURLOPT_POSTFIELDS 		=> $data,
		CURLOPT_TIMEOUT 		=> 10
	));
	$response = curl_exec($ch);
	curl_close($ch);
	$resData = json_decode($response, true);
	// print_r($resData); exit;
	if(!empty($resData)) {
		return true;
	} else {
		return false;
	}
}

function generateError($errorString=''){
	global $json;
	
	$response['r'] = false;
	$response['errstr'] = $errorString;
	
	$val = $json->encode($response);
	echo $val;
	exit;
}
?>