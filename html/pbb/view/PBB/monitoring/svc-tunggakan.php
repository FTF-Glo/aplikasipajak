<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
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

date_default_timezone_set("Asia/Jakarta");

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


$myDBLink ="";

function headerMonitoringTunggakan($mod,$nama) {
	global $appConfig;
	$model = ($mod==0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod==0) { 
		$dl = $appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table class=\"table table-bordered table-striped\" style=\"width:700px\"><tr><th colspan=6><b>{$dl}<b></th></tr>
	  <tr>
		<th width=28 rowspan=2>NO</th>
		<th width=200 rowspan=2>{$model}</th>
		<th width=136 rowspan=2>JUMLAH OP MENUNGGAK</th>
		<th width=450 colspan=3>NILAI TUNGGAKAN</th>
	  </tr>
    	<tr>
		<th width=150>POKOK</th>
		<th width=150>DENDA</th>
		<th width=150>TOTAL</th>
	  </tr>
	";
	return $html; 
}

// koneksi postgres
function openMysql () {
	global $appConfig;
        $host = $appConfig['GW_DBHOST'];
        $port = isset($appConfig['GW_DBPORT'])? $appConfig['GW_DBPORT']:'3306';
        $user = $appConfig['GW_DBUSER'];
        $pass = $appConfig['GW_DBPWD'];
        $dbname = $appConfig['GW_DBNAME']; 
		$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
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


function getTunggakan($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s;
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
    
        $tahun = "";
	if($thn != ""){$tahun = "AND A.SPPT_TAHUN_PAJAK='{$thn}'";}	

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " A.NOP LIKE '".$kec[$i]["id"]."%' ".$tahun." AND (A.PAYMENT_FLAG != '1' OR A.PAYMENT_FLAG IS NULL)";
		$da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["POKOK"] = $da["POKOK"];
		$data[$i]["DENDA"] = $da["DENDA"];
	}
	
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $thn;
	$dt = getTunggakan($mod);
	$c = count($dt);
	$html = "";
	$a=1;
	$html = headerMonitoringTunggakan ($mod,$nama);
        
        $jmlWP = 0;
        $jmlTunggakan = 0;
        $jmlDenda = 0;
	for ($i=0;$i<$c;$i++) {
            $jmlWP += $dt[$i]["WP"];
            $jmlTunggakan += $dt[$i]["POKOK"];
            $jmlDenda += $dt[$i]["DENDA"];
            $wp = number_format($dt[$i]["WP"],0,",",".");
            $pokok = number_format($dt[$i]["POKOK"],0,",",".");
            $denda = number_format($dt[$i]["DENDA"],0,",",".");
            $total = number_format($dt[$i]["POKOK"]+$dt[$i]["DENDA"],0,",",".");
            $html .= " <tr>
                <td align=\"right\">{$a}</td>
                <td>{$dt[$i]["name"]}</td>
                <td align=\"right\">".$wp."</td>
                <td align=\"right\">".$pokok."</td>
                <td align=\"right\">".$denda."</td>
                <td align=\"right\">".$total."</td>
                </tr>";
				
          $a++;
        }
		$html .= " <tr>
            <td align=\"right\">&nbsp;</td>
            <td>JUMLAH</td>
            <td align=\"right\">".number_format($jmlWP,0,',','.')."</td>
            <td align=\"right\">".number_format($jmlTunggakan,0,',','.')."</td>
            <td align=\"right\">".number_format($jmlDenda,0,',','.')."</td>
            <td align=\"right\">".number_format($jmlTunggakan+$jmlDenda,0,',','.')."</td>
          </tr>";
		  
	return $html."</table>";
}
function getData($where) {
	global $myDBLink,$kd,$thn,$bulan;

	$myDBLink = openMysql();
	$return=array();
	$return["POKOK"]=0;
	$return["WP"]=0;
	$whr="";
	if($where) {
		$whr =" where {$where}";
	}	
	$query = "SELECT count(A.wp_nama) AS WP, sum(A.SPPT_PBB_HARUS_DIBAYAR) AS POKOK, sum(B.PBB_DENDA) AS DENDA 
                FROM pbb_sppt A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK {$whr}"; 
    //echo $query.'<br/>';exit;
	if($_SERVER['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:129.0) Gecko/20100101 Firefox/129.0'){
		// echo '<pre>';
		// print_r($query);
		// exit;
	}
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["DENDA"]=($row["DENDA"]!="")?$row["DENDA"]:0;
		$return["POKOK"]=($row["POKOK"]!="")?$row["POKOK"]:0;
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
