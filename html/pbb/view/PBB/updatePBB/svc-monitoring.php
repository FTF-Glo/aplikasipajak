<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'updatePBB', '', dirname(__FILE__))) . '/';
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
require_once("config-monitoring.php");

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

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na = @isset($_REQUEST['na']) ? str_replace("%20"," ",$_REQUEST['na']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$kelurahan ="";
if ($q=="") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$host = DBHOST;
$port = DBPORT;
$user = DBUSER;
$pass = DBPWD;
$dbname = DBNAME; 

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_kelurahan\", \"length\" : \"180px\", \"title\" : \"Kelurahan WP\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"op_kelurahan\", \"length\" : \"180px\", \"title\" : \"Kelurahan OP\", \"align\":\"center\"},
{\"field\":\"op_rt\", \"length\" : \"160px\", \"title\" : \"RT OP\", \"align\":\"center\"},
{\"field\":\"op_rw\", \"length\" : \"160px\", \"title\" : \"RW OP\", \"align\":\"center\"},
{\"field\":\"op_luas_bumi\", \"length\" : \"140px\", \"title\" : \"Luas Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_luas_bangunan\", \"length\" : \"140px\", \"title\" : \"Luas Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_njop_bumi\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bumi\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"op_njop_bangunan\", \"length\" : \"140px\", \"title\" : \"Tot NJOP Bangunan\", \"align\":\"center\",\"format\":\"number\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Tgl Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Tagihan\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\", \"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
{\"field\":\"payment_paid\", \"length\" : \"180px\", \"title\" : \"Tanggal\", \"align\":\"right\",\"format\":\"date\"}
]}";

$arrTempo = array();
if ($tempo1!="") array_push($arrTempo,"payment_paid>='{$tempo1}'");
if ($tempo2!="") array_push($arrTempo,"payment_paid<='{$tempo2}'");
$tempo = implode (" AND ",$arrTempo);

$arrWhere = array();
if ($kecamatan !="") {
	if ($kelurahan !="") array_push($arrWhere,"nop like '{$kelurahan}%'");
	else array_push($arrWhere,"nop like '{$kecamatan}%'");
}

if ($nop!="") array_push($arrWhere,"nop LIKE '{$nop}%'");
if ($thn!="") array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
if ($na!="") array_push($arrWhere,"wp_nama like '%{$na}%'");
if ($status!="") {

        if ($status==1){
            array_push($arrWhere,"payment_flag = 1");            
        }else{
            array_push($arrWhere,"(payment_flag = 0 OR payment_flag = 2)");            
        }
}
if ($tempo1!="") array_push($arrWhere,"({$tempo})");
$where = implode (" AND ",$arrWhere);

//echo $where;

if(stillInSession($DBLink,$json,$sdata)){			
	$monPBB = new dbMonitoring ($host,$port,$user,$pass,$dbname);
	$monPBB->setConnectToMysql();
	$monPBB->setRowPerpage(30);
	$monPBB->setPage($p);
	$monPBB->setTable("PBB_SPPT");
	$monPBB->setWhere($where);
	$monPBB->query("select nop, wp_nama, wp_alamat, wp_kelurahan, op_alamat, op_kecamatan, op_kelurahan, op_rt, op_rw,
					op_luas_bumi, op_luas_bangunan,op_njop_bumi,op_njop_bangunan,sppt_tahun_pajak, sppt_tanggal_jatuh_tempo , sppt_pbb_harus_dibayar, payment_flag, payment_paid
					");
	$monPBB->setTitleHeader($jsonTitle);
	if ($export=="") $monPBB->showHTML();
	else $monPBB->exportToXls ();
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

?>