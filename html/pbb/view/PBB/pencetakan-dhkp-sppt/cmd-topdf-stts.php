<?php
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
ini_set('display_errors', 1);
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
	global $DBLink, $appConfig, $kd_kel, $tahun, $blok;

	$query = sprintf("SELECT 
		A.SPPT_TAHUN_PAJAK, 
		A.NOP,
		A.OP_ALAMAT,
		A.OP_RT,
		A.OP_RW, 
		A.OP_KELURAHAN, 
		A.OP_KECAMATAN, 
		A.OP_KOTAKAB,
		A.WP_NAMA AS WP_NAMA, 
		A.WP_ALAMAT AS WP_ALAMAT, 
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
		WHERE SUBSTR(A.NOP,1,13)='%s' ORDER BY NOP ASC", $kd_kel . $blok);

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

		$row["TITLE_BANGUNAN"] = 'BANGUNAN';
		$OP_LUAS_BANGUNAN = (strrchr($row['OP_LUAS_BANGUNAN'], '.') != '') ? number_format($row['OP_LUAS_BANGUNAN'], 2, ',', '.') : number_format($row['OP_LUAS_BANGUNAN'], 0, ',', '.');
		$row["OP_LUAS_BANGUNAN"] = str_pad($OP_LUAS_BANGUNAN, 10, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$row["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJKP"] = str_pad(number_format($row['OP_NJKP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["OP_NJKP_TANPA_PADDING"] = @number_format($row['OP_NJKP'], 0, '', '.');
		$row["OP_TARIF"] = rtrim($row['OP_TARIF'], "0");
		$row["SPPT_PBB_PERSEN_PENGURANGAN"] = ' ';
		$row["SPPT_PBB_PENGURANGAN"] = ' ';
		$row["TITLE_PENGURANGAN1"] = ' ';
		$row["TITLE_PENGURANGAN2"] = ' ';

		$SPPT_PBB_SEBELUM_PENGURANGAN = ($row['OP_TARIF'] / 100) * $row['OP_NJKP'];
		if ($row['SPPT_PBB_PENGURANGAN'] > 0) {
			$SPPT_PBB_SEBELUM_PENGURANGAN = $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'];
			$row["SPPT_PBB_PERSEN_PENGURANGAN"] = $row['SPPT_PBB_PERSEN_PENGURANGAN'];
			$row["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
			$row["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
			$row["TITLE_PENGURANGAN2"] = '= ' . number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'], 0, '', '') . ' % x ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'], 0, '', '.');
		}

		$row["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN, 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$row["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');
		$row["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
		$row["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
		$row["TERBILANG"] = strtoupper(SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR'])) . ' RUPIAH';
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
	return $html = "
		<html>
			<table cellpadding=\"1\" border=\"0\" width=\"600px\">
				<tr>
					<td align=\"left\" colspan=\"4\" width=\"50%\">
						<B><font size=\"8\">PEMERINTAH KABUPATEN PANDEGLANG<br>DINAS PENDAPATAN DAERAH</font></B>
					</td>
					<td align=\"center\" colspan=\"3\" width=\"50%\">
						<B><font size=\"8\">SPPT PBB<br>BUKAN MERUPAKAN BUKTI KEPEMILIKAN HAK</font></B>
					</td>
				</tr>
				<tr>
					<td colspan=\"7\"></td>
				</tr>
				<tr>
					<td colspan=\"7\" align=\"center\"><b>SURAT PEMBERI TAHUAN PAJAK TERHUTANG<br>PAJAK BUMI DAN BANGUNAN TAHUN " . $tahun . "</b></td>
				</tr>
				<tr>
					<td colspan=\"7\">NOP : " . $dt['NOP'] . "</td>
				</tr>
				<tr>
					<td align=\"center\" colspan=\"4\" width=\"50%\" style=\"border-top:1px solid black;border-right:1px solid black;\">
						<B>LETAK OBJEK PAJAK</B>
					</td>
					<td align=\"center\" colspan=\"3\" width=\"50%\" style=\"border-top:1px solid black;\">
						<B>NAMA DAN ALAMAT OBJEK PAJAK</B>
					</td>
				</tr>
				<tr>
					<td align=\"left\" colspan=\"4\" width=\"50%\" style=\"border-right:1px solid black;\">
						" . ($dt['OP_ALAMAT'] != "" ? $dt['OP_ALAMAT'] : '-') . "<br>
						RT " . ($dt['OP_RT'] != "" ? $dt['OP_RT'] : '-') . " RW " . ($dt['OP_RW'] != "" ? $dt['OP_RW'] : '-') . "<br>
						" . ($dt['OP_KELURAHAN'] != "" ? $dt['OP_KELURAHAN'] : '-') . "<br>
						" . ($dt['OP_KECAMATAN'] != "" ? $dt['OP_KECAMATAN'] : '-') . "<br>
						" . $appConfig['C_KABKOT'] . " " . ($dt['OP_KOTAKAB'] != "" ? $dt['OP_KOTAKAB'] : '-') . "
					</td>
					<td align=\"left\" colspan=\"3\" width=\"50%\">
						&nbsp;&nbsp;" . ($dt['WP_NAMA'] != "" ? $dt['WP_NAMA'] : '-') . "<br>
						&nbsp;&nbsp;" . ($dt['WP_ALAMAT'] != "" ? $dt['WP_ALAMAT'] : '-') . "<br>
						&nbsp;&nbsp;RT " . ($dt['WP_RT'] != "" ? $dt['WP_RT'] : '-') . " RW " . ($dt['WP_RW'] != "" ? $dt['WP_RW'] : '-') . "<br>
						&nbsp;&nbsp;" . ($dt['OP_KECAMATAN'] != "" ? $dt['OP_KECAMATAN'] : '-') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . ($dt['OP_KOTAKAB'] != "" ? $dt['OP_KOTAKAB'] : '-') . " <br>
						&nbsp;&nbsp;NPWP : - 
					</td>
				</tr>
				<tr>
					<td width=\"20%\" style=\"border: 1px solid black;\" align=\"center\">OBJEK PAJAK</td>
					<td width=\"20%\" style=\"border: 1px solid black;\" align=\"center\" colspan=\"2\">LUAS(m<sup>2</sup>)</td>
					<td width=\"10%\" style=\"border: 1px solid black;\" align=\"center\">KELAS</td>
					<td width=\"25%\" style=\"border: 1px solid black;\" align=\"center\" colspan=\"2\">NJOP PER m<sup>2</sup>(Rp)</td>
					<td width=\"25%\" style=\"border: 1px solid black;\" align=\"center\">TOTAL NJOP (Rp)</td>
				</tr>
				<tr>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"left\">BUMI</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" colspan=\"2\">" . $dt['OP_LUAS_BUMI'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\">" . $dt['OP_KELAS_BUMI'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" colspan=\"2\">" . $dt['OP_NJOP_BUMI_M2'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\">" . $dt['OP_NJOP_BUMI'] . "</td>
				</tr>
				<tr>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\">BANGUNAN</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" colspan=\"2\">" . $dt['OP_LUAS_BANGUNAN'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" >" . $dt['OP_KELAS_BANGUNAN'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" colspan=\"2\">" . $dt['OP_NJOP_BANGUNAN_M2'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" align=\"right\" >" . $dt['OP_NJOP_BANGUNAN'] . "</td>
				</tr>
				<tr>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\">" . $dt['TITLE_BUMI_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" colspan=\"2\">" . $dt['OP_LUAS_BUMI_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\">" . $dt['OP_KELAS_BUMI_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\" colspan=\"2\">" . $dt['OP_NJOP_BUMI_M2_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;\">" . $dt['OP_NJOP_BUMI_BERSAMA'] . "</td>
				</tr>
				<tr>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\">" . $dt['TITLE_BANGUNAN_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\" colspan=\"2\">" . $dt['OP_LUAS_BANGUNAN_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\">" . $dt['OP_KELAS_BANGUNAN_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\" colspan=\"2\">" . $dt['OP_NJOP_BANGUNAN_M2_BERSAMA'] . "</td>
					<td style=\"border-left: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;\">" . $dt['OP_NJOP_BANGUNAN_BERSAMA'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\">NJOP sebagai dasar pengenaan PBB</td>
					<td width=\"10%\">:</td>
					<td colspan=\"3\" align=\"right\">" . $dt['OP_NJOP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\">NJOPTKP (NJOP Tidak Kena Pajak)</td>
					<td width=\"10%\">:</td>
					<td colspan=\"3\" align=\"right\">" . $dt['OP_NJOPTKP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\">NJKP (Nilai Jual Kena Pajak)</td>
					<td width=\"10%\">:</td>
					<td colspan=\"3\" align=\"right\">" . $dt['OP_NJKP'] . "</td>
				</tr>
				<tr>
					<td colspan=\"3\">PBB yang terhutang</td>
					<td width=\"10%\">:</td>
					<td colspan=\"2\"align=\"right\">" . $dt['OP_TARIF'] . " x " . $dt['OP_NJKP_TANPA_PADDING'] . "</td>
					<td align=\"right\">" . $dt['SPPT_PBB_SEBELUM_PENGURANGAN'] . "</td>
				</tr>
				<tr>
					<td style=\"border-bottom:1px solid black;\" colspan=\"3\">" . $dt['TITLE_PENGURANGAN1'] . "</td>
					<td style=\"border-bottom:1px solid black;\" width=\"10%\">" . $dt['TITLE_PENGURANGAN2'] . "</td>
					<td style=\"border-bottom:1px solid black;\" colspan=\"3\" align=\"right\">" . $dt['SPPT_PBB_PENGURANGAN'] . "</td>
				</tr>
				<tr>
					<td colspan=\"6\">PAJAK BUMI DAN BANGUNAN YANG HARUS DIBAYAR (Rp)</td>
					<td align=\"right\">" . $dt['SPPT_PBB_HARUS_DIBAYAR'] . "</td>
				</tr>
				<tr>
					<td style=\"border-bottom:1px solid black;\" colspan=\"7\">" . $dt['TERBILANG'] . "</td>
				</tr>
				<tr>
					<td colspan=\"2\">TGL. JATUH TEMPO</td>
					<td colspan=\"2\">:&nbsp;&nbsp;&nbsp;&nbsp;" . $dt['SPPT_TANGGAL_JATUH_TEMPO'] . "</td>
					<td colspan=\"3\" align=\"center\">" . $appConfig['NAMA_KOTA_PENGESAHAN'] . ", " . $dt['SPPT_TANGGAL_TERBIT'] . "</td>
				</tr>
				<tr>
					<td colspan=\"2\">TEMPAT PEMBAYARAN</td>
					<td colspan=\"2\">:&nbsp;&nbsp;&nbsp;&nbsp;" . $dt['TEMPAT_PEMBAYARAN'] . "</td>
					<td colspan=\"3\" align=\"center\">KEPALA DINAS PENDAPATAN DAERAH<br>" . $appConfig['C_KABKOT'] . " " . ($dt['OP_KOTAKAB'] != "" ? $dt['OP_KOTAKAB'] : '-') . "</td>
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
					<td colspan=\"2\">NAMA WAJIB PAJAK</td>
					<td colspan=\"3\">: " . ($dt['WP_NAMA'] != "" ? substr($dt['WP_NAMA'], 0, 20) : '-') . "</td>
					<td colspan=\"2\" align=\"left\">Diterima tanggal :</td>
				</tr>
				<tr>
					<td colspan=\"2\">Letak Objek Pajak</td>
					<td colspan=\"3\">: Kec. " . ($dt['OP_KECAMATAN'] != "" ? $dt['OP_KECAMATAN'] : '-') . "</td>
					<td colspan=\"2\" align=\"left\">Tanda Tangan :</td>
				</tr>
				<tr>
					<td colspan=\"2\"></td>
					<td colspan=\"3\">: " . substr($appConfig['LABEL_KELURAHAN'], 0, 3) . ". " . ($dt['OP_KELURAHAN'] != "" ? $dt['OP_KELURAHAN'] : '-') . "</td>
					<td colspan=\"2\" align=\"center\"></td>
				</tr>
				<tr>
					<td colspan=\"2\">NOP</td>
					<td colspan=\"3\">: " . ($dt['NOP'] != "" ? $dt['NOP'] : '-') . "</td>
					<td colspan=\"2\" align=\"center\">(............................................................)</td>
				</tr>
				<tr>
					<td colspan=\"2\">SPPT TAHUN / Rp</td>
					<td colspan=\"3\">: " . ($tahun != "" ? $tahun : '-') . " / Rp " . $dt['SPPT_PBB_HARUS_DIBAYAR'] . "</td>
					<td colspan=\"2\" align=\"center\">Nama Terang</td>
				</tr>
			</table>
		</html>";
}

function savePdf($data)
{
	global $blok, $path, $jumlahNOP;


	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Alfa System');
	$pdf->SetTitle('SPPT Blok ' . $blok);
	$pdf->SetSubject('SPPT');
	$pdf->SetKeywords('Alfa System');
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	$pdf->SetFooterMargin(0);
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(5, 10, 5);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->SetFont('helvetica', '', 10);
	$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

	$width = 180;
	$height = 215;

	try {

		$x = 0;
		$nomorFile = 1;
		do {
			$html = buildHTML($data[$x]);
			$pdf->AddPage('P', array($height, $width));
			$pdf->writeHTML($html, true, false, false, false, '');

			if ($x == ($jumlahNOP - 1)) {
				/*ARD: jika akhir data*/
				$namaFile = ($nomorFile > 1) ? 'STTS-' . $kd_kel . '-' . $nomorFile . '.pdf' : 'STTS-' . $kd_kel . '.pdf';
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
			} elseif ($x % 100 == 0) {
				/*ARD: jika data sudah mencapai kelipatan 100 maka disimpan difile baru*/
				$namaFile = 'blok-' . $blok . '-' . $nomorFile . '.pdf';
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
				$nomorFile++;
			}

			$x++;
		} while ($x < $jumlahNOP);
	} catch (Exception $e) {
		return false;
	}

	return true;
}

/* inisiasi parameter */
$params = $argv[1];
$q = @$params;
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$kd_kel = $q->kd_kel;
$blok = $q->blok;
$tahun = $q->tahun;
$path = $sRootPath . "pdf-sppt/";

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

/*main process*/
$data = getData();
$jumlahNOP = count($data);

if ($jumlahNOP == 0) exit('tidak ada data ditemukan');
else echo 'data ditemukan sebanyak ' . $jumlahNOP . ' NOP';
savePdf($data);
