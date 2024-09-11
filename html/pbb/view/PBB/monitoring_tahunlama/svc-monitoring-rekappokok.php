<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring_wilayah', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 1);


$myDBLink ="";

function headerRekapPokok ($mod,$nama) {
	global $appConfig;
	$model = ($mod==0) ? "KECAMATAN" : strtoupper($appConfig['LABEL_KELURAHAN']);
	$dl = "";
	if ($mod==0) { 
		$dl = $appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
	<tr>
		<th colspan=\"15\"><b>{$dl}<b></td>
	</tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <tr>
		<th rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<th rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<th colspan=\"3\" width=\"136\" align=\"center\">PEDESAAN</td>
		<th colspan=\"3\" width=\"136\" align=\"center\">PERKOTAAN</td>
		<th colspan=\"3\" width=\"136\" align=\"center\">PEDESAAN - PERKOTAAN</td>
	  </tr>
	  <tr>
		<th align=\"center\">OP</td>
		<th align=\"center\">DHKP</td>
		<th align=\"center\">PBB</td>
		<th align=\"center\">OP</td>
		<th align=\"center\">DHKP</td>
		<th align=\"center\">PBB</td>
		<th align=\"center\">OP</td>
		<th align=\"center\">DHKP</td>
		<th align=\"center\">PBB</td>
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
		// echo "3rror"; 
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
		echo "string";
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res )) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}
	// print_r($data);
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
    
    $tahun = "";
	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
	
	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] 	= $kec[$i]["name"];
		$data[$i]["id"] 	= $kec[$i]["id"];
		$pedesaan 			= getOpPedesaan($kec[$i]["id"]);
		$perkotaan			= getOpPerkotaan($kec[$i]["id"]);
		// $dhkpPedesaan		= getCountDHKPPedesaan($data[$i]["id"]);
		// $dhkpPerkotaan		= getCountDHKPPerkotaan($data[$i]["id"]);
		
		$data[$i]["OP_PEDESAAN"] 	= $pedesaan["OP"];
		$data[$i]["DHKP_PEDESAAN"] 	= $pedesaan["DHKP"];
		$data[$i]["PBB_PEDESAAN"]  	= $pedesaan["PBB"];
		
		$data[$i]["OP_PERKOTAAN"] 	= $perkotaan["OP"];
		$data[$i]["DHKP_PERKOTAAN"] = $perkotaan["DHKP"];
		$data[$i]["PBB_PERKOTAAN"]  = $perkotaan["PBB"];
		
	}
	
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt 			= getData($mod);

	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$a=1;
	$html .= headerRekapPokok ($mod,$nama);
	$summary = array('name'=>'TOTAL','op_pedesaan'=>0, 'dhkp_pedesaan'=>0, 'pbb_pedesaan'=>0, 'op_perkotaan'=>0, 'dhkp_perkotaan'=>0, 'pbb_perkotaan'=>0, 'op_all'=>0,'dhkp_all'=>0, 'pbb_all'=>0);
        for ($i=0;$i<$c;$i++) {
				
				$dtname 		= $dt[$i]["name"];
				
                $opPedesaan 	= number_format($dt[$i]["OP_PEDESAAN"],0,",",".");
				$dhkpPedesaan 	= number_format($dt[$i]["DHKP_PEDESAAN"],0,",",".");
				$pbbPedesaan 	= number_format($dt[$i]["PBB_PEDESAAN"],0,",",".");
				
				$opPerkotaan 	= number_format($dt[$i]["OP_PERKOTAAN"],0,",",".");
				$dhkpPerkotaan 	= number_format($dt[$i]["DHKP_PERKOTAAN"],0,",",".");
				$pbbPerkotaan 	= number_format($dt[$i]["PBB_PERKOTAAN"],0,",",".");
				
				$op_all 		= $dt[$i]["OP_PEDESAAN"]+$dt[$i]["OP_PERKOTAAN"];
				$dhkp_all 		= $dt[$i]["DHKP_PEDESAAN"]+$dt[$i]["DHKP_PERKOTAAN"];
				$pbb_all 		= $dt[$i]["PBB_PEDESAAN"]+$dt[$i]["PBB_PERKOTAAN"];
				
				$opAll			= number_format($op_all,0,",",".");
				$dhkpall		= number_format($dhkp_all,0,",",".");
				$pbbAll			= number_format($pbb_all,0,",",".");
          
                $html .= " <tr>
	            <td align=\"right\">{$a}</td>
	            <td>{$dtname}</td>
	            <td align=\"right\">{$opPedesaan}</td>
	            <td align=\"right\">{$dhkpPedesaan}</td>
	            <td align=\"right\">{$pbbPedesaan}</td>
	            <td align=\"right\">{$opPerkotaan}</td>
	            <td align=\"right\">{$dhkpPerkotaan}</td>
	            <td align=\"right\">{$pbbPerkotaan}</td>
	            <td align=\"right\">{$opAll}</td>
	            <td align=\"right\">{$dhkpall}</td>
	            <td align=\"right\">{$pbbAll}</td>
	          </tr>";
		  
				$summary['op_pedesaan']		+= $dt[$i]["OP_PEDESAAN"];
				$summary['dhkp_pedesaan'] 	+= $dt[$i]["DHKP_PEDESAAN"];
				$summary['pbb_pedesaan'] 	+= $dt[$i]["PBB_PEDESAAN"];
				
				$summary['op_perkotaan']	+= $dt[$i]["OP_PERKOTAAN"];
				$summary['dhkp_perkotaan'] 	+= $dt[$i]["DHKP_PERKOTAAN"];
				$summary['pbb_perkotaan'] 	+= $dt[$i]["PBB_PERKOTAAN"];
				
				$summary['op_all']			+= $op_all;
				$summary['dhkp_all'] 		+= $dhkp_all;
				$summary['pbb_all'] 		+= $pbb_all;
				
          $a++;
        }

		$html .= " <tr>
            <td align=\"right\"> </td>
            <td>".$summary['name']."</td>
            <td align=\"right\">".number_format($summary['op_pedesaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['dhkp_pedesaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['pbb_pedesaan'],0,',','.')."</td>
			
            <td align=\"right\">".number_format($summary['op_perkotaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['dhkp_perkotaan'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['pbb_perkotaan'],0,',','.')."</td>
			
            <td align=\"right\">".number_format($summary['op_all'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['dhkp_all'],0,',','.')."</td>
            <td align=\"right\">".number_format($summary['pbb_all'],0,',','.')."</td>
          </tr>";
		  
	return $html."</table>";
}

function getOpPedesaan($kel) {
	global $myDBLink,$kd,$thn,$bulan, $appConfig,$where_plus;

	$myDBLink = openMysql();
	$return=array();
	$return["OP"]=0;
	$return["PBB"]=0;
	$return["DHKP"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	$query = "SELECT COUNT(*) AS OP, SUM(SPPT_PBB_HARUS_DIBAYAR) AS PBB, COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS DHKP FROM (
				SELECT
					A.NOP, A.SPPT_PBB_HARUS_DIBAYAR, C.CPC_KD_SEKTOR, A.OP_KELURAHAN_KODE
				FROM
					{$db_gw}.PBB_SPPT A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				) AS PBB 
				WHERE CPC_KD_SEKTOR = '10' $where_plus"; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["OP"]	=($row["OP"]!="")?$row["OP"]:0;
		$return["PBB"]	=($row["PBB"]!="")?$row["PBB"]:0;
		$return["DHKP"]	=($row["DHKP"]!="")?$row["DHKP"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getOpPerkotaan($kel) {
	global $myDBLink,$kd,$thn,$bulan,$where_plus;

	$myDBLink = openMysql();
	$return=array();
	$return["OP"]=0;
	$return["PBB"]=0;
	$return["DHKP"]=0;
	$query = "SELECT COUNT(*) AS OP, SUM(SPPT_PBB_HARUS_DIBAYAR) AS PBB, COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS DHKP FROM (
				SELECT
					A.NOP, A.SPPT_PBB_HARUS_DIBAYAR, C.CPC_KD_SEKTOR, A.OP_KELURAHAN_KODE
				FROM
					PBB_SPPT A
				JOIN cppmod_tax_kelurahan B
				JOIN cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				) AS PBB 
				WHERE CPC_KD_SEKTOR = '20' $where_plus "; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["OP"]	=($row["OP"]!="")?$row["OP"]:0;
		$return["PBB"]	=($row["PBB"]!="")?$row["PBB"]:0;
		$return["DHKP"]	=($row["DHKP"]!="")?$row["DHKP"]:0;
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

// echo "<p?pre>";

//echo $s;

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
// echo "<pre>";
// print_r($appConfig);
// echo "</pre>";
$kd 				= $appConfig['KODE_KOTA'];
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$buku 			= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "0";
$kecamatan 			= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan 			= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama 				= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$eperiode 			= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan 	= @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";

$arrWhere = array();
if($buku != 0){
    switch ($buku){
        case 1      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "); break;
        case 12     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "); break;
        case 123    : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
        case 1234   : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 12345  : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 2      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "); break;
        case 23     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
        case 234    : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 2345   : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 3      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "); break;
        case 34     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 345    : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 4      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "); break;
        case 45     : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
        case 5      : array_push($arrWhere," (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "); break;
    }
}
$where = implode (" AND ",$arrWhere);
$where_plus = " AND".$where;

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
// $where = implode (" AND ",$arrWhere);
// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";

if ($kecamatan=="" && $kelurahan=="") { 
	echo showTable ();
} else {
	echo showTable(1,$nama);
	echo $nama;
}
?>