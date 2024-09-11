<?php
$penilaianTimeOut = 3600;
set_time_limit(3700);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");

//variable for input program:
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param 		= $json->decode(base64_decode($prm->SVR_PRM));
$tahun 			= $prm->TAHUN;
$kd_kab 		= $prm->KABUPATEN;
$ServerAddress 	= $svr_param->ServerAddress;
$ServerPort 	= $svr_param->ServerPort;
$ServerTimeOut 	= $penilaianTimeOut;//$svr_param->ServerTimeOut;

$sRequestStream = "{\"PAN\":\"TPR\",\"TAHUN\":\"".$tahun."\",\"KABUPATEN\":\"".$kd_kab."\"}";
// echo $sRequestStream; exit;
// echo $ServerAddress.$ServerPort.$ServerTimeOut.$sRequestStream.$sResp; exit;

$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);

if ($bOK == 0) {
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;
}

?>