<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_individu', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
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

//error_reporting(E_ALL);
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
	return strtoupper($bulan);
}

function getData()
{
	global $DBLink, $thn, $kecamatan, $kelurahan;

	$addCondition = "";
	if ($thn != "") {
		$addCondition .= " AND A.CPM_SPPT_THN_PENETAPAN = '" . $thn . "' ";
	}
	if ($kecamatan != "") {
		$addCondition .= " AND A.CPM_OP_KECAMATAN = '" . $kecamatan . "' ";
	}
	if ($kelurahan != "") {
		$addCondition .= " AND A.CPM_OP_KELURAHAN = '" . $kelurahan . "' ";
	}
	$query = "SELECT * FROM (SELECT
				A.CPM_SPPT_DOC_ID,
				A.CPM_NOP AS NOP,
				A.CPM_WP_NAMA AS NAMA,
				A.CPM_OP_ALAMAT AS ALAMAT,
				A.CPM_OP_RT AS RT,
				A.CPM_OP_RW AS RW,
				A.CPM_OP_KELURAHAN AS KELURAHAN,
				A.CPM_OP_KECAMATAN AS KECAMATAN,
				A.CPM_OP_LUAS_TANAH AS LUAS_TANAH,
				A.CPM_OP_LUAS_BANGUNAN AS LUAS_BANGUNAN,
				A.CPM_OT_ZONA_NILAI AS ZNT,
				A.CPM_OP_KELAS_TANAH AS KLS_TANAH,
				A.CPM_OP_KELAS_BANGUNAN AS KLS_BANGUNAN,
				A.CPM_NJOP_TANAH AS NJOP_TANAH,
				A.CPM_NJOP_BANGUNAN AS NJOP_BANGUNAN,
				B.CPM_OP_NUM AS NO_BANGUNAN,
				B.CPM_PAYMENT_INDIVIDU AS NILAI_INDIVIDU,
				COUNT(B.CPM_SPPT_DOC_ID) AS JML_BNG,
				C.CPM_LUAS_BUMI_BEBAN AS LUAS_TNH_BERSAMA,
				C.CPM_LUAS_BNG_BEBAN AS LUAS_BNG_BERSAMA,
				C.CPM_NJOP_BUMI_BEBAN AS NJOP_TNH_BERSAMA,
				C.CPM_NJOP_BNG_BEBAN AS NJOP_BNG_BERSAMA
			FROM
				cppmod_pbb_sppt_final A
			LEFT JOIN cppmod_pbb_sppt_ext_final B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
			LEFT JOIN cppmod_pbb_sppt_anggota C ON A.CPM_NOP=C.CPM_NOP
			WHERE
				B.CPM_PAYMENT_PENILAIAN_BGN = 'individu' " . $addCondition . "
			GROUP BY A.CPM_NOP
			UNION
			SELECT
				A.CPM_SPPT_DOC_ID,
				A.CPM_NOP AS NOP,
				A.CPM_WP_NAMA AS NAMA,
				A.CPM_OP_ALAMAT AS ALAMAT,
				A.CPM_OP_RT AS RT,
				A.CPM_OP_RW AS RW,
				A.CPM_OP_KELURAHAN AS KELURAHAN,
				A.CPM_OP_KECAMATAN AS KECAMATAN,
				A.CPM_OP_LUAS_TANAH AS LUAS_TANAH,
				A.CPM_OP_LUAS_BANGUNAN AS LUAS_BANGUNAN,
				A.CPM_OT_ZONA_NILAI AS ZNT,
				A.CPM_OP_KELAS_TANAH AS KLS_TANAH,
				A.CPM_OP_KELAS_BANGUNAN AS KLS_BANGUNAN,
				A.CPM_NJOP_TANAH AS NJOP_TANAH,
				A.CPM_NJOP_BANGUNAN AS NJOP_BANGUNAN,
				B.CPM_OP_NUM AS NO_BANGUNAN,
				B.CPM_PAYMENT_INDIVIDU AS NILAI_INDIVIDU,
				COUNT(B.CPM_SPPT_DOC_ID) AS JML_BNG,
				C.CPM_LUAS_BUMI_BEBAN AS LUAS_TNH_BERSAMA,
				C.CPM_LUAS_BNG_BEBAN AS LUAS_BNG_BERSAMA,
				C.CPM_NJOP_BUMI_BEBAN AS NJOP_TNH_BERSAMA,
				C.CPM_NJOP_BNG_BEBAN AS NJOP_BNG_BERSAMA
			FROM
				cppmod_pbb_sppt_susulan A
			LEFT JOIN cppmod_pbb_sppt_ext_susulan B ON A.CPM_SPPT_DOC_ID=B.CPM_SPPT_DOC_ID
			LEFT JOIN cppmod_pbb_sppt_anggota C ON A.CPM_NOP=C.CPM_NOP
			WHERE
				B.CPM_PAYMENT_PENILAIAN_BGN = 'individu' " . $addCondition . "
			GROUP BY A.CPM_NOP ) AS TBL ORDER BY CPM_SPPT_DOC_ID";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$data  = array();
	$i     = 0;
	while ($rows  = mysqli_fetch_assoc($res)) {
		$data[$i]['NOP'] 			= $rows['NOP'];
		$data[$i]['NAMA'] 			= $rows['NAMA'];
		$data[$i]['ALAMAT'] 		= $rows['ALAMAT'];
		$data[$i]['RT'] 			= $rows['RT'];
		$data[$i]['RW'] 			= $rows['RW'];
		$data[$i]['KELURAHAN'] 		= $rows['KELURAHAN'];
		$data[$i]['KECAMATAN'] 		= $rows['KECAMATAN'];
		$data[$i]['LUAS_TANAH'] 	= $rows['LUAS_TANAH'];
		$data[$i]['LUAS_BANGUNAN'] 	= $rows['LUAS_BANGUNAN'];
		$data[$i]['ZNT'] 			= $rows['ZNT'];
		$data[$i]['KLS_TANAH']		= $rows['KLS_TANAH'];
		$data[$i]['KLS_BANGUNAN']	= $rows['KLS_BANGUNAN'];
		$data[$i]['NJOP_TANAH']		= $rows['NJOP_TANAH'];
		$data[$i]['NJOP_BANGUNAN']	= $rows['NJOP_BANGUNAN'];
		$data[$i]['NO_BANGUNAN'] 	= $rows['NO_BANGUNAN'];
		$data[$i]['NILAI_INDIVIDU'] = $rows['NILAI_INDIVIDU'];
		$data[$i]['JML_BNG'] 		= $rows['JML_BNG'];
		$data[$i]['LUAS_TNH_BERSAMA'] = ($rows['LUAS_TNH_BERSAMA'] != '' ? $rows['LUAS_TNH_BERSAMA'] : 0);
		$data[$i]['LUAS_BNG_BERSAMA'] = ($rows['LUAS_BNG_BERSAMA'] != '' ? $rows['LUAS_BNG_BERSAMA'] : 0);
		$data[$i]['NJOP_TNH_BERSAMA'] = ($rows['NJOP_TNH_BERSAMA'] != '' ? $rows['NJOP_TNH_BERSAMA'] : 0);
		$data[$i]['NJOP_BNG_BERSAMA'] = ($rows['NJOP_BNG_BERSAMA'] != '' ? $rows['NJOP_BNG_BERSAMA'] : 0);
		$i++;
	}
	return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;
// print_r($_REQUEST); exit;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$kd 		= $appConfig['KODE_KOTA'];
$nm_kota	= $appConfig['NAMA_KOTA'];
$nm_prov	= $appConfig['NAMA_PROVINSI'];
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec	= @isset($_REQUEST['nkec']) ? $_REQUEST['nkec'] : "";
$namaKel	= @isset($_REQUEST['nkel']) ? $_REQUEST['nkel'] : "";

$lKecamatan = "";
$lKelurahan = "";

$data = getData();
$sumRows = count($data);
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
// Set properties
$objPHPExcel->getProperties()
	->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");

$objPHPExcel->getDefaultStyle()->getFont()
	->setName('Courier New');
// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);
function lampiran($row)
{
	global $objPHPExcel, $nm_kota;
	$row1 = $row;
	$row4 = $row + 4;

	#1
	$objPHPExcel->getActiveSheet()->mergeCells("L{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("L{$row}", " LAMPIRAN III : ");
	$row++; #2
	$objPHPExcel->getActiveSheet()->mergeCells("L{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("L{$row}", " KEPUTUSAN WALIKOTA " . $nm_kota);
	$row++; #3
	$objPHPExcel->getActiveSheet()->mergeCells("L{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("L{$row}", " NOMOR 	 : 292/KEP/DPPKAD/VI/2016");
	$row++; #4
	$objPHPExcel->getActiveSheet()->mergeCells("L{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("L{$row}", " TANGGAL : " . date("d") . " " . bulan(date("m")) . " " . date("Y"));
	$row++; #5
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:O{$row}");
	$row++; #6

	$objPHPExcel->getActiveSheet()->getStyle("L{$row1}:L{$row4}")->applyFromArray(
		array(
			'font' => array(
				'bold' => true
			)
		)
	);

	return $row;
}

function judul($row)
{
	global $objPHPExcel, $nm_kota, $thn, $appConfig, $nm_prov, $kecamatan, $kelurahan, $namaKec, $namaKel, $kd;
	$row6 = $row;
	$row7 = $row + 1;
	#6
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " KLASIFIKASI DAN BESARNYA NJOP BUMI DAN BANGUNAN ");
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$row++; #7
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " DENGAN NILAI INDIVIDU " . $thn);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$row++; #8
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("K{$row}:O{$row}");
	$row++; #9
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("K{$row}:O{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " PROVINSI : " . $appConfig["KODE_PROVINSI"] . " - " . $nm_prov);
	$objPHPExcel->getActiveSheet()->setCellValue("K{$row}", " KECAMATAN : " . substr($kecamatan, 4) . " - " . $namaKec);
	$row++; #10
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
	$objPHPExcel->getActiveSheet()->setCellValue("A{$row}", " KOTA : " . substr($kd, 2) . " - " . $nm_kota);
	$objPHPExcel->getActiveSheet()->setCellValue("K{$row}", " " . strtoupper($appConfig["LABEL_KELURAHAN"]) . " : " . substr($kelurahan, 7) . " - " . $namaKel);

	$objPHPExcel->getActiveSheet()->getStyle("A{$row6}:O{$row7}")->applyFromArray(
		array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row6}:O{$row}")->applyFromArray(
		array(
			'font' => array(
				'bold' => true
			)
		)
	);
	return $row;
}
function table_header($row)
{
	global $objPHPExcel;
	#12
	$row2 = $row + 1;
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:A{$row2}");
	$objPHPExcel->getActiveSheet()->mergeCells("B{$row}:B{$row2}");
	$objPHPExcel->getActiveSheet()->mergeCells("C{$row}:C{$row2}");
	$objPHPExcel->getActiveSheet()->mergeCells("D{$row}:D{$row2}");
	$objPHPExcel->getActiveSheet()->mergeCells("E{$row}:E{$row2}");
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:G{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("H{$row}:H{$row2}");
	$objPHPExcel->getActiveSheet()->mergeCells("I{$row}:J{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("K{$row}:L{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("M{$row}:N{$row}");
	$objPHPExcel->getActiveSheet()->mergeCells("O{$row}:O{$row2}");
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue("A{$row}", " NO ")
		->setCellValue("B{$row}", " NOMOR OBJEK\n PAJAK ")
		->setCellValue("C{$row}", " NAMA WAJIB PAJAK\n ALAMAT OBJEK PAJAK ")
		->setCellValue("D{$row}", " RT/\nRW ")
		->setCellValue("E{$row}", " JML\nBNG ")
		->setCellValue("F{$row}", " LUAS ")
		->setCellValue("F{$row2}", " TANAH\n TNH-BERS ")
		->setCellValue("G{$row2}", " BNG\n BNG-BERS ")
		->setCellValue("H{$row}", " KODE\nZNT ")
		->setCellValue("I{$row}", " KELAS ")
		->setCellValue("I{$row2}", " TNH ")
		->setCellValue("J{$row2}", " BNG ")
		->setCellValue("K{$row}", " NJOP/M2 ")
		->setCellValue("K{$row2}", " TANAH\n TNH-BERS ")
		->setCellValue("L{$row2}", " BNG\n BNG-BERS ")
		->setCellValue("M{$row}", " NJOP ")
		->setCellValue("M{$row2}", " TANAH\n TNH-BERS")
		->setCellValue("N{$row2}", " BNG\n BNG-BERS ")
		->setCellValue("O{$row}", " JUMLAH NJOP\n(Rp 000,-)");

	//style header
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}:O{$row2}")->applyFromArray(
		array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'font' => array(
				'bold' => true
			)
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle("E{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("F{$row2}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("G{$row2}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("K{$row2}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("L{$row2}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("M{$row2}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("N{$row2}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("H{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("O{$row}")->getAlignment()->setWrapText(true);

	return $row2++;
}

function fetchData()
{
	global $objPHPExcel, $data, $row;
	$row1 = $row;
	for ($i = 0; $i < count($data); $i++) {

		if (!isset($data[$i])) break;

		$totalNJOPBersama 	  	  	  = $data[$i]['NJOP_TNH_BERSAMA'] + $data[$i]['NJOP_BNG_BERSAMA'];
		$NJOPTanahPerMeterBersama 	  = ($data[$i]['LUAS_TNH_BERSAMA'] != 0 && $data[$i]['NJOP_TNH_BERSAMA'] != 0 ? ($data[$i]['NJOP_TNH_BERSAMA'] / $data[$i]['LUAS_TNH_BERSAMA']) : 0);
		$NJOPBangunanPerMeterBersama  = ($data[$i]['LUAS_BNG_BERSAMA'] != 0 && $data[$i]['NJOP_BNG_BERSAMA'] != 0 ? ($data[$i]['NJOP_BNG_BERSAMA'] / $data[$i]['LUAS_BNG_BERSAMA']) : 0);

		$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($i + 1));
		$objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($data[$i]['NOP'] . " "));
		$objPHPExcel->getActiveSheet()->setCellValue('C' . $row, ($data[$i]['NAMA'] . "\n" . $data[$i]['ALAMAT']));
		$objPHPExcel->getActiveSheet()->setCellValue('D' . $row, ($data[$i]['RT'] . "/" . $data[$i]['RW']));
		$objPHPExcel->getActiveSheet()->setCellValue('E' . $row, (" " . $data[$i]['JML_BNG'] . " "));
		$objPHPExcel->getActiveSheet()->setCellValue('F' . $row, ($data[$i]['LUAS_TANAH'] . "\n" . $data[$i]['LUAS_TNH_BERSAMA']));
		$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ($data[$i]['LUAS_BANGUNAN'] . "\n" . $data[$i]['LUAS_BNG_BERSAMA']));
		$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, (" " . $data[$i]['ZNT'] . " "));
		$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ($data[$i]['KLS_TANAH']));
		$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($data[$i]['KLS_BANGUNAN']));

		$_njopPerMeter = ($data[$i]['LUAS_TANAH'] == 0) ? 0 : ($data[$i]['NJOP_TANAH'] / $data[$i]['LUAS_TANAH']);
		$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, ($_njopPerMeter . "\n" . $NJOPTanahPerMeterBersama));

		$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, ($data[$i]['NJOP_BANGUNAN'] / $data[$i]['LUAS_BANGUNAN'] . "\n" . $NJOPBangunanPerMeterBersama));
		$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, ($data[$i]['NJOP_TANAH'] . "\n" . $data[$i]['NJOP_TNH_BERSAMA']));
		$objPHPExcel->getActiveSheet()->setCellValue('N' . $row, ($data[$i]['NJOP_BANGUNAN'] . "\n" . $data[$i]['NJOP_BNG_BERSAMA']));
		$objPHPExcel->getActiveSheet()->setCellValue('O' . $row, ($data[$i]['NJOP_TANAH'] + $data[$i]['NJOP_BANGUNAN'] . "\n" . $totalNJOPBersama));

		$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
		$row++;
	}

	$objPHPExcel->getActiveSheet()->getStyle("C{$row1}:C{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("F{$row1}:F{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("G{$row1}:G{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("K{$row1}:K{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("L{$row1}:L{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("M{$row1}:M{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("N{$row1}:N{$row}")->getAlignment()->setWrapText(true);
	$objPHPExcel->getActiveSheet()->getStyle("O{$row1}:O{$row}")->getAlignment()->setWrapText(true);

	$objPHPExcel->getActiveSheet()->getStyle("A{$row1}:B{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("C{$row1}:C{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("D{$row1}:E{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("F{$row1}:G{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("H{$row1}:J{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	$objPHPExcel->getActiveSheet()->getStyle("K{$row1}:O{$row}")->applyFromArray(
		array("alignment" => array(
			"horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
			"vertical" => PHPExcel_Style_Alignment::VERTICAL_TOP
		))
	);
	//border list data
	$objPHPExcel->getActiveSheet()->getStyle("A" . ($row1 - 2) . ":O" . ($row - 1))->applyFromArray(
		array('borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		))
	);

	return $row;
}

$numRows = count($data);
$row = 1;
$row = lampiran($row);
$row = judul($row);
$row++;
$row = table_header($row);
$row++;
$row = fetchData();

$objPHPExcel->getActiveSheet()->setCellValue('M' . ($row + 2), ' WALIKOTA ' . strtoupper($appConfig['NAMA_KOTA']));
$objPHPExcel->getActiveSheet()->setCellValue('M' . ($row + 6), ' MUHAMMAD IRWANSYAH');
$objPHPExcel->getActiveSheet()->getStyle("M" . ($row + 2) . ":M" . ($row + 6))->applyFromArray(
	array(
		'font' => array(
			'bold' => true
		)
	)
);

#setup print
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 12);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:O' . ($row + 7));
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(73);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.75);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.20);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.26);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.75);
$objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
$objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
#end setup print

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Klasifikasi Individu');

#$objPHPExcel->getActiveSheet()->getStyle('A12:O13')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
#$objPHPExcel->getActiveSheet()->getStyle('A12:O13')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(21);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(13);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Klasifikasi_individu_' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
