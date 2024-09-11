<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
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
require_once("config-monitoring.php");

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

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$p = @isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$kel = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : 0;
$result = array();
$result['result']="failure";

error_reporting(E_ALL);
ini_set('display_errors', 1);

function openPostgres () {
	$host = DBHOST;
	$port = DBPORT;
	$dbname = DBNAME;
	$user = DBUSER;
	$pass = DBPWD;
	
	if ($pgDBlink = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}")) {
		echo pg_last_error($pgDBlink); 
		//exit();
	}
	return $pgDBlink;
}

function closePostgres($con){
	pg_close($con);
}

if(stillInSession($DBLink,$json,$sdata)){
	if ($p) {
		if ($kel==0) {
			/*$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."'";
			$res = mysqli_query($DBLink, $query);
			if ($res === false) {
				 $result['msg'] = mysqli_error($DBLink);
				 echo $json->encode($result);
				 exit();
			}
			$data = array();
			$i=0;
			while ($row = mysqli_fetch_assoc($res)) {
				$data[$i]["id"] = $row["CPC_TKC_ID"];
				$data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
				$i++;
			}*/
			$data = array();
			$pgDBlink = openPostgres();

			$dbresult = pg_query($pgDBlink,"SELECT id_kecamatan,kode_kecamatan, nama_kecamatan FROM ".DBTABLEKECAMATAN." WHERE id_kota='97' ORDER BY nama_kecamatan");
			
			if ($dbresult ===false) {
				echo pg_result_error($dbresult );
			 // exit;
			}
			$i=0;
			while ($row = pg_fetch_assoc($dbresult )) {
				$data[$i]["id"] = $row["kode_kecamatan"];
				$data[$i]["name"] = $row["nama_kecamatan"];
				$i++;
			}
			closePostgres($pgDBlink);
		} else {
			/*$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID ='".$p."'";
			$res = mysqli_query($DBLink, $query);
			if ($res === false) {
				 $result['msg'] = mysqli_error($DBLink);
				 echo $json->encode($result);
				 exit();
			}
			$data = array();
			$i=0;
			while ($row = mysqli_fetch_assoc($res)) {
				$data[$i]["id"] = $row["CPC_TKL_ID"];
				$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
				$i++;
			}*/
			$data = array();
			$pgDBlink = openPostgres();

			$dbresult = pg_query($pgDBlink,"SELECT id_kelurahan,kode_kelurahan, nama_kelurahan FROM ".DBTABLEKELURAHAN." WHERE kode_kelurahan like '{$p}%' ORDER BY nama_kelurahan");
			
			if ($dbresult ===false) {
				echo pg_result_error($dbresult );
			 // exit;
			}
			$i=0;
			while ($row = pg_fetch_assoc($dbresult )) {
				$data[$i]["id"] = $row["kode_kelurahan"];
				$data[$i]["name"] = $row["nama_kelurahan"];
				$i++;
			}
			closePostgres($pgDBlink);
	
		}
		$result['result'] = "success";
		$result['msg'] = $data;
		echo  $json->encode($result);
	}
}else{
	$result['result']="failure";
	$result['msg'] = "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
	echo  $json->encode($result);
}
?>
