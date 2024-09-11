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

function headerRealisasiPP ($mod,$nama) {
	global $appConfig;
	$model = ($mod==0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod==0) { 
		$dl = $appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table class=\"table table-bordered table-striped\" style=\"width:1000px\"><tr><th colspan=15><b>{$dl}<b></th></tr>
	  <tr>
		<th rowspan=2 width=10>NO</th>
		<th rowspan=2>{$model}</th>
		<th colspan=2>TARGET POTENSI</th>
		<th rowspan=2>JUMLAH</th>
		<th colspan=2>REALISASI</th>
		<th rowspan=2>JUMLAH</th>
		<th rowspan=2>%</th>
	  </tr>
	  <tr>
		<th width=120>PEDESAAN</td>
		<th width=120>PERKOTAAN</td>
		<th width=120>PEDESAAN</td>
		<th width=120>PERKOTAAN</td>
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
	global $DBLink,$kelurahan;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
	// echo $query."<br>";
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

function lastDay($month = '', $year = '') {
   if (empty($month)) {
      $month = date('m');
   }
   if (empty($year)) {
      $year = date('Y');
   }
   $result = strtotime("{$year}-{$month}-01");
   $result = strtotime('-1 second', strtotime('+1 month', $result));
   return date('Y-m-d', $result).' 23:59:59';
}

//get tanggal awal pada bulan
function firstDay($month = '', $year = '')
{
   if (empty($month)) {
      $month = date('m');
   }
   if (empty($year)) {
      $year = date('Y');
   }
   $result = strtotime("{$year}-{$month}-01");
   return date('Y-m-d', $result).' 00:00:00';
} 

function getData($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s,$qBuku,$eperiode;
	if ($mod==0) $kec =  getKecamatan($kab);
	else {
		if($kelurahan)
			$kec = getKelurahan($kelurahan);
		else 
			$kec = getKelurahan($kecamatan);
	}
	
	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] 	= $kec[$i]["name"];
		$data[$i]["id"] 	= $kec[$i]["id"];
		$targetPedesaan 	= getTargetPedesaan($kec[$i]["id"],$eperiode);
		$targetPerkotaan	= getTargetPerkotaan($kec[$i]["id"],$eperiode);
		$realisasiPedesaan	= getRealisasiPedesaan($kec[$i]["id"],$eperiode);
		$realisasiPerkotaan	= getRealisasiPerkotaan($kec[$i]["id"],$eperiode);
		
		$data[$i]["TARGET_PEDESAAN"] 		= $targetPedesaan["TARGET"];
		$data[$i]["TARGET_PERKOTAAN"] 		= $targetPerkotaan["TARGET"];
		$data[$i]["REALISASI_PEDESAAN"] 	= $realisasiPedesaan["REALISASI"];
		$data[$i]["REALISASI_PERKOTAAN"] 	= $realisasiPerkotaan["REALISASI"];
		
	}
	
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt 			= getData($mod);

	$c 			= count($dt);
	$html 		= '<div class="tbl-monitoring responsive">';
	$a=1;
	$html .= headerRealisasiPP ($mod,$nama);
	$summary = array('name'=>'TOTAL','target_pedesaan'=>0, 'realisasi_pedesaan'=>0, 'target_perkotaan'=>0, 'realisasi_perkotaan'=>0, 'total_target'=>0, 'total_realisasi'=>0,'total_persen'=>0);
        for ($i=0;$i<$c;$i++) {
				
				$dtname 		= $dt[$i]["name"];
				
                $target_pedesaan 		= $dt[$i]["TARGET_PEDESAAN"];
				$target_perkotaan 		= $dt[$i]["TARGET_PERKOTAAN"];
				$sumTarget				= $dt[$i]["TARGET_PEDESAAN"]+$dt[$i]["TARGET_PERKOTAAN"];
				
				$realisasi_pedesaan 	= $dt[$i]["REALISASI_PEDESAAN"];
				$realisasi_perkotaan 	= $dt[$i]["REALISASI_PERKOTAAN"];
				$sumRealisasi			= $dt[$i]["REALISASI_PEDESAAN"]+$dt[$i]["REALISASI_PERKOTAAN"];
				
				$percent	= ($sumRealisasi != 0 && $sumTarget != 0) ? ($sumRealisasi/$sumTarget*100) : 0;
          
                $html .= " <tr>
	            <td align=\"right\">{$a}</td>
	            <td>{$dtname}</td>
	            <td align=\"right\">".number_format($target_pedesaan,0,",",".")."</td>
	            <td align=\"right\">".number_format($target_perkotaan,0,",",".")."</td>
	            <td align=\"right\">".number_format($sumTarget,0,",",".")."</td>
	            <td align=\"right\">".number_format($realisasi_pedesaan,0,",",".")."</td>
	            <td align=\"right\">".number_format($realisasi_perkotaan,0,",",".")."</td>
	            <td align=\"right\">".number_format($sumRealisasi,0,",",".")."</td>
	            <td align=\"right\">".number_format($percent,2,",",".")."</td>
	          </tr>";
		  
				$summary['target_pedesaan']		+= $target_pedesaan;
				$summary['target_perkotaan'] 	+= $target_perkotaan;
				$summary['total_target'] 		+= $sumTarget;
				
				$summary['realisasi_pedesaan']	+= $realisasi_pedesaan;
				$summary['realisasi_perkotaan'] += $realisasi_perkotaan;
				$summary['total_realisasi'] 	+= $sumRealisasi;
				
          $a++;
        }
		$summary['total_persen'] = ($summary['total_realisasi'] != 0 && $summary['total_target'] != 0) ? ($summary['total_realisasi']/$summary['total_target']*100) : 0;
		$html .= " <tr>
            <td align=\"right\"> </td>
            <td>".$summary['name']."</td>
            <td align=\"right\">".number_format($summary['target_pedesaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['target_perkotaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['total_target'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['realisasi_pedesaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['realisasi_perkotaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['total_realisasi'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['total_persen'],2,',','.')."</td>
          </tr>";
		  
	return $html."</table>";
}

function getTargetPedesaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan, $appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["TARGET"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	$qPeriode="";
	if($periode!=0){
		$qPeriode = " AND SPPT_TANGGAL_TERBIT BETWEEN '{$firstMon}' AND '{$lastMon}' ";
	}
	
	$query = "SELECT
					SUM(A.SPPT_PBB_HARUS_DIBAYAR) AS TARGET
				FROM
					{$db_gw}.pbb_sppt A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '10' $qPeriode "; 
	//echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["TARGET"]	=($row["TARGET"]!="")?$row["TARGET"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getTargetPerkotaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan,$appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["TARGET"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	$qPeriode="";
	if($periode!=0){
		$qPeriode = " AND SPPT_TANGGAL_TERBIT BETWEEN '{$firstMon}' AND '{$lastMon}' ";
	}
	
	$query = "SELECT
					SUM(A.SPPT_PBB_HARUS_DIBAYAR) AS TARGET
				FROM
					{$db_gw}.pbb_sppt A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '20' $qPeriode ";
	// echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["TARGET"]	=($row["TARGET"]!="")?$row["TARGET"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getRealisasiPedesaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan, $appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["REALISASI"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	$lastMon1 = lastDay('12', $thn);
	$qPeriode="";
	IF($periode!=0){
		$qPeriode = " AND (PAYMENT_PAID BETWEEN '{$firstMon}' AND '{$lastMon}') ";
	} ELSE {
		$qPeriode = " AND (PAYMENT_PAID BETWEEN '{$firstMon}' AND '{$lastMon1}') ";
	} 
	
	IF($thn != ''){
		$query = "SELECT
					SUM(A.PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.pbb_sppt A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND NOP LIKE '{$kel}%'
				AND A.SPPT_TAHUN_PAJAK = '{$thn}'
				AND CPC_KD_SEKTOR = '10' 
				AND PAYMENT_FLAG = '1' $qPeriode ";
		
	} ELSE {
	$query = "SELECT
					SUM(A.PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.pbb_sppt A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '10' 
				AND PAYMENT_FLAG = '1' $qPeriode ";
	} 
	// echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["REALISASI"]	=($row["REALISASI"]!="")?$row["REALISASI"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getRealisasiPerkotaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan,$appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["REALISASI"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	$lastMon1 = lastDay('12', $thn);
	$qPeriode="";
	IF($periode!=0){
		$qPeriode = " AND (PAYMENT_PAID BETWEEN '{$firstMon}' AND '{$lastMon}') ";
	} ELSE {
		$qPeriode = " AND (PAYMENT_PAID BETWEEN '{$firstMon}' AND '{$lastMon1}') ";
	} 
	IF($thn != ''){
		$query = "SELECT
					SUM(A.PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.pbb_sppt A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND NOP LIKE '{$kel}%'
				AND A.SPPT_TAHUN_PAJAK = '{$thn}'
				AND CPC_KD_SEKTOR = '20' 
				AND PAYMENT_FLAG = '1' $qPeriode ";
		
	} ELSE {
	$query = "SELECT
					SUM(A.PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.pbb_sppt A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '20' 
				AND PAYMENT_FLAG = '1' $qPeriode ";
	}				
	// echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["REALISASI"]	=($row["REALISASI"]!="")?$row["REALISASI"]:0;
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

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$kd 				= $appConfig['KODE_KOTA'];
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 			= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 			= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama 				= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$eperiode 			= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan 	= @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";

// print_r($_REQUEST);exit;
$arrWhere = array();

if ($thn!=""){
    array_push($arrWhere,"SPPT_TAHUN_PAJAK='{$thn}'"); 
} 
$qBuku = "";
// if($buku != 0){
    // switch ($buku){
        // case 1 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
        // case 12 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
        // case 123 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
        // case 1234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
        // case 12345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
        // case 2 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
        // case 23 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
        // case 234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
        // case 2345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		// case 3 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		// case 34 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		// case 345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		// case 4 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		// case 45 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		// case 5 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
    // }
// }
// echo $qBuku;
$where = implode (" AND ",$arrWhere);

if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1,$nama);
}
