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
$alasan		= @isset($_REQUEST['alasan']) ? $_REQUEST['alasan'] : "";
$no_sk		= @isset($_REQUEST['no_sk']) ? $_REQUEST['no_sk'] : "";

$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 
$uname	= $userLogin->GetUserName($uid);
$tahun_tagihan = $_REQUEST['TAHUN_TAGIHAN']; 

$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_PORT = $port;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;

$bOK = false;
//Copy data dari PBB_SPPT ke PBB_SPPT_DIBATALKAN
$bOK = $svcPembatalan->copyToPembatalan($nop,$tahun);
//Delete data yang sudah di copy ke PBB_SPPT_DIBATALKAN dari PBB_SPPT 
if($bOK){ 
	$bOK = $svcPembatalan->delGateWayPBBSPPT($nop,$tahun);
}
if($svcPembatalan->isCurrentExist($nop,$tahun,$tahun_tagihan)){ 
	$respon['isCurrentExist'] = true;
	//INSERT ke tabel SW.cppmod_pbb_sppt_current_dibatalkan
	if($bOK){
		$bOK = $svcPembatalan->copySPPTCurrentToPembatalan($nop,$tahun,$tahun_tagihan);
	}

	if($bOK){
		// DELETE dari SW.cppmod_pbb_sppt_current (kalau data di tabel CURRENT nya ada/SPPT tahun berjalan)
		$bOK = $svcPembatalan->deleteSPPTCurrent($nop,$tahun,$tahun_tagihan);
	}
}else{
	$respon['isCurrentExist'] = false;
}
//Jika di fasumkan 
if($proses==1){
	if($bOK){
		//UPDATE data pada tabel SW.cppmod_pbb_sppt_final/SW.cppmod_pbb_sppt_susulan/SW.cppmod_pbb_sppt field CPM_OT_JENIS nilainya menjadi 4
		if($svcPembatalan->isPBBSPPTExist($nop)){
			$bOK = $svcPembatalan->updateJenisTanahPBBSPPT($nop);
		}
		if($svcPembatalan->isFinalExist($nop,$tahun)){
			$bOK = $svcPembatalan->updateJenisTanahFinal($nop,$tahun);
		}
		if($svcPembatalan->isSusulanExist($nop,$tahun)){
			$bOK = $svcPembatalan->updateJenisTanahSusulan($nop,$tahun);
		}
	}
}
if($proses==2){
	if($bOK){
		//UPDATE data pada tabel SW.cppmod_pbb_sppt_final/SW.cppmod_pbb_sppt_susulan/SW.cppmod_pbb_sppt field CPM_OT_JENIS nilainya menjadi 5
		if($svcPembatalan->isPBBSPPTExist($nop)){
			$bOK = $svcPembatalan->updateJenisTanahPBBSPPTNO($nop);
		}
		if($svcPembatalan->isFinalExist($nop,$tahun)){
			$bOK = $svcPembatalan->updateJenisTanahFinalNO($nop,$tahun);
		}
		if($svcPembatalan->isSusulanExist($nop,$tahun)){
			$bOK = $svcPembatalan->updateJenisTanahSusulanNO($nop,$tahun);
		}
	}
}
if($bOK){
	//UPDATE Tahun Penetapan
	$svcPembatalan->updateTahunPenetapan($nop,$tahun);
}
if($bOK){
	//INSERT proses pembatalan ke cppmod_pbb_log_pembatalan
	//$svcPembatalan->addToLog($uname,$nop,$tahun);
	$svcPembatalan->addToLog($uname,$nop,$tahun, '', $alasan);
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