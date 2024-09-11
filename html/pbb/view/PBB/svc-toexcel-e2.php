<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';

error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

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

        $periode = " and payment_paid like '{$prev}%' ";
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

//        $tahun = "";
//	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}

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
        $periode = " AND to_date(payment_paid,'YYYY-MM-DD') <= to_date('$e_date','YYYY-MM-DD') ";

        $tahun = "";
	if($thn != ""){
            $tahun = " AND sppt_tahun_pajak='{$thn}' ";
            if($speriode != -1 && $eperiode != -1){
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
//      $tahun = "";
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
	$dtsisa = getSisaKetetapan($mod);
//	$dtsisa = getSisaSampaiBulanSekarang($mod);
	$c = count($dt);
//	$html = "";
	$a=1;
//	$html = headerMonitoringE2 ($mod,$nama);
        $data = array();
	for ($i=0;$i<$c;$i++) {
            $tmp = array(
                "name" => $dt[$i]["name"],
                "ketetapan_wp" => number_format($dt[$i]["wp"],0,",","."),
                "ketetapan_rp" => number_format($dt[$i]["rp"],0,",","."),
                "rbl_wp" => number_format($dt1[$i]["wp"],0,",","."),
                "rbl_rp" => number_format($dt1[$i]["rp"],0,",","."),
                "percent1" => ($dt1[$i]["wp"] != 0 && $dt[$i]["wp"] != 0) ? number_format($dt1[$i]["wp"]/$dt[$i]["wp"]*100,2,",",".") : 0,
                "rbi_wp" => number_format($dt2[$i]["wp"],0,",","."),
                "rbi_rp" => number_format($dt2[$i]["rp"],0,",","."),
                "kom_rbi_wp" => number_format($dtall[$i]["wp"],0,",","."),
                "kom_rbi_rp" => number_format($dtall[$i]["rp"],0,",","."),
                "percent2" => ($dtall[$i]["wp"] !=0 && $dt[$i]["wp"] !=0 ) ? number_format($dtall[$i]["wp"]/$dt[$i]["wp"]*100,2,",",".") : 0,
                "sk_wp" => number_format($dtsisa[$i]["wp"],0,",","."),
                "sk_rp" => number_format($dtsisa[$i]["rp"],0,",","."),
                "percent3" => ($dtsisa[$i]["wp"] != 0 && $dt[$i]["wp"] != 0) ? number_format($dtsisa[$i]["wp"]/$dt[$i]["wp"]*100,2,",",".") : 0
            );
            $data[] = $tmp;
	}
	return $data;
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

//        echo "SELECT count(wp_nama) AS WP, sum(sppt_pbb_harus_dibayar) as RP FROM ".DBTABLE." {$whr}"."<br/>";
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

$bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
$kab  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : "1671"; 
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
//$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
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

if ($kecamatan=="") { 
	$data = showTable ();
} else {
	$data = showTable(1,$nama);
}

//echo '<pre>';
//print_r($data);
//echo '</pre>';



// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

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
$objRichText->createText(': '.strtoupper($bulan[$speriode]).' s/d '.strtoupper($bulan[$eperiode]).' '.$thn);
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:J3');


$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
$objPHPExcel->getActiveSheet()->setCellValue('C2', 'BUKU');
$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BULAN');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Model E.2');

$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C1:J3')->applyFromArray(
    array('font'    => array('bold' => true))
);


$objRichText = new PHPExcel_RichText();
$objRichText->createText('RANGKING');
$objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KOTA PALEMBANG');
$objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
$objPHPExcel->getActiveSheet()->getStyle('A5:B6')->applyFromArray(
    array(
        'font'    => array('bold' => true, 'italic' => true),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    )
);


        

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KECAMATAN');
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
            'bold' => true
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

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);

$no=1;
foreach($data as $buffer){
    $objPHPExcel->getActiveSheet()->setCellValue('A'.(9+$no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B'.(9+$no), $buffer['name']);
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
    $no++;
}
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
$objRichText->createText('PALEMBANG, '.strtoupper($bulan[date('m')-1]).' '.$thn);
$objPHPExcel->getActiveSheet()->getCell('I'.(11+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(11+count($data)).':K'.(11+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KEPALA DINAS PENDAPATAN DAERAH');
$objPHPExcel->getActiveSheet()->getCell('I'.(12+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(12+count($data)).':K'.(12+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KOTA PALEMBANG');
$objPHPExcel->getActiveSheet()->getCell('I'.(13+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(13+count($data)).':K'.(13+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('Dra. Hj. SUMAIYAH. MZ, MM');
$objPHPExcel->getActiveSheet()->getCell('I'.(17+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(17+count($data)).':K'.(17+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PEMBINA UTAMA MUDA');
$objPHPExcel->getActiveSheet()->getCell('I'.(18+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(18+count($data)).':K'.(18+count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NIP. 19550922 197903 2 003');
$objPHPExcel->getActiveSheet()->getCell('I'.(19+count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I'.(19+count($data)).':K'.(19+count($data)));

$objPHPExcel->getActiveSheet()->getStyle('I'.(17+count($data)).':K'.(17+count($data)))->applyFromArray(
    array('font'    => array('bold' => true))
);
$objPHPExcel->getActiveSheet()->getStyle('I'.(11+count($data)).':K'.(19+count($data)))->applyFromArray(
    array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEGAL);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporting_model_e2"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
