<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);

//date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
//require_once($sRootPath . "inc/payment/constant.php");
//require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
//require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
//require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
//require_once($sRootPath . "inc/central/setting-central.php");
//require_once($sRootPath . "inc/central/user-central.php");
//require_once($sRootPath . "inc/central/dbspec-central.php");
//require_once($sRootPath . "inc/PBB/dbMonitoring.php");


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


$myDBlink = "";

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

function headerMonitoringE2($mod, $nama)
{
    global $appConfig;
    $model = ($mod == 0) ? "KECAMATAN" : strtoupper($_REQUEST['LBL_KEL']);
    $dl = "";
    if ($mod == 0) {
        $dl = $appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA'];
    } else {
        $dl = $nama;
    }
    $html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"15\"><b>{$dl}<b></td></tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" span=\"2\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <col width=\"47\" />
	  <col width=\"48\" />
	  <col width=\"89\" />
	  <col width=\"56\" />
	  <tr>
		<td rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<td rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">KETETAPAN</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN LALU</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI BULAN INI</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">REALISASI S/D BULAN  INI</td>
		<td rowspan=\"2\" width=\"47\" align=\"center\">%</td>
		<td colspan=\"2\" width=\"137\" align=\"center\">SISA     KETETAPAN</td>
		<td rowspan=\"2\" width=\"56\" align=\"center\">SISA  %</td>
	  </tr>
	  <tr>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
	  </tr>
	";
    return $html;
}

// koneksi postgres
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

function convertDate($date, $delimiter = '-')
{
    if ($date == null || $date == '') return '';

    $tmp = explode($delimiter, $date);
    return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}

function getData($where = '')
{
    global $DBLink, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama;

    if ($srcNama != "") $whereClause[] = " TBL.CPM_WP_NAME LIKE '%" . $srcNama . "%' ";
    if ($srcNomor != "") $whereClause[] = " (TBL.CPM_ID LIKE '%" . $srcNomor . "%' OR TBL.CPM_NEW_NOP LIKE '%" . $srcNomor . "%'  OR TBL.CPM_OP_NUMBER LIKE '%" . $srcNomor . "%' ) ";
    if ($srcTglAwal != "") $whereClause[] = " TBL.CPM_DATE_RECEIVE >= '" . convertDate($srcTglAwal) . "' ";
    if ($srcTglAkhir != "") $whereClause[] = " TBL.CPM_DATE_RECEIVE <= '" . convertDate($srcTglAkhir) . "' ";
    $where = "";
    if ($whereClause) $where = " WHERE " . join('AND', $whereClause);

    $query = getQueryDalamProses() . "   
                $where ORDER BY TBL.CPM_DATE_RECEIVE DESC LIMIT 0,10 ";


    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    //	while ($row = mysqli_fetch_assoc($res)) {
    //		//print_r($row);
    //		$return["RP"]=($row["RP"]!="")?$row["RP"]:0;
    //		$return["WP"]=($row["WP"]!="")?$row["WP"]:0;
    //	}

    return $res;
}

function getQueryDalamProses()
{
    return "
        SELECT TBL.*, TKEC.CPC_TKC_KECAMATAN, TKEL.CPC_TKL_KELURAHAN FROM (SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE  , BS.CPM_DATE_RECEIVE , BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, BS.CPM_DATE_APPROVER
        FROM cppmod_pbb_services BS LEFT JOIN 
        cppmod_pbb_service_new_op NEW ON NEW.CPM_NEW_SID = BS.CPM_ID
        WHERE BS.CPM_STATUS IN (4) AND BS.CPM_TYPE IN ('3','4','5','6','7','8')
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE  , BS.CPM_DATE_RECEIVE  , BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, BS.CPM_DATE_APPROVER
        FROM cppmod_pbb_services BS, cppmod_pbb_service_new_op NEW, cppmod_pbb_sppt_susulan SPPT 
        WHERE BS.CPM_STATUS IN (4) AND (BS.CPM_TYPE = '1') AND NEW.CPM_NEW_SID = BS.CPM_ID AND SPPT.CPM_NOP=NEW.CPM_NEW_NOP AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_NEW_NOP, BS.CPM_TYPE  , BS.CPM_DATE_RECEIVE , BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, BS.CPM_DATE_APPROVER
        FROM cppmod_pbb_services BS, cppmod_pbb_service_new_op NEW, cppmod_pbb_sppt_final SPPT
        WHERE BS.CPM_STATUS IN (4) AND (BS.CPM_TYPE = '1') AND NEW.CPM_NEW_SID = BS.CPM_ID AND SPPT.CPM_NOP=NEW.CPM_NEW_NOP AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE  , BS.CPM_DATE_RECEIVE , BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, BS.CPM_DATE_APPROVER 
        FROM cppmod_pbb_services BS, cppmod_pbb_service_split NEW, cppmod_pbb_sppt_susulan SPPT
        WHERE BS.CPM_STATUS IN (4) AND (BS.CPM_TYPE = '2') AND NEW.CPM_SP_NOP = BS.CPM_ID 
        AND SPPT.CPM_NOP=NEW.CPM_SP_NOP AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
        UNION ALL
        SELECT BS.CPM_ID, BS.CPM_WP_NAME, BS.CPM_OP_ADDRESS, BS.CPM_OP_NUMBER, NEW.CPM_SP_NOP AS CPM_NEW_NOP, BS.CPM_TYPE  , BS.CPM_DATE_RECEIVE , BS.CPM_OP_KECAMATAN, BS.CPM_OP_KELURAHAN, BS.CPM_DATE_APPROVER 
        FROM cppmod_pbb_services BS, cppmod_pbb_service_split NEW, cppmod_pbb_sppt_final SPPT
        WHERE BS.CPM_STATUS IN (4) and (BS.CPM_TYPE = '2') AND NEW.CPM_SP_NOP = BS.CPM_ID AND SPPT.CPM_NOP=NEW.CPM_SP_NOP AND SPPT.CPM_SPPT_THN_PENETAPAN!='0'
        ) TBL 
        LEFT JOIN cppmod_tax_kecamatan TKEC ON TKEC.CPC_TKC_ID = TBL.CPM_OP_KECAMATAN 
        LEFT JOIN cppmod_tax_kelurahan TKEL ON TKEL.CPC_TKL_ID = TBL.CPM_OP_KELURAHAN
            ";
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$srcTglAwal  = $q->srcTglAwal;
$srcTglAkhir  = $q->srcTglAkhir;
$srcNomor  = $q->srcNomor;
$srcNama  = $q->srcNama;

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

$objRichText->createText('DATA LAPORAN PAJAK BUMI DAN BANGUNAN ');
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL');
$objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A2:J2');
$objPHPExcel->getActiveSheet()->getStyle('A1:J2')->applyFromArray(
    array(
        'font'    => array('bold' => true, 'size' => $fontSizeHeader),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    )
);

$objPHPExcel->setActiveSheetIndex(0);


$objPHPExcel->getActiveSheet()->getStyle('A1:J2')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->mergeCells('C3:G3');
$objPHPExcel->getActiveSheet()->setCellValue('C4', 'NOP');
$objPHPExcel->getActiveSheet()->setCellValue('D4', 'NAMA');
$objPHPExcel->getActiveSheet()->setCellValue('E4', 'ALAMAT OP');
$objPHPExcel->getActiveSheet()->setCellValue('F4', 'KELURAHAN');
$objPHPExcel->getActiveSheet()->setCellValue('G4', 'KECAMATAN');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL MASUK');
$objPHPExcel->getActiveSheet()->getCell('H3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('H3:H4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL SELESAI');
$objPHPExcel->getActiveSheet()->getCell('I3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I3:I4');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KETERANGAN');
$objPHPExcel->getActiveSheet()->getCell('J3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('J3:J4');

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A3:J4')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

//$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
//$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

while ($row = mysqli_fetch_assoc($result)) {
    $objPHPExcel->getActiveSheet()->getRowDimension(4 + $no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $no), $row['CPM_ID']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $no), " " . $row['CPM_OP_NUMBER']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $row['CPM_WP_NAME']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $no), $row['CPM_OP_ADDRESS']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $no), $row['CPC_TKL_KELURAHAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (4 + $no), $row['CPC_TKC_KECAMATAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (4 + $no), convertDate($row['CPM_DATE_RECEIVE']));
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (4 + $no), convertDate($row['CPM_DATE_APPROVER']));
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (4 + $no), $arrType[$row['CPM_TYPE']]);
    $no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (8 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle('A3:J' . (3 + $no))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
//$objPHPExcel->getActiveSheet()->getStyle('A10:A'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('C10:F'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('G10:G'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('H10:K'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('L10:L'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('M10:N'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('O10:O'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="laporan_pbb.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
