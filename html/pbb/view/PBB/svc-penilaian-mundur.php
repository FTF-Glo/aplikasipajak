<?php  
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
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

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$a = 'aPBB';
$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
// print_r($appConfig);
$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na = @isset($_REQUEST['na']) ? str_replace("%20"," ",$_REQUEST['na']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$kelurahan ="";
if ($q=="") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$host = $appConfig['GW_DBHOST'];
$port = $appConfig['GW_DBPORT'];
$user = $appConfig['GW_DBUSER'];
$pass = $appConfig['GW_DBPWD'];
$dbname = $appConfig['ADMIN_SW_DBNAME']; 
$uname	= $uid;

$jsonTitle = "{\"data\" : [

{\"field\":\"cpm_op_luas_tanah\", \"length\" : \"110px\", \"title\" : \"Luas Bumi\", \"align\":\"center\"},
{\"field\":\"cpm_nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"cpm_wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"cpm_wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"cpm_wp_kelurahan\", \"length\" : \"120px\", \"title\" : \"Kelurahan WP\"},
{\"field\":\"cpm_op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"cpm_op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"cpm_op_kelurahan\", \"length\" : \"160px\", \"title\" : \"Kelurahan OP\", \"align\":\"center\"},
{\"field\":\"cpm_op_rt\", \"length\" : \"160px\", \"title\" : \"RT OP\", \"align\":\"center\"},
{\"field\":\"cpm_op_rw\", \"length\" : \"160px\", \"title\" : \"RW OP\", \"align\":\"center\"},
{\"field\":\"cpm_op_luas_bumi\", \"length\" : \"140px\", \"title\" : \"Luas Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"CPM_OP_LUAS_BANGUNAN\", \"length\" : \"140px\", \"title\" : \"Luas Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"cpm_njop_tanah\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"cpm_njop_bangunan\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bangunan\", \"align\":\"center\",\"format\":\"number\"}
]}";

$arrWhere = array();
if ($nop!="") array_push($arrWhere,"cpm_nop ='{$nop}'");

           
$where = implode (" AND ",$arrWhere);
// echo $where;exit;
if(stillInSession($DBLink,$json,$sdata)){	
	$monPBB = new dbMonitoring ($host,$port,$user,$pass,$dbname);
	$monPBB->setStatus($status);
	$monPBB->setConnectToMysql();
	$monPBB->setRowPerpage(30);
	$monPBB->setPage($p);
	$monPBB->setTable("cppmod_pbb_sppt_mundur");
	$monPBB->setWhere($where);
	$monPBB->query("select cpm_nop, cpm_wp_nama, cpm_wp_alamat, cpm_wp_kelurahan, cpm_op_alamat, cpm_op_kecamatan, cpm_op_kelurahan, cpm_op_rt, cpm_op_rw,
					cpm_op_luas_tanah, CPM_OP_LUAS_BANGUNAN,cpm_njop_tanah,cpm_njop_bangunan
					");
	$monPBB->setTitleHeader($jsonTitle);
	$monPBB->showHTMLMundur($uid);
	// echo  $monPBB->getCountData(); 
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
?>