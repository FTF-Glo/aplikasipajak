<?php
//exit;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$tahun = "2022";
$nop = "";
$param = base64_encode('{"SVR_PRM":"eyJTZXJ2ZXJBZGRyZXNzIjoibG9jYWxob3N0IiwiU2VydmVyUG9ydCI6IjI3MDA4IiwiU2VydmVyVGltZU91dCI6IjEyMCJ9","KELURAHAN":"696969", "NOP":"'.$nop.'", "TAHUN":"' . $tahun . '", "TIPE":"1", "SUSULAN":"0"}');

DEFINE('DEBUG', true);
DEFINE('DEBUG_DEBUG', true);
DEFINE('END_OF_MSG', true);

$penilaianTimeOut = 3600;
set_time_limit(3700);
require_once("inc/payment/comm-central.php");
require_once("inc/payment/ctools.php");
require_once("inc/payment/json.php");

//variable for input program:
$getSvcRequest = $param;
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$nop = isset($prm->NOP) ? $prm->NOP : '';
$tahun = isset($prm->TAHUN) ? $prm->TAHUN : '';
$tipe = isset($prm->TIPE) ? $prm->TIPE : '';
$kelurahan = isset($prm->KELURAHAN) ? $prm->KELURAHAN : '';
$susulan = isset($prm->SUSULAN) ? $prm->SUSULAN : '';
$ServerAddress = $svr_param->ServerAddress;
$ServerPort = $svr_param->ServerPort;
$ServerTimeOut = $penilaianTimeOut; //$svr_param->ServerTimeOut;

$sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"" . $tahun . "\",\"KELURAHAN\":\"" . $kelurahan . "\",\"TIPE\":\"" . $tipe . "\",\"NOP\":\"" . $nop . "\",\"SUSULAN\":\"" . $susulan . "\"}";
// echo $sRequestStream . PHP_EOL;
//exit;
$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);

if ($bOK == 0) {
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;
}
