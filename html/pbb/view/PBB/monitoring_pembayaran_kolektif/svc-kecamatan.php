<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_pembayaran_kolektif', '', dirname(__FILE__))) . '/';
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

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$p = @isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$kel = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : 0;
$result = array();
$result['result']="failure";

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// if(stillInSession($DBLink,$json,$sdata)){
	if ($p) {
		if ($kel==0) {
			$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."' order by CPC_TKC_URUTAN ASC";
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
			}
		} else {
			$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID ='".$p."'  order by CPC_TKL_URUTAN ASC";
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
			}
		}
		$result['result'] = "success";
		$result['msg'] = $data; 
                $result['query'] = $query; 
		echo  $json->encode($result);
	}
// }else{
// 	$result['result']="failure";
// 	$result['msg'] = "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
// 	echo  $json->encode($result);
// }
?>
