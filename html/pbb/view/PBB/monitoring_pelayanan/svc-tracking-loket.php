<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_pelayanan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

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

error_reporting(E_ALL);
ini_set('display_errors', 1);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;
$uid = $q->uid;
//echo $s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$keyword  	= @isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : ""; 

// print_r($_REQUEST);
 
echo showTable();

function headerMonitoring() {
	global $appConfig;
	$html = "
	<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>
	<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>
	<style>
	td,th {
		height: 43px;
		vertical-align: middle;
	}
	</style>
	<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" width=\"100%\">
	  <tr>
		<th width=\"20%\" align=\"center\">NOP</td>
		<th width=\"20%\" align=\"center\">NAMA</td>
		<th width=\"20%\" align=\"center\">POSISI BERKAS</td>
		<th width=\"40\" align=\"center\">RIWAYAT PELAYANAN</td>
	  </tr>";
	return $html; 
}

function showTable() {
	global $appConfig;
	
	$dt 		= getData(); 
	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$a 			= 1;
	$html 		.= headerMonitoring ();
	
    for ($i=0;$i<$c;$i++) {
		
		$status 		= $dt[$i]['STATUS'];
		$jnsBerkas		= $dt[$i]['JENIS_BERKAS'];
		$receiver		= $dt[$i]['RECEIVER'];
		$validator		= $dt[$i]['VALIDATOR'];
		$verificator	= $dt[$i]['VERIFICATOR'];
		$approver		= $dt[$i]['APPROVER'];
		$posisiBerkas 	= "";
		$riwayat 		= "";
		
		switch($jnsBerkas){
			case 1 : $strJnsBerkas = "OP Baru"; break;
			case 2 : $strJnsBerkas = "Pemecahan"; break;
			case 3 : $strJnsBerkas = "Penggabungan"; break;
			case 4 : $strJnsBerkas = "Mutasi"; break;
			case 5 : $strJnsBerkas = "Perubahan Data"; break;
			case 6 : $strJnsBerkas = "Pembatalan"; break;
			case 7 : $strJnsBerkas = "Salinan"; break;
			case 8 : $strJnsBerkas = "Penghapusan"; break;
			case 9 : $strJnsBerkas = "Pengurangan"; break;
			case 10 : $strJnsBerkas = "Keberatan"; break;
			case 11 : $strJnsBerkas = "Pengurangan Denda"; break;
		}
		
		$riwayat = array();
		switch($status){
			case 0 : $posisiBerkas 	= "Loket"; 
					 $riwayat 		= "Loket(".$receiver.")";
					 break;
			case 1 : //Penentuan POSISI
					 if($jnsBerkas==1 || $jnsBerkas==2){ //Jenis Berkas OP Baru atau Pemecahan
						$posisiBerkas = "Pendataan/Verifikasi I: ".$strJnsBerkas;
					 } else if ($jnsBerkas==9 || $jnsBerkas==10 || $jnsBerkas==11){
						$posisiBerkas = $strJnsBerkas;
					 } else {
						$posisiBerkas = "Mutasi: ".$strJnsBerkas;
					 } 
					 $riwayat = "Loket(".$receiver.")";
					 break;
			case 2 : if($jnsBerkas==1 || $jnsBerkas==2){ //Jenis Berkas OP Baru atau Pemecahan
						$posisiBerkas = "Verifikasi II: ".$strJnsBerkas;
					 } else if ($jnsBerkas==9 || $jnsBerkas==10 || $jnsBerkas==11){
						$posisiBerkas = "Verifikasi ".$strJnsBerkas;
					 } else {
						$posisiBerkas = "Verifikasi Mutasi: ".$strJnsBerkas;
					 } 
					 $riwayat = "Loket(".$receiver.")->Validator(".$validator.")"; 
					 break;
			case 3 : if($jnsBerkas==1 || $jnsBerkas==2){ //Jenis Berkas OP Baru atau Pemecahan
						$posisiBerkas = "Verifikasi III: ".$strJnsBerkas;
					  } else if ($jnsBerkas==9 || $jnsBerkas==10 || $jnsBerkas==11){
						$posisiBerkas = "Persetujuan ".$strJnsBerkas;
					 } else {
						$posisiBerkas = "Persetujuan Mutasi: ".$strJnsBerkas;
					 } 
					 if($jnsBerkas==7 || $jnsBerkas==11){ //Jenis Berkas Salinan atau Pengurngan Denda
						$riwayat = "Loket(".$receiver.")"; 
					 } else {
						 $riwayat = "Loket(".$receiver.")->Validator(".$validator.")->Verifikator(".$verificator.")";
					 }
					 break;
			case 4 : $posisiBerkas 	= "Selesai ".$strJnsBerkas; 
					 if($jnsBerkas==7 || $jnsBerkas==11){ //Jenis Berkas Salinan atau Pengurngan Denda
						$riwayat = "Loket(".$receiver.")->Approver(".$approver.")"; 
					 } else if($jnsBerkas==1 || $jnsBerkas==2){
						if($appConfig['jumlah_verifikasi']==1){
							$riwayat = "Loket(".$receiver.")->Approver(".$approver.")";
						} else if($appConfig['jumlah_verifikasi']==2){
							$riwayat = "Loket(".$receiver.")->Validator(".$validator.")->Approver(".$approver.")";
						} else {
							$riwayat = "Loket(".$receiver.")->Validator(".$validator.")->Verifikator(".$verificator.")->Approver(".$approver.")";
						}
					 } else {
						$riwayat = "Loket(".$receiver.")->Validator(".$validator.")->Verifikator(".$verificator.")->Approver(".$approver.")";
					 }
					 break;
			case 5 : $posisiBerkas = "Tolak Verifikasi ".$strJnsBerkas; 
					 if($jnsBerkas==7 || $jnsBerkas==11){ //Jenis Berkas Salinan atau Pengurngan Denda
						$riwayat = "Loket(".$receiver.")->Approver(".$approver.")"; 
					 } else {
						$riwayat = "Loket(".$receiver.")->Validator(".$validator.")->Verifikator(".$verificator.")";
					 }
					 break;
			case 6 : $posisiBerkas = "Tolak Persetujuan ".$strJnsBerkas; 
					 if($jnsBerkas==7 || $jnsBerkas==11){ //Jenis Berkas Salinan atau Pengurngan Denda
						$riwayat = "Loket(".$receiver.")->Approver(".$approver.")"; 
					 } else {
						$riwayat = "Loket(".$receiver.")->Validator(".$validator.")->Verifikator(".$verificator.")->Approver(".$approver.")";
					 }
					 break;
		}
		
		//Jika OP Baru NOP ambil dari NOP_BARU
		if($jnsBerkas==1){
			$nop = ($dt[$i]['NOP_BARU']!="" ? $dt[$i]['NOP_BARU'] : "NOP belum tersedia");
		} else if($jnsBerkas==2){
			$nop = "(Induk)".$dt[$i]['NOP']."".($dt[$i]['NOP_SPLIT']!="" ? ", (Baru)".$dt[$i]['NOP_SPLIT'] : "");
		} else {
			$nop = $dt[$i]['NOP'];
		}
		
        $html .= " 
			<tr>
				<td align=\"center\">".$nop."</td>
				<td align=\"left\">".$dt[$i]['NAMA']."</td>
				<td align=\"left\">".$posisiBerkas."</td>
				<td align=\"left\">".$riwayat."</td>
			</tr>";	
        $a++;
    }
		  
	return $html."</table>";
}

function getData() {
	global $DBLink, $appConfig, $keyword;
	$keyword = trim($keyword);
	
	$where = "";
	if($keyword!=""){
		$where = " WHERE CPM_OP_NUMBER LIKE '%$keyword%' OR CPM_WP_NAME LIKE '%$keyword%' ";
	}
	
	$query = "SELECT * FROM cppmod_pbb_services A 
			  LEFT JOIN cppmod_pbb_service_new_op B ON B.CPM_NEW_SID = A.CPM_ID 
			  LEFT JOIN cppmod_pbb_service_split C ON C.CPM_SP_SID = A.CPM_ID $where"; 
	// echo $query.'<br/>';
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	// $row = mysqli_fetch_assoc($res);
	$data 	= array();
	$i		= 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["NOP"]				= $row["CPM_OP_NUMBER"];
		$data[$i]["NOP_BARU"]			= $row["CPM_NEW_NOP"];
		$data[$i]["NOP_SPLIT"]			= $row["CPM_SP_NOP"];
		$data[$i]["NAMA"]				= $row["CPM_WP_NAME"];
		$data[$i]["JENIS_BERKAS"]		= $row["CPM_TYPE"];
		$data[$i]["STATUS"]				= $row["CPM_STATUS"];
		$data[$i]["RECEIVER"]			= $row["CPM_RECEIVER"];
		$data[$i]["VALIDATOR"]			= $row["CPM_VALIDATOR"];
		$data[$i]["VERIFICATOR"]		= $row["CPM_VERIFICATOR"];
		$data[$i]["APPROVER"]			= $row["CPM_APPROVER"];
		$i++;
	}
	// print_r($data);
	return $data;
}
?>

