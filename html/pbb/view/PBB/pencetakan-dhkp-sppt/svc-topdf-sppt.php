<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pencetakan-dhkp-sppt', '', dirname(__FILE__))).'/';
date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath."inc/payment/ctools.php"); 
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/error-messages.php"); 

require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/nid.php");
require_once($sRootPath."inc/payment/uuid.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[". strftime("%Y%m%d%H%M%S", time()) ."][". (basename(__FILE__)) .":". __LINE__ ."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* inisiasi parameter */
if(isset($_REQUEST['q'])){
	$uid = c_uuid();
	$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] :"";
	$q = base64_decode($q);
	$q = $json->decode($q);
	$q->kd_kel 	= $_POST['kd_kel'];
	$q->blok 	= $_POST['blok'];
	$q->blok2 	= $_POST['blok2'];
	$q->tahun 	= $_POST['thn'];
	$q->kd_buku = $_POST['kd_buku']; //Add By ZNK
	$q->buku 	= ($_POST['kd_buku']!=0 ? $_POST['buku'] : "-");//Add By ZNK
	$q->uid 	= $uid;
	$a 			= $q->a;
	$m 			= $q->m;
	
	//Edited By ZNK
	$qBuku = "";
	if($q->kd_buku!=0){
		switch ($q->kd_buku){
			case 1 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
			case 12 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
			case 123 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
			case 1234 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
			case 12345 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
			case 2 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
			case 23 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
			case 234 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
			case 2345 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
			case 3 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
			case 34 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
			case 345 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
			case 4 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
			case 45 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
			case 5 : $qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		}
	}
	$query = "SELECT COUNT(*) AS TOTAL FROM cppmod_pbb_sppt_current WHERE NOP >= '".$q->kd_kel.$q->blok."00000' AND NOP <= '".$q->kd_kel.$q->blok2."99999' {$qBuku}";
	// echo $query;
	//End ZNK
	$res = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
	$data = mysqli_fetch_array($res);
	
	if($data['TOTAL'] == 0) exit('NOP tidak ditemukan.');
	
	/*insert to table download*/
	$param['CPM_ID'] = "'{$uid}'";
	$param['CPM_BLOK'] = "'{$q->kd_kel}{$q->blok}-{$q->kd_kel}{$q->blok2}'";
	$param['CPM_TAHUN'] = "'{$q->tahun}'";
	$param['CPM_BUKU'] = "'{$q->buku}'"; //Add By ZNK
	$param['CPM_SIZE'] = "'-'";
	$param['CPM_JUMLAH_NOP'] = "'-'";
	$param['CPM_STATUS'] = "'0'";
	$param['CPM_DATETIME'] = 'NOW()';
	
	$fields = implode(',',array_keys($param));
	$values = implode(',',array_values($param)); 
	$query = "INSERT INTO cppmod_pbb_sppt_download ({$fields}) VALUES ({$values})";	
	mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
	/*end insert*/
	
	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$arConfig 	= $User->GetModuleConfig($m);
	$appConfig 	= $User->GetAppConfig($a);
	
	$C_HOST_PORT 	= $appConfig['GW_DBHOST'];
	$C_USER 		= $appConfig['GW_DBUSER'];
	$C_PWD 			= $appConfig['GW_DBPWD'];
	$C_DB 			= $appConfig['GW_DBNAME'];

	SCANPayment_ConnectToDB($DBLinkGW, $DBConnGW, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB);
	
	// $url = "http://192.168.26.11:23023/view/PBB/pencetakan-dhkp-sppt/api-topdf-sppt.php";
	$url = $appConfig['URL_SVC_CETAK_MASAL_SPPT'];
	$vars = (array) $q;
	
	$postData = http_build_query($vars);
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_POST, 1);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt( $ch, CURLOPT_HEADER, 0);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
	
	$response = curl_exec( $ch );
	//echo $json->encode(array('msg'=>$response));
	/* end kirim*/
}
?>
