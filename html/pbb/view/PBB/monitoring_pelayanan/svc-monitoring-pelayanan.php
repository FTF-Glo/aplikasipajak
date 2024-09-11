<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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


$filterFromDate = isset($_REQUEST['from_date']) && $_REQUEST['from_date'] ? $_REQUEST['from_date'] : '';
$filterToDate = isset($_REQUEST['to_date']) && $_REQUEST['to_date'] ? $_REQUEST['to_date'] : '';

$filterDate = array(
	mysqli_escape_string($DBLink, $filterFromDate), 
	mysqli_escape_string($DBLink, $filterToDate)
);

// print_r($_REQUEST);
 
echo showTable();

function customScriptOrStyle()
{
	return '
	<style>
		.ui-datepicker-title { color: #000!important }
	</style>
	<script>
		$(function() {
			var dateFormat = "yy-mm-dd",
			mon_from_date = $("#monitoring-from-date").datepicker({
				dateFormat: dateFormat,
                changeYear: true,
                changeMonth: true
			}).on( "change", function() {
				mon_to_date.datepicker( "option", "minDate", getDate( this ) );
			}),
			mon_to_date = $("#monitoring-to-date").datepicker({
				dateFormat: dateFormat,
                changeYear: true,
                changeMonth: true
			}).on( "change", function() {
				mon_from_date.datepicker( "option", "maxDate", getDate( this ) );
			});


			function getDate( element ) {
				var date;
				try {
					date = $.datepicker.parseDate( dateFormat, element.value );
				} catch( error ) {
					date = null;
				}
				return date;
			}

			$("body").on("click", "#monitoring-reset-btn", function(){
				$("#monitoring-from-date, #monitoring-to-date").val("");
				$("#monitoring-from-date, #monitoring-to-date").trigger("change");
			})

		})
	</script>
	';
}

function headerMonitoring() {
	global $appConfig, $filterDate;

	list($fromDate, $toDate) = $filterDate;

	$html = "
	<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>
	<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>
	". customScriptOrStyle() ."
	<div style=\"display: block\">
		<div class=\"row\">
			<div class=\"form-group col-xs-2\">
				<label for=\"monitoring-from-date\">Dari tanggal</label>
				<input type=\"text\" class=\"form-control\" id=\"monitoring-from-date\" size=\"10\" value=\"". $fromDate ."\">
			</div>
			<div class=\"form-group col-xs-2\">
				<label for=\"monitoring-to-date\">Sampai tanggal</label>
				<input type=\"text\" class=\"form-control\" id=\"monitoring-to-date\" size=\"10\" value=\"". $toDate ."\">
			</div>
			<div class=\"form-group col-xs-8\">
				<div style=\"display: block;margin-top: 2em\">
					<button type=\"button\" class=\"btn btn-primary btn-blue\" id=\"monitoring-search-btn\">Cari</button>
					<button type=\"button\" class=\"btn btn-seconday\" id=\"monitoring-reset-btn\">Reset</button>
				</div>
				
			</div>
		</div>
	</div>
	<div class=\"table table-responsive\">
	<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\" width=\"100%\" class=\"table table-bordered\">
	  <tr>
		<th width=\"15%\" align=\"center\">LAYANAN</td>
		<th width=\"15%\" align=\"center\">DATA ENTRY</td>
		<th width=\"15%\" align=\"center\">PENELITI</td>
		<th width=\"15\" align=\"center\">KASIE PENDATAAN</td>
		<th width=\"15\" align=\"center\">PENCETAKAN</td>
		<th width=\"10\" align=\"center\">JUMLAH BERKAS</td>
	  </tr>";
	return $html; 
}

function searchByType($type, $array) {
   foreach ($array as $key => $val) {
       if ($val['CPM_TYPE'] === $type) {
           return $val['JUMLAH'];
       }
   }
   return 0;
}

function showTable() {
	global $appConfig;
	
	$dt 		= getData(); 
	
	//Ambil data tertunda
	$dtLokTer	= getTertunda(0);
	$dtValTer	= getTertunda(1);
	$dtVerTer	= getTertunda(2);
	$dtAppTer	= getTertunda(3);
	
	//Ambil data sudah diproses
	$dtLokPros	= getProses(0);
	$dtValPros	= getProses(1);
	$dtVerPros	= getProses(2);
	$dtAppPros	= getProses(3);
	
	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$a 			= 1;
	$html 		.= headerMonitoring ();
    for ($i=0;$i<$c;$i++) {

		 $html .= " 
			<tr>
				<td align=\"left\">".$dt[$i]['JENIS_BERKAS']."</td>
				<td align=\"center\">".searchByType($dt[$i]['CPM_TYPE'], $dtLokTer)."/".searchByType($dt[$i]['CPM_TYPE'],$dtLokPros)."</td>
				<td align=\"center\">".searchByType($dt[$i]['CPM_TYPE'], $dtValTer)."/".searchByType($dt[$i]['CPM_TYPE'],$dtValPros)."</td>
				<td align=\"center\">".searchByType($dt[$i]['CPM_TYPE'], $dtVerTer)."/".searchByType($dt[$i]['CPM_TYPE'],$dtVerPros)."</td>
				<td align=\"center\">".searchByType($dt[$i]['CPM_TYPE'], $dtAppTer)."/".searchByType($dt[$i]['CPM_TYPE'],$dtAppPros)."</td>
				<td align=\"center\">".$dt[$i]['JUMLAH']."</td>
			</tr>";	
        $a++;
    }
		  
	return $html."</div></table>";
}

function getData() {
	global $DBLink, $appConfig, $filterDate;

	list($fromDate, $toDate) = $filterDate;
	
	$queryDate = "";
	if ($fromDate || $toDate) {
		$queryDate = array();
		$queryDate[] = $fromDate ? "B.CPM_DATE_RECEIVE >= '{$fromDate}'" : false;
		$queryDate[] = $toDate ? "B.CPM_DATE_RECEIVE <= '{$fromDate}'" : false;

		$queryDate = array_filter($queryDate, function($value) { return $value; });
		$queryDate = 'AND ('. implode(' AND ', $queryDate) .')';
	}
	
	// $query = "SELECT
	// 				A.SERVICE_TYPE_DESC AS JENIS_BERKAS, B.CPM_TYPE, COUNT(*) AS JUMLAH
	// 			FROM
	// 				cppmod_pbb_services_type A
	// 			LEFT JOIN cppmod_pbb_services B ON A.SERVICE_TYPE_ID = B.CPM_TYPE
	// 			GROUP BY SERVICE_TYPE_ID
	// 			ORDER BY SERVICE_TYPE_ID"; 
	$query = "SELECT
					A.SERVICE_TYPE_DESC AS JENIS_BERKAS, B.CPM_TYPE, 
					CASE WHEN B.CPM_TYPE IS NOT NULL 
					THEN COUNT(*)
					ELSE 0
					END AS JUMLAH ,
					COUNT(*) AS JUMLAH_LAMA # REMOVE BY 35UTECH
				FROM
					cppmod_pbb_services_type A
				LEFT JOIN cppmod_pbb_services B ON A.SERVICE_TYPE_ID = B.CPM_TYPE
				WHERE 1=1 {$queryDate}
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

function getTertunda($status) {
	global $DBLink, $filterDate;

	list($fromDate, $toDate) = $filterDate;
	
	$queryDate = "";
	if ($fromDate || $toDate) {
		$queryDate = array();
		$queryDate[] = $fromDate ? "SVC.CPM_DATE_RECEIVE >= '{$fromDate}'" : false;
		$queryDate[] = $toDate ? "SVC.CPM_DATE_RECEIVE <= '{$fromDate}'" : false;

		$queryDate = array_filter($queryDate, function($value) { return $value; });
		$queryDate = 'AND ('. implode(' AND ', $queryDate) .')';
	}
	
	$query = "SELECT
				SERVICE_TYPE_DESC,
				SERVICE_TYPE_ID AS CPM_TYPE,
				COUNT(*) AS JML,
				CPM_STATUS
			FROM
				cppmod_pbb_services_type SVC_TYPE
			LEFT JOIN cppmod_pbb_services SVC ON SERVICE_TYPE_ID = CPM_TYPE
			WHERE CPM_STATUS = '$status' {$queryDate}
			GROUP BY
				SERVICE_TYPE_ID
			"; 
	// echo $query.'<br/>';
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$data 	= array();
	$i		= 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["CPM_TYPE"] 	  = $row["CPM_TYPE"];
		$data[$i]["JUMLAH"]		  = $row["JML"];
		$i++;
	}
	// print_r($data);
	return $data;
}

function getProses($status) {
	global $DBLink, $filterDate;

	list($fromDate, $toDate) = $filterDate;
	
	$queryDate = "";
	if ($fromDate || $toDate) {
		$queryDate = array();
		$queryDate[] = $fromDate ? "SVC.CPM_DATE_RECEIVE >= '{$fromDate}'" : false;
		$queryDate[] = $toDate ? "SVC.CPM_DATE_RECEIVE <= '{$fromDate}'" : false;

		$queryDate = array_filter($queryDate, function($value) { return $value; });
		$queryDate = 'AND ('. implode(' AND ', $queryDate) .')';
	}
	
	$query = "SELECT
				SERVICE_TYPE_DESC,
				SERVICE_TYPE_ID AS CPM_TYPE,
				COUNT(*) AS JML,
				CPM_STATUS
			FROM
				cppmod_pbb_services_type SVC_TYPE
			LEFT JOIN cppmod_pbb_services SVC ON SERVICE_TYPE_ID = CPM_TYPE
			WHERE CPM_STATUS > '$status' {$queryDate}
			GROUP BY
				SERVICE_TYPE_ID
			"; 
	// echo $query.'<br/>';
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$data 	= array();
	$i		= 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["CPM_TYPE"] 	  = $row["CPM_TYPE"];
		$data[$i]["JUMLAH"]		  = $row["JML"];
		$i++;
	}
	// print_r($data);
	return $data;
}
