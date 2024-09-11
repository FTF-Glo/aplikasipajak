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

function getData($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s,$qBuku;
    
	if ($mod==0) $kec =  getKecamatan($kab);
	else {
		if($kelurahan)
			$kec = getKelurahan($kelurahan);
		else 
			$kec = getKelurahan($kecamatan);
	}
	
	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] 	= $kec[$i]["name"];
		$data[$i]["id"] 	= $kec[$i]["id"];
		$dCount 			= getCount($kec[$i]["id"]);
		
		$data[$i]["SPPT"] 		= $dCount["SPPT"];
		$data[$i]["DHKP"] 		= $dCount["DHKP"];
		$data[$i]["TERHUTANG"]  = $dCount["TERHUTANG"];
	}
	
	return $data;
}

function getCount($kec) {
	global $myDBLink,$kd,$thn,$bulan, $qBuku;

	$myDBLink = openMysql();
	$return=array();
	$return["SPPT"]=0;
	$return["DHKP"]=0;
	$return["TERHUTANG"]=0;
	$query = "SELECT
					COUNT(*) AS SPPT,
					SUM(SPPT_PBB_HARUS_DIBAYAR) AS TERHUTANG,
					COUNT(DISTINCT(OP_KELURAHAN_KODE)) AS DHKP
				FROM
					PBB_SPPT
				WHERE
				SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kec}%' {$qBuku} "; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["SPPT"]		=($row["SPPT"]!="")?$row["SPPT"]:0;
		$return["DHKP"]		=($row["DHKP"]!="")?$row["DHKP"]:0;
		$return["TERHUTANG"]=($row["TERHUTANG"]!="")?$row["TERHUTANG"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

// function getCountDHKPPedesaan($kec) {
	// global $myDBLink,$kd,$thn,$bulan;

	// $myDBLink = openMysql();
	// $return=array();
	// $return["DHKP_PEDESAAN"]=0;
	// $query = "SELECT COUNT(*) AS DHKP_PEDESAAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID = '{$kec}' AND CPC_TKL_KDSEKTOR = '10' "; 
	// // echo $query.'<br/>';
	// $res = mysqli_query($myDBLink, $query);
	// if ($res === false) {
		// echo mysqli_error($DBLink);
		// exit();
	// }
	
	// while ($row = mysqli_fetch_assoc($res)) {
		// //print_r($row);
		// $return["DHKP_PEDESAAN"]=($row["DHKP_PEDESAAN"]!="")?$row["DHKP_PEDESAAN"]:0;
	// }
	// closeMysql($myDBLink);
	// return $return;
// }

// function getCountDHKPPerkotaan($kec) {
	// global $myDBLink,$kd,$thn,$bulan;

	// $myDBLink = openMysql();
	// $return=array();
	// $return["DHKP_PERKOTAAN"]=0;
	// $query = "SELECT COUNT(*) AS DHKP_PERKOTAAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID = '{$kec}' AND CPC_TKL_KDSEKTOR = '20' "; 
	// // echo $query.'<br/>';
	// $res = mysqli_query($myDBLink, $query);
	// if ($res === false) {
		// echo mysqli_error($DBLink);
		// exit();
	// }
	
	// while ($row = mysqli_fetch_assoc($res)) {
		// // print_r($row);
		// $return["DHKP_PERKOTAAN"]=($row["DHKP_PERKOTAAN"]!="")?$row["DHKP_PERKOTAAN"]:0;
	// }
	// closeMysql($myDBLink);
	// return $return;
// }

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt 		= getData($mod);
	$data		= array();
	$c 			= count($dt);
	// $summary = array('name'=>'TOTAL','sppt'=>0, 'dhkp'=>0, 'terhutang'=>0);
        for ($i=0;$i<$c;$i++) {
				
				$dtname 		= $dt[$i]["name"];
				
				$sppt 		= $dt[$i]["SPPT"];
				$dhkp		= $dt[$i]["DHKP"];
				$terhutang 	= $dt[$i]["TERHUTANG"];
				
				
				$tmp = array(
					'KECAMATAN' => $dtname,
					'SPPT' => $sppt,
					'DHKP' => $dhkp,
					'TERHUTANG' => $terhutang
				);
				$data[] = $tmp;
				
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

// print_r($appConfig);EXIT;
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$kab  		= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$kecamatan 	= @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$kelurahan	= @isset($_REQUEST['kl']) ? $_REQUEST['kl'] : "";
$thn 		= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$namaKec	= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$namaKel	= @isset($_REQUEST['nk']) ? $_REQUEST['nk'] : "";
$buku 		= @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";

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

$data = array ();
if ($kecamatan=="") { 
	$data = showTable ();
} else {
	$data = showTable(1,$namaKec);
}

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' RINCIAN SPPT DAN DHKP TAHUN '.$thn.'');
$objPHPExcel->getActiveSheet()->setCellValue('A3', ' SEKTOR PEDESAAN DAN PERKOTAAN ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A5', ' NO ')
            ->setCellValue('B5', ' KECAMATAN ')
            ->setCellValue('C5', " SPPT\n(LEMBAR) ")
			->setCellValue('D5', " DHKP\n(BUKU) ")
            ->setCellValue('E5', " JUMLAH PAJAK\nTERHUTANG(RP) ");

$objPHPExcel->getActiveSheet()->getStyle('C5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('D5')->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->setWrapText(true);

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$summary = array('T_SPPT'=>0,'T_DHKP'=>0,'T_TERHUTANG'=>0);
for ($i=0;$i<$sumRows;$i++) {
    $objPHPExcel->getActiveSheet()->setCellValue('A'.($row), ($row-5));
	$objPHPExcel->getActiveSheet()->setCellValue('B'.($row), $data[$i]['KECAMATAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('C'.($row), $data[$i]['SPPT']);
    $objPHPExcel->getActiveSheet()->setCellValue('D'.($row), $data[$i]['DHKP']);
	$objPHPExcel->getActiveSheet()->setCellValue('E'.($row), $data[$i]['TERHUTANG']);

    $row++;
	
	$summary['T_SPPT'] 		+= $data[$i]['SPPT'];
	$summary['T_DHKP'] 	 	+= $data[$i]['DHKP'];
	$summary['T_TERHUTANG'] += $data[$i]['TERHUTANG'];

}

// JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':'.'B'.$row);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $summary['T_SPPT']);
$objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $summary['T_DHKP']);
$objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $summary['T_TERHUTANG']);


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
$objPHPExcel->getActiveSheet()->setTitle('Rincian SPPT dan DHKP');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:E'.($sumRows+6))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:E5')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Rincian SPPT dan DHKP '.date('d-m-Y').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
