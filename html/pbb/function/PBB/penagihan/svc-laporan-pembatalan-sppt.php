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
$kd 		= $appConfig['KODE_KOTA'];

$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$sp			= @isset($_REQUEST['sp']) ? $_REQUEST['sp'] : 0;
$t1			= @isset($_REQUEST['t1']) ? $_REQUEST['t1'] : "";
$t2			= @isset($_REQUEST['t2']) ? $_REQUEST['t2'] : "";
$nkc 		= @isset($_REQUEST['nkc']) ? $_REQUEST['nkc'] : "";
$cb_batal   = @isset($_REQUEST['cb_batal']) ? $_REQUEST['cb_batal'] : 0;

switch ($cb_batal){
case 3 : $txtJnsPembatalan = "PEMBATALAN SPPT PBB";
		 break;
case 4 : $txtJnsPembatalan = "WP YANG ALAMATNYA TIDAK DITEMUKAN";
		 break;
case 5 : $txtJnsPembatalan = "TANAH YANG SENGKETA";
		 break;
case 6 : $txtJnsPembatalan = "WP SUDAH PERUBAHAN DATA";
		 break;
}

$host = $appConfig['GW_DBHOST'];
$port = $appConfig['GW_DBPORT'];
$user = $appConfig['GW_DBUSER'];
$pass = $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];
$myDBLink ="";

function headerMonitoringE2 () {
	global $appConfig, $nkc, $sp, $txtJnsPembatalan;
	
	if($sp==1)
		$noSP = "I";
	else if($sp==2)
		$noSP = "II";
	else
		$noSP = "III";
	
	if($nkc!="Pilih Semua"){
		$kcm = "KECAMATAN ".$nkc;
	} else {
		$kcm = strtoupper($appConfig['C_KABKOT'])." ".strtoupper($appConfig['kota']);
	}
	$html = "
	<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
	<tbody>
	   <tr>
		  <td class=\"tdheader\" colspan=\"15\" align=\"center\"><b>DAFTAR ".($txtJnsPembatalan!="" ? $txtJnsPembatalan."<br>SETELAH PENERBITAN SURAT PEMBERITAHUAN $noSP" : "SP$sp YANG BERMASALAH")."</b></td>
	   </tr>
	   <tr>
		<td class=\"tdheader\" width=\"28\" align=\"center\">NO</td>
		<td class=\"tdheader\" width=\"117\" align=\"center\">NOP</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">NAMA</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">ALAMAT WP</td>
		<td class=\"tdheader\" width=\"100\" align=\"center\">RT WP</td>
		<td class=\"tdheader\" width=\"100\" align=\"center\">RW WP</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">KELURAHAN WP</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">ALAMAT OP</td>
		<td class=\"tdheader\" width=\"100\" align=\"center\">RT OP</td>
		<td class=\"tdheader\" width=\"100\" align=\"center\">RW OP</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">KELURAHAN OP</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">KECAMATAN OP</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">KETETAPAN</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">TAHUN PAJAK</td>
		<td class=\"tdheader\" width=\"136\" align=\"center\">KETERANGAN</td>
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
	
	// 1 => "SP1 yang sudah diterima Wajib Pajak",
	// 2 => "Wajib Pajak yang sudah membayar PBB setelah penerbitaan SP 1",
	// 3 => "Data Wajib Pajak yang dibatalkan",
	// 4 => "Alamat tidak ditemukan",
	// 5 => "Tanah sengketa",
	// 6 => "Wajib Pajak sudah melakukan perubahan data"

	$c = count($dt);
	$html = "";
	$a=1;
	$html = headerMonitoringE2 ();
	$summary = array('TOTAL_KETETAPAN'=>0,'DITERIMA'=>0,'DIKEMBALIKAN'=>0);
        for ($i=0;$i<$c;$i++) {
				$class = $i%2==0 ? "tdbody1":"tdbody2";
                $html .= " <tr>
	            <td align=\"right\" class=\"".$class."\">{$a}</td>
	            <td class=\"".$class."\">".$dt[$i]['NOP']."</td>
	            <td class=\"".$class."\" width=\"200\">".$dt[$i]['NAMA']."</td>
				<td class=\"".$class."\" width=\"210\">".$dt[$i]['ALAMATWP']."</td>
				<td class=\"".$class."\" width=\"100\">".$dt[$i]['RTWP']."</td>
				<td class=\"".$class."\" width=\"100\">".$dt[$i]['RWWP']."</td>
				<td class=\"".$class."\" width=\"210\">".$dt[$i]['KELURAHANWP']."</td>
	            <td class=\"".$class."\" width=\"210\">".$dt[$i]['ALAMAT']."</td>
				<td class=\"".$class."\" width=\"100\">".$dt[$i]['RTOP']."</td>
				<td class=\"".$class."\" width=\"100\">".$dt[$i]['RWOP']."</td>
				<td class=\"".$class."\" width=\"210\">".$dt[$i]['KELURAHANOP']."</td>
				<td class=\"".$class."\" width=\"210\">".$dt[$i]['KECAMATANOP']."</td>
	            <td class=\"".$class."\" align=\"right\">".number_format($dt[$i]['KETETAPAN'],0,',','.')."</td>
	            <td class=\"".$class."\" align=\"center\">".$dt[$i]['TAHUNSP']."</td>
				<td class=\"".$class."\">".$dt[$i]['KETERANGAN']."&nbsp;</td>
	          </tr>";
			  
			  $summary['TOTAL_KETETAPAN'] += $dt[$i]['KETETAPAN'];
			  $summary['DITERIMA'] = $c;
			  
			  if($dt[$i]['KETERANGAN']){
				$summary['DIKEMBALIKAN'] += 1;
			  }  
				
          $a++;
        }
		
		$html .= " 
		<tr>
            <td align=\"center\" colspan=\"13\">
				TOTAL KETETAPAN
			</td>
			<td align=\"right\">
				".number_format($summary['TOTAL_KETETAPAN'],0,',','.')."
			</td>
			<td colspan=\"2\">&nbsp;</td>
        </tr>";
		  
	return $html."</tbody></table>";
}

function getData() {
	global $myDBLink,$kd,$thn,$sp,$kecamatan,$t1,$t2,$cb_batal;

	$myDBLink = openMysql();
	
	$qSP = "";
	switch ($sp){
		case 1 : $qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NULL OR A.TGL_SP2 = '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
				 $fieldKet = "KETERANGAN_SP1";
				 $fieldThn = "TAHUN_SP1";
				 $fieldTgl = "TGL_SP1";
				 break;
		case 2 : $qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NULL OR A.TGL_SP3 = '')";
				 $fieldKet = "KETERANGAN_SP2";
				 $fieldThn = "TAHUN_SP2";
				 $fieldTgl = "TGL_SP2";
				 break;
		case 3 : $qSP .= "AND (A.TGL_SP1 IS NOT NULL OR A.TGL_SP1 <> '') AND (A.TGL_SP2 IS NOT NULL OR A.TGL_SP2 <> '') AND (A.TGL_SP3 IS NOT NULL OR A.TGL_SP3 <> '')";
				 $fieldKet = "KETERANGAN_SP3";
				 $fieldThn = "TAHUN_SP3";
				 $fieldTgl = "TGL_SP3";
				 break;
	}
	
	$arrWhere = array();
	if ($kecamatan !=""){
		array_push($arrWhere,"A.NOP LIKE '{$kecamatan}%'");
	}
	if ($thn!=""){
		array_push($arrWhere,"A.$fieldThn LIKE '%{$thn}%'");   
	}
	if (($t1) && ($t2)){
		array_push($arrWhere,"A.$fieldTgl >= '$t1' AND A.$fieldTgl <= '$t2'");
	}
	if ($cb_batal!=0){
		array_push($arrWhere,"A.STATUS_SP = $cb_batal");
	}
	$where = implode (" AND ",$arrWhere);
	
	$whr = "";
	if($where) {
		$whr .=" AND $where";
	}	
	
	$query = "SELECT
					*, SUM(SPPT_PBB_HARUS_DIBAYAR) AS KETETAPAN
				FROM
					(
						SELECT
							A.NOP,
							C.WP_NAMA,
							C.WP_ALAMAT,
							C.WP_RT,
							C.WP_RW,
							C.WP_KELURAHAN,
							C.OP_ALAMAT,
							C.OP_RT,
							C.OP_RW,
							C.OP_KELURAHAN,
							C.OP_KECAMATAN,
							C.SPPT_PBB_HARUS_DIBAYAR,
							A.$fieldTgl,
							A.$fieldThn,
							A.$fieldKet,
							A.STATUS_SP,
							A.STATUS_PERSETUJUAN
						FROM
							PBB_SPPT_PENAGIHAN A
						JOIN PBB_SPPT C
						WHERE
							A.NOP = C.NOP AND A.STATUS_SP NOT IN ('0','1','2')
						$qSP $whr
					) AS PENAGIHAN
				GROUP BY
					NOP"; 
				// echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res )) {
		$data[$i]["NOP"] 		= $row["NOP"];
		$data[$i]["NAMA"] 		= $row["WP_NAMA"];
		$data[$i]["ALAMATWP"] 	= $row["WP_ALAMAT"];
		$data[$i]["RTWP"] 		= $row["WP_RT"];
		$data[$i]["RWWP"] 		= $row["WP_RW"];
		$data[$i]["KELURAHANWP"]= $row["WP_KELURAHAN"];
		$data[$i]["ALAMAT"] 	= $row["OP_ALAMAT"];
		$data[$i]["RTOP"] 		= $row["OP_RT"];
		$data[$i]["RWOP"] 		= $row["OP_RW"];
		$data[$i]["KELURAHANOP"]= $row["OP_KELURAHAN"];
		$data[$i]["KECAMATANOP"]= $row["OP_KECAMATAN"];
		$data[$i]["KETETAPAN"] 	= $row["KETETAPAN"];
		$data[$i]["TAHUNSP"] 	= $row["$fieldThn"];
		$data[$i]["KETERANGAN"]	= $row["$fieldKet"];
		$i++;
	}
	closeMysql($myDBLink);
	return $data;
}

if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1);
}
