<?php

class SvcTanah {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	//DATABASE FUNCTION
	private function getNir($nop, $znt) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		$znt = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($znt));
		
		$loc = substr($nop, 0, 10);
		
		$query = "SELECT * FROM cppmod_pbb_znt WHERE CPM_KODE_LOKASI='$loc' AND CPM_KODE_ZNT='$znt'";
		
		// echo $query;
		
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	private function getKelasBumi($nilai) {
		$nilai = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nilai));
		
		$query = "SELECT * FROM cppmod_pbb_kelas_bumi WHERE CPM_NILAI_BAWAH<'$nilai' AND '$nilai'<=CPM_NILAI_ATAS";
		
		// echo $query;
		
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	//GENERAL FUNCTION
	public function getNilai($nop, $znt) {
		$res = $this->getNir($nop, $znt);
		
		return $res[0]['CPM_NIR'];
	}
	
	public function getKelasNjop($nilai, &$kelas, &$njop) {
		$res = $this->getKelasBumi($nilai);
		
		$kelas = $res[0]['CPM_KELAS'];
		$njop = $res[0]['CPM_NJOP_M2'];
	}
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");

require_once("../payment/db-payment.php");
require_once("../payment/inc-payment-db-c.php");
require_once("../central/dbspec-central.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcTanah = new SvcTanah($dbSpec);

//variable for input program: NOP dan ZNT
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$nop = $prm->nop;
$znt = $prm->znt;
$tipe = $prm->tipe;
$tahun = $prm->tahun;
$njop_m2 = $prm->njop_m2;
$njop = $njop_m2 * $tipe;
//var_dump($njop_m2);var_dump($njop);var_dump($nop);var_dump($znt);exit();
if(!empty($njop_m2) && !empty($njop) && !empty($nop) && !empty($znt)){
	$sRequestStream = "{\"KELAS\":\"088\",\"NJOP\":\"$njop\",\"NJOP_M2\":\"$njop_m2\",\"NOP\":\"$nop\", \"PAN\":\"TPT\", \"RC\":\"0000\", \"TAHUN\":\"$tahun\",\"TIPE\":\"$tipe\",\"ZNT\":\"$znt\"}";
	echo $sRequestStream;
}

/*$ServerAddress = $svr_param->ServerAddress;
$ServerPort = $svr_param->ServerPort;
$ServerTimeOut = $svr_param->ServerTimeOut;

//$sRequestStream = "{\"PAN\":\"TPT\",\"ZNT\":\"$znt\",\"TIPE\":\"$tipe\",\"NOP\":\"$nop\", \"TAHUN\":\"$tahun\"}";
$sRequestStream = "{\"KELAS\":\"088\",\"NJOP\":\"$njop\",\"NJOP_M2\":\"$njop_m2\",\"NOP\":\"$nop\", \"PAN\":\"TPT\", \"RC\":\"0000\", \"TAHUN\":\"$tahun\",\"TIPE\":\"$tipe\",\"ZNT\":\"$znt\"}";
#echo "$ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp";exit;

/*$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);

if ($bOK == 0) {
	$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'*/
    //echo $sRequestStream;
//}
?>
