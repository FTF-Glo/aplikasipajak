<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_tanah', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
//require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
//require_once($sRootPath . "inc/central/setting-central.php");
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

ini_set('display_errors', 1);


$myDBlink = "";

function openMysql()
{
	global $appConfig;
	$host = $appConfig['GW_DBHOST'];
	$port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
	$user = $appConfig['GW_DBUSER'];
	$pass = $appConfig['GW_DBPWD'];
	$dbname = $appConfig['GW_DBNAME'];
	$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		//echo mysqli_error($myDBLink);
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con)
{
	mysqli_close($con);
}

function getKodeKec($nama)
{
	global $DBLink;
	$nama = trim($nama);

	$query = "SELECT CPC_TKC_ID FROM cppmod_tax_kecamatan WHERE CPC_TKC_KECAMATAN = '$nama' ";

	$res = mysqli_query($DBLink, $query);
	$row = mysqli_fetch_assoc($res);
	// echo $query;
	// print_r($row);
	// exit;
	$row = $row['CPC_TKC_ID'];
	$row = substr($row, 4, 3);
	return $nama . " - " . $row;
}
function getKodeKel($nama)
{
	global $DBLink;
	$nama = trim($nama);

	$query = "SELECT CPC_TKL_ID FROM cppmod_tax_kelurahan WHERE CPC_TKL_KELURAHAN = '$nama' ";
	$res = mysqli_query($DBLink, $query);
	$row = mysqli_fetch_assoc($res);
	$row = $row['CPC_TKL_ID'];
	$row = substr($row, 7, 3);
	// echo $query;
	// exit;
	return $nama . " - " . $row;
	// exit;
}


function getListZNT($kel, $thn2)
{
	global $DBLink;
	$return = array();

	$queryKelas = "SELECT CPM_KELAS, CPM_NILAI_BAWAH, CPM_NILAI_ATAS, CPM_NJOP_M2 FROM cppmod_pbb_kelas_bumi WHERE CPM_KELAS <> 'XXX' ORDER BY CPM_KELAS";
	$res = mysqli_query($DBLink, $queryKelas);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$dataKelas = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$dataKelas[$i]["CPM_KELAS"]             = $row["CPM_KELAS"];
		$dataKelas[$i]["CPM_NILAI_BAWAH"]       = $row["CPM_NILAI_BAWAH"] * 1000;
		$dataKelas[$i]["CPM_NILAI_ATAS"]        = $row["CPM_NILAI_ATAS"] * 1000;
		$dataKelas[$i]["CPM_NJOP_M2"]           = $row["CPM_NJOP_M2"] * 1000;
		$i++;
	}
	$tahun = "";
	if ($thn2 != "") {
		$tahun = " AND B.CPM_TAHUN='$thn2' ";
	}

	$queryZNT = "SELECT
						SUBSTR(A.CPM_NOP, 11, 3) AS KD_BLOK,
						D.CPM_OP_JALAN AS CPM_OP_ALAMAT,
						A.CPM_OT_ZONA_NILAI AS CPM_KODE_ZNT,
						B.CPM_NIR,
						C.CPM_NJOP_M2 AS NIR_BUMI,
						B.CPM_TAHUN AS CPM_TAHUN
				FROM
						cppmod_pbb_sppt_final A
				LEFT JOIN cppmod_pbb_znt B ON A.CPM_OT_ZONA_NILAI = B.CPM_KODE_ZNT
				AND A.CPM_OP_KELURAHAN = B.CPM_KODE_LOKASI
				LEFT JOIN cppmod_pbb_kelas_bumi C ON rpad(C.CPM_KELAS, 3, ' ') = rpad(A.CPM_OT_ZONA_NILAI, 3, ' ')
				LEFT JOIN cppmod_pbb_jalan D ON A.CPM_NOP = D.CPM_NOP
				WHERE
						A.CPM_OP_KELURAHAN = '" . $kel . "' 
						$tahun
				GROUP BY
						SUBSTR(A.CPM_NOP, 11, 3),
						D.CPM_OP_JALAN,
						A.CPM_OT_ZONA_NILAI
				ORDER BY
						SUBSTR(A.CPM_NOP, 11, 3),
						D.CPM_OP_JALAN,
						A.CPM_OT_ZONA_NILAI";

	//echo $queryZNT;exit;
	$res = mysqli_query($DBLink, $queryZNT);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$data = array();
	$i = 0;
	$SubKelas = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["CPM_KODE_ZNT"]           = $row["CPM_KODE_ZNT"];
		$data[$i]["KD_BLOK"]                = $row["KD_BLOK"];
		$data[$i]["CPM_OP_ALAMAT"]          = $row["CPM_OP_ALAMAT"];
		$data[$i]["CPM_TAHUN"]              = $row["CPM_TAHUN"];
		if (in_array(substr($row["CPM_KODE_ZNT"], 0, 1), $SubKelas)) {
			$nir = $row["NIR_BUMI"] * 1000;
		} else {
			$nir = $row["CPM_NIR"] * 1000;
		}
		$find = false;
		$idx = 0;

		while (!$find && $idx < count($dataKelas)) {
			if ($nir >= $dataKelas[$idx]["CPM_NILAI_BAWAH"] && $nir <= $dataKelas[$idx]["CPM_NILAI_ATAS"]) {
				$data[$i]["CPM_KELAS"]              = $dataKelas[$idx]["CPM_KELAS"];
				$data[$i]["CPM_NILAI_BAWAH"]        = $dataKelas[$idx]["CPM_NILAI_BAWAH"];
				$data[$i]["CPM_NILAI_ATAS"]         = $dataKelas[$idx]["CPM_NILAI_ATAS"];
				$data[$i]["CPM_NJOP_M2"]            = $dataKelas[$idx]["CPM_NJOP_M2"];
			}
			$idx++;
		}

		$i++;
	}

	return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;

$namakelurahan  = @isset($_REQUEST['nkel']) ? $_REQUEST['nkel'] : "";
$namakecamatan  = @isset($_REQUEST['nkec']) ? $_REQUEST['nkec'] : "";
$thn            = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namakota       = @isset($_REQUEST['kota']) ? $_REQUEST['kota'] : "";
$kel            = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$thn2            = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";

// print_r($_REQUEST);
// exit;
$data = getListZNT($kel, $thn2);

$c = count($data);

$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getDefaultStyle()->getFont()->setName('bookmanoldstyle');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A7:J7')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle("A7:J7")->applyFromArray(
	array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	)
);
// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
	->setLastModifiedBy("vpost")
	->setTitle("Alfa System")
	->setSubject("Alfa System pbb")
	->setDescription("pbb")
	->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();
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

//function lampiran($row){
//global $objPHPExcel, $namakota;
//$row1 = $row;
//$row4 = $row+4;

//hilangkan
#1
//$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:J{$row}");
//$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " LAMPIRAN 1  : KEPUTUSAN KABUPATEN ".$namakota);
//$row++;#2
//$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:J{$row}");
//$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " ");
//$row++;#3
//$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:J{$row}");
//$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " NOMOR 	 : 188.45/     /4.4.2.1/2018");
//$row++;#4
//$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:J{$row}");
//$objPHPExcel->getActiveSheet()->setCellValue("F{$row}", " TANGGAL :  ".bulan(date("m"))." ".date("Y"));
//$row++;#5
//$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:J{$row}");
//$row++;#6

//$objPHPExcel->getActiveSheet()->getStyle("F{$row1}:F{$row4}")->applyFromArray(
//	array(
//		'font' => array(
//			'bold' => true
//		)
//	)
//);
//return $row;
//}

function judul($row)
{
	global $objPHPExcel, $objRichText, $namakota, $namakecamatan, $namakelurahan, $thn, $fontSizeHeader;
	#1
	$objRichText->createText("PENETAPAN NJOP PERMUKAAN BUMI BERUPA TANAH TAHUN " . $thn);
	$objPHPExcel->getActiveSheet()->getStyle("A{$row}")->applyFromArray(
		array(
			'font'    => array(
				'size' => $fontSizeHeader
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		)
	);
	$objPHPExcel->getActiveSheet()->getCell("A{$row}")->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:I{$row}");
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("PROPINSI");
	$row += 2; #3
	$objPHPExcel->getActiveSheet()->getCell("A{$row}")->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells("A{$row}:B{$row}");

	$objRichText = new PHPExcel_RichText();
	$objRichText->createText(": LAMPUNG - 18");
	$objPHPExcel->getActiveSheet()->getCell("C{$row}")->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells("C{$row}:D{$row}");
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("KABUPATEN");

	$row++; #4
	$objPHPExcel->getActiveSheet()->getCell("A{$row}")->setValue($objRichText);
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText(": $namakota - 01");
	$objPHPExcel->getActiveSheet()->getCell("C{$row}")->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells("C{$row}:D{$row}");


	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("KECAMATAN");
	$objPHPExcel->getActiveSheet()->getCell("E3")->setValue($objRichText);

	$namakodekecamatan = getKodeKec($namakecamatan);
	// $namakodekecamatan = $namakodekecamatan;
	// echo $namakodekecamatan;
	// exit;
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText(": $namakodekecamatan");
	$objPHPExcel->getActiveSheet()->getCell("F3")->setValue($objRichText);


	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("KELURAHAN");
	$objPHPExcel->getActiveSheet()->getCell("E4")->setValue($objRichText);


	$namakodekelurahan = getKodeKel($namakelurahan);
	// $namakodekelurahan = $namakodekelurahan;

	$objRichText = new PHPExcel_RichText();
	$objRichText->createText(": $namakodekelurahan");
	$objPHPExcel->getActiveSheet()->getCell("F4")->setValue($objRichText);




	$row++;

	//// Header Of Table
	$row += 2; #7
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("NO");
	$objPHPExcel->getActiveSheet()->getCell("A{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("A{$row}:A".($row+1));
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("BLOK");
	$objPHPExcel->getActiveSheet()->getCell("B{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("B{$row}:B".($row+1));
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("NAMA JALAN");
	$objPHPExcel->getActiveSheet()->getCell("C{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("C{$row}:C".($row+1));
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("KODE ZNT");
	$objPHPExcel->getActiveSheet()->getCell("D{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("D{$row}:D".($row+1));
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("KELAS BUMI");
	$objPHPExcel->getActiveSheet()->getCell("E{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("E{$row}:E".($row+1));
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("PENGGOLONGAN  NILAI JUAL BUMI (Rupiah/M2)");
	$objPHPExcel->getActiveSheet()->getCell("F{$row}")->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells("F{$row}:H{$row}");

	$objRichText = new PHPExcel_RichText();
	$objRichText->createText(" KET NILAI JUAL OBJEK PAJAK BUMI (Rupiah/M2) ");
	$objPHPExcel->getActiveSheet()->getCell("I{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("H{$row}:H".($row+1));
	//hilang in
	//$objRichText = new PHPExcel_RichText();
	//$objRichText->createText("TAHUN");
	//$objPHPExcel->getActiveSheet()->getCell("J{$row}")->setValue($objRichText);
	// $objPHPExcel->getActiveSheet()->mergeCells("I{$row}:J".($row+1));
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("NILAI BAWAH");
	$row++;
	$objPHPExcel->getActiveSheet()->getCell("F{$row}")->setValue($objRichText);
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("NILAI ATAS");
	$objPHPExcel->getActiveSheet()->getCell("G{$row}")->setValue($objRichText);
	return $row;
}

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Klasifikasi ZNT');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getRowDimension('7')->setRowHeight(70);

$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
//hilang in
//$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(18);
$no = 1;
$row = 1;
//$row = lampiran($row);
$row = judul($row);

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

for ($i = 0; $i < $c; $i++) {
	$objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(18);
	$objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($i + 1))->getStyle('A' . ($row))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), "'" . $data[$i]["KD_BLOK"])->getStyle('B' . ($row))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[$i]["CPM_OP_ALAMAT"])->getStyle('C' . ($row))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[$i]["CPM_KODE_ZNT"])->getStyle('D' . ($row))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[$i]["CPM_KELAS"])->getStyle('E' . ($row))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $data[$i]["CPM_NILAI_BAWAH"])->getStyle('F' . ($row))->applyFromArray($noBold);

	$objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), " S/D ")->getStyle('G' . ($row))->applyFromArray($noBold);

	$objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $data[$i]["CPM_NILAI_ATAS"])->getStyle('H' . ($row))->applyFromArray($noBold);
	$objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[$i]["CPM_NJOP_M2"])->getStyle('H' . ($row))->applyFromArray($noBold);
	//hilang in
	//$objPHPExcel->getActiveSheet()->setCellValue('J'.($row), $data[$i]["CPM_TAHUN"])->getStyle('I'.($row))->applyFromArray($noBold);
	$row++;
}

$objPHPExcel->getActiveSheet()->getStyle('A7:I' . (7 + count($data)))->applyFromArray(
	array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);

#setup print
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 13);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:I' . (3 + count($data)));
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(80);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.59);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.00);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.21);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.32);
$objPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
$objPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
#end setup print


//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="KLASIFIKASI_ZNT_' . $namakecamatan . '-' . $namakelurahan . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
