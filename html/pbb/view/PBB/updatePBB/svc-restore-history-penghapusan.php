<?php  
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'updatePBB', '', dirname(__FILE__))) . '/';

error_reporting(E_ERROR);
ini_set('display_errors', 1);

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
require_once($sRootPath . "inc/PBB/dbMonitoring.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");
require_once($sRootPath . "inc/payment/uuid.php");

$DBLink = NULL;
$DBConn = NULL;

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
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);
$a = $q->a;
$m = $q->m;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$response['msg'] = "Maaf.. proses restore data history penghapusan untuk NOP {$nop} GAGAL.";

if(stillInSession($DBLink,$json,$sdata)){
	
	$periode = (date('n') < $appConfig['susulan_start'] || $appConfig['susulan_end'] < date('n'))? 'FINAL' : 'SUSULAN';
	/*ambil data doc id*/
	$query = sprintf("SELECT CPM_SPPT_DOC_ID FROM CPPMOD_PBB_SPPT_HISTORY WHERE CPM_NOP = '%s'", $nop);
	$res = mysqli_query($DBLink, $query);
	if($data = mysqli_fetch_assoc($res)){
		
		$doc_id = $data['CPM_SPPT_DOC_ID'];
		/*insert ke sppt*/
		$query = sprintf("INSERT INTO CPPMOD_PBB_SPPT_{$periode} 
		SELECT * FROM CPPMOD_PBB_SPPT_HISTORY WHERE CPM_NOP = '%s'", $nop);
		
		/*jika insert ke sspt berhasil*/
		if(mysqli_query($DBLink, $query)){
			
			/*hapus sppt history*/
			$query = sprintf("DELETE FROM CPPMOD_PBB_SPPT_HISTORY WHERE CPM_NOP = '%s'", $nop);
			mysqli_query($DBLink, $query);
		
			/*insert ke sppt ext*/
			$query = sprintf("INSERT INTO CPPMOD_PBB_SPPT_EXT_{$periode} 
			SELECT * FROM CPPMOD_PBB_SPPT_EXT_HISTORY WHERE CPM_SPPT_DOC_ID = '%s'", $doc_id);
			
			/*jika insert ke sspt ext berhasil*/
			if(mysqli_query($DBLink, $query)){
				/*hapus sppt ext history*/
				$query = sprintf("DELETE FROM CPPMOD_PBB_SPPT_EXT_HISTORY WHERE CPM_SPPT_DOC_ID = '%s'", $doc_id);
				mysqli_query($DBLink, $query);
			}
			
			$response['msg'] = "Proses restore data history penghapusan untuk NOP {$nop} berhasil.";
		}
	}else{
		$response['msg'] = "Maaf.. proses data dengan NOP {$nop} tidak tersedia di History Penghapusan.";
	}
	
	
}else{
	$response['msg'] = "Proses restore Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

exit($json->encode($response));
?>
