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

function headerRealisasi ($mod,$nama) {
	global $appConfig, $thn;
	$html = "<center><b>REKAPAN KESELURUHAN (rekapan penetapan tahun berjalan dan tunggakan tahun-tahun sebelumnya)</b></center>
            <table class=\"table table-bordered table-striped\">
	  
	  <tr>
		<th rowspan=3>NO</th>
		<th rowspan=3>KECAMATAN</th>
		<th colspan=2>PENETAPAN ".$thn."</th>
		<th colspan=10>TUNGGAKAN PENETAPAN</th>
	  </tr>
	  <tr>
		<th rowspan=2>OP</th>
		<th rowspan=2>RP</th>
		<th colspan=2>".($thn-1)."</th>
		<th colspan=2>".($thn-2)."</th>
		<th colspan=2>".($thn-3)."</th>
		<th colspan=2>".($thn-4)."</th>
		<th colspan=2>".($thn-5)."</th>
	  </tr>
	  <tr>
		<th>OP</th>
		<th>RP</th>
		<th>OP</th>
		<th>RP</th>
		<th>OP</th>
		<th>RP</th>
		<th>OP</th>
		<th>RP</th>
		<th>OP</th>
		<th>RP</th>
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

function getRealisasi($mod) {
    global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$date_start,$date_end,$kab;

    //$periode = "and payment_paid between '{$date_start}' and '{$date_end}'"; //Antara tanggal 01 sampai tanggal 30 bulan sekarang
    $kec =  getKecamatan($kab);
    $c = count($kec);
    
    $strtahun = "SPPT_TAHUN_PAJAK in ('".$thn."'";
    for($t=$thn-1; $t >= $thn-5; $t--){
        $strtahun .= ",'".$t."'";
    }
    $strtahun .= ") ";
    $data = array();
    for ($i=0;$i<$c;$i++) {
            for($j=0; $j < 24; $j++){
                $data[$i][$j]='0';
            }
            $data[$i][0] = $i+1;
            $whr = " WHERE NOP like '".$kec[$i]["id"]."%' and (payment_flag!='1' OR payment_flag is null) and $strtahun ";
            $da = getData($whr);
            $data[$i][1] = $kec[$i]["name"];
            $data[$i][2] = $da[$thn]["wp"];
            $data[$i][3] = $da[$thn]["pokok"];
            $data[$i][4] = $da[$thn-1]["wp"];
            $data[$i][5] = $da[$thn-1]["pokok"];
            $data[$i][6] = $da[$thn-2]["wp"];
            $data[$i][7] = $da[$thn-2]["pokok"];
            $data[$i][8] = $da[$thn-3]["wp"];
            $data[$i][9] = $da[$thn-3]["pokok"];
            $data[$i][10] = $da[$thn-4]["wp"];
            $data[$i][11] = $da[$thn-4]["pokok"];
            $data[$i][12] = $da[$thn-5]["wp"];
            $data[$i][13] = $da[$thn-5]["pokok"];
    }
    return $data;
}

function showTable ($mod=0,$nama="") {
	global $thn;
	$dt = getRealisasi($mod);
	$dtall = array();
	
//	$dtsisa = getSisaSampaiBulanSekarang($mod);
	//$dtsisa = getSisaKetetapan($mod);
	$c = count($dt);
        
	$html = '<div class="tbl-monitoring responsive">';
	$a=1;
	$html .= headerRealisasi ($mod,$nama);

        $summary['2'] = 0;
        $summary['3'] = 0;
        $summary['4'] = 0;
        $summary['5'] = 0;
        $summary['6'] = 0;
        $summary['7'] = 0;
        $summary['8'] = 0;
        $summary['9'] = 0;
        $summary['10'] = 0;
        $summary['11'] = 0;
        $summary['12'] = 0;
        $summary['13'] = 0;
        
        for ($i=0;$i<$c;$i++) {
                $html .= " <tr>
	            <td align=\"left\">{$dt[$i][0]}</td>
	            <td align=\"left\">{$dt[$i][1]}</td>
	            <td align=\"right\">".number_format($dt[$i][2],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][3],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][4],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][5],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][6],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][7],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][8],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][9],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][10],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][11],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][12],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][13],0,',','.')."</td>
	          </tr>";
		  
                    $summary['2'] += $dt[$i][2];
                    $summary['3'] += $dt[$i][3];
                    $summary['4'] += $dt[$i][4];
                    $summary['5'] += $dt[$i][5];
                    $summary['6'] += $dt[$i][6];
                    $summary['7'] += $dt[$i][7];
                    $summary['8'] += $dt[$i][8];
                    $summary['9'] += $dt[$i][9];
                    $summary['10'] += $dt[$i][10];
                    $summary['11'] += $dt[$i][11];
                    $summary['12'] += $dt[$i][12];
                    $summary['13'] += $dt[$i][13];
                    
          $a++;
        }
        $html .= " <tr>
                <td align=\"left\"></td>
                <td align=\"center\">JUMLAH</td>
                <td align=\"right\">".number_format($summary['2'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['3'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['4'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['5'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['6'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['7'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['8'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['9'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['10'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['11'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['12'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['13'],0,',','.')."</td>
                </tr>";
	return $html."</table></div>";
}
function getData($where) {
	global $myDBLink,$thn;

	$myDBLink = openMysql();
	$return=array();
        for($t=$thn; $t >= $thn-5; $t--){
            $return[$t]["pokok"]=0;
            $return[$t]["wp"]=0;
        }
        
	$query = " SELECT COUNT(wp_nama) AS wp, sum(SPPT_PBB_HARUS_DIBAYAR) as pokok, SPPT_TAHUN_PAJAK FROM pbb_sppt 
                {$where}
                GROUP BY SPPT_TAHUN_PAJAK
                ORDER BY SPPT_TAHUN_PAJAK DESC";
    //echo $query."</br>";exit;
	$res = mysqli_query($myDBLink, $query); 
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$row["SPPT_TAHUN_PAJAK"]]["pokok"]=($row["pokok"]!="")?$row["pokok"]:0;
		$return[$row["SPPT_TAHUN_PAJAK"]]["wp"]=($row["wp"]!="")?$row["wp"]:0;
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
$kab = $appConfig['KODE_KOTA'];
$thn = $appConfig['tahun_tagihan'];
$date_start = @isset($_REQUEST['ds']) ? $_REQUEST['ds'] : "2014-01-01";
$date_end = @isset($_REQUEST['de']) ? $_REQUEST['de'] : "2014-05-30";
$date_start = $date_start.' 00:00:00';
$date_end = $date_end.' 23:59:59';
// echo $qBuku;
// $where = implode (" AND ",$arrWhere);

//if ($kecamatan=="") { 
	echo showTable ();
//} else {
//	echo showTable(1,$nama);
//}
