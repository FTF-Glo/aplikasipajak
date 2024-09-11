<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'rekonsiliasi', '', dirname(__FILE__))) . '/';
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

$setting = new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

// print_r($_REQUEST);

$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p 		= @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$thn 	= @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$statusPembayaran = @isset($_REQUEST['stp']) ? $_REQUEST['stp'] : "";
// echo $p." ".$status;
if ($q=="") exit(1);
$q = base64_decode($q);

$j 			= $json->decode($q);
$uid 		= $j->uid;
$area 		= $j->a;
$moduleIds 	= $j->m;

$host 	= $_REQUEST['GW_DBHOST'];
$port 	= $_REQUEST['GW_DBPORT'];
$user 	= $_REQUEST['GW_DBUSER'];
$pass 	= $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_kelurahan\", \"length\" : \"180px\", \"title\" : \"".$_REQUEST['LBL_KEL']." WP\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"op_kelurahan\", \"length\" : \"180px\", \"title\" : \"".$_REQUEST['LBL_KEL']." OP\", \"align\":\"center\"},
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

$arrWhere = array();

if ($thn!="") array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
if ($statusPembayaran!="") {
        if ($statusPembayaran==1){
            array_push($arrWhere,"payment_flag = 1");            
        }else if($statusPembayaran==2){
            array_push($arrWhere,"(payment_flag != 1 OR payment_flag IS NULL)");            
        }
}	
$where = implode (" AND ",$arrWhere);

// echo $where;

if(stillInSession($DBLink,$json,$sdata)){			
	$monPBB = new dbMonitoring ($host,$port,$user,$pass,$dbname);
	$monPBB->setStatus($status);
	$monPBB->setConnectToMysql();
	$monPBB->setRowPerpage(30);
	$monPBB->setPage($p);
	$monPBB->setTable("PBB_SPPT");
	$monPBB->setWhere($where);
	$monPBB->query("select nop, wp_nama, wp_alamat, wp_kelurahan, op_alamat, op_kecamatan, op_kelurahan, op_rt, op_rw,
					op_luas_bumi, op_luas_bangunan,op_njop_bumi,op_njop_bangunan,sppt_tahun_pajak, sppt_tanggal_jatuh_tempo , sppt_pbb_harus_dibayar, IFNULL(payment_flag,0) as payment_flag, payment_paid
					");
	$monPBB->setTitleHeader($jsonTitle);
	if ($export=="") echo $monPBB->showHTML();
	else $monPBB->exportToXls ();
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

?>