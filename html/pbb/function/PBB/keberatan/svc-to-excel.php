<?php
//session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'keberatan', '', dirname(__FILE__))) . '/';

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

function getConfigValue($id, $key)
{
    global $DBLink;
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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$p = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
$nkc = @isset($_REQUEST['nkc']) ? $_REQUEST['nkc'] : "";

/* $jml = @isset($_REQUEST['j']) ? $_REQUEST['j'] : 1;
$nop = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$na = @isset($_REQUEST['na']) ? str_replace("%20"," ",$_REQUEST['na']) : ""; */

$nmFile = "Data-WP-Keberatan";

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
$minimum_njoptkp                   =  $appConfig['minimum_njoptkp'];
$minimum_sppt_pbb_terhutang         =  $appConfig['minimum_sppt_pbb_terhutang'];

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
    $query = "SELECT * FROM cppmod_pbb_services A JOIN cppmod_pbb_service_objection B WHERE A.CPM_ID = B.CPM_OB_SID  AND A.CPM_STATUS='4' " . $where;
    //echo $query;exit;
    $res = mysqli_query($DBLink, $query);
    if ($res) {
        $result["result"] = "true";
        $result["data"] = $res;
    } else {
        $result["result"] = "false";
        $result["data"] = mysqli_error($DBLink);
    }
    return $result;
}

function hitung($aValue)
{
    global $DBLink, $minimum_njoptkp, $minimum_sppt_pbb_terhutang;

    $NJOPTKP = $minimum_njoptkp;
    $minPBBHarusBayar = $minimum_sppt_pbb_terhutang;

    $NJOP = $aValue['CPM_NJOP_TANAH'] + $aValue['CPM_NJOP_BANGUNAN'];

    if ($NJOP > $NJOPTKP)
        $NJKP = $NJOP - $NJOPTKP;
    else $NJKP = 0;

    $aValue['OP_NJOP'] = $NJOP;
    $aValue['OP_NJKP'] = $NJKP;
    $aValue['OP_NJOPTKP'] = $NJOPTKP;

    $cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                        CPM_TRF_NILAI_BAWAH <= " . $NJKP . " AND
                        CPM_TRF_NILAI_ATAS >= " . $NJKP;
    $resTarif = mysqli_query($DBLink, $cari_tarif);
    if (!$resTarif) {
        echo mysqli_error($DBLink);
        echo $cari_tarif;
    }

    $dataTarif = mysqli_fetch_array($resTarif);
    $op_tarif = $dataTarif['CPM_TRF_TARIF'];
    $aValue['OP_TARIF'] = $op_tarif;
    $PBB_HARUS_DIBAYAR = $NJKP * ($op_tarif / 100);

    if ($PBB_HARUS_DIBAYAR < $minPBBHarusBayar)
        $PBB_HARUS_DIBAYAR = $minPBBHarusBayar;
    $aValue['SPPT_PBB_HARUS_DIBAYAR'] = $PBB_HARUS_DIBAYAR;

    return $aValue;
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
$objPHPExcel->getActiveSheet()->mergeCells('A2:M2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:M3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'DAFTAR WAJIB PAJAK YANG MENGAJUKAN KEBERATAN');

if ($nkc != "Pilih Semua") {
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'KECAMATAN : ' . $nkc);
} else {
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'KABUPATEN/KOTA : ' . $appConfig['NAMA_KOTA']);
}
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->getStyle('I5')->getAlignment()->setWrapText(true);
// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'NO.')
    ->setCellValue('B5', 'NOP')
    ->setCellValue('C5', 'NAMA')
    ->setCellValue('D5', 'ALAMAT')
    ->setCellValue('E5', 'KECAMATAN')
    ->setCellValue('F5', strtoupper($appConfig['LABEL_KELURAHAN']))
    ->setCellValue('G5', 'NJOP BUMI/M2')
    ->setCellValue('G6', 'SEMULA')
    ->setCellValue('H6', 'MENJADI')
    ->setCellValue('I5', 'NJOP BANGUNAN/M2')
    ->setCellValue('J5', 'TOTAL NJOP')
    ->setCellValue('J6', 'SEMULA')
    ->setCellValue('K6', 'MENJADI')
    ->setCellValue('L5', 'KETETAPAN')
    ->setCellValue('L6', 'SEMULA')
    ->setCellValue('M6', 'MENJADI');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 7;
$sumRows = mysqli_num_rows($result['data']);
$summary = array('name' => 'JUMLAH', 'ketetapan_awal' => 0, 'ketetapan_disetujui' => 0, 'njop_awal' => 0, 'njop_disetujui' => 0, 'njop_bangunan' => 0);
$vObjection    = array();
while ($rowData = mysqli_fetch_assoc($result['data'])) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, ($row - 6));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, ($rowData['CPM_OP_NUMBER'] . " "));
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $rowData['CPM_WP_NAME']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $rowData['CPM_OP_ADDRESS']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, getKecamatanNama($rowData['CPM_OP_KECAMATAN']));
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, getKelurahanNama($rowData['CPM_OP_KELURAHAN']));

    $luasTanah        = ($rowData['CPM_OB_LUAS_TANAH'] != 0 ? $rowData['CPM_OB_LUAS_TANAH'] : 1);
    $luasBangunan    = ($rowData['CPM_OB_LUAS_BANGUNAN'] != 0 ? $rowData['CPM_OB_LUAS_BANGUNAN'] : 1);

    $vObjection['CPM_NJOP_TANAH']        = $rowData['CPM_OB_NJOP_TANAH_APP'];
    $vObjection['CPM_NJOP_BANGUNAN']       = $rowData['CPM_OB_NJOP_BANGUNAN'];
    $tagihanMenjadi                        = hitung($vObjection);

    $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, ($rowData['CPM_OB_NJOP_TANAH']     != '' ? floor(($rowData['CPM_OB_NJOP_TANAH'] / $luasTanah))         : 0));
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, ($rowData['CPM_OB_NJOP_TANAH_APP'] != '' ? floor(($rowData['CPM_OB_NJOP_TANAH_APP'] / $luasTanah))     : 0));
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, ($rowData['CPM_OB_NJOP_BANGUNAN'] != '' ? floor(($rowData['CPM_OB_NJOP_BANGUNAN'] / $luasBangunan))    : 0));
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, ($rowData['CPM_OB_NJOP_TANAH'] + $rowData['CPM_OB_NJOP_BANGUNAN']));
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, ($rowData['CPM_OB_NJOP_TANAH_APP'] + $rowData['CPM_OB_NJOP_BANGUNAN']));
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $rowData['CPM_SPPT_DUE']);
    $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, floor($tagihanMenjadi['SPPT_PBB_HARUS_DIBAYAR']));
    $row++;
    $i++;

    $summary['njop_awal']               += floor(($rowData['CPM_OB_NJOP_TANAH'] / $luasTanah));
    $summary['njop_disetujui']       += floor(($rowData['CPM_OB_NJOP_TANAH_APP'] / $luasTanah));
    $summary['njop_bangunan']            += floor(($rowData['CPM_OB_NJOP_BANGUNAN'] / $luasBangunan));
    $summary['ketetapan_awal']           += ($rowData['CPM_OB_NJOP_TANAH'] + $rowData['CPM_OB_NJOP_BANGUNAN']);
    $summary['ketetapan_disetujui']  += ($rowData['CPM_OB_NJOP_TANAH_APP'] + $rowData['CPM_OB_NJOP_BANGUNAN']);
    $summary['tagihan_semula']         += $rowData['CPM_SPPT_DUE'];
    $summary['tagihan_menjadi']         += $tagihanMenjadi['SPPT_PBB_HARUS_DIBAYAR'];
}

//JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':' . 'F' . $row);
$objPHPExcel->getActiveSheet()->setCellValue('A' . $row, 'JUMLAH');
$objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $summary['njop_awal']);
$objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $summary['njop_disetujui']);
$objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $summary['njop_bangunan']);
$objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $summary['ketetapan_awal']);
$objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $summary['ketetapan_disetujui']);
$objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $summary['tagihan_semula']);
$objPHPExcel->getActiveSheet()->setCellValue('M' . $row, $summary['tagihan_menjadi']);
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
$objPHPExcel->getActiveSheet()->setTitle('Daftar Keberatan');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:M6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:M' . ($sumRows + 7))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);
$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');
$objPHPExcel->getActiveSheet()->mergeCells('B5:B6');
$objPHPExcel->getActiveSheet()->mergeCells('C5:C6');
$objPHPExcel->getActiveSheet()->mergeCells('D5:D6');
$objPHPExcel->getActiveSheet()->mergeCells('E5:E6');
$objPHPExcel->getActiveSheet()->mergeCells('F5:F6');
$objPHPExcel->getActiveSheet()->mergeCells('I5:I6');

$objPHPExcel->getActiveSheet()->mergeCells('G5:H5');
$objPHPExcel->getActiveSheet()->mergeCells('J5:K5');
$objPHPExcel->getActiveSheet()->mergeCells('L5:M5');

$objPHPExcel->getActiveSheet()->getStyle('A5:M6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:M6')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getStyle('A7:A' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('B7:B' . ($sumRows + 6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('D7:D' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('E7:E' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('F7:F' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()->getStyle('G7:G' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('H7:H' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('I7:I' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('J7:J' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('K7:K' . ($sumRows + 7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

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

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Daftar Keberatan ' . $kcm . ' ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
