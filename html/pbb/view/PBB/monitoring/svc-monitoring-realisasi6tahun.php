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
        global $appConfig, $thn,$kecnama,$kec,$headerKec;
        // echo "ini".$kec;exit;
        $labelKec = ($headerKec != "")? 'KELURAHAN':'KECAMATAN';
        $headerKec = ($headerKec != "")? ' KECAMATAN '.$kecnama:'';
        
	$html = "<center><b>REALISASI (TAHUN BERJALAN DAN TUNGGAKAN)".$headerKec."</b></center>
            <table class=\"table table-bordered table-striped\">
	  
	  <tr>
		<th rowspan=4>NO</th>
		<th rowspan=4>".$labelKec."</th>
		<th colspan=2>TAHUN BERJALAN</th>
		<th colspan=42>TAHUN TUNGGAKAN</th>
	  </tr>
	  <tr>
		<th colspan=2>".$thn."</th>
		<th colspan=4>".($thn-1)."</th>
		<th colspan=4>".($thn-2)."</th>
		<th colspan=4>".($thn-3)."</th>
		<th colspan=4>".($thn-4)."</th>
                <th colspan=4>".($thn-5)."</th>
                <th colspan=4>".($thn-6)."</th>
		<th colspan=4>".($thn-7)."</th>
		<th colspan=4>".($thn-8)."</th>
		<th colspan=4>".($thn-9)."</th>
		<th colspan=4>".($thn-10)."</th>
	  </tr>
	  <tr>
                <th rowspan=2>OP</th>
		<th rowspan=2>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
                <th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
		<th rowspan=2>OP</th>
                <th colspan=3>RP</th>
	  </tr>
	  <tr>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
                <th>TOTAL</th>
                <th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
		<th>POKOK</th>
		<th>DENDA</th>
		<th>TOTAL</th>
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

function getRealisasi($mod) {
    global $DBLink,$kd,$kecnama,$kec,$thn,$date_start,$date_end,$kab;

        $periode ="";
        if($date_start != "" && $date_end != ""){
            $periode = "and payment_paid between '{$date_start}' and '{$date_end}'"; //Antara tanggal 01 sampai tanggal 30 bulan sekarang
        }
    
    if($kec)
        $kec = getKelurahan($kec);
    else 
        $kec =  getKecamatan($kab);
    
    $c = count($kec);
//     var_dump($c);exit;

    $strtahun = "SPPT_TAHUN_PAJAK in ('".$thn."'";
    for($t=$thn-1; $t >= $thn-10; $t--){
        $strtahun .= ",'".$t."'";
    }
    $strtahun .= ") ";
    $data = array();
    for ($i=0;$i<$c;$i++) {
            for($j=0; $j < 44; $j++){
                $data[$i][$j]='0';
            }
        //     var_dump($data);exit;
            $data[$i][0] = $i+1;
            $whr = " WHERE NOP like '".$kec[$i]["id"]."%' ".$periode." and payment_flag='1' and $strtahun ";
            $da = getData($whr);
        //     echo "<pre>";
        //     var_dump($da);
        //     echo "</pre>";
        //     exit;

            $data[$i][1] = $kec[$i]["name"];
            $data[$i][2] = $da[$thn]["wp"];
            $data[$i][3] = $da[$thn]["total"];
            $data[$i][4] = $da[$thn-1]["wp"];
            $data[$i][5] = $da[$thn-1]["pokok"];
            $data[$i][6] = $da[$thn-1]["denda"];
            $data[$i][7] = $da[$thn-1]["total"];
            $data[$i][8] = $da[$thn-2]["wp"];
            $data[$i][9] = $da[$thn-2]["pokok"];
            $data[$i][10] = $da[$thn-2]["denda"];
            $data[$i][11] = $da[$thn-2]["total"];
            $data[$i][12] = $da[$thn-3]["wp"];
            $data[$i][13] = $da[$thn-3]["pokok"];
            $data[$i][14] = $da[$thn-3]["denda"];
            $data[$i][15] = $da[$thn-3]["total"];
            $data[$i][16] = $da[$thn-4]["wp"];
            $data[$i][17] = $da[$thn-4]["pokok"];
            $data[$i][18] = $da[$thn-4]["denda"];
            $data[$i][19] = $da[$thn-4]["total"];
            $data[$i][20] = $da[$thn-5]["wp"];
            $data[$i][21] = $da[$thn-5]["pokok"];
            $data[$i][22] = $da[$thn-5]["denda"];
            $data[$i][23] = $da[$thn-5]["total"];
            $data[$i][24] = $da[$thn-6]["wp"];
            $data[$i][25] = $da[$thn-6]["pokok"];
            $data[$i][26] = $da[$thn-6]["denda"];
            $data[$i][27] = $da[$thn-6]["total"];
            $data[$i][28] = $da[$thn-7]["wp"];
            $data[$i][29] = $da[$thn-7]["pokok"];
            $data[$i][30] = $da[$thn-7]["denda"];
            $data[$i][31] = $da[$thn-7]["total"];
            $data[$i][32] = $da[$thn-8]["wp"];
            $data[$i][33] = $da[$thn-8]["pokok"];
            $data[$i][34] = $da[$thn-8]["denda"];
            $data[$i][35] = $da[$thn-8]["total"];
            $data[$i][36] = $da[$thn-9]["wp"];
            $data[$i][37] = $da[$thn-9]["pokok"];
            $data[$i][38] = $da[$thn-9]["denda"];
            $data[$i][39] = $da[$thn-9]["total"];
            $data[$i][40] = $da[$thn-10]["wp"];
            $data[$i][41] = $da[$thn-10]["pokok"];
            $data[$i][42] = $da[$thn-10]["denda"];
            $data[$i][43] = $da[$thn-10]["total"];
            
    }
//     echo "<pre>";
//     var_dump($data);exit;
    return $data;
}

function showTable ($mod=0,$nama="") {
	global $thn;
	$dt = getRealisasi($mod);
	$dtall = array();
	
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
        $summary['14'] = 0;
        $summary['15'] = 0;
        $summary['16'] = 0;
        $summary['17'] = 0;
        $summary['18'] = 0;
        $summary['19'] = 0;
        $summary['20'] = 0;
        $summary['21'] = 0;
        $summary['22'] = 0;
        $summary['23'] = 0;
        $summary['24'] = 0;
        $summary['25'] = 0;
        $summary['26'] = 0;
        $summary['27'] = 0;
        $summary['28'] = 0;
        $summary['29'] = 0;
        $summary['30'] = 0;
        $summary['31'] = 0;
        $summary['32'] = 0;
        $summary['33'] = 0;
        $summary['34'] = 0;
        $summary['35'] = 0;
        $summary['36'] = 0;
        $summary['37'] = 0;
        $summary['38'] = 0;
        $summary['39'] = 0;
        $summary['40'] = 0;
        $summary['41'] = 0;
        $summary['42'] = 0;
        $summary['43'] = 0;
        
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
	            <td align=\"right\">".number_format($dt[$i][14],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][15],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][16],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][17],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][18],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][19],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][20],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][21],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][22],0,',','.')."</td>
                    <td align=\"right\">".number_format($dt[$i][23],0,',','.')."</td>
                    <td align=\"right\">".number_format($dt[$i][24],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][25],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][26],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][27],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][28],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][29],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][30],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][31],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][32],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][33],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][34],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][35],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][36],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][37],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][38],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][39],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][40],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][41],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][42],0,',','.')."</td>
	            <td align=\"right\">".number_format($dt[$i][43],0,',','.')."</td>
        
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
                    $summary['14'] += $dt[$i][14];
                    $summary['15'] += $dt[$i][15];
                    $summary['16'] += $dt[$i][16];
                    $summary['17'] += $dt[$i][17];
                    $summary['18'] += $dt[$i][18];
                    $summary['19'] += $dt[$i][19];
                    $summary['20'] += $dt[$i][20];
                    $summary['21'] += $dt[$i][21];
                    $summary['22'] += $dt[$i][22];
                    $summary['23'] += $dt[$i][23];
                    $summary['24'] += $dt[$i][4];
                    $summary['25'] += $dt[$i][5];
                    $summary['26'] += $dt[$i][6];
                    $summary['27'] += $dt[$i][7];
                    $summary['28'] += $dt[$i][8];
                    $summary['29'] += $dt[$i][9];
                    $summary['30'] += $dt[$i][10];
                    $summary['31'] += $dt[$i][11];
                    $summary['32'] += $dt[$i][12];
                    $summary['33'] += $dt[$i][13];
                    $summary['34'] += $dt[$i][14];
                    $summary['35'] += $dt[$i][15];
                    $summary['36'] += $dt[$i][16];
                    $summary['37'] += $dt[$i][17];
                    $summary['38'] += $dt[$i][18];
                    $summary['39'] += $dt[$i][19];
                    $summary['40'] += $dt[$i][20];
                    $summary['41'] += $dt[$i][21];
                    $summary['42'] += $dt[$i][22];
                    $summary['43'] += $dt[$i][23];
                    
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
                <td align=\"right\">".number_format($summary['14'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['15'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['16'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['17'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['18'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['19'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['20'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['21'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['22'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['23'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['24'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['25'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['26'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['27'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['28'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['29'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['30'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['31'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['32'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['33'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['34'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['35'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['36'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['37'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['38'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['39'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['40'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['41'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['42'],0,',','.')."</td>
                <td align=\"right\">".number_format($summary['43'],0,',','.')."</td>
                </tr>";
	return $html."</table></div>";
}
function getData($where) {
	global $myDBLink,$thn;

	$myDBLink = openMysql();
	$return=array();
        for($t=$thn; $t >= $thn-10; $t--){
            $return[$t]["pokok"]=0;
            $return[$t]["denda"]=0;
            $return[$t]["total"]=0;
            $return[$t]["wp"]=0;
        }
        
	$query = " SELECT COUNT(*) AS wp, sum(SPPT_PBB_HARUS_DIBAYAR) as pokok, sum(PBB_DENDA) as denda, sum(PBB_TOTAL_BAYAR) as total,  SPPT_TAHUN_PAJAK FROM pbb_sppt 
                {$where}
                GROUP BY SPPT_TAHUN_PAJAK
                ORDER BY SPPT_TAHUN_PAJAK DESC";
                
	 //echo $query;exit;
	$res = mysqli_query($myDBLink, $query); 
	if ($res === false) {
		echo mysqli_error($myDBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		$return[$row["SPPT_TAHUN_PAJAK"]]["pokok"]=($row["pokok"]!="")?$row["pokok"]:0;
		$return[$row["SPPT_TAHUN_PAJAK"]]["denda"]=($row["denda"]!="")?$row["denda"]:0;
		$return[$row["SPPT_TAHUN_PAJAK"]]["total"]=($row["total"]!="")?$row["total"]:0;
		$return[$row["SPPT_TAHUN_PAJAK"]]["wp"]=($row["wp"]!="")?$row["wp"]:0;
        }
        // echo "<pre>";
        // var_dump($return);exit;
        
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
// var_dump($_REQUEST);exit;
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kab = $appConfig['KODE_KOTA'];
$thn = $appConfig['tahun_tagihan'];
$date_start = ($_REQUEST['ds'] != '') ? $_REQUEST['ds'] : "";
$date_end = ($_REQUEST['de'] != '') ? $_REQUEST['de'] : "";
$kec = ($_REQUEST['kc'] != '') ? $_REQUEST['kc'] : "";
$kecnama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";

$headerKec = $kec;

// var_dump($kec);exit;
if ($date_start != "") {
        $date_start = $date_start.'-01-01 00:00:00';
}

if ($date_start != "") {
        // $date_start = $date_start.' 00:00:00';
        $date_end = $date_end.'-12-31 23:59:59';

}
 //var_dump($date_start);exit;

echo showTable ();
