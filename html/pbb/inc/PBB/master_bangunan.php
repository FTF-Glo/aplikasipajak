<?
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");

//variable for input program:
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$lantai = $prm->LANTAI;
$jpb = str_pad($prm->JPB, 2, "0", STR_PAD_LEFT);
$tipe = $prm->TIPE;
$nop = $prm->NOP;
$tahun = $prm->TAHUN;
$ServerAddress = $svr_param->ServerAddress;
$ServerPort = $svr_param->ServerPort;
$ServerTimeOut = $svr_param->ServerTimeOut;

$sRequestStream = "{\"PAN\":\"TPB\",\"LANTAI\":\"$lantai\",\"JPB\":\"$jpb\",\"TIPE\":\"$tipe\",\"NOP\":\"$nop\", \"TAHUN\":\"$tahun\"}";
//echo $sRequestStream;
$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);

if ($bOK == 0) {
    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
    echo $sResp;
}
?>