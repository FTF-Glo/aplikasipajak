<?php
ini_set('memory_limit', '1024M');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pencetakan-dhkp-sppt', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL);
ini_set('display_errors', 0);
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

function getData()
{
	global $DBLink, $appConfig, $kd_kel, $tahun, $blok, $blok2, $kd_buku;

	$qBuku = "";
	if ($kd_buku != 0) {
		switch ($kd_buku) {
			case 1:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
				break;
			case 12:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
				break;
			case 123:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
				break;
			case 1234:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 12345:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 2:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
				break;
			case 23:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
				break;
			case 234:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 2345:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 3:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
				break;
			case 34:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 345:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 4:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 45:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 5:
				$qBuku .= " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
		}
	}

	$query = "SELECT 
		A.SPPT_TAHUN_PAJAK, 
		A.NOP,
		REPLACE(A.OP_ALAMAT,'\'',' ') AS OP_ALAMAT,
		A.OP_RT,
		A.OP_RW, 
		A.OP_KELURAHAN, 
		A.OP_KECAMATAN, 
		A.OP_KOTAKAB,
		REPLACE(A.WP_NAMA,'\'',' ') AS WP_NAMA,
		REPLACE(A.WP_ALAMAT,'\'',' ') AS WP_ALAMAT,
		A.WP_RT AS WP_RT, 
		A.WP_RW AS WP_RW, 
		A.WP_KELURAHAN AS WP_KELURAHAN, 
		A.WP_KECAMATAN AS WP_KECAMATAN, 
		A.WP_KOTAKAB AS WP_KOTAKAB, 
		A.WP_KODEPOS AS WP_KODEPOS,
		A.OP_LUAS_BUMI, 
		A.OP_LUAS_BANGUNAN, 
		A.OP_KELAS_BUMI, 
		A.OP_KELAS_BANGUNAN, 
		A.OP_NJOP_BUMI, 
		A.OP_NJOP_BANGUNAN, 
		A.OP_NJOP,
		A.OP_NJOPTKP,
		A.OP_NJKP,
		A.SPPT_TANGGAL_JATUH_TEMPO, 
		A.SPPT_PBB_HARUS_DIBAYAR, 
		A.SPPT_TANGGAL_TERBIT, 
		A.SPPT_PBB_PENGURANGAN, 
		A.SPPT_PBB_PERSEN_PENGURANGAN, 
		A.OP_TARIF, 
		A.SPPT_DOC_ID, 
		A.OP_TARIF,
		A.OP_LUAS_BUMI_BERSAMA, 
		A.OP_LUAS_BANGUNAN_BERSAMA, 
		A.OP_NJOP_BUMI_BERSAMA, 
		A.OP_NJOP_BANGUNAN_BERSAMA,                
		A.OP_KELAS_BUMI_BERSAMA, 
		A.OP_KELAS_BANGUNAN_BERSAMA,
		A.OP_NJOP_BUMI_BERSAMA, 
		A.OP_NJOP_BANGUNAN_BERSAMA,
		C.CPC_NM_SEKTOR, 
		C.CPC_KD_AKUN, 
		IF(B.CPC_TKL_KDSEKTOR='10','PEDESAAN','PERKOTAAN') AS SEKTOR
		FROM cppmod_pbb_sppt_current A 
		LEFT JOIN cppmod_tax_kelurahan B ON A.OP_KELURAHAN_KODE=B.CPC_TKL_ID 
		LEFT JOIN cppmod_pbb_jns_sektor C ON C.CPC_KD_SEKTOR=B.CPC_TKL_KDSEKTOR
		WHERE NOP >= '" . $kd_kel . $blok . "00000' AND NOP <= '" . $kd_kel . $blok2 . "99999' {$qBuku} ORDER BY NOP ASC";
	//echo $query;
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	$i     = 0;
	while ($row  = mysqli_fetch_assoc($res)) {

		$row['OP_NJOP_BUMI_M2'] = @($row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI']);
		$row['OP_NJOP_BANGUNAN_M2'] = @($row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN']);
		$row['OP_NJOP_BUMI_M2_BERSAMA'] = @($row['OP_NJOP_BUMI_BERSAMA'] / $row['OP_LUAS_BUMI_BERSAMA']);
		$row['OP_NJOP_BANGUNAN_M2_BERSAMA'] = @($row['OP_NJOP_BANGUNAN_BERSAMA'] / $row['OP_LUAS_BANGUNAN_BERSAMA']);

		$row['NOP'] = substr($row['NOP'], 0, 2) . '.' . substr($row['NOP'], 2, 2) . '.' . substr($row['NOP'], 4, 3) . '.' . substr($row['NOP'], 7, 3) . '.' . substr($row['NOP'], 10, 3) . '-' . substr($row['NOP'], 13, 4) . '.' . substr($row['NOP'], 17, 1);
		$OP_LUAS_BUMI = (strrchr($row['OP_LUAS_BUMI'], '.') != '') ? number_format($row['OP_LUAS_BUMI'], 2, ',', '.') : number_format($row['OP_LUAS_BUMI'], 0, ',', '.');
		$row["OP_LUAS_BUMI"] = str_pad($OP_LUAS_BUMI, 10, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BUMI_M2"] = str_pad(number_format($row['OP_NJOP_BUMI_M2'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BUMI"] = str_pad(number_format($row['OP_NJOP_BUMI'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

		$row["TITLE_BANGUNAN_BERSAMA"] = 'BANGUNAN BERSAMA';
		$row["OP_LUAS_BANGUNAN_BERSAMA"] = str_pad(number_format($row['OP_LUAS_BANGUNAN_BERSAMA'], 0, '', '.'), 6, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BANGUNAN_M2_BERSAMA"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2_BERSAMA'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BANGUNAN_BERSAMA"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_BERSAMA'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

		$row["TITLE_BUMI_BERSAMA"] = 'BUMI BERSAMA';
		$row["OP_LUAS_BUMI_BERSAMA"] = str_pad(number_format($row['OP_LUAS_BUMI_BERSAMA'], 0, '', '.'), 10, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BUMI_M2_BERSAMA"] = str_pad(number_format($row['OP_NJOP_BUMI_M2_BERSAMA'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BUMI_BERSAMA"] = str_pad(number_format($row['OP_NJOP_BUMI_BERSAMA'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

		if (!($row['OP_LUAS_BUMI_BERSAMA'] != 0 && $row['OP_LUAS_BANGUNAN_BERSAMA'] != 0)) {
			$row["TITLE_BANGUNAN_BERSAMA"] = ' ';
			$row["OP_LUAS_BANGUNAN_BERSAMA"] = ' ';
			$row["OP_KELAS_BANGUNAN_BERSAMA"] = ' ';
			$row["OP_NJOP_BANGUNAN_M2_BERSAMA"] = ' ';
			$row["OP_NJOP_BANGUNAN_BERSAMA"] = ' ';

			$row["TITLE_BUMI_BERSAMA"] = ' ';
			$row["OP_LUAS_BUMI_BERSAMA"] = ' ';
			$row["OP_KELAS_BUMI_BERSAMA"] = ' ';
			$row["OP_NJOP_BUMI_M2_BERSAMA"] = ' ';
			$row["OP_NJOP_BUMI_BERSAMA"] = ' ';
		}

		$row["TITLE_BANGUNAN"] = 'BANGUNAN';
		$OP_LUAS_BANGUNAN = (strrchr($row['OP_LUAS_BANGUNAN'], '.') != '') ? number_format($row['OP_LUAS_BANGUNAN'], 2, ',', '.') : number_format($row['OP_LUAS_BANGUNAN'], 0, ',', '.');
		$row["OP_LUAS_BANGUNAN"] = str_pad($OP_LUAS_BANGUNAN, 10, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJKP_TANPA_PADDING"] = number_format($row['OP_NJKP'], 0, '', '.');
		$SPPT_PBB_SEBELUM_PENGURANGAN = ($row['OP_TARIF'] / 100) * $row['OP_NJKP'];
		$row["OP_NJKP"] = str_pad(number_format($row['OP_NJKP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		//$row["OP_NJKP_TANPA_PADDING"] = @number_format($row['OP_NJKP'],0,'','.');
		$row["OP_TARIF"] = rtrim($row['OP_TARIF'], "0");
		$row["SPPT_PBB_PERSEN_PENGURANGAN"] = ' ';
		$row["SPPT_PBB_PENGURANGAN"] = ' ';
		$row["TITLE_PENGURANGAN1"] = ' ';
		$row["TITLE_PENGURANGAN2"] = ' ';


		if ($row['SPPT_PBB_PENGURANGAN'] > 0) {
			$SPPT_PBB_SEBELUM_PENGURANGAN = $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'];
			$row["SPPT_PBB_PERSEN_PENGURANGAN"] = $row['SPPT_PBB_PERSEN_PENGURANGAN'];
			$row["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
			$row["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
			$row["TITLE_PENGURANGAN2"] = '= ' . number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'], 0, '', '') . ' % x ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'], 0, '', '.');
		}

		$row["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN, 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');
		$row["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthLong((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
		$row["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthLong((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
		$row["TERBILANG"] = strtoupper(SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR']));
		$row["SPPT_PBB_HARUS_DIBAYAR"] = str_pad(number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

		$row["TEMPAT_PEMBAYARAN"] = $appConfig['TEMPAT_PEMBAYARAN'];
		$row["NAMA_KOTA"] = $appConfig['NAMA_KOTA_PENGESAHAN'];
		$row["NAMA_PEJABAT_SK2"] = $appConfig['NAMA_PEJABAT_SK2'];
		$row["NAMA_PEJABAT_SK2_NIP"] = $appConfig['NAMA_PEJABAT_SK2_NIP'];
		$row["NAMA_PEJABAT_SK2_JABATAN"] = $appConfig['NAMA_PEJABAT_SK2_JABATAN'];
		$row["SEKTOR"] = str_pad($row['CPC_NM_SEKTOR'], 10, " ", STR_PAD_LEFT);

		$data[$i] = $row;
		$i++;
	}
	return $data;
}

function buildHTML($dt)
{
	global $appConfig, $tahun;
	$tahunCetak    = $appConfig['tahun_tagihan'];
	return $html = "
		<html>
			<table cellpadding=\"1\">
				
				<tr>
					<td align=\"left\" colspan=\"4\" width=\"50%\">
						<B><font size=\"8\">PEMERINTAH KABUPATEN PANDEGLANG<br>BADAN PELAYANAN PAJAK DAERAH</font></B>
					</td>
					<td align=\"center\" colspan=\"3\" width=\"50%\">
						<B><font size=\"8\">SPPT PBB<br>BUKAN MERUPAKAN BUKTI KEPEMILIKAN HAK</font></B>
					</td>
				</tr>

				<tr>
					<td colspan=\"7\"></td>
				</tr>
				<tr>
					<td colspan=\"7\" align=\"center\"><font size=\"+1\"><b>SURAT PEMBERITAHUAN PAJAK TERHUTANG<br>PAJAK BUMI DAN BANGUNAN TAHUN " . $tahunCetak . "</b></font></td>
				</tr>
				<tr>
					<td colspan=\"7\"><b>NOP :</b> " . $dt['NOP'] . "</td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"4\" width=\"50%\" style=\"border-top:1px solid black;border-right:1px solid black;\">
						<B>LETAK OBJEK PAJAK</B>
					</td>
					<td align=\"center\" colspan=\"3\" width=\"50%\" style=\"border-top:1px solid black;\">
						<B>NAMA DAN ALAMAT WAJIB PAJAK</B>
					</td>
				</tr>
				<tr>
					<td align=\"left\" colspan=\"4\" width=\"50%\" style=\"border-right:1px solid black;\">
						" . ($dt['OP_ALAMAT'] != "" ? $dt['OP_ALAMAT'] : '-') . "<br>
						RT " . ($dt['OP_RT'] != "" ? $dt['OP_RT'] : '-') . " RW " . ($dt['OP_RW'] != "" ? $dt['OP_RW'] : '-') . "<br>
						" . ($dt['OP_KELURAHAN'] != "" ? $dt['OP_KELURAHAN'] : '-') . "<br>
						" . ($dt['OP_KECAMATAN'] != "" ? $dt['OP_KECAMATAN'] : '-') . "<br>
						" . $appConfig['C_KABKOT'] . " " . (isset($dt['OP_KOTA']) ? $dt['OP_KOTAKAB'] : '') . "
					</td>
					<td align=\"left\" colspan=\"3\" width=\"50%\">
						&nbsp;&nbsp;" . ($dt['WP_NAMA'] != "" ? $dt['WP_NAMA'] : '-') . "<br>
						&nbsp;&nbsp;" . ($dt['WP_ALAMAT'] != "" ? $dt['WP_ALAMAT'] : '-') . "<br>
						&nbsp;&nbsp;RT " . ($dt['WP_RT'] != "" ? $dt['WP_RT'] : '-') . " RW " . ($dt['WP_RW'] != "" ? $dt['WP_RW'] : '-') . "<br>
						&nbsp;&nbsp;" . ($dt['WP_KELURAHAN'] != "" ? $dt['WP_KELURAHAN'] : '-') . "<br>
						&nbsp;&nbsp;" . ($dt['WP_KECAMATAN'] != "" ? $dt['WP_KECAMATAN'] : '-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . ($dt['WP_KOTAKAB'] != "" ? $dt['WP_KOTAKAB'] : '-') . " <br>
						&nbsp;&nbsp;NPWP : - 
					</td>
				</tr>
				<tr>
					<td width=\"25%\" style=\"border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;\" align=\"center\"><b>OBJEK PAJAK</b></td>
					<td width=\"15%\" style=\"border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;\" align=\"center\" colspan=\"2\"><b>LUAS(m<sup>2</sup>)</b></td>
					<td width=\"10%\" style=\"border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;\" align=\"center\"><b>KELAS</b></td>
					<td width=\"25%\" style=\"border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;\" align=\"center\" colspan=\"2\"><b>NJOP PER m<sup>2</sup>(Rp)</b></td>
					<td width=\"25%\" style=\"border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;\" align=\"center\"><b>TOTAL NJOP (Rp)</b></td>
				</tr>
				<tr>
					<td style=\"border-right: 1px solid black;\" align=\"left\">&nbsp;BUMI</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"center\" colspan=\"2\">" . $dt['OP_LUAS_BUMI'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"center\">" . $dt['OP_KELAS_BUMI'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"center\" colspan=\"2\">" . $dt['OP_NJOP_BUMI_M2'] . "</td>
					<td style=\"border-left: 1px solid black;\" align=\"right\">" . $dt['OP_NJOP_BUMI'] . "</td>
				</tr>
				<tr>
					<td style=\"border-right: 1px solid black;\">&nbsp;BANGUNAN</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"center\" colspan=\"2\">" . $dt['OP_LUAS_BANGUNAN'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"center\" >" . $dt['OP_KELAS_BANGUNAN'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"center\" colspan=\"2\">" . $dt['OP_NJOP_BANGUNAN_M2'] . "</td>
					<td style=\"border-left: 1px solid black;\" align=\"right\" >" . $dt['OP_NJOP_BANGUNAN'] . "</td>
				</tr>
				<tr>
					<td style=\"border-right: 1px solid black;\">" . $dt['TITLE_BUMI_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" colspan=\"2\">" . $dt['OP_LUAS_BUMI_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\">" . $dt['OP_KELAS_BUMI_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" colspan=\"2\">" . $dt['OP_NJOP_BUMI_M2_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;\" align=\"right\">" . $dt['OP_NJOP_BUMI_BERSAMA'] . "</td>
				</tr>
				<tr>
					<td style=\"border-right: 1px solid black;border-bottom: 1px solid black;\">" . $dt['TITLE_BANGUNAN_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\" align=\"center\" colspan=\"2\">" . $dt['OP_LUAS_BANGUNAN_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\" align=\"center\">" . $dt['OP_KELAS_BANGUNAN_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\" align=\"center\" colspan=\"2\">" . $dt['OP_NJOP_BANGUNAN_M2_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-bottom: 1px solid black;\" align=\"right\">" . $dt['OP_NJOP_BANGUNAN_BERSAMA'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\"><b>NJOP sebagai dasar pengenaan PBB</b></td>
					<td width=\"10%\">:</td>
					<td colspan=\"3\" align=\"right\">" . $dt['OP_NJOP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\"><b>NJOPTKP (NJOP Tidak Kena Pajak)</b></td>
					<td width=\"10%\">:</td>
					<td colspan=\"3\" align=\"right\">" . $dt['OP_NJOPTKP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\"><b>NJKP (Nilai Jual Kena Pajak)</b></td>
					<td width=\"10%\">:</td>
					<td colspan=\"3\" align=\"right\">" . $dt['OP_NJKP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\"><b>PBB yang terhutang</b></td>
					<td width=\"10%\">:</td>
					<td colspan=\"2\"align=\"right\">" . ($dt['OP_TARIF']) . "% x " . $dt['OP_NJKP_TANPA_PADDING'] . "</td>
					<td align=\"right\">" . $dt['SPPT_PBB_SEBELUM_PENGURANGAN'] . "</td>
				</tr>
				<tr>
					<td style=\"border-bottom:1px solid black;\" colspan=\"3\"><b>" . $dt['TITLE_PENGURANGAN1'] . "</b></td>
					<td style=\"border-bottom:1px solid black;\" width=\"10%\">" . $dt['TITLE_PENGURANGAN2'] . "</td>
					<td style=\"border-bottom:1px solid black;\" colspan=\"3\" align=\"right\">" . $dt['SPPT_PBB_PENGURANGAN'] . "</td>
				</tr>
				<tr>
					<td colspan=\"6\"><b>PAJAK BUMI DAN BANGUNAN YANG HARUS DIBAYAR (Rp)</b></td>
					<td align=\"right\">" . $dt['SPPT_PBB_HARUS_DIBAYAR'] . "</td>
				</tr>
				<tr>
					<td style=\"border-bottom:1px solid black;height:30px\" colspan=\"7\"><font size=\"-1\">" . $dt['TERBILANG'] . " RUPIAH</font></td>
				</tr>
				<tr>
					<td width=\"25%\"><b>TGL. JATUH TEMPO</b></td>
					<td colspan=\"3\">:&nbsp;&nbsp;&nbsp;&nbsp;" . $dt['SPPT_TANGGAL_JATUH_TEMPO'] . "</td>
					<td colspan=\"3\" align=\"center\">" . $appConfig['NAMA_KOTA_PENGESAHAN'] . ", " . $dt['SPPT_TANGGAL_TERBIT'] . "</td>
				</tr>
				<tr>
					<td width=\"25%\"><b>TEMPAT PEMBAYARAN</b></td>
					<td colspan=\"3\">:&nbsp;&nbsp;&nbsp;&nbsp;" . $dt['TEMPAT_PEMBAYARAN'] . "</td>
					<td colspan=\"3\" align=\"center\"><b>KEPALA BADAN PELAYANAN PAJAK DAERAH<br>" . $appConfig['C_KABKOT'] . " " . ($appConfig['NAMA_KOTA'] != "" ? $appConfig['NAMA_KOTA'] : '-') . "</b></td>
				</tr>
				<tr>
					<td colspan=\"7\"></td>
				</tr>
				<tr>
					<td colspan=\"7\"></td>
				</tr>
				<tr>
					<td colspan=\"2\"></td>
					<td colspan=\"2\"></td>
					<td colspan=\"3\" align=\"center\">" . $dt['NAMA_PEJABAT_SK2'] . "<br>NIP. " . $dt['NAMA_PEJABAT_SK2_NIP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"7\"></td>
				</tr>
				<tr>
					<td colspan=\"7\"></td>
				</tr>
				<tr>
					<td colspan=\"2\"><b>NAMA WAJIB PAJAK</b></td>
					<td colspan=\"3\">: " . ($dt['WP_NAMA'] != "" ? substr($dt['WP_NAMA'], 0, 20) : '-') . "</td>
					<td colspan=\"2\" align=\"left\">Diterima tanggal :</td>
				</tr>
				<tr>
					<td colspan=\"2\"><b>Letak Objek Pajak</b></td>
					<td colspan=\"3\">: <b>Kec.</b> " . ($dt['OP_KECAMATAN'] != "" ? $dt['OP_KECAMATAN'] : '-') . "</td>
					<td colspan=\"2\" align=\"left\">Tanda Tangan :</td>
				</tr>
				<tr>
					<td colspan=\"2\"></td>
					<td colspan=\"3\">: <b>" . substr($appConfig['LABEL_KELURAHAN'], 0, 3) . ".</b> " . ($dt['OP_KELURAHAN'] != "" ? $dt['OP_KELURAHAN'] : '-') . "</td>
					<td colspan=\"2\" align=\"center\"></td>
				</tr>
				<tr>
					<td colspan=\"2\"><b>NOP</b></td>
					<td colspan=\"3\">: " . ($dt['NOP'] != "" ? $dt['NOP'] : '-') . "</td>
					<td colspan=\"2\" align=\"center\">(............................................................)</td>
				</tr>
				<tr>
					<td colspan=\"2\"><b>SPPT TAHUN / Rp</b></td>
					<td colspan=\"3\">: " . ($tahunCetak != "" ? $tahunCetak : '-') . " / Rp " . $dt['SPPT_PBB_HARUS_DIBAYAR'] . "</td>
					<td colspan=\"2\" align=\"center\">Nama Terang</td>
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
		$this->SetTitle('SPPT Blok ' . $blok);
		$this->SetSubject('SPPT');
		$this->SetKeywords('Alfa System');
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		//$this->Image($sRootPath.'image/stempel-ttd.png', 20, 12, 35, '', '', '', '', false, 300, '', false);
		$this->SetMargins(17, 10, 18);
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$this->SetFont('helvetica', '', 9);
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

function savePdf($data)
{
	global $kd_kel, $blok, $kd_buku, $path, $jumlahNOP, $maxNopPerFile, $tahun, $totalFileSize, $arrFiles, $sRootPath;

	$fullBlok = $kd_kel . $blok;

	$fileName = "blok-{$fullBlok}-{$tahun}-BUKU{$kd_buku}-" . date('Ymd');
	#$width = 180;
	#$height = 215;


	try {
		$pdf = new SPPT_PDF($fullBlok);

		$x = 0;
		$nomorFile = 1;
		do {
			$html = buildHTML($data[$x]);
			#$pdf->AddPage('P', array($height,$width));

			$pdf->AddPage('P', 'A4');
			$pdf->writeHTML($html, true, false, false, false, '');
			if (($kd_buku != 45) && ($kd_buku != 4) && ($kd_buku != 5)) {
				$pdf->Image($sRootPath . 'image/stempel-ttd.png', 120, 125, 55, '', '', '', '', false, 300);
			}
			$x++;

			if ($x == ($jumlahNOP)) {
				/*ARD: jika akhir data*/
				$namaFile = ($nomorFile > 1) ? ($fileName . '-' . $nomorFile . '.pdf') : ($fileName . '.pdf');
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
				$arrFiles[] = $fullPath;
				$totalFileSize += filesize($fullPath);
			} elseif ($x % $maxNopPerFile == 0) {
				/*ARD: jika data sudah mencapai kelipatan 100 maka disimpan difile baru*/
				$namaFile = $fileName . '-' . $nomorFile . '.pdf';
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
				$nomorFile++;
				$pdf = new SPPT_PDF($fullBlok);
				$arrFiles[] = $fullPath;
				$totalFileSize += filesize($fullPath);
			}
		} while ($x < $jumlahNOP);
	} catch (Exception $e) {
		return false;
	}

	return true;
}

/* inisiasi parameter */
if (isset($_POST['a'])) {
	$a = @$_POST['a'];
	$m = @$_POST['m'];
	$kd_kel 	= @$_POST['kd_kel'];
	$blok 		= @$_POST['blok'];
	$blok2 		= @$_POST['blok2'];
	$tahun 		= @$_POST['tahun'];
	$kd_buku 	= @$_POST['kd_buku'];
	$uid 		= @$_POST['uid'];
	$path 		= $sRootPath . "pdf-sppt/";

	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$arConfig 	= $User->GetModuleConfig($m);
	$appConfig 	= $User->GetAppConfig($a);

	$maxNopPerFile = $appConfig['MAX_NOP_PERFILE'];
	$tahunCetak    = $appConfig['tahun_tagihan'];
	$totalFileSize = 0;
	$arrFiles = array();
	/*main process*/
	$data = getData();
	$jumlahNOP = count($data);

	$param = array(
		"CPM_SIZE = '0'",
		"CPM_STATUS = '1'",
		"CPM_JUMLAH_NOP = '0'",
		"CPM_FILES = ''",
	);
	if ($jumlahNOP > 0) {
		if (savePdf($data)) {
			$size = formatSizeUnits($totalFileSize);
			$param = array(
				"CPM_SIZE = '{$size}'",
				"CPM_STATUS = '1'",
				"CPM_JUMLAH_NOP = '{$jumlahNOP}'",
				"CPM_FILES = '" . implode(";", $arrFiles) . "'",
			);
		}
	}

	$sets = implode(',', $param);
	$query = "UPDATE cppmod_pbb_sppt_download SET {$sets} WHERE CPM_ID ='{$uid}'";
	$sql = mysqli_query($DBLink, $query);
	exit;
}
