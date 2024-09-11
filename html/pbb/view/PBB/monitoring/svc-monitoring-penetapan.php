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

function headerPenetapan () {
	global $appConfig, $kecamatan, $kelurahan, $namaKec, $namaKel;
	if($kecamatan!=""){
		if($kelurahan!=""){
			$dl = " KECAMATAN : ".strtoupper($namaKec)."<br> ".strtoupper($appConfig['LABEL_KELURAHAN'])." : ".strtoupper($namaKel)."";
		} else {
			$dl = " KECAMATAN : ".strtoupper($namaKec).""; 
		}
	} else $dl = $appConfig['NAMA_KOTA'];
	
	$html = "<table class=\"table table-bordered table-striped\" style=\"width:500px\"><tr><th colspan=15 class=tleft><b>{$dl}<b></th></tr>
	  <tr>
		<th width=8>NO</th>
		<th width=100>NOP</th>
		<th>WAJIB PAJAK</th>
		<th>TERHUTANG</th>
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

function paging($totalrows) {
		global $page,$perpage;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"showPenetapanPage(".($page-1).")\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"showPenetapanPage(".($page+1).")\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
	
function getCountData($where){
	global $myDBLink,$qBuku;

	$myDBLink 			= openMysql();
	
	$whr = "";
	if($where) {
		$whr = " where {$where}";
	}	
	$qRows = "SELECT COUNT(*) FROM pbb_sppt {$whr} {$qBuku} AND NOP <> '' OR NOP <> NULL"; 
	// echo $qRows.'<br/>';
	
	$exec 		= mysqli_query($myDBLink, $qRows);
	$resCount 	= mysqli_fetch_array($exec);
	$totalrows  = $resCount[0];
	if ($exec === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	closeMysql($myDBLink);
	return $totalrows;
}

function getData($where) {
	global $myDBLink,$perpage,$page,$qBuku;

	$myDBLink 			= openMysql();
	$data 				= array();
	$return				= array();
	$return["NOP"]		= "";
	$return["NAMA"]		= "";
	$return["TAGIHAN"]	= 0;
	$hal 				= (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	
	$whr = "";
	if($where) {
		$whr = " where {$where} {$qBuku}";
	}	
	$query = "SELECT NOP, WP_NAMA, SPPT_PBB_HARUS_DIBAYAR FROM pbb_sppt {$whr} {$qBuku} AND NOP <> '' OR NOP <> NULL LIMIT {$page},{$perpage} "; 
	// echo $query.'<br/>';
	
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		// print_r($row);
		$return["NOP"]		=($row["NOP"]!="")?$row["NOP"]:"";
		$return["NAMA"]		=($row["WP_NAMA"]!="")?$row["WP_NAMA"]:"";
		$return["TAGIHAN"]	=($row["SPPT_PBB_HARUS_DIBAYAR"]!=0)?$row["SPPT_PBB_HARUS_DIBAYAR"]:0;
		$data[] = $return;
	}
	closeMysql($myDBLink);
	return $data;
}

function showTable () {
	global $where,$page,$perpage;
	$data 		= getData($where);
	$totalrows 	= getCountData($where);
	$c 			= count($data);
	$html 		= '<div class="tbl-monitoring responsive">';
	$html 		.= headerPenetapan ();
	$row 		= (($page-1) > 0 ? ($page-1) : 0) * $perpage;
	$no			= ($row+1);
	if($c!=0){
		for($i=0;$i<$c;$i++){
			$html .= "
						<tr>
							<td align=\"right\">".$no."</td>
							<td align=\"center\">".$data[$i]['NOP']."</td>
							<td>".$data[$i]['NAMA']."</td>
							<td align=\"right\">".number_format($data[$i]['TAGIHAN'],0,',','.')."</td>
						</tr>
						";
		$no++;
		}
	} else {
		$html .= "
					<tr>
						<td colspan=\"4\" align=\"center\">Tidak ada data.</td>
					</tr>";
	}
		  
	return $html.= "	<tr>
							<td colspan=\"4\" align=\"center\">
							".paging($totalrows)."
							</td>
						</tr></table>";
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
$kd 		= $appConfig['KODE_KOTA'];
$perpage	= $appConfig['ITEM_PER_PAGE'];

// print_r($appConfig);
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec	= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel	= @isset($_REQUEST['nk']) ? $_REQUEST['nk'] : "";
$buku		= @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : "";
// print_r($_REQUEST);

$arrWhere = array();
if ($kecamatan !="") {
	if ($kelurahan !="") array_push($arrWhere,"NOP like '{$kelurahan}%'");
	else array_push($arrWhere,"NOP like '{$kecamatan}%'");
}
if ($thn!=""){
    array_push($arrWhere,"SPPT_TAHUN_PAJAK ='{$thn}'");   
} 
$where = implode (" AND ",$arrWhere);

$qBuku = "";
 if($buku != 0){
 switch ($buku){
 case 1 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
 case 12 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
 case 123 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
 case 1234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 12345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 2 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
 case 23 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
 case 234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 2345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 3 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
 case 34 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 4 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 45 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 5 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 }
 }


echo showTable ();
