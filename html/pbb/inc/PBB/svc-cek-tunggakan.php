<?
set_time_limit(800);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

if(!isset($_POST['action'])) exit('action is not valid!');
$a = isset($_POST['a'])? $_POST['a'] : '';
$m = isset($_POST['m'])? $_POST['m'] : '';
$tahun_cek_tagihan = isset($_POST['tahun'])? $_POST['tahun'] : '';

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$appConfig = $User->GetAppConfig($a);

$GWDBLink = mysqli_connect($appConfig['GW_DBHOST'] . ":".$appConfig['GW_DBPORT'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME']);
if (!$GWDBLink) {
	echo mysqli_error($GWDBLink); 
}
//mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) or die(mysqli_error($DBLink));

$data->tahun = $tahun_cek_tagihan;
$data->result = '01';
$query = "INSERT IGNORE INTO PBB_SPPT_TUNGGAKAN (NOP, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR) 
		(SELECT A.NOP, A.SPPT_TAHUN_PAJAK, A.SPPT_PBB_HARUS_DIBAYAR FROM PBB_SPPT A
		WHERE A.PAYMENT_FLAG = '0' AND A.SPPT_TAHUN_PAJAK = '{$tahun_cek_tagihan}')";
		
/*$query = "INSERT IGNORE INTO PBB_SPPT_TUNGGAKAN (NOP, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR) 
		(SELECT A.NOP, A.SPPT_TAHUN_PAJAK, A.SPPT_PBB_HARUS_DIBAYAR FROM PBB_SPPT A
		WHERE A.PAYMENT_FLAG = '0')";
*/
$res = mysqli_query($GWDBLink, $query);

if($res){
	$queryCount = "SELECT count(*) as total FROM PBB_SPPT WHERE PAYMENT_FLAG = '0' AND SPPT_TAHUN_PAJAK = '{$tahun_cek_tagihan}'";
	$res = mysqli_query($GWDBLink, $queryCount);
	$d = mysql_fetch_object($res);
	$data->total = number_format($d->total, 0);
	$data->result = '00';
}
echo $json->encode((array) $data);
?>
