<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_pembayaran_kolektif', '', dirname(__FILE__))) . '/';
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
require_once("dbMonitoring.php");

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
$tagihan = @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$kode = @isset($_REQUEST['kode']) ? $_REQUEST['kode'] : "";

$kelurahan ="";
if ($q=="") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$host = $_REQUEST['GW_DBHOST'];
$port = $_REQUEST['GW_DBPORT'];
$user = $_REQUEST['GW_DBUSER'];
$pass = $_REQUEST['GW_DBPWD'];
$dbname = $_REQUEST['GW_DBNAME']; 

$jsonTitle = "{\"data\" : [
{\"field\":\"nop\", \"length\" : \"110px\", \"title\" : \"NOP\", \"align\":\"center\"},
{\"field\":\"wp_nama\", \"length\" : \"280px\", \"title\" : \"Nama WP\"},
{\"field\":\"wp_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat WP\"},
{\"field\":\"wp_kelurahan\", \"length\" : \"120px\", \"title\" : \"Kelurahan WP\"},
{\"field\":\"wp_kecamatan\", \"length\" : \"120px\", \"title\" : \"Kecamatan WP\"},
{\"field\":\"wp_handphone\", \"length\" : \"120px\", \"title\" : \"Nomor HP\", \"align\":\"center\"},
{\"field\":\"op_alamat\", \"length\" : \"420px\", \"title\" : \"Alamat OP\"},
{\"field\":\"op_kecamatan\", \"length\" : \"160px\", \"title\" : \"Kecamatan OP\", \"align\":\"center\"},
{\"field\":\"op_kelurahan\", \"length\" : \"160px\", \"title\" : \"Kelurahan OP\", \"align\":\"center\"},
{\"field\":\"sppt_tahun_pajak\", \"length\" : \"80px\", \"title\" : \"Thn Pajak\", \"align\":\"center\"},
{\"field\":\"sppt_tanggal_jatuh_tempo\", \"length\" : \"80px\", \"title\" : \"Jth Tempo\", \"align\":\"center\"},
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Tagihan\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\"}, 
{\"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
]}";

$arrTempo = array();
if ($tempo1!="") array_push($arrTempo,"payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2!="") array_push($arrTempo,"payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode (" AND ",$arrTempo);

$arrWhere = array();
if ($kode!="") array_push($arrWhere,"CPM_CG_PAYMENT_CODE =".$kode);
if ($kecamatan !="") {
	if ($kelurahan !="") array_push($arrWhere,"nop like '{$kelurahan}%'");
	else array_push($arrWhere,"nop like '{$kecamatan}%'");
}

if ($nop!="") array_push($arrWhere,"nop='{$nop}'");
if ($thn!="") array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
if ($na!="") array_push($arrWhere,"wp_nama like '{$na}%'");
// if ($status!="") {
// 	if ($status==1){
//        array_push($arrWhere,"payment_flag = 1");            
//     }else{
//        array_push($arrWhere,"(payment_flag != 1 OR payment_flag IS NULL)");            
//     }
// }
if ($tempo1!="") array_push($arrWhere,"({$tempo})");

if($tagihan != 0){
    switch ($tagihan){
        case 1 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR < 5000000) "); break;
        case 2 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 5000000 AND SPPT_PBB_HARUS_DIBAYAR < 10000000) "); break;
        case 3 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 10000000 AND SPPT_PBB_HARUS_DIBAYAR < 20000000) "); break;
        case 4 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 20000000 AND SPPT_PBB_HARUS_DIBAYAR < 30000000) "); break;
        case 5 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 30000000 AND SPPT_PBB_HARUS_DIBAYAR < 40000000) "); break;
        case 6 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 40000000 AND SPPT_PBB_HARUS_DIBAYAR < 50000000) "); break;
        case 7 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 50000000 AND SPPT_PBB_HARUS_DIBAYAR < 100000000) "); break;
        case 8 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100000000) "); break;
        case 9 : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR > 100000000) "); break;
    }
}

$where = implode (" AND ",$arrWhere);

    // print_r($where);exit;
if(stillInSession($DBLink,$json,$sdata)){			
	$monPBB = new dbMonitoring ($host,$port,$user,$pass,$dbname);
    $monPBB->setConnectToMysql();
	$monPBB->setWhere($where);    
	$monPBB->setTable(" cppmod_tax_kecamatan M, cppmod_tax_kelurahan K, CPPMOD_COLLECTIVE_GROUP A LEFT JOIN CPPMOD_COLLECTIVE_GROUP_STATUS S ON S.ID = A.CPM_CG_STATUS  ");
        echo $monPBB->getCountData();
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
?>
