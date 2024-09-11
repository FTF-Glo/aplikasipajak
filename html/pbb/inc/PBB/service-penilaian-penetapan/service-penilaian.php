<?php
ini_set('display_errors',1);
error_reporting(E_ERROR);

class Kelurahan {

    private $dbSpec = null;
    private $kode;
    
    public function __construct($dbSpec) {
        $this->dbSpec = $dbSpec;
    }
	
	public function setKode($kode){
		$this->kode = mysql_real_escape_string(trim($kode));
		return $this;
	}
	
	public function getData(){
		$query = "
		SELECT 
			CPC_TKL_ID, 
			CPC_TKL_KELURAHAN 
		FROM cppmod_tax_kelurahan 
		WHERE CPC_TKL_ID LIKE '{$this->kode}%' 
		ORDER BY CPC_TKL_ID ASC";
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
		return false;
	}
}

class Service {
	
	private $url;
	private $kode;
	private $Kelurahan;
	
	public function __construct($dbSpec, $arg){
		$this->url = $arg[0];
		
		if(isset($arg[1])){
			$this->kode = $arg[1];
			$this->Kelurahan = new Kelurahan($dbSpec);
			$this->Kelurahan->setKode($this->kode);
			$this->proses();
		}else{
			#system('clear');
			$this->info();
		}
	}
	
	public function proses(){
		$data = $this->Kelurahan->getData();
		if($data){ 
			if($this->confirm($data)){
				$this->doMainProcess($data);
			}
		}else{
			#system('clear');
			$this->errorMessage();
			exit;
		}
	}
	
	
	private function doMainProcess($data){
		global $appConfig;
		
		$susulan = "0";
		$nop = "";
		$tahun = $appConfig['SERVICE_TAHUN'];
		$ServerAddress = $appConfig['SERVICE_HOST'];
		$ServerPort = $appConfig['SERVICE_PORT'];
		$ServerTimeOut = $appConfig['SERVICE_TIMEOUT'];
		$tanggal = $appConfig['SERVICE_TANGGAL'];
		
		set_time_limit($ServerTimeOut);
		echo "[".date('d-m-Y H:i:s')."]Starting...\n";
		echo "Tahun : ".$tahun."\n";
		echo "Address : ".$ServerAddress."\n";
		echo "Port : ".$ServerPort."\n";
		echo "Timeout :".$ServerTimeOut."\n";
		echo "Tgl :".$tanggal."\n";

		$i = 0;
		foreach($data as $row){
			$i++;
			
			$nomor = str_pad($i,3,'0',STR_PAD_LEFT);
			$kdKel = $row['CPC_TKL_ID'];
			$nmKel = $row['CPC_TKL_KELURAHAN'];
			
			/*BEGIN PENILAIAN*/
			$status = "error";
			$total = 0;
			try {
				$sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"".$tahun."\",\"KELURAHAN\":\"".$row['CPC_TKL_ID']."\",\"TIPE\":\"1\",\"NOP\":\"".$nop."\",\"SUSULAN\":\"".$susulan."\"}"; 
				$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);
				if ($bOK == 0) {
					$sResp = rtrim($sResp, END_OF_MSG);
					$r = explode(",",$sResp);
					$j = explode(":",$r[1]);
					$rc = explode(":",$r[3]);
					$total = str_replace('"','',$j[1]);
					$status = str_replace('"','',$rc[1]);
				}
			}catch(Exception $e) { $this->write_log(__LINE__, $e->getMessage()); }
			echo "[".date('d-m-Y H:i:s')."][Penilaian : {$status}][{$total} NOP] {$nomor}. {$kdKel} - {$nmKel}\n";
			/*END PENILAIAN*/
			
			
		}
		echo "[".date('d-m-Y H:i:s')."]Finished.\n\n";
	}
	
	private function confirm($data){
		echo "Data ditemukan sebanyak ".count($data)." kelurahan.\n";
		echo "Anda yakin akan melanjutkan ?\n";
		echo "Ketik 'ya' untuk melanjutkan proses Penilaian : ";
		$handle = fopen ("php://stdin","r");
		$line = fgets($handle);
		if(trim($line) != 'ya'){
			echo "Proses dibatalkan.\n";
			return false;
		}
		fclose($handle);
		echo "\n";
		return true;
	}
	
	private function errorMessage(){
		$lenCode = strlen($this->kode);
		
		echo "Pesan Error: Kode '{$this->kode}' tidak valid ";
		if($lenCode == 10) echo "[Kelurahan tidak ditemukan]\n";
		elseif($lenCode == 7) echo "[Kecamatan tidak ditemukan]\n";
		elseif($lenCode == 4) echo "[Kabupaten / Kota tidak ditemukan]\n";
		else $this->info();
		echo "\n";
	}
	
	private function info(){
		echo "Penggunaan: php service.php [kode_kabkota]\n";
		echo "            php service.php [kode_kec]\n";
		echo "            php service.php [kode_kel]\n";
		echo "\n";
		echo "  kode_kabkota          : Kode kabupaten atau kota, contoh : php service.php 3204\n";
		echo "  kode_kec              : Kode Kecamatan, contoh php service.php 3204111\n";
		echo "  kode_kel              : Kode Kelurahan, contoh php service.php 3204140010\n";
		echo "\n";
	}
	
	public function write_log($line, $message) {
		global $appConfig;
		$logPath = $appConfig['SERVICE_LOG_PATH'];
		$logfile = $logPath."error_log_".date("dmY").".log";
		
		if( ($time = $_SERVER['REQUEST_TIME']) == '') {
			$time = time();
		}
		
		$date = date("Y-m-d H:i:s", $time);
		if($fd = @fopen($logfile, "a")) {
			$result = fputcsv($fd, array($date, "Line {$line}", $message));
			fclose($fd);
		}
	}
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'service-penilaian-penetapan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific('DEBUG', 'LOG_DMS_FILENAME', $DBLink);
$User = new SCANCentralUser('DEBUG', 'LOG_DMS_FILENAME', $DBLink);
$appConfig = $User->GetAppConfig('svcPBB');
$Service = new Service($dbSpec, $argv);
?>
