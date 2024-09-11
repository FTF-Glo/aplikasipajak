<?
set_time_limit(3700);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");

$json		= new Services_JSON();
$tJsonReq 	= file_get_contents('php://input');
$tmRequest	= $json->decode($tJsonReq);

$ipServerQS		= "192.168.26.111";
$portServerQS	= 23666;
$ServerTimeOut 	= 3600;
$sRequestStream = '{"f":"pbbv21.inquerypbb.bpn","PAN":"85000","i":{"nop":"'.$tmRequest->NOP.'","tahunpajak":"'.$tmRequest->TahunPajak.'"}}';

if (GetRemoteResponse($ipServerQS, $portServerQS, $ServerTimeOut, $sRequestStream, $sResp) == 0) {
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;
}

?>
