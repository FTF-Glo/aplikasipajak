<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getData()
{
    global $DBLink, $srcNOP1, $selectby, $kecamatan, $desa, $tahun, $srcNOP2, $srcNOP3, $srcNOP4, $srcNOP5, $srcNOP6, $srcNOP7, $srcAlamat, $srcNama, $tab;

    $whr = " WHERE CPM_NOP='-'";

    $arrWhere = array();

    if($selectby==1){
        if ($kecamatan != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 1, 7) = '{$kecamatan}'");
        }
        if ($desa != "" && $desa != null && $desa != 'null') {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 1, 10) = '{$desa}'");
        }
        if ($tab != 0 && $tahun != "") {
            array_push($arrWhere, " CPM_SPPT_THN_PENETAPAN = '{$tahun}'");
        }

    }else{
        
        if ($srcNOP1 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 1, 2) = '{$srcNOP1}'");
        }
        if ($srcNOP2 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 3, 2) = '{$srcNOP2}'");
        }
        if ($srcNOP3 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 5, 3) = '{$srcNOP3}'");
        }
        if ($srcNOP4 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 8, 3) = '{$srcNOP4}'");
        }
        if ($srcNOP5 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 11, 3) = '{$srcNOP5}'");
        }
        if ($srcNOP6 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 14, 4) = '{$srcNOP6}'");
        }
        if ($srcNOP7 != "") {
            array_push($arrWhere, " SUBSTR(CPM_NOP, 18, 1) = '{$srcNOP7}'");
        }
        if ($srcNama != "") {
            array_push($arrWhere, " CPM_WP_NAMA LIKE '%{$srcNama}%'");
        }
        if ($srcAlamat != "") {
            array_push($arrWhere, " CPM_OP_ALAMAT LIKE '%{$srcAlamat}%'");
        }
    }

    // added by d3Di = khusus Tab Register tanah   -----------------------
    if ($tab == 3) array_push($arrWhere, " SUBSTR(CPM_NOP, 18, 1) = '3'");
    //--------------------------------------------------------------------
    
    $where = implode(" AND ", $arrWhere);

    if ($where != '') {
        $whr = ' WHERE ' . $where;
    }

    switch ($tab) {
        case 0:
            $tableName = "cppmod_pbb_sppt";
            break;
        case 1:
            $tableName = "cppmod_pbb_sppt_susulan";
            break;
        case 2:
            $tableName = "cppmod_pbb_sppt_final";
            break;
        case 3:
            $tableName = "cppmod_pbb_sppt_final";
            break;
    }

    $query = "SELECT 
                CPM_SPPT_DOC_ID,
                CPM_SPPT_DOC_VERSION,
                CPM_NOP,
                CPM_WP_NAMA,
                CPM_OP_ALAMAT, 
                CPM_OP_KELURAHAN, 
                CPM_OP_KECAMATAN, 
                CPM_OT_ZONA_NILAI, 
                CPM_OP_LUAS_TANAH, 
                CPM_OP_LUAS_BANGUNAN, 
                CPM_NJOP_TANAH, 
                CPM_NJOP_BANGUNAN 
            FROM $tableName 
                $whr ";
    // echo $query; exit;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    $check = mysqli_num_rows($res);
    if($check>0) {
        $row = array();
        $i = 0;
        while ($row = mysqli_fetch_assoc($res)) {
            $return[$i]["CPM_SPPT_DOC_ID"]      = ($row["CPM_SPPT_DOC_ID"] != "") ? $row["CPM_SPPT_DOC_ID"] : '-';
            $return[$i]["CPM_SPPT_DOC_VERSION"] = ($row["CPM_SPPT_DOC_VERSION"] != "") ? $row["CPM_SPPT_DOC_VERSION"] : '-';
            $return[$i]["CPM_NOP"]               = ($row["CPM_NOP"] != "") ? $row["CPM_NOP"] : '-';
            $return[$i]["CPM_WP_NAMA"]          = ($row["CPM_WP_NAMA"] != "") ? $row["CPM_WP_NAMA"] : '-';
            $return[$i]["CPM_OP_ALAMAT"]         = ($row["CPM_OP_ALAMAT"] != "") ? $row["CPM_OP_ALAMAT"] : '-';
            $return[$i]["CPM_OT_ZONA_NILAI"]    = ($row["CPM_OT_ZONA_NILAI"] != "") ? $row["CPM_OT_ZONA_NILAI"] : '-';
            $return[$i]["CPM_OP_LUAS_TANAH"]    = ($row["CPM_OP_LUAS_TANAH"] != "") ? $row["CPM_OP_LUAS_TANAH"] : '0';
            $return[$i]["CPM_OP_LUAS_BANGUNAN"] = ($row["CPM_OP_LUAS_BANGUNAN"] != "") ? $row["CPM_OP_LUAS_BANGUNAN"] : '0';
            $return[$i]["CPM_NJOP_TANAH"]         = ($row["CPM_NJOP_TANAH"] != "") ? $row["CPM_NJOP_TANAH"] : '0';
            $return[$i]["CPM_NJOP_BANGUNAN"]     = ($row["CPM_NJOP_BANGUNAN"] != "") ? $row["CPM_NJOP_BANGUNAN"] : '0';
            $return[$i]["CPM_NJOP_TOTAL"]         = ($row["CPM_NJOP_TANAH"] + $row["CPM_NJOP_BANGUNAN"]);
            $return[$i]["CPM_OP_KELURAHAN"]      = ($row["CPM_OP_KELURAHAN"] != "") ? $row["CPM_OP_KELURAHAN"] : '';
            $return[$i]["CPM_OP_KECAMATAN"]      = ($row["CPM_OP_KECAMATAN"] != "") ? $row["CPM_OP_KECAMATAN"] : '';
            $i++;
        }
    }else{
        $return[0]["CPM_SPPT_DOC_ID"]       = '-';
        $return[0]["CPM_SPPT_DOC_VERSION"]  = '-';
        $return[0]["CPM_NOP"]               = '-';
        $return[0]["CPM_WP_NAMA"]           = '-';
        $return[0]["CPM_OP_ALAMAT"]         = '-';
        $return[0]["CPM_OT_ZONA_NILAI"]     = '-';
        $return[0]["CPM_OP_LUAS_TANAH"]     = '0';
        $return[0]["CPM_OP_LUAS_BANGUNAN"]  = '0';
        $return[0]["CPM_NJOP_TANAH"]        = '0';
        $return[0]["CPM_NJOP_BANGUNAN"]     = '0';
        $return[0]["CPM_NJOP_TOTAL"]        = '0';
        $return[0]["CPM_OP_KELURAHAN"]      = '';
        $return[0]["CPM_OP_KECAMATAN"]      = '';
    }

    // echo '<pre>';
    // print_r($return);

    return $return;
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

$q          = @isset($_REQUEST['q'])        ? $_REQUEST['q'] : "";
$selectby   = @isset($_REQUEST['selectby']) ? $_REQUEST['selectby'] : '1';
$kecamatan  = @isset($_REQUEST['kec'])      ? $_REQUEST['kec'] : '';
$desa       = @isset($_REQUEST['desa'])     ? $_REQUEST['desa'] : '';
$tahun      = @isset($_REQUEST['tahun'])    ? $_REQUEST['tahun'] : '';
$srcAlamat  = @isset($_REQUEST['srcAlamat'])? $_REQUEST['srcAlamat'] : '';
$srcNama    = @isset($_REQUEST['srcNama'])  ? $_REQUEST['srcNama'] : '';
$srcNOP1    = @isset($_REQUEST['srcNOP1'])  ? $_REQUEST['srcNOP1'] : '';
$srcNOP2    = @isset($_REQUEST['srcNOP2'])  ? $_REQUEST['srcNOP2'] : '';
$srcNOP3    = @isset($_REQUEST['srcNOP3'])  ? $_REQUEST['srcNOP3'] : '';
$srcNOP4    = @isset($_REQUEST['srcNOP4'])  ? $_REQUEST['srcNOP4'] : '';
$srcNOP5    = @isset($_REQUEST['srcNOP5'])  ? $_REQUEST['srcNOP5'] : '';
$srcNOP6    = @isset($_REQUEST['srcNOP6'])  ? $_REQUEST['srcNOP6'] : '';
$srcNOP7    = @isset($_REQUEST['srcNOP7'])  ? $_REQUEST['srcNOP7'] : '';

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$tab = $q->s;

$User                 = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig             = $User->GetAppConfig($a);

switch ($tab) {
    case 0:
        $txt = "DALAM PROSES";
        break;
    case 1:
        $txt = "SUSULAN";
        break;
    case 2:
        $txt = "MASAL";
        break;
    case 3:
        $txt = "TANAH REGISTER";
        break;
}

// print_r($appConfig); exit;
$data = getData();

// echo "<pre>";
// print_r($data);exit;
$sumRows = count($data);
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
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' DAFTAR NOP ' . $txt);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', " NOP ")
    ->setCellValue('B5', " NAMA ")
    ->setCellValue('C5', " ALAMAT ")
    ->setCellValue('D5', " KODE ZNT ")
    ->setCellValue('E5', " LUAS TANAH ")
    ->setCellValue('F5', " LUAS BANGUNAN ")
    ->setCellValue('G5', " NJOP TANAH ")
    ->setCellValue('H5', " NJOP BANGUNAN ")
    ->setCellValue('I5', " TOTAL NJOP ")
    ->setCellValue('J5', " KECAMATAN ")
    ->setCellValue('K5', " KELURAHAN ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
for ($i = 0; $i < $sumRows; $i++) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), " " . $data[$i]['CPM_NOP']);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[$i]['CPM_WP_NAMA'] . " ");
    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[$i]['CPM_OP_ALAMAT']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[$i]['CPM_OT_ZONA_NILAI']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[$i]['CPM_OP_LUAS_TANAH']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $data[$i]['CPM_OP_LUAS_BANGUNAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $data[$i]['CPM_NJOP_TANAH']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $data[$i]['CPM_NJOP_BANGUNAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[$i]['CPM_NJOP_TOTAL']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), getKelurahanNama($data[$i]['CPM_OP_KELURAHAN']));
    $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), getKecamatanNama($data[$i]['CPM_OP_KECAMATAN']));
    $row++;
}

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
$objPHPExcel->getActiveSheet()->setTitle('DAFTAR NOP ' . $txt);

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:K5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:K' . ($sumRows + 5))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:K5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:K5')->getFill()->getStartColor()->setRGB('E4E4E4');

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

// Redirect output to a clients web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="DAFTAR_NOP_' . $txt . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
