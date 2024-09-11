<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

date_default_timezone_set('Asia/Jakarta');
//error_reporting(E_ALL);
ini_set('display_errors', 1);

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

global $appConfig;
$get =& $_GET;
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
        echo mysqli_error($myDBLink);
    }
    return $myDBLink;
}

function closeMysql($con)
{
    mysqli_close($con);
}

function getKetetapanAll()
{
    global $DBLink, $appConfig, $thn, $qBuku, $speriode, $eperiode, $kecamatan;
    $myDBLink = openMysql();

    $where = "";
    $wherekec = "";
    if ($thn != "") {
        $where .= "AND PBB.SPPT_TAHUN_PAJAK='$thn'";
    }
    if ($kecamatan != "") {
        $where .= " AND PBB.OP_KECAMATAN_KODE='$kecamatan' ";
        $wherekec = " WHERE KEC.CPC_TKC_ID='$kecamatan' ";
    }
    $query = "  SELECT ID, KELURAHAN, KECAMATAN, sum(JML) AS JML, SUM(JUMLAH) AS JUMLAH
        FROM(	
					SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, 0 JML, 0 JUMLAH
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				" . $wherekec . "
				GROUP BY KEL.CPC_TKL_ID

			UNION ALL
				SELECT KEL.CPC_TKL_ID AS ID,KEC.CPC_TKC_KECAMATAN AS KECAMATAN,KEL.CPC_TKL_KELURAHAN AS KELURAHAN, COUNT(PBB.WP_NAMA) AS JML, SUM(PBB.SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH
				FROM
					cppmod_tax_kelurahan KEL
				JOIN cppmod_tax_kecamatan KEC ON KEL.CPC_TKL_KCID=KEC.CPC_TKC_ID
				JOIN PBB_SPPT PBB ON KEL.CPC_TKL_ID = PBB.OP_KELURAHAN_KODE AND KEL.CPC_TKL_KCID=PBB.OP_KECAMATAN_KODE WHERE 1=1 " . $where . $qBuku . "
				GROUP BY KEL.CPC_TKL_ID


        ) y
        GROUP BY ID
        ORDER BY KECAMATAN, KELURAHAN
			";
    $res = mysqli_query($myDBLink, $query);
    if ($res === false) {
        echo mysqli_error($myDBLink);
        exit();
    }

    $data     = array();
    $i        = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$i]["ID"]              = $row["ID"];
        $data[$i]["KECAMATAN"]       = $row["KECAMATAN"];
        $data[$i]["KELURAHAN"]       = $row["KELURAHAN"];
        $data[$i]["JML"]          = $row["JML"];
        $data[$i]["JUMLAH"]          = $row["JUMLAH"];

        $i++;
    }
    return $data;
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kab = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan = @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$speriode = @isset($_REQUEST['speriode']) ? $_REQUEST['speriode'] : "";

$eperiode = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$eperiode2 = @isset($_REQUEST['eperiode2']) ? $_REQUEST['eperiode2'] : "";
$bulan_lalu_idx = 0;

$arrBln = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
);


$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";
$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : 0;
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

$sBuku = " ";

$fontSizeHeader = 10;
$fontSizeDefault = 9;
/*================================ FUNGSI-FUNGSI ===================================== */
function setCellAlignment($sheet, $cell, $horizontalAlignment, $verticalAlignment) {
    $sheet->getStyle($cell)->getAlignment()->setHorizontal($horizontalAlignment);
    $sheet->getStyle($cell)->getAlignment()->setVertical($verticalAlignment);
}
function setHoriz($Cellnya, $alignmen,$objPHPExcel){
    $objPHPExcel->getActiveSheet()->getStyle($Cellnya)->getAlignment()->setHorizontal($alignmen);
}
function applyBorders($objPHPExcel, $range) {
    $objPHPExcel->getActiveSheet()->getStyle($range)->applyFromArray(array(
        'borders' => array(
            'top'       => array('style' => PHPExcel_Style_Border::BORDER_DOTTED),
            'bottom'    => array('style' => PHPExcel_Style_Border::BORDER_DOTTED),
        ),
    ));
}

function getKelurahan(){
    global $kecamatan;
    if($kecamatan == 'Pilih Semua'){$kecamatan = '';}
    $wherekec = '';
    if(!empty($kecamatan)){
        $wherekec = 'WHERE kel.CPC_TKL_KCID="'.$kecamatan.'"';
    }

    $con = openMysql();
    $rows = [];
    $r = $con->query("SELECT 
                            kel.CPC_TKL_KCID AS kec,
                            kec.CPC_TKC_KECAMATAN AS KECAMATAN,
                            kel.CPC_TKL_ID AS kel,
                            kel.CPC_TKL_KELURAHAN AS KELURAHAN
                        FROM cppmod_tax_kelurahan kel 
                        LEFT JOIN cppmod_tax_kecamatan kec ON kec.CPC_TKC_ID=kel.CPC_TKL_KCID
                        $wherekec ORDER BY KECAMATAN, kel");
    while($row = $r->fetch_object()){
        $kec = $row->kec;

        $rows[$kec][] = [
                    'kel' => $row->kel,
                    'namakel' => $row->KELURAHAN,
                    'namakec' => $row->KECAMATAN
                ];
    }
    return $rows;
}

function getKetetapan(){
    global $thn, $kecamatan, $qBuku;

    $con = openMysql();
    $where = "";

    if ($thn != "") {
        $where .= " AND SPPT_TAHUN_PAJAK='$thn' ";
    }

    if ($kecamatan != "") {
        $where .= " AND LEFT(NOP,7)='$kecamatan' ";
    }

    $res = $con->query("SELECT 
                            LEFT(NOP,10) AS kel,
                            COUNT(NOP) AS JML,
                            SUM(SPPT_PBB_HARUS_DIBAYAR) AS JUMLAH
                        FROM gw_pbb.pbb_sppt
                        WHERE 1=1 $where $qBuku
                        GROUP BY LEFT(NOP,10)");
    $rows = [];
    while($r = $res->fetch_object()){
        $kel = $r->kel;
        $rows[$kel] = ['JML' => $r->JML, 'JUMLAH' => $r->JUMLAH];
    }
    return $rows;
}

function getReasisasi(){
    global $DBLink, $appConfig, $thn, $qBuku, $eperiode, $eperiode2, $kecamatan, $ketetapan;

    $con = openMysql();
    $where = "";
    if ($thn != "") {
        $where .= " AND SPPT_TAHUN_PAJAK='$thn' ";
    }

    if ($kecamatan != "") {
        $where .= " AND LEFT(NOP,7)='$kecamatan' ";
    }

    $res = $con->query("SELECT 
                            LEFT(NOP,10) AS kel,
                            COUNT(NOP) AS JML,
                            SUM(PBB_TOTAL_BAYAR) AS JUMLAH
                        FROM gw_pbb.pbb_sppt
                        WHERE 
                            PAYMENT_FLAG='1' 
                            AND DATE(LEFT(PAYMENT_PAID,10))>='$eperiode'
                            AND DATE(LEFT(PAYMENT_PAID,10))<='$eperiode2' 
                            $where $qBuku 
                        GROUP BY LEFT(NOP,10)");
    $rows = [];
    while($r = $res->fetch_object()){
        $kel = $r->kel;
        $rows[$kel] = ['JML' => $r->JML, 'JUMLAH' => $r->JUMLAH];
    }
    return $rows;
}
/*================================ EOL =============================================== */

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
$objPHPExcel->getActiveSheet()->setShowGridlines(false);
$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Courier New')->setBold(true);

/*============== CENTERING =========================================================== */
$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('D6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('D6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('F6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('I6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('I6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
/*============== EOL ================================================================= */

/*============== RATA KANAN ========================================================== */
$objPHPExcel->getActiveSheet()->getStyle('D7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('E7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('F7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('G7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('H7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('I7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('J7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('K7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
/*============== EOL ================================================================= */

/*============== SETTING BORDER ====================================================== */
applyBorders($objPHPExcel, 'B5:K5');
applyBorders($objPHPExcel, 'B8:K8');
applyBorders($objPHPExcel, 'B10:K10');
/*============== COLUMN DIMENSION===================================================== */
$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
/*============== EOL ================================================================= */

/*============== ROW 2-6 ============================================================= */
$strTahun = ($thn!='') ? "TAHUN $thn" : "";

$strBuku = "-";
if($buku!=0){
    $strBuku = [];
    for ($i=0; $i < strlen($buku); $i++) { 
        $strBuku[] = substr($buku,$i,1);
    }
    $strBuku = implode(', ',$strBuku);
}

$objPHPExcel->getActiveSheet()->setCellValue('B2', "EVALUASI PENERIMAAN PBB PERKELURAHAN $strTahun" . PHP_EOL . 'KABUPATEN PESAWARAN');
$objPHPExcel->getActiveSheet()->setCellValue('B3', "Tanggal $eperiode s/d $eperiode2");
$objPHPExcel->getActiveSheet()->setCellValue('B4', "Ketetapan Golongan Buku $strBuku");
$objPHPExcel->getActiveSheet()->setCellValue('B6', "KECAMATAN/".PHP_EOL."KELURAHAN");
$objPHPExcel->getActiveSheet()->setCellValue('D6', "POKOK KETETAPAN".PHP_EOL."TAHUN $thn");
$objPHPExcel->getActiveSheet()->setCellValue('F6', "REALISASI POKOK KETETAPAN".PHP_EOL."TAHUN $thn");
$objPHPExcel->getActiveSheet()->setCellValue('I6', "SISA POKOK KETETAPAN".PHP_EOL."TAHUN $thn");
/*============== EOL ================================================================= */

/*============== ROW 7 =============================================================== */
$objPHPExcel->getActiveSheet()->setCellValue('D7', "SPPT");
$objPHPExcel->getActiveSheet()->setCellValue('E7', "JUMLAH (RP)");
$objPHPExcel->getActiveSheet()->setCellValue('F7', "SPPT");
$objPHPExcel->getActiveSheet()->setCellValue('G7', "JUMLAH POKOK PENERIMAAN (Rp)");
$objPHPExcel->getActiveSheet()->setCellValue('H7', "%");
$objPHPExcel->getActiveSheet()->setCellValue('I7', "SPPT");
$objPHPExcel->getActiveSheet()->setCellValue('J7', "JUMLAH SISA POKOK (Rp)");
$objPHPExcel->getActiveSheet()->setCellValue('K7', "%");
/*============== EOL ================================================================= */

/*============== ROW 8 =============================================================== */
$objPHPExcel->getActiveSheet()->setCellValue('G8', "POKOK");
$objPHPExcel->getActiveSheet()->getStyle('G8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
/*============== EOL ================================================================= */

/*============== DATA POKOK KETETAPAN ================================================ */

$getkelurahan = getKelurahan();
// header('Content-Type: application/json; charset=utf-8');
// die(json_encode($getkelurahan));

$n = 9;
$style = new PHPExcel_Style();
$style->getFont()->setBold(false); // Set teks tidak bold


$ket = getKetetapan();
$rea = getReasisasi();

$totketsppt = 0;
$totketpbb  = 0; 
$totrealsppt= 0;
$totrealpbb = 0; 
$totsisasppt= 0;
$totsisapbb = 0; 

foreach ($getkelurahan as $idkec=>$dKec) {
    $kode_prov = substr($idkec, 0, 2);
    $kode_kab  = substr($idkec, 2, 2);
    $kode_kec  = substr($idkec, 4, 3);
    // Menggabungkan kode dengan titik di antara setiap bagian
    $kode_terpisah = $kode_prov . '.' . $kode_kab . '.' . $kode_kec;
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $n, '(' . $kode_terpisah . ')' . $dKec[0]['namakec']);
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $n . ':C' . $n);
    $n++;
    $n++;

    $jmlketsppt = 0;
    $jmlketpbb  = 0; 
    $jmlrealsppt= 0;
    $jmlrealpbb = 0; 
    $jmlsisasppt= 0;
    $jmlsisapbb = 0;
    
    foreach ($dKec as $r) {
        $kel        = $r['kel'];
        $urut       = substr($kel, -3);
        $ketsppt    = isset($ket[$kel]) ? $ket[$kel]['JML'] : 0;
        $ketpbb     = isset($ket[$kel]) ? $ket[$kel]['JUMLAH'] : 0;
        $realsppt   = isset($rea[$kel]) ? $rea[$kel]['JML'] : 0;
        $realpbb    = isset($rea[$kel]) ? $rea[$kel]['JUMLAH'] : 0;
        $realPersen = ($ketpbb!=0 && $realpbb!=0) ? ($realpbb/$ketpbb)*100 : 0;
        $sisasppt   = $ketsppt - $realsppt;
        $sisapbb    = $ketpbb - $realpbb;
        $sisaPersen = ($ketpbb!=0 && $sisapbb!=0) ? ($sisapbb/$ketpbb)*100 : 0;

        $objPHPExcel->getActiveSheet()->setCellValue('B' . $n, '(' . $urut . ')' . $r['namakel']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $n, $ketsppt);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $n, $ketpbb);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $n, $realsppt);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $n, $realpbb);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $n, $realPersen);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $n, $sisasppt);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $n, $sisapbb);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $n, $sisaPersen);

        $jmlketsppt = $jmlketsppt  + $ketsppt;
        $jmlketpbb  = $jmlketpbb   + $ketpbb; 
        $jmlrealsppt= $jmlrealsppt + $realsppt;
        $jmlrealpbb = $jmlrealpbb  + $realpbb; 
        $jmlsisasppt= $jmlsisasppt + $sisasppt;
        $jmlsisapbb = $jmlsisapbb  + $sisapbb;

        $totketsppt = $totketsppt  + $ketsppt;
        $totketpbb  = $totketpbb   + $ketpbb; 
        $totrealsppt= $totrealsppt + $realsppt;
        $totrealpbb = $totrealpbb  + $realpbb; 
        $totsisasppt= $totsisasppt + $sisasppt;
        $totsisapbb = $totsisapbb  + $sisapbb;

        $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('F' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('H' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('I' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('J' . $n)->getFont()->setBold(false);
        $objPHPExcel->getActiveSheet()->getStyle('K' . $n)->getFont()->setBold(false);
        /*---------------------- SETING NUMBER FORMAT ------------------------------------ */
        $objPHPExcel->getActiveSheet()->getStyle("D$n:G$n")->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
        $objPHPExcel->getActiveSheet()->getStyle('H' . $n)->getNumberFormat()->setFormatCode('#,##0.0;-#,##0.0;0');
        $objPHPExcel->getActiveSheet()->getStyle("I$n:J$n")->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
        $objPHPExcel->getActiveSheet()->getStyle('K' . $n)->getNumberFormat()->setFormatCode('#,##0.0;-#,##0.0;0');
        /*-------------------------------------------------------------------------------- */    
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $n . ':C' . $n);
        $n++;
    }

    $realPersenJml = ($jmlketpbb!=0 && $jmlrealpbb!=0) ? ($jmlrealpbb/$jmlketpbb)*100 : 0;
    $sisaPersenJml = ($jmlketpbb!=0 && $jmlsisapbb!=0) ? ($jmlsisapbb/$jmlketpbb)*100 : 0;
    
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $n, 'JUMLAH');
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $n, $jmlketsppt);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $n, $jmlketpbb);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $n, $jmlrealsppt);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $n, $jmlrealpbb);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $n, $realPersenJml);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $n, $jmlsisasppt);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $n, $jmlsisapbb);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $n, $sisaPersenJml);

    $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('F' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('H' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('I' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('J' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('K' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $n . ':C' . $n);
    /*---------------------- SETING NUMBER FORMAT ------------------------------------ */    
    $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('H' . $n)->getNumberFormat()->setFormatCode('#,##0.0;-#,##0.0;0');
    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('J' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('K' . $n)->getNumberFormat()->setFormatCode('#,##0.0;-#,##0.0;0');
    /*-------------------------------------------------------------------------------- */
    applyBorders($objPHPExcel,'B' . $n . ':K' . $n);
    $n += 2;
    applyBorders($objPHPExcel,'B' . ($n+1) . ':K' . ($n+1));
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $n . ':C' . $n);

    $n++;
}

if ($kecamatan == "") {
    $realPersenTot = ($totketpbb!=0 && $totrealpbb!=0) ? ($totrealpbb/$totketpbb)*100 : 0;
    $sisaPersenTot = ($totketpbb!=0 && $totsisapbb!=0) ? ($totsisapbb/$totketpbb)*100 : 0;

    $objPHPExcel->getActiveSheet()->setCellValue('B' . $n, 'TOTAL');
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $n, $totketsppt);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $n, $totketpbb);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $n, $totrealsppt);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $n, $totrealpbb);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $n, $realPersenTot);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $n, $totsisasppt);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $n, $totsisapbb);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $n, $sisaPersenTot);

    $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('F' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('H' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('I' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('J' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->getStyle('K' . $n)->getFont()->setBold(false);
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $n . ':C' . $n);
    /*---------------------- SETING NUMBER FORMAT ------------------------------------ */    
    $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('H' . $n)->getNumberFormat()->setFormatCode('#,##0.0;-#,##0.0;0');
    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('J' . $n)->getNumberFormat()->setFormatCode('_(""* #,##0_);_(""* \(#,##0\);_(""* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle('K' . $n)->getNumberFormat()->setFormatCode('#,##0.0;-#,##0.0;0');
    /*-------------------------------------------------------------------------------- */
    applyBorders($objPHPExcel,'B' . $n . ':K' . $n);
    $n += 2;
    applyBorders($objPHPExcel,'B' . ($n+1) . ':K' . ($n+1));
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $n . ':C' . $n);
}
/*============== EOL ================================================================= */

/*============== WORD WRAP =========================================================== */
$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('F6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('I6')->getAlignment()->setWrapText(true);
/*============== EOL ================================================================= */

/*============== MERGE CELL ========================================================== */
$objPHPExcel->getActiveSheet()->mergeCells('B2:K2');
$objPHPExcel->getActiveSheet()->mergeCells('B3:E3');
$objPHPExcel->getActiveSheet()->mergeCells('B4:E4');
$objPHPExcel->getActiveSheet()->mergeCells('D6:E6');
$objPHPExcel->getActiveSheet()->mergeCells('F6:H6');
$objPHPExcel->getActiveSheet()->mergeCells('I6:J6');
/*============== EOL ================================================================= */

/*

if ($kecamatan == '') {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('RANGKING');
    $objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A5:C5');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText($appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA']);
    $objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A5:C6')->applyFromArray(
        array(
            'font' => array('italic' => true, 'size' => $fontSizeHeader),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        )
    );
} else {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KECAMATAN : ' . $nama);
    $objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
    $objPHPExcel->getActiveSheet()->getStyle('A5:D6')->applyFromArray(
        array(
            'font' => array('italic' => false, 'size' => $fontSizeHeader),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
        )
    );
}

// Header Of Table
if ($kecamatan == "") {
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('NO');
    $objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A8:A9');

    $objRichText = new PHPExcel_RichText();
    if ($kecamatan == "") {
        $objRichText->createText('KECAMATAN');
    } else {
        $objRichText->createText('NO');
    }

    $objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
    $objRichText = new PHPExcel_RichText();

    if ($kecamatan == "") {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
    } else {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
    }

    $objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('C8:C9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('D8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('D8:E8');
    $objPHPExcel->getActiveSheet()->setCellValue('D9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('E9', 'RP');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI BULAN LALU (RP)');
    $objPHPExcel->getActiveSheet()->getCell('F8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('F8:I8');
    $objPHPExcel->getActiveSheet()->setCellValue('F9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('G9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('H9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('I9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('J8:J9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI BULAN INI (RP)');
    $objPHPExcel->getActiveSheet()->getCell('K8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('K8:N8');
    $objPHPExcel->getActiveSheet()->setCellValue('K9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('L9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('M9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('N9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI s/d BULAN INI (RP)');
    $objPHPExcel->getActiveSheet()->getCell('O8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('O8:R8');
    $objPHPExcel->getActiveSheet()->setCellValue('O9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('P9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('Q9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('R9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('S8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('S8:S9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('SISA KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('T8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('T8:U8');
    $objPHPExcel->getActiveSheet()->setCellValue('T9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('U9', 'RP');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('V8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('V8:V9');
} else {
    $objRichText = new PHPExcel_RichText();
    if ($kecamatan == "") {
        $objRichText->createText('KECAMATAN');
    } else {
        $objRichText->createText('NO');
    }

    $objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
    $objRichText = new PHPExcel_RichText();

    if ($kecamatan == "") {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
    } else {
        $objRichText->createText(strtoupper($appConfig['LABEL_KELURAHAN']));
    }

    $objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('C8:D8');
    $objPHPExcel->getActiveSheet()->setCellValue('C9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('D9', 'RP');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI BULAN LALU (RP)');
    $objPHPExcel->getActiveSheet()->getCell('E8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('E8:H8');
    $objPHPExcel->getActiveSheet()->setCellValue('E9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('F9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('G9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('H9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('I8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('I8:I9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI BULAN INI (RP)');
    $objPHPExcel->getActiveSheet()->getCell('J8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('J8:M8');
    $objPHPExcel->getActiveSheet()->setCellValue('J9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('K9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('L9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('M9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('REALISASI s/d BULAN INI (RP)');
    $objPHPExcel->getActiveSheet()->getCell('N8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('N8:Q8');
    $objPHPExcel->getActiveSheet()->setCellValue('N9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('O9', 'POKOK');
    $objPHPExcel->getActiveSheet()->setCellValue('P9', 'DENDA');
    $objPHPExcel->getActiveSheet()->setCellValue('Q9', 'TOTAL');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('R8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('R8:R9');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('SISA KETETAPAN');
    $objPHPExcel->getActiveSheet()->getCell('S8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('S8:T8');
    $objPHPExcel->getActiveSheet()->setCellValue('S9', 'WP');
    $objPHPExcel->getActiveSheet()->setCellValue('T9', 'RP');
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText('%');
    $objPHPExcel->getActiveSheet()->getCell('U8')->setValue($objRichText);
    $objPHPExcel->getActiveSheet()->mergeCells('U8:U9');
}


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$end = "";
if ($kecamatan == '') {
    $end = "A8:V9";
} else {
    $end = "A8:U9";
}
$objPHPExcel->getActiveSheet()->getStyle($end)->applyFromArray(
    array(
        'font' => array(
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

$objPHPExcel->getActiveSheet()->getStyle('A1:P50')->applyFromArray(
    array(
        'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

//Set column widths
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(8);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no = 0;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

if ($kecamatan == "") {

    $noKec = 1;
    foreach ($data as $buffer) {
        $objPHPExcel->getActiveSheet()->getRowDimension(10 + $no)->setRowHeight(18);
        // data rekap per kelurahan
        $dtnamekec = $buffer['namekec'];
        if ($no != 0) {

            if ($dtnamekec == $tempdtnamekec) {
                $dtnamekec = "";
                //hitung total jumlah jumlah sagala macem.
            } else { // ganti kecamatan
                //tulis jumlah heula kecamatan sebelumnya.
                $dtnamekec = $buffer['namekec'];
                $tempdtnamekec = $dtnamekec;
            }
        } else {
            $tempdtnamekec = $dtnamekec;
        }

        if ($buffer['name'] == "JUMLAH") { //summary per kelurahan
			$percent1s = ($buffer['ketetapan_rps'] != 0 && $buffer['rbl_totals'] != 0) ? ($buffer['rbl_totals']/$buffer['ketetapan_rps'] * 100) : 0;
			$percent2s = ($buffer['ketetapan_rps'] != 0 && $buffer['kom_rbi_totals'] != 0) ? ($buffer['kom_rbi_totals'] / $buffer['ketetapan_rps'] * 100) : 0;
			$percent3s = ($buffer['sk_rps'] != 0 && $buffer['ketetapan_rps'] != 0) ? ($buffer['sk_rps'] / $buffer['ketetapan_rps'] * 100) : 0;
			
            $objPHPExcel->getActiveSheet()->mergeCells('B' . (10 + $no) . ':C' . (10 + $no));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['ketetapan_rps']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_pokoks']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_dendas']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['rbl_totals']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), number_format($percent1s, 2, ',', '.'));
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_pokoks']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_dendas']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['rbi_totals']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . (10 + $no), $buffer['kom_rbi_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . (10 + $no), $buffer['kom_rbi_pokoks']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . (10 + $no), $buffer['kom_rbi_dendas']);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . (10 + $no), $buffer['kom_rbi_totals']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . (10 + $no), number_format($percent2s, 2, ',', '.'));
            $objPHPExcel->getActiveSheet()->setCellValue('T' . (10 + $no), $buffer['sk_wps']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . (10 + $no), $buffer['sk_rps']);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . (10 + $no), number_format($percent3s, 2, ',', '.'));
            $objPHPExcel->getActiveSheet()->getStyle('C' . (10 + $no))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        } else if ($buffer['name'] == 'TOTAL') { //summary tota

            $objPHPExcel->getActiveSheet()->mergeCells('B' . (10 + $no) . ':C' . (10 + $no));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['ketetapan_rp']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['rbl_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent1']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . (10 + $no), $buffer['kom_rbi_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . (10 + $no), $buffer['kom_rbi_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . (10 + $no), $buffer['kom_rbi_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . (10 + $no), $buffer['kom_rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . (10 + $no), $buffer['percent2']);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . (10 + $no), $buffer['sk_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . (10 + $no), $buffer['sk_rp']);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . (10 + $no), $buffer['percent3']);
        } else {
            $nomor = ($dtnamekec != "") ? $noKec : "";
            if ($dtnamekec != "") {
                $noKec++;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), $nomor);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $dtnamekec);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_wp'])->getStyle('C' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['ketetapan_rp'])->getStyle('D' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_wp'])->getStyle('E' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_pokok'])->getStyle('F' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_denda'])->getStyle('G' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['rbl_total'])->getStyle('H' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['percent1'])->getStyle('I' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_wp'])->getStyle('J' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_pokok'])->getStyle('K' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_denda'])->getStyle('L' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['rbi_total'])->getStyle('M' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . (10 + $no), $buffer['kom_rbi_wp'])->getStyle('N' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . (10 + $no), $buffer['kom_rbi_pokok'])->getStyle('O' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . (10 + $no), $buffer['kom_rbi_denda'])->getStyle('P' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . (10 + $no), $buffer['kom_rbi_total'])->getStyle('Q' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . (10 + $no), $buffer['percent2'])->getStyle('R' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . (10 + $no), $buffer['sk_wp'])->getStyle('S' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . (10 + $no), $buffer['sk_rp'])->getStyle('T' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . (10 + $no), $buffer['percent3'])->getStyle('U' . (10 + $no))->applyFromArray($noBold);
        }

        $no++;
    }
} else {
    foreach ($data as $buffer) {
        $objPHPExcel->getActiveSheet()->getRowDimension(10 + $no)->setRowHeight(18);

        if ($buffer['name'] == 'JUMLAH') {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), "");
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $buffer['ketetapan_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_rp']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['rbl_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['percent1']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['rbi_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['kom_rbi_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . (10 + $no), $buffer['kom_rbi_pokok']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . (10 + $no), $buffer['kom_rbi_denda']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . (10 + $no), $buffer['kom_rbi_total']);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . (10 + $no), $buffer['percent2']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . (10 + $no), $buffer['sk_wp']);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . (10 + $no), $buffer['sk_rp']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . (10 + $no), $buffer['percent3']);
        } else {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), $no + 1);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (10 + $no), $buffer['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (10 + $no), $buffer['ketetapan_wp'])->getStyle('C' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (10 + $no), $buffer['ketetapan_rp'])->getStyle('D' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (10 + $no), $buffer['rbl_wp'])->getStyle('E' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (10 + $no), $buffer['rbl_pokok'])->getStyle('F' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (10 + $no), $buffer['rbl_denda'])->getStyle('G' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (10 + $no), $buffer['rbl_total'])->getStyle('H' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (10 + $no), $buffer['percent1'])->getStyle('I' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (10 + $no), $buffer['rbi_wp'])->getStyle('J' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (10 + $no), $buffer['rbi_pokok'])->getStyle('K' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (10 + $no), $buffer['rbi_denda'])->getStyle('L' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . (10 + $no), $buffer['rbi_total'])->getStyle('M' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . (10 + $no), $buffer['kom_rbi_wp'])->getStyle('N' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . (10 + $no), $buffer['kom_rbi_pokok'])->getStyle('O' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . (10 + $no), $buffer['kom_rbi_denda'])->getStyle('P' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . (10 + $no), $buffer['kom_rbi_total'])->getStyle('Q' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . (10 + $no), $buffer['percent2'])->getStyle('R' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . (10 + $no), $buffer['sk_wp'])->getStyle('S' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . (10 + $no), $buffer['sk_rp'])->getStyle('T' . (10 + $no))->applyFromArray($noBold);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . (10 + $no), $buffer['percent3'])->getStyle('U' . (10 + $no))->applyFromArray($noBold);
        }
        $no++;
    }
}

$tblEnd = "";
if ($kecamatan == '') {
    $tblEnd = "A10:V";
} else {
    $tblEnd = "A10:U";
}
$objPHPExcel->getActiveSheet()->setCellValue('A' . (10 + $no), '');
$objPHPExcel->getActiveSheet()->getStyle($tblEnd . (9 + count($data)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
);
$objPHPExcel->getActiveSheet()->getStyle('A10:A' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('C10:F' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('G10:G' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('H10:K' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('L10:L' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('M10:N' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('O10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('P10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('Q10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('R10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('S10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('T10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$objPHPExcel->getActiveSheet()->getStyle('U10:O' . (9 + count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_KOTA_PENGESAHAN'] . ', ' . strtoupper($bulan[date('m') - 1]) . ' ' . $thn);
$objPHPExcel->getActiveSheet()->getCell('I' . (11 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (11 + count($data)) . ':K' . (11 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['PEJABAT_SK2']);
$objPHPExcel->getActiveSheet()->getCell('I' . (12 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (12 + count($data)) . ':K' . (12 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['C_KABKOT'] . " " . $appConfig['NAMA_KOTA']);
$objPHPExcel->getActiveSheet()->getCell('I' . (13 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (13 + count($data)) . ':K' . (13 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_PEJABAT_SK2']);
$objPHPExcel->getActiveSheet()->getCell('I' . (17 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (17 + count($data)) . ':K' . (17 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText($appConfig['NAMA_PEJABAT_SK2_JABATAN']);
$objPHPExcel->getActiveSheet()->getCell('I' . (18 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (18 + count($data)) . ':K' . (18 + count($data)));
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NIP. ' . $appConfig['NAMA_PEJABAT_SK2_NIP']);
$objPHPExcel->getActiveSheet()->getCell('I' . (19 + count($data)))->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('I' . (19 + count($data)) . ':K' . (19 + count($data)));

$objPHPExcel->getActiveSheet()->getStyle('I' . (17 + count($data)) . ':K' . (17 + count($data)));
$objPHPExcel->getActiveSheet()->getStyle('I' . (11 + count($data)) . ':K' . (19 + count($data)))->applyFromArray(
    array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
*/
//Redirect output to a client’s web browser (Excel5)
$uniq = substr(uniqid(),-5);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="realisasi_pbb_'.$uniq.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;