<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/json.php");


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


$arrType = array(
    1 => "OP Baru",
    2 => "Pemecahan",
    3 => "Penggabungan",
    4 => "Mutasi",
    5 => "Perubahan Data",
    6 => "Pembatalan",
    7 => "Duplikat",
    8 => "Penghapusan",
    9 => "Pengurangan",
    10 => "Keberatan"
);


function closeMysql($con)
{
    mysqli_close($con);
}

function convertDate($date, $delimiter = '-')
{
    if ($date == null || $date == '') return '';

    $tmp = explode($delimiter, $date);
    return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}

function getData($where = '')
{
    global $DBLink, $srcTglAwal, $srcTglAkhir, $srcNama, $srcNomor, $srcTahun;

    $whereClause = array();
    $where = " ";

    if ($srcTglAwal != "") $whereClause[] = " CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
    if ($srcTglAkhir != "") $whereClause[] = " CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";
    if ($srcNama != "") $whereClause[] = " CPM_WP_NAME LIKE '%" . $srcNama . "%' ";
    if ($srcNomor != "") $whereClause[] = " (CPM_ID LIKE '%" . $srcNomor . "%' OR CPM_NEW_NOP LIKE '%" . $srcNomor . "%'  OR CPM_OP_NUMBER LIKE '%" . $srcNomor . "%' ) ";
    if ($srcTahun != "") $whereClause[] = " CPM_SPPT_YEAR = '" . $srcTahun . "' ";

    if ($whereClause) $where = " WHERE " . join('AND', $whereClause);

    $query = "SELECT * FROM (
                SELECT
                        BS.CPM_ID,
                        BS.CPM_WP_NAME,
                        BS.CPM_OP_ADDRESS,
                        BS.CPM_OP_NUMBER,
                        NULL as CPM_NEW_NOP,
                        BS.CPM_TYPE,
                        BS.CPM_STATUS,
                        BS.CPM_DATE_RECEIVE,
                        BS.CPM_RECEIVER,
                        TKEL.CPC_TKL_KELURAHAN,
                        CUR.OP_LUAS_BUMI,
                        CUR.OP_LUAS_BANGUNAN,
                        CUR.SPPT_PBB_HARUS_DIBAYAR,
                        BS.CPM_REPRESENTATIVE,
                        BS.CPM_DATE_APPROVER, BS.CPM_SPPT_YEAR
                FROM
                        cppmod_pbb_services BS
                LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN
                LEFT JOIN cppmod_pbb_sppt_current CUR ON CUR.NOP=BS.CPM_OP_NUMBER
                WHERE
                        BS.CPM_STATUS = '4'
                AND (
                        BS.CPM_TYPE != '1'
                        AND BS.CPM_TYPE != '2'
                )
                
                UNION ALL
                SELECT
		BS.CPM_ID,
		BS.CPM_WP_NAME,
                        BS.CPM_OP_ADDRESS,
		BS.CPM_OP_NUMBER,
		NEW.CPM_NEW_NOP,
		BS.CPM_TYPE,
		BS.CPM_STATUS,
		BS.CPM_DATE_RECEIVE,
		BS.CPM_RECEIVER,
		TKEL.CPC_TKL_KELURAHAN,
		CUR.OP_LUAS_BUMI,
		CUR.OP_LUAS_BANGUNAN,
		CUR.SPPT_PBB_HARUS_DIBAYAR,
		BS.CPM_REPRESENTATIVE,
		BS.CPM_DATE_APPROVER, BS.CPM_SPPT_YEAR
	FROM
		cppmod_pbb_services BS
	LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN
	LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID
	JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP = NEW.CPM_NEW_NOP
	LEFT JOIN cppmod_pbb_sppt_current CUR ON CUR.NOP=SPPT.CPM_NOP
	WHERE
		BS.CPM_STATUS ='4'
	AND (BS.CPM_TYPE = '1')
	AND SPPT.CPM_SPPT_THN_PENETAPAN != '0'
                
                UNION ALL
                SELECT
		BS.CPM_ID,
		BS.CPM_WP_NAME,
                        BS.CPM_OP_ADDRESS,
		BS.CPM_OP_NUMBER,
		NEW.CPM_NEW_NOP,
		BS.CPM_TYPE,
		BS.CPM_STATUS,
		BS.CPM_DATE_RECEIVE,
		BS.CPM_RECEIVER,
		TKEL.CPC_TKL_KELURAHAN,
		CUR.OP_LUAS_BUMI,
		CUR.OP_LUAS_BANGUNAN,
		CUR.SPPT_PBB_HARUS_DIBAYAR,
		BS.CPM_REPRESENTATIVE,
		BS.CPM_DATE_APPROVER, BS.CPM_SPPT_YEAR
	FROM
		cppmod_pbb_services BS
	LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN
	LEFT JOIN cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID
	JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP = NEW.CPM_NEW_NOP
	LEFT JOIN cppmod_pbb_sppt_current CUR ON CUR.NOP=SPPT.CPM_NOP
	WHERE
		BS.CPM_STATUS ='4'
	AND (BS.CPM_TYPE = '1')
	AND SPPT.CPM_SPPT_THN_PENETAPAN != '0'
        UNION ALL
        
        SELECT
				BS.CPM_ID,
				BS.CPM_WP_NAME,
                        BS.CPM_OP_ADDRESS,
				BS.CPM_OP_NUMBER,
				NEW.CPM_SP_NOP AS CPM_NEW_NOP,
				BS.CPM_TYPE,
				BS.CPM_STATUS,
				BS.CPM_DATE_RECEIVE,
				BS.CPM_RECEIVER,
				TKEL.CPC_TKL_KELURAHAN,
				CUR.OP_LUAS_BUMI,
				CUR.OP_LUAS_BANGUNAN,
				CUR.SPPT_PBB_HARUS_DIBAYAR,
				BS.CPM_REPRESENTATIVE,
				BS.CPM_DATE_APPROVER, BS.CPM_SPPT_YEAR
			FROM
				cppmod_pbb_services BS
			LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN
			LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID
			JOIN cppmod_pbb_sppt_susulan SPPT ON SPPT.CPM_NOP = NEW.CPM_SP_NOP
			LEFT JOIN cppmod_pbb_sppt_current CUR ON CUR.NOP=SPPT.CPM_NOP
			WHERE
				BS.CPM_STATUS ='4'
			AND (BS.CPM_TYPE = '2')
			AND SPPT.CPM_SPPT_THN_PENETAPAN != '0'
        UNION ALL
        
        SELECT
				BS.CPM_ID,
				BS.CPM_WP_NAME,
                        BS.CPM_OP_ADDRESS,
				BS.CPM_OP_NUMBER,
				NEW.CPM_SP_NOP AS CPM_NEW_NOP,
				BS.CPM_TYPE,
				BS.CPM_STATUS,
				BS.CPM_DATE_RECEIVE,
				BS.CPM_RECEIVER,
				TKEL.CPC_TKL_KELURAHAN,
				CUR.OP_LUAS_BUMI,
				CUR.OP_LUAS_BANGUNAN,
				CUR.SPPT_PBB_HARUS_DIBAYAR,
				BS.CPM_REPRESENTATIVE,
				BS.CPM_DATE_APPROVER, BS.CPM_SPPT_YEAR
			FROM
				cppmod_pbb_services BS
			LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN
			LEFT JOIN cppmod_pbb_service_split NEW ON NEW.CPM_SP_NOP = BS.CPM_ID
			JOIN cppmod_pbb_sppt_final SPPT ON SPPT.CPM_NOP = NEW.CPM_SP_NOP
			LEFT JOIN cppmod_pbb_sppt_current CUR ON CUR.NOP=SPPT.CPM_NOP
			WHERE
				BS.CPM_STATUS ='4'
			AND (BS.CPM_TYPE = '2')
			AND SPPT.CPM_SPPT_THN_PENETAPAN != '0'
        ) REKAP 
            $where ORDER BY CPM_DATE_RECEIVE DESC ";
    //echo $query;exit;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    return $res;
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

// print_r($_REQUEST); exit;

$srcTglAwal      = $_REQUEST['srcTglAwal'];
$srcTglAkhir      = $_REQUEST['srcTglAkhir'];
$srcNama        = $_REQUEST['srcNama'];
$srcNomor       = $_REQUEST['srcNomor'];
$srcTahun       = $_REQUEST['srcTahun'];

$result = getData();

$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.8);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.5);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(false);

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("Alfa System")
    ->setSubject("Alfa System pbb")
    ->setDescription("pbb")
    ->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();

$objRichText->createText('DATA LAPORAN PAJAK BUMI DAN BANGUNAN TAHUN');
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:N1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL');
$objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A2:N2');
$objPHPExcel->getActiveSheet()->getStyle('A1:N2')->applyFromArray(
    array(
        'font'    => array('bold' => true, 'size' => $fontSizeHeader),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    )
);

$objPHPExcel->setActiveSheetIndex(0);


$objPHPExcel->getActiveSheet()->getStyle('A1:N2')->applyFromArray(
    array('font'    => array('size' => $fontSizeHeader))
);

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A3:A4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NOMOR PELAYANAN');
$objPHPExcel->getActiveSheet()->getCell('B3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B3:B4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('DATA SPPT PBB');
$objPHPExcel->getActiveSheet()->getCell('C3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C3:H3');
$objPHPExcel->getActiveSheet()->setCellValue('C4', 'NOP');
$objPHPExcel->getActiveSheet()->setCellValue('D4', 'NAMA');
$objPHPExcel->getActiveSheet()->setCellValue('E4', 'ALAMAT OP');
$objPHPExcel->getActiveSheet()->setCellValue('F4', 'KELURAHAN');
$objPHPExcel->getActiveSheet()->setCellValue('G4', 'L.BUMI/M2');
$objPHPExcel->getActiveSheet()->setCellValue('H4', 'L.BANGUNAN/M2');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('PAJAK TERUTANG');
$objPHPExcel->getActiveSheet()->getCell('I3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I3:I4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL MASUK');
$objPHPExcel->getActiveSheet()->getCell('J3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J3:J4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL SELESAI');
$objPHPExcel->getActiveSheet()->getCell('K3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('K3:K4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('LAMA PROSES (Hari)');
$objPHPExcel->getActiveSheet()->getCell('L3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('L3:L4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NAMA KUASA');
$objPHPExcel->getActiveSheet()->getCell('M3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M3:M4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KETERANGAN');
$objPHPExcel->getActiveSheet()->getCell('N3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('N3:N4');
$objPHPExcel->getActiveSheet()->getStyle('L3:L4')
    ->getAlignment()->setWrapText(true);
// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A3:N4')->applyFromArray(
    array(
        'font'    => array(
            'size' => $fontSizeHeader
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(17);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);

$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

while ($row = mysqli_fetch_assoc($result)) {
    $datetime1 = date_create($row['CPM_DATE_RECEIVE']);
    $datetime2 = date_create(convertDate($row['CPM_DATE_APPROVER']));
    $interval = date_diff($datetime1, $datetime2);
    //echo $interval->format('%R%a days');
    $objPHPExcel->getActiveSheet()->getRowDimension(4 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $no), $row['CPM_ID']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $no), " " . $row['CPM_OP_NUMBER']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $row['CPM_WP_NAME']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $no), $row['CPM_OP_ADDRESS']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $no), $row['CPC_TKL_KELURAHAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (4 + $no), $row['OP_LUAS_BUMI']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (4 + $no), $row['OP_LUAS_BANGUNAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (4 + $no), $row['SPPT_PBB_HARUS_DIBAYAR']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (4 + $no), convertDate($row['CPM_DATE_RECEIVE']));
    $objPHPExcel->getActiveSheet()->setCellValue('K' . (4 + $no), $row['CPM_DATE_APPROVER']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . (4 + $no), $interval->format('%a'));
    $objPHPExcel->getActiveSheet()->setCellValue('M' . (4 + $no), $row['CPM_REPRESENTATIVE']);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . (4 + $no), $arrType[$row['CPM_TYPE']]);
    $no++;
}
//$objPHPExcel->getActiveSheet()->setCellValue('A'.(8+$no), '');
$objPHPExcel->getActiveSheet()->getStyle('A3:N' . (3 + $no))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="laporan_rekap.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
