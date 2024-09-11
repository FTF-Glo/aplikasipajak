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

function headerRekapPokok ($mod,$nama) {
	global $appConfig;
	// print_r($appConfig);
	$model = ($mod==0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod==0) { 
		$dl = $appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table class=\"table table-bordered table-striped\" style=\"width:600px\">
	<tr>
		<th colspan=5><b>RINCIAN SPPT DAN DHKP TAHUN ".$appConfig['tahun_tagihan']."<br>SEKTOR PEDESAAN DAN PERKOTAAN<b></th>
	</tr>
	  <tr>
		<th width=28>NO</th>
		<th>{$model}</th>
		<th width=70>SPPT<br>(LEMBAR)</th>
		<th width=60>DHKP<br>(BUKU)</th>
		<th>JUMLAH PAJAK<br>TERHUTANG(Rp)</th>
		<!-- <th>KETERANGAN</th> -->
	  </tr>";
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


function getData($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s,$qBuku;
    
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
		$dCount 			= getCount($kec[$i]["id"]);
		
		$data[$i]["SPPT"] 		= $dCount["SPPT"];
		$data[$i]["DHKP"] 		= $dCount["DHKP"];
		$data[$i]["TERHUTANG"]  = $dCount["TERHUTANG"];
	}
	
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt 			= getData($mod);

	$c 			= count($dt);
	$html 		= '<div class="tbl-monitoring responsive">';
	$a=1;
	$html .= headerRekapPokok ($mod,$nama);
	$summary = array('name'=>'TOTAL','sppt'=>0, 'dhkp'=>0, 'pbb_pedesaan'=>0, 'terhutang'=>0);
        for ($i=0;$i<$c;$i++) {
				
				$dtname 		= $dt[$i]["name"];
				
                $sppt 		= number_format($dt[$i]["SPPT"],0,",",".");
				$dhkp		= number_format($dt[$i]["DHKP"],0,",",".");
				$terhutang 	= number_format($dt[$i]["TERHUTANG"],0,",",".");
          
                $html .= " <tr>
	            <td align=\"right\">{$a}</td>
	            <td>{$dtname}</td>
	            <td align=\"right\">{$sppt}</td>
	            <td align=\"right\">{$dhkp}</td>
	            <td align=\"right\">{$terhutang}</td>
	            <!-- <td align=\"right\"></td> -->
	          </tr>";
		  
				$summary['sppt']		+= $dt[$i]["SPPT"];
				$summary['dhkp'] 		+= $dt[$i]["DHKP"];
				$summary['terhutang'] 	+= $dt[$i]["TERHUTANG"];
				
          $a++;
        }

		$html .= " <tr>
            <td align=\"right\"> </td>
            <td>".$summary['name']."</td>
            <td align=\"right\">".number_format($summary['sppt'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['dhkp'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['terhutang'],0,',','.')."</td>
          </tr>";
		  
	return $html."</table>";
}

function getCount($kec) {
	global $myDBLink,$kd,$thn,$bulan, $qBuku;

	$myDBLink = openMysql();
	$return=array();
	$return["SPPT"]=0;
	$return["DHKP"]=0;
	$return["TERHUTANG"]=0;
	$query = "SELECT
					COUNT(*) AS SPPT,
					SUM(SPPT_PBB_HARUS_DIBAYAR) AS TERHUTANG,
					COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS DHKP
				FROM
					pbb_sppt
				WHERE
				SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kec}%' {$qBuku} "; 
	//echo $query.'<br/>';EXIT;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["SPPT"]		=($row["SPPT"]!="")?$row["SPPT"]:0;
		$return["DHKP"]		=($row["DHKP"]!="")?$row["DHKP"]:0;
		$return["TERHUTANG"]=($row["TERHUTANG"]!="")?$row["TERHUTANG"]:0;
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
$buku 				= @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : "";
// print_r($_REQUEST);exit;
// $arrWhere = array();

// if ($kecamatan !="") {
        // array_push($arrWhere,"nop like '{$kecamatan}%'");
// }

// if ($thn!=""){
    // array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");  
    // array_push($arrWhere,"payment_paid like '{$thn}%'");  
// } 
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
// echo $qBuku;
// $where = implode (" AND ",$arrWhere);

if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1,$nama);
}
