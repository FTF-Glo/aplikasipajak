<?php
/* 
 *  Penghapusan Massal 
 */

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB/penghapusanmassal', '', dirname(__FILE__))) . '/';
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


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

function doDelete($id, &$arrValues, $tahunCetak)
{
	global $paymentDt, $host, $port, $timeOut, $DBLink;

	$paymentDt = strftime("%Y%m%d%H%M%S", time());

	$arrValues['result'] = true;
	$arrValues['message'] = 'Penghapusan Massal berhasil';
	$arrValues['printValue'] = deleteRequest($id, $tahunCetak);
	//$arrValues['HtmlValue'] = $strHTML;
	// echo $strHTML; exit();
	return true;
}

function getValuesForPrint(&$aTemplateValues, $row, $tahunCetak)
{
	global $appConfig;
	$aTemplateValues["NOP_CL"] = $row['NOP']; //clean NOP

	$row['NOP'] = substr($row['NOP'], 0, 2) . '.' . substr($row['NOP'], 2, 2) . '.' . substr($row['NOP'], 4, 3) . '.' . substr($row['NOP'], 7, 3) . '.' . substr($row['NOP'], 10, 3) . '-' . substr($row['NOP'], 13, 4) . '.' . substr($row['NOP'], 17, 1);

	$aTemplateValues["SPPT_TAHUN_PAJAK"] = $row['SPPT_TAHUN_PAJAK'];
	$aTemplateValues["NOP"] = $row['NOP'];
	$aTemplateValues["OP_ALAMAT"] = $row['OP_ALAMAT'];
	$aTemplateValues["OP_RT"] = $row['OP_RT'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_KELURAHAN"] = $row['OP_KELURAHAN'];
	$aTemplateValues["OP_KECAMATAN"] = $row['OP_KECAMATAN'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_KOTAKAB"] = $row['OP_KOTAKAB'];

	$aTemplateValues["WP_NAMA"] = $row['WP_NAMA'];
	$aTemplateValues["WP_ALAMAT"] = $row['WP_ALAMAT'];
	$aTemplateValues["WP_RT"] = $row['WP_RT'];
	$aTemplateValues["WP_RW"] = $row['WP_RW'];
	$aTemplateValues["WP_KELURAHAN"] = $row['WP_KELURAHAN'];
	$aTemplateValues["WP_KECAMATAN"] = $row['WP_KECAMATAN'];
	$aTemplateValues["WP_KOTAKAB"] = $row['WP_KOTAKAB'];
	$aTemplateValues["WP_KODEPOS"] = $row['WP_KODEPOS'];

	$OP_LUAS_TANAH_VIEW = '0';
	if (strrchr($row['OP_LUAS_BUMI'], '.') != '') {
		$OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 2, ',', '.');
	} else $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'], 0, ',', '.');
	$aTemplateValues["OP_LUAS_BUMI"] = str_pad($OP_LUAS_TANAH_VIEW, 10, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_KELAS_BUMI"] = $row['OP_KELAS_BUMI'];
	$aTemplateValues["OP_NJOP_BUMI_M2"] = str_pad(number_format($row['OP_NJOP_BUMI_M2'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOP_BUMI"] = str_pad(number_format($row['OP_NJOP_BUMI'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

	if ($row['OP_LUAS_BUMI_BERSAMA'] != null && $row['OP_LUAS_BANGUNAN_BERSAMA'] != null) {
		$aTemplateValues["TITLE_BANGUNAN_BER"] = 'BANGUNAN BERSAMA';
		$aTemplateValues["OP_LUAS_BANGUNAN_BER"] = str_pad(number_format($row['OP_LUAS_BANGUNAN_BERSAMA'], 0, '', '.'), 6, " ", STR_PAD_LEFT);
		$aTemplateValues["OP_KELAS_BANGUNAN_BER"] = $row['OP_KELAS_BANGUNAN_BERSAMA'];
		$aTemplateValues["OP_NJOP_BANGUNAN_M2_BER"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2_BERSAMA'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$aTemplateValues["OP_NJOP_BANGUNAN_BER"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_BERSAMA'], 0, '', '.'), 17, " ", STR_PAD_LEFT);

		$aTemplateValues["TITLE_BUMI_BER"] = 'BUMI BERSAMA';
		$aTemplateValues["OP_LUAS_BUMI_BER"] = str_pad(number_format($row['OP_LUAS_BUMI_BERSAMA'], 0, '', '.'), 10, " ", STR_PAD_LEFT);
		$aTemplateValues["OP_KELAS_BUMI_BER"] = $row['OP_KELAS_BUMI_BERSAMA'];
		$aTemplateValues["OP_NJOP_BUMI_M2_BER"] = str_pad(number_format($row['OP_NJOP_BUMI_M2_BERSAMA'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
		$aTemplateValues["OP_NJOP_BUMI_BER"] = str_pad(number_format($row['OP_NJOP_BUMI_BERSAMA'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
	} else {
		$aTemplateValues["TITLE_BANGUNAN_BER"] = ' ';
		$aTemplateValues["OP_LUAS_BANGUNAN_BER"] = ' ';
		$aTemplateValues["OP_KELAS_BANGUNAN_BER"] = ' ';
		$aTemplateValues["OP_NJOP_BANGUNAN_M2_BER"] = ' ';
		$aTemplateValues["OP_NJOP_BANGUNAN_BER"] = ' ';

		$aTemplateValues["TITLE_BUMI_BER"] = ' ';
		$aTemplateValues["OP_LUAS_BUMI_BER"] = ' ';
		$aTemplateValues["OP_KELAS_BUMI_BER"] = ' ';
		$aTemplateValues["OP_NJOP_BUMI_M2_BER"] = ' ';
		$aTemplateValues["OP_NJOP_BUMI_BER"] = ' ';
	}
	$aTemplateValues["TITLE_BANGUNAN"] = 'BANGUNAN';
	$OP_LUAS_BANGUNAN_VIEW = '0';
	if (strrchr($row['OP_LUAS_BANGUNAN'], '.') != '') {
		$OP_LUAS_BANGUNAN_VIEW = number_format($row['OP_LUAS_BANGUNAN'], 2, ',', '.');
	} else $OP_LUAS_BANGUNAN_VIEW = number_format($row['OP_LUAS_BANGUNAN'], 0, ',', '.');
	$aTemplateValues["OP_LUAS_BANGUNAN"] = str_pad($OP_LUAS_BANGUNAN_VIEW, 10, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_KELAS_BANGUNAN"] = $row['OP_KELAS_BANGUNAN'];
	$aTemplateValues["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'], 0, '', '.'), 11, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJKP"] = str_pad(number_format($row['OP_NJKP'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJKP_TANPA_PADDING"] = number_format($row['OP_NJKP'], 0, '', '.');
	$aTemplateValues["OP_TARIF"] = rtrim($row['OP_TARIF'], "0");

	$aTemplateValues["SPPT_PBB_PERSEN_PENGURANGAN"] = ' ';
	$aTemplateValues["SPPT_PBB_PENGURANGAN"] = ' ';
	$aTemplateValues["TITLE_SELISIH"] = ' ';
	$aTemplateValues["SELISIH"] = ' ';
	$aTemplateValues["TITLE_PENGURANGAN1"] = ' ';
	$aTemplateValues["TITLE_PENGURANGAN2"] = ' ';

	$SPPT_PBB_SEBELUM_PENGURANGAN = ($row['OP_TARIF'] / 100) * $row['OP_NJKP'];
	if ($row['SPPT_PBB_PENGURANGAN'] > 0) {
		$SPPT_PBB_SEBELUM_PENGURANGAN = $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'];
		$aTemplateValues["SPPT_PBB_PERSEN_PENGURANGAN"] = $row['SPPT_PBB_PERSEN_PENGURANGAN'];
		$aTemplateValues["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
		$aTemplateValues["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
		$aTemplateValues["TITLE_PENGURANGAN2"] = '= ' . number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'], 0, '', '') . ' % x ' . number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'], 0, '', '.');
	}
	if ($row['SELISIH'] > 0) {
		if ($row['PAYMENT_TYPE'] == "1") {
			$aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
			$aTemplateValues["TITLE_SELISIH"] = 'SPPT KURANG BAYAR';
		} elseif ($row['PAYMENT_TYPE'] == "2") {
			$aTemplateValues["SELISIH"] = str_pad(number_format($row['SELISIH'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
			$aTemplateValues["TITLE_SELISIH"] = 'SPPT LEBIH BAYAR';
		}
	}

	//Jika Cetak SPPT Perhitungan Lama
	// if($tahunCetak < '2014') {
	// $NJKP_OLD = $row['OP_NJOP'] - $row['OP_NJOPTKP'];
	// $SPPT_PBB_HASIL_PERHITUNGAN_OLD = ($NJKP_OLD * ($row['OP_NJKP']/100)) * 0.005;
	// $aTemplateValues["SPPT_PBB_HASIL_PERHITUNGAN_OLD"] = str_pad(number_format($SPPT_PBB_HASIL_PERHITUNGAN_OLD,0,'','.'), 17, " ", STR_PAD_LEFT);
	// $aTemplateValues["NJKP_OLD"] = str_pad(number_format($NJKP_OLD,0,'','.'), 17, " ", STR_PAD_LEFT);
	// $aTemplateValues["NJKP_OLD_TANPA_PADDING"] = number_format($NJKP_OLD,0,'','.');
	// }

	$aTemplateValues["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN, 0, '', '.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["SPPT_PBB_HARUS_DIBAYAR"] = str_pad(number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, '', '.');
	$aTemplateValues["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
	$aTemplateValues["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2) . ' ' . strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))) . ' ' . substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
	$aTemplateValues["SPPT_DOC_ID"] = generateNoReg($row); //$row['SPPT_DOC_ID'];
	$aTemplateValues["TERBILANG"] = strtoupper(SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR']));
	$aTemplateValues["TEMPAT_PEMBAYARAN"] = $appConfig['TEMPAT_PEMBAYARAN'];
	$aTemplateValues["NAMA_KOTA"] = $appConfig['NAMA_KOTA_PENGESAHAN'];
	$aTemplateValues["NAMA_PEJABAT_SK2"] = $appConfig['NAMA_PEJABAT_SK2'];
	$aTemplateValues["NAMA_PEJABAT_SK2_NIP"] = $appConfig['NAMA_PEJABAT_SK2_NIP'];
	$aTemplateValues["NAMA_PEJABAT_SK2_JABATAN"] = $appConfig['NAMA_PEJABAT_SK2_JABATAN'];
	$aTemplateValues["SEKTOR"] = str_pad($row['CPC_NM_SEKTOR'], 10, " ", STR_PAD_LEFT);
	$aTemplateValues["AKUN"] = $row['CPC_KD_AKUN'];



	return true;
} // end of

function generateNoReg($row)
{
	$noReg = "#";
	$noReg .= date('dmyHis');
	$noReg .= substr($row['SPPT_PBB_HARUS_DIBAYAR'], 0, 1);
	$noReg .= substr($row['WP_NAMA'], 0, 1);
	$noReg .= "A"; //huruf awal znt
	$noReg .= substr($row['WP_NAMA'], -1);
	$noReg .= "Z"; //huruf akhir znt
	$noReg .= strlen($row['SPPT_PBB_HARUS_DIBAYAR']);
	$noReg .= "01"; //2 digit kode bangunan
	$noReg .= "AS"; //2 digit salinan atau bukan
	$noReg .= "01"; //2 digit penetapan ke berapa
	$noReg .= "#";
	return $noReg;
}

function deleteRequest($id, $tahunCetak)
{
	global $DBLink, $tTime, $modConfig, $sRootPath, $sdata, $Setting, $prm, $appConfig;
	//template tahun 2014 kebawah disamakan dengan tahun berjalan
	//if ($tahunCetak >= '2014') $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print.xml");
	//else $sTemplateFile = $sRootPath . ("function/PBB/print/svc-print.xml");
	//$driver="epson";
	//$driver = "printonix";

	//$re = new reportEngine($sTemplateFile, $driver);

	$ids = explode(",", $id);
	$idh = null;
	$x = 0;
	foreach ($ids as $idss) {
		if ($x == 0) {
			$idh .= "'" . $idss . "'";
		} else {
			$idh .= ", '" . $idss . "'";
		}

		$x++;
	}

	$GW_DBHOST = $appConfig['GW_DBHOST'];
	$GW_DBPORT = (isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306');
	$GW_DBNAME = $appConfig['GW_DBNAME'];
	$GW_DBUSER = $appConfig['GW_DBUSER'];
	$GW_DBPWD = $appConfig['GW_DBPWD'];

	$dbConn = mysqli_connect($GW_DBHOST, $GW_DBUSER, $GW_DBPWD, $GW_DBNAME, $GW_DBPORT);

	$query = "SELECT A.* , IFNULL(A.sppt_pbb_harus_dibayar,0) AS 'SPPT_PBB_HARUS_DIBAYAR', IFNULL(B.pbb_denda,0) as 'PBB_DENDA' , IFNULL(A.sppt_pbb_harus_dibayar+B.pbb_denda,0) as 'PBB_TOTAL_BAYAR' from pbb_sppt A LEFT JOIN pbb_denda B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK WHERE A.NOP in (" . $idh . ")";

	$ress = mysqli_query($dbConn, $query);

	$nRes = mysqli_num_rows($ress);

	if ($nRes > 0) {
		while ($row = mysqli_fetch_array($ress)) {
			/*$row['OP_NJOP_BUMI_M2'] = $row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI'];
			$row['OP_NJOP_BANGUNAN_M2'] = $row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN'];

			$row['OP_NJOP_BUMI_M2_BERSAMA'] = $row['OP_NJOP_BUMI_BERSAMA'] / $row['OP_LUAS_BUMI_BERSAMA'];
			$row['OP_NJOP_BANGUNAN_M2_BERSAMA'] = $row['OP_NJOP_BANGUNAN_BERSAMA'] / $row['OP_LUAS_BANGUNAN_BERSAMA'];*/

			//$queryinsert = "insert into pbb_sppt_temp(SPPT_TAHUN_PAJAK, NOP, OP_ALAMAT, OP_RT, OP_RW, OP_KELURAHAN, OP_KECAMATAN, OP_KOTAKAB, WP_NAMA, WP_ALAMAT, WP_RT, WP_RW, WP_KELURAHAN, WP_KECAMATAN, WP_KOTAKAB, WP_KODEPOS, OP_LUAS_BUMI, OP_LUAS_BANGUNAN, OP_KELAS_BUMI, OP_KELAS_BANGUNAN, OP_NJOP_BUMI, OP_NJOP_BANGUNAN, OP_NJOP, OP_NJOPTKP, OP_NJKP, SPPT_TANGGAL_JATUH_TEMPO, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_TERBIT, SPPT_PBB_PENGURANGAN, SPPT_PBB_PERSEN_PENGURANGAN, OP_TARIF, SPPT_DOC_ID, OP_LUAS_BUMI_BERSAMA, OP_LUAS_BANGUNAN_BERSAMA, OP_NJOP_BUMI_BERSAMA, OP_NJOP_BANGUNAN_BERSAMA, OP_KELAS_BUMI_BERSAMA, OP_KELAS_BANGUNAN_BERSAMA) values ('" . $row['SPPT_TAHUN_PAJAK'] . "', '" . $row['NOP'] . "', '" . $row['OP_ALAMAT'] . "', '" . $row['OP_RT'] . "', '" . $row['OP_RW'] . "', '" . $row['OP_KELURAHAN'] . "', '" . $row['OP_KECAMATAN'] . "', '" . $row['OP_KOTAKAB'] . "', '" . $row['WP_NAMA'] . "', '" . $row['WP_ALAMAT'] . "', '" . $row['WP_RT'] . "', '" . $row['WP_RW'] . "', '" . $row['WP_KELURAHAN'] . "', '" . $row['WP_KECAMATAN'] . "', '" . $row['WP_KOTAKAB'] . "', " . $row['WP_KODEPOS'] . ", " . $row['OP_LUAS_BUMI'] . ", " . $row['OP_LUAS_BANGUNAN'] . ", '" . $row['OP_KELAS_BUMI'] . "', '" . $row['OP_KELAS_BANGUNAN'] . "', " . $row['OP_NJOP_BUMI'] . ", " . $row['OP_NJOP_BANGUNAN'] . ", '" . ($row['OP_NJOP'] == null ? 0 : $row['OP_NJOP']) . "', '" . ($row['OP_NJOPTKP'] == null ? 0 : $row['OP_NJOPTKP']) . "', '" . ($row['OP_NJKP'] == null ? '0' : $row['OP_NJKP']) . "', '" . $row['SPPT_TANGGAL_JATUH_TEMPO'] . "', " . $row['SPPT_PBB_HARUS_DIBAYAR'] . ", '" . $row['SPPT_TANGGAL_TERBIT'] . "', '" . ($row['SPPT_PBB_PENGURANGAN'] == null ? 0 : $row['SPPT_PBB_PENGURANGAN']) . "', '" . ($row['SPPT_PBB_PERSEN_PENGURANGAN'] == null ? 0 : $row['SPPT_PBB_PERSEN_PENGURANGAN']) . "', '" . ($row['OP_TARIF'] == null ? 0 : $row['OP_TARIF']) . "', '" . $row['SPPT_DOC_ID'] . "', '" . ($row['OP_LUAS_BUMI_BERSAMA'] == null ? 0 : $row['OP_LUAS_BUMI_BERSAMA']) . "', '" . ($row['OP_LUAS_BANGUNAN_BERSAMA'] == null ? 0 : $row['OP_LUAS_BANGUNAN_BERSAMA']) . "', '" . ($row['OP_NJOP_BUMI_BERSAMA'] == null ? 0 : $row['OP_NJOP_BUMI_BERSAMA']) . "', '" . ($row['OP_NJOP_BANGUNAN_BERSAMA'] == null ? 0 : $row['OP_NJOP_BANGUNAN_BERSAMA']) . "', '" . $row['OP_KELAS_BUMI_BERSAMA'] . "', '" . $row['OP_KELAS_BANGUNAN_BERSAMA'] . "')";

			$queryinsert = "insert into pbb_sppt_temp values ('" . $row['NOP'] . "', '" . $row['SPPT_TAHUN_PAJAK'] . "', '" . $row['SPPT_TANGGAL_JATUH_TEMPO'] . "', '" . $row['SPPT_PBB_HARUS_DIBAYAR'] . "', '" . $row['WP_NAMA'] . "', '" . $row['WP_TELEPON'] . "', '" . $row['WP_NO_HP'] . "', '" . $row['WP_ALAMAT'] . "', '" . $row['WP_RT'] . "', '" . $row['WP_RW'] . "', '" . $row['WP_KELURAHAN'] . "', '" . $row['WP_KECAMATAN'] . "', '" . $row['WP_KOTAKAB'] . "', " . $row['WP_KODEPOS'] . ", " . $row['SPPT_TANGGAL_TERBIT'] . ", " . $row['SPPT_TANGGAL_CETAK'] . ", " . $row['OP_LUAS_BUMI'] . ", " . $row['OP_LUAS_BANGUNAN'] . ", '" . $row['OP_KELAS_BUMI'] . "', '" . $row['OP_KELAS_BANGUNAN'] . "', " . $row['OP_NJOP_BUMI'] . ", " . $row['OP_NJOP_BANGUNAN'] . ", '" . ($row['OP_NJOP'] == null ? 0 : $row['OP_NJOP']) . "', '" . ($row['OP_NJOPTKP'] == null ? 0 : $row['OP_NJOPTKP']) . "', '" . ($row['OP_NJKP'] == null ? '0' : $row['OP_NJKP']) . "', '" . $row['PAYMENT_FLAG'] . "', '" . $row['PAYMENT_PAID'] . "', '" . $row['PAYMENT_REF_NUMBER'] . "', '" . $row['PAYMENT_BANK_CODE'] . "', '" . $row['PAYMENT_SW_REFNUM'] . "', '" . $row['PAYMENT_GW_REFNUM'] . "', '" . $row['PAYMENT_SW_ID'] . "', '" . $row['PAYMENT_MERCHANT_CODE'] . "', '" . $row['PAYMENT_SETTLEMENT_DATE'] . "', '" . ($row['PBB_COLLECTIBLE'] == null ? 0 : $row['PBB_COLLECTIBLE']) . "', '" . ($row['PBB_DENDA'] == null ? 0 : $row['PBB_DENDA']) . "', '" . ($row['PBB_ADMIN_GW'] == null ? 0 : $row['PBB_ADMIN_GW']) . "', '" . ($row['PBB_MISC_FEE'] == null ? 0 : $row['PBB_MISC_FEE']) . "', '" . ($row['PBB_TOTAL_BAYAR'] == null ? 0 : $row['PBB_TOTAL_BAYAR']) . "', '" . $row['OP_ALAMAT'] . "', '" . $row['OP_RT'] . "', '" . $row['OP_RW'] . "', '" . $row['OP_KELURAHAN'] . "', '" . $row['OP_KECAMATAN'] . "', '" . $row['OP_KOTAKAB'] . "', '" . $row['OP_KELURAHAN_KODE'] . "', '" . $row['OP_KECAMATAN_KODE'] . "', '" . $row['OP_KOTAKAB_KODE'] . "', '" . $row['OP_PROVINSI_KODE'] . "', '" . $row['TGL_STPD'] . "', '" . $row['TGL_SP1'] . "', '" . $row['TGL_SP2'] . "', '" . $row['TGL_SP3'] . "', '" . $row['STATUS_SP'] . "', '" . $row['STATUS_CETAK'] . "', '" . $row['WP_PEKERJAAN'] . "', '" . $row['PAYMENT_OFFLINE_USER_ID'] . "', '" . $row['PAYMENT_OFFLINE_FLAG'] . "', '" . $row['PAYMENT_OFFLINE_PAID'] . "', '" . $row['ID_WP'] . "', '" . $row['PAYMENT_CODE'] . "', '" . $row['BOOKING_EXPIRED'] . "', '" . $row['COLL_PAYMENT_CODE'] . "')";

			$result = mysqli_query($dbConn, $queryinsert);

			$querydelete = "delete from pbb_sppt where NOP = '" . $row['NOP'] . "'";

			$resultdelete = mysqli_query($dbConn, $querydelete);
		}
	}

	if ($appConfig['tahun_tagihan'] != null && $tahunCetak == $appConfig['tahun_tagihan']) $table = 'cppmod_pbb_sppt_current';
	else $table = "cppmod_pbb_sppt_cetak_$tahunCetak";

	$queryspptcurr = "SELECT 
					A.SPPT_TAHUN_PAJAK, A.NOP,
					A.OP_ALAMAT,A.OP_RT,A.OP_RW, A.OP_KELURAHAN, A.OP_KECAMATAN, A.OP_KOTAKAB,
					IFNULL(E.CPM_WP_NAMA,A.WP_NAMA) AS WP_NAMA, IFNULL(E.CPM_WP_ALAMAT,A.WP_ALAMAT) AS WP_ALAMAT, IFNULL(E.CPM_WP_RT,A.WP_RT) AS WP_RT, IFNULL(E.CPM_WP_RW, A.WP_RW) AS WP_RW, IFNULL(E.CPM_WP_KELURAHAN, A.WP_KELURAHAN) AS WP_KELURAHAN, IFNULL(E.CPM_WP_KECAMATAN, A.WP_KECAMATAN) AS WP_KECAMATAN, IFNULL(E.CPM_WP_KOTAKAB, A.WP_KOTAKAB) AS WP_KOTAKAB, IFNULL(E.CPM_WP_KODEPOS, A.WP_KODEPOS) AS WP_KODEPOS,
					A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, A.OP_KELAS_BUMI, A.OP_KELAS_BANGUNAN, A.OP_NJOP_BUMI, A.OP_NJOP_BANGUNAN, A.OP_NJOP,
					A.OP_NJOPTKP,A.OP_NJKP,
					A.SPPT_TANGGAL_JATUH_TEMPO, A.SPPT_PBB_HARUS_DIBAYAR, A.SPPT_TANGGAL_TERBIT, 
					A.SPPT_PBB_PENGURANGAN, A.SPPT_PBB_PERSEN_PENGURANGAN, A.OP_TARIF, A.SPPT_DOC_ID,
					A.OP_LUAS_BUMI_BERSAMA, A.OP_LUAS_BANGUNAN_BERSAMA, 
					A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,                
					A.OP_KELAS_BUMI_BERSAMA, A.OP_KELAS_BANGUNAN_BERSAMA,
					C.CPC_NM_SEKTOR, C.CPC_KD_AKUN
					FROM $table A 
					LEFT JOIN cppmod_tax_kelurahan B ON A.OP_KELURAHAN_KODE=B.CPC_TKL_ID 
					LEFT JOIN cppmod_pbb_jns_sektor C ON C.CPC_KD_SEKTOR=B.CPC_TKL_KDSEKTOR
					LEFT JOIN (
						SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt WHERE CPM_NOP in (" . $idh . ")
						UNION ALL 
						SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_final WHERE CPM_NOP in (" . $idh . ")
						UNION ALL 
						SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP in (" . $idh . ")
					) D ON A.NOP=D.CPM_NOP
					LEFT JOIN cppmod_pbb_wajib_pajak E ON E.CPM_WP_ID=D.CPM_WP_NO_KTP
					where A.NOP in (" . $idh . ")";

	$resultspptcurr = mysqli_query($DBLink, $queryspptcurr);
	$nresultspptcurr = mysqli_num_rows($resultspptcurr);

	if ($nresultspptcurr > 0) {
		while ($rowspptcurr = mysqli_fetch_array($resultspptcurr)) {
			$queryinsertspptcurr = "insert into pbb_sppt_temp(SPPT_TAHUN_PAJAK, NOP, OP_ALAMAT, OP_RT, OP_RW, OP_KELURAHAN, OP_KECAMATAN, OP_KOTAKAB, WP_NAMA, WP_ALAMAT, WP_RT, WP_RW, WP_KELURAHAN, WP_KECAMATAN, WP_KOTAKAB, WP_KODEPOS, OP_LUAS_BUMI, OP_LUAS_BANGUNAN, OP_KELAS_BUMI, OP_KELAS_BANGUNAN, OP_NJOP_BUMI, OP_NJOP_BANGUNAN, OP_NJOP, OP_NJOPTKP, OP_NJKP, SPPT_TANGGAL_JATUH_TEMPO, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_TERBIT, SPPT_PBB_PENGURANGAN, SPPT_PBB_PERSEN_PENGURANGAN, OP_TARIF, SPPT_DOC_ID, OP_LUAS_BUMI_BERSAMA, OP_LUAS_BANGUNAN_BERSAMA, OP_NJOP_BUMI_BERSAMA, OP_NJOP_BANGUNAN_BERSAMA, OP_KELAS_BUMI_BERSAMA, OP_KELAS_BANGUNAN_BERSAMA) values ('" . $rowspptcurr['SPPT_TAHUN_PAJAK'] . "', '" . $rowspptcurr['NOP'] . "', '" . $rowspptcurr['OP_ALAMAT'] . "', '" . $rowspptcurr['OP_RT'] . "', '" . $rowspptcurr['OP_RW'] . "', '" . $rowspptcurr['OP_KELURAHAN'] . "', '" . $rowspptcurr['OP_KECAMATAN'] . "', '" . $rowspptcurr['OP_KOTAKAB'] . "', '" . $rowspptcurr['WP_NAMA'] . "', '" . $rowspptcurr['WP_ALAMAT'] . "', '" . $rowspptcurr['WP_RT'] . "', '" . $rowspptcurr['WP_RW'] . "', '" . $rowspptcurr['WP_KELURAHAN'] . "', '" . $rowspptcurr['WP_KECAMATAN'] . "', '" . $rowspptcurr['WP_KOTAKAB'] . "', " . $rowspptcurr['WP_KODEPOS'] . ", " . $rowspptcurr['OP_LUAS_BUMI'] . ", " . $rowspptcurr['OP_LUAS_BANGUNAN'] . ", '" . $rowspptcurr['OP_KELAS_BUMI'] . "', '" . $rowspptcurr['OP_KELAS_BANGUNAN'] . "', " . $rowspptcurr['OP_NJOP_BUMI'] . ", " . $rowspptcurr['OP_NJOP_BANGUNAN'] . ", '" . ($rowspptcurr['OP_NJOP'] == null ? 0 : $rowspptcurr['OP_NJOP']) . "', '" . ($rowspptcurr['OP_NJOPTKP'] == null ? 0 : $rowspptcurr['OP_NJOPTKP']) . "', '" . ($rowspptcurr['OP_NJKP'] == null ? '0' : $rowspptcurr['OP_NJKP']) . "', '" . $rowspptcurr['SPPT_TANGGAL_JATUH_TEMPO'] . "', " . $rowspptcurr['SPPT_PBB_HARUS_DIBAYAR'] . ", '" . $rowspptcurr['SPPT_TANGGAL_TERBIT'] . "', '" . ($rowspptcurr['SPPT_PBB_PENGURANGAN'] == null ? 0 : $rowspptcurr['SPPT_PBB_PENGURANGAN']) . "', '" . ($rowspptcurr['SPPT_PBB_PERSEN_PENGURANGAN'] == null ? 0 : $rowspptcurr['SPPT_PBB_PERSEN_PENGURANGAN']) . "', '" . ($rowspptcurr['OP_TARIF'] == null ? 0 : $rowspptcurr['OP_TARIF']) . "', '" . $rowspptcurr['SPPT_DOC_ID'] . "', '" . ($rowspptcurr['OP_LUAS_BUMI_BERSAMA'] == null ? 0 : $rowspptcurr['OP_LUAS_BUMI_BERSAMA']) . "', '" . ($rowspptcurr['OP_LUAS_BANGUNAN_BERSAMA'] == null ? 0 : $rowspptcurr['OP_LUAS_BANGUNAN_BERSAMA']) . "', '" . ($rowspptcurr['OP_NJOP_BUMI_BERSAMA'] == null ? 0 : $rowspptcurr['OP_NJOP_BUMI_BERSAMA']) . "', '" . ($rowspptcurr['OP_NJOP_BANGUNAN_BERSAMA'] == null ? 0 : $rowspptcurr['OP_NJOP_BANGUNAN_BERSAMA']) . "', '" . $rowspptcurr['OP_KELAS_BUMI_BERSAMA'] . "', '" . $rowspptcurr['OP_KELAS_BANGUNAN_BERSAMA'] . "')";

			$resultspptcurr = mysqli_query($DBLink, $queryinsertspptcurr);

			$querydeletespptcurr = "delete from " . $table . " where NOP = '" . $rowspptcurr['NOP'] . "'";

			$resultdeletespptcurr = mysqli_query($DBLink, $querydeletespptcurr);
		}
	}

	$queryspptcurrs = "SELECT 
					A.SPPT_TAHUN_PAJAK, A.NOP,
					A.OP_ALAMAT,A.OP_RT,A.OP_RW, A.OP_KELURAHAN, A.OP_KECAMATAN, A.OP_KOTAKAB,
					IFNULL(E.CPM_WP_NAMA,A.WP_NAMA) AS WP_NAMA, IFNULL(E.CPM_WP_ALAMAT,A.WP_ALAMAT) AS WP_ALAMAT, IFNULL(E.CPM_WP_RT,A.WP_RT) AS WP_RT, IFNULL(E.CPM_WP_RW, A.WP_RW) AS WP_RW, IFNULL(E.CPM_WP_KELURAHAN, A.WP_KELURAHAN) AS WP_KELURAHAN, IFNULL(E.CPM_WP_KECAMATAN, A.WP_KECAMATAN) AS WP_KECAMATAN, IFNULL(E.CPM_WP_KOTAKAB, A.WP_KOTAKAB) AS WP_KOTAKAB, IFNULL(E.CPM_WP_KODEPOS, A.WP_KODEPOS) AS WP_KODEPOS,
					A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, A.OP_KELAS_BUMI, A.OP_KELAS_BANGUNAN, A.OP_NJOP_BUMI, A.OP_NJOP_BANGUNAN, A.OP_NJOP,
					A.OP_NJOPTKP,A.OP_NJKP,
					A.SPPT_TANGGAL_JATUH_TEMPO, A.SPPT_PBB_HARUS_DIBAYAR, A.SPPT_TANGGAL_TERBIT, 
					A.SPPT_PBB_PENGURANGAN, A.SPPT_PBB_PERSEN_PENGURANGAN, A.OP_TARIF, A.SPPT_DOC_ID,
					A.OP_LUAS_BUMI_BERSAMA, A.OP_LUAS_BANGUNAN_BERSAMA, 
					A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,                
					A.OP_KELAS_BUMI_BERSAMA, A.OP_KELAS_BANGUNAN_BERSAMA,
					C.CPC_NM_SEKTOR, C.CPC_KD_AKUN
					FROM cppmod_pbb_sppt_current A 
					LEFT JOIN cppmod_tax_kelurahan B ON A.OP_KELURAHAN_KODE=B.CPC_TKL_ID 
					LEFT JOIN cppmod_pbb_jns_sektor C ON C.CPC_KD_SEKTOR=B.CPC_TKL_KDSEKTOR
					LEFT JOIN (
						SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt WHERE CPM_NOP in (" . $idh . ")
						UNION ALL 
						SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_final WHERE CPM_NOP in (" . $idh . ")
						UNION ALL 
						SELECT CPM_NOP, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP in (" . $idh . ")
					) D ON A.NOP=D.CPM_NOP
					LEFT JOIN cppmod_pbb_wajib_pajak E ON E.CPM_WP_ID=D.CPM_WP_NO_KTP
					where A.NOP in (" . $idh . ")";

	$resultspptcurrs = mysqli_query($DBLink, $queryspptcurrs);
	$nresultspptcurrs = mysqli_num_rows($resultspptcurrs);

	if ($nresultspptcurrs > 0) {
		while ($rowspptcurrs = mysqli_fetch_array($resultspptcurrs)) {
			$queryinsertspptcurrs = "insert into pbb_sppt_temp(SPPT_TAHUN_PAJAK, NOP, OP_ALAMAT, OP_RT, OP_RW, OP_KELURAHAN, OP_KECAMATAN, OP_KOTAKAB, WP_NAMA, WP_ALAMAT, WP_RT, WP_RW, WP_KELURAHAN, WP_KECAMATAN, WP_KOTAKAB, WP_KODEPOS, OP_LUAS_BUMI, OP_LUAS_BANGUNAN, OP_KELAS_BUMI, OP_KELAS_BANGUNAN, OP_NJOP_BUMI, OP_NJOP_BANGUNAN, OP_NJOP, OP_NJOPTKP, OP_NJKP, SPPT_TANGGAL_JATUH_TEMPO, SPPT_PBB_HARUS_DIBAYAR, SPPT_TANGGAL_TERBIT, SPPT_PBB_PENGURANGAN, SPPT_PBB_PERSEN_PENGURANGAN, OP_TARIF, SPPT_DOC_ID, OP_LUAS_BUMI_BERSAMA, OP_LUAS_BANGUNAN_BERSAMA, OP_NJOP_BUMI_BERSAMA, OP_NJOP_BANGUNAN_BERSAMA, OP_KELAS_BUMI_BERSAMA, OP_KELAS_BANGUNAN_BERSAMA) values ('" . $rowspptcurrs['SPPT_TAHUN_PAJAK'] . "', '" . $rowspptcurrs['NOP'] . "', '" . $rowspptcurrs['OP_ALAMAT'] . "', '" . $rowspptcurrs['OP_RT'] . "', '" . $rowspptcurrs['OP_RW'] . "', '" . $rowspptcurrs['OP_KELURAHAN'] . "', '" . $rowspptcurrs['OP_KECAMATAN'] . "', '" . $rowspptcurrs['OP_KOTAKAB'] . "', '" . $rowspptcurrs['WP_NAMA'] . "', '" . $rowspptcurrs['WP_ALAMAT'] . "', '" . $rowspptcurrs['WP_RT'] . "', '" . $rowspptcurrs['WP_RW'] . "', '" . $rowspptcurrs['WP_KELURAHAN'] . "', '" . $rowspptcurrs['WP_KECAMATAN'] . "', '" . $rowspptcurrs['WP_KOTAKAB'] . "', " . $rowspptcurrs['WP_KODEPOS'] . ", " . $rowspptcurrs['OP_LUAS_BUMI'] . ", " . $rowspptcurrs['OP_LUAS_BANGUNAN'] . ", '" . $rowspptcurrs['OP_KELAS_BUMI'] . "', '" . $rowspptcurrs['OP_KELAS_BANGUNAN'] . "', " . $rowspptcurrs['OP_NJOP_BUMI'] . ", " . $rowspptcurrs['OP_NJOP_BANGUNAN'] . ", '" . ($rowspptcurrs['OP_NJOP'] == null ? 0 : $rowspptcurrs['OP_NJOP']) . "', '" . ($rowspptcurrs['OP_NJOPTKP'] == null ? 0 : $rowspptcurrs['OP_NJOPTKP']) . "', '" . ($rowspptcurrs['OP_NJKP'] == null ? '0' : $rowspptcurrs['OP_NJKP']) . "', '" . $rowspptcurrs['SPPT_TANGGAL_JATUH_TEMPO'] . "', " . $rowspptcurrs['SPPT_PBB_HARUS_DIBAYAR'] . ", '" . $rowspptcurrs['SPPT_TANGGAL_TERBIT'] . "', '" . ($rowspptcurrs['SPPT_PBB_PENGURANGAN'] == null ? 0 : $rowspptcurrs['SPPT_PBB_PENGURANGAN']) . "', '" . ($rowspptcurrs['SPPT_PBB_PERSEN_PENGURANGAN'] == null ? 0 : $rowspptcurrs['SPPT_PBB_PERSEN_PENGURANGAN']) . "', '" . ($rowspptcurrs['OP_TARIF'] == null ? 0 : $rowspptcurrs['OP_TARIF']) . "', '" . $rowspptcurrs['SPPT_DOC_ID'] . "', '" . ($rowspptcurrs['OP_LUAS_BUMI_BERSAMA'] == null ? 0 : $rowspptcurrs['OP_LUAS_BUMI_BERSAMA']) . "', '" . ($rowspptcurrs['OP_LUAS_BANGUNAN_BERSAMA'] == null ? 0 : $rowspptcurrs['OP_LUAS_BANGUNAN_BERSAMA']) . "', '" . ($rowspptcurrs['OP_NJOP_BUMI_BERSAMA'] == null ? 0 : $rowspptcurrs['OP_NJOP_BUMI_BERSAMA']) . "', '" . ($rowspptcurrs['OP_NJOP_BANGUNAN_BERSAMA'] == null ? 0 : $rowspptcurrs['OP_NJOP_BANGUNAN_BERSAMA']) . "', '" . $rowspptcurrs['OP_KELAS_BUMI_BERSAMA'] . "', '" . $rowspptcurrs['OP_KELAS_BANGUNAN_BERSAMA'] . "')";

			$resultspptcurrs = mysqli_query($DBLink, $queryinsertspptcurrs);

			$querydeletespptcurrs = "delete from cppmod_pbb_sppt_current where NOP = '" . $rowspptcurrs['NOP'] . "'";

			$resultdeletespptcurrs = mysqli_query($DBLink, $querydeletespptcurrs);
		}
	}

	$queryspptfinal = "SELECT * FROM cppmod_pbb_sppt_final where CPM_NOP in (" . $idh . ")";

	$resultspptfinals = mysqli_query($DBLink, $queryspptfinal);
	$nresultspptfinal = mysqli_num_rows($resultspptfinals);

	if ($nresultspptfinal > 0) {
		while ($rowspptfinal = mysqli_fetch_array($resultspptfinals)) {
			$queryinsertspptfinal = "INSERT INTO cppmod_pbb_sppt_final_temp (CPM_SPPT_DOC_ID, CPM_SPPT_DOC_VERSION, CPM_SPPT_DOC_AUTHOR, CPM_SPPT_DOC_CREATED, CPM_NOP, CPM_NOP_BERSAMA, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_KELURAHAN, CPM_OP_RT, CPM_OP_RW, CPM_OP_KECAMATAN, CPM_OP_KOTAKAB, CPM_WP_STATUS, CPM_WP_PEKERJAAN, CPM_WP_NAMA, CPM_WP_ID, CPM_WP_ALAMAT, CPM_WP_KELURAHAN, CPM_WP_RT, CPM_WP_RW, CPM_WP_PROPINSI, CPM_WP_KOTAKAB, CPM_WP_KECAMATAN, CPM_WP_KODEPOS, CPM_WP_NO_KTP, CPM_WP_NO_HP, CPM_OT_LATITUDE, CPM_OT_LONGITUDE, CPM_OT_ZONA_NILAI, CPM_OT_JENIS, CPM_OT_PENILAIAN_TANAH, CPM_OT_PAYMENT_SISTEM, CPM_OT_PAYMENT_INDIVIDU, CPM_OP_JML_BANGUNAN, CPM_PP_TIPE, CPM_PP_NAMA, CPM_PP_DATE, CPM_OPR_TGL_PENDATAAN, CPM_OPR_NAMA, CPM_OPR_NIP, CPM_PJB_TGL_PENELITIAN, CPM_PJB_NAMA, CPM_PJB_NIP, CPM_OP_SKET, CPM_OP_FOTO, CPM_OP_LUAS_TANAH, CPM_OP_KELAS_TANAH, CPM_NJOP_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_OP_KELAS_BANGUNAN, CPM_NJOP_BANGUNAN, CPM_SPPT_THN_PENETAPAN, CPM_OP_ACCOUNT) VALUES
			('" . $rowspptfinal['CPM_SPPT_DOC_ID'] . "', '" . $rowspptfinal['CPM_SPPT_DOC_VERSION'] . "', '" . $rowspptfinal['CPM_SPPT_DOC_AUTHOR'] . "', '" . $rowspptfinal['CPM_SPPT_DOC_CREATED'] . "', '" . $rowspptfinal['CPM_NOP'] . "', '" . $rowspptfinal['CPM_NOP_BERSAMA'] . "', '" . $rowspptfinal['CPM_OP_ALAMAT'] . "', '" . $rowspptfinal['CPM_OP_NOMOR'] . "', '" . $rowspptfinal['CPM_OP_KELURAHAN'] . "', '" . $rowspptfinal['CPM_OP_RT'] . "', '" . $rowspptfinal['CPM_OP_RW'] . "', '" . $rowspptfinal['CPM_OP_KECAMATAN'] . "', '" . $rowspptfinal['CPM_OP_KOTAKAB'] . "', '" . $rowspptfinal['CPM_WP_STATUS'] . "', '" . $rowspptfinal['CPM_WP_PEKERJAAN'] . "', '" . $rowspptfinal['CPM_WP_NAMA'] . "', '" . $rowspptfinal['CPM_WP_ID'] . "', '" . $rowspptfinal['CPM_WP_ALAMAT'] . "', '" . $rowspptfinal['CPM_WP_KELURAHAN'] . "', '" . $rowspptfinal['CPM_WP_RT'] . "', '" . $rowspptfinal['CPM_WP_RW'] . "', '" . $rowspptfinal['CPM_WP_PROPINSI'] . "', '" . $rowspptfinal['CPM_WP_KOTAKAB'] . "', '" . $rowspptfinal['CPM_WP_KECAMATAN'] . "', '" . $rowspptfinal['CPM_WP_KODEPOS'] . "', '" . $rowspptfinal['CPM_WP_NO_KTP'] . "', '" . $rowspptfinal['CPM_WP_NO_HP'] . "', '" . $rowspptfinal['CPM_OT_LATITUDE'] . "', '" . $rowspptfinal['CPM_OT_LONGITUDE'] . "', '" . $rowspptfinal['CPM_OT_ZONA_NILAI'] . "', '" . $rowspptfinal['CPM_OT_JENIS'] . "', '" . $rowspptfinal['CPM_OT_PENILAIAN_TANAH'] . "', '" . $rowspptfinal['CPM_OT_PAYMENT_SISTEM'] . "', '" . $rowspptfinal['CPM_OT_PAYMENT_INDIVIDU'] . "', '" . $rowspptfinal['CPM_OP_JML_BANGUNAN'] . "', '" . $rowspptfinal['CPM_PP_TIPE'] . "', '" . $rowspptfinal['CPM_PP_NAMA'] . "', '" . $rowspptfinal['CPM_PP_DATE'] . "', '" . $rowspptfinal['CPM_OPR_TGL_PENDATAAN'] . "', '" . $rowspptfinal['CPM_OPR_NAMA'] . "', '" . $rowspptfinal['CPM_OPR_NIP'] . "', '" . $rowspptfinal['CPM_PJB_TGL_PENELITIAN'] . "', '" . $rowspptfinal['CPM_PJB_NAMA'] . "', '" . $rowspptfinal['CPM_PJB_NIP'] . "', '" . $rowspptfinal['CPM_OP_SKET'] . "', '" . $rowspptfinal['CPM_OP_FOTO'] . "', '" . $rowspptfinal['CPM_OP_LUAS_TANAH'] . "', '" . $rowspptfinal['CPM_OP_KELAS_TANAH'] . "', '" . $rowspptfinal['CPM_NJOP_TANAH'] . "', '" . $rowspptfinal['CPM_OP_LUAS_BANGUNAN'] . "', '" . $rowspptfinal['CPM_OP_KELAS_BANGUNAN'] . "', '" . $rowspptfinal['CPM_NJOP_BANGUNAN'] . "', '" . $rowspptfinal['CPM_SPPT_THN_PENETAPAN'] . "', '" . $rowspptfinal['CPM_OP_ACCOUNT'] . "')";

			$resultspptfinal = mysqli_query($DBLink, $queryinsertspptfinal);

			$querydeletespptfinal = "delete from cppmod_pbb_sppt_final where CPM_NOP = '" . $rowspptfinal['CPM_NOP'] . "'";

			$resultdeletespptfinal = mysqli_query($DBLink, $querydeletespptfinal);
		}
	}


	/*$re = new reportEngine($sTemplateFile, $driver);

	if (GetValuesForPrint($aTemplateValue, $row)) {
		$re->ApplyTemplateValue($aTemplateValue);
		if ($driver == "other") {
			$re->Print2OnpaysTXT($printValue);
			$strTXT = $printValue;
		} else {
			$re->Print2TXT($printValue);
			$strTXT = base64_encode($printValue);
		}


		$re->PrintHTML($strHTML);
		//                echo $strHTML; exit();
	}
	return $strTXT;*/
}

$tTime = time();
$paymentDt;
//$params = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
//$p = base64_decode($params);
$json = new Services_JSON();
//$prm = $json->decode($p);
$appID = $_REQUEST["appID"];
$NOP = $_REQUEST["NOP"];
$tahun = $_REQUEST["tahun"];
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$appConfig = $User->GetAppConfig($appID);
$tahunCetak = $tahun;

$Setting = new SCANCentralSetting(DEBUG, LOG_FILENAME, $DBLink);

$arrValues = array();

$appConfig = $User->GetAppConfig($appID);

// var_dump($sdata);
// var_dump($json);
// var_dump($DBLink);
// exit;
if (stillInSession($DBLink, $json, $sdata)) {
	if ($NOP) {
		doDelete($NOP, $arrValues, $tahunCetak);
	}
} else {
	$arrValues['result'] = false;
	$arrValues['message'] = "Penghapusan massal gagal. Silahkan lakukan login terlebih dahulu.";
}

echo json_encode($arrValues);
