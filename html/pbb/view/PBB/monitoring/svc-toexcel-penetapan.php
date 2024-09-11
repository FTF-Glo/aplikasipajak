<?php
 ini_set('memory_limit', '1024M');
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

//error_reporting(E_ALL);
ini_set('display_errors', 1);

$myDBlink ="";
// koneksi postgres
function openMysql () {
	global $appConfig;
        $host = $appConfig['GW_DBHOST'];
        $port = isset($appConfig['GW_DBPORT'])? $appConfig['GW_DBPORT']:'3306';
        $user = $appConfig['GW_DBUSER'];
        $pass = $appConfig['GW_DBPWD'];
        $dbname = $appConfig['GW_DBNAME']; 
				$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		echo mysqli_error($myDBLink); 
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con){
	mysqli_close($con);
}
	
function getKecamatan($p) {
	global $DBLink;
	$return = array();
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."' ORDER BY CPC_TKC_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKC_ID"];
		$data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
		$i++;
	}
	
	return $data;
}

function getKelurahan($p) {
	global $DBLink;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		 exit();
	}
	$data = array();
	$i=0;
	while ($row = mysqli_fetch_assoc($res )) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}
	return $data;
}

function paging($totalrows) {
		global $page,$perpage;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"showPenetapanPage(".($page-1).")\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"showPenetapanPage(".($page+1).")\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
	
function getCountData($where){
	global $myDBLink;

	$myDBLink 			= openMysql();
	
	$whr = "";
	if($where) {
		$whr = " where {$where}";
	}	
	$qRows = "SELECT COUNT(*) FROM PBB_SPPT {$whr} "; 
	// echo $qRows.'<br/>';
	
	$exec 		= mysqli_query($myDBLink, $qRows);
	$resCount 	= mysqli_fetch_array($exec);
	$totalrows  = $resCount[0];
	if ($exec === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	closeMysql($myDBLink);
	return $totalrows;
}

function getData($where) {
	global $myDBLink,$perpage,$page, $qBuku;

	$myDBLink 			= openMysql();
	$data 				= array();
	$return				= array();
	$return["NOP"]		= "";
	$return["NAMA"]		= "";
	$return["TAGIHAN"]	= 0;
	$hal 				= (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	
	$whr = "";
	if($where) {
		$whr = " where {$where} {$qBuku}";
	}	
	$query = "SELECT NOP, WP_NAMA, SPPT_PBB_HARUS_DIBAYAR FROM PBB_SPPT {$whr} {$qBuku} AND NOP <> '' OR NOP <> NULL "; 
	// echo $query.'<br/>';exit();
	
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		// print_r($row);
		$return["NOP"]		=($row["NOP"]!="")?$row["NOP"]:"";
		$return["NAMA"]		=($row["WP_NAMA"]!="")?$row["WP_NAMA"]:"";
		$return["TAGIHAN"]	=($row["SPPT_PBB_HARUS_DIBAYAR"]!=0)?$row["SPPT_PBB_HARUS_DIBAYAR"]:0;
		$data[] = $return;
	}
	closeMysql($myDBLink);
	return $data;
}

function showTable () {
	global $where,$page,$perpage, $qBuku;
	$dt 		= getData($where);
	// $totalrows 	= getCountData($where);
	$c 			= count($dt);
	$data		= array();
	if($c!=0){
		for($i=0;$i<$c;$i++){
			$nop		= $dt[$i]['NOP'];
			$nama		= $dt[$i]['NAMA'];
			$tagihan	= $dt[$i]['TAGIHAN'];
			$tmp = array(
				'NOP' => $nop,
				'NAMA' => $nama,
				'TAGIHAN' => $tagihan
			);
			$data[] = $tmp;
		}
	} 
		  
	return $data;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);
$kd 		= $appConfig['KODE_KOTA'];
$perpage	= $appConfig['ITEM_PER_PAGE'];

// print_r($appConfig);
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec	= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel	= @isset($_REQUEST['nk']) ? $_REQUEST['nk'] : "";
$buku		= @isset($_REQUEST['bk']) ? $_REQUEST['bk'] : "";

// print_r($_REQUEST);
$lKecamatan = "";
$lKelurahan = "";
$arrWhere = array();
if ($kecamatan !="") {
	if ($kelurahan !=""){
		array_push($arrWhere,"NOP like '{$kelurahan}%'");
		$lKecamatan = "KECAMATAN : ".strtoupper($namaKec);
		$lKelurahan = strtoupper($appConfig['LABEL_KELURAHAN'])." : ".strtoupper($namaKel);
	} else {
		array_push($arrWhere,"NOP like '{$kecamatan}%'");
		$lKecamatan = "KECAMATAN : ".strtoupper($namaKec);
		$lKelurahan = "";
	}
}
if ($thn!=""){
    array_push($arrWhere,"SPPT_TAHUN_PAJAK ='{$thn}'");   
} 
$where = implode (" AND ",$arrWhere);

$qBuku = "";
 if($buku != 0){
 switch ($buku){
 case 1 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
 case 12 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
 case 123 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
 case 1234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 12345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 2 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
 case 23 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
 case 234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 2345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 3 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
 case 34 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 4 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
 case 45 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 case 5 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
 }
 }


$data = showTable ();

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:D2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:D3');
$objPHPExcel->getActiveSheet()->mergeCells('A4:D4');
$objPHPExcel->getActiveSheet()->mergeCells('A5:D5');
$objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
$objPHPExcel->getActiveSheet()->setCellValue('A2', 'MONITORING PENETAPAN ');
$objPHPExcel->getActiveSheet()->setCellValue('A3', 'TAHUN '.$thn.'' );
$objPHPExcel->getActiveSheet()->setCellValue('A5', $lKecamatan);
$objPHPExcel->getActiveSheet()->setCellValue('A6', $lKelurahan);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A7', ' NO ')
            ->setCellValue('B7', ' NOP ')
            ->setCellValue('C7', " NAMA ")
            ->setCellValue('D7', " TAGIHAN ");

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 8;
$summary = array('TOTAL_TAGIHAN'=>0);
for ($i=0;$i<$sumRows;$i++) {
    $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, ($row-7));
    $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, ($data[$i]['NOP']." "));
    $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, ($data[$i]['NAMA']));
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$row, ($data[$i]['TAGIHAN']));
    $row++;
	
	$summary['TOTAL_TAGIHAN'] 		 += $data[$i]['TAGIHAN'];

}

//JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':'.'C'.$row);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $summary['TOTAL_TAGIHAN']);

$objPHPExcel->getActiveSheet()->getStyle('A'.$row)->applyFromArray(
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
$objPHPExcel->getActiveSheet()->setTitle('Monitoring Penetapan');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A7:D7')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A7:D'.($sumRows+8))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A7:D7')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A7:D7')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('B7')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('C7')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D7')->getAlignment()->setWrapText(true);

$objPHPExcel->getActiveSheet()->getStyle('B7:B'.($sumRows+8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Monitoring Penetapan '.date('d-m-Y').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
