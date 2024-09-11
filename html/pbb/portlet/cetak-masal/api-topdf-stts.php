<?php
if (isset($_REQUEST['group_id'])) {
	$CPM_GROUP_ID = $_REQUEST['group_id'];
} else {
	$CPM_GROUP_ID = NULL;
}
// var_dump($CPM_GROUP_ID);
// exit;
// print_r($_RE)
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'portlet' . DIRECTORY_SEPARATOR . 'cetak-masal', '', dirname(__FILE__))) . '/';
date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/payment/cdatetime.php");
require_once($sRootPath . "inc/payment/error-messages.php");

require_once($sRootPath . "inc/report/eng-report.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/payment/nid.php");

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

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
function bulan($bln)
{
	$bulan = $bln;
	switch ($bulan) {
		case 1:
			$bulan = "Januari";
			break;
		case 2:
			$bulan = "Februari";
			break;
		case 3:
			$bulan = "Maret";
			break;
		case 4:
			$bulan = "April";
			break;
		case 5:
			$bulan = "Mei";
			break;
		case 6:
			$bulan = "Juni";
			break;
		case 7:
			$bulan = "Juli";
			break;
		case 8:
			$bulan = "Agustus";
			break;
		case 9:
			$bulan = "September";
			break;
		case 10:
			$bulan = "Oktober";
			break;
		case 11:
			$bulan = "November";
			break;
		case 12:
			$bulan = "Desember";
			break;
	}
	return $bulan;
}

// var_dump()
// echo "masuk";
function getData()
{
	global $DBLinkGW, $appConfig, $kd_kel, $tahun, $blok, $blok2, $kd_buku, $CPM_GROUP_ID;

	$query = "SELECT
			A.NOP,
			A.SPPT_TAHUN_PAJAK,
			A.WP_NAMA,
			A.OP_KECAMATAN,
			A.OP_KELURAHAN,
			A.SPPT_TANGGAL_JATUH_TEMPO,
			A.OP_LUAS_BUMI,
			A.OP_LUAS_BANGUNAN,
			A.SPPT_PBB_HARUS_DIBAYAR,
			A.PBB_DENDA SPPT_DENDA,
			IFNULL(
			A.PAYMENT_PAID,
			DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
			) AS PAYMENT_PAID
			FROM
			PBB_SPPT A
			INNER JOIN CPPMOD_CG_MEMBER M ON M.CPM_CGM_NOP = A.NOP
			AND M.CPM_CGM_TAX_YEAR = A.SPPT_TAHUN_PAJAK
			INNER JOIN CPPMOD_COLLECTIVE_GROUP G ON M.CPM_CGM_ID = G.CPM_CG_ID
			WHERE
			G.CPM_CG_ID = '$CPM_GROUP_ID' 
				 ";
	// echo $query; 
	// exit;
	// SUBSTR(A.NOP,1,10) = '".$kd_kel."'
	$res = mysqli_query($DBLinkGW, $query);
	if ($res === false) {
		echo mysqli_error($DBLinkGW);
		exit();
	}

	$data  = array();
	$j     = 0;
	while ($row  = mysqli_fetch_assoc($res)) {
		$nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
		$denda = $row['SPPT_DENDA'];
		$total = $nilai + $denda;

		$row['TEMPAT_BAYAR'] 		= $appConfig['TEMPAT_PEMBAYARAN'];
		$row['THN_BAYAR'] 			= $row['SPPT_TAHUN_PAJAK'];
		$row['THN_DARI'] 			= date('Y');
		$row['SUBJECT_NAME'] 		= $row['WP_NAMA'];
		$row['OBJECT_KECAMATAN'] 	= $row['OP_KECAMATAN'];
		$row['OBJECT_KELURAHAN']	= $row['OP_KELURAHAN'];
		$row['NOPNPWP'] 			= substr($row['NOP'], 0, 2) . '.' . substr($row['NOP'], 2, 2) . '.' . substr($row['NOP'], 4, 3) . '.' . substr($row['NOP'], 7, 3) . '.' . substr($row['NOP'], 10, 3) . '-' . substr($row['NOP'], 13, 4) . '.' . substr($row['NOP'], 17, 1);
		$row['TRAN_AMOUNT_TEXT'] 	= number_format($nilai, 2, ',', '.');;
		$row['JTHTMP'] 				= $row['SPPT_TANGGAL_JATUH_TEMPO'];
		$row['TGL_BAYAR'] 			= $row['PAYMENT_PAID'];
		$row['LT'] 					= $row['OP_LUAS_BUMI'];
		$row['LB'] 					= $row['OP_LUAS_BANGUNAN'];
		$row['TOT_TRAN_AMOUNT_TEXT'] = number_format($total, 2, ',', '.');

		for ($i = 1; $i <= 24; $i++) {
			$totalBulan = $nilai + (((2 / 100) * $nilai) * $i);
			$row["BAYAR_PLUS_DENDA_$i"] = number_format($totalBulan, 2, ',', '.');
		}

		$data[$j] = $row;
		$j++;
	}
	return $data;
}

function buildHTML($dt)
{
	global $appConfig, $tahun;
	$bulan = array(
		'01' => 'Januari',
		'02' => 'Februari',
		'03' => 'Maret',
		'04' => 'April',
		'05' => 'Mei',
		'06' => 'Juni',
		'07' => 'Juli',
		'08' => 'Agustus',
		'09' => 'September',
		'10' => 'Oktober',
		'11' => 'November',
		'12' => 'Desember'
	);
	return $html = "
		<html>
			<table cellpadding=\"2\" border=\"0\">
				<tr>
					<td align=\"left\" colspan=\"3\" width=\"50%\">
						<B><font size=\"8\">PEMERINTAH KABUPATEN PANDEGLANG<br>DINAS PENDAPATAN DAERAH</font></B>
					</td>
					<td align=\"left\" colspan=\"2\" width=\"50%\">
					</td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"5\">
						<B>SURAT TANDA TERIMA SETORAN<br>PAJAK BUMI DAN BANGUNAN</B>
					</td>
				</tr>
				<tr>
					<td width=\"25%\">Tempat Pembayaran</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['TEMPAT_BAYAR'] . "</td>
				</tr>
				<tr>
					<td colspan=\"5\">Telah menerima pembayaran PBB Tahun " . $dt['THN_BAYAR'] . " dari:</td>
				</tr>
				<tr>
					<td width=\"25%\">Nama Wajib Pajak</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['SUBJECT_NAME'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">Letak Objek Pajak</td>
					<td width=\"75%\" colspan=\"4\">: Kecamatan " . $dt['OBJECT_KECAMATAN'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\"></td>
					<td width=\"75%\" colspan=\"4\">: Kelurahan " . $dt['OBJECT_KELURAHAN'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">No SPPT (NOP)</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['NOPNPWP'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">Sejumlah (Rp)</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['TRAN_AMOUNT_TEXT'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;border-top: 1px solid black;\">Tanggal Jatuh Tempo</td>
					<td width=\"75%\" style=\"border-right: 1px solid black;border-top: 1px solid black;\" colspan=\"4\">: " . substr($dt['JTHTMP'], 8, 2) . " " . $bulan[substr($dt['JTHTMP'], 5, 2)] . " " . substr($dt['JTHTMP'], 0, 4) . "</td>
				</tr>
				<tr>
					<td colspan=\"5\" style=\"border-left: 1px solid black;border-right: 1px solid black;\">Jumlah yang harus dibayar (termasuk denda) jika pembayaran<br>dilakukan pada bulan ke (setelah tanggal jatuh tempo)</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">I</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_1'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XIII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_13'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">II</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_2'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XIV</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_14'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">III</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_3'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XV</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_15'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">IV</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_4'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XVI</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_16'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">V</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_5'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XVII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_17'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">VI</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_6'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XVIII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_18'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">VII</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_7'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XIX</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_19'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">VIII</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_8'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XX</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_20'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">IX</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_9'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XXI</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_21'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">X</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_10'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XXII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_22'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">XI</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_11'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XXIII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_23'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-bottom: 1px solid black;border-left: 1px solid black;\">XII</td>
					<td width=\"20%\" style=\"border-bottom: 1px solid black;\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_12'] . "</td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;\"></td>
					<td width=\"25%\" style=\"border-bottom: 1px solid black;\">XXIV</td>
					<td width=\"20%\" style=\"border-bottom: 1px solid black;border-right: 1px solid black;\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_24'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">Tanggal Pembayaran</td>
					<td width=\"30%\" colspan=\"2\">: " . substr($dt['TGL_BAYAR'], 8, 2) . " " . $bulan[substr($dt['TGL_BAYAR'], 5, 2)] . " " . substr($dt['TGL_BAYAR'], 0, 4) . "</td>
					<td width=\"25%\"> LT : " . $dt['LT'] . "</td>
					<td width=\"20%\" rowspan=\"3\" align=\"center\">Tanda Terima<br>dan<br>Cap Bank/Pos</td>
				</tr>
				<tr>
					<td width=\"25%\">Jumlah yang dibayar</td>
					<td width=\"30%\" colspan=\"2\" style=\"border:1px solid black;\"> " . $dt['TOT_TRAN_AMOUNT_TEXT'] . "</td>
					<td width=\"25%\"> LB : " . $dt['LB'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">(Rp)</td>
					<td width=\"30%\" colspan=\"2\"></td>
					<td width=\"25%\"></td>
				</tr>
			</table>
			<br>
			<br>
			<br>
			<table cellpadding=\"2\" border=\"0\">
				<tr>
					<td align=\"left\" colspan=\"3\" width=\"50%\">
						<B><font size=\"8\">PEMERINTAH KABUPATEN PANDEGLANG<br>DINAS PENDAPATAN DAERAH</font></B>
					</td>
					<td align=\"left\" colspan=\"2\" width=\"50%\">
					</td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"5\">
						<B>SURAT TANDA TERIMA SETORAN<br>PAJAK BUMI DAN BANGUNAN</B>
					</td>
				</tr>
				<tr>
					<td width=\"25%\">Tempat Pembayaran</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['TEMPAT_BAYAR'] . "</td>
				</tr>
				<tr>
					<td colspan=\"5\">Telah menerima pembayaran PBB Tahun " . $dt['THN_BAYAR'] . " dari:</td>
				</tr>
				<tr>
					<td width=\"25%\">Nama Wajib Pajak</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['SUBJECT_NAME'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">Letak Objek Pajak</td>
					<td width=\"75%\" colspan=\"4\">: Kecamatan " . $dt['OBJECT_KECAMATAN'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\"></td>
					<td width=\"75%\" colspan=\"4\">: Kelurahan " . $dt['OBJECT_KELURAHAN'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">No SPPT (NOP)</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['NOPNPWP'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">Sejumlah (Rp)</td>
					<td width=\"75%\" colspan=\"4\">: " . $dt['TRAN_AMOUNT_TEXT'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;border-top: 1px solid black;\">Tanggal Jatuh Tempo</td>
					<td width=\"75%\" style=\"border-right: 1px solid black;border-top: 1px solid black;\" colspan=\"4\">: " . substr($dt['JTHTMP'], 8, 2) . " " . $bulan[substr($dt['JTHTMP'], 5, 2)] . " " . substr($dt['JTHTMP'], 0, 4) . "</td>
				</tr>
				<tr>
					<td colspan=\"5\" style=\"border-left: 1px solid black;border-right: 1px solid black;\">Jumlah yang harus dibayar (termasuk denda) jika pembayaran<br>dilakukan pada bulan ke (setelah tanggal jatuh tempo)</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">I</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_1'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XIII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_13'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">II</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_2'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XIV</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_14'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">III</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_3'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XV</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_15'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">IV</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_4'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XVI</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_16'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">V</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_5'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XVII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_17'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">VI</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_6'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XVIII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_18'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">VII</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_7'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XIX</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_19'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">VIII</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_8'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XX</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_20'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">IX</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_9'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XXI</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_21'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">X</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_10'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XXII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_22'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-left: 1px solid black;\">XI</td>
					<td width=\"20%\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_11'] . "</td>
					<td width=\"10%\"></td>
					<td width=\"25%\">XXIII</td>
					<td width=\"20%\" align=\"right\" style=\"border-right: 1px solid black;\">" . $dt['BAYAR_PLUS_DENDA_23'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-bottom: 1px solid black;border-left: 1px solid black;\">XII</td>
					<td width=\"20%\" style=\"border-bottom: 1px solid black;\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_12'] . "</td>
					<td width=\"10%\" style=\"border-bottom: 1px solid black;\"></td>
					<td width=\"25%\" style=\"border-bottom: 1px solid black;\">XXIV</td>
					<td width=\"20%\" style=\"border-bottom: 1px solid black;border-right: 1px solid black;\" align=\"right\">" . $dt['BAYAR_PLUS_DENDA_24'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">Tanggal Pembayaran</td>
					<td width=\"30%\" colspan=\"2\">: " . substr($dt['TGL_BAYAR'], 8, 2) . " " . $bulan[substr($dt['TGL_BAYAR'], 5, 2)] . " " . substr($dt['TGL_BAYAR'], 0, 4) . "</td>
					<td width=\"25%\"> LT : " . $dt['LT'] . "</td>
					<td width=\"20%\" rowspan=\"3\" align=\"center\">Tanda Terima<br>dan<br>Cap Bank/Pos</td>
				</tr>
				<tr>
					<td width=\"25%\">Jumlah yang dibayar</td>
					<td width=\"30%\" colspan=\"2\" style=\"border:1px solid black;\"> " . $dt['TOT_TRAN_AMOUNT_TEXT'] . "</td>
					<td width=\"25%\"> LB : " . $dt['LB'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">(Rp)</td>
					<td width=\"30%\" colspan=\"2\"></td>
					<td width=\"25%\"></td>
				</tr>
			</table>
		</html>";
}

class SPPT_PDF extends TCPDF
{

	public function __construct($blok)
	{
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('Alfa System');
		$this->SetTitle('STTS ' . $blok);
		$this->SetSubject('STTS');
		$this->SetKeywords('Alfa System');
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$this->SetMargins(40, 5, 35);
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$this->SetFont('helvetica', '', 8);
		$this->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
	}
}

function formatSizeUnits($bytes)
{
	if ($bytes >= 1073741824) {
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	} elseif ($bytes >= 1048576) {
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	} elseif ($bytes >= 1024) {
		$bytes = number_format($bytes / 1024, 2) . ' kB';
	} elseif ($bytes > 1) {
		$bytes = $bytes . ' bytes';
	} elseif ($bytes == 1) {
		$bytes = $bytes . ' byte';
	} else {
		$bytes = '0 bytes';
	}
	return $bytes;
}

function getKelName($kd)
{
	global $DBLink;

	$query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kd . "';";
	$res   = mysqli_query($DBLink, $query);
	$row   = mysqli_fetch_array($res);
	return $row['CPC_TKL_KELURAHAN'];
}

function savePdf($data)
{
	global $kd_kel, $blok, $blok2, $kd_buku, $path, $jumlahNOP, $maxNopPerFile, $tahun, $totalFileSize, $arrFiles, $CPM_GROUP_ID;

	$fullBlok = date("YmdHis");

	// $fileName = "STTS-{$kd_kel}-{$blok}-{$blok2}-{$tahun}-BUKU{$kd_buku}-".date('Ymd');
	$fileName = date("Ymd") . "-" . $CPM_GROUP_ID;
	#$width = 180;

	#$height = 215;


	try {
		$pdf = new SPPT_PDF($fullBlok);

		$x = 0;
		$nomorFile = 1;
		$fullPath = "";
		do {
			$html = buildHTML($data[$x]);
			#$pdf->AddPage('P', array($height,$width));
			$pdf->AddPage('P', 'A4');
			$pdf->writeHTML($html, true, false, false, false, '');
			$x++;
			// var_dump($x);
			// var_dump($jumlahNOP);
			// echo "<br>";
			// exit;
			if ($x == ($jumlahNOP)) {
				/*ARD: jika akhir data*/
				$namaFile = ($nomorFile > 1) ? ($fileName . '-' . $nomorFile . '.pdf') : ($fileName . '.pdf');
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
				// var_dump($fullPath);
				$arrFiles[] = $fullPath;
			} elseif ($x % $maxNopPerFile == 0) {
				/*ARD: jika data sudah mencapai kelipatan 100 maka disimpan difile baru*/
				$namaFile = $fileName . '-' . $nomorFile . '.pdf';
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
				$nomorFile++;
				$pdf = new SPPT_PDF($fullBlok);
				$arrFiles[] = $fullPath;
			}

			$totalFileSize += filesize($fullPath);
		} while ($x < $jumlahNOP);
	} catch (Exception $e) {
		return false;
	}

	return true;
}

/* inisiasi parameter */
// if(isset($_POST['a'])){
// $a = @$_POST['a'];
$a = "aPBB";
$m = @$_POST['m'];
$kd_kel = @$_POST['kd_kel'];
$blok = @$_POST['blok'];
$blok2 = @$_POST['blok2'];
$kd_buku = @$_POST['kd_buku'];
$tahun = @$_POST['tahun'];
$uid = @$_POST['uid'];
$path = $sRootPath . "pdf-sppt/";

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

$C_HOST_PORT 	= $appConfig['GW_DBHOST'];
$C_USER 		= $appConfig['GW_DBUSER'];
$C_PWD 			= $appConfig['GW_DBPWD'];
$C_DB 			= $appConfig['GW_DBNAME'];

SCANPayment_ConnectToDB($DBLinkGW, $DBConnGW, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB);

$maxNopPerFile = $appConfig['MAX_NOP_PERFILE'];
// $maxNopPerFile = 2;
$totalFileSize = 0;
$arrFiles = array();
/*main process*/
$data = getData();
// echo "<pre>";
// print_r($data);
// echo "</pre>";
$jumlahNOP = count($data);
// var_dump($jumlahNOP);
// exit;
if ($jumlahNOP == 0) {
	echo json_encode(array("error" => "NOP Tidak ditemukan"));
	exit;
}
// else{
// 	echo json_encode(array("error"=>"Error not found"));		
// }
// exit;
if (savePdf($data)) {
	// echo "masuk";
	$size = formatSizeUnits($totalFileSize);
	$param = array(
		"CPM_SIZE = '{$size}'",
		"CPM_STATUS = '1'",
		"CPM_JUMLAH_NOP = '{$jumlahNOP}'",
		"CPM_FILES = '" . implode(";", $arrFiles) . "'",
	);

	$sets = implode(',', $param);
	$query = "UPDATE $appConfig[ADMIN_SW_DBNAME].cppmod_pbb_stts_download_collective SET {$sets} WHERE CPM_GROUP_ID ='{$CPM_GROUP_ID}'";
	$sql = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
	if ($sql) {
		// echo "Berhasil !!";
		echo json_encode(array("success" => true));
	}
	// var_dump($sql);

} else {
	echo json_encode(array("success" => false));
}
	
// }else{
// 	echo json_encode(array("error"=>"NOP Tidak ditemukan"));
// }
