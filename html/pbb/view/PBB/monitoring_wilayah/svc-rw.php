<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
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
require_once($sRootPath . "inc/PBB/dbMonitoring.php");

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
// print_r($_REQUEST);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$p 			= @isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$a 			= @isset($_REQUEST['appID']) ? $_REQUEST['appID'] : 0;
$sts		= @isset($_REQUEST['sts']) ? $_REQUEST['sts'] : "";

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);

// $conn = mysql_connect($host,$user,$pass);

$result 		  = array();
$result['result'] = "failure";

$arrWhere 		  = array();
if ($sts!="") {
    if($sts==1) array_push($arrWhere,"PAYMENT_FLAG = 1");            
    else if($sts==2) array_push($arrWhere,"(PAYMENT_FLAG != 1 OR PAYMENT_FLAG IS NULL)");
}
$where = implode (" AND ",$arrWhere);
$where = " AND ".$where;

if(stillInSession($DBLink,$json,$sdata)){
	if(getConnection()){
		$query = "SELECT IF(OP_RW='' OR OP_RW IS NULL,'000',OP_RW) AS OP_RW FROM pbb_sppt WHERE OP_KELURAHAN_KODE = '".$p."' ".$where." GROUP BY OP_RW order by OP_RW ASC";
		// echo $query;
		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			$result['msg'] = mysqli_error($DBLink);
			echo $json->encode($result);
			exit();
		}
		$data = array();
		$i=0;
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$i]["RW"] = $row["OP_RW"];
			$i++;
		}
		// mysqli_close(getConnection());
		$result['result'] = "success";
		$result['msg'] = $data; 
		$result['query'] = $query; 
		echo  $json->encode($result);
	}
}else{
	$result['result']="failure";
	$result['msg'] = "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
	echo  $json->encode($result);
}

function getConnection() {
	global $appConfig;
	
	$host 	= $appConfig['ADMIN_DBHOST'];
	$port 	= $appConfig['ADMIN_DBPORT'];
	$user 	= $appConfig['ADMIN_DBUSER'];
	$pass 	= $appConfig['ADMIN_DBPWD'];
	$dbname = $appConfig['ADMIN_GW_DBNAME'];
	$conn = false;
	if($host != null && $port != null && $user != null && $pass != null && $dbname != null){
		$conn = mysqli_connect($host, $user, $pass, $dbname,$port);
	}

	if ($conn) {
		return true;
		//if (mysql_select_db($dbname, $conn)) return true;
	}
	return false;
}
