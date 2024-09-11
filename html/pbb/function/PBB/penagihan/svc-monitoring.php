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

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = DBHOST;
$port = DBPORT;
$user = DBUSER;
$pass = DBPWD;
$dbname = DBNAME; 
$myDBLink ="";

function headerMonitoringE2 ($mod,$nama) {
	$model = ($mod==0) ? "KECAMATAN" : "KELURAHAN";
	$dl = "";
	if ($mod==0) { 
		$dl = "KOTA PALEMBANG";
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"4\"><b>{$dl}<b></td></tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <tr>
		<td rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<td rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">TAGIHAN</td>
	  </tr>
	  <tr>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
	  </tr>
	";
	return $html; 
}

// koneksi postgres
function openMysql () {
	$host = DBHOST;
	$port = DBPORT;
	$dbname = DBNAME;
	$user = DBUSER;
	$pass = DBPWD;
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


function getKetetapan($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s;
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
    
    $tahun = "";
	
	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
	

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
                $whr = " NOP like '".$kec[$i]["id"]."%' ".$tahun;
                $da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt = getKetetapan($mod);
	
	$c = count($dt);
	$html = "";
	$a=1;
	$html = headerMonitoringE2 ($mod,$nama);
	$summary = array('name'=>'JUMLAH', 'ketetapan_wp'=>0, 'ketetapan_rp'=>0, 'rbl_wp'=>0, 'rbl_rp'=>0, 'percent1'=>0, 'rbi_wp'=>0, 'rbi_rp'=>0,'kom_rbi_wp'=>0, 'kom_rbi_rp'=>0, 'percent2'=>0, 'sk_wp'=>0, 'sk_rp'=>0, 'percent3'=>0);
        for ($i=0;$i<$c;$i++) {
               
		$dtname = $dt[$i]["name"];
                $wp = number_format($dt[$i]["WP"],0,",",".");
                $rp = number_format($dt[$i]["RP"],0,",",".");
                $html .= " <tr>
	            <td align=\"right\">{$a}</td>
	            <td>{$dtname}</td>
	            <td align=\"right\">{$wp}</td>
	            <td align=\"right\">{$rp}</td>
	          </tr>";
		  
				$summary['ketetapan_wp'] += $dt[$i]["WP"];
				$summary['ketetapan_rp'] += $dt[$i]["RP"];
				
          $a++;
        }
		
		$html .= " <tr>
            <td align=\"right\"> </td>
            <td>".$summary['name']."</td>
            <td align=\"right\">".number_format($summary['ketetapan_wp'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['ketetapan_rp'],0,',','.')."</td>
          </tr>";
		  
	return $html."</table>";
}

function getData($where) {
	global $myDBLink,$kd,$thn,$bulan;

	$myDBLink = openMysql();
	$return=array();
	$return["RP"]=0;
	$return["WP"]=0;
	$whr=" where (payment_flag !='1' or payment_flag is null) ";
	if($where) {
		$whr .=" and {$where}";
	}	
	$query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) as RP FROM ".DBTABLE." {$whr}"; //echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		$return["RP"]=($row["RP"]!="")?$row["RP"]:0;
		$return["WP"]=($row["WP"]!="")?$row["WP"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kab  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$eperiode = "";
$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";

$arrWhere = array();

if ($kecamatan !="") {
        array_push($arrWhere,"nop like '{$kecamatan}%'");
}

if ($thn!=""){
    array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");  
    array_push($arrWhere,"payment_paid like '{$thn}%'");  
} 

$where = implode (" AND ",$arrWhere);

if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1,$nama);
}
