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
	//var_dump($tahun, $thnTagihan, $table);die;
	
    $return = array();
    $return["RESULT"] = 0;
    $default = array("NJOP_AKTIF" => 0);
    $queryKecamatan = "SELECT
							a.CPC_TKL_ID,
							a.CPC_TKL_KELURAHAN,
							b.CPC_TKC_ID,
							b.CPC_TKC_KECAMATAN 
						FROM
							cppmod_tax_kelurahan a
							INNER JOIN cppmod_tax_kecamatan b ON a.CPC_TKL_KCID = b.CPC_TKC_ID 
						ORDER BY
							b.CPC_TKC_ID;";
    $res = mysqli_query($DBLink, $queryKecamatan);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return[$row["CPC_TKL_ID"]]             = $default;
        $return[$row["CPC_TKL_ID"]]["KEC"]    = $row['CPC_TKC_KECAMATAN'];
		$return[$row["CPC_TKL_ID"]]["KEL"]    = $row['CPC_TKL_KELURAHAN'];
    }
	
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
	
    $query = "SELECT OP_KECAMATAN_KODE, OP_KELURAHAN_KODE, COUNT(*) AS JUMLAH FROM {$table}
                WHERE NOP != '' {$qBuku}
                GROUP BY OP_KELURAHAN_KODE
                ORDER BY OP_KELURAHAN_KODE ASC;";
				
	//var_dump($query);die;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo mysqli_error($DBLink);
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $return[$row["OP_KELURAHAN_KODE"]]["JUMLAH_AKTIF"]    = $row["JUMLAH"];
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

$User         = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig     = $User->GetAppConfig($a);
$tahun        = $_GET['tahun'];
$buku = @isset($_GET['buku']) ? $_GET['buku'] : 0;

$data = getData();

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
$objPHPExcel->getActiveSheet()->setCellValue('A1', 'REKAPITULASI OBJEK PAJAK');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'PAJAK BUMI DAN BANGUNAN TAHUN ' . $tahun);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');
$objPHPExcel->getActiveSheet()->mergeCells('B5:B6');
$objPHPExcel->getActiveSheet()->mergeCells('C5:C6');
$objPHPExcel->getActiveSheet()->mergeCells('D5:D6');
$objPHPExcel->getActiveSheet()->mergeCells('E5:F5');
$objPHPExcel->getActiveSheet()->mergeCells('G5:G6');

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A5', 'NO')
    ->setCellValue('B5', 'DESA')
    ->setCellValue('C5', "KECAMATAN")
    ->setCellValue('D5', "JUMLAH WP")
    ->setCellValue('E5', "STATUS");

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('E6', 'PROSES')
    ->setCellValue('F6', 'SELESAI');

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$kec = '';
$end = 0;
$starts = 0;
//var_dump('C'.($start).':C'.($row));die;
foreach ($data as $key => $value) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($row - 6));
    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $value['KEL']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $value['JUMLAH_AKTIF']);
	
	if($kec != $value['KEC']){
		$starts = $end;
		$end = $row;
		//echo $starts, $end, '</br>';

		$objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $value['KEC']);
		if($end != 0 && $starts != 0){
			$objPHPExcel->getActiveSheet()->mergeCells('C'.($starts).':C'.($end-1));
			
		}
	}
	
	
	//if($kec == $value['KEC']){
	//	$start = $row;
	//}
	
	$kec = $value['KEC'];
    $row++;
}
//echo $starts, $end, ' ',$row,'</br>';
$objPHPExcel->getActiveSheet()->mergeCells('C'.($end).':C'.($row-1));
//exit;

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
$objPHPExcel->getActiveSheet()->getStyle('A5:F6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:F' . (count($data) + 5))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('C7:C' . ($row))->applyFromArray(
    array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:F6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:F6')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
$objPHPExcel->getActiveSheet()->getStyle('C5:F6')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D5:F6')->getAlignment()->setWrapText(true);
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Statistik-NJOPV2-' . date('d-m-Y') . '.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
