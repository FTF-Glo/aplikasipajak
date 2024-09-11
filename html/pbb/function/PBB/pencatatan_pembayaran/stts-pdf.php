<?php

/*
Nama Developer : Jajang Apriansyah (jajang@vsi.co.id)
Tanggal 	   : 28 Nov 2016
*/

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB/pencatatan_pembayaran', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/report_stts/eng-report.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$sql     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'TEMPAT_PEMBAYARAN'";
$result  = mysqli_query($DBLink, $sql);
$row 	 = mysqli_fetch_array($result);
$tempat_bayar = $row['CTR_AC_VALUE'];

$sqlheader     = "SELECT * FROM `central_app_config` WHERE `CTR_AC_KEY` = 'C_HEADER_FORM_PENERIMAAN'";
$resultheader  = mysqli_query($DBLink, $sqlheader);
$rowheader 	 = mysqli_fetch_array($resultheader);
$teks_header = $rowheader['CTR_AC_VALUE'];

require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

$params = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p 		= base64_decode($params);
$json 	= new Services_JSON();
$prm 	= $json->decode($p);

$nop   	= $prm->nop;
$tahun 	= $prm->year;
$mode  	= $prm->mode;
$uname  = $prm->uname;
$tgl  	= $prm->tgl;

function buildHTML($printValue, $teks_header)
{
	$dt = $printValue;
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
						<b>" . $teks_header . "</b>
					</td>
					<td align=\"left\" colspan=\"2\" width=\"50%\">
					</td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"5\">
						<B>SURAT TANDA TERIMA SETORAN<br>PAJAK BUMI DAN BANGUNAN</B>
					</td>
				</tr>
				<br>
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
					<td width=\"30%\" colspan=\"2\" style=\"border:1px solid black;\">: " . $dt['TOT_TRAN_AMOUNT_TEXT'] . "</td>
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

			<!--
			<table cellpadding=\"2\" border=\"0\">
				<tr>
					<td align=\"left\" colspan=\"3\" width=\"50%\">
						<b>" . $teks_header . "</b>
					</td>
					<td align=\"left\" colspan=\"2\" width=\"50%\">
					</td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"5\">
						<B>SURAT TANDA TERIMA SETORAN<br>PAJAK BUMI DAN BANGUNAN</B>
					</td>
				</tr>
				<br>
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
					<td width=\"30%\" colspan=\"2\" style=\"border:1px solid black;\">: " . $dt['TOT_TRAN_AMOUNT_TEXT'] . "</td>
					<td width=\"25%\"> LB : " . $dt['LB'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\">(Rp)</td>
					<td width=\"30%\" colspan=\"2\"></td>
					<td width=\"25%\"></td>
				</tr>
			</table>
			//-->
		</html>
	";
}

if ($mode == 'cetak_ulang') {
	$sql = "SELECT A.NOP,
				A.SPPT_TAHUN_PAJAK, 
				A.WP_NAMA, 
				A.OP_KECAMATAN,
				A.OP_KELURAHAN,
				A.SPPT_TANGGAL_JATUH_TEMPO,
				A.OP_LUAS_BUMI,
				A.OP_LUAS_BANGUNAN,
				A.SPPT_PBB_HARUS_DIBAYAR,
				A.PBB_DENDA SPPT_DENDA,
            IFNULL(A.PAYMENT_PAID,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:%s')) AS PAYMENT_PAID				   
			FROM PBB_SPPT A
			WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
	// echo $sql; exit;
} else {
	$tmp = explode("-", $tgl);
	$tgl = $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
	$sql = "SELECT A.NOP,
			A.SPPT_TAHUN_PAJAK, 
			A.WP_NAMA, 
			A.OP_KECAMATAN,
			A.OP_KELURAHAN,
			A.SPPT_TANGGAL_JATUH_TEMPO,
			A.OP_LUAS_BUMI,
			A.OP_LUAS_BANGUNAN,
			A.SPPT_PBB_HARUS_DIBAYAR,
			@dendaBulan := CEIL(TIMESTAMPDIFF(DAY,A.SPPT_TANGGAL_JATUH_TEMPO,'" . $tgl . "')/30) dendaBulan,
			@dendaBulan := if(@dendaBulan < 0, 0, @dendaBulan) dendaBulanFix1,
			@dendaBulan := if(@dendaBulan > 24, 24, @dendaBulan) dendaBulanFix2,
			FLOOR((2/100)*@dendaBulan*A.SPPT_PBB_HARUS_DIBAYAR) SPPT_DENDA,
			CONCAT('" . $tgl . "',DATE_FORMAT(NOW(),' %H:%i:%s')) AS PAYMENT_PAID
			FROM   PBB_SPPT A
			WHERE  A.NOP = '$nop' AND A.SPPT_TAHUN_PAJAK = '$tahun' LIMIT 1";
	// echo $sql; exit;
}

$res = mysqli_query($DBLink2, $sql);
if ($row = mysqli_fetch_array($res)) {

	$nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
	$denda = $row['SPPT_DENDA'];
	$total = $nilai + $denda;

	$templatePrintValue['TEMPAT_BAYAR'] 		= $tempat_bayar;
	$templatePrintValue['THN_BAYAR'] 		= $row['SPPT_TAHUN_PAJAK'];
	$templatePrintValue['THN_DARI'] 		= date('Y');
	$templatePrintValue['SUBJECT_NAME'] 		= $row['WP_NAMA'];
	$templatePrintValue['OBJECT_KECAMATAN'] 	= $row['OP_KECAMATAN'];
	$templatePrintValue['OBJECT_KELURAHAN'] 	= $row['OP_KELURAHAN'];
	$templatePrintValue['NOPNPWP'] 			= substr($row['NOP'], 0, 2) . '.' . substr($row['NOP'], 2, 2) . '.' . substr($row['NOP'], 4, 3) . '.' . substr($row['NOP'], 7, 3) . '.' . substr($row['NOP'], 10, 3) . '-' . substr($row['NOP'], 13, 4) . '.' . substr($row['NOP'], 17, 1);
	$templatePrintValue['TRAN_AMOUNT_TEXT'] 	= number_format($nilai, 2, ',', '.');;
	$templatePrintValue['JTHTMP'] 			= $row['SPPT_TANGGAL_JATUH_TEMPO'];
	$templatePrintValue['TGL_BAYAR'] 		= $row['PAYMENT_PAID'];
	$templatePrintValue['LT'] 			= $row['OP_LUAS_BUMI'];
	$templatePrintValue['LB'] 			= $row['OP_LUAS_BANGUNAN'];
	$templatePrintValue['TOT_TRAN_AMOUNT_TEXT']     = number_format($total, 2, ',', '.');

	for ($i = 1; $i <= 24; $i++) {
		$totalBulan = $nilai + (((2 / 100) * $nilai) * $i);
		$templatePrintValue["BAYAR_PLUS_DENDA_$i"] = number_format($totalBulan, 2, ',', '.');
	}
}

// echo "<pre>";
// print_r($templatePrintValue); exit;

$strHTML = buildHTML($templatePrintValue, $teks_header);

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(3, 5, 75);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 8);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$pdf->AddPage('P', 'A4');
$pdf->writeHTML($strHTML, true, false, false, false, '');
$pdf->SetAlpha(0.3);
$pdf->Output($nop . '.pdf', 'I');
