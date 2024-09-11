<?php
ini_set('memory_limit','500M');
ini_set ("max_execution_time", "100000");

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembayaran_va', '', dirname(__FILE__))) . '/';

require_once("tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once("classCollective.php");
$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$dbUtils = new DbUtils($dbSpec);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$appConfig 	= $User->GetAppConfig("aPBB");
$tahun		= $appConfig['tahun_tagihan'];
$host 	= $appConfig['GW_DBHOST'];
$port 	= $appConfig['GW_DBPORT'];
$user 	= $appConfig['GW_DBUSER'];
$pass 	= $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME']; 

// $group_id = $_REQUEST['id'];
// print_r($group_id);
// exit;

$svcCollective = new classCollective($dbSpec, $dbUtils);
$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER = $user;
$svcCollective->C_PWD = $pass;
$svcCollective->C_DB = $dbname;
$svcCollective->C_PORT = $port;

$data = $svcCollective->getMemberByIDArray($_REQUEST['id']);
$NAMA_GROUP = strtoupper($data[0]['NAMA_GROUP']);
// echo "<pre>";
// print_r($data);
// echo "</pre>";
// exit;



// exit;
$c  = count($data);
if($c>0){
	// $sum_harus_dibayar 	= 0;
	// $sum_denda			= 0;
	// $isiTable = "";
	// $i		  = 1;
	foreach($data as $dt){
		// if($nop==""){
		// 	$dtNOP = substr($dt['NOP'],0,2).'.'.substr($dt['NOP'],2,2).'.'.substr($dt['NOP'],4,3).'.'.substr($dt['NOP'],7,3).'.'.substr($dt['NOP'],10,3).'-'.substr($dt['NOP'],13,4).'.'.substr($dt['NOP'],17,1);
		// } else {
		// 	$dtNOP = $dt['WP_NAMA'];
		// }
		$isiTable .= "
			<tr>
				<td>".$dt['NOP']."</td>
				<td align=\"center\">".$dt['SPPT_TAHUN_PAJAK']."</td>
				<td align=\"right\">Rp ".number_format($dt['SPPT_PBB_HARUS_DIBAYAR'])."</td>
				<td align=\"right\">Rp ".$dt['PBB_DENDA']."</td>
				<td align=\"right\">".substr($dt['SPPT_TANGGAL_JATUH_TEMPO'],8,2)."/".substr($dt['SPPT_TANGGAL_JATUH_TEMPO'],5,2)."/".substr($dt['SPPT_TANGGAL_JATUH_TEMPO'],0,4)."</td>
				<td align=\"right\">Rp ".number_format($dt['SPPT_PBB_HARUS_DIBAYAR'])."</td>
				<td align=\"center\">BELUM BAYAR</td>
			</tr>";
		$sum_harus_dibayar += ($dt['SPPT_PBB_HARUS_DIBAYAR']); 
		$sum_denda 		   += ($dt['PBB_DENDA']); 
		$i++;
	}
// 	var_dump($isiTable);
// exit;
	// if($nop==""){
	// 	$fdNOP = "NOP";
	// } else {
	// 	$fdNOP = "NAMA <br>WAJIB PAJAK";
	// }
	
	$bulan = array(
		"01" => "Januari",
		"02" => "Februari",
		"03" => "Maret",
		"04" => "April",
		"05" => "Mei",
		"06" => "Juni",
		"07" => "Juli",
		"08" => "Agustus",
		"09" => "September",
		"10" => "Oktober",
		"11" => "November",
		"12" => "Desember"
	);
	
// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);
	$html = "
	<html>
		<table width=\"100%\" border=\"0\">
			<tr>
				<td colspan=\"3\" align=\"center\">
					".$appConfig ['C_HEADER_DISPOSISI']."<br>
					".$appConfig ['C_ALAMAT_DISPOSISI']."<br>
					<hr>
				</td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\"><b>INFORMASI DATA PEMBAYARAN KOLEKTIF ".$NAMA_GROUP."</b></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
				
				</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
					<table border=\"1\" width=\"100%\" cellpadding=\"2\">
					<tr>
						<td align=\"center\" width=\"19%\">NOP</td>
						<td align=\"center\" width=\"7%\">TAHUN PAJAK</td>
						<td align=\"center\" width=\"14%\">PBB</td>
						<td align=\"center\" width=\"14%\">DENDA (*)</td>
						<td align=\"center\" width=\"10%\">JATUH<Br>TEMPO</td>
						<td align=\"center\" width=\"13%\">KURANG <br>BAYAR</td>
						<td align=\"center\" width=\"23%\">STATUS <br>BAYAR</td>
					</tr>
					".$isiTable."
					<!-- <tr>
						<td align=\"center\" colspan=\"5\">TOTAL</td>
						<td align=\"right\">Rp ".$sum_harus_dibayar."</td>
						<td align=\"center\"></td>
					</tr>-->
				</table>
				</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\">
					<table border=\"0\" width=\"100%\" cellpadding=\"1\">
						<tr>
							<td align=\"left\" width=\"50%\" style=\"border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">TOTAL PBB YANG BELUM DIBAYAR</td>
							<td align=\"right\" width=\"50%\" style=\"border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">Rp ".number_format($sum_harus_dibayar)."</td>
						</tr>
						<tr>
							<td align=\"left\" width=\"50%\" style=\"border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">TOTAL DENDA (SESUAI TANGGAL <i>PRINTOUT</i>)</td>
							<td align=\"right\" width=\"50%\" style=\"border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">Rp </td>
						</tr>
						<tr>
							<td align=\"left\" width=\"50%\" style=\"border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">JUMLAH YANG HARUS DIBAYAR</td>
							<td align=\"right\" width=\"50%\" style=\"border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;\">Rp </td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td colspan=\"3\">*Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.</td>
			</tr>
			
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td>Petugas : .......................................................</td>
				<td></td>
				<td align=\"center\">
					".$appConfig['NAMA_KOTA_PENGESAHAN'].", ".date('d')." ".$bulan[date('m')]." ".date('Y')."
				</td>
			</tr>
			<tr>
				<td colspan=\"3\">Keperluan : .......................................................</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align=\"center\">............................................................</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align=\"center\">............................................................</td>
			</tr>
		</table>
	</html>";
} else {
	$html = 'Data tidak tersedia';
}
// echo $html;
// exit;

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('pbb');
$pdf->SetSubject('pbb');
$pdf->SetKeywords('pbb');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 14, 10);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$pdf->AddPage('PL', 'A4');
// $pdf->Image('logo.png', 10, 10, 20, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->SetAlpha(0.3);
$pdf->Output('Data Tagihan PBB.pdf', 'I');
