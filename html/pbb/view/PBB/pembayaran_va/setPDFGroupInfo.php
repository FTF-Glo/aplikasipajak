<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

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

$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting     = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$appConfig     = $User->GetAppConfig("aPBB");
// print_r($appConfig);
$tahun    = $appConfig['tahun_tagihan'];
$host     = $appConfig['GW_DBHOST'];
$port     = $appConfig['GW_DBPORT'];
$user     = $appConfig['GW_DBUSER'];
$pass     = $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];

$group_id = $_REQUEST['id'];
// print_r($group_id);
// exit;

$svcCollective = new classCollective($dbSpec, $dbUtils);
$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER = $user;
$svcCollective->C_PWD = $pass;
$svcCollective->C_DB = $dbname;
$svcCollective->C_PORT = $port;

$data = $svcCollective->getDetailGroup($group_id);
$total_pembayaran = $data[0]['TOTAL_DENDA'] + $data[0]['CPM_CG_ORIGINAL_AMOUNT'];
/*
 echo "<pre>";
 print_r($data);
 echo "</pre>";
*/
// exit;
$isiTable = "";

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
// exit;

$html = "
	<html>
		<table width=\"100%\" border=\"0\">
			<tr>
				<td colspan=\"3\" align=\"center\">
					" . $appConfig['C_HEADER_DISPOSISI'] . "<br>
					" . $appConfig['C_ALAMAT_DISPOSISI'] . "<br>
					<hr>
				</td>
			</tr>
			<tr>
				<td colspan=\"3\" align=\"center\"><b>SURAT PENGANTAR PEMBAYARAN KOLEKTIF </b></td>
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

					<table border=\"0\" width=\"100%\" cellpadding=\"5\">
						<tr><td width=\"28%\">ID Group</td><td width=\"2%\">:</td><td width=\"70%\">$group_id</td></tr>
						<tr><td>Nama Group</td><td>:</td><td>" . $data[0]['CPM_CG_NAME'] . "</td></tr>
						<tr><td>Keterangan</td><td>:</td><td>" . $data[0]['CPM_CG_DESC'] . "</td></tr>
						<tr><td>Nama Kolektor </td><td>:</td><td>" . $data[0]['CPM_CG_COLLECTOR'] . "</td></tr>
						<tr><td>HP Kolektor</td><td>:</td><td>" . $data[0]['CPM_CG_HP_COLLECTOR'] . "</td></tr>
						<tr><td>Kecamatan</td><td>:</td><td>" . $data[0]['NAMA_KECAMATAN'] . "</td></tr>
						<tr><td>Kelurahan</td><td>:</td><td>" . $data[0]['NAMA_KELURAHAN'] . "</td></tr>
						<tr><td>Kode Pembayaran</td><td>:</td><td>" . $data[0]['CPM_CG_PAYMENT_CODE'] . "</td></tr>
						<tr><td>Jumlah NOP</td><td>:</td><td>" . number_format($data[0]['CPM_CG_NOP_NUMBER']) . "</td></tr>
						<tr><td>Denda</td><td>:</td><td> Rp. " . number_format($data[0]['TOTAL_DENDA'],0,',','.') . " * </td></tr>
						<tr><td>Pokok Pembayaran</td><td>:</td><td> Rp. " . number_format($data[0]['CPM_CG_ORIGINAL_AMOUNT'],0,',','.') . "</td></tr>
						<tr><td>Total Pembayaran</td><td>:</td><td> Rp. " . number_format($total_pembayaran,0,',','.') . "</td></tr>
						<!-- tr><td>Tanggal Kadaluarsa</td><td>:</td><td>" . date("d M Y H:i", strtotime($data[0]['CPM_CG_EXPIRED_DATE'])) . "</td></tr -->
						<tr><td>Status</td><td>:</td><td>" . $data[0]['STATUS_NAME'] . "</td></tr>
					</table>
					
				</td>
			</tr>
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			
			<tr>
				<td colspan=\"3\">* Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.</td>
			</tr>
			<!--<tr>
				<td colspan=\"3\">*Pembayaran menggunakan BRI Virtual Account (BRIVA) <b>11046</b> + Kode Bayar.</td>
			</tr>-->

			
			<tr>
				<td colspan=\"3\"></td>
			</tr>
			<tr>
				<td>Petugas : .......................................................</td>
				<td></td>
				<td align=\"center\">
					" . $data[0]['NAMA_KECAMATAN'] /*$appConfig['NAMA_KOTA_PENGESAHAN']*/ . ", " . date('d') . " " . $bulan[date('m')] . " " . date('Y') . "
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
// } else {
// 	$html = 'Data tidak tersedia';
// }
// echo $html;
// exit;
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
$pdf->Image('logo_lampung.png', 10, 10, 20, '', '', '', '', false, 300, '', false);
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->SetAlpha(0.3);
$pdf->Output('Data Tagihan PBB.pdf', 'I');
