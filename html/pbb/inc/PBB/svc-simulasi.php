<?
$penilaianTimeOut = 600;
set_time_limit(800);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");

//variable for input program:
/*$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$tahun = $prm->TAHUN;
$susulan = $prm->SUSULAN;
$pbbminimum = $prm->PBB_MINIMUM;
$tanggal = $prm->TANGGAL;
$tmp = explode('-',$tanggal);
$tanggal = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
$ServerAddress = $svr_param->ServerAddress;
$ServerPort = $svr_param->ServerPort;*/
$tahun = $_REQUEST['TAHUN'];
$pbbminimum = $_REQUEST['PBB_MINIMUM'];
$tanggal = date("Y-m-d");
$ServerAddress = '127.0.0.1';
$ServerPort = '27009';
$ServerTimeOut = $penilaianTimeOut;

$sRequestStream = "{\"PAN\":\"TSP\",\"TAHUN\":\"".$tahun."\",\"PBB_MINIMUM\":\"".$pbbminimum."\",\"TANGGAL\":\"".$tanggal."\"}";

echo $sRequestStream;
$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);

if ($bOK == 0) {
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;
}

?>