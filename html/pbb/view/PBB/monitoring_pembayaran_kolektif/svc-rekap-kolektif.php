<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_pembayaran_kolektif', '', dirname(__FILE__))) . '/';
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

function headerPenetapan () {
	global $appConfig, $kecamatan, $kelurahan, $namaKec, $namaKel;
	// if($kecamatan!=""){
	// 	if($kelurahan!=""){
	// 		$dl = " KECAMATAN : ".strtoupper($namaKec)."<br> ".strtoupper($appConfig['LABEL_KELURAHAN'])." : ".strtoupper($namaKel)."";
	// 	} else {
	// 		$dl = " KECAMATAN : ".strtoupper($namaKec).""; 
	// 	}
	// } else $dl = $appConfig['NAMA_KOTA'];
	
	$html = "<table class=\"table table-sm table-bordered\" cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
		<th width=\"28\" align=\"center\">NO</td>
		<th width=\"130\" align=\"center\">NAMA GRUP</td>
		<th width=\"136\" align=\"center\">NAMA KOLEKTOR</td>
		<th width=\"136\" align=\"center\">NO TELP/HP KOLEKTOR</td>
		<th width=\"117\" align=\"center\">KECAMATAN</td>
		<th width=\"136\" align=\"center\">KELURAHAN</td>
		<th width=\"136\" align=\"center\">KODE BAYAR</td>
		<th width=\"30\" align=\"center\">JUMLAH NOP</td>
		<th width=\"136\" align=\"center\">POKOK</td>	
		<th width=\"117\" align=\"center\">DENDA</td>
		<th width=\"136\" align=\"center\">TOTAL BAYAR</td>
		<th width=\"136\" align=\"center\">TANGGAL PEMBAYARAN</td>
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
				$myDBLink = mysqli_connect($host, $user, $pass, $dbname,$port);
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
	global $myDBLink;

	$myDBLink 			= openMysql();
	
	$whr = "";
	if($where) {
		$whr = " where {$where}";
	}	
	$qRows = "SELECT COUNT(*) 
		FROM cppmod_collective_group A 
		LEFT JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = SUBSTRING(A.CPM_CG_AREA_CODE,1,7)
		LEFT JOIN cppmod_tax_kelurahan KL ON KL.CPC_TKL_ID = A.CPM_CG_AREA_CODE
		JOIN cppmod_cg_member CGM ON A.CPM_CG_ID = CGM.CPM_CGM_ID
		JOIN pbb_sppt SPPT ON CGM.CPM_CGM_TAX_YEAR = SPPT.SPPT_TAHUN_PAJAK AND CGM.CPM_CGM_NOP = SPPT.NOP
		{$whr} "; 
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
	global $myDBLink,$perpage,$page, $limit;

	$myDBLink 			= openMysql();
	$data 				= array();
	$return				= array();
	$hal 				= (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	
	$whr = "";
	if($where) {
		$whr = " where {$where}";
	}	
	$query = "SELECT 
    A.CPM_CG_NAME,
    A.CPM_CG_COLLECTOR, 
    A.CPM_CG_HP_COLLECTOR, 
    KC.CPC_TKC_KECAMATAN,
    KL.CPC_TKL_KELURAHAN,
    A.CPM_CG_PAYMENT_CODE, 
    A.CPM_CG_NOP_NUMBER, 
    A.CPM_CG_ORIGINAL_AMOUNT, 
    A.CPM_CG_PENALTY_FEE,  
    (A.CPM_CG_ORIGINAL_AMOUNT + A.CPM_CG_PENALTY_FEE) AS TOTAL,
    A.CPM_CG_PAY_DATE
	FROM cppmod_collective_group A
	LEFT JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = SUBSTRING(A.CPM_CG_AREA_CODE,1,7)
	LEFT JOIN cppmod_tax_kelurahan KL ON KL.CPC_TKL_ID = A.CPM_CG_AREA_CODE
	JOIN cppmod_cg_member CGM ON A.CPM_CG_ID = CGM.CPM_CGM_ID
	JOIN pbb_sppt SPPT ON CGM.CPM_CGM_TAX_YEAR = SPPT.SPPT_TAHUN_PAJAK AND CGM.CPM_CGM_NOP = SPPT.NOP
	{$whr} 
	{$limit}
	GROUP BY A.CPM_CG_ID"; 
	// echo $query.'<br/>';
	
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		// print_r($row);[]
		$return["NAMA_GRUP"]     = $row["CPM_CG_NAME"];
		$return["NAMA_KOLEKTOR"] = $row["CPM_CG_COLLECTOR"];
		$return["NO_KOLEKTOR"]   = $row["CPM_CG_HP_COLLECTOR"];
		$return["KECAMATAN"]     = $row["CPC_TKC_KECAMATAN"];
		$return["KELURAHAN"]     = $row["CPC_TKL_KELURAHAN"];
		$return["KODE BAYAR"]    = $row["CPM_CG_PAYMENT_CODE"];
		$return["NOP"]           = $row["CPM_CG_NOP_NUMBER"];
		$return["POKOK"]         = $row["CPM_CG_ORIGINAL_AMOUNT"];
		$return["DENDA"]         = $row["CPM_CG_PENALTY_FEE"];
		$return["TOTAL"]         = $row["TOTAL"];
		$return["TGL"]           = $row["CPM_CG_PAY_DATE"];

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
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring table-responsive\">";
	$html 		.= headerPenetapan ();
	$row 		= (($page-1) > 0 ? ($page-1) : 0) * $perpage;
	$no			= ($row+1);
	if($c!=0){
		for($i=0;$i<$c;$i++){
			$html .= "
						<tr>
							<td align=\"right\">".$no."</td>
							<td align=\"center\">".$data[$i]['NAMA_GRUP']."</td>
							<td>".$data[$i]['NAMA_KOLEKTOR']."</td>
							<td>".$data[$i]['NO_KOLEKTOR']."</td>
							<td>".$data[$i]['KECAMATAN']."</td>
							<td>".$data[$i]['KELURAHAN']."</td>
							<td>".$data[$i]['KODE BAYAR']."</td>
							<td>".$data[$i]['NOP']."</td>
							<td align=\"right\">".number_format($data[$i]['POKOK'],0,',','.')."</td>
							<td align=\"right\">".number_format($data[$i]['DENDA'],0,',','.')."</td>
							<td align=\"right\">".number_format($data[$i]['TOTAL'],0,',','.')."</td>
							<td>".$data[$i]['TGL']."</td>

						</tr>
						";
		$no++;
		}
	} else {
		$html .= "
					<tr>
						<td colspan=\"12\" align=\"center\">Tidak ada data.</td>
					</tr>";
	}
		  
	return $html.= "	<tr>
							<td colspan=\"12\" align=\"center\">
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
$page      = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
$kab       = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn       = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec   = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel   = @isset($_REQUEST['nk']) ? $_REQUEST['nk'] : "";
$kode      = @isset($_REQUEST['kode']) ? $_REQUEST['kode'] : "";
$tempo1    = @isset($_REQUEST['tempo1']) ? $_REQUEST['tempo1'] : "";
$tempo2    = @isset($_REQUEST['tempo1']) ? $_REQUEST['tempo2'] : "";

// print_r($_REQUEST);
$arrTempo = array();

// if ($tempo1!="") array_push($arrTempo,"A.CPM_CG_PAY_DATE>='{$tempo1} 00:00:00'");
// if ($tempo2!="") array_push($arrTempo,"A.CPM_CG_PAY_DATE<='{$tempo2} 23:59:59'");

if ($tempo1!="") array_push($arrTempo,"DATE(SPPT.PAYMENT_PAID)>='{$tempo1}'");
if ($tempo2!="") array_push($arrTempo,"DATE(SPPT.PAYMENT_PAID)<='{$tempo2}'");

$tempo = implode (" AND ",$arrTempo);
$limit = "";

$arrWhere = array();
// if ($kecamatan !="") {
// 	if ($kelurahan !="") array_push($arrWhere,"NOP like '{$kelurahan}%'");
// 	else array_push($arrWhere,"NOP like '{$kecamatan}%'");
// }
// if ($thn!=""){
//     array_push($arrWhere,"SPPT_TAHUN_PAJAK ='{$thn}'");   
// } 

// if ($kode!="") array_push($arrWhere,"CPM_CG_PAYMENT_CODE =".$kode);
if ($kode!="") array_push($arrWhere,"SPPT.COLL_PAYMENT_CODE = '{$kode}'");
if ($tempo1!="") array_push($arrWhere,"({$tempo})");


array_push($arrWhere," A.CPM_CG_STATUS = '2'");

$where = implode (" AND ",$arrWhere);

$limit = ($kode != "" || $tempo1 != "" || $tempo2 != "" ) ? "" : " LIMIT {$page},{$perpage} ";

// print_r($where);exit;
echo showTable ();
?>