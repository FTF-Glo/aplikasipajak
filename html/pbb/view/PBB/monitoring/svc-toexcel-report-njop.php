<?php
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
ini_set('display_errors', 0);

function closeMysql($con)
{
    mysqli_close($con);
}
function getData()
{
    global $DBLink, $appConfig, $tahun, $buku;

    $thnTagihan = $appConfig['tahun_tagihan'];
    $return = array();
    $table = ($tahun != $thnTagihan) ? "cppmod_pbb_sppt_cetak_" . $tahun : "cppmod_pbb_sppt_current";
	
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

    $queryKecamatan = "select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,sum(tinggi_bumi) tinggi_bumi,sum(rendah_bumi) rendah_bumi,sum(tinggi_bangunan) tinggi_bangunan,sum(rendah_bangunan) rendah_bangunan from (
	select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,max(OP_NJOP_BUMI/OP_LUAS_BUMI) tinggi_bumi, 0 rendah_bumi, 0 tinggi_bangunan, 0 rendah_bangunan 
	from {$table}
	where OP_NJOP_BUMI is not null and OP_NJOP_BUMI!=0 {$qBuku}
	group by OP_KELURAHAN_KODE,OP_KELURAHAN
	union all
	select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,0 tinggi_bumi,min(OP_NJOP_BUMI/OP_LUAS_BUMI) rendah_bumi, 0 tinggi_bangunan, 0 rendah_bangunan
	from {$table}
	where OP_NJOP_BUMI is not null and OP_NJOP_BUMI!=0 {$qBuku}
	group by OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN
	union all
	select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,0 tinggi_bumi, 0 rendah_bumi, max(OP_NJOP_BANGUNAN/OP_LUAS_BANGUNAN) tinggi_bangunan, 0 rendah_bangunan 
	from {$table}
	where OP_LUAS_BANGUNAN!=0 and OP_NJOP_BANGUNAN!=0 {$qBuku}
	group by OP_KELURAHAN_KODE,OP_KELURAHAN
	union all
	select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,0 tinggi_bumi,0 rendah_bumi, 0 tinggi_bangunan, min(OP_NJOP_BANGUNAN/OP_LUAS_BANGUNAN) rendah_bangunan
	from {$table}
	where OP_LUAS_BANGUNAN!=0 and OP_NJOP_BANGUNAN!=0 {$qBuku}
	group by OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN
	) a 
	group by OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN
	order by OP_KECAMATAN,OP_KELURAHAN";
	//var_dump($queryKecamatan);die;
    $res = mysqli_query($DBLink, $queryKecamatan);
    if ($res === false) {
        //echo mysqli_error($DBLink);
        // echo "Data SPPT untuk tahun {$tahun} tidak tersedia.";
        // exit();
        return $return;
    }

    while ($row = mysqli_fetch_object($res)) {
        $return[] = $row;
    }
    closeMysql($DBLink);
    return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;
$tahun        = $_GET['tahun'];
$buku = @isset($_GET['buku']) ? $_GET['buku'] : 0;
$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig     = $User->GetAppConfig($a);
$data        = getData();

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
$objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
$objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'REKAPITULASI STATISTIK OBJEK PAJAK');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'PAJAK BUMI DAN BANGUNAN TAHUN ' . $tahun);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');
$objPHPExcel->getActiveSheet()->mergeCells('B5:B6');
$objPHPExcel->getActiveSheet()->mergeCells('C5:C6');
$objPHPExcel->getActiveSheet()->mergeCells('D5:E5');
$objPHPExcel->getActiveSheet()->mergeCells('F5:G5');

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'NO')
    ->setCellValue('B5', 'DESA')
    ->setCellValue('C5', "KECAMATAN")
    ->setCellValue('D5', "NILAI NJOP BUMI PERMETER (m2)")
    ->setCellValue('F5', "NILAI NJOP BANGUNAN PERMETER (m2)")
    ->setCellValue('D6', "TERENDAH")
    ->setCellValue('E6', "TERTINGGI")
    ->setCellValue('F6', "TERENDAH")
    ->setCellValue('G6', "TERTINGGI");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 7;
if (count($data) > 0) {
    foreach ($data as $data) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($row - 6));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data->OP_KELURAHAN);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data->OP_KECAMATAN);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data->rendah_bumi);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data->tinggi_bumi);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $data->rendah_bangunan);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $data->tinggi_bangunan);
        $row++;
    }
} else {
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':G' . $row . '');
    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), "Tidak ada data untuk ditampilkan");
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
$objPHPExcel->getActiveSheet()->getStyle('A1:A2')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->setTitle('Rekapitulasi Objek Pajak');


//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:G6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:G' . ($row - 1))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:G6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:G6')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
// $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
$objPHPExcel->getActiveSheet()->getStyle('A5:G5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D6:G6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D7:G7' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
// $objPHPExcel->getActiveSheet()->getColumnDimension('D5')->setAutoSize(true);
// $objPHPExcel->getActiveSheet()->getColumnDimension('F5')->setAutoSize(true);
for ($x = "B"; $x <= "C"; $x++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($x)->setAutoSize(true);
}
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rekapitulasi Objek Pajak ' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
