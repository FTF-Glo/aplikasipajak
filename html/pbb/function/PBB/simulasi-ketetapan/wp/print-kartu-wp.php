<?php

$DIR = "PBB";
$modul = "nop";

ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul . DIRECTORY_SEPARATOR . 'wp', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
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
require_once($sRootPath . "inc/PBB/dbWajibPajak.php");

// echo $sRootPath;

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


$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
if ($q == "")
	exit(1);
$q = base64_decode($q);
$q = $json->decode($q);
$wpid 	= $q->wpid;
$appid 	= $q->appid;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink 	= $User->GetDbConnectionFromApp($appid);
$dbSpec 	= new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

function getConfigValue($key)
{
	global $DBLink, $appid;
	$qry = "select * from central_app_config where CTR_AC_AID = '" . $appid . "' and CTR_AC_KEY = '$key'";

	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getJns($pekerjaan)
{
	if ($pekerjaan == 'Badan')
		return $pekerjaan;
	else
		return "Perseorangan";
}

function getBarcodeID($pekerjaan)
{
	global $wpid;

	if ($pekerjaan == 'Badan')
		$barcodeID = '2' . $wpid;
	else
		$barcodeID = '1' . $wpid;

	return $barcodeID;
}

if (stillInSession($DBLink, $json, $sdata)) {

	$dbWajibPajak 	= new DbWajibPajak($dbSpec);
	$filter			= array();
	if ($wpid != "") $filter['CPM_WP_ID'] = $wpid;
	$data		  	= $dbWajibPajak->get($filter, $perpage = "", $page = "");

	// print_r($data);

	$KOTA				= getConfigValue('KOTA');
	$LOGO_CETAK_PDF 	= getConfigValue('LOGO_CETAK_PDF');

	$html = "<html><div border=\"1\"><table border=\"0\" cellpadding=\"2\"  width=\"297\">
				<tr>
					<td>
						<table border=\"0\" style=\"width:100%\">
						  <tr>
							<td rowspan=\"3\" width=\"50\"></td>
						  </tr>
						  <tr>
							<td align=\"center\"><font size=\"10\">PEMERINTAH KOTA " . strtoupper($KOTA) . "</font></td>
						  </tr>
						  <tr>
							<td align=\"center\"><font size=\"7\">Dinas Pendapatan Pengelolaan Keuangan dan Aset Daerah</font><br></td>
						  </tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align=\"center\"><hr style=\"height: 1px;\"><br>KARTU WAJIB PAJAK</td>
				</tr>
				<tr>
					<td>
						<table cellpadding=\"3\" border=\"0\">
							<tr>
								<td width=\"50\">IDWP</td><td width=\"10\">:</td><td width=\"235\">" . $data[0]['CPM_WP_ID'] . "</td>
							</tr>
							<tr>
								<td>NAMA</td><td>:</td><td>" . $data[0]['CPM_WP_NAMA'] . "</td>
							</tr>
							<tr>
								<td>ALAMAT</td><td>:</td><td>" . $data[0]['CPM_WP_ALAMAT'] . "</td>
							</tr>
							<tr>
								<td>JENIS</td><td>:</td><td>" . getJns($data[0]['CPM_WP_PEKERJAAN']) . "</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align=\"center\"></td>
				</tr>
				<tr>
					<td align=\"center\"></td>
				</tr>
			</table></div></html>";

	$style = array(
		'position' => 'fixed',
		'align' => 'C',
		'stretch' => false,
		'fitwidth' => true,
		'cellfitalign' => '',
		'border' => false,
		'hpadding' => 'auto',
		'vpadding' => 'auto',
		'fgcolor' => array(0, 0, 0),
		'bgcolor' => false, //array(255,255,255),
		'text' => true,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 3
	);

	$pagelayout = array(85.6, 54);
	// $pagelayout = array(210, 500);
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pagelayout, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('vpost');
	$pdf->SetTitle('Alfa System');
	$pdf->SetSubject('Alfa System spppd');
	$pdf->SetKeywords('Alfa System');
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	$pdf->SetAutoPageBreak(TRUE, 0);
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(1, 1, 1);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->SetFont('helvetica', '', 7);
	$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
	$resolution = array(87, 56);
	$pdf->AddPage('L', $resolution);
	// $pdf->AddPage('L', 'A6');
	$pdf->writeHTML($html, true, false, false, false, '');
	$pdf->write1DBarcode(getBarcodeID($data[0]['CPM_WP_PEKERJAAN']), 'C128', '9', '43', '', 12, 0.4, $style, 'N');
	$pdf->Image($sRootPath . 'image/' . $LOGO_CETAK_PDF, 3, 3, 12, '', '', '', '', false, 300, '', false);
	$pdf->SetAlpha(0.3);
	$pdf->Output('kartuwp-' . getBarcodeID($data[0]['CPM_WP_PEKERJAAN']) . '.pdf', 'I');
} else {
	echo "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
