<?php
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

/** PHPExcel */
require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
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

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$nkc = @isset($_REQUEST['nkc']) ? $_REQUEST['nkc'] : "";
/* $jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na = @isset($_REQUEST['na']) ? str_replace("%20"," ",$_REQUEST['na']) : ""; */

$nmFile = "Data-WP-Pengurangan";

$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$export = @isset($_REQUEST['exp']) ? $_REQUEST['exp'] : "";
$kelurahan = "";
if ($q == "") exit(1);
$q = base64_decode($q);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;

$User                              = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig                          = $User->GetAppConfig($area);

if ($nkc != "Pilih Semua") {
    $kcm = "Kecamatan " . $nkc;
} else {
    $kcm = ucfirst(strtolower($appConfig['C_KABKOT'])) . " " . $appConfig['kota'];
}

$arrWhere = array();
if ($kecamatan != "") {
    if ($kelurahan != "") array_push($arrWhere, "A.CPM_OP_NUMBER like '{$kelurahan}%'");
    else array_push($arrWhere, "A.CPM_OP_NUMBER like '{$kecamatan}%'");
}

if ($arrWhere != NULL) {
    $where = " AND ";
    $where .= implode(" AND ", $arrWhere);
}

if (stillInSession($DBLink, $json, $sdata)) {
    $result = getData($where);
} else {
    echo  "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}

function getData($where)
{
    global $DBLink;

    $result = array();
    $query = "SELECT A.CPM_OP_NUMBER,A.CPM_WP_NAME,A.CPM_OP_ADDRESS,A.CPM_SPPT_DUE,B.CPM_RE_PERCENT_APPROVE, B.CPM_RE_ARGUEMENT, A.CPM_DATE_RECEIVE, A.CPM_DATE_APPROVER, C.CPC_TKC_KECAMATAN AS KECAMATAN,D.CPC_TKL_KELURAHAN AS KELURAHAN,  REPLACE((FORMAT((A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE*(B.CPM_RE_PERCENT_APPROVE/100))),0)), ',','') AS JUMLAH_BAYAR, 
                    REPLACE((FORMAT((A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE - (A.CPM_SPPT_DUE*(B.CPM_RE_PERCENT_APPROVE/100)))),0)), ',','') AS PENGURANGAN 
                    FROM cppmod_pbb_services A 
                    JOIN cppmod_pbb_service_reduce B 
                    LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN=C.CPC_TKC_ID
                    LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN=D.CPC_TKL_ID WHERE A.CPM_ID = B.CPM_RE_SID " . $where;
    //echo $query;exit;
    $res = mysqli_query($DBLink, $query);
    if ($res) {
        $result["result"] = "true";
        $result["data"] = $res;
    } else {
        $result["result"] = "false";
        $result["data"] = mysql_error;
    }
    return $result;
}

function getKecamatanNama($kode)
{
    global $DBLink;
    $query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
    $res   = mysqli_query($DBLink, $query);
    $row   = mysqli_fetch_array($res);
    return $row['CPC_TKC_KECAMATAN'];
}
function getKelurahanNama($kode)
{
    global $DBLink;
    $query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
    $res   = mysqli_query($DBLink, $query);
    $row   = mysqli_fetch_array($res);
    return $row['CPC_TKL_KELURAHAN'];
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("Alfa System")
    ->setSubject("Alfa System pbb")
    ->setDescription("pbb")
    ->setKeywords("Alfa System");
//COP
$objPHPExcel->getActiveSheet()->mergeCells('A2:K2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:K3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'DAFTAR WAJIB PAJAK YANG MENGAJUKAN PENGURANGAN');
if ($nkc != "Pilih Semua") {
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'KECAMATAN : ' . $nkc);
} else {
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'KABUPATEN/KOTA : ' . $appConfig['NAMA_KOTA']);
}
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'NO.')
    ->setCellValue('B5', 'NOP')
    ->setCellValue('C5', 'NAMA')
    ->setCellValue('D5', 'ALAMAT WP')
    ->setCellValue('E5', 'KECAMATAN')
    ->setCellValue('F5', strtoupper($appConfig['LABEL_KELURAHAN']))
    ->setCellValue('G5', "PBB TERHUTANG\nSEBELUM PENGURANGAN\n(RP)")
    ->setCellValue('H5', "PENGURANGAN \n(%)")
    ->setCellValue('I5', "PENGURANGAN \n(RP)")
    ->setCellValue('J5', "PBB TERHUTANG\nSETELAH PENGURANGAN\n(RP)")
    ->setCellValue('K5', "TANGGAL MASUK")
    ->setCellValue('L5', "TANGGAL SELESAI")
    ->setCellValue('M5', 'ALASAN PENGURANGAN');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$sumRows = mysqli_num_rows($result['data']);
$summary = array('name' => 'JUMLAH', 'percent' => 0, 'ketetapan_awal' => 0, 'ketetapan_disetujui' => 0);
while ($rowData = mysqli_fetch_assoc($result['data'])) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 5));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['CPM_OP_NUMBER'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['CPM_WP_NAME']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_OP_ADDRESS']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['KECAMATAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['KELURAHAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_SPPT_DUE']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_RE_PERCENT_APPROVE']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['PENGURANGAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $rowData['JUMLAH_BAYAR']);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_DATE_RECEIVE']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_DATE_APPROVER']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, str_replace('#', ', ', $rowData['CPM_RE_ARGUEMENT']));
    $row++;
    $i++;

    $summary['percent']               += $rowData['CPM_RE_PERCENT_APPROVE'];
    $summary['ketetapan_awal']           += $rowData['CPM_SPPT_DUE'];
    $summary['pengurangan']           += $rowData['PENGURANGAN'];
    $summary['ketetapan_disetujui']  += $rowData['JUMLAH_BAYAR'];
}

//JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'F' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'JUMLAH');
$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $summary['ketetapan_awal']);
$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $summary['percent']);
$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $summary['pengurangan']);
$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $summary['ketetapan_disetujui']);
$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->applyFromArray(
    array(
        'font'    => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray(
    array(
        'font'    => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray(
    array(
        'font'    => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar Pengurangan');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:M5')->applyFromArray(
    array(
        'font'    => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//border header table
$objPHPExcel->getActiveSheet()->getStyle('A5:M' . ($sumRows + 6))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:M5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:M5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A6:A' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('B6:B' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('E6:E' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('F6:F' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('G6:G' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('H6:H' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('I6:I' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('J6:J' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('K6:K' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('L6:L' . ($sumRows + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('M6:M' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('G5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('H5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('I5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('J5')->getAlignment()->setWrapText(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Daftar Pengurangan ' . $kcm . ' ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
