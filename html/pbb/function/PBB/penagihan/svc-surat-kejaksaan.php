<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL);
ini_set('display_errors', 1);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

// print_r($_REQUEST);

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$arConfig 	= $User->GetModuleConfig($m);
$kd 		= $appConfig['KODE_KOTA'];

$srch 		= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";

$host 		= $appConfig['GW_DBHOST'];
$port 		= isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : "";
$user 		= $appConfig['GW_DBUSER'];
$pass 		= $appConfig['GW_DBPWD'];
$dbname 	= $appConfig['GW_DBNAME'];
$myDBLink 	="";

function headerMonitoringE2 () {
	global $appConfig, $nkc;
	$html = "
	<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\"><table cellspacing=\"1\" cellpadding=\"5\" border=\"0\" width=\"100%\">
	<tbody>
	   <tr>
		<th width=\"20\" align=\"center\">&nbsp;</td>
		<th width=\"100\" align=\"center\">NOMOR</td>
		<th width=\"200\" align=\"center\">NAMA PENERIMA KUASA</td>
		<th width=\"400\" align=\"center\">JABATAN</td>
		<th width=\"200\" align=\"center\">ALAMAT</td>
	  </tr>
	";
	return $html; 
}

// koneksi postgres
function openMysql () {
	global $host,$port,$dbname,$user,$pass;
	$myDBLink = mysqli_connect($host , $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		echo mysqli_error($myDBLink); 
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con){
	mysqli_close($con);
}
	
function getKecamatan($p) {
	global $DBLink;
	$return = array();
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."' ORDER BY CPC_TKC_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKC_ID"];
		$data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
		$i++;
	}
	
	return $data;
}

function getKelurahan($p) {
	global $DBLink;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res )) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}
	return $data;
}

function showTable () {
	global $a,$m,$arConfig;
	$params 	= "a=".$a."&m=".$m;
	
	$dt = getData();

	$c = count($dt);
	$html = "";
	$a=1;
	$html = headerMonitoringE2 ();
        for ($i=0;$i<$c;$i++) {
				$class = $i%2==0 ? "tdbody1":"tdbody2";
                $html .= " <tr>
				<td width=\"20\" align=\"center\"><input name=\"check-all2[]\" class=\"check-all2\" type=\"checkbox\" value=\"".$dt[$i]['NOMOR']."\" /></td>
	            <td align=\"center\"><a href='main.php?param=" . base64_encode($params."&f=".$arConfig['form_kejaksaan']."&mode=edit&svcid=".$dt[$i]['NOMOR']) . "'>" . $dt[$i]['NOMOR'] . "</a></td>
	            <td align=\"left\">".$dt[$i]['NAMA']."</td>
	            <td align=\"left\">".$dt[$i]['JABATAN']."</td>
	            <td align=\"left\">".$dt[$i]['ALAMAT']."</td>
	          </tr>";
				
          $a++;
        }
		  
	return $html."</tbody></table></div>";
}

function getData() {
	global $myDBLink,$srch;

	$myDBLink = openMysql();
	
	$where="";
	if ($srch !=""){
		$where = "WHERE SPK_NOMOR LIKE '%{$srch}%' OR SPK_NAMA LIKE '%{$srch}%'";
	}
	
	$whr = "";
	if($where) {
		$whr .="$where";
	}	
	
	$query = "SELECT
					*
				FROM
					SURAT_PENGANTAR_KEJAKSAAN $whr";
		// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["NOMOR"] 		= $row["SPK_NOMOR"];
		$data[$i]["NAMA"] 		= $row["SPK_NAMA"];
		$data[$i]["JABATAN"] 	= $row["SPK_JABATAN"];
		$data[$i]["ALAMAT"] 	= $row["SPK_ALAMAT"];
		$i++;
	}
	closeMysql($myDBLink);
	return $data;
}

echo showTable ();
