<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'updatePBB', '', dirname(__FILE__))).'/';
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



/* inisiasi parameter */
$a = $_POST['appID'];
$m = $_POST['modID'];
$u = $_POST['uID'];

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);
// print_r($_REQUEST); exit;

/*proses simpan / delete pembentukkan */
if(isset($_POST['action'])){
	
	$response['msg'] = 'Proses data berhasil.';
	
	$nop = $_POST['nop'];
	$thn = $_POST['thn'];
	$tgl_jatuh_tempo = $_POST['tgl_jatuh_tempo'];

	if($_POST['action'] == 'btn-save'){
		
		$GW_DBHOST = $appConfig['GW_DBHOST'];
		$GW_DBUSER = $appConfig['GW_DBUSER'];
		$GW_DBPWD = $appConfig['GW_DBPWD'];
		$GW_DBNAME = $appConfig['GW_DBNAME'];
		$GWDBLink = mysqli_connect($GW_DBHOST,$GW_DBUSER,$GW_DBPWD,$GW_DBNAME) or die(mysqli_error($DBLink));
		//mysql_select_db($GW_DBNAME,$GWDBLink);
		
		$where = " NOP = '{$nop}' ";
		
		#update data pbb sppt (GW)
		$param = array(
			"SPPT_TANGGAL_JATUH_TEMPO = '{$tgl_jatuh_tempo}'"
		);
		
		$sets = implode(',',$param); 
		$query = "update PBB_SPPT set {$sets} where SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL)";
		// echo $query;exit;
		$res = mysqli_query($GWDBLink, $query);
		
		if($res){
			$query = "select count(*) as TOTAL from PBB_SPPT where SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL)";
			$res = mysqli_query($GWDBLink, $query);
			$data = mysqli_fetch_assoc($res);
			// $response['msg'] = 'Data berhasil diproses sejumlah : '.$data['TOTAL'];
			$response['msg'] = 'Data berhasil diproses';
		}else{
			$response['msg'] = 'Data gagal diproses';
		}
		
	}
	
	exit($json->encode($response));
}
