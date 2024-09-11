<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
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
//require_once($sRootPath . "inc/PBB/dbMonitoring.php");
require_once("dbMonitoringDph.php");

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

$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p 		= @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$jml 	= @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$thn 	= @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$thn2	= @isset($_REQUEST['th2']) ? $_REQUEST['th2'] : 1;
$nop 	= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na 	= @isset($_REQUEST['na']) ? str_replace("%20"," ",$_REQUEST['na']) : "";
$status = @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
$tempo1 = @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$tempo2 = @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";

$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$rw 		= @isset($_REQUEST['rw']) ? $_REQUEST['rw'] : "";
$export 	= @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$tagihan 	= @isset($_REQUEST['tagihan']) ? $_REQUEST['tagihan'] : "0";
$buku 		= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";
$bank 		= @isset($_REQUEST['bank']) ? $_REQUEST['bank'] : "0";
$alamat		= @isset($_REQUEST['almt']) ? $_REQUEST['almt'] : "";
$barcode = @isset($_REQUEST['barcode']) ? $_REQUEST['barcode'] : "0";

if ($q=="") exit(1);
$q = base64_decode($q);

$j 			= $json->decode($q);
$uid 		= $j->uid;
$area 		= $j->a;
$moduleIds 	= $j->m;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($area);

$host 	= $appConfig['GW_DBHOST'];
$port 	= $appConfig['GW_DBPORT'];
$user 	= $appConfig['GW_DBUSER'];
$pass 	= $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME']; 

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
{\"field\":\"sppt_pbb_harus_dibayar\", \"length\" : \"80px\", \"title\" : \"Pokok\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_denda\", \"length\" : \"80px\", \"title\" : \"Denda\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"pbb_total_bayar\", \"length\" : \"80px\", \"title\" : \"Total\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"selisih\", \"length\" : \"80px\", \"title\" : \"Selisih\", \"align\":\"right\",\"format\":\"number\"},
{\"field\":\"payment_flag\", \"length\" : \"80px\", \"title\" : \"Status\", \"align\":\"center\",\"format\":\"optional\",\"optional\":[\"Terutang\",\"Lunas\"]},
{\"field\":\"payment_paid\", \"length\" : \"180px\", \"title\" : \"Tanggal\", \"align\":\"right\",\"format\":\"date\"},
{\"field\":\"CDC_B_NAME\", \"length\" : \"200px\", \"title\" : \"Bank\", \"align\":\"center\"}
]}";

$arrTempo = array();
if ($tempo1!="") array_push($arrTempo,"A.payment_paid>='{$tempo1} 00:00:00'");
if ($tempo2!="") array_push($arrTempo,"A.payment_paid<='{$tempo2} 23:59:59'");
$tempo = implode (" AND ",$arrTempo);

$arrWhere = array();
	// print_r($kecamatan );
	// print_r("---------------");
	// print_r($kelurahan);
	// print_r("---------------");
	// print_r($nop);
	// exit;
if ($kecamatan !="" && $kecamatan !='undefined') {
    if ($kelurahan !=""){  
        array_push($arrWhere,"A.OP_KECAMATAN_KODE like '{$kecamatan}%'");
        array_push($arrWhere,"A.OP_KELURAHAN_KODE like '{$kelurahan}%'");
    }
    else {
        array_push($arrWhere,"A.OP_KECAMATAN_KODE like '{$kecamatan}%'");
    } 
}else  {
     if ($kelurahan !=""){  
         array_push($arrWhere,"A.OP_KELURAHAN_KODE like '{$kelurahan}%'");
    }
}
if ($rw !="") array_push($arrWhere,"A.OP_RW = '{$rw}'");
if ($nop!="") array_push($arrWhere,"A.nop LIKE '{$nop}%'");
if ($thn!="") array_push($arrWhere,"A.sppt_tahun_pajak between  '{$thn}' and '{$thn2}'  ");
if ($na!="") array_push($arrWhere,"A.wp_nama like '%{$na}%'");
if ($alamat!="") array_push($arrWhere,"A.OP_ALAMAT like '%{$alamat}%'");
if ($status!="") {
        array_push($arrWhere,"(A.payment_flag != 1 OR A.payment_flag IS NULL)");            
}
if ($tempo1!="") array_push($arrWhere,"({$tempo})");

if($buku != 0){
	switch ($buku){
		case 1 		: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "); break;
		case 12 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "); break;
		case 123 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
		case 1234 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
		case 12345 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
		case 2 		: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "); break;
		case 23 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
		case 234 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
		case 2345 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
		case 3 		: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
		case 34 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
		case 345 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
		case 4 		: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
		case 45 	: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
		case 5 		: array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
	}
}
if ($bank!=0) array_push($arrWhere,"A.PAYMENT_BANK_CODE IN ('".str_replace(",", "','", $bank)."') ");     
$where = implode (" AND ",$arrWhere);
// echo $where;
	
if(stillInSession($DBLink,$json,$sdata)){			
	// $monPBB1 = new dbMonitoring ($host,$port,$user,$pass,$dbname);
	// $monPBB1->setConnectToMysql();
	// $monPBB1->setTable("PBB_SPPT A");
	// $monPBB1->setWhere($where);

	$monPBB1 = new dbMonitoringDph ($host,$port,$user,$pass,$dbname);
    $monPBB1->setConnectToMysql(); 
    $monPBB1->setRowPerpage(30);
    $monPBB1->setPage($p);
    $monPBB1->setStatus($status);
        $sql_table = "PBB_SPPT A";
        $sql_select = "SELECT A.NOP, A.SPPT_TAHUN_PAJAK AS TAHUN , A.WP_NAMA,
        OP_KELURAHAN AS DESA_OP , OP_KECAMATAN AS KECAMATAN_OP , 
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0) AS PBB_TERHUTANG,
        IFNULL(A.PBB_DENDA,0) as DENDA ,
        IFNULL(A.SPPT_PBB_HARUS_DIBAYAR,0)+IFNULL(A.PBB_DENDA,0) as JUMLAH";

        $monPBB1->setTable($sql_table);
        $monPBB1->setWhere($where);      
        $monPBB1->query($sql_select);

	// print_r($monPBB1->getAllQuery());
	// print_r("***************");
	// print_r($monPBB1);
	// exit;
          if ($barcode=="1"){
          	$test = $monPBB1->query_result($sql_select);
          	$row = mysql_fetch_row($test['data']);
        	// print_r($row);
        	// echo "nop :"+$row[0];
        	echo json_encode($row);
          }else{
          	   echo $monPBB1->getCountData();
          }

 
}else{
	echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
?>