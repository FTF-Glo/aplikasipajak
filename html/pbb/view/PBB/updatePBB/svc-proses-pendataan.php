<?php  
class SvcPengembalianDataKePendataan {
	private $dbSpec = null;
	
        public $C_HOST_PORT;
        public $C_USER;
        public $C_PWD;
        public $C_DB;
		public $C_PORT;
                                
	public function __construct($dbSpec) {
		$this->dbSpec = $dbSpec;
	}
	
	//DATABASE FUNCTION
	public function getDataSPPT($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "SELECT A.*, B.* FROM (
                        SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'
                        UNION ALL
                        SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP='$nop'
                        ) A LEFT JOIN
                        cppmod_pbb_sppt_current B ON A.CPM_NOP=B.NOP";
		// echo $query;exit();
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
    public function deleteSPPTCurrent($nop) {
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
		
		$query = "DELETE FROM cppmod_pbb_sppt_current WHERE NOP = '$nop' ";
		// echo $query;exit();
		return $this->dbSpec->sqlQuery($query);
	}
        
	public function delGateWayPBBSPPT($nop,$tahun) {
		$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD, $this->C_DB, $this->C_PORT) or die(mysqli_error($this->dbSpec->getDBLink()));
                //mysql_select_db($this->C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "DELETE FROM PBB_SPPT WHERE NOP='$nop' ";
		if($tahun){
			$query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
		}
		//echo $query;exit;		
		$result = mysqli_query($LDBLink, $query);
                
                if (!$result) {
                    return false;
                }
                return true;
                
        }
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'updatePBB', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/PBB/dbMonitoring.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");
require_once($sRootPath . "inc/payment/uuid.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSppt = new DbSppt($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$svcPengembalian = new SvcPengembalianDataKePendataan($dbSpec);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$tahun = @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";

$host = $_REQUEST['GW_DBHOST'];
$port = $_REQUEST['GW_DBPORT'];
$user = $_REQUEST['GW_DBUSER'];
$pass = $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 

$svcPengembalian->C_HOST_PORT = $host;
$svcPengembalian->C_USER = $user;
$svcPengembalian->C_PWD = $pass;
$svcPengembalian->C_DB = $dbname;
$svcPengembalian->C_PORT = $port;

// echo $where;exit;

// $dokumen = $dbFinalSppt->get_where(array("CPM_NOP" => $nop));
// $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
// $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];
// $thn_penetapan = $dokumen[0]['CPM_SPPT_THN_PENETAPAN'];
// $bOK = false;

// //copy dari final ke proses
// $bOK = $dbFinalSppt->doResurect($iddoc, $vers);
// die();

if(stillInSession($DBLink,$json,$sdata)){
	$res1 = $svcPengembalian->delGateWayPBBSPPT($nop, $tahun);
        if($res1){
            $res2 = $svcPengembalian->deleteSPPTCurrent($nop);
            if($res2){
                $res3 = spopEdit($nop);
                if ($res3){
                    $res4 = spopDelete($nop);
                    if($res4) echo "1";
                    else echo "04";
                }else echo "03";
            }else echo "02";
        }else echo "01";
        
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

function spopEdit($nop) {
    
    global $dbFinalSppt, $dbSpptTran, $dbSppt, $dbSpptExt, $uname;
    
    $isFinal = $isSusulan = false;
    if ($dbFinalSppt->isNopExist($nop)) {
        $isFinal = true;
    }else if ($dbFinalSppt->isNopExistInSusulan($nop)) {
        $isSusulan = true;
    }
    if($isFinal){
        //cari tahu iddoc dan vers
        $dokumen = $dbFinalSppt->get_where(array("CPM_NOP" => $nop));
        $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
        $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];
        $thn_penetapan = $dokumen[0]['CPM_SPPT_THN_PENETAPAN'];
        $bOK = false;

        //copy dari final ke proses
        $bOK = $dbFinalSppt->doResurect($iddoc, $vers);

        $idt = c_uuid();
        $tranValue['CPM_TRAN_REFNUM'] = c_uuid();
        $tranValue['CPM_TRAN_STATUS'] = "0";
        $tranValue['CPM_TRAN_SPPT_DOC_ID'] = $iddoc;
        $tranValue['CPM_SPPT_DOC_VERSION'] = $vers;
        $tranValue['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");
        $tranValue['CPM_TRAN_OPR_KONSOL'] = $uname;
        $bOK = $dbSpptTran->add($idt, $tranValue);
        // echo "string";exit();

        if (!$bOK) {
            //failed add transaction. Maybe ID transaction already exist. Second try
            $idt = c_uuid();
            $bOK = $dbSpptTran->add($idt, $tranValue);
            if (!$bOK) {
                //something failed. Delete Sppt document
                $dbSppt->del($iddoc);
                $dbSpptExt->del($iddoc);
                $errMsg = "Data SPOP gagal dipersiapkan. Mohon ulangi.";
            }
        }
            
        return $bOK;
        
    }else if($isSusulan){
        //cari tahu iddoc dan vers
        $dokumen = $dbFinalSppt->get_susulan(array("CPM_NOP" => $nop));
        $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
        $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];
        $thn_penetapan = $dokumen[0]['CPM_SPPT_THN_PENETAPAN'];
        $bOK = false;

        //copy dari final ke proses
        $bOK = $dbFinalSppt->doResurectSusulan($iddoc, $vers);
        $idt = c_uuid();
        $tranValue['CPM_TRAN_REFNUM'] = c_uuid();
        $tranValue['CPM_TRAN_STATUS'] = "0";
        $tranValue['CPM_TRAN_SPPT_DOC_ID'] = $iddoc;
        $tranValue['CPM_SPPT_DOC_VERSION'] = $vers;
        $tranValue['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");
        $tranValue['CPM_TRAN_OPR_KONSOL'] = $uname;
        $bOK = $dbSpptTran->add($idt, $tranValue);
        if (!$bOK) {
            //failed add transaction. Maybe ID transaction already exist. Second try
            $idt = c_uuid();
            $bOK = $dbSpptTran->add($idt, $tranValue);
            if (!$bOK) {
                //something failed. Delete Sppt document
                $dbSppt->del($iddoc);
                $dbSpptExt->del($iddoc);
                $errMsg = "Data SPOP gagal dipersiapkan. Mohon ulangi.";
            }
        }
        
        return $bOK;
    }
    
    return false;
}

function spopDelete($nop) {
    global $dbFinalSppt, $dbSpptTran, $dbSppt, $dbSpptExt;
    
    $isFinal = $isSusulan = false;
    if ($dbFinalSppt->isNopExist($nop)) {
        $isFinal = true;
    }else if ($dbFinalSppt->isNopExistInSusulan($nop)) {
        $isSusulan = true;
    }
    
    if($isFinal){
        //cari tahu iddoc dan vers
        $dokumen = $dbFinalSppt->get_where(array("CPM_NOP" => $nop));
        $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
        $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];
        // echo $iddoc;
        // echo "string";
        // exit();
        //hapus dari final
        $bOK = $dbFinalSppt->doPurge($iddoc, $vers);
        return $bOK;
    }else if($isSusulan){
        //cari tahu iddoc dan vers
        $dokumen = $dbFinalSppt->get_susulan(array("CPM_NOP" => $nop));
        $iddoc = $dokumen[0]['CPM_SPPT_DOC_ID'];
        $vers = $dokumen[0]['CPM_SPPT_DOC_VERSION'];

        //hapus dari final
        $bOK = $dbFinalSppt->doPurgeSusulan($iddoc, $vers);
        return $bOK;
    }
    return false;
}
?>