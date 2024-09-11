<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

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

function openMysql() {
    global $appConfig;
    $host = $appConfig['GW_DBHOST'];
    $port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
    $user = $appConfig['GW_DBUSER'];
    $pass = $appConfig['GW_DBPWD'];
    $dbname = $appConfig['GW_DBNAME'];
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
    if (!$myDBLink) {
        echo mysqli_error($myDBLink);
        //exit();
    }
    //$database = mysql_select_db($dbname, $myDBLink);
    return $myDBLink;
}

function closeMysql($con) {
    mysqli_close($con);
}

function getData($kode=false) {
    global $myDBLink, $thn1, $thn2, $tanggal1, $tanggal2, $qBuku;																																	

    $myDBLink = openMysql();

    $whr = '';
    if($kode){
        $len = strlen($kode);
        $whr = " AND LEFT(NOP,$len)='$kode'";
    }

    $q="SELECT
            OP_KECAMATAN AS KECAMATAN,
            LEFT(NOP,7) AS KEC, 
            OP_KELURAHAN AS KELURAHAN,
            LEFT(NOP,10) AS KEL, 
            SPPT_TAHUN_PAJAK AS THN,
            COUNT(NOP) AS STTS,
            SUM(SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
            SUM(PBB_DENDA) AS DENDA,
            SUM(PBB_TOTAL_BAYAR) AS TOTAL
        FROM pbb_sppt
        WHERE 
            PAYMENT_FLAG = '1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) >= '$tanggal1' 
            AND DATE(LEFT(PAYMENT_PAID,10)) <= '$tanggal2' 
            AND SPPT_TAHUN_PAJAK >= '$thn1' 
            AND SPPT_TAHUN_PAJAK <= '$thn2' 
            $whr 
            $qBuku
        GROUP BY LEFT(NOP,7), LEFT(NOP,10), SPPT_TAHUN_PAJAK
        ORDER BY LEFT(NOP,7), LEFT(NOP,10), SPPT_TAHUN_PAJAK DESC";

    $res = mysqli_query($myDBLink, $q);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }
    
    $rows = [];

    $addware = ($kode) ? "WHERE l.CPC_TKL_KCID='$kode'" : "";
    $k="SELECT 
            c.CPC_TKC_KECAMATAN AS KECAMATAN,
            l.CPC_TKL_KCID AS KEC,
            l.CPC_TKL_KELURAHAN AS KELURAHAN,
            l.CPC_TKL_ID AS KEL
        FROM cppmod_tax_kelurahan l
        INNER JOIN cppmod_tax_kecamatan c ON c.CPC_TKC_ID=l.CPC_TKL_KCID
        $addware 
        ORDER BY KEL";

    $datakel = mysqli_query($myDBLink, $k);
    if ($datakel === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($datakel)){
        for($thn=$thn2; $thn>=$thn1; $thn--) { 
            $kec = substr($row['KEC'],0,2) .'.'. substr($row['KEC'],2,2) .'.'. substr($row['KEC'],-3,3);
            $kel = substr($row['KEL'],-3,3);
            $rows[$row['KEC']][$row['KEL']]['KEC'] = $kec;
            $rows[$row['KEC']][$row['KEL']]['KEL'] = $kel;
            $rows[$row['KEC']][$row['KEL']]['KECAMATAN'] = $row['KECAMATAN'];
            $rows[$row['KEC']][$row['KEL']]['KELURAHAN'] = $row['KELURAHAN'];
            $rows[$row['KEC']][$row['KEL']][$thn] = array("STTS"=>0,"POKOK"=>0,"DENDA"=>0,"TOTAL"=>0);
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

        $rows[$kec][$kel][$thn] = $row;
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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig  = $User->GetAppConfig($a);
$kd         = $appConfig['KODE_KOTA'];

$thn1       = (@isset($_REQUEST['thn1']) && $_REQUEST['thn1']!='') ? $_REQUEST['thn1'] : false;
$thn2       = (@isset($_REQUEST['thn2']) && $_REQUEST['thn2']!='') ? $_REQUEST['thn2'] : false;
$tanggal1   = (@isset($_REQUEST['tgl1']) && $_REQUEST['tgl1']!='') ? $_REQUEST['tgl1'] : false;
$tanggal2   = (@isset($_REQUEST['tgl2']) && $_REQUEST['tgl2']!='') ? $_REQUEST['tgl2'] : false;

if(!$tanggal1 || !$tanggal2) die('');

$kodekec        = (@isset($_REQUEST['kec']) && $_REQUEST['kec']!='') ? $_REQUEST['kec'] : false;
$namakecamatan  = @isset($_REQUEST['nmkec']) ? $_REQUEST['nmkec'] : "";

$buku   = @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : 0;

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

if($kodekec){
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
function nf($num) {
    return number_format($num,0,',','.');
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
/*_____________________SETING COLUMN DIMENSION__________________________________*/
$sheet->getColumnDimension('A')->setWidth(1);
$sheet->getColumnDimension('B')->setWidth(1);
$kolom = 'C';
$kolomEnd = 'A';
$jmlcolomn = 6 + (4*($thn2-$thn1+1));
for ($i=0; $i<$jmlcolomn ; $i++) { 
    $sheet->getColumnDimension($kolom)->setAutoSize(true);
    $kolom++;
    $kolomEnd++;
}
$sheet->getRowDimension(7)->setRowHeight(25);
$sheet->getRowDimension(8)->setRowHeight(30);
/*______________________EOL DIMENSION_______________________________________*/

/*___________________SETING MERGING & ALIGNM________________________________*/
$sheet->mergeCells('C3:'.$kolomEnd.'3');
$sheet->mergeCells('C4:'.$kolomEnd.'4');
$sheet->mergeCells('C7:C8');
alignCenter($sheet, 'C3:C4');
alignCenter($sheet, 'C7:'.$kolomEnd.'8');
alignMiddle($sheet, 'C7');
/*________________________EOL MERGING______________________________________*/

/*________________________BORDER SETTING___________________________________*/
$objPHPExcel->getActiveSheet()->setTitle('REALISASI TUNGGAKAN');

applyBorders($sheet, 'C6:'.$kolomEnd.'6',$bordertop);
applyBorders($sheet, 'C7:'.$kolomEnd.'7',$borderArray);
applyBorders($sheet, 'C8:'.$kolomEnd.'8',$borderArray);
applyBorders($sheet, 'C9:'.$kolomEnd.'9',$borderbottom);
/*________________________EOL BORDER SETTING_______________________________*/
/*======================== EOL DOCUMENT SETTING ========================== */

/*========================= HEADER SETTING =============================== */
$sheet->setCellValue('C3', 'REALISASI TUNGGAKAN PAJAK BUMI DAN BANGUNAN');
$sheet->setCellValue('C4', $periode);
$sheet->setCellValue('C7', 'WILAYAH');

$tahunpajak = $thn2;
$kolomtahun = 'D';
while($tahunpajak >= $thn1){
    $kolom = $kolomtahun;

    $sheet->setCellValue($kolomtahun.'7', $tahunpajak);

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

$kolom = $kolomtahun;

$sheet->setCellValue($kolomtahun.'7', 'JUMLAH REALISASI');

$sheet->setCellValue($kolom.'8', 'STTS');
$kolom++;

$sheet->setCellValue($kolom.'8', "PBB (RP)");
$kolom++;

$sheet->setCellValue($kolom.'8', "DENDA (RP)");
$kolom++;

$sheet->setCellValue($kolom.'8', "JUMLAH (RP)");

$sheet->mergeCells($kolomtahun.'7:'.$kolom.'7');


$sheet->getStyle('C7:'.$kolomEnd.'8')->applyFromArray($bold)->getAlignment()->setWrapText(true);
alignCenter($sheet, 'C3:'.$kolomEnd.'8');
alignMiddle($sheet, 'C3:'.$kolomEnd.'8');


$n = 10;
$nnn = $n;
$labelkec = '';
$arrColJML = [];
foreach ($data as $dataKecamatan) {
    $labelkel = '';
    foreach ($dataKecamatan as $r) {
        $lblkec = isset($r['KEC']) ? $r['KEC'] . ' - ' . $r['KECAMATAN'] : '-';
        $lblkel = isset($r['KEL']) ? $r['KEL'] . ' - ' . $r['KELURAHAN'] : '-';
        $arrColJumHorisontal = [];
        if($labelkel == $lblkec){
            $sheet->setCellValue("C$n", $lblkel);
            applyBorders($sheet, "C$n", $borderleft);
            applyBorders($sheet, "C$n", $borderright);

            $tahunpajak = $thn2;
            $tahunkebawah = $thn1;
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
                $arrColJumHorisontal['stts'][] = $kolom.$n;
                $kolom++;
                
                $sheet->setCellValue($kolom.$n, $pokok);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColJumHorisontal['pokok'][] = $kolom.$n;
                $kolom++;
                
                $sheet->setCellValue($kolom.$n, $denda);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColJumHorisontal['denda'][] = $kolom.$n;
                $kolom++;
                
                $sheet->setCellValue($kolom.$n, $total);
                applyBorders($sheet, $kolom.$n,$borderright);
                alignRight($sheet, $kolom.$n);
                $arrColJumHorisontal['total'][] = $kolom.$n;
                $kolom++;
                $tahunpajak--;
            }

            // Segment JUMLAH ----------------------------------------
            $colreal = implode('+',$arrColJumHorisontal['stts']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            
            $colreal = implode('+',$arrColJumHorisontal['pokok']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            
            $colreal = implode('+',$arrColJumHorisontal['denda']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;

            $colreal = implode('+',$arrColJumHorisontal['total']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            // END Segment JUMLAH ______________________________

        }else{
            $labelkel = $lblkec;
            $sheet->setCellValue("C$n", $lblkec);
            $sheet->getStyle("C$n")->applyFromArray($bold);
            applyBorders($sheet, "C{$n}:{$kolomEnd}{$n}", $borderArray);
            $sheet->mergeCells("C{$n}:{$kolomEnd}{$n}");
            $n++;
            $nn = $n;
            $sheet->setCellValue("C$n", $lblkel);
            applyBorders($sheet, "C$n", $borderleft);
            applyBorders($sheet, "C$n", $borderright);

            $tahunpajak = $thn2;
            $tahunkebawah = $thn1;
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
                $arrColJumHorisontal['stts'][] = $kolom.$n;
                $kolom++;
                
                $sheet->setCellValue($kolom.$n, $pokok);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColJumHorisontal['pokok'][] = $kolom.$n;
                $kolom++;
                
                $sheet->setCellValue($kolom.$n, $denda);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColJumHorisontal['denda'][] = $kolom.$n;
                $kolom++;

                $sheet->setCellValue($kolom.$n, $total);
                applyBorders($sheet, $kolom.$n, $borderright);
                alignRight($sheet, $kolom.$n);
                $arrColJumHorisontal['total'][] = $kolom.$n;
                $kolom++;
                $tahunpajak--;
            }

            // Segment JUMLAH HORISONTAL ----------------------------------------
            $colreal = implode('+',$arrColJumHorisontal['stts']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            
            $colreal = implode('+',$arrColJumHorisontal['pokok']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            
            $colreal = implode('+',$arrColJumHorisontal['denda']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;

            $colreal = implode('+',$arrColJumHorisontal['total']);
            $sheet->setCellValue($kolom.$n, '='.$colreal);
            applyBorders($sheet, $kolom.$n, $borderright);
            alignRight($sheet, $kolom.$n);
            $kolom++;
            // END JUMLAH HORISONTAL ______________________________
        }
        $n++;
    }

    $sheet->setCellValue("C$n", 'J U M L A H :');
    alignCenter($sheet, "C$n");
    applyBorders($sheet, "C{$n}:{$kolomEnd}{$n}", $borderArray);

    $tahunpajak = $thn2;
    $tahunkebawah = $thn1;
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

    /// ---- JML 
    $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $arrColJML['colreal']['stts'][] = $kolom.$n;
    $kolom++;
    
    $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $arrColJML['colreal']['pokok'][] = $kolom.$n;
    $kolom++;
    
    $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $arrColJML['colreal']['denda'][] = $kolom.$n;
    $kolom++;

    $sheet->setCellValue($kolom.$n, '=SUM('.$kolom.$nn.':'.$kolom.($n-1).')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $arrColJML['colreal']['total'][] = $kolom.$n;
    $kolom++;
    
    $sheet->getStyle("C$n:{$kolomEnd}{$n}")->applyFromArray($bold);
    alignMiddle($sheet, "C$n:{$kolomEnd}{$n}");
    $sheet->getRowDimension($n)->setRowHeight(25);
    $n++;
    $n++;
}

$n++;

if(!$kodekec){
    $sheet->setCellValue("C$n", 'T O T A L :');
    alignCenter($sheet, "C$n");
    applyBorders($sheet, "C{$n}:{$kolomEnd}{$n}", $borderArray);
    
    $tahunpajak = $thn2;
    $tahunkebawah = $thn1;
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

    // TOTAL REALISASI
    $onlycolJml = implode(',',$arrColJML['colreal']['stts']);
    $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $kolom++;
    
    $onlycolJml = implode(',',$arrColJML['colreal']['pokok']);
    $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $kolom++;
    
    $onlycolJml = implode(',',$arrColJML['colreal']['denda']);
    $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $kolom++;

    $onlycolJml = implode(',',$arrColJML['colreal']['total']);
    $sheet->setCellValue($kolom.$n, '=SUM('.$onlycolJml.')');
    applyBorders($sheet, $kolom.$n, $borderArray);
    alignRight($sheet, $kolom.$n);
    $kolom++;
    
    $sheet->getStyle("C$n:{$kolomEnd}{$n}")->applyFromArray($bold);
    alignMiddle($sheet, "C$n:{$kolomEnd}{$n}");
    $sheet->getRowDimension($n)->setRowHeight(30);
}

/*========================= EOL HEADER SETTING ============================ */

$uniq = uniqid();
$namakecamatan = str_replace(' ','_',$namakecamatan);
if($kodekec){
    $add = strtoupper(substr($uniq,-5));
    $filename = "REALISASI_TUNGGAKAN_{$namakecamatan}_{$add}.xls";
}else{
    $add = strtoupper(substr($uniq,-7));
    $filename = "REALISASI_TUNGGAKAN_{$add}.xls";
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

exit;