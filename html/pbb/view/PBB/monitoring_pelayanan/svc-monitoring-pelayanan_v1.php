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
	<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" width=\"100%\">
	  <tr>
		<th width=\"15%\" align=\"center\">LAYANAN</td>
		<th width=\"15%\" align=\"center\">DATA ENTRY</td>
		<th width=\"15%\" align=\"center\">PENELITY</td>
		<th width=\"15\" align=\"center\">KASIE PENDATAAN</td>
		<th width=\"15\" align=\"center\">PENCETAKAN</td>
		<th width=\"10\" align=\"center\">JUMLAH BERKAS</td>
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
        $html .= " 
			<tr>
				<td align=\"left\">".$dt[$i]['JENIS_BERKAS']."</td>
				<td align=\"center\">".getCountTertunda($dt[$i]['CPM_TYPE'],'0')."/".getCountProses($dt[$i]['CPM_TYPE'],'0')."</td>
				<td align=\"center\">".getCountTertunda($dt[$i]['CPM_TYPE'],'1')."/".getCountProses($dt[$i]['CPM_TYPE'],'1')."</td>
				<td align=\"center\">".getCountTertunda($dt[$i]['CPM_TYPE'],'2')."/".getCountProses($dt[$i]['CPM_TYPE'],'2')."</td>
				<td align=\"center\">".getCountTertunda($dt[$i]['CPM_TYPE'],'3')."/".getCountProses($dt[$i]['CPM_TYPE'],'3')."</td>
				<td align=\"center\">".$dt[$i]['JUMLAH']."</td>
			</tr>";	
        $a++;
    }
		  
	return $html."</table>";
}

function getData() {
	global $DBLink, $appConfig;
	
	$query = "SELECT
					A.SERVICE_TYPE_DESC AS JENIS_BERKAS, A.SERVICE_TYPE_ID AS CPM_TYPE, COUNT(CPM_TYPE) AS JUMLAH
				FROM
					cppmod_pbb_services_type A
				LEFT JOIN cppmod_pbb_services B ON A.SERVICE_TYPE_ID = B.CPM_TYPE
				GROUP BY SERVICE_TYPE_ID
				ORDER BY SERVICE_TYPE_ID"; 
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
		$data[$i]["JENIS_BERKAS"] = $row["JENIS_BERKAS"];
		$data[$i]["CPM_TYPE"] 	  = $row["CPM_TYPE"];
		$data[$i]["JUMLAH"]		  = $row["JUMLAH"];
		$i++;
	}
	// print_r($data);
	return $data;
}

function getCountTertunda($type,$status) {
	global $DBLink;
	
	$query = "SELECT COUNT(*) AS JUMLAH FROM cppmod_pbb_services WHERE CPM_TYPE = '$type' AND CPM_STATUS = '$status'"; 
	// echo $query.'<br/>';
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$row = mysqli_fetch_assoc($res);
	return $row['JUMLAH'];
}

function getCountProses($type,$status) {
	global $DBLink;
	
	$query = "SELECT COUNT(*) AS JUMLAH FROM cppmod_pbb_services WHERE CPM_TYPE = '$type' AND CPM_STATUS > '$status'"; 
	// echo $query.'<br/>';
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$row = mysqli_fetch_assoc($res);
	return $row['JUMLAH'];
}
?>

