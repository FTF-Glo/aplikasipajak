<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$penilaianTimeOut = 600;
set_time_limit(800);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
// tambahan aldes
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
// tambahan aldes -- end
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
// tambahan aldes
// require_once($sRootPath . "inc/PBB/dbSppt.php");
// require_once($sRootPath . "inc/PBB/dbSpptExt.php");
// require_once($sRootPath . "inc/PBB/dbSpptHistory.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp('aPBB');
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);
// $dbSpptHistory = new DbSpptHistory($dbSpec);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
// tambahan aldes -- end

//variable for input program:
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$nop = isset($prm->NOP) && $prm->NOP != null ? $prm->NOP : '';
$tahun = $prm->TAHUN;
$tipe = $prm->TIPE;
$susulan = $prm->SUSULAN;
$kelurahan = isset($prm->KELURAHAN) ? $prm->KELURAHAN : '';
$tanggal = $prm->TANGGAL;
$uname = $prm->USER;
$tmp = explode('-', $tanggal);
$tanggal = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
//$ServerAddress = $svr_param->ServerAddress;
$ServerAddress = "localhost";
$ServerPort = $svr_param->ServerPort;
$ServerTimeOut = $penilaianTimeOut; //$svr_param->ServerTimeOut;

$sRequestStream = "{\"PAN\":\"TP\",\"TAHUN\":\"" . $tahun . "\",\"KELURAHAN\":\"" . $kelurahan . "\",\"TIPE\":\"" . $tipe . "\",\"NOP\":\"" . $nop . "\",\"SUSULAN\":\"" . $susulan . "\",\"TANGGAL\":\"" . $tanggal . "\",\"USER\":\"" . $uname . "\"}";

$appConfig = $User->GetAppConfig('aPBB');
$tahunTagihan = $appConfig['tahun_tagihan'];
// if(isset($_GET['tgl_penetapan'])) {
//     $tahunTagihan = !empty($_GET['tgl_penetapan']) ? date('Y', strtotime($_GET['tgl_penetapan'])) : $tahunTagihan;
// }
$res = $dbFinalSppt->editTemp($nop);
$resmove = $dbFinalSppt->moveTemp($nop, $tahunTagihan);

//Tambahan
/*$data = $dbFinalSppt->get_by_nop($nop);

if (isset($data[0]['CPM_SPPT_DOC_ID']) && !empty($data[0]['CPM_SPPT_DOC_ID']) && $data[0]['CPM_SPPT_DOC_ID'] != null) {
    //$aVal['CPM_TRAN_STATUS'] = 4;
    $datsppt['CPM_SPPT_THN_PENETAPAN'] = $tahun;
    $dbFinalSppt->edits($data[0]['CPM_SPPT_DOC_ID'], $nop, $datsppt);
    /*$dbSpptTran->edit($data[0]['CPM_TRAN_ID'], $aVal);

    $dbSpptTran->move($data[0]['CPM_TRAN_SPPT_DOC_ID']);*/

/*$sResp = '{"RC":"0000","KELURAHAN":"' . $kelurahan . '","JML":"1","TAHUN":"' . $tahun . '","PAN":"TP","USER":"","TIPE":"' . $tipe . '","NOP":"' . $nop . '","TANGGAL":"' . $tanggal . '","SUSULAN":"' . $susulan . '"}';
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;
}*/

/*var_dump($ServerAddress);
var_dump($ServerPort);
var_dump($ServerTimeOut);
var_dump($sRequestStream);
//var_dump($sResp);
exit();*/

//$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);

// aldes edit -- test test 1 2 3
// $bOK = 1;
// $table_sppt = 'cppmod_pbb_sppt';
// $table_sppt_ext = 'cppmod_pbb_sppt_ext';
// if($susulan == "1"){
// $table_sppt = 'cppmod_pbb_sppt';
// 	$table_sppt_ext = 'cppmod_pbb_sppt_susulan_ext';
// }

// $query = "SELECT a.CPM_TRAN_ID FROM cppmod_pbb_tranmain a INNER JOIN {$table_sppt} b ON a.CPM_TRAN_SPPT_DOC_ID = b.CPM_SPPT_DOC_ID WHERE b.CPM_NOP = '{$nop}' ORDER BY CPM_TRAN_DATE DESC LIMIT 1";
// $bOKNew = $dbSpec->sqlQueryRow($query, $resTranId);
// die(var_dump($resTranId));
// if($bOKNew && $resTranId){
// 	$dbSpptHistory->goFinal($resTranId[0]['CPM_TRAN_ID'], $tahun);
// }



// echo $ServerAddress."<br>";
// echo $ServerPort."<br>";
// echo $ServerTimeOut."<br>";
// echo $sRequestStream."<br>";
// echo $sResp."<br>";
//if ($bOK == 0) {
//$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
//echo $sResp;
//}

echo 1;
