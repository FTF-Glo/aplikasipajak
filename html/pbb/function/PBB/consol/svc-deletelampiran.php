<?php
class SvcLampiran {

    private $dbSpec = null;

    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }
	
	public function GetAppConfig($appId) {
		$arConfig = null;
		$sQ = sprintf("select * from central_app_config 
			where CTR_AC_AID = '%s' order by CTR_AC_KEY asc",$appId);
		
		if ($this->dbSpec->sqlQuery($sQ, $res)) {
			$bOK = true;
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$i = 0;
				$arConfig = array();
				while ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
					$key = $row["CTR_AC_KEY"];
					$value = $row["CTR_AC_VALUE"];
					$arConfig[$key] = $value;
					$i++;
				}
			}
		}
		return $arConfig;
	}
	
    public function deleteLampiran($doc_id, $op_num) {
        $hasil = false;
        $sql = "DELETE FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='".$doc_id."' AND CPM_OP_NUM='".$op_num."'";
        $bOK = $this->dbSpec->sqlQuery($sql, $res);
		$sql = "SELECT COUNT(CPM_SPPT_DOC_ID) as JML_BANGUNAN FROM cppmod_pbb_sppt_ext WHERE CPM_SPPT_DOC_ID='".$doc_id."'";
		$this->dbSpec->sqlQuery($sql, $res);
		$data = mysqli_fetch_assoc($res);
		$jml_bangunan = $data['JML_BANGUNAN'];
		
		$params = array();
		$params[] = "CPM_OP_JML_BANGUNAN='{$jml_bangunan}'";
		
		if($jml_bangunan == 0){
			$params[] = "CPM_OP_LUAS_BANGUNAN='0'";
			$params[] = "CPM_OP_KELAS_BANGUNAN='XXX'";
			$params[] = "CPM_NJOP_BANGUNAN='0'";
		}else{
			$sql = "SELECT CPM_NOP FROM cppmod_pbb_sppt WHERE CPM_SPPT_DOC_ID='".$doc_id."'";
			$this->dbSpec->sqlQuery($sql, $res);
			if($data = mysqli_fetch_assoc($res)){
				$NOP = $data['CPM_NOP'];
				$appConfig = $this->GetAppConfig('aPBB');
				$ServerAddress = $appConfig['TPB_ADDRESS'];
				$ServerPort = $appConfig['TPB_PORT'];
				$ServerTimeOut = $appConfig['TPB_TIMEOUT'];
				$sRequestStream = "{\"PAN\":\"TPB\",\"NOP\":\"".$NOP."\", \"TAHUN\":\"".$appConfig['tahun_tagihan']."\"}";
				$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);
			}
		}
		
		$sets = implode(',',$params);
		$sql = "UPDATE cppmod_pbb_sppt SET {$sets} WHERE CPM_SPPT_DOC_ID='".$doc_id."'"; 
		$bOK = $this->dbSpec->sqlQuery($sql, $res);
        return $bOK;
    }
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'consol', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcLampiran = new SvcLampiran($dbSpec);

//variable for input program:
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);

$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$doc_id = $prm->DOC_ID;
$op_num = $prm->OP_NUM;

$bOK = $svcLampiran->deleteLampiran($doc_id, $op_num);

if ($bOK == 1) {
    echo 'sukses';
} else echo 'gagal';

?>
