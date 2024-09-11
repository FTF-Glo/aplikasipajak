<?php
class SvcTanah {
	private $dbSpec = null;
	
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	//DATABASE FUNCTION
	private function getNir($nop, $znt) {
		$nop = mysqli_real_escape_string($DBLink, trim($nop));
		$znt = mysqli_real_escape_string($DBLink, trim($znt));
		
		$loc = substr($nop, 0, 10);
		
		$query = "SELECT * FROM cppmod_pbb_znt WHERE CPM_KODE_LOKASI='$loc' AND CPM_KODE_ZNT='$znt'";
		
		// echo $query;
		
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	private function getKelasBumi($nilai) {
		$nilai = mysqli_real_escape_string($DBLink, trim($nilai));
		
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

require_once("../payment/db-payment.php");
require_once("../payment/inc-payment-db-c.php");
require_once("../central/dbspec-central.php");
require_once("../payment/json.php");

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
$getSvcRequest  = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);
// echo "<pre>";
// print_r($prm);
// echo "</pre>";
$nop = $prm->nop;
$znt = $prm->znt;

/////////////////////////////////////////////////
// dummy data. To be deleted after development //
// choose one data							   //
/////////////////////////////////////////////////
$nop = "1111111111";
$znt = "AA";
//$nop = "2222222222";


$nilai = $svcTanah->getNilai($nop, $znt);
$svcTanah->getKelasNjop($nilai, $kelas, $njop);

$response = array();
$response['r'] = true;
$response['d']['nt'] = $nilai;
$response['d']['kelas'] = $kelas;
$response['d']['njop'] = $njop;

$val = $json->encode($response);
echo $val;
?>