<?php  

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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
		$nop = mysqli_real_escape_string($this->dbSpec->getDBLink() ,trim($nop));
		
		$query = "SELECT A.*, B.* FROM (
                        SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP='$nop'
                        UNION ALL
                        SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP='$nop'
                        ) A LEFT JOIN
                        cppmod_pbb_sppt_current B ON A.CPM_NOP=B.NOP";
		if ($this->dbSpec->sqlQueryRow($query, $res)) {
			return $res;
		}
	}
	
	public function getGateWayPBBSPPT($nop,$tahun) {
		$LDBLink = mysqli_connect($this->C_HOST_PORT,$this->C_USER,$this->C_PWD,$this->C_DB, $this->C_PORT) or die(mysqli_error($LDBLink));
                // mysqli_select_db($LDBLink, $this->C_DB);
                
		$nop   = mysqli_real_escape_string($LDBLink ,trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink ,trim($tahun));
                
		$query = "SELECT SPPT_TAHUN_PAJAK, SPPT_TANGGAL_JATUH_TEMPO, SPPT_PBB_HARUS_DIBAYAR, PAYMENT_FLAG, PAYMENT_PAID  FROM PBB_SPPT WHERE NOP='$nop' ";
		if($tahun){
			$query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
		}
		 //echo $query;
		$result = mysqli_query($LDBLink ,$query);
                
                if (!$result) {
                    return false;
                }
                while ($row = mysqli_fetch_assoc($result)) {
                    return $row;
                }
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
$svcPengembalian = new SvcPengembalianDataKePendataan($dbSpec);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

if ($q=="") exit(1);

$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$arConfig 		= $User->GetModuleConfig($moduleIds);
$appConfig 		= $User->GetAppConfig($area);
// print_r($appConfig); exit;
$tahun	= $appConfig['tahun_tagihan'];


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

//$arrWhere = array();
//array_push($arrWhere,"SPPT_TAHUN_PAJAK = '{$tahun}'");
//
//if ($nop!="") array_push($arrWhere,"nop LIKE '%{$nop}%'");
//           
//$where = implode (" AND ",$arrWhere);

if(stillInSession($DBLink,$json,$sdata)){	
	$res = $svcPengembalian->getDataSPPT($nop);

    // die(var_dump($res));
        if(!empty($res)){
	    $resGateway = $svcPengembalian->getGateWayPBBSPPT($nop, $tahun);
        $button = $resGateway['PAYMENT_FLAG']=='1' ? 'LUNAS': '<input type="button" name="btn-prosespendataan" id="btn-prosespendataan" value="Kembalikan ke Pendataan" onclick="prosespendataan(\''.$res[0]['CPM_NOP'].'\',\''.$tahun.'\',\'0\')">';
        echo '<script type="text/javascript" src="inc/PBB/jquery-1.3.2.min.js"></script>
                <script type="text/javascript" src="view/PBB/updatePBB/pembayaran.js"></script>
            <div id="frame-tbl-monitoring" class="tbl-monitoring">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th class="tdheader" width=9>Aksi</th>
                        <th class="tdheader" width=9>NOP</th>
                        <th class="tdheader">Nama WP</th>
                        <th class="tdheader">Alamat WP</th>
                        <th class="tdheader">Kelurahan WP</th>
                        <th class="tdheader">Alamat OP</th>
                        <th class="tdheader">Kecamatan OP</th>
                        <th class="tdheader">Kelurahan OP</th>
                        <th class="tdheader" width=9>RT OP</th>
                        <th class="tdheader" width=9>RW OP</th>
                        <th class="tdheader" width=9>Luas Bumi</th>
                        <th class="tdheader" width=9>Luas Bangunan</th>
                        <th class="tdheader">Tot NJOP Bumi</th>
                        <th class="tdheader">Tot NJOP Bangunan</th>
                        <th class="tdheader" width=9>Thn Pajak</th>
                        <th class="tdheader">Tgl Jth Tempo</th>
                        <th class="tdheader">Tagihan</th>
                        <th class="tdheader">Tanggal</th>
                    </tr>
                    <tr>
                        <td align="center">&nbsp;'.$button.'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_NOP'].'</td>
                        <td align="">&nbsp;'.$res[0]['CPM_WP_NAMA'].'</td>
                        <td align="">&nbsp;'.$res[0]['CPM_WP_ALAMAT'].'</td>
                        <td align="">&nbsp;'.$res[0]['WP_KELURAHAN'].'</td>
                        <td align="">&nbsp;'.$res[0]['CPM_OP_ALAMAT'].'</td>
                        <td align="center">&nbsp;'.$res[0]['OP_KECAMATAN'].'</td>
                        <td align="center">&nbsp;'.$res[0]['OP_KELURAHAN'].'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_OP_RT'].'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_OP_RW'].'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_OP_LUAS_TANAH'].'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_OP_LUAS_BANGUNAN'].'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_NJOP_TANAH'].'</td>
                        <td align="center">&nbsp;'.$res[0]['CPM_NJOP_BANGUNAN'].'</td>
                        <td align="center">&nbsp;'.$resGateway['SPPT_TAHUN_PAJAK'].'</td>
                        <td align="center">&nbsp;'.$resGateway['SPPT_TANGGAL_JATUH_TEMPO'].'</td>
                        <td align="right">&nbsp;'.$resGateway['SPPT_PBB_HARUS_DIBAYAR'].'</td>
                        <td align="right">&nbsp;'.$resGateway['PAYMENT_PAID'].'</td>
                    </tr>
                </table></div>';
        }else{
            echo  "Data tidak ditemukan !\n";
    }
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
?>