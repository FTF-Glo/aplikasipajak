<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
//error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
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

//error_reporting(E_ALL);
ini_set('display_errors', 1);

function headerMonitoringRealisasi() {
    global $appConfig;
   
    $html = "<table class=\"table table-bordered table-striped\" style=\"width:800px\">
	  <tr>
		<th rowspan=2 width=10>NO</th>
		<th rowspan=2>KECAMATAN</th>
		<th rowspan=2>JUMLAH OP</th>
		<th rowspan=2>JUMLAH OP FASUM</th>
		<th colspan=2>LUAS</th>
		<th rowspan=2>NJOP</th>
	  </tr>
	  <tr>
		<th width=130>TANAH</th>
		<th width=130>BANGUNAN</th>
	  </tr>
	";
    return $html;
}

function getData() {
	global $DBLink;

	$return=array();
	//$return["RESULT"]=0;
	$default = array("JUMLAH_AKTIF"=>0, "JUMLAH_FASUM"=>0, "BUMI_AKTIF"=>0, "BUMI_FASUM"=>0, "BANGUNAN_AKTIF"=>0, "BANGUNAN_FASUM"=>0, "NJOP_AKTIF"=>0, "NJOP_FASUM"=>0);
	$queryKecamatan = "SELECT CPC_TKC_ID, CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan ORDER BY CPC_TKC_ID";
	$res = mysqli_query($DBLink, $queryKecamatan);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$row["CPC_TKC_ID"]] = $default;
		$return[$row["CPC_TKC_ID"]]["NAMA"]	= $row['CPC_TKC_KECAMATAN'];
	}
        
	$query = "SELECT OP_KECAMATAN_KODE AS KODE, COUNT(*) AS JUMLAH, SUM(OP_LUAS_BUMI) AS BUMI, SUM(OP_LUAS_BANGUNAN) AS BANGUNAN, SUM(OP_NJOP) AS NJOP  FROM cppmod_pbb_sppt_current
                WHERE NOP != ''
                GROUP BY OP_KECAMATAN_KODE
                ORDER BY OP_KECAMATAN_KODE ASC"; 
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$row["KODE"]]["JUMLAH_AKTIF"]	= $row["JUMLAH"];
		$return[$row["KODE"]]["BUMI_AKTIF"]	= $row["BUMI"];
		$return[$row["KODE"]]["BANGUNAN_AKTIF"]	= $row["BANGUNAN"];
		$return[$row["KODE"]]["NJOP_AKTIF"]	= $row["NJOP"];
	}
        
	$query2 = "SELECT CPM_OP_KECAMATAN AS KODE, COUNT(*) AS JUMLAH, SUM(CPM_OP_LUAS_TANAH) AS BUMI, SUM(CPM_OP_LUAS_BANGUNAN) AS BANGUNAN, SUM(CPM_NJOP_TANAH +CPM_NJOP_BANGUNAN) AS NJOP  FROM cppmod_pbb_sppt_final
                WHERE CPM_OT_JENIS='4'
                GROUP BY CPM_OP_KECAMATAN
                ORDER BY CPM_OP_KECAMATAN ASC";
                
	
	$res = mysqli_query($DBLink, $query2);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$row["KODE"]]["JUMLAH_FASUM"]	= $row["JUMLAH"];
		$return[$row["KODE"]]["BUMI_FASUM"]	= $row["BUMI"];
		$return[$row["KODE"]]["BANGUNAN_FASUM"]	= $row["BANGUNAN"];
		$return[$row["KODE"]]["NJOP_FASUM"]	= $row["NJOP"];
	}
	closeMysql($DBLink);
	return $return;
}

function closeMysql($con) {
    mysqli_close($con);
}

function showTable ($mod=0,$nama="") {
	$html = headerMonitoringRealisasi();
        
        $data = getData();
        $i = 1;
        foreach ($data as $key => $row) {
            $html .= "<tr class=tright>
                    <td class=tcenter>".$i."</td>
                    <td class=tleft>".@$row['NAMA']."</td>
                    <td>".number_format(@$row['JUMLAH_AKTIF'],0,',','.')."</td>
                    <td>".number_format(@$row['JUMLAH_FASUM'],0,',','.')."</td>
                    <td>".number_format((@$row['BUMI_AKTIF']+@$row['BUMI_FASUM']),0,',','.')."</td>
                    <td>".number_format((@$row['BANGUNAN_AKTIF']+@$row['BANGUNAN_FASUM']),0,',','.')."</td>
                    <td>".number_format((@$row['NJOP_AKTIF']+@$row['NJOP_FASUM']),0,',','.')."</td>
            </tr>";
            $i++;
        }
        $html .= "</table>";
	return $html;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);

echo showTable();


?>
