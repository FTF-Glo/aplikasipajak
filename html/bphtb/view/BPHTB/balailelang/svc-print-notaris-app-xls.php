<?php

ini_set('memory_limit', '400M');
ini_set("max_execution_time", "1000");
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'balailelang', '', dirname(__FILE__))) . '/';

/** Error reporting */
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

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
require_once($sRootPath . "inc/PBB/dbMonitoring.php");

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

function getConfigValue($id, $key)
{
    global $DBLink, $appID;
    $id = $appID;
    $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);


$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);


#print_r($q);exit;
$tgl1 = $q[0]->tgl1;
$tgl2 = $q[0]->tgl2;
$appID = base64_decode($q[0]->appID);
#echo $ids;
#exit;

$query = sprintf("SELECT * , DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%d-%%m-%%Y') as EXPIRED, B.CPM_TRAN_DATE
                            FROM cppmod_ssb_doc A inner join cppmod_ssb_tranmain B on 
                            A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
                            WHERE B.CPM_TRAN_FLAG=0 AND 
                            (B.CPM_TRAN_DATE BETWEEN '%s' AND '%s')", getConfigValue("aBPHTB", 'TENGGAT_WAKTU'), $tgl1, $tgl2);
#echo $query;exit;
$res = mysqli_query($DBLink, $query);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
    ->setLastModifiedBy("vpost")
    ->setTitle("-")
    ->setSubject("-")
    ->setDescription("bphtb")
    ->setKeywords("-");


// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'No.')
    ->setCellValue('B1', 'Nama Wajib Pajak')
    ->setCellValue('C1', 'Alamat Wajib Pajak')
    ->setCellValue('D1', 'No. KTP')
    ->setCellValue('E1', 'Kelurahan / Desa')
    ->setCellValue('F1', 'RT / RW')
    ->setCellValue('G1', 'Kecamatan')
    ->setCellValue('H1', 'Kabupaten / Kota')
    ->setCellValue('I1', 'Kode Pos')
    ->setCellValue('J1', 'NOP PBB')
    ->setCellValue('K1', 'Letak tanah / Bangunan')
    ->setCellValue('L1', 'Kelurahan / Desa')
    ->setCellValue('M1', 'RT / RW')
    ->setCellValue('N1', 'Kecamatan')
    ->setCellValue('O1', 'Kabupaten / Kota')
    ->setCellValue('P1', 'Luas (Tanah / Bumi)')
    ->setCellValue('Q1', 'NJOP PBB/m2 (Tanah / Bumi)')
    ->setCellValue('R1', 'Luas * NJOP PBB (Tanah / Bumi)')
    ->setCellValue('S1', 'Luas (Bangunan)')
    ->setCellValue('T1', 'NJOP PBB/m2 (Bangunan)')
    ->setCellValue('U1', 'Luas * NJOP PBB (Bangunan)')
    ->setCellValue('V1', 'NJOP PBB Total')
    ->setCellValue('W1', 'Harga Transaksi')
    ->setCellValue('X1', 'Jenis Perolehan')
    ->setCellValue('Y1', 'No. Sertifikat tanah')
    ->setCellValue('Z1', 'NPOP')
    ->setCellValue('AA1', 'NPOPTKP')
    ->setCellValue('AB1', 'NPOPKP')
    ->setCellValue('AC1', 'BPHTB')
    ->setCellValue('AD1', 'Dengan Angka')
    ->setCellValue('AE1', 'Tanggal Transaksi');



// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

function getAttr($jns)
{
    $jenishak = $jns;
    if ($jns == '1')
        $jenishak = "Jual beli";
    if ($jns == '2')
        $jenishak = "Tukar Menukar";
    if ($jns == '3')
        $jenishak = "Hibah";
    if ($jns == '4')
        $jenishak = "Hibah Wasiat Sedarah Satu Derajat";
    if ($jns == '5')
        $jenishak = "Hibah Wasiat Non Sedarah Satu Derajat";
    if ($jns == '6')
        $jenishak = "Waris";
    if ($jns == '7')
        $jenishak = "Pemasukan dalam perseroan/badan hukum lainnya";
    if ($jns == '8')
        $jenishak = "Pemisahan hak yang mengakibatkan peralihan";
    if ($jns == '9')
        $jenishak = "Penunjukan pembeli dalam lelang";
    if ($jns == '10')
        $jenishak = "Pelaksanaan putusan hakim yang <br>mempunyai kekuatan hukum tetap";
    if ($jns == '11')
        $jenishak = "Penggabungan usaha";
    if ($jns == '12')
        $jenishak = "Pemekaran usaha";
    if ($jns == '13')
        $jenishak = "Hadiah";
    if ($jns == '14')
        $jenishak = "Jual beli khusus perolehan hak Rumah Sederhana dan
	Rumah Susun Sederhana melalui KPR bersubsidi";
    if ($jns == '15')
        $jenishak = "Pemberian hak baru sebagai kelanjutan pelepasan hak";
    if ($jns == '16')
        $jenishak = "Pemberian hak baru diluar pelepasan hak";

    $label_transaksi = "Nilai Pasar";
    if ($jns == '1') {
        $label_transaksi = "Harga Transaksi";
    } else if ($jns == '9') {
        $label_transaksi = "Harga Lelang";
    }
    return array("jenishak" => $jenishak, "label_transaksi" => $label_transaksi);
}

$row = 2;
$sumRows = mysqli_num_rows($res);


while ($rowData = mysqli_fetch_assoc($res)) {

    $attr = getAttr($rowData['CPM_OP_JENIS_HAK']);

    $npop = 0;
    $pwaris = "-";

    $a = strval($rowData['CPM_OP_LUAS_BANGUN']) * strval($rowData['CPM_OP_NJOP_BANGUN']) + strval($rowData['CPM_OP_LUAS_TANAH']) * strval($rowData['CPM_OP_NJOP_TANAH']);
    $b = strval($rowData['CPM_OP_HARGA']);
    $NPOPTKP = $rowData['CPM_OP_NPOPTKP'];
    $typeR = $rowData['CPM_OP_JENIS_HAK'];
    $type = $rowData['CPM_PAYMENT_TIPE'];
    $NOP = $rowData['CPM_OP_NOMOR'];
    $c1 = "";
    $c2 = "";
    $c3 = "";
    $c4 = "";


    if ($b < $a)
        $npop = $a;
    else
        $npop = $b;

    $n = strval($rowData['CPM_PAYMENT_TIPE_PENGURANGAN']);
    $a = $npop - strval($NPOPTKP) > 0 ? $npop - strval($NPOPTKP) : 0;
    $m = ($a) * 0.05;
    $a = $a * 0.05;

    if ($n != 0)
        $m = $m - $m * ($n * 0.01);
    $b = $npop - $NPOPTKP;
    if ($b < 0)
        $b = 0;
    if (($rowData['CPM_PAYMENT_TIPE'] == '2') && (!is_null($rowData['CPM_OP_BPHTB_TU']))) {
        $a = $rowData['CPM_OP_BPHTB_TU'];
        $m = $a;
    }

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 1));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $rowData['CPM_WP_NAMA']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['CPM_WP_ALAMAT']);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $rowData['CPM_WP_NOKTP'], PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $rowData['CPM_WP_KELURAHAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $rowData['CPM_WP_RT'] . "/" . $rowData['CPM_WP_RW']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $rowData['CPM_WP_KECAMATAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $rowData['CPM_WP_KABUPATEN']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $rowData['CPM_WP_KODEPOS']);
    $objPHPExcel->getActiveSheet()->setCellValueExplicit('J' . $row, $rowData['CPM_OP_NOMOR'], PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $rowData['CPM_OP_LETAK']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_OP_KELURAHAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $rowData['CPM_OP_RT'] . "/" . $rowData['CPM_OP_RW']);
    $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $rowData['CPM_OP_KECAMATAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $rowData['CPM_OP_KABUPATEN']);
    $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $rowData['CPM_OP_LUAS_TANAH']);
    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $rowData['CPM_OP_NJOP_TANAH']);
    $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $rowData['CPM_OP_NJOP_TANAH'] * $rowData['CPM_OP_LUAS_TANAH']);
    $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $rowData['CPM_OP_LUAS_BANGUN']);
    $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $rowData['CPM_OP_NJOP_BANGUN']);
    $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $rowData['CPM_OP_NJOP_BANGUN'] * $rowData['CPM_OP_LUAS_BANGUN']);
    $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, ($rowData['CPM_OP_NJOP_TANAH'] * $rowData['CPM_OP_LUAS_TANAH']) + ($rowData['CPM_OP_NJOP_BANGUN'] * $rowData['CPM_OP_LUAS_BANGUN']));
    $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, $rowData['CPM_OP_HARGA']);
    $objPHPExcel->getActiveSheet()->setCellValue('X' . $row, $attr['label_transaksi']);
    $objPHPExcel->getActiveSheet()->setCellValue('Y' . $row, $rowData['CPM_OP_NMR_SERTIFIKAT']);
    $objPHPExcel->getActiveSheet()->setCellValue('Z' . $row, $npop);
    $objPHPExcel->getActiveSheet()->setCellValue('AA' . $row, $NPOPTKP);
    $objPHPExcel->getActiveSheet()->setCellValue('AB' . $row, $b);
    $objPHPExcel->getActiveSheet()->setCellValue('AC' . $row, $a);
    $objPHPExcel->getActiveSheet()->setCellValue('AD' . $row, $m);
    $objPHPExcel->getActiveSheet()->setCellValue('AE' . $row, $rowData['CPM_TRAN_DATE']);
    #$rowData['CPM_SSB_AKUMULASI']
    #$attr['jenishak']
    #$rowData['CPM_WP_NPWP']
    $row++;
}


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Daftar SSPD BPHTB');

//----set style cell
//style header
$objPHPExcel->getActiveSheet()->getStyle('A1:AE1')->applyFromArray(
    array(
        'font' => array(
            'bold' => true
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);
$objPHPExcel->getActiveSheet()->getStyle('I2:L' . ($sumRows + 1))->applyFromArray(
    array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A1:AE1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A1:AE1')->getFill()->getStartColor()->setRGB('E4E4E4');

//$objPHPExcel->getActiveSheet()->getStyle('A2:B' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('N2:N' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('M2:M' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('O2:O' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('P2:P' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('Q2:Q' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('R2:R' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('S2:S' . ($sumRows + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

for ($x = "A"; $x <= "Z"; $x++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
}
$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');

header('Content-Disposition: attachment;filename="' . date('yymdhmi') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
