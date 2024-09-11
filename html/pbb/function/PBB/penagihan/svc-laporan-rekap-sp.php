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
$kd 		= $appConfig['KODE_KOTA'];

$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$sp			= @isset($_REQUEST['sp']) ? $_REQUEST['sp'] : 0;
$nkc 		= @isset($_REQUEST['nkc']) ? $_REQUEST['nkc'] : "";
$t1			= @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$t2			= @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";

$host = $appConfig['GW_DBHOST'];
$port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : "";
$user = $appConfig['GW_DBUSER'];
$pass = $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];
$myDBLink ="";

function headerMonitoringE2 () {
	global $appConfig,$nkc,$kab,$sp;
	
	if($sp==1)
		$noSP = "I";
	else if($sp==2)
		$noSP = "II";
	else
		$noSP = "III";
	
	$kec = getKecamatan($kab);
	$jmlKec = count($kec);
	
	if($nkc!="Pilih Semua"){
		$kcm = "KECAMATAN ".$nkc;
	} else {
		$kcm = strtoupper($appConfig['C_KABKOT'])." ".strtoupper($appConfig['kota']);
	}
	$html = "
	<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
	<tbody>
	   <tr>
		  <td class=\"tdheader\" colspan=\"6\" align=\"center\"><b>DAFTAR REKAP PENYAMPAIAN SURAT PEMBERITAHUAN $noSP<br>DI ".$jmlKec." KECAMATAN<b></td>
	   </tr>
	   <tr>
		<td class=\"tdheader\" width=\"28\" align=\"center\">NO</td>
		<td class=\"tdheader\" width=\"117\" align=\"center\">KECAMATAN</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">TOTAL SP".$sp." YANG DITERBITKAN</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">TOTAL SP".$sp." YANG BELUM DIKEMBALIKAN</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">TOTAL SP".$sp." YANG DIKEMBALIKAN</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">TOTAL KETETAPAN SP".$sp."</td>
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

function showTable ($mod=0,$nama="") {
	global $eperiode,$sp;
	$dt = getData();

	$c = count($dt);
	$html = "";
	$a=1;
	$html = headerMonitoringE2 ();
	$summary = array('SUM_TOTAL_SP_TERBIT'=>0,'SUM_TOTAL_SP_BELUM_KEMBALI'=>0,'SUM_TOTAL_SP_KEMBALI'=>0,'SUM_KETETAPAN'=>0);
        for ($i=0;$i<$c;$i++) {
				$class = $i%2==0 ? "tdbody1":"tdbody2";
                $html .= " <tr>
	            <td align=\"right\" class=\"".$class."\">{$a}</td>
	            <td class=\"".$class."\">".$dt[$i]['KECAMATAN']."</td>
	            <td class=\"".$class."\" align=\"right\">".$dt[$i]['TOTAL_SP_TERBIT']."</td>
	            <td class=\"".$class."\" align=\"right\">".$dt[$i]['TOTAL_SP_BELUM_KEMBALI']."</td>
	            <td class=\"".$class."\" align=\"right\">".$dt[$i]['TOTAL_SP_KEMBALI']."</td>
	            <td class=\"".$class."\" align=\"right\">".number_format($dt[$i]['KETETAPAN'],0,',','.')."</td>
	          </tr>";
			  
			  $summary['SUM_TOTAL_SP_TERBIT'] 		 += $dt[$i]['TOTAL_SP_TERBIT'];
			  $summary['SUM_TOTAL_SP_BELUM_KEMBALI'] += $dt[$i]['TOTAL_SP_BELUM_KEMBALI'];
			  $summary['SUM_TOTAL_SP_KEMBALI'] 		 += $dt[$i]['TOTAL_SP_KEMBALI'];
			  $summary['SUM_KETETAPAN'] 		 	 += $dt[$i]['KETETAPAN'];
				
          $a++;
        }
		
		$html .= " 
		<tr>
            <td align=\"center\" colspan=\"2\">
				TOTAL
			</td>
			<td align=\"right\">".number_format($summary['SUM_TOTAL_SP_TERBIT'],0,',','.')."</td>
			<td align=\"right\">".number_format($summary['SUM_TOTAL_SP_BELUM_KEMBALI'],0,',','.')."</td>
			<td align=\"right\">".number_format($summary['SUM_TOTAL_SP_KEMBALI'],0,',','.')."</td>
			<td align=\"right\">".number_format($summary['SUM_KETETAPAN'],0,',','.')."</td>
        </tr>";
		  
	return $html."</tbody></table>";
}

function getJmlSP($qSP,$where,$kecKode){
	global $myDBLink;

	$myDBLink = openMysql();
	$return["TOTAL_SP_TERBIT"]=0;
	
	$whr="";
	if($where) {
		$whr =" AND $where";
	}	
	
	$query = "SELECT COUNT(*) AS TOTAL_SP_TERBIT
                FROM
                        PBB_SPPT_PENAGIHAN A
                WHERE
                A.NOP LIKE '$kecKode%' $whr $qSP"; 
	// echo $query."<br>";
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$row = mysqli_fetch_assoc($res);

	$return["TOTAL_SP_TERBIT"]=($row["TOTAL_SP_TERBIT"]!="")?$row["TOTAL_SP_TERBIT"]:0;
		
	// closeMysql($myDBLink);
	return $return["TOTAL_SP_TERBIT"];
}

function getSPDikembalikan($qSP,$where,$kecKode){
	global $myDBLink, $fieldKet;

	$myDBLink = openMysql();
	$return["TOTAL_SP_KEMBALI"]=0;
	
	$whr="";
	if($where) {
		$whr =" AND {$where}";
	}	
	
	$query = "SELECT
					COUNT(DISTINCT(A.NOP)) AS TOTAL_SP_KEMBALI
				FROM
					PBB_SPPT_PENAGIHAN A
					JOIN PBB_SPPT C
				WHERE
					A.NOP = C.NOP
				AND C.OP_KECAMATAN_KODE = '$kecKode' $qSP $whr AND ($fieldKet <> '' OR $fieldKet IS NOT NULL)
				ORDER BY
					C.SPPT_TAHUN_PAJAK DESC"; 
		
	// echo $query."<br>";
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$row = mysqli_fetch_assoc($res);

	$return["TOTAL_SP_KEMBALI"]=($row["TOTAL_SP_KEMBALI"]!="")?$row["TOTAL_SP_KEMBALI"]:0;
		
	// closeMysql($myDBLink);
	return $return["TOTAL_SP_KEMBALI"];
}

function getKetetapan($qSP,$where,$kecKode){
	global $myDBLink, $fieldKet,$fieldKetetapan;

	$myDBLink = openMysql();
	$return["TOTAL_SP_KEMBALI"]=0;
	
	$whr="";
	if($where) {
		$whr =" AND {$where}";
	}	
	
//	$query = "SELECT
//					SUM(A.$fieldKetetapan) AS KETETAPAN
//				FROM
//					PBB_SPPT_PENAGIHAN A
//				LEFT JOIN PBB_SPPT_TAHUN_PENAGIHAN B ON A.ID = B.ID
//				JOIN PBB_SPPT C
//				WHERE
//					A.NOP = C.NOP
//				AND B.SPPT_TAHUN_PAJAK = C.SPPT_TAHUN_PAJAK
//				AND C.OP_KECAMATAN_KODE = '$kecKode' $qSP $whr
//				ORDER BY
//					B.SPPT_TAHUN_PAJAK DESC"; 
	$query = "SELECT
                    SUM(A.$fieldKetetapan) AS KETETAPAN
                FROM
					PBB_SPPT_PENAGIHAN A
                WHERE
                A.NOP LIKE '$kecKode%' $whr $qSP";
	 
	// echo $query;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$row = mysqli_fetch_assoc($res);

	$return["KETETAPAN"]=($row["KETETAPAN"]!="")?$row["KETETAPAN"]:0;
		
	// closeMysql($myDBLink);
	return $return["KETETAPAN"];
}

function getData() {
	global $myDBLink,$kd,$bulan,$thn,$sp,$kecamatan,$kab,$qSP,$where;

	$myDBLink = openMysql();
	
	$kec = getKecamatan($kab);
	
	$data = array();
	$c 	  = count($kec);
	$i=0;
	for($i=0;$i<$c;$i++) {
		$data[$i]["KECAMATAN"] 		 		= $kec[$i]['name'];
		$data[$i]["TOTAL_SP_TERBIT"] 		= getJmlSP($qSP,$where,$kec[$i]['id']);
		$data[$i]["TOTAL_SP_KEMBALI"] 		= getSPDikembalikan($qSP,$where,$kec[$i]['id']);
		$data[$i]["TOTAL_SP_BELUM_KEMBALI"] = $data[$i]["TOTAL_SP_TERBIT"] - $data[$i]["TOTAL_SP_KEMBALI"];
		$data[$i]["KETETAPAN"]		 		= getKetetapan($qSP,$where,$kec[$i]['id']);
	}
	closeMysql($myDBLink);
	return $data;
}

$qSP = "";
switch ($sp){
case 1 : $qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
		 $fieldKet = "KETERANGAN_SP1";
		 $fieldThn = "TAHUN_SP1";
		 $fieldTgl = "TGL_SP1";
		 $fieldKetetapan = "KETETAPAN_SP1";
		 break;
case 2 : $qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
		 $fieldKet = "KETERANGAN_SP2";
		 $fieldThn = "TAHUN_SP2";
		 $fieldTgl = "TGL_SP2";
		 $fieldKetetapan = "KETETAPAN_SP2";
		 break;
case 3 : $qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NOT NULL OR A.TGL_SP3 <> '')";
		 $fieldKet = "KETERANGAN_SP3";
         $fieldThn = "TAHUN_SP3";
		 $fieldTgl = "TGL_SP3";
		 $fieldKetetapan = "KETETAPAN_SP3";
		 break;
}

$arrWhere = array();
	if ($thn!=""){
		array_push($arrWhere,"A.$fieldThn LIKE '%{$thn}%'");   
	}
	if (($t1) && ($t2)){
		array_push($arrWhere,"A.$fieldTgl >= '$t1' AND A.$fieldTgl <= '$t2'");
	}
$where = implode (" AND ",$arrWhere);



if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1);
}
