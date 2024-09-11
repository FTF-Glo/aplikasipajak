<?php

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'kartu_piutang', '', dirname(__FILE__))) . '/';
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
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");

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

$tipekartu 	= $q->tipekartu;
$nop		= $q->nop;
$kecamatan	= $q->kecamatan;
$kelurahan	= $q->kelurahan;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$myDBLink = "";

switch($tipekartu){
	case "pernop" : 
		$html = kartuPerNOP(); 
		$nama_file = "Kartu_piutang_pernop";
		break;
	case "kec" : 
		$html = kartuPerKecamatan(); 
		$nama_file = "Kartu_piutang_perkecamatan";
		break;
	case "kel" : 
		$html = kartuPerKelurahan(); 
		$nama_file = "Kartu_piutang_perkelurahan";
		break;
	case "inv" : 
		$html = kartuInventaris(); 
		$nama_file = "Kartu_piutang_inventaris";
		break;
}

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('kjp');
$pdf->SetSubject('kjp');
$pdf->SetKeywords('kjp');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 14, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$pdf->AddPage('P', 'A4');
$pdf->Image($sRootPath.'image/'.$appConfig['LOGO_CETAK_PDF'], 17, 17, 25, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->SetAlpha(0.3);
$pdf->Output(''.$nama_file.'.pdf', 'I');

function kartuPerNOP(){
	global $appConfig, $nop, $kecamatan, $kelurahan;
	
	$dt 		= getDataPerNOP($nop);
	
	if(!empty($dt)){
		$dtLatest	= $dt[0];
		if($dtLatest['NOP']==""){
			echo "<br>Tidak ada data."; exit;
		}
	} else {
		echo "<br>Tidak ada data."; exit;
	}
	
	// print_r($dtLatest); exit;
	
	$thnTagihan = "";
	$totalPiutang = 0;
	foreach($dt as $d){
		$thnTagihan .= "
			<tr> 
				<td align=\"center\" width='50%' >".$d['SPPT_TAHUN_PAJAK']."</td>
				<td align=\"right\" width='50%' >".$d['SPPT_PBB_HARUS_DIBAYAR']."</td>
			</tr>";
			
		$totalPiutang += $d['SPPT_PBB_HARUS_DIBAYAR'];
	}
	
	
	$html = "
	<div border=\"1\">
		<table width=\"100%\" cellpadding=\"2\" border=\"0\">
			<tr>
				<td colspan=\"7\" align=\"center\">".headerKartu()."</td>
			</tr>
			<tr>
				<td colspan=\"7\"><hr></td>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\"><br>KARTU PIUTANG PBB-P2</td>
			</tr>
			<tr>
				<td>NOP</td><td width=\"8\">:</td><td width=\"220\">".$dtLatest['NOP']."</td>
				<td width=\"25\"></td>
				<td>NAMA</td><td  width=\"8\">:</td><td width=\"220\">".$dtLatest['WP_NAMA']."</td>
			</tr>
			<tr>
				<td>ALAMAT</td><td width=\"8\">:</td><td>".$dtLatest['OP_ALAMAT']."</td>
				<td></td>
				<td>ALAMAT</td><td  width=\"8\">:</td><td>".$dtLatest['WP_ALAMAT']."</td>
			</tr>
			<tr>
				<td>RT/RW</td><td width=\"8\">:</td><td>".$dtLatest['OP_RT']."/".$dtLatest['OP_RW']."</td>
				<td></td>
				<td>RT/RW</td><td  width=\"8\">:</td><td>".$dtLatest['WP_RT']."/".$dtLatest['WP_RW']."</td>
			</tr>
			<tr>
				<td>DESA/KEL</td><td width=\"8\">:</td><td>".$dtLatest['OP_KELURAHAN']."</td>
				<td></td>
				<td>DESA/KEL</td><td  width=\"8\">:</td><td>".$dtLatest['WP_KELURAHAN']."</td>
			</tr>
			<tr>
				<td>KEC</td><td width=\"8\">:</td><td>".$dtLatest['OP_KECAMATAN']."</td>
				<td></td>
				<td>KEC</td><td  width=\"8\">:</td><td>".$dtLatest['OP_KECAMATAN']."</td>
			</tr>
			<tr>
				<td></td><td width=\"8\"></td><td></td>
				<td></td>
				<td>KAB</td><td  width=\"8\">:</td><td>".strtoupper($dtLatest['WP_KOTAKAB'])."</td>
			</tr>
			<tr>
				<td></td><td width=\"8\"></td><td></td>
				<td></td>
				<td>PROV</td><td  width=\"8\">:</td><td>".$appConfig['NAMA_PROVINSI']."</td>
			</tr>
			<tr>
				<td colspan=\"7\"><hr></td>
			</tr>
			<tr>
				<td colspan=\"7\">
					<table cellpadding=\"2\">
						<tr>
							<td width=\"150\">LUAS BUMI</td><td width=\"8\">:</td><td>".$dtLatest['OP_LUAS_BUMI']." m<sup>2</sup></td>
						</tr>
						<tr>
							<td>LUAS BANGUNAN</td><td>:</td><td>".$dtLatest['OP_LUAS_BANGUNAN']." m<sup>2</sup></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"7\"><hr></td>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\">PIUTANG :</td>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\">
					<table cellpadding=\"2\" width=\"100%\" border=\"1\">
						<tr> 
							<td align=\"center\" width=\"50%\" >TAHUN</td>
							<td align=\"center\" width=\"50%\" >KETETAPAN<br>(RP)</td>
						</tr>
						".$thnTagihan."
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"6\" align=\"left\">TOTAL PIUTANG :</td><td align=\"right\">".$totalPiutang."&nbsp;</td>
			</tr>
			<tr>
				<td colspan=\"7\"><hr></td>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\">
					<br>
					".footer()."
				</td>
			</tr>
		</table>
	</div>
	";
	
	return $html;
}

function kartuPerKecamatan(){
	global $appConfig, $nop, $kecamatan;

	$dt = getDataPerKecamatan($kecamatan);
	
	if(!empty($dt)){
		$dtLatest	= $dt[0];
		if($dtLatest['OP_KECAMATAN']==""){
			echo "<br>Tidak ada data."; exit;
		}
	} else {
		echo "<br>Tidak ada data."; exit;
	}
	
	$thnTagihan 	= "";
	$totalPiutang 	= 0;
	$dtListPiutang 	= getListPiutangPerKec($kecamatan);
	$dtLuas		 	= getLuasPerKec($kecamatan);
	foreach($dtListPiutang as $d){
		$thnTagihan .= "
			<tr style=\"border: 1px solid black;\"> 
				<td align=\"center\" style=\"border: 1px solid black;\">".$d['SPPT_TAHUN_PAJAK']."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($d['PIUTANG_PERTAHUN'],0,',','.')."</td>
			</tr>";
			
		$totalPiutang += $d['PIUTANG_PERTAHUN'];
	}
	
	
	$html = "
		<div border=\"1\">
		<table cellpadding=\"2\" border=\"0\">
			<tr >
				<td colspan=\"3\" align=\"center\">".headerKartu()."</td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1.5px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\"><br>KARTU PIUTANG PBB-P2 KECAMATAN</td>
			</tr>
			<tr>
				<td width=\"200\">NAMA KECAMATAN</td><td width=\"8\">:</td><td width=\"465\">".$dtLatest['OP_KECAMATAN']."</td>
			</tr>
			<tr>
				<td>DESA/KEL</td><td width=\"8\">:</td><td>".number_format($dtLatest['JML_KEL'],0,',','.')."</td>
			</tr>
			<tr>
				<td>OBJEK PAJAK</td><td width=\"8\">:</td><td>".number_format($dtLatest['JML_OP'],0,',','.')."</td>
			</tr>
			<tr>
				<td>SUBJEK PAJAK</td><td width=\"8\">:</td><td>".number_format($dtLatest['JML_WP'],0,',','.')."</td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td>LUAS BUMI</td><td width=\"8\">:</td><td>".number_format($dtLuas[0]['LUAS_BUMI'],2,',','.')." m<sup>2</sup></td>
			</tr>
			<tr>
				<td>LUAS BANGUNAN</td><td width=\"8\">:</td><td>".number_format($dtLuas[0]['LUAS_BANGUNAN'],2,',','.')." m<sup>2</sup></td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\">PIUTANG :</td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\">
					<table cellpadding=\"2\">
						<tr style=\"border: 1px solid black;\"> 
							<td align=\"center\" style=\"border: 1px solid black;\">TAHUN</td>
							<td align=\"center\" style=\"border: 1px solid black;\">KETETAPAN<br>(RP)</td>
						</tr>
						".$thnTagihan."
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"2\" align=\"left\">TOTAL PIUTANG :</td><td align=\"right\">".number_format($totalPiutang,0,',','.')."&nbsp;</td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\">
					<br>
					<br>
					".footer()."
				</td>
			</tr>
		</table>
		</div>
	";
	
	return $html;
}

function kartuPerKelurahan(){
	global $appConfig, $kelurahan;
	
	$dt = getDataPerKelurahan($kelurahan);
	if(!empty($dt)){
		$dtLatest	= $dt[0];
		if($dtLatest['OP_KELURAHAN']==""){
			echo "<br>Tidak ada data."; exit;
		}
	} else {
		echo "<br>Tidak ada data."; exit;
	}
	
	$thnTagihan 	= "";
	$totalPiutang 	= 0;
	$dtListPiutang 	= getListPiutangPerKel($kelurahan);
	$dtLuas 		= getLuasPerKel($kelurahan);
	foreach($dtListPiutang as $d){
		$thnTagihan .= "
			<tr style=\"border: 1px solid black;\"> 
				<td align=\"center\" width=\"50%\" style=\"border: 1px solid black;\">".$d['SPPT_TAHUN_PAJAK']."</td>
				<td align=\"right\" width=\"50%\" style=\"border: 1px solid black;\">".number_format($d['PIUTANG_PERTAHUN'],0,',','.')."</td>
			</tr>";
			
		$totalPiutang += $d['PIUTANG_PERTAHUN'];
	}
	
	
	$html = "
		<div border=\"1\">
		<table cellpadding=\"2\" border=\"0\">
			<tr >
				<td colspan=\"3\" align=\"center\">".headerKartu()."</td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1.5px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\"><br>KARTU PIUTANG PBB-P2 DESA</td>
			</tr>
			<tr>
				<td width=\"120\">NAMA DESA/KEL</td><td width=\"8\">:</td><td width=\"545\">".$dtLatest['OP_KELURAHAN']."</td>
			</tr>
			<tr>
				<td>OBJEK PAJAK</td><td width=\"8\">:</td><td>".number_format($dtLatest['JML_OP'],0,',','.')."</td>
			</tr>
			<tr>
				<td>SUBJEK PAJAK</td><td width=\"8\">:</td><td>".number_format($dtLatest['JML_WP'],0,',','.')."</td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td>LUAS BUMI</td><td width=\"8\">:</td><td>".number_format($dtLuas[0]['LUAS_BUMI'],2,',','.')." m<sup>2</sup></td>
			</tr>
			<tr>
				<td>LUAS BANGUNAN</td><td width=\"8\">:</td><td>".number_format($dtLuas[0]['LUAS_BANGUNAN'],2,',','.')." m<sup>2</sup></td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\">PIUTANG :</td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\">
					<table cellpadding=\"2\" width=\"100%\" style=\"border : solid black 1px; border-collapse: collapse;\">
						<tr style=\"border: 1px solid black;\"> 
							<td align=\"center\" width=\"50%\" style=\"border: 1px solid black;\">TAHUN</td>
							<td align=\"center\" width=\"50%\" style=\"border: 1px solid black;\">KETETAPAN<br>(RP)</td>
						</tr>
						".$thnTagihan."
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"2\" align=\"left\">TOTAL PIUTANG :</td><td align=\"right\">".number_format($totalPiutang,0,',','.')."&nbsp;</td>
			</tr>
			<tr>
				<td colspan=\"3\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\">
					<br>
					".footer()."
				</td>
			</tr>
		</table>
		</div>
	";
	
	return $html;
}

function kartuInventaris(){
	global $appConfig, $kecamatan;

	$dt = getDataInventaris($kecamatan);
	
	if(empty($dt)){
		echo "<br>Tidak ada data."; exit;
	}
	
	$i = 1;
	$summary = array('TOTAL_JML_OP'=>0, 'TOTAL_JML_WP'=>0, 'GRAND_TOTAL_LS_BUMI'=>0, 'GRAND_TOTAL_LS_NAGUNAN'=>0);
	$listInv = "";
	foreach($dt as $d){
		$listInv .= "
			<tr style=\"border: 1px solid black;\"> 
				<td align=\"center\" style=\"border: 1px solid black;\">".$i."</td>
				<td align=\"left\" style=\"border: 1px solid black;\">".$d['OP_KELURAHAN']."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($d['JML_OP'],0,',','.')."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($d['JML_WP'],0,',','.')."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($d['SUM_LUAS_BUMI'],2,',','.')."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($d['SUM_LUAS_BANGUNAN'],2,',','.')."</td>
			</tr>";
		$i++;	
		$summary['TOTAL_JML_OP'] 			+= $d['JML_OP'];
		$summary['TOTAL_JML_WP'] 			+= $d['JML_WP'];
		$summary['GRAND_TOTAL_LS_BUMI'] 	+= $d['SUM_LUAS_BUMI'];
		$summary['GRAND_TOTAL_LS_NAGUNAN'] 	+= $d['SUM_LUAS_BANGUNAN'];
	}
	
	$listInv .= "
			<tr> 
				<td colspan=\"2\" style=\"border: 1px solid black;\">TOTAL</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($summary['TOTAL_JML_OP'],0,',','.')."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($summary['TOTAL_JML_WP'],0,',','.')."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($summary['GRAND_TOTAL_LS_BUMI'],2,',','.')."</td>
				<td align=\"right\" style=\"border: 1px solid black;\">".number_format($summary['GRAND_TOTAL_LS_NAGUNAN'],2,',','.')."</td>
			</tr>";
	
	
	$html = "
		<div border=\"1\">
		<table width=\"100%\" cellpadding=\"4\" border=\"0\">
			<tr >
				<td colspan=\"7\" align=\"center\">".headerKartu()."</td>
			</tr>
			<tr>
				<td colspan=\"7\"><hr style=\"border : 1.5px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\"><br>INVENTARIS PIUTANG PBB-P2 KECAMATAN ".$dt[0]['OP_KECAMATAN']."</td><br>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\">
					<table cellpadding=\"4\" width=\"100%\" style=\"border : solid black 1px; border-collapse: collapse;\">
						<tr style=\"border: 1px solid black;\"> 
							<td align=\"center\" width=\"30\" style=\"border: 1px solid black;\">NO</td>
							<td align=\"center\" style=\"border: 1px solid black;\" width=\"192\">DESA/KEL</td>
							<td align=\"center\" style=\"border: 1px solid black;\">OBJEK PAJAK</td>
							<td align=\"center\" style=\"border: 1px solid black;\">SUBJEK PAJAK</td>
							<td align=\"center\" style=\"border: 1px solid black;\">LUAS BUMI</td>
							<td align=\"center\" style=\"border: 1px solid black;\">LUAS BANGUNAN</td>
						</tr>
						".$listInv."
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"7\"><hr style=\"border : 1px solid black;\"></td>
			</tr>
			<tr>
				<td colspan=\"7\" align=\"center\">
					<br>
					".footer()."
				</td>
			</tr>
		</table>
		</div>
	";
	
	return $html;
}

function headerKartu() {
    global $appConfig;
	
    $html = "<br><br><b>PEMERINTAH KABUPATEN KUPANG<br>DINAS PENDAPATAN, PENGELOLAANKEUANGAN <br>DAN ASET DAERAH</b><br>JALAN TIMOR RAYA KM. 37 OELAMASI<br>";
    return $html;
}

function footer(){
	global $appConfig;
	$html = "OELAMASI, ".date("d-m-Y")."<br>
			KEPALA DINAS,
			<br>
			<br>
			<br>
			<br>
			<br>
			<u>".$appConfig['NAMA_PEJABAT_SK2']."</u><br>
			".$appConfig['NAMA_PEJABAT_SK2_JABATAN']."<br>
			NIP. ".$appConfig['NAMA_PEJABAT_SK2_NIP']."<br><br>";
	return $html;
}

// koneksi postgres
function openMysql() {
    global $appConfig;
    $host = $appConfig['GW_DBHOST'];
    $port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user = $appConfig['GW_DBUSER'];
    $pass = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname,$port);
    if (!$myDBLink) {
        echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname, $myDBLink);
    return $myDBLink;
}

function closeMysql($con) {
    mysqli_close($con);
}

function getKecamatan($p) {
    global $DBLink;
    $return = array();
    $query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='" . $p . "' ORDER BY CPC_TKC_URUTAN";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["id"] = $row["CPC_TKC_ID"];
        $data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
        $i++;
    }

    return $data;
}

function getKelurahan($p) {
    global $DBLink, $kelurahan;
    $query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
    // echo $query."<br>";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["id"] = $row["CPC_TKL_ID"];
        $data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
        $i++;
    }
    return $data;
}

function getDataPerNOP($nop){
	$nop 		= mysql_real_escape_string($nop);

	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT * FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> '1'
		AND NOP = '".$nop."' ";	
	
	$query .= "ORDER BY SPPT_TAHUN_PAJAK DESC";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['NOP'] 			= $row['NOP'];
		$data[$i]['OP_ALAMAT'] 		= $row['OP_ALAMAT'];
		$data[$i]['OP_RT'] 			= $row['OP_RT'];
		$data[$i]['OP_RW'] 			= $row['OP_RW'];
		$data[$i]['OP_KELURAHAN'] 	= $row['OP_KELURAHAN'];
		$data[$i]['OP_KECAMATAN'] 	= $row['OP_KECAMATAN'];
		$data[$i]['OP_LUAS_BUMI'] 	= $row['OP_LUAS_BUMI'];
		$data[$i]['OP_LUAS_BANGUNAN'] = $row['OP_LUAS_BANGUNAN'];
		$data[$i]['WP_NAMA'] 		= $row['WP_NAMA'];
		$data[$i]['WP_ALAMAT'] 		= $row['WP_ALAMAT'];
		$data[$i]['WP_RT'] 			= $row['WP_RT'];
		$data[$i]['WP_RW'] 			= $row['WP_RW'];
		$data[$i]['WP_KELURAHAN'] 	= $row['WP_KELURAHAN'];
		$data[$i]['WP_KECAMATAN'] 	= $row['WP_KECAMATAN'];
		$data[$i]['WP_KOTAKAB'] 	= $row['WP_KOTAKAB'];
		$data[$i]['SPPT_TAHUN_PAJAK'] = $row['SPPT_TAHUN_PAJAK'];
		$data[$i]['SPPT_PBB_HARUS_DIBAYAR'] = $row['SPPT_PBB_HARUS_DIBAYAR'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}

function getDataPerKecamatan($kd_kec){
	
	$kd_kec 	= mysql_real_escape_string($kd_kec);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			OP_KECAMATAN,
			OP_KECAMATAN_KODE,
			COUNT(NOP) AS JML_OP,
			COUNT(DISTINCT(ID_WP)) AS JML_WP,
			COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS JML_KEL
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KECAMATAN_KODE = '".$kd_kec."'
	";
	
	$query .= "GROUP BY OP_KECAMATAN_KODE ";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['OP_KECAMATAN'] 		= $row['OP_KECAMATAN'];
		$data[$i]['JML_KEL'] 			= $row['JML_KEL'];
		$data[$i]['JML_OP'] 			= $row['JML_OP'];
		$data[$i]['JML_WP'] 			= $row['JML_WP'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}

function getDataPerKelurahan($kd_kel){
	
	$kd_kel 	= mysql_real_escape_string($kd_kel);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			OP_KELURAHAN,
			OP_KELURAHAN_KODE,
			COUNT(NOP) AS JML_OP,
			COUNT(DISTINCT(ID_WP)) AS JML_WP
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KELURAHAN_KODE = '".$kd_kel."'
	";
	
	$query .= "GROUP BY OP_KECAMATAN_KODE ";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['OP_KELURAHAN'] 		= $row['OP_KELURAHAN'];
		$data[$i]['JML_OP'] 			= $row['JML_OP'];
		$data[$i]['JML_WP'] 			= $row['JML_WP'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}

// function getDataInventaris($kd_kec){
	// global $appConfig;
	
	// $kd_kec 	= mysql_real_escape_string($kd_kec);
	
	// $data 		= array();
	// $myDBLink 	= openMysql();
	
	// $query = "
		// SELECT
			// OP_KECAMATAN,
			// OP_KECAMATAN_KODE,
			// OP_KELURAHAN,
			// COUNT(DISTINCT(NOP)) AS JML_OP,
			// COUNT(DISTINCT(ID_WP)) AS JML_WP,
			// SUM(OP_LUAS_BUMI) AS SUM_LUAS_BUMI,
			// SUM(OP_LUAS_BANGUNAN) AS SUM_LUAS_BANGUNAN
		// FROM
			// PBB_SPPT
		// WHERE
			// PAYMENT_FLAG <> 1
		// AND OP_KECAMATAN_KODE = '".$kd_kec."' AND SPPT_TAHUN_PAJAK = '".mysql_real_escape_string($appConfig['tahun_tagihan'])."' 
	// ";
	
	// $query .= "GROUP BY OP_KELURAHAN_KODE ";
	
	// // echo $query;
    
    // $res = mysqli_query($myDBLink, $query);
    // if ($res === false) {
        // echo mysqli_error($DBLink);
        // exit();
    // }
	
	// // $row = mysqli_fetch_assoc($res);

	// $i = 0;
	// while ($row = mysqli_fetch_assoc($res)) {      
		// $data[$i]['OP_KECAMATAN'] 		= $row['OP_KECAMATAN'];
		// $data[$i]['OP_KELURAHAN'] 		= $row['OP_KELURAHAN'];
		// $data[$i]['JML_OP'] 			= $row['JML_OP'];
		// $data[$i]['JML_WP'] 			= $row['JML_WP'];
		// $data[$i]['SUM_LUAS_BUMI'] 		= $row['SUM_LUAS_BUMI'];
		// $data[$i]['SUM_LUAS_BANGUNAN'] 	= $row['SUM_LUAS_BANGUNAN'];
		// $i++;
    // }
	
	// // echo "<pre>";
	// // print_r($data); exit;
	
    // closeMysql($myDBLink);
    // return $data;
// }

function getCountDataInventaris($kd_kel){
	global $appConfig;
	
	$kd_kel 	= mysql_real_escape_string($kd_kel);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			OP_KECAMATAN,
			OP_KECAMATAN_KODE,
			OP_KELURAHAN,
			COUNT(DISTINCT(NOP)) AS JML_OP,
			COUNT(DISTINCT(ID_WP)) AS JML_WP,
			SUM(OP_LUAS_BUMI) AS SUM_LUAS_BUMI,
			SUM(OP_LUAS_BANGUNAN) AS SUM_LUAS_BANGUNAN
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KELURAHAN_KODE = '".$kd_kel."' AND SPPT_TAHUN_PAJAK = '".mysql_real_escape_string($appConfig['tahun_tagihan'])."' 
	";
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

	while ($row = mysqli_fetch_assoc($res)) {      
		$data['OP_KECAMATAN'] 		= $row['OP_KECAMATAN'];
		$data['OP_KELURAHAN'] 		= $row['OP_KELURAHAN'];
		$data['JML_OP'] 			= $row['JML_OP'];
		$data['JML_WP'] 			= $row['JML_WP'];
		$data['SUM_LUAS_BUMI'] 		= $row['SUM_LUAS_BUMI'];
		$data['SUM_LUAS_BANGUNAN'] 	= $row['SUM_LUAS_BANGUNAN'];
    }
	
    closeMysql($myDBLink);
    return $data;
}

function getDataInventaris($kd_kec){
	global $appConfig;
	
	$kd_kec 	= mysql_real_escape_string($kd_kec);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$kelurahan = getKelurahan($kd_kec);
	
	$i = 0;
	foreach ($kelurahan as $kel){
		$dtInv = getCountDataInventaris($kel['id']);
		$data[$i]['OP_KELURAHAN'] 		= $kel['name'];
		$data[$i]['OP_KECAMATAN'] 		= $dtInv['OP_KECAMATAN'];
		$data[$i]['JML_OP'] 			= $dtInv['JML_OP'];
		$data[$i]['JML_WP'] 			= $dtInv['JML_WP'];
		$data[$i]['SUM_LUAS_BUMI'] 		= $dtInv['SUM_LUAS_BUMI'];
		$data[$i]['SUM_LUAS_BANGUNAN'] 	= $dtInv['SUM_LUAS_BANGUNAN'];
		$i++;
	}
	
    return $data;
}

function getLuasPerKec($kd_kec){
	
	$kd_kec 	= mysql_real_escape_string($kd_kec);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			SPPT_TAHUN_PAJAK, SUM(OP_LUAS_BUMI) AS LUAS_BUMI, SUM(OP_LUAS_BANGUNAN) AS LUAS_BANGUNAN
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KECAMATAN_KODE = '".$kd_kec."'
	";
	
	$query .= "GROUP BY SPPT_TAHUN_PAJAK ORDER BY SPPT_TAHUN_PAJAK DESC";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['SPPT_TAHUN_PAJAK'] 	= $row['SPPT_TAHUN_PAJAK'];
		$data[$i]['LUAS_BUMI'] 			= $row['LUAS_BUMI'];
		$data[$i]['LUAS_BANGUNAN'] 		= $row['LUAS_BANGUNAN'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}

function getLuasPerKel($kd_kel){
	
	$kd_kel 	= mysql_real_escape_string($kd_kel);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			SPPT_TAHUN_PAJAK, SUM(OP_LUAS_BUMI) AS LUAS_BUMI, SUM(OP_LUAS_BANGUNAN) AS LUAS_BANGUNAN
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KELURAHAN_KODE = '".$kd_kel."'
	";
	
	$query .= "GROUP BY SPPT_TAHUN_PAJAK ORDER BY SPPT_TAHUN_PAJAK DESC";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['SPPT_TAHUN_PAJAK'] 	= $row['SPPT_TAHUN_PAJAK'];
		$data[$i]['LUAS_BUMI'] 			= $row['LUAS_BUMI'];
		$data[$i]['LUAS_BANGUNAN'] 		= $row['LUAS_BANGUNAN'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}

function getListPiutangPerKec($kd_kec){
	
	$kd_kec 	= mysql_real_escape_string($kd_kec);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			SPPT_TAHUN_PAJAK, SUM(SPPT_PBB_HARUS_DIBAYAR) AS PIUTANG_PERTAHUN
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KECAMATAN_KODE = '".$kd_kec."'
	";
	
	$query .= "GROUP BY OP_KECAMATAN_KODE, SPPT_TAHUN_PAJAK ";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['SPPT_TAHUN_PAJAK'] 	= $row['SPPT_TAHUN_PAJAK'];
		$data[$i]['PIUTANG_PERTAHUN'] 	= $row['PIUTANG_PERTAHUN'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}

function getListPiutangPerKel($kd_kel){
	
	$kd_kel 	= mysql_real_escape_string($kd_kel);
	
	$data 		= array();
	$myDBLink 	= openMysql();
	
	$query = "
		SELECT
			SPPT_TAHUN_PAJAK, SUM(SPPT_PBB_HARUS_DIBAYAR) AS PIUTANG_PERTAHUN
		FROM
			PBB_SPPT
		WHERE
			PAYMENT_FLAG <> 1
		AND OP_KELURAHAN_KODE = '".$kd_kel."'
	";
	
	$query .= "GROUP BY OP_KELURAHAN_KODE, SPPT_TAHUN_PAJAK ";
	
	// echo $query;
    
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }
	
	// $row = mysqli_fetch_assoc($res);

	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {      
		$data[$i]['SPPT_TAHUN_PAJAK'] 	= $row['SPPT_TAHUN_PAJAK'];
		$data[$i]['PIUTANG_PERTAHUN'] 	= $row['PIUTANG_PERTAHUN'];
		$i++;
    }
	
	// echo "<pre>";
	// print_r($data); exit;
	
    closeMysql($myDBLink);
    return $data;
}
