<?php  

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembatalan-sppt', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
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
require_once($sRootPath . "inc/payment/uuid.php");
require_once("classPembatalan.php");

$DBLink = NULL;
$DBConn = NULL;

error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec 		= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcPembatalan	= new SvcPembatalanSPPT($dbSpec);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$userLogin	= new SCANCentralUser (0,LOG_FILENAME,$DBLink);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$nop 		= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$tahun 		= @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$proses		= @isset($_REQUEST['proses']) ? $_REQUEST['proses'] : "";
$uid		= @isset($_REQUEST['uid']) ? $_REQUEST['uid'] : "";

// print_r($_REQUEST); exit;

$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 
$tahun_tagihan = $_REQUEST['TAHUN_TAGIHAN']; 
$uname	= $userLogin->GetUserName($uid);
$alasan		= @isset($_REQUEST['alasan']) ? $_REQUEST['alasan'] : "";

$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;
$svcPembatalan->C_PORT = $port;

$bOK = true;
//Copy data dari PBB_SPPT_DIBATALKAN ke PBB_SPPT
$bOK = $svcPembatalan->copyToPBBSPPT($nop,$tahun);
//Delete data yang sudah di copy ke PBB_SPPT dari PBB_SPPT_DIBATALKAN
if($bOK){ 
	$bOK = $svcPembatalan->delGateWayPBBSPPTPembatalan($nop,$tahun);
} 
if($svcPembatalan->isCurrentPembatalanExist($nop,$tahun)){ 
	if($bOK){
		//INSERT ke tabel SW.cppmod_pbb_sppt_current
		$bOK = $svcPembatalan->copyPembatalanToSPPTCurrent($nop,$tahun,$tahun_tagihan);
	}
	if($bOK){
		//DELETE dari SW.cppmod_pbb_sppt_current (kalau data di tabel CURRENT nya ada/SPPT tahun berjalan)
		$bOK = $svcPembatalan->deleteSPPTCurrentPembatalan($nop,$tahun);
	}
} 

if($proses==1){
	if($bOK){
		//UPDATE data pada tabel SW.cppmod_pbb_sppt_final/SW.cppmod_pbb_sppt_susulan/SW.cppmod_pbb_sppt field CPM_OT_JENIS nilainya menjadi 4
		if($svcPembatalan->isPBBSPPTExist($nop)) $bOK = $svcPembatalan->updateJenisTanahPBBSPPT($nop);
		if($svcPembatalan->isFinalExist($nop,$tahun)) $bOK = $svcPembatalan->updateJenisTanahFinal($nop,$tahun);
		if($svcPembatalan->isSusulanExist($nop,$tahun)) $bOK = $svcPembatalan->updateJenisTanahSusulan($nop,$tahun);
	}
} else if ($proses==2){
	if($bOK){
		if($svcPembatalan->isPBBSPPTExist($nop)){
			if($svcPembatalan->isPBBSPPTExtExist($nop))
				$bOK = $svcPembatalan->updateJenisTanah($nop,$tahun,"cppmod_pbb_sppt","1");
			else
				$bOK = $svcPembatalan->updateJenisTanah($nop,$tahun,"cppmod_pbb_sppt","3");
		}else if($svcPembatalan->isFinalExist($nop,$tahun)){
			$dat 	= $svcPembatalan->getDataFinal($nop,$tahun);
			$id 	= $dat['CPM_SPPT_DOC_ID']; 
			if($svcPembatalan->isFinalExtExist($id))
				$bOK = $svcPembatalan->updateJenisTanah($nop,$tahun,"cppmod_pbb_sppt_final","1");
			else
				$bOK = $svcPembatalan->updateJenisTanah($nop,$tahun,"cppmod_pbb_sppt_final","3");
		}else if($svcPembatalan->isSusulanExist($nop,$tahun)){
            $dat 	= $svcPembatalan->getDataSusulan($nop,$tahun);
            $id 	= $dat['CPM_SPPT_DOC_ID'];
			if($svcPembatalan->isSusulanExtExist($id))
				$bOK = $svcPembatalan->updateJenisTanah($nop,$tahun,"cppmod_pbb_sppt_susulan","1");
			else
				$bOK = $svcPembatalan->updateJenisTanah($nop,$tahun,"cppmod_pbb_sppt_susulan","3");
		}
	}
}

if($bOK){
	//INSERT proses pembatalan ke cppmod_pbb_log_penerbitan
	//$bOK = $svcPembatalan->addToLogPenerbitan($uname,$nop,$tahun);
	$bOK = $svcPembatalan->addToLogPenerbitan($uname,$nop,$tahun,$alasan);
}

if(!$bOK){
    $respon['respon'] = false;
	$respon['message'] = mysqli_error($DBLink);
}else{
	$respon['respon'] = true;
	$respon['message'] = "sukses: ".$nop;
}
echo json_encode($respon);exit;
?>