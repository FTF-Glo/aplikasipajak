<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);

//date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
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

//error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBlink ="";


function headerMonitoringE2 ($mod,$nama) {
	global $appConfig;
	$model = ($mod==0) ? "KECAMATAN" : strtoupper($_REQUEST['LBL_KEL']);
	$dl = "";
	if ($mod==0) {
		$dl = $appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA'];
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
function openMysql () {
	global $appConfig;
        $host = $appConfig['GW_DBHOST'];
        $port = isset($appConfig['GW_DBPORT'])? $appConfig['GW_DBPORT']:'3306';
        $user = $appConfig['GW_DBUSER'];
        $pass = $appConfig['GW_DBPWD'];
        $dbname = $appConfig['GW_DBNAME']; 
				$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		//echo mysqli_error($myDBLink);
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
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."' ORDER BY CPC_TKC_URUTAN ASC";
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
	/* $data = array();
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
	closePostgres($pgDBlink); */

	return $data;
}

function getKelurahan($p) {
	global $DBLink;
			$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID ='".$p."' ORDER BY CPC_TKL_URUTAN ASC";
			$res = mysqli_query($DBLink, $query);
			if ($res === false) {
				 $result['msg'] = mysqli_error($DBLink);
				 echo $json->encode($result);
				 exit();
			}
			$data = array();
			$i=0;
			while ($row = mysqli_fetch_assoc($res)) {
				$data[$i]["id"] = $row["CPC_TKL_ID"];
				$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
				$i++;
			}

	/* $pgDBlink = openPostgres();
	$data = array();
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
	closePostgres($pgDBlink); */
	return $data;
}

function getKetetapan($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s;
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
    
    $tahun = "";
	if($s==3) {
		if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
	}else if($s==4){
		if($thn != ""){$tahun = "and A.TAHUN='{$thn}'";}
	}
	

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		if($s==3){
			$whr = " NOP like '".$kec[$i]["id"]."%' ".$tahun;
			$da = getDataTargetE2($whr);
		}
		else if($s==4){
			$whr = " A.KELURAHAN like '".$kec[$i]["id"]."%' ".$tahun;
			$da = getDataTarget($whr);
		}
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
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
		$whr = " NOP like '".$kec[$i]["id"]."%' and (payment_flag!='1' or payment_flag is null) ".$tahun;
		$da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}

	return $data;
}

/* function getBulanLalu($mod) {
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
                $periode = " AND (date_format(payment_paid,'YYYY-MM-DD') >= date_format('$s_date','YYYY-MM-DD') AND date_format(payment_paid,'YYYY-MM-DD') <= date_format('$e_date','YYYY-MM-DD'))";
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
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	
	return $data;
} */

//get tanggal akhir pada bulan
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

function getBulanLalu($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
	
	//$date = date("Y-m-d");
	//$ardate = explode("-",$date);
	//$prevMon = $ardate[1]-1;//Bulan Sebelumnya
	
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($eperiode-1, $thn);//Ambil tanggal akhir bulan
	
	//$selectedMon = lastDay($eperiode);//Bulan yg dipilih
	
    $tahun = "";
	/* if($eperiode != -1){ //Jika ada bulan yang dipilih
        $periode = "and payment_paid between '{$firstMon}' and '{$selectedMon}'";
    } else //Jika tidak ada bulan yang dipilih
		$periode = "and payment_paid between '{$firstMon}' and '{$lastMon}'"; */
	
	$periode = "and payment_paid between '{$firstMon}' and '{$lastMon}'";
	
	// if($thn != ""){ //Jika tahun dipilih
		// $tahun = "and sppt_tahun_pajak='{$thn}'";  
    // }

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		
		if($eperiode > 1){
			$whr = " NOP like '".$kec[$i]["id"]."%' ".$periode." and payment_flag='1' ".$tahun;
			$da = getData($whr);
			$data[$i]["WP"] = $da["WP"];
			$data[$i]["RP"] = $da["RP"];
		}else {
			$data[$i]["WP"] = 0;
			$data[$i]["RP"] = 0;
		}
	}
	
	return $data;
}

/* function getSampaiBulanSekarang($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);

        $e_date = date('Y-m-').date('t',strtotime(date('m').'/1/'.date('Y')));
        $periode = " AND date_format(payment_paid,'YYYY-MM-DD') <= date_format('$e_date', 'YYYY-MM-DD') ";

        $tahun = "";
	if($thn != ""){
            $tahun = " AND sppt_tahun_pajak='{$thn}' ";
            if($speriode!= -1 && $eperiode != -1){
                $s_date = $thn."-".$speriode."-1";
                $lastday = date('t',strtotime($eperiode.'/1/'.$thn));
                $e_date = $thn."-".$eperiode."-".$lastday;
                $periode = " AND (date_format(payment_paid,'YYYY-MM-DD') >= date_format('$s_date','YYYY-MM-DD') AND date_format(payment_paid,'YYYY-MM-DD') <= date_format('$e_date','YYYY-MM-DD')) ";
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
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	
	return $data;
} */

function getSampaiBulanSekarang($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$speriode,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
	
	$firstMon = firstDay('01', $thn);
	$nowMon = lastDay($eperiode, $thn);

    $periode = "and payment_paid between '{$firstMon}' and '{$nowMon}'"; //Antara tanggal 01/01/ sampai sekarang
		
	$tahun = "";	
	// if($thn != ""){ //Jika tahun dipilih
		// $tahun = "and sppt_tahun_pajak='{$thn}'";  
    // }

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_flag='1' ".$periode.$tahun;
		$da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
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
            // $tahun = " AND sppt_tahun_pajak='{$thn}' ";
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
		$whr = " NOP like '".$kec[$i]["id"]."%' and payment_flag!='1' ".$periode.$tahun;
		$da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	
	return $data;
}

/* function getBulanSekarang($mod) {
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
                $periode = " AND (date_format(payment_paid,'YYYY-MM-DD') >= date_format('$s_date','YYYY-MM-DD') AND date_format(payment_paid,'YYYY-MM-DD') <= date_format('$e_date','YYYY-MM-DD'))";
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
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	return $data;
} */

function getBulanSekarang($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$eperiode;
	
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
	
	
	$firstMon = firstDay($eperiode, $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($eperiode, $thn);//Ambil tanggal akhir bulan
	

    $periode = "and payment_paid between '{$firstMon}' and '{$lastMon}'"; //Antara tanggal 01 sampai tanggal 30 bulan sekarang
	
    $tahun = "";
	/* if($thn != ""){
            $tahun = "and sppt_tahun_pajak='{$thn}'";
    } */

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' ".$periode." and payment_flag='1' ".$tahun;
		$da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt = getKetetapan($mod);
	$dt1 = getBulanLalu($mod);
	$dt2 = getBulanSekarang($mod);
	$dtall = array();
	if($eperiode == 1)
		$dtall = getSampaiBulanSekarang($mod);
	else {
		foreach($dt1 as $key => $row){
			$dtall[$key]["WP"] = $row["WP"] + $dt2[$key]["WP"];
			$dtall[$key]["RP"] = $row["RP"] + $dt2[$key]["RP"];
		}
	}
	
	//$dtsisa = getSisaKetetapan($mod);
//	$dtsisa = getSisaSampaiBulanSekarang($mod);
	$c = count($dt);
//	$html = "";
	$a=1;
//	$html = headerMonitoringE2 ($mod,$nama);
    $data = array();
	$summary = array('name'=>'JUMLAH', 'ketetapan_wp'=>0, 'ketetapan_rp'=>0, 'rbl_wp'=>0, 'rbl_rp'=>0, 'percent1'=>0, 'rbi_wp'=>0, 'rbi_rp'=>0,'kom_rbi_wp'=>0, 'kom_rbi_rp'=>0, 'percent2'=>0, 'sk_wp'=>0, 'sk_rp'=>0, 'percent3'=>0);
	
	for ($i=0;$i<$c;$i++) {
			$sk_wp = $dt[$i]["WP"] - $dtall[$i]["WP"];
			$sk_rp = $dt[$i]["RP"] - $dtall[$i]["RP"];
			
			$percent1 = ($dt1[$i]["RP"] != 0 && $dt[$i]["RP"] != 0) ? ($dt1[$i]["RP"]/$dt[$i]["RP"]*100) : 0;
			$percent2 = ($dtall[$i]["RP"] !=0 && $dt[$i]["RP"] !=0 ) ? ($dtall[$i]["RP"]/$dt[$i]["RP"]*100) : 0;
			$percent3 = ($sk_rp != 0 && $dt[$i]["RP"] != 0) ? ($sk_rp/$dt[$i]["RP"]*100) : 0;
            $tmp = array(
                "name" => $dt[$i]["name"],
                "ketetapan_wp" => number_format($dt[$i]["WP"],0,"",""),
                "ketetapan_rp" => number_format($dt[$i]["RP"],0,"",""),
                "rbl_wp" => number_format($dt1[$i]["WP"],0,"",""),
                "rbl_rp" => number_format($dt1[$i]["RP"],0,"",""),
                "percent1" => number_format($percent1,2,",","."),
				
                "rbi_wp" => number_format($dt2[$i]["WP"],0,"",""),
                "rbi_rp" => number_format($dt2[$i]["RP"],0,"",""),
                "kom_rbi_wp" => number_format($dtall[$i]["WP"],0,"",""),
                "kom_rbi_rp" => number_format($dtall[$i]["RP"],0,"",""),
                "percent2" => number_format($percent2,2,",","."),
				"sk_wp" => number_format($sk_wp,0,"",""),
                "sk_rp" => number_format($sk_rp,0,"",""),
                "percent3" => number_format($percent3,2,",",".")
				
            );
            $data[] = $tmp;
			$summary['ketetapan_wp'] += $dt[$i]["WP"];
			$summary['ketetapan_rp'] += $dt[$i]["RP"];
			$summary['rbl_wp'] += $dt1[$i]["WP"];
			$summary['rbl_rp'] += $dt1[$i]["RP"];
			$summary['rbi_wp'] += $dt2[$i]["WP"];
			$summary['rbi_rp'] += $dt2[$i]["RP"];
			$summary['kom_rbi_wp'] += $dtall[$i]["WP"];
			$summary['kom_rbi_rp'] += $dtall[$i]["RP"];
			$summary['sk_wp'] += $sk_wp;
			$summary['sk_rp'] += $sk_rp;
	}
	
	$summary['percent1'] = ($summary['ketetapan_rp'] != 0 && $summary['rbl_rp'] != 0) ? ($summary["rbl_rp"]/$summary["ketetapan_rp"]*100) : 0;
	$summary['percent2'] = ($summary['ketetapan_rp'] != 0 && $summary['kom_rbi_rp'] != 0) ? ($summary["kom_rbi_rp"]/$summary["ketetapan_rp"]*100) : 0;
	$summary['percent3'] = ($summary['ketetapan_rp'] != 0 && $summary['sk_rp'] != 0) ? ($summary["sk_rp"]/$summary["ketetapan_rp"]*100) : 0; 
	
	$summary['percent1'] = number_format($summary['percent1'],2,",",".");
	$summary['percent2'] = number_format($summary['percent2'],2,",",".");
	$summary['percent3'] = number_format($summary['percent3'],2,",",".");
				
	$data[] = $summary;
	
	return $data;
}

function getData($where) {
	global $myDBLink,$kd,$thn,$bulan;

	$myDBLink = openMysql();
	$return=array();
	$return["RP"]=0;
	$return["WP"]=0;
	$whr="";
	if($where) {
		$whr =" where {$where}";
	}
	$query = "SELECT count(wp_nama) AS WP, sum(PBB_TOTAL_BAYAR) as RP FROM PBB_SPPT {$whr}"; //echo $query;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["RP"]=($row["RP"]!="")?$row["RP"]:0;
		$return["WP"]=($row["WP"]!="")?$row["WP"]:0;
	}
	//closeMysql($myDBLink);
	return $return;
}

function getDataTargetE2($where) {
	global $myDBLink,$kd,$thn,$bulan;

	$myDBLink = openMysql();
	$return=array();
	$return["RP"]=0;
	$return["WP"]=0;
	$whr="";
	if($where) {
		$whr =" where {$where}";
	}	
	$query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) as RP FROM PBB_SPPT {$whr}"; //echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["RP"]=($row["RP"]!="")?$row["RP"]:0;
		$return["WP"]=($row["WP"]!="")?$row["WP"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getDataTarget($where) {
	global $myDBLink,$kd,$thn,$bulan,$target_ketetapan;

	$myDBLink = openMysql();
	$return=array();
	$return["RP"]=0;
	$return["WP"]=0;
	$whr="";
	if($where) {
		$whr =" where {$where}";
	}	
	
	if($target_ketetapan == 'semua')
		$query = "SELECT sum(A.TARGET_WP) AS WP, sum(A.TARGET_VALUE) as RP FROM PBB_SPPT_TARGET A {$whr}"; 
	else $query = "SELECT (COALESCE(AWP,0) - COALESCE(BWP,0)) AS WP, (COALESCE(ARP,0) - COALESCE(BRP,0)) AS RP FROM (
		SELECT sum(A.TARGET_WP) AS AWP, sum(A.TARGET_VALUE) as ARP, sum(B.TARGET_WP) AS BWP, sum(B.TARGET_VALUE) as BRP FROM PBB_SPPT_TARGET A LEFT JOIN 	PBB_SPPT_TARGET_PENGECUALIAN B
		ON A.KELURAHAN = B.KELURAHAN AND A.TAHUN=B.TAHUN 
		{$whr}
		) TBL1"; 
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["RP"]=($row["RP"]!="")?$row["RP"]:0;
		$return["WP"]=($row["WP"]!="")?$row["WP"]:0;
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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kab  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$speriode = @isset($_REQUEST['speriode']) ? $_REQUEST['speriode'] : "";
$eperiode = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";

$arrWhere = array();

if ($kecamatan !="") {
        array_push($arrWhere,"nop like '{$kecamatan}%'");
}

if ($thn!=""){
    array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
    array_push($arrWhere,"payment_paid like '{$thn}%'");
}

$where = implode (" AND ",$arrWhere);

if ($kecamatan=="") {
	$data = showTable ();
} else {
	$data = showTable(1,$nama);
} 


$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.8);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.5);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(true); 

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
                             ->setLastModifiedBy("vpost")
                             ->setTitle("Alfa System")
                             ->setSubject("Alfa System pbb")
                             ->setDescription("pbb")
                             ->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();

$objRichText->createText(': KETETAPAN DAN REALISASI PBB TAHUN ANGGARAN '.$thn);
$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D1:J1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText(': I s/d III');
$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D2:J2');
$objRichText = new PHPExcel_RichText();
if($eperiode > 1)
	$objRichText->createText(': JANUARI s/d '.strtoupper($bulan[$eperiode-1]).' '.$thn);
else $objRichText->createText(': JANUARI  '.$thn);
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:J3'); 


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'BUKU');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BULAN');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Model E.2');

$objPHPExcel->getActiveSheet()->getStyle('C1:L3')->applyFromArray(
    array('font'    => array('size'=>$fontSizeHeader))
);
if($kecamatan == ''){
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('RANGKING');
	$objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText($appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA']);
	$objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
	$objPHPExcel->getActiveSheet()->getStyle('A5:B6')->applyFromArray(
	    array(
	        'font'    => array('italic' => true,'size'=>$fontSizeHeader),
	        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
	    )
	);
}else{
	$objRichText = new PHPExcel_RichText();
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('KECAMATAN : '.$nama);
	$objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
	$objPHPExcel->getActiveSheet()->getStyle('A5:D6')->applyFromArray(
	    array(
	        'font'    => array('italic' => false,'size'=>$fontSizeHeader),
	        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
	    )
	);
} 

        

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
$objRichText = new PHPExcel_RichText();

if ($kecamatan=="") {
	$objRichText->createText('KECAMATAN');
} else {
	$objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
}

$objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KETETAPAN');
$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C8:D8');
$objPHPExcel->getActiveSheet()->setCellValue('C9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('D9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI BULAN LALU');
$objPHPExcel->getActiveSheet()->getCell('E8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E8:F8');
$objPHPExcel->getActiveSheet()->setCellValue('E9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('F9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('G8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('G8:G9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI BULAN INI');
$objPHPExcel->getActiveSheet()->getCell('H8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('H8:I8');
$objPHPExcel->getActiveSheet()->setCellValue('H9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('I9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('REALISASI s/d BULAN INI');
$objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J8:K8');
$objPHPExcel->getActiveSheet()->setCellValue('J9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('K9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('L8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('L8:L9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('SISA KETETAPAN');
$objPHPExcel->getActiveSheet()->getCell('M8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M8:N8');
$objPHPExcel->getActiveSheet()->setCellValue('M9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('N9', 'RP');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('%');
$objPHPExcel->getActiveSheet()->getCell('O8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('O8:O9');


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A8:O9')->applyFromArray(
    array(
        'font'    => array(            
            'size' => $fontSizeHeader
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1:P50')->applyFromArray(
    array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(7);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no=1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

foreach($data as $buffer){
    $objPHPExcel->getActiveSheet()->getRowDimension(9+$no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A'.(9+$no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B'.(9+$no), $buffer['name']);
	if($buffer['name'] == 'JUMLAH'){
		$objPHPExcel->getActiveSheet()->setCellValue('C'.(9+$no), $buffer['ketetapan_wp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('D'.(9+$no), $buffer['ketetapan_rp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('E'.(9+$no), $buffer['rbl_wp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('F'.(9+$no), $buffer['rbl_rp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('G'.(9+$no), $buffer['percent1']);
	    $objPHPExcel->getActiveSheet()->setCellValue('H'.(9+$no), $buffer['rbi_wp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('I'.(9+$no), $buffer['rbi_rp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('J'.(9+$no), $buffer['kom_rbi_wp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('K'.(9+$no), $buffer['kom_rbi_rp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('L'.(9+$no), $buffer['percent2']);
	    $objPHPExcel->getActiveSheet()->setCellValue('M'.(9+$no), $buffer['sk_wp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('N'.(9+$no), $buffer['sk_rp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('O'.(9+$no), $buffer['percent3']);
	}else {
	    $objPHPExcel->getActiveSheet()->setCellValue('C'.(9+$no), $buffer['ketetapan_wp'])->getStyle('C'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('D'.(9+$no), $buffer['ketetapan_rp'])->getStyle('D'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('E'.(9+$no), $buffer['rbl_wp'])->getStyle('E'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('F'.(9+$no), $buffer['rbl_rp'])->getStyle('F'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('G'.(9+$no), $buffer['percent1'])->getStyle('G'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('H'.(9+$no), $buffer['rbi_wp'])->getStyle('H'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('I'.(9+$no), $buffer['rbi_rp'])->getStyle('I'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('J'.(9+$no), $buffer['kom_rbi_wp'])->getStyle('J'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('K'.(9+$no), $buffer['kom_rbi_rp'])->getStyle('K'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('L'.(9+$no), $buffer['percent2'])->getStyle('L'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('M'.(9+$no), $buffer['sk_wp'])->getStyle('M'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('N'.(9+$no), $buffer['sk_rp'])->getStyle('N'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('O'.(9+$no), $buffer['percent3'])->getStyle('O'.(9+$no))->applyFromArray($noBold);
	}
    $no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A'.(8+$no), '');
$objPHPExcel->getActiveSheet()->getStyle('A10:O'.(9+count($data)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
); 
$objPHPExcel->getActiveSheet()->getStyle('A10:A'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('C10:F'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('G10:G'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('H10:K'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('L10:L'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('M10:N'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('O10:O'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_KOTA_PENGESAHAN'].', '.strtoupper($bulan[date('m')-1]).' '.$thn);
$objPHPExcel->getActiveSheet()->getCell('I'.(11+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(11+count($data)).':K'.(11+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['PEJABAT_SK2']);
$objPHPExcel->getActiveSheet()->getCell('I'.(12+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(12+count($data)).':K'.(12+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['C_KABKOT'] ." ".$appConfig['NAMA_KOTA']);
$objPHPExcel->getActiveSheet()->getCell('I'.(13+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(13+count($data)).':K'.(13+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_PEJABAT_SK2']);
$objPHPExcel->getActiveSheet()->getCell('I'.(17+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(17+count($data)).':K'.(17+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_PEJABAT_SK2_JABATAN']);
$objPHPExcel->getActiveSheet()->getCell('I'.(18+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(18+count($data)).':K'.(18+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NIP. '.$appConfig['NAMA_PEJABAT_SK2_NIP']);
$objPHPExcel->getActiveSheet()->getCell('I'.(19+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(19+count($data)).':K'.(19+count($data)));

$objPHPExcel->getActiveSheet()->getStyle('I'.(17+count($data)).':K'.(17+count($data)));
$objPHPExcel->getActiveSheet()->getStyle('I'.(11+count($data)).':K'.(19+count($data)))->applyFromArray(
    array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporting_model_e2.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
