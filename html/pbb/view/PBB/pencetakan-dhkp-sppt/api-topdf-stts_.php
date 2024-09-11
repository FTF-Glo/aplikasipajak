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

// function getData(){
// global $DBGWLink, $appConfig, $kd_kel, $tahun, $blok;

// $query = sprintf("SELECT
// A.NOP,
// A.SPPT_TAHUN_PAJAK,
// A.WP_NAMA,
// A.OP_KECAMATAN,
// A.OP_KELURAHAN,
// A.SPPT_TANGGAL_JATUH_TEMPO,
// A.OP_LUAS_BUMI,
// A.OP_LUAS_BANGUNAN,
// A.SPPT_PBB_HARUS_DIBAYAR,
// A.PBB_DENDA SPPT_DENDA,
// IFNULL(
// A.PAYMENT_PAID,
// DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
// ) AS PAYMENT_PAID
// FROM
// PBB_SPPT A
// WHERE
// SUBSTR(A.NOP,1,10) = '%s'
// AND A.SPPT_TAHUN_PAJAK = '%s'
// LIMIT 1", $kd_kel, $tahun);

// $res = mysqli_query($DBLink, $query);
// if ($res === false) {
// echo mysqli_error($DBLink);
// exit();
// }

// $data  = array();
// $i     = 0;
// while($row  = mysqli_fetch_assoc($res)){
// $nilai = $row['SPPT_PBB_HARUS_DIBAYAR'];
// $denda = $row['SPPT_DENDA'];
// $total = $nilai + $denda;

// $templatePrintValue['TEMPAT_BAYAR'] 	= $appConfig['TEMPAT_PEMBAYARAN'];
// $templatePrintValue['THN_BAYAR'] 		= $row['SPPT_TAHUN_PAJAK'];
// $templatePrintValue['THN_DARI'] 		= date('Y');
// $templatePrintValue['SUBJECT_NAME'] 	= $row['WP_NAMA'];
// $templatePrintValue['OBJECT_KECAMATAN'] = $row['OP_KECAMATAN'];
// $templatePrintValue['OBJECT_KELURAHAN'] = $row['OP_KELURAHAN'];
// $templatePrintValue['NOPNPWP'] 			= substr($row['NOP'],0,2).'.'.substr($row['NOP'],2,2).'.'.substr($row['NOP'],4,3).'.'.substr($row['NOP'],7,3).'.'.substr($row['NOP'],10,3).'-'.substr($row['NOP'],13,4).'.'.substr($row['NOP'],17,1);
// $templatePrintValue['TRAN_AMOUNT_TEXT'] = number_format($nilai,2,',','.');;
// $templatePrintValue['JTHTMP'] 			= $row['SPPT_TANGGAL_JATUH_TEMPO'];
// $templatePrintValue['TGL_BAYAR'] 		= $row['PAYMENT_PAID'];
// $templatePrintValue['LT'] 				= $row['OP_LUAS_BUMI'];
// $templatePrintValue['LB'] 				= $row['OP_LUAS_BANGUNAN'];
// $templatePrintValue['TOT_TRAN_AMOUNT_TEXT'] = number_format($total,2,',','.');

// for($i=1; $i<=24; $i++){
// $totalBulan = $nilai + (((2/100) * $nilai)*$i);
// $templatePrintValue["BAYAR_PLUS_DENDA_$i"] = number_format($totalBulan,2,',','.');
// }	

// $data[] = $templatePrintValue;
// $i++;
// }
// return $data;
// }

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
	return $html = "";
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
		$this->SetMargins(6, 10, 35);
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
	global $kd_kel, $blok, $path, $jumlahNOP, $maxNopPerFile, $tahun, $totalFileSize, $arrFiles;

	$fullBlok = $kd_kel . $blok;

	$fileName = "blok-{$fullBlok}-{$tahun}-" . date('Ymd');
	#$width = 180;
	#$height = 215;


	try {
		$pdf = new SPPT_PDF($fullBlok);

		$x = 0;
		$nomorFile = 1;
		do {
			// $html = buildHTML($data);
			$html = buildHTML($data[$x]);
			#$pdf->AddPage('P', array($height,$width));
			$pdf->AddPage('P', 'A4');
			$pdf->writeHTML($html, true, false, false, false, '');
			$x++;

			if ($x == ($jumlahNOP)) {
				/*ARD: jika akhir data*/
				$namaFile = ($nomorFile > 1) ? ($fileName . '-' . $nomorFile . '.pdf') : ($fileName . '.pdf');
				$fullPath = $path . $namaFile;
				if (!file_exists($fullPath)) $pdf->Output($fullPath, 'F');
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
if (isset($_POST['a'])) {
	$a = @$_POST['a'];
	$m = @$_POST['m'];
	$kd_kel = @$_POST['kd_kel'];
	$blok = @$_POST['blok'];
	$tahun = @$_POST['tahun'];
	$uid = @$_POST['uid'];
	$path = $sRootPath . "pdf-stts/";

	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$arConfig 	= $User->GetModuleConfig($m);
	$appConfig 	= $User->GetAppConfig($a);

	// SCANPayment_ConnectToDB($DBGWLink, $DBConn, $appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBUSER'], $appConfig['GW_DBNAME'], true);
	// if ($iErrCode != 0) {
	// $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	// if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
	// error_log("[". strftime("%Y%m%d%H%M%S", time()) ."][". (basename(__FILE__)) .":". __LINE__ ."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	// exit(1);
	// }

	$maxNopPerFile = $appConfig['MAX_NOP_PERFILE'];
	$totalFileSize = 0;
	$arrFiles = array();
	/*main process*/
	$data = getData();
	// print_r($data); exit;
	$jumlahNOP = count($data);

	if ($jumlahNOP == 0) exit;
	if (savePdf($data)) {

		$size = formatSizeUnits($totalFileSize);
		$param = array(
			"CPM_SIZE = '{$size}'",
			"CPM_STATUS = '1'",
			"CPM_JUMLAH_NOP = '{$jumlahNOP}'",
			"CPM_FILES = '" . implode(";", $arrFiles) . "'",
		);

		$sets = implode(',', $param);
		$query = "UPDATE cppmod_pbb_stts_download SET {$sets} WHERE CPM_ID ='{$uid}'";
		$sql = mysqli_query($DBLink, $query);
	}
}
