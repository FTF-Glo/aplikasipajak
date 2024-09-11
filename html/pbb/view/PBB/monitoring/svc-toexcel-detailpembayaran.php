<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

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

function getData($kode=false)
{
    global $myDBLink, $kd, $tanggal1, $tanggal2, $qBuku, $appConfig;																																	

    $myDBLink = openMysql();
    $tahunpajak = $appConfig['tahun_tagihan'];
    $tahuntunggak = $tahunpajak - 1;
    $tahunbatas = $tahunpajak - 6;
    $tahunkebawah = $tahunpajak - 7;
    $tahunTerbawah = 1995;

    $whr = $whr2 = '';
    if($kode){
        $len = strlen($kode);
        $whr = " AND LEFT(NOP,$len)='$kode'";
        $opt = ($len==10) ? "l.CPC_TKL_ID" : "l.CPC_TKL_KCID";
        $whr2 = "WHERE $opt='$kode'";
    }

    $q="SELECT
            OP_KECAMATAN AS KECAMATAN,
            LEFT(NOP,7) AS KEC, 
            OP_KELURAHAN AS KELURAHAN,
            LEFT(NOP,10) AS KEL, 
            SPPT_TAHUN_PAJAK AS THN,
            COUNT(NOP) AS STTS,
            SUM(COALESCE(SPPT_PBB_HARUS_DIBAYAR, 0)) AS POKOK,
            SUM(COALESCE(PBB_DENDA, 0)) AS DENDA,
            SUM(COALESCE(PBB_TOTAL_BAYAR, 0)) AS TOTAL
        FROM pbb_sppt
        WHERE 
            PAYMENT_FLAG = '1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) >= '$tanggal1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) <= '$tanggal2'
            $whr 
        GROUP BY LEFT(NOP,7), LEFT(NOP,10), SPPT_TAHUN_PAJAK
        ORDER BY LEFT(NOP,7), LEFT(NOP,10), SPPT_TAHUN_PAJAK DESC";//exit($q);

    $res = mysqli_query($myDBLink, $q);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }
    
    $rows = [];

    $k="SELECT 
            c.CPC_TKC_KECAMATAN AS KECAMATAN,
            l.CPC_TKL_KCID AS KEC,
            l.CPC_TKL_KELURAHAN AS KELURAHAN,
            l.CPC_TKL_ID AS KEL
        FROM cppmod_tax_kelurahan l
        INNER JOIN cppmod_tax_kecamatan c ON c.CPC_TKC_ID=l.CPC_TKL_KCID
        $whr2
        ORDER BY KEL";

    $datakel = mysqli_query($myDBLink, $k);
    if ($datakel === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($datakel)){
        for($thn=$tahunpajak; $thn>=1995; $thn--) { 
            $kec = substr($row['KEC'],0,2) .'.'. substr($row['KEC'],2,2) .'.'. substr($row['KEC'],-3,3);
            $kel = substr($row['KEL'],-3,3);
            $rows[$row['KEC']][$row['KEL']]['KEC'] = $kec;
            $rows[$row['KEC']][$row['KEL']]['KEL'] = $kel;
            $rows[$row['KEC']][$row['KEL']]['KECAMATAN'] = $row['KECAMATAN'];
            $rows[$row['KEC']][$row['KEL']]['KELURAHAN'] = $row['KELURAHAN'];
            if($thn>$tahunkebawah){
                $rows[$row['KEC']][$row['KEL']][$thn] = array("STTS"=>0,"POKOK"=>0,"DENDA"=>0,"TOTAL"=>0);
            }else{
                $rows[$row['KEC']][$row['KEL']][$tahunkebawah] = array("STTS"=>0,"POKOK"=>0,"DENDA"=>0,"TOTAL"=>0);
            }
            
        }
    }

    while ($row = mysqli_fetch_assoc($res)){
        $kec = $row['KEC'];
        $kel = $row['KEL'];
        $thn = (int)$row['THN'];
        $row['KEC'] = substr($row['KEC'],0,2) .'.'. substr($row['KEC'],2,2) .'.'. substr($row['KEC'],-3,3);
        $row['KEL'] = substr($row['KEL'],-3,3);
        
        $rows[$kec][$kel]['KEC'] = $row['KEC'];
        $rows[$kec][$kel]['KEL'] = $row['KEL'];
        $rows[$kec][$kel]['KECAMATAN'] = $row['KECAMATAN'];
        $rows[$kec][$kel]['KELURAHAN'] = $row['KELURAHAN'];

        unset($row['THN']);
        unset($row['KEC']);
        unset($row['KEL']);
        unset($row['KECAMATAN']);
        unset($row['KELURAHAN']);

        if($thn>$tahunkebawah){
            $rows[$kec][$kel][$thn] = $row;
        }else{
            $temp = (isset($rows[$kec][$kel][$tahunkebawah])) ? $rows[$kec][$kel][$tahunkebawah] : array("STTS"=>0,"POKOK"=>0,"DENDA"=>0,"TOTAL"=>0);
            $row['STTS']    = $temp['STTS'] + $row['STTS'];
            $row['POKOK']   = $temp['POKOK'] + $row['POKOK'];
            $row['DENDA']   = $temp['DENDA'] + $row['DENDA'];
            $row['TOTAL']   = $temp['TOTAL'] + $row['TOTAL'];
            $rows[$kec][$kel][$tahunkebawah] = $row;
        }
    }

    closeMysql($myDBLink);

    // echo '<pre>';
    // print_r($tung);
    // exit;

    // header('Content-Type: application/json; charset=utf-8');
    // print_r(json_encode($rows));
    // exit;
    
    return $rows;
}

function getDataDetail($kode=false)
{
    global $myDBLink, $tanggal1, $tanggal2, $appConfig;																																	

    $myDBLink = openMysql();

    $len = strlen($kode);
    if($len!=10) return [];

    $q="SELECT
            OP_KECAMATAN AS KECAMATAN,
            OP_KELURAHAN AS KELURAHAN,
            SPPT_TAHUN_PAJAK AS TAHUN,
            NOP,
            SPPT_PBB_HARUS_DIBAYAR AS POKOK, 
            PBB_DENDA AS DENDA,
            PBB_TOTAL_BAYAR AS TOTAL_BAYAR,
            DATE_FORMAT(LEFT(PAYMENT_PAID,10),'%d-%m-%Y') AS TGL_BAYAR,
            ID_WP AS WP_KTP,
            WP_NAMA,
            WP_ALAMAT,
            WP_RT,
            WP_RW,
            WP_KELURAHAN,
            WP_KECAMATAN,
            WP_KOTAKAB
        FROM pbb_sppt
        WHERE 
            PAYMENT_FLAG = '1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) >= '$tanggal1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) <= '$tanggal2'
            AND LEFT(NOP,$len)='$kode' 
        ORDER BY (DATE_FORMAT(LEFT(PAYMENT_PAID,10),'%Y%m%d')) ASC, NOP ASC, SPPT_TAHUN_PAJAK DESC";

    $res = mysqli_query($myDBLink, $q);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }
    
    $rows = [];
    while ($row = mysqli_fetch_assoc($res)){
        $row['KECAMATAN']   = strtoupper($row['KECAMATAN']);
        $row['KELURAHAN']   = strtoupper($row['KELURAHAN']);
        $row['TAHUN']       = (int)$row['TAHUN'];
        $row['POKOK']       = (float)$row['POKOK'];
        $row['DENDA']       = (float)$row['DENDA'];
        $row['TOTAL_BAYAR'] = (float)$row['TOTAL_BAYAR'];
        $row['POKOK']       = (float)$row['POKOK'];
        $row['WP_KTP']      = addslashes($row['WP_KTP']);
        $row['WP_NAMA']     = strtoupper($row['WP_NAMA']);
        $row['WP_ALAMAT']   = strtoupper($row['WP_ALAMAT']);
        $row['WP_KELURAHAN']= strtoupper($row['WP_KELURAHAN']);
        $row['WP_KECAMATAN']= strtoupper($row['WP_KECAMATAN']);
        $row['WP_KOTAKAB']  = strtoupper($row['WP_KOTAKAB']);
        $rows[]             = $row;
    }
    closeMysql($myDBLink);
    // header('Content-Type: application/json; charset=utf-8');
    // print_r(json_encode($rows));
    // exit;
    return $rows;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;

$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig  = $User->GetAppConfig($a);
$kd         = $appConfig['KODE_KOTA'];

$tanggal1 = (@isset($_REQUEST['tgl1']) && $_REQUEST['tgl1']!='') ? $_REQUEST['tgl1'] : false;
$tanggal2 = (@isset($_REQUEST['tgl2']) && $_REQUEST['tgl2']!='') ? $_REQUEST['tgl2'] : false;

if(!$tanggal1 || !$tanggal2) die('');

$kodekel = (@isset($_REQUEST['kel']) && $_REQUEST['kel']!='' && $_REQUEST['kel']!='null') ? $_REQUEST['kel'] : false;
$kodekec = (@isset($_REQUEST['kec']) && $_REQUEST['kec']!='') ? $_REQUEST['kec'] : false;
$namakelurahan = @isset($_REQUEST['nmkel']) ? $_REQUEST['nmkel'] : "";
$namakecamatan = @isset($_REQUEST['nmkec']) ? $_REQUEST['nmkec'] : "";

$buku = @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : 0;
$arrWhere = array();

$periode1 = date("d-m-Y", strtotime($tanggal1));
$periode2 = date("d-m-Y", strtotime($tanggal2));
$periode = "PERIODE $periode1 S/D $periode2";


$qBuku = "";
if ($buku != 0) {
    switch ($buku) {
        case 1:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
            break;
        case 12:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 123:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 1234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 12345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 2:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
            break;
        case 23:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 234:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 2345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 3:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
            break;
        case 34:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 345:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 4:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
            break;
        case 45:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
        case 5:
            $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
            break;
    }
}
$where = implode(" AND ", $arrWhere);

if($kodekel){
    $data = getDataDetail($kodekel);
}elseif($kodekec){
    $data = getData($kodekec);
}else{
    $data = getData();
}

$fontSizeHeader = 10;
$fontSizeDefault = 9;
$border = [];
$bordertop    = array('borders' => 
                    array(
                        'top'   => array('style' => PHPExcel_Style_Border::BORDER_DOTTED)));
$borderbottom = array('borders' => 
                    array(
                        'bottom'=> array('style' => PHPExcel_Style_Border::BORDER_DOTTED)));
$borderleft   = array('borders' => 
                    array(
                        'left'  => array('style' => PHPExcel_Style_Border::BORDER_DOTTED)));
$borderright  = array('borders' => 
                    array(
                        'right' => array('style' => PHPExcel_Style_Border::BORDER_DOTTED)));
$borderArray['borders'] = array('allborders'   => array('style' => PHPExcel_Style_Border::BORDER_DOTTED));
$bold = array('font' => array('bold' => true));

function applyBorders($sheet, $range,$isinya) {
    $sheet->getStyle($range)->applyFromArray($isinya);
}

function alignCenter($sheet, $colCell) {
    $sheet->getStyle($colCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
}

function alignMiddle($sheet, $colCell) {
    $sheet->getStyle($colCell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
}
function alignRight($sheet, $colCell) {
    $sheet->getStyle($colCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle($colCell)->getNumberFormat()->setFormatCode("#,##0");
}


$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();
/*===============   DOCUMENT SETTING    ============================================ */
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$sheet->getPageMargins()->setTop(0.8);
$sheet->getPageMargins()->setRight(0);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.3);

$objPHPExcel->getDefaultStyle()->getFont()->setName('Courier New');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
$objPHPExcel->getProperties()->setCreator("vpost")
            ->setLastModifiedBy("vpost")
            ->setTitle("Alfa System")
            ->setSubject("Alfa System pbb")
            ->setDescription("pbb")
            ->setKeywords("Alfa System");
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->setShowGridlines(false);

if($kodekel){
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->getColumnDimension('E')->setAutoSize(true);
    $sheet->getColumnDimension('F')->setAutoSize(true);
    $sheet->getColumnDimension('G')->setAutoSize(true);
    $sheet->getColumnDimension('H')->setAutoSize(true);
    $sheet->getColumnDimension('I')->setAutoSize(true);
    $sheet->getColumnDimension('J')->setAutoSize(true);
    $sheet->getColumnDimension('K')->setAutoSize(true);
    $sheet->getColumnDimension('L')->setAutoSize(true);
    /*______________________EOL DIMENSION_______________________________________*/

    /*________________________BORDER SETTING___________________________________*/
    $objPHPExcel->getActiveSheet()->setTitle('DETAIL PEMBAYARAN');

    applyBorders($sheet, 'A1:L1', $bordertop);
    applyBorders($sheet, 'A1:L1', $borderArray);
    applyBorders($sheet, 'A1:L1', $borderArray);
    applyBorders($sheet, 'A1:L1', $borderbottom);
    /*________________________EOL BORDER SETTING_______________________________*/
    /*======================== EOL DOCUMENT SETTING ========================== */

    /*========================= HEADER SETTING =============================== */
    $sheet->setCellValue('A1', '#');
    $sheet->setCellValue('B1', 'KECAMATAN');
    $sheet->setCellValue('C1', 'DESA/KELURAHAN');
    $sheet->setCellValue('D1', 'TAHUN');
    $sheet->setCellValue('E1', 'NOP');
    $sheet->setCellValue('F1', 'POKOK');
    $sheet->setCellValue('G1', 'DENDA');
    $sheet->setCellValue('H1', 'NOMINAL BAYAR');
    $sheet->setCellValue('I1', 'TANGGAL BAYAR');
    $sheet->setCellValue('J1', 'KTP');
    $sheet->setCellValue('K1', 'NAMA WP');
    $sheet->setCellValue('L1', 'ALAMAT WP');

    $sheet->getStyle('A1:L1')->applyFromArray($bold)->getAlignment()->setWrapText(true);

    /*___________________SETING MERGING & ALIGNM________________________________*/
    alignCenter($sheet, 'A1:L1');
    $sheet->getStyle("A1:L1")->applyFromArray($bold);
    alignMiddle($sheet, "A1:L1");
    $sheet->getRowDimension(1)->setRowHeight(30);
    /*________________________EOL MERGING______________________________________*/

    $n = 2;
    foreach ($data as $r) {
        $alamatwp = $r['WP_ALAMAT'];
        $alamatwp .= ((int)$r['WP_RT']>0) ? ' RT:'.$r['WP_RT'] : '';
        $alamatwp .= ((int)$r['WP_RW']>0) ? ' RW:'.$r['WP_RW'] : '';
        $alamatwp .= ($r['WP_KELURAHAN']!='') ? ' '.$r['WP_KELURAHAN'] : '';
        $alamatwp .= ($r['WP_KECAMATAN']!='' && $r['WP_KECAMATAN']!=$r['KECAMATAN']) ? ', KEC. '.$r['WP_KECAMATAN'] : '';
        $alamatwp .= ($r['WP_KOTAKAB']!='' && $r['WP_KOTAKAB']!='PESAWARAN') ? ', '.$r['WP_KOTAKAB'] : '';

        $sheet->setCellValue("A$n", ($n-1));
        $sheet->setCellValue("B$n", $r['KECAMATAN']);
        $sheet->setCellValue("C$n", $r['KELURAHAN']);
        $sheet->setCellValue("D$n", $r['TAHUN']);
        $sheet->setCellValue("E$n", $r['NOP']." ");
        $sheet->setCellValue("F$n", $r['POKOK']);
        $sheet->setCellValue("G$n", $r['DENDA']);
        $sheet->setCellValue("H$n", $r['TOTAL_BAYAR']);
        $sheet->setCellValue("I$n", $r['TGL_BAYAR']);
        $sheet->setCellValue("J$n", $r['WP_KTP']." ");
        $sheet->setCellValue("K$n", $r['WP_NAMA']);
        $sheet->setCellValue("L$n", $alamatwp);

        alignRight($sheet, "A$n");
        alignCenter($sheet, "D$n");
        alignCenter($sheet, "E$n");
        alignCenter($sheet, "I$n");
        alignCenter($sheet, "J$n");
        alignRight($sheet, "F$n");
        alignRight($sheet, "G$n");
        alignRight($sheet, "H$n");
        applyBorders($sheet, "A{$n}:L$n", $borderArray);

        $n++;
    }

    $last = $n - 1;
    $sheet->setCellValue("A$n", 'JUMLAH:');
    alignCenter($sheet, "A$n");
    $sheet->mergeCells("A{$n}:E$n");
    applyBorders($sheet, "A{$n}:E$n", $borderArray);

    $sheet->setCellValue("F$n", '=SUM(F2:F'.$last.')');
    applyBorders($sheet, "F$n", $borderArray);
    alignRight($sheet, "F$n");

    $sheet->setCellValue("G$n", '=SUM(G2:G'.$last.')');
    applyBorders($sheet, "G$n", $borderArray);
    alignRight($sheet, "G$n");

    $sheet->setCellValue("H$n", '=SUM(H2:H'.$last.')');
    applyBorders($sheet, "H$n", $borderArray);
    alignRight($sheet, "H$n");
    
    $sheet->mergeCells("I{$n}:L$n");
    applyBorders($sheet, "I{$n}:L$n", $borderArray);

}else{
    /*_____________________SETING COLUMN DIMENSION__________________________________*/
    $sheet->getColumnDimension('A')->setWidth(1);
    $sheet->getColumnDimension('B')->setWidth(1);
    $kolom = 'C';
    for ($i=0; $i<42 ; $i++) { 
        $sheet->getColumnDimension($kolom)->setAutoSize(true);
        $kolom++;
    }
    $sheet->getRowDimension(7)->setRowHeight(25);
    $sheet->getRowDimension(8)->setRowHeight(30);
    /*______________________EOL DIMENSION_______________________________________*/

    /*___________________SETING MERGING & ALIGNM________________________________*/
    $sheet->mergeCells('C3:AQ3');
    $sheet->mergeCells('C4:AQ4');
    $sheet->mergeCells('C7:C8');
    alignCenter($sheet, 'C3:C4');
    alignCenter($sheet, 'C7:AQ8');
    alignMiddle($sheet, 'C7');
    /*________________________EOL MERGING______________________________________*/

    /*________________________BORDER SETTING___________________________________*/
    $objPHPExcel->getActiveSheet()->setTitle('REKAP PEMBAYARAN');

    applyBorders($sheet, 'C6:AQ6',$bordertop);
    applyBorders($sheet, 'C7:AQ7',$borderArray);
    applyBorders($sheet, 'C8:AQ8',$borderArray);
    applyBorders($sheet, 'C9:AQ9',$borderbottom);
    /*________________________EOL BORDER SETTING_______________________________*/
    /*======================== EOL DOCUMENT SETTING ========================== */

    /*========================= HEADER SETTING =============================== */
    $tahunpajak = $appConfig['tahun_tagihan'];
    $tahunbatas = $tahunpajak - 6;
    $tahunkebawah = $tahunpajak - 7;
    $tahunTerbawah = 1995;
    $kolomtahun = 'D';
    // print_r($yearBottom);exit;
    while($tahunpajak >= $tahunkebawah) {
        $kolom = $kolomtahun;
        switch ($tahunpajak) {
            case $appConfig['tahun_tagihan']:
                $extend = 'POKOK '.$tahunpajak;
                break;
            case $tahunbatas:
                $extend = '<= '.$tahunpajak;
                break;
            case $tahunkebawah:
                $extend = $tahunpajak.' => '.$tahunTerbawah;
                break;
            default:
                $extend = $tahunpajak;
                break;
        }
        $sheet->setCellValue($kolomtahun.'7', $extend);

        $sheet->setCellValue($kolom.'8', 'STTS');
        $kolom++;
        
        $sheet->setCellValue($kolom.'8', "PBB (RP)");
        $kolom++;
        
        $sheet->setCellValue($kolom.'8', "DENDA (RP)");
        $kolom++;

        $sheet->setCellValue($kolom.'8', "JUMLAH (RP)");

        $sheet->mergeCells($kolomtahun.'7:'.$kolom.'7');
        $kolom++;

        $kolomtahun = $kolom;
        $tahunpajak--;
    }


    $sheet->setCellValue('C3', 'LAPORAN PENERIMAAN PAJAK BUMI DAN BANGUNAN');
    $sheet->setCellValue('C4', $periode);
    $sheet->setCellValue('C7', 'WILAYAH');

    $sheet->setCellValue('AJ7', 'JUMLAH TUNGGAKAN');
    $sheet->setCellValue('AJ8', 'STTS');
    $sheet->setCellValue('AK8', 'PBB (RP)');
    $sheet->setCellValue('AL8', 'DENDA (RP)');
    $sheet->setCellValue('AM8', 'JUMLAH (RP)');
    $sheet->mergeCells('AJ7:AM7');

    $sheet->setCellValue('AN7', 'JML POKOK + TUNGGAKAN');
    $sheet->setCellValue('AN8', 'STTS');
    $sheet->setCellValue('AO8', 'PBB (RP)');
    $sheet->setCellValue('AP8', 'DENDA (RP)');
    $sheet->setCellValue('AQ8', 'JUMLAH (RP)');
    $sheet->mergeCells('AN7:AQ7');
    $sheet->getStyle('C7:AQ8')->applyFromArray($bold)->getAlignment()->setWrapText(true);
    alignCenter($sheet, 'C3:AQ8');
    alignMiddle($sheet, 'C3:AQ8');


    $n = 10;
    $nnn = $n;
    $labelkec = '';
    $arrColJML = [];
    $arrColThnTunggakan = [];
    foreach ($data as $dataKecamatan) {
        $labelkel = '';
        foreach ($dataKecamatan as $r) {
            $lblkec = isset($r['KEC']) ? $r['KEC'] . ' - ' . $r['KECAMATAN'] : '-';
            $lblkel = isset($r['KEL']) ? $r['KEL'] . ' - ' . $r['KELURAHAN'] : '-';
            $arrColJmlTgk = [];
            $arrColPokokPlusTgk = [];
            if($labelkel == $lblkec){
                $sheet->setCellValue("C$n", $lblkel);
                applyBorders($sheet, "C$n", $borderleft);
                applyBorders($sheet, "C$n", $borderright);

                $tahunpajak = $appConfig['tahun_tagihan'];
                $tahunkebawah = $tahunpajak - 7;
                $kolom = 'D';
                while($tahunpajak >= $tahunkebawah){

                    $tahunada = isset($r[$tahunpajak]) ? true : false;
                    $stts  = ($tahunada) ? $r[$tahunpajak]['STTS'] : 0;
                    $pokok = ($tahunada) ? $r[$tahunpajak]['POKOK'] : 0;
                    $denda = ($tahunada) ? $r[$tahunpajak]['DENDA'] : 0;
                    $total = ($tahunada) ? $r[$tahunpajak]['TOTAL'] : 0;
                    
                    $sheet->setCellValue($kolom.$n, $stts);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['stts'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['stts'][] = $kolom.$n;
                    }
                    $kolom++;
                    
                    $sheet->setCellValue($kolom.$n, $pokok);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['pokok'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['pokok'][] = $kolom.$n;
                    }
                    $kolom++;
                    
                    $sheet->setCellValue($kolom.$n, $denda);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['denda'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['denda'][] = $kolom.$n;
                    }
                    $kolom++;
                    
                    $sheet->setCellValue($kolom.$n, $total);
                    applyBorders($sheet, $kolom.$n,$borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['total'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['total'][] = $kolom.$n;
                    }
                    $kolom++;
                    $tahunpajak--;
                }

                // Segment Tunggakan ----------------------------------------
                $jmltgk = implode('+',$arrColJmlTgk['stts']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['stts'][] = $kolom.$n;
                $kolom++;
                
                $jmltgk = implode('+',$arrColJmlTgk['pokok']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['pokok'][] = $kolom.$n;
                $kolom++;
                
                $jmltgk = implode('+',$arrColJmlTgk['denda']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['denda'][] = $kolom.$n;
                $kolom++;

                $jmltgk = implode('+',$arrColJmlTgk['total']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n,$borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['total'][] = $kolom.$n;
                $kolom++;
                // END Segment Tunggakan ______________________________

                // Segment POKOK + Tunggakan ----------------------------------------
                $pkktgk = implode('+',$arrColPokokPlusTgk['stts']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;
                
                $pkktgk = implode('+',$arrColPokokPlusTgk['pokok']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;
                
                $pkktgk = implode('+',$arrColPokokPlusTgk['denda']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;

                $pkktgk = implode('+',$arrColPokokPlusTgk['total']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;
                // END Segment POKOK + Tunggakan ______________________________

            }else{
                $labelkel = $lblkec;
                $sheet->setCellValue("C$n", $lblkec);
                $sheet->getStyle("C$n")->applyFromArray($bold);
                applyBorders($sheet, "C{$n}:AQ$n", $borderArray);
                $sheet->mergeCells("C{$n}:AQ$n");
                $n++;
                $nn = $n;
                $sheet->setCellValue("C$n", $lblkel);
                applyBorders($sheet, "C$n", $borderleft);
                applyBorders($sheet, "C$n", $borderright);

                $tahunpajak = $appConfig['tahun_tagihan'];
                $tahunkebawah = $tahunpajak - 7;
                $kolom = 'D';
                while($tahunpajak >= $tahunkebawah){

                    $tahunada = isset($r[$tahunpajak]) ? true : false;
                    $stts  = ($tahunada) ? $r[$tahunpajak]['STTS'] : 0;
                    $pokok = ($tahunada) ? $r[$tahunpajak]['POKOK'] : 0;
                    $denda = ($tahunada) ? $r[$tahunpajak]['DENDA'] : 0;
                    $total = ($tahunada) ? $r[$tahunpajak]['TOTAL'] : 0;

                    $sheet->setCellValue($kolom.$n, $stts);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['stts'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['stts'][] = $kolom.$n;
                    }
                    $kolom++;
                    
                    $sheet->setCellValue($kolom.$n, $pokok);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['pokok'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['pokok'][] = $kolom.$n;
                    }
                    $kolom++;
                    
                    $sheet->setCellValue($kolom.$n, $denda);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['denda'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['denda'][] = $kolom.$n;
                    }
                    $kolom++;

                    $sheet->setCellValue($kolom.$n, $total);
                    applyBorders($sheet, $kolom.$n, $borderright);
                    alignRight($sheet, $kolom.$n);
                    if($tahunpajak==$appConfig['tahun_tagihan']) {
                        $arrColPokokPlusTgk['total'][] = $kolom.$n;
                    }else{
                        $arrColJmlTgk['total'][] = $kolom.$n;
                    }
                    $kolom++;
                    $tahunpajak--;
                }

                // Segment Tunggakan ----------------------------------------
                $jmltgk = implode('+',$arrColJmlTgk['stts']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['stts'][] = $kolom.$n;
                $kolom++;
                
                $jmltgk = implode('+',$arrColJmlTgk['pokok']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['pokok'][] = $kolom.$n;
                $kolom++;
                
                $jmltgk = implode('+',$arrColJmlTgk['denda']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['denda'][] = $kolom.$n;
                $kolom++;

                $jmltgk = implode('+',$arrColJmlTgk['total']);
                $sheet->setCellValue($kolom.$n, '='.$jmltgk);
                applyBorders($sheet, $kolom.$n,$borderright);
                alignRight($sheet, $kolom.$n);
                $arrColPokokPlusTgk['total'][] = $kolom.$n;
                $kolom++;
                // END Segment Tunggakan ______________________________
                
                // Segment POKOK + Tunggakan ----------------------------------------
                $pkktgk = implode('+',$arrColPokokPlusTgk['stts']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;
                
                $pkktgk = implode('+',$arrColPokokPlusTgk['pokok']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;
                
                $pkktgk = implode('+',$arrColPokokPlusTgk['denda']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;

                $pkktgk = implode('+',$arrColPokokPlusTgk['total']);
                $sheet->setCellValue($kolom.$n, '='.$pkktgk);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $kolom++;
                // END Segment POKOK + Tunggakan ______________________________
            }
            $n++;
        }

        $sheet->setCellValue("C$n", 'J U M L A H :');
        alignCenter($sheet, "C$n");
        applyBorders($sheet, "C{$n}:AQ$n", $borderArray);

        $tahunpajak = $appConfig['tahun_tagihan'];
        $tahunkebawah = $tahunpajak - 7;
        $kolom = 'D';
        while($tahunpajak >= $tahunkebawah){

            $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $arrColJML[$tahunpajak]['stts'][] = $kolom.$n;
            $kolom++;
            
            $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $arrColJML[$tahunpajak]['pokok'][] = $kolom.$n;
            $kolom++;
            
            $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $arrColJML[$tahunpajak]['denda'][] = $kolom.$n;
            $kolom++;

            $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $arrColJML[$tahunpajak]['total'][] = $kolom.$n;
            $kolom++;
            $tahunpajak--;
        }

        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['tunggakan']['stts'][] = $kolom.$n;
        $kolom++;
        
        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['tunggakan']['pokok'][] = $kolom.$n;
        $kolom++;
        
        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['tunggakan']['denda'][] = $kolom.$n;
        $kolom++;

        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['tunggakan']['total'][] = $kolom.$n;
        $kolom++;

        /// ---- JML pokok + tunggakan
        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['pkktgk']['stts'][] = $kolom.$n;
        $kolom++;
        
        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['pkktgk']['pokok'][] = $kolom.$n;
        $kolom++;
        
        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['pkktgk']['denda'][] = $kolom.$n;
        $kolom++;

        $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $arrColJML['pkktgk']['total'][] = $kolom.$n;
        $kolom++;
        
        $sheet->getStyle("C$n:AQ$n")->applyFromArray($bold);
        alignMiddle($sheet, "C$n:AQ$n");
        $sheet->getRowDimension($n)->setRowHeight(25);
        $n++;
        $n++;
    }

    $n++;

    if(!$kodekec){
        $sheet->setCellValue("C$n", 'T O T A L :');
        alignCenter($sheet, "C$n");
        applyBorders($sheet, "C{$n}:AQ$n", $borderArray);
        
        $tahunpajak = $appConfig['tahun_tagihan'];
        $tahunkebawah = $tahunpajak - 7;
        $kolom = 'D';
        while($tahunpajak >= $tahunkebawah){
        
            $onlycolJml = implode(',',$arrColJML[$tahunpajak]['stts']);
            $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            
            $onlycolJml = implode(',',$arrColJML[$tahunpajak]['pokok']);
            $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            
            $onlycolJml = implode(',',$arrColJML[$tahunpajak]['denda']);
            $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $kolom++;
        
            $onlycolJml = implode(',',$arrColJML[$tahunpajak]['total']);
            $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
            applyBorders($sheet, $kolom.$n, $borderArray);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            $tahunpajak--;
        }

        $onlycolJml = implode(',',$arrColJML['tunggakan']['stts']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;
        
        $onlycolJml = implode(',',$arrColJML['tunggakan']['pokok']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;
        
        $onlycolJml = implode(',',$arrColJML['tunggakan']['denda']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;

        $onlycolJml = implode(',',$arrColJML['tunggakan']['total']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;

        // TOTAL Pokok + Tunggakan
        $onlycolJml = implode(',',$arrColJML['pkktgk']['stts']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;
        
        $onlycolJml = implode(',',$arrColJML['pkktgk']['pokok']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;
        
        $onlycolJml = implode(',',$arrColJML['pkktgk']['denda']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;

        $onlycolJml = implode(',',$arrColJML['pkktgk']['total']);
        $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
        applyBorders($sheet, $kolom.$n, $borderArray);
        alignRight($sheet, $kolom.$n);
        $kolom++;
        
        $sheet->getStyle("C$n:AQ$n")->applyFromArray($bold);
        alignMiddle($sheet, "C$n:AQ$n");
        $sheet->getRowDimension($n)->setRowHeight(30);
    }

    /*========================= EOL HEADER SETTING ============================ */
}

$uniq = uniqid();
$namakecamatan = str_replace(' ','_',$namakecamatan);
$namakelurahan = str_replace(' ','_',$namakelurahan);
if($kodekel){
    $add = strtoupper(substr($uniq,-3));
    $filename = "DETAIL_PEMBAYARAN_{$namakecamatan}_{$namakelurahan}_{$add}.xls";
}elseif($kodekec){
    $add = strtoupper(substr($uniq,-5));
    $filename = "REKAP_PEMBAYARAN_{$namakecamatan}_{$add}.xls";
}else{
    $add = strtoupper(substr($uniq,-7));
    $filename = "REKAP_PEMBAYARAN_{$add}.xls";
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

exit;