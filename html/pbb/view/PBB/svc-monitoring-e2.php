<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
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
require_once("config-monitoring.php");


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

$host = DBHOST;
$port = DBPORT;
$user = DBUSER;
$pass = DBPWD;
$dbname = DBNAME; 
$pgDBlink ="";
$kd = "1671";

function headerMonitoringE2 ($mod,$nama) {
	$model = ($mod==0) ? "KECAMATAN" : "KELURAHAN";
	$dl = "";
	if ($mod==0) { 
		$dl = "KOTA PALEMBANG";
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"15\"><b>{$dl}<b></td></tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" span=\"2\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"48\" />
	  <col width=\"89\" />
	  <col width=\"56\" />
	  <tr>
		<td rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<td rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">KETETAPAN</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN LALU</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN INI</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI S/D BULAN  INI</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"137\" align=\"center\">SISA     KETETAPAN</td>
		<td rowspan=\"2\" width=\"56\" align=\"center\">SISA  %</td>
	  </tr>
	  <tr>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
	  </tr>
	";
	return $html; 
}

// koneksi postgres
function openPostgres () {
	$host = DBHOST;
	$port = DBPORT;
	$dbname = DBNAME;
	$user = DBUSER;
	$pass = DBPWD;
	
	if ($pgDBlink = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$pass}")) {
		echo pg_last_error($pgDBlink); 
		//exit();
	}
	return $pgDBlink;
}

function closePostgres($con){
	pg_close($con);
}
	
function getKecamatan($p) {
	/*global $DBLink;
	$return = array();
	$query = "SELECT * FROM cppmod_tax_kecamatan_1671 WHERE CPC_TKC_KKID ='".$p."' ORDER BY CPC_TKC_KECAMATAN";
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
	}*/
	$data = array();
	$pgDBlink = openPostgres();

	$result = pg_query($pgDBlink,"SELECT kode_kecamatan, nama_kecamatan FROM ".DBTABLEKECAMATAN." WHERE id_kota='97' ORDER BY nama_kecamatan");
	
	if ($result===false) {
		echo pg_result_error($result);
	  exit;
	}
	$i=0;
	while ($row = pg_fetch_assoc($result)) {
		$data[$i]["id"] = $row["kode_kecamatan"];
		$data[$i]["name"] = $row["nama_kecamatan"];
		$i++;
	}
	closePostgres($pgDBlink);
	
	return $data;
}

function getKelurahan($p) {
	$data = array();
	$pgDBlink = openPostgres();

	$dbresult = pg_query($pgDBlink,"SELECT id_kelurahan,kode_kelurahan, nama_kelurahan FROM ".DBTABLEKELURAHAN." WHERE kode_kelurahan like '{$p}%' ORDER BY nama_kelurahan");
	
	if ($dbresult ===false) {
		echo pg_result_error($dbresult );
	  exit;
	}
	$i=0;
	while ($row = pg_fetch_assoc($dbresult )) {
		$data[$i]["id"] = $row["kode_kelurahan"];
		$data[$i]["name"] = $row["nama_kelurahan"];
		$i++;
	}
	closePostgres($pgDBlink);
	return $data;
}


function getKetetapan($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab;
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
        
        $tahun = "";
	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' ".$tahun;
		$da = getData($whr);
		$data[$i]["wp"] = $da["wp"];
		$data[$i]["rp"] = $da["rp"];
	}
	
	return $data;
}

function getSisaKetetapan($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab;
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);

        $tahun = "";
	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_flag!=1 ".$tahun;
		$da = getData($whr);
		$data[$i]["wp"] = $da["wp"];
		$data[$i]["rp"] = $da["rp"];
	}

	return $data;
}

function getBulanLalu($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);

	$date = date("Y-m-d");
	$ardate = explode("-",$date);
	$month = $ardate[1]-1;
	$tdate = mktime(0,0,0,$month,$ardate[2],$ardate[0]);
	$prev = date("Y-m",$tdate);

        $periode = "and payment_paid like '{$prev}%'";
        $tahun = "";
	if($thn != ""){
            $tahun = "and sppt_tahun_pajak='{$thn}'";
            if($speriode != -1 && $eperiode != -1){
                $tmp_eperiod = $eperiode;
                if($speriode != $eperiode){$tmp_eperiod = $eperiode - 1;}

                $s_date = $thn."-".$speriode."-1";
                $lastday = date('t',strtotime($tmp_eperiod.'/1/'.$thn));
                $e_date = $thn."-".$tmp_eperiod."-".$lastday;
                $periode = " AND (to_date(payment_paid,'YYYY-MM-DD') >= to_date('$s_date','YYYY-MM-DD') AND to_date(payment_paid,'YYYY-MM-DD') <= to_date('$e_date','YYYY-MM-DD'))";
            }
        }

//        $tahun = "";
//	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
//	$kec =  getKecamatan($kab);
	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
//		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_paid like '{$prev}%' and payment_flag=1 ".$tahun;
		$whr = " NOP like '".$kec[$i]["id"]."%' ".$periode." and payment_flag=1 ".$tahun;
		$da = getData($whr);
		$data[$i]["wp"] = $da["wp"];
		$data[$i]["rp"] = $da["rp"];
	}
	
	return $data;
}

function getSampaiBulanSekarang($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);

        $e_date = date('Y-m-').date('t',strtotime(date('m').'/1/'.date('Y')));
        $periode = " AND to_date(payment_paid,'YYYY-MM-DD') <= to_date('$e_date', 'YYYY-MM-DD') ";

        $tahun = "";
	if($thn != ""){
            $tahun = " AND sppt_tahun_pajak='{$thn}' ";
            if($speriode!= -1 && $eperiode != -1){
                $s_date = $thn."-".$speriode."-1";
                $lastday = date('t',strtotime($eperiode.'/1/'.$thn));
                $e_date = $thn."-".$eperiode."-".$lastday;
                $periode = " AND (to_date(payment_paid,'YYYY-MM-DD') >= to_date('$s_date','YYYY-MM-DD') AND to_date(payment_paid,'YYYY-MM-DD') <= to_date('$e_date','YYYY-MM-DD')) ";
            }
        }

//        $tahun = "";
//	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_flag=1 ".$periode.$tahun;
		$da = getData($whr);
		$data[$i]["wp"] = $da["wp"];
		$data[$i]["rp"] = $da["rp"];
	}
	
	return $data;
}

function getSisaSampaiBulanSekarang($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
	
//        $tahun = "";
//	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}

        $e_date = date('Y-m-').date('t',strtotime(date('m').'/1/'.date('Y')));
        $periode = " AND date(sppt_tanggal_terbit) < '$e_date'";

        $tahun = "";
	if($thn != ""){
            $tahun = " AND sppt_tahun_pajak='{$thn}' ";
            if($eperiode != -1){
                $lastday = date('t',strtotime($eperiode.'/1/'.$thn));
                $e_date = $thn."-".$eperiode."-".$lastday;
                $periode = " AND date(sppt_tanggal_terbit) < '$e_date'";
            }
        }


	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_flag!=1 ".$periode.$tahun;
		$da = getData($whr);
		$data[$i]["wp"] = $da["wp"];
		$data[$i]["rp"] = $da["rp"];
	}
	
	return $data;
}

function getBulanSekarang($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$eperiode;
	$date = date("Y-m-d");
	$ardate = explode("-",$date);
	$tdate = mktime(0,0,0,$ardate[1],$ardate[2],$ardate[0]);
	$prev = date("Y-m",$tdate);
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);

        $periode = " and payment_paid like '{$prev}%' ";
        $tahun = "";

	if($thn != ""){
            $tahun = "and sppt_tahun_pajak='{$thn}'";
            if($eperiode != -1){
                $s_date = $thn."-".$eperiode."-1";
                $lastday = date('t',strtotime($eperiode.'/1/'.$thn));
                $e_date = $thn."-".$eperiode."-".$lastday;
                $periode = " AND (to_date(payment_paid,'YYYY-MM-DD') >= to_date('$s_date','YYYY-MM-DD') AND to_date(payment_paid,'YYYY-MM-DD') <= to_date('$e_date','YYYY-MM-DD'))";
            }
        }

//        $tahun = "";
//	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
//		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_paid like '{$prev}%' and payment_flag=1 ".$tahun.$periode;
		$whr = " NOP like '".$kec[$i]["id"]."%' ".$periode." and payment_flag=1 ".$tahun.$periode;
		$da = getData($whr);
		$data[$i]["wp"] = $da["wp"];
		$data[$i]["rp"] = $da["rp"];
	}
	return $data;
}

function showTable ($mod=0,$nama="") {
	$dt = getKetetapan($mod);
	$dt1 = getBulanLalu($mod);
	$dt2 = getBulanSekarang($mod);
	$dtall = getSampaiBulanSekarang($mod);
//	$dtsisa = getSisaSampaiBulanSekarang($mod);
	$dtsisa = getSisaKetetapan($mod);
	$c = count($dt);
	$html = "";
	$a=1;
	$html = headerMonitoringE2 ($mod,$nama);
        for ($i=0;$i<$c;$i++) {
                $dtname = $dt[$i]["name"];
                $wp = number_format($dt[$i]["wp"],0,",",".");
                $rp = number_format($dt[$i]["rp"],0,",",".");
                $wpp = number_format($dt1[$i]["wp"],0,",",".");
                $rpp = number_format($dt1[$i]["rp"],0,",",".");
                $prc1 = ($dt1[$i]["wp"] != 0 && $dt[$i]["wp"] != 0) ? number_format($dt1[$i]["wp"]/$dt[$i]["wp"]*100,2,",",".") : 0;
                $wpn = number_format($dt2[$i]["wp"],0,",",".");
                $rpn = number_format($dt2[$i]["rp"],0,",",".");
                $wpall = number_format($dtall[$i]["wp"],0,",",".");
                $rpall = number_format($dtall[$i]["rp"],0,",",".");
                $prc2 = ($dtall[$i]["wp"] !=0 && $dt[$i]["wp"] !=0 ) ? number_format($dtall[$i]["wp"]/$dt[$i]["wp"]*100,2,",",".") : 0;
                $wpsisa = number_format($dtsisa[$i]["wp"],0,",",".");
                $rpsisa = number_format($dtsisa[$i]["rp"],0,",",".");
                $prc3 = ($dtsisa[$i]["wp"] != 0 && $dt[$i]["wp"] != 0) ? number_format($dtsisa[$i]["wp"]/$dt[$i]["wp"]*100,2,",",".") : 0;
                $html .= " <tr>
            <td align=\"right\">{$a}</td>
            <td>{$dtname}</td>
            <td align=\"right\">{$wp}</td>
            <td align=\"right\">{$rp}</td>
            <td align=\"right\">{$wpp}</td>
            <td align=\"right\">{$rpp}</td>
            <td align=\"right\">{$prc1}</td>
            <td align=\"right\">{$wpn}</td>
            <td align=\"right\">{$rpn}</td>
            <td align=\"right\">{$wpall}</td>
            <td align=\"right\">{$rpall}</td>
            <td align=\"right\">{$prc2}</td>
            <td align=\"right\">{$wpsisa}</td>
            <td align=\"right\">{$rpsisa}</td>
            <td align=\"right\">{$prc3}</td>
          </tr>";
          $a++;
        }
	return $html."</table>";
}
function getData($where) {
	global $DBLink,$kd,$thn,$bulan,$pgDBlink;
	$return=array();
	$return["rp"]=0;
	$return["wp"]=0;
	$whr="";
	if($where) {
		$whr =" where {$where}";
	}	
	$pgDBlink = openPostgres();

//                echo "SELECT count(wp_nama) AS WP, sum(sppt_pbb_harus_dibayar) as RP FROM ".DBTABLE." {$whr}"."<br/>";
	$result = pg_query($pgDBlink,"SELECT count(wp_nama) AS WP, sum(sppt_pbb_harus_dibayar) as RP FROM ".DBTABLE." {$whr}");
//	$result = pg_query($pgDBlink,"SELECT count(nop) AS WP, sum(sppt_pbb_harus_dibayar) as RP FROM ".DBTABLE." {$whr}");
	//echo "SELECT count(nop) AS WP, sum(sppt_pbb_harus_dibayar) as RP FROM ".DBTABLE." {$whr}";
	if ($result===false) {
		echo pg_result_error($result);
	  exit;
	}
	while ($row = pg_fetch_assoc($result)) {
		//print_r($row);
		$return["rp"]=($row["rp"]!="")?$row["rp"]:0;
		$return["wp"]=($row["wp"]!="")?$row["wp"]:0;
	}
	closePostgres($pgDBlink);
	return $return;
}

$kab  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : "1671"; 
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
//$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
//$bulan = @isset($_REQUEST['bl1']) ? $_REQUEST['bl1'] : 1;
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$speriode = @isset($_REQUEST['speriode']) ? $_REQUEST['speriode'] : "";
$eperiode = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";

$arrWhere = array();

if ($kecamatan !="") {
//	if ($kelurahan !="") array_push($arrWhere,"nop like '{$kelurahan}%'");
//	else array_push($arrWhere,"nop like '{$kecamatan}%'");
        array_push($arrWhere,"nop like '{$kecamatan}%'");
}

//if ($thn!="") array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
if ($thn!=""){
    array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");  
    array_push($arrWhere,"payment_paid like '{$thn}%'");  
} 
//if ($bulan!="") array_push($arrWhere,"payment_paid like '{$thn}-{$bulan}%'");

$where = implode (" AND ",$arrWhere);

//echo $where;

if ($kecamatan=="") { 
	echo showTable ();
} else {
	echo showTable(1,$nama);
}
?>