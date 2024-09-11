<?
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'deposit', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/electricity/electricity-protocol-generic.php");
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/deposit/deposit-protocol-debit-credit.php");
require_once($sRootPath."inc/deposit/deposit-protocol-balance-check.php");
require_once($sRootPath."inc/deposit/deposit-protocol-registration.php");
require_once($sRootPath."inc/deposit/deposit-protocol-maintenance.php");
require_once($sRootPath."inc/deposit/deposit-protocol-transfer.php");
require_once($sRootPath."inc/deposit/deposit-protocol-delete.php");
require_once($sRootPath."inc/deposit/deposit-protocol-list.php");
require_once($sRootPath."inc/deposit/deposit-protocol-list-account.php");
require_once($sRootPath."inc/deposit/deposit-protocol-list-relation.php");
require_once($sRootPath."inc/deposit/deposit-protocol-add-relation.php");
require_once($sRootPath."inc/deposit/deposit-protocol-query-account.php");
class ServiceDeposit 
{
	private $ServerAddress;
	private $ServerPort;
	private $ServerTimeOut;
	
	function __construct($address, $port, $timeout)
	{
		$this->ServerAddress = $address;
		$this->ServerPort = $port;
		$this->ServerTimeOut = $timeout;
	}
	
	/**
	 * Prosedur untuk melakukan aksi debit
	 * param : 
	 * 		$account : account number yang akan didebit
	 *		$amount : jumlah uang yang akan di debit
	 * 		$message : (optional) pesan tambahan ke sistem
	 * 		$moduleID : (optional) module/PAN
	  * 		$operatorID : (optional) operator/central id
	   * 		$transactCode : (optional) transaction code default deposit top-up
	   * 		$AuthCode : (optional) Authorization code default dms
	 * return type : boolean
	 *		true : debit berhasil
	 *		false : debit gagal
	 */
	public function Debit(&$rc, $account, $amount, $message = " ",$moduleID="88001",$operatorID="",$transactCode="deposit top-up",$AuthCode="dms") {
		$bSuccess = false;
			
		$aPrivData = array();
		$aPrivData['amount'] = $amount;
		$aPrivData['sendermodname'] =$moduleID;
		$aPrivData['swrefnum'] = " ";
		$aPrivData2 = $message;
		$aPrivData3 = $operatorID;
		$aPrivData4 = $transactCode;
		$aPrivData5 = $AuthCode;
			
		$DepositDebitCredit = new DepositProtocolDebitCreditRequest();
		$DepositDebitCredit->SetComponentTmp('pan', 	$account);
		$DepositDebitCredit->SetComponentTmp('p_code', 	"010000");
		$DepositDebitCredit->SetComponentTmp('rp', 		$amount);
		$DepositDebitCredit->SetComponentTmp('dt', 		strftime("%Y%m%d%H%M%S",time()));
		$DepositDebitCredit->SetComponentTmp('priv',	$aPrivData);
		$DepositDebitCredit->SetComponentTmp('priv2',	$aPrivData2);
		$DepositDebitCredit->SetComponentTmp('priv3',	$aPrivData3);
		$DepositDebitCredit->SetComponentTmp('priv4',	$aPrivData4);
		$DepositDebitCredit->SetComponentTmp('priv5',	$aPrivData5);
		
		$DepositDebitCredit->ConstructStream();
		$sDepositDebitCreditStream = $DepositDebitCredit->GetConstructedStream();
		
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositDebitCreditStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositDebitCreditStream, $sResp);
		
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolDebitCreditResponse($sResp);
			$Res->ExtractDataElement();
			
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if ($Res->dataElement['mti'] == '2210')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$bSuccess = true;
				}
			}
		}
		return $bSuccess;
	}
	
	/**
	 * Prosedur untuk melakukan aksi credit
	 * param : 
	 * 		$account : account number yang akan dikredit
	 *		$amount : jumlah uang yang akan dikredit
	 * 		$message : (optional) pesan tambahan ke sistem
	  * 		$moduleID : (optional) module/PAN
	  * 		$operatorID : (optional) operator/central id
	   * 		$transactCode : (optional) transaction code default deposit top-up
	   * 		$AuthCode : (optional) Authorization code default dms
	 * return type : boolean
	 *		true : kredit berhasil
	 *		false : kredit gagal
	 */
	public function Credit(&$rc, $account, $amount, $message = " ",$moduleID="88001",$operatorID="",$transactCode="deposit credit",$AuthCode="dms") {
		$bSuccess = false;
			
		$aPrivData = array();
		$aPrivData['amount'] = $amount;
		$aPrivData['sendermodname'] =$moduleID;
		$aPrivData['swrefnum'] = " ";
		$aPrivData2 = $message;
		$aPrivData3 = $operatorID;
		$aPrivData4 = $transactCode;
		$aPrivData5 = $AuthCode;
			
		$DepositDebitCredit = new DepositProtocolDebitCreditRequest();
		$DepositDebitCredit->SetComponentTmp('pan', 	$account);
		$DepositDebitCredit->SetComponentTmp('p_code', 	"210000");
		$DepositDebitCredit->SetComponentTmp('rp', 		$amount);
		$DepositDebitCredit->SetComponentTmp('dt', 		strftime("%Y%m%d%H%M%S",time()));
		$DepositDebitCredit->SetComponentTmp('priv',	$aPrivData);
		$DepositDebitCredit->SetComponentTmp('priv2',	$aPrivData2);
		$DepositDebitCredit->SetComponentTmp('priv3',	$aPrivData3);
		$DepositDebitCredit->SetComponentTmp('priv4',	$aPrivData4);
		$DepositDebitCredit->SetComponentTmp('priv5',	$aPrivData5);
		
		$DepositDebitCredit->ConstructStream();
		$sDepositDebitCreditStream = $DepositDebitCredit->GetConstructedStream();
		
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositDebitCreditStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositDebitCreditStream, $sResp);
		
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolDebitCreditResponse($sResp);
			$Res->ExtractDataElement();
			
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if ($Res->dataElement['mti'] == '2210')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$bSuccess = true;
				}
			}
		}
		return $bSuccess;
	}
	
	/**
	 * Prosedur untuk melakukan pengecekan account balance
	 * param : 
	 * 		$account : account number yang akan dicek
	 * return type : boolean / string
	 *		false : pengecekan gagal
	 *		string : balance amount (dalam angka)
	 */
	public function BalanceCheck(&$rc, $account) {
		$balance = null;
		
		$BalanceReq = new DepositProtocolBalanceCheckRequest();
		$BalanceReq->SetComponentTmp('pan', $account);
		$BalanceReq->SetComponentTmp('dt', 	strftime("%Y%m%d%H%M%S",time()));
		$BalanceReq->ConstructStream();
		$sBalanceRequestStream = $BalanceReq->GetConstructedStream();
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sBalanceRequestStream, $sResp);
		
		if($bOK == 0)
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolBalanceCheckResponse($sResp);
			$Res->ExtractDataElement();
			
			// echo "<pre>";
			// print_r($Res->dataElement);
			// echo "</pre>";
			
			if ($Res->dataElement['mti'] == '2210') 
			{ 
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // valid balance check response
				{
					$balance = ltrim($Res->dataElement['priv']['amount'], "0");
					if($balance=="")$balance="0";
				}
			}
		}
		return $balance;
	}
	
	/**
	 * Prosedur untuk mengambil daftar transaksi
	 * param : 
	 * 		$account : account number yang akan diambil transaksinya
	 *		$dtfrom : Tanggal mulai pengecekan
	 *		$dtuntil : Tanggal akhir pengecekan
	 * 		$start : (optional) dimulai dari transaksi ke. Bila tidak dinyatakan, dimulai dari 1
	 * 		$total : (optional) Jumlah maksimal transaksi yang akan diambil. Bila tidak dinyatakan, diambil seluruh transaksi (mulai dtfrom sampai dtuntil)
	 * 		$agregat : (optional) Mode agregat. "0" untuk no agregat mode, "1" untuk agregat mode.
	 * return type : boolean / Array
	 *		false : pengambilan daftar transaksi gagal
	 *		Array : daftar transaksi
	 *			Array daftar transaksi berisi array dengan key:
	 *			- Reference Number 	
	 *			- Transaction Amount
	 *			- (D)/(C) 			
	 *			- Modules 			
	 *			- Transaction Time 	
	 *			- Custom Message 	
	 *			- Transaction Code 	
	 *			- Authorization Code
	 *			- Balance 			
	 */
	public function ListTransaction(&$rc, $account, $dtfrom, $dtuntil, $start = null, $total = null, $agregat = null,$lookbyrefnum=0,$refnum="") {
		$data = false;
		$json = new Services_JSON();
		
		$aPrivData = array();
		$aPrivData['dt_from'] = $dtfrom;
		$aPrivData['dt_until'] = $dtuntil;
		$aPrivData['start_idx'] = $start;
		$aPrivData['total'] = $total;
		$aPrivData['aggregat'] = $agregat;
		
		$ListReq = new DepositProtocolListRequest();
		$ListReq->SetComponentTmp('pan', $account);
		$ListReq->SetComponentTmp('dt', strftime("%Y%m%d%H%M%S",time()));
		$ListReq->SetComponentTmp('priv', $aPrivData);
		$ListReq->SetComponentTmp('priv2', $refnum);
		$ListReq->SetComponentTmp('priv3', $lookbyrefnum);
		
		$ListReq->ConstructStream();
		$sListRequestStream = $ListReq->GetConstructedStream();
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sListRequestStream, $sResp);
		//echo $sListRequestStream."\n".$sResp;
		if($bOK == 0)
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolListResponse($sResp);
			$Res->ExtractDataElement();
			
			// echo "<pre>";
			// print_r($Res->dataElement);
			// echo "</pre>";
			
			if ($Res->dataElement['mti'] == '2210')
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // valid balance check response
				{
					$tdata = $json->decode(base64_decode($Res->dataElement['data']));
					$max_total = intval($Res->dataElement['priv']['total_retrieved']);
					
					for ($i=0; $i<$max_total; $i++) {
						foreach ($tdata as $key => $value) {
							$data[$i][$key] = $value[$i];
							// echo "$key<br>";
						}
					}
				}
			}
		}
		return $data;
	}
	
	/**
	 * Prosedur untuk melakukan pendaftaran deposit account baru
	 * param : 
	 * 		$account : account number yang akan ditambahkan
	 *		$deposit : jumlah deposit awal
	 * 		$desc : deskripsi account yang baru. Biasanya diisi nama pemilik account
	 * return type : boolean
	 *		true : penambahan berhasil
	 *		false : penambahan gagal
	 */
	public function Register (&$rc, $account, $deposit, $desc) {
		$bSuccess = false;
		
		$RegistrationReq = new DepositProtocolRegistrationRequest();
		$RegistrationReq->SetComponentTmp('pan', $account);
		$RegistrationReq->SetComponentTmp('dt', strftime("%Y%m%d%H%M%S",time()));
		$RegistrationReq->SetComponentTmp('priv', $deposit);
		$RegistrationReq->SetComponentTmp('priv2', $desc);
		
		$RegistrationReq->ConstructStream();
		$sRegistrationRequestStream = $RegistrationReq->GetConstructedStream();
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sRegistrationRequestStream, $sResp);
		
		if($bOK == 0)
		{	
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolRegistrationResponse($sResp);
			$Res->ExtractDataElement();
			
			// echo "<pre>";
			// print_r($Res->dataElement);
			// echo "</pre>";
			$rc = intval($Res->dataElement['rc']);
			
			if ($Res->dataElement['mti'] == '2610') 
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // valid balance check response
				{
					$bSuccess = true;
				}
			}
		}
		return $bSuccess;
	}
	
	/**
	 * Prosedur untuk meminta account maintenance
	 * param : 
	 * 		$account : account number yang akan di-maintenance
	 * return type : boolean
	 *		true : permintaan maintenance berhasil
	 *		false : permintaan maintenance gagal
	 */
	public function Maintenance(&$rc, $account) {
		$bSuccess = false;
		
		$MaintenanceReq = new DepositProtocolMaintenanceRequest();
		$MaintenanceReq->SetComponentTmp('pan', $account);
		$MaintenanceReq->SetComponentTmp('dt',  strftime("%Y%m%d%H%M%S",time()));
		$MaintenanceReq->ConstructStream();
		$sMaintenanceRequestStream = $MaintenanceReq->GetConstructedStream();
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sMaintenanceRequestStream, $sResp);

		if($bOK == 0)
		{	
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolMaintenanceResponse($sResp);
			$Res->ExtractDataElement();
			
			// echo "<pre>";
			// print_r($Res->dataElement);
			// echo "</pre>";
			
			if ($Res->dataElement['mti'] == '2610') 
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // valid balance check response
				{
					$bSuccess = true;
				}
			}
		}
		return $bSuccess;
	}
	
	/**
	 * Prosedur untuk melakukan transfer
	 * param : 
	 * 		$source_account : account number yang akan mengirimkan uang
	 * 		$amount : jumlah uang yang akan ditransfer
	 * 		$message : pesan transfer
	 * 		$receiver_account : account number tujuan yang akan menerima uang
	 * 		$receiver_ppid : (optional) PPID penerima yang akan menerima uang
	 * return type : boolean
	 *		true : transfer berhasil
	 *		false : transfer gagal
	 */
	public function Transfer(&$rc, $source_account, $amount, $message, $receiver_account, $receiver_ppid=" ") {
		$bSuccess = false;
	
		$aPrivData = array();
		$aPrivData['amount'] = $amount;
		$aPrivData['sendermodname'] = "88001";
		$aPrivData['swrefnum'] = " ";
		$aPrivData2 = $message;
		$aPrivData3 = "0110101";
		$aPrivData4 = "deposit transfer";
		$aPrivData5 = "dms";

		$DepositTransfer = new DepositProtocolTransferRequest();
		$DepositTransfer->SetComponentTmp('pan', 	$source_account);
		$DepositTransfer->SetComponentTmp('dt', 	strftime("%Y%m%d%H%M%S",time()));
		$DepositTransfer->SetComponentTmp('ppid',	$receiver_ppid);
		$DepositTransfer->SetComponentTmp('RAN',	$receiver_account);
		$DepositTransfer->SetComponentTmp('priv',	$aPrivData);
		$DepositTransfer->SetComponentTmp('priv2',	$aPrivData2);
		$DepositTransfer->SetComponentTmp('priv3',	$aPrivData3);
		$DepositTransfer->SetComponentTmp('priv4',	$aPrivData4);
		$DepositTransfer->SetComponentTmp('priv5',	$aPrivData5);

		$DepositTransfer->ConstructStream();
		$sDepositTransferStream = $DepositTransfer->GetConstructedStream();
		
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositTransferStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositTransferStream,$sResp);
		
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolTransferResponse($sResp);
			$Res->ExtractDataElement();
			
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if (isset($Res->dataElement['mti']) && $Res->dataElement['mti'] == '2210')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$bSuccess = true;
				}
			}
		}
		
		return $bSuccess;	
	}
	
	/**
	 * Prosedur untuk melakukan penghapusan
	 * param : 
	 * 		$account : account number yang akan dihapus
	 * return type : boolean
	 *		true : penghapusan berhasil
	 *		false : penghapusan gagal
	 */
	public function Delete(&$rc, $account) {
		$bSuccess = false;

		$DepositDelete = new DepositProtocolDeleteRequest();
		$DepositDelete->SetComponentTmp('pan', 	$account);
		$DepositDelete->SetComponentTmp('dt', 	strftime("%Y%m%d%H%M%S",time()));

		$DepositDelete->ConstructStream();
		$sDepositDeleteStream = $DepositDelete->GetConstructedStream();
		
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositDeleteStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositDeleteStream,$sResp);
		
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolDeleteResponse($sResp);
			$Res->ExtractDataElement();
			
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if (isset($Res->dataElement['mti']) && $Res->dataElement['mti'] == '2610')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$bSuccess = true;
				}
			}
		}		
		return $bSuccess;	
	}
	
	/**
	 * Prosedur untuk menampilkan daftar account number yang tercata
	 * param : 
	 * 		$filter : diisi dengan
	 *				"0" = inactive
	 *				"1" = active
	 *				"2" = blocked
	 *				"3" = deleted
	 *				"4" = all
	 * return type : boolean / Array
	 *		false : permintaan gagal
	 *		Array : Daftar account number 
	 *			array daftar account number berisi array dengan atribut (key) : 
	 *				"Status"
	 *				"Account Number"
	 */
	public function ListAccount(&$rc, $filter) {
		$data = false;
		$json = new Services_JSON();
		
		$DepositListAccount = new DepositProtocolListAccountRequest();
		$DepositListAccount->SetComponentTmp('dt', 	strftime("%Y%m%d%H%M%S",time()));
		$DepositListAccount->SetComponentTmp('priv', $filter);

		$DepositListAccount->ConstructStream();
		$sDepositListAccountStream = $DepositListAccount->GetConstructedStream();
		// echo "sDepositListAccountStream :<br>".$sDepositListAccountStream."<br>";
		$abt=$DepositListAccount->GenerateBitmap();
		//var_dump($abt);
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositListAccountStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositListAccountStream,$sResp);
		// echo "sResp :<br>".$sResp."<br>";
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolListAccountResponse($sResp);
			$Res->ExtractDataElement();
			// echo "<pre>";
			// print_r($Res->dataElement);
			// echo "</pre>";
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if (isset($Res->dataElement['mti']) && $Res->dataElement['mti'] == '2610')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$tdata = $json->decode(base64_decode($Res->dataElement['data']));
					$total = count($tdata->Status);
					for ($i=0; $i<$total; $i++) {
						foreach ($tdata as $key => $value) {
							$data[$i][$key] = $value[$i];
						}
					}					
				}
			}
		}		
		return $data;	
	}
	
	/**
	 * Prosedur untuk menampilkan daftar relasi
	 * param : 
	 *		$account : account number yang relasinya akan diambil
	 * 		$filter : diisi dengan
	 *				"0" = inactive
	 *				"1" = active
	 *				"2" = blocked
	 *				"3" = deleted
	 *				"4" = all
	 *		$flag : diisi dengan
	 *				"0" = get member
	 *				"1" = get group
	 * return type : boolean / Array
	 *		false : permintaan gagal
	 *		Array : Daftar relasi
	 *			Array Daftar relasi berisi array dengan atribut (key) :
	 *				- Status
	 *				- Relation
	 *				- Account Number
	 */
	public function ListRelation(&$rc, $account, $filter, $flag) {
		$data = false;
		$json = new Services_JSON();
		
		$DepositRequest = new DepositProtocolListRelationRequest();
		$DepositRequest->SetComponentTmp('pan',	$account);
		$DepositRequest->SetComponentTmp('dt', 	strftime("%Y%m%d%H%M%S",time()));
		$DepositRequest->SetComponentTmp('priv', 	$filter);
		$DepositRequest->SetComponentTmp('priv2', 	$flag);

		$DepositRequest->ConstructStream();
		$sDepositRequestStream = $DepositRequest->GetConstructedStream();
		
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositRequestStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositRequestStream, $sResp);
		
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolListRelationResponse($sResp);
			$Res->ExtractDataElement();
			
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if (isset($Res->dataElement['mti']) && $Res->dataElement['mti'] == '2610')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$tdata = $json->decode(base64_decode($Res->dataElement['data']));
					$total = count($tdata->Status);
					
					for ($i=0; $i<$total; $i++) {
						foreach ($tdata as $key => $value) {
							$data[$i][$key] = $value[$i];
						}
					}
				}
			}
		}		
		return $data;	
	}
	
	/**
	 * Prosedur untuk menambahkan relasi
	 * param : 
	 * 		$account : account number yang relasinya akan ditambahkan
	 *		$uplimit : Jumlah limit balance maksimal (dalam angka)
	 *		$downlimit : Jumlah limit balance minimal (dalam angka)
	 *		$target_account : account number penerima yang akan direlasikan dengan $account
	 *		$relation_type : tipe relasi, diisi dengan :
	 *			"1" = swapping
	 *			"2" = top up
	 *			"3" = zero balance
	 * return type : boolean
	 *		true : penambahan berhasil
	 *		false : penambahan gagal
	 */
	public function AddRelation(&$rc, $account, $uplimit, $downlimit, $target_account, $relation_type) {
		$bSuccess = false;
		
		$DepositRequest = new DepositProtocolAddRelationRequest();
		$DepositRequest->SetComponentTmp('pan',		$account);
		$DepositRequest->SetComponentTmp('rpup',	$uplimit);
		$DepositRequest->SetComponentTmp('rpdown',	$downlimit);
		$DepositRequest->SetComponentTmp('dt', 		strftime("%Y%m%d%H%M%S",time()));
		$DepositRequest->SetComponentTmp('ran', 	$target_account);
		$DepositRequest->SetComponentTmp('priv', 	$relation_type);

		$DepositRequest->ConstructStream();
		$sDepositRequestStream = $DepositRequest->GetConstructedStream();
		
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositRequestStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositRequestStream, $sResp);
		
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolAddRelationResponse($sResp);
			$Res->ExtractDataElement();
			
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if (isset($Res->dataElement['mti']) && $Res->dataElement['mti'] == '2610')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$bSuccess = true;
				}
			}
		}		
		return $bSuccess;	
	}

	public function getRCMessage($mti,$rc){
		if($mti=="2610"){
			switch($rc){
				case "0000": 
					return "Success";
					break;
				case "0005": 
					return "Error other";
					break;
				case "0015":
					return "Unknown Account";
					break;
				case "0030":
					return "Invalid Messages";
					break;
				case "0032":
					return "Forbiden client Access";
					break;
				case "0068": 
					return "Error Timeout";
					break;
				case "0066":
					return "Account Already Exists";
					break;
				default:
					return "Undefined Error";
					break;
			}
		}if($mti=="2210"){
			switch($rc){
				case "0000": 
					return "Success";
					break;
				case "0005": 
					return "Error other";
					break;
				case "0068": 
					return "Error Timeout";
					break;
				case "0015":
					return "Unknown Account Number";
					break;
				case "0016":
					return "Inactive Account";
					break;
				case "0017":
					return "Locked Account";
					break;
				case "0046":
					return "Insufficient Deposit";
					break;
				case "0099":
					return "No Transaction in given time";
					break;
				default:
					return "Undefined Error";
					break;
			}
		}else{
			return "UNKNOWN ERROR NO MTI";
		}
	}

	/**
	 * Prosedur untuk mengecek informasi account number
	 * param : 
	 * 		$flag : diisi dengan
	 *				"0" = search menggunakan account number (exact match)
	 *				"1" = search menggunakan account holder name (pattern match)
	 * 		$filter : diisi dengan
	 *				"0" = inactive
	 *				"1" = active
	 *				"2" = blocked
	 *				"3" = deleted
	 *				"4" = all
	 * 		$page : diisi nomor page start dari data 1
	 * return type : boolean / Array
	 *		false : permintaan gagal
	 *		Array : Daftar account number 
	 *			array daftar account number berisi array dengan atribut (key) : 
	 *				"Status"
	 *				"Account Number"
	 */
	public function QueryAccount(&$rc, $flag,$filter,$page=1) {
		$data = false;
		$json = new Services_JSON();
		
		$DepositListAccount = new DepositProtocolQueryAccountRequest();
		$DepositListAccount->SetComponentTmp('dt', 	strftime("%Y%m%d%H%M%S",time()));
		$DepositListAccount->SetComponentTmp('priv', $flag);
		$DepositListAccount->SetComponentTmp('priv2', $page);
		$DepositListAccount->SetComponentTmp('priv3', $filter);

		$DepositListAccount->ConstructStream();
		$sDepositListAccountStream = $DepositListAccount->GetConstructedStream();
		 echo "sDepositListAccountStream :<br>".$sDepositListAccountStream."<br>\n";
		$abt=$DepositListAccount->GenerateBitmap();
		//var_dump($abt);
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sDepositListAccountStream]\n", 3, LOG_FILENAME);
		
		$bOK = GetRemoteResponse($this->ServerAddress, $this->ServerPort, $this->ServerTimeOut, $sDepositListAccountStream,$sResp);
		echo "sResp :<br>".$sResp."<br>\n";
		if($bOK == "0")
		{
			$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
			$Res = new DepositProtocolQueryAccountResponse($sResp);
			$Res->ExtractDataElement();
			// echo "<pre>";
			print_r($Res->dataElement);
			// echo "</pre>";
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			{
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Response Header [".print_r($Res->dataElement, true)."]\n", 3, LOG_FILENAME);
			}
			
			if (isset($Res->dataElement['mti']) && $Res->dataElement['mti'] == '2610')  // valid payment response
			{
				$rc = intval($Res->dataElement['rc']);
				if ($Res->dataElement['rc'] == '0000') // successful
				{	
					$tdata = $json->decode(base64_decode($Res->dataElement['data']));
					$total = count($tdata->Status);
					for ($i=0; $i<$total; $i++) {
						foreach ($tdata as $key => $value) {
							$data[$i][$key] = $value[$i];
						}
					}					
				}
			}
		}		
		return $data;	
	}
}

// $Dep = new ServiceDeposit("127.0.0.1", "23590", 60);
// $account = "1000000000000000001";

// echo "Account number is ".$account."<br>";

// $balance = $Dep->BalanceCheck($rc, $account);
// echo "RC :$rc<br>";
// echo "Current balance is ".$balance."<br>";

// $amount = 10000;
// $Dep->Debit($account, $amount);
// $newbalance = $Dep->BalanceCheck($account);
// echo "Dilakukan debit 10.000 jadi ".$newbalance."<br>";

// $Dep->Credit($account, $amount);
// $newbalance = $Dep->BalanceCheck($account);
// echo "Dilakukan credit 10.000 jadi ".$newbalance."<br>";

// echo "Melihat list transaksi <br>";
 // $list = $Dep->ListTransaction($rc,$account, "20101101", "20101104");
 // echo "<pre>";
 // print_r($list);
 // echo "</pre>";
 // echo "RC:$rc<br>";

// echo "Melakukan permintaan maintenance<br>";
// if ($Dep->Maintenance($account)) {
	// echo "Maintenance berhasil<br>";
// } else {
	// echo "Maintenance gagal<br>";
// }

// $target_account = "1849890629672405422";
// echo "Account target is $target_account with balance is ".$Dep->BalanceCheck($target_account)."<br>";
// echo "Melakukan transfer sebesar $amount ke akun $target_account<br>";
// if ($Dep->Transfer($account, $amount, "Test transfer", $target_account)) {
	// echo "Transfer berhasil<br>";
// } else {
	// echo "Transfer gagal<br>";
// }
// echo "Account target $target_account balance is now ".$Dep->BalanceCheck($target_account)."<br>";

// echo "Menampilkan seluruh akun<br>";
// $acc_list = $Dep->ListAccount($rc, "4");
// echo "RC : $rc";
// echo "<pre>";
// print_r($acc_list);
// echo "</pre>";

// echo "Menampilkan relasi kepada akun $account<br>";
// $acc_rel = $Dep->ListRelation($rc, $account, 4, 0);
// echo "RC : $rc";
// echo "<pre>";
// print_r($acc_rel);
// echo "</pre>";
?>