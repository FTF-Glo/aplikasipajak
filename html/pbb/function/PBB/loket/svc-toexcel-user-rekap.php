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

function encode_for_slug($string)
{
    $replace  = str_replace(' ', '-', $string);
    $replace  = str_replace(".", "-",$replace);
    $replace  = str_replace("&", "-",$replace);
    $replace  = str_replace(" ", "-",$replace);
    $replace  = str_replace("  ", "-",$replace);
    $replace  = str_replace("   ", "-",$replace);
    $replace  = str_replace("$", "-",$replace);
    $replace  = str_replace("+", "-",$replace);
    $replace  = str_replace("! ", "-",$replace);
    $replace  = str_replace("@", "-",$replace);
    $replace  = str_replace("#", "-",$replace);
    $replace  = str_replace("$", "-",$replace);
    $replace  = str_replace("%", "-",$replace);
    $replace  = str_replace("^", "-",$replace);
    $replace  = str_replace("&", "-",$replace);
    $replace  = str_replace("*", "-",$replace);
    $replace  = str_replace("(", "-",$replace);
    $replace  = str_replace(")", "-",$replace);
    $replace  = str_replace("/", "-",$replace);
    $replace  = str_replace("+", "-",$replace);
    $replace  = preg_replace('/[^A-Za-z0-9\-]/', '', $replace);
    $replace  = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $replace);
    $replace = preg_replace('/-+/', '-', $replace);

    if(substr($replace, -1) == '-')
    {
        $replace = substr_replace($replace,'',-1);
    }

    return strtolower($replace);
}

function getData($where = '')
{
    global $DBLink, $srcTglAwal, $srcTglAkhir, $srcNama;

    $srcNama = trim($srcNama);
    $wherenama = '';
    if ($srcNama && $srcNama!="") $wherenama = " AND CPM_RECEIVER LIKE '$srcNama%' ";
    $TglAwal = convertDate($srcTglAwal);
    $TglAkhir = convertDate($srcTglAkhir);

    $query="SELECT 
                CPM_RECEIVER AS PENERIMA,
                CONCAT('$srcTglAwal',' - ','$srcTglAkhir') AS TANGGAL,
                CPM_TYPE AS TIPE,
                IF(CPM_APPROVER IS NULL, 0, 1) AS APPROV
            FROM sw_pbb.cppmod_pbb_services 
            WHERE
                DATE(CPM_DATE_RECEIVE) >= '$TglAwal' AND
                DATE(CPM_DATE_RECEIVE) <= '$TglAkhir' 
                $wherenama 
            ORDER BY CPM_RECEIVER, CPM_TYPE";

    // print_r($query); exit;

    $res = mysqli_query($DBLink, $query);
    
    if ($res === false) return false;

    $data = [];
    while($row = mysqli_fetch_object($res)) $data[] = $row;

    /// pengelompokan dulu
    $tempData = [];
    foreach ($data as $row) {
        $penerima = encode_for_slug($row->PENERIMA);
        $tipe = $row->TIPE;
        $stts = $row->APPROV;
        // unset($row->TIPE);
        // unset($row->APPROV);
        $tempData[$penerima][$tipe][$stts][] = $row;
    }
    ksort($tempData);
    ///==========================================

    /// format data dan hitung status
    $dataUrut = [];
    foreach ($tempData as $rows) {
        $penerima = '';
        $tanggal = '';
        $jmltotal = 0;

        $op_baru_0 = 0;
        $op_baru_1 = 0;

        $pecah_0 = 0;
        $pecah_1 = 0;

        $gabung_0 = 0;
        $gabung_1 = 0;

        $mutasi_0 = 0;
        $mutasi_1 = 0;

        $ubah_0 = 0;
        $ubah_1 = 0;

        $batal_0 = 0;
        $batal_1 = 0;

        $duplikat_0 = 0;
        $duplikat_1 = 0;

        $hapus_0 = 0;
        $hapus_1 = 0;

        $pengurangan_0 = 0;
        $pengurangan_1 = 0;

        $keberatan_0 = 0;
        $keberatan_1 = 0;

        $cetak_0 = 0;
        $cetak_1 = 0;

        foreach ($rows as $tipe) {
            foreach ($tipe as $stts) {
                foreach ($stts as $r) {
                    $penerima = $r->PENERIMA;
                    $tanggal = $r->TANGGAL;
                    $jmltotal++;

                    if($r->TIPE=='1' && $r->APPROV=='0') $op_baru_0++;
                    if($r->TIPE=='1' && $r->APPROV=='1') $op_baru_1++;
                    
                    if($r->TIPE=='2' && $r->APPROV=='0') $pecah_0++;
                    if($r->TIPE=='2' && $r->APPROV=='1') $pecah_1++;

                    if($r->TIPE=='3' && $r->APPROV=='0') $gabung_0++;
                    if($r->TIPE=='3' && $r->APPROV=='1') $gabung_1++;
                    
                    if($r->TIPE=='4' && $r->APPROV=='0') $mutasi_0++;
                    if($r->TIPE=='4' && $r->APPROV=='1') $mutasi_1++;
                    
                    if($r->TIPE=='5' && $r->APPROV=='0') $ubah_0++;
                    if($r->TIPE=='5' && $r->APPROV=='1') $ubah_1++;
                    
                    if($r->TIPE=='6' && $r->APPROV=='0') $batal_0++;
                    if($r->TIPE=='6' && $r->APPROV=='1') $batal_1++;
                    
                    if($r->TIPE=='7' && $r->APPROV=='0') $duplikat_0++;
                    if($r->TIPE=='7' && $r->APPROV=='1') $duplikat_1++;
                    
                    if($r->TIPE=='8' && $r->APPROV=='0') $hapus_0++;
                    if($r->TIPE=='8' && $r->APPROV=='1') $hapus_1++;
                    
                    if($r->TIPE=='9' && $r->APPROV=='0') $pengurangan_0++;
                    if($r->TIPE=='9' && $r->APPROV=='1') $pengurangan_1++;
                    
                    if($r->TIPE=='10' && $r->APPROV=='0') $keberatan_0++;
                    if($r->TIPE=='10' && $r->APPROV=='1') $keberatan_1++;
                    
                    if($r->TIPE=='11' && $r->APPROV=='0') $cetak_0++;
                    if($r->TIPE=='11' && $r->APPROV=='1') $cetak_1++;
                }
            }
        }

        $obj                = (object)[];
        $obj->PENERIMA      = $penerima;
        $obj->TANGGAL       = $tanggal;
        $obj->JUMLAH        = $jmltotal;
        $obj->OP_BARU_0     = $op_baru_0;
        $obj->OP_BARU_1     = $op_baru_1;
        $obj->PECAH_0       = $pecah_0;
        $obj->PECAH_1       = $pecah_1;
        $obj->GABUNG_0      = $gabung_0;
        $obj->GABUNG_1      = $gabung_1;
        $obj->MUTASI_0      = $mutasi_0;
        $obj->MUTASI_1      = $mutasi_1;
        $obj->UBAH_0        = $ubah_0;
        $obj->UBAH_1        = $ubah_1;
        $obj->BATAL_0       = $batal_0;
        $obj->BATAL_1       = $batal_1;
        $obj->DUPLIKAT_0    = $duplikat_0;
        $obj->DUPLIKAT_1    = $duplikat_1;
        $obj->HAPUS_0       = $hapus_0;
        $obj->HAPUS_1       = $hapus_1;
        $obj->PENGURANGAN_0 = $pengurangan_0;
        $obj->PENGURANGAN_1 = $pengurangan_1;
        $obj->KEBERATAN_0   = $keberatan_0;
        $obj->KEBERATAN_1   = $keberatan_1;
        $obj->CETAK_0       = $cetak_0;
        $obj->CETAK_1       = $cetak_1;
        $dataUrut[] = $obj;
    }
    
    // echo '<pre style="background:#17182b;color:#5cff2f">';
    // // print_r(json_encode($tempData));
    // print_r( json_encode($dataUrut, JSON_PRETTY_PRINT) );
    // echo '</pre>';
    // exit;

    if(count($dataUrut)==0) return false;

    return $dataUrut;
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

// print_r($_REQUEST); exit;

$srcTglAwal     = @isset($_REQUEST['srcTglAwal'])   ? $_REQUEST['srcTglAwal']   : date("01-m-Y");
$srcTglAkhir    = @isset($_REQUEST['srcTglAkhir'])  ? $_REQUEST['srcTglAkhir']  : ( (date('d')=='01') ? date("t-m-Y") : date("d-m-Y") ); 
$srcNama        = @isset($_REQUEST['srcNama'])      ? $_REQUEST['srcNama']      : false;

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

$objRichText->createText('REKAP LAPORAN DOKUMEN MASUK DI LOKET PELAYANAN');
$objPHPExcel->getActiveSheet()->getCell('A1')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A1:Z1');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL : '.$srcTglAwal.' - '.$srcTglAkhir);
$objPHPExcel->getActiveSheet()->getCell('A2')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A2:Z2');
$objPHPExcel->getActiveSheet()->getStyle('A1:Z2')->applyFromArray(
    array(
        'font'    => array('bold' => true, 'size' => $fontSizeHeader),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    )
);

$objPHPExcel->setActiveSheetIndex(0);


$objPHPExcel->getActiveSheet()->getStyle('A1:Z2')->applyFromArray(
    array('font'    => array('size' => $fontSizeHeader))
);

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A3:A4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('USER PENERIMA');
$objPHPExcel->getActiveSheet()->getCell('B3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B3:B4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('TANGGAL');
$objPHPExcel->getActiveSheet()->getCell('C3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C3:C4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('JML DOK.');
$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('D3:D4');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('OP BARU');
$objPHPExcel->getActiveSheet()->getCell('E3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('E3:F3');
$objPHPExcel->getActiveSheet()->setCellValue('E4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('F4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PEMECAHAN');
$objPHPExcel->getActiveSheet()->getCell('G3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('G3:H3');
$objPHPExcel->getActiveSheet()->setCellValue('G4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('H4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENGGABUNGAN');
$objPHPExcel->getActiveSheet()->getCell('I3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I3:J3');
$objPHPExcel->getActiveSheet()->setCellValue('I4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('J4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('MUTASI');
$objPHPExcel->getActiveSheet()->getCell('K3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('K3:L3');
$objPHPExcel->getActiveSheet()->setCellValue('K4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('L4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PERUBAHAN');
$objPHPExcel->getActiveSheet()->getCell('M3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('M3:N3');
$objPHPExcel->getActiveSheet()->setCellValue('M4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('N4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PEMBATALAN');
$objPHPExcel->getActiveSheet()->getCell('O3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('O3:P3');
$objPHPExcel->getActiveSheet()->setCellValue('O4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('P4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('DUPLIKAT');
$objPHPExcel->getActiveSheet()->getCell('Q3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Q3:R3');
$objPHPExcel->getActiveSheet()->setCellValue('Q4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('R4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENGHAPUSAN');
$objPHPExcel->getActiveSheet()->getCell('S3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('S3:T3');
$objPHPExcel->getActiveSheet()->setCellValue('S4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('T4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('PENGURANGAN');
$objPHPExcel->getActiveSheet()->getCell('U3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('U3:V3');
$objPHPExcel->getActiveSheet()->setCellValue('U4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('V4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('KEBERATAN');
$objPHPExcel->getActiveSheet()->getCell('W3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('W3:X3');
$objPHPExcel->getActiveSheet()->setCellValue('W4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('X4', 'SELESAI');

$objRichText = new PHPExcel_RichText();
$objRichText->createText('CETAK SKNJOP');
$objPHPExcel->getActiveSheet()->getCell('Y3')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('Y3:Z3');
$objPHPExcel->getActiveSheet()->setCellValue('Y4', 'PROSES');
$objPHPExcel->getActiveSheet()->setCellValue('Z4', 'SELESAI');

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('REKAPITULASI LOKET PELAYANAN');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A3:Z4')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(8);

$no = 1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));
foreach ($result as $r) {
    $objPHPExcel->getActiveSheet()->getRowDimension(4 + $no)->setRowHeight(12.5);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $no), $r->PENERIMA);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $no), $r->TANGGAL);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $no), $r->JUMLAH);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $no), $r->OP_BARU_0);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $no), $r->OP_BARU_1);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . (4 + $no), $r->PECAH_0);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . (4 + $no), $r->PECAH_1);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . (4 + $no), $r->GABUNG_0);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . (4 + $no), $r->GABUNG_1);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . (4 + $no), $r->MUTASI_0);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . (4 + $no), $r->MUTASI_1);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . (4 + $no), $r->UBAH_0);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . (4 + $no), $r->UBAH_1);
    $objPHPExcel->getActiveSheet()->setCellValue('O' . (4 + $no), $r->BATAL_0);
    $objPHPExcel->getActiveSheet()->setCellValue('P' . (4 + $no), $r->BATAL_1);
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . (4 + $no), $r->DUPLIKAT_0);
    $objPHPExcel->getActiveSheet()->setCellValue('R' . (4 + $no), $r->DUPLIKAT_1);
    $objPHPExcel->getActiveSheet()->setCellValue('S' . (4 + $no), $r->HAPUS_0);
    $objPHPExcel->getActiveSheet()->setCellValue('T' . (4 + $no), $r->HAPUS_1);
    $objPHPExcel->getActiveSheet()->setCellValue('U' . (4 + $no), $r->PENGURANGAN_0);
    $objPHPExcel->getActiveSheet()->setCellValue('V' . (4 + $no), $r->PENGURANGAN_1);
    $objPHPExcel->getActiveSheet()->setCellValue('W' . (4 + $no), $r->KEBERATAN_0);
    $objPHPExcel->getActiveSheet()->setCellValue('X' . (4 + $no), $r->KEBERATAN_1);
    $objPHPExcel->getActiveSheet()->setCellValue('Y' . (4 + $no), $r->CETAK_0);
    $objPHPExcel->getActiveSheet()->setCellValue('Z' . (4 + $no), $r->CETAK_1);
    $no++;
}
//$objPHPExcel->getActiveSheet()->setCellValue('A'.(8+$no), '');
$objPHPExcel->getActiveSheet()->getStyle('A3:Z' . (3 + $no))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="REKAPITULASI_LAPORAN_LOKET_PELAYANAN_'.date('dmy').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
