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
$uid		= @isset($_REQUEST['USER_LOGIN']) ? $_REQUEST['USER_LOGIN'] : "";
$alasan		= @isset($_REQUEST['alasan']) ? $_REQUEST['alasan'] : "";
$no_sk		= @isset($_REQUEST['no_sk']) ? $_REQUEST['no_sk'] : "";
$nop_string = "";

$array_all = array();
$kunci = 0;
$temp_array = array();
for($x=0;$x<count($_REQUEST['data']['nop']);$x++){
	array_push($array_all, 
		array(
			'nop'=>$_REQUEST['data']['nop'][$x],
			'tahun'=>$_REQUEST['data']['tahun'][$x],
			'tahun_tagihan'=>$_REQUEST['data']['tahun_tagihan'][$x]
		)
	);
}

$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 
// $dbname = $_REQUEST['use']; 

$uname	= $userLogin->GetUserName($uid);
$uname	= empty($uname) ? $uid : $uname;

$svcPembatalan->C_HOST_PORT = $host;
$svcPembatalan->C_USER = $user;
$svcPembatalan->C_PWD = $pass;
$svcPembatalan->C_DB = $dbname;
$svcPembatalan->C_PORT = $port;

// exit;
$bOK = false;
//Copy data dari PBB_SPPT ke PBB_SPPT_DIBATALKAN
$array_status = array();

foreach ($array_all as $key2=>$value):
	// echo $value['tahun'];
	$tahun = $value['tahun'];
	$nop = $value['nop'];
	$tahun_tagihan = $value['tahun_tagihan'];


	// $bOK = $svcPembatalan->copyToPembatalan($nop, $tahun, $no_sk, $alasan, $uname);
	$bOK = $svcPembatalan->copyToPembatalan($nop, $tahun);
	//Delete data yang sudah di copy ke PBB_SPPT_DIBATALKAN dari PBB_SPPT 
	if($bOK){ 
		$bOK = $svcPembatalan->delGateWayPBBSPPT($nop,$tahun);
		array_push($array_status, 1);
	}else{
		array_push($array_status, 0);
		continue;
	}
	if($svcPembatalan->isCurrentExist($nop,$tahun, $tahun_tagihan)){
		$respon['isCurrentExist'] = true;
		//INSERT ke tabel SW.cppmod_pbb_sppt_current_dibatalkan
		if($bOK){
//			$bOK = $svcPembatalan->copySPPTCurrentToPembatalan($nop,$tahun,$no_sk,$alasan,$uname);
            $bOK = $svcPembatalan->copySPPTCurrentToPembatalan($nop,$tahun,$tahun_tagihan);
			// if (!$bOK){
			// 	echo "fatal sih";
			// }
		}

		if($bOK){
			// //DELETE dari SW.cppmod_pbb_sppt_current (kalau data di tabel CURRENT nya ada/SPPT tahun berjalan)
			$bOK = $svcPembatalan->deleteSPPTCurrent($nop,$tahun, $tahun_tagihan);
			// array_push($array_status, 1);
		}else{
			// array_push($array_status, 0);
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
			else if($svcPembatalan->isFinalExist($nop,$tahun)){
				$bOK = $svcPembatalan->updateJenisTanahFinal($nop,$tahun);
			}
			else if($svcPembatalan->isSusulanExist($nop,$tahun)){
				$bOK = $svcPembatalan->updateJenisTanahSusulan($nop,$tahun);
			}
		}
	}
	if($bOK){
		//UPDATE Tahun Penetapan
		$svcPembatalan->updateTahunPenetapan($nop,$tahun);
	}
	if($bOK){
		//INSERT proses pembatalan ke cppmod_pbb_log_pembatalan
		$svcPembatalan->addToLog($uname,$nop,$tahun, $no_sk, $alasan);
	}

endforeach;
// echo "ini status";
// print_r($array_status);

if(!$bOK){
    $respon['respon'] = false;
	$respon['message'] = mysqli_error($DBLink);
}else{
	$respon['respon'] = true;
	$respon['message'] = "sukses: ".$nop;
}
echo json_encode($respon);exit;
