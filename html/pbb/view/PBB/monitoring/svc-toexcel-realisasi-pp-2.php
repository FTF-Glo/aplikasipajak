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
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s,$qBuku,$eperiode;
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
		$targetPedesaan 	= getTargetPedesaan($kec[$i]["id"],$eperiode);
		$targetPerkotaan	= getTargetPerkotaan($kec[$i]["id"],$eperiode);
		$realisasiPedesaan	= getRealisasiPedesaan($kec[$i]["id"],$eperiode);
		$realisasiPerkotaan	= getRealisasiPerkotaan($kec[$i]["id"],$eperiode);
		
		$data[$i]["TARGET_PEDESAAN"] 		= $targetPedesaan["TARGET"];
		$data[$i]["TARGET_PERKOTAAN"] 		= $targetPerkotaan["TARGET"];
		$data[$i]["REALISASI_PEDESAAN"] 	= $realisasiPedesaan["REALISASI"];
		$data[$i]["REALISASI_PERKOTAAN"] 	= $realisasiPerkotaan["REALISASI"];
		
	}
	
	return $data;
}

function getTargetPedesaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan, $appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["TARGET"]=0;
	$db_gw = $appConfig['ADMIN_GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	
	$qPeriode="";
	if($periode!=0){
		$qPeriode = " AND SPPT_TANGGAL_TERBIT BETWEEN '{$firstMon}' AND '{$lastMon}' ";
	}
	
	$query = "SELECT
					SUM(A.SPPT_PBB_HARUS_DIBAYAR) AS TARGET
				FROM
					{$db_gw}.PBB_SPPT A
				JOIN VSI_SWITCHER_DEVEL.cppmod_tax_kelurahan B
				JOIN VSI_SWITCHER_DEVEL.cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '10' $qPeriode "; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["TARGET"]	=($row["TARGET"]!="")?$row["TARGET"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getTargetPerkotaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan,$appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["TARGET"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	
	$qPeriode="";
	if($periode!=0){
		$qPeriode = " AND SPPT_TANGGAL_TERBIT BETWEEN '{$firstMon}' AND '{$lastMon}' ";
	}
	
	$query = "SELECT
					SUM(A.SPPT_PBB_HARUS_DIBAYAR) AS TARGET
				FROM
					{$db_gw}.PBB_SPPT A
				JOIN VSI_SWITCHER_DEVEL.cppmod_tax_kelurahan B
				JOIN VSI_SWITCHER_DEVEL.cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND SPPT_TAHUN_PAJAK = '{$thn}'
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '20' $qPeriode ";
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["TARGET"]	=($row["TARGET"]!="")?$row["TARGET"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getRealisasiPedesaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan, $appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["REALISASI"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	
	$qPeriode="";
	if($periode!=0){
		$qPeriode = " AND PAYMENT_PAID BETWEEN '{$firstMon}' AND '{$lastMon}' ";
	}
	
	$query = "SELECT
					SUM(A.PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.PBB_SPPT A
				JOIN VSI_SWITCHER_DEVEL.cppmod_tax_kelurahan B
				JOIN VSI_SWITCHER_DEVEL.cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '10' 
				AND PAYMENT_FLAG = '1' $qPeriode "; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["REALISASI"]	=($row["REALISASI"]!="")?$row["REALISASI"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function getRealisasiPerkotaan($kel,$periode) {
	global $myDBLink,$kd,$thn,$bulan,$appConfig;

	$myDBLink = openMysql();
	$return=array();
	$return["REALISASI"]=0;
	$db_gw = $appConfig['GW_DBNAME'];
	
	$firstMon = firstDay('01', $thn);//Ambil tanggal awal bulan
	$lastMon = lastDay($periode, $thn);//Ambil tanggal akhir bulan
	
	$qPeriode="";
	if($periode!=0){
		$qPeriode = " AND PAYMENT_PAID BETWEEN '{$firstMon}' AND '{$lastMon}' ";
	}
	
	$query = "SELECT
					SUM(A.PBB_TOTAL_BAYAR) AS REALISASI
				FROM
					{$db_gw}.PBB_SPPT A
				JOIN VSI_SWITCHER_DEVEL.cppmod_tax_kelurahan B
				JOIN VSI_SWITCHER_DEVEL.cppmod_pbb_jns_sektor C
				WHERE
					A.OP_KELURAHAN_KODE = B.CPC_TKL_ID
				AND B.CPC_TKL_KDSEKTOR = C.CPC_KD_SEKTOR
				AND NOP LIKE '{$kel}%'
				AND CPC_KD_SEKTOR = '20' 
				AND PAYMENT_FLAG = '1' $qPeriode "; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["REALISASI"]	=($row["REALISASI"]!="")?$row["REALISASI"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

function lastDay($month = '', $year = '') {
   if (empty($month)) {
      $month = date('m');
   }
   if (empty($year)) {
      $year = date('Y');
   }
   $result = strtotime("{$year}-{$month}-01");
   $result = strtotime('-1 second', strtotime('+1 month', $result));
   return date('Y-m-d', $result).' 23:59:59';
}

//get tanggal awal pada bulan
function firstDay($month = '', $year = '')
{
   if (empty($month)) {
      $month = date('m');
   }
   if (empty($year)) {
      $year = date('Y');
   }
   $result = strtotime("{$year}-{$month}-01");
   return date('Y-m-d', $result).' 00:00:00';
} 

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt 		= getData($mod);
	$data		= array();
	$c 			= count($dt);
	// $summary = array('name'=>'TOTAL','sppt'=>0, 'dhkp'=>0, 'terhutang'=>0);
        for ($i=0;$i<$c;$i++) {
				
				$dtname 		= $dt[$i]["name"];
				
				$t_pedesaan		= $dt[$i]["TARGET_PEDESAAN"];	
				$t_perkotaan	= $dt[$i]["TARGET_PERKOTAAN"];	
				$r_pedesaan		= $dt[$i]["REALISASI_PEDESAAN"];	
				$r_perkotaan	= $dt[$i]["REALISASI_PERKOTAAN"];
				
				$tmp = array(
					'KECAMATAN' 	=> $dtname,
					'T_PEDESAAN' 	=> $t_pedesaan,
					'T_PERKOTAAN' 	=> $t_perkotaan,
					'R_PEDESAAN' 	=> $r_pedesaan,
					'R_PERKOTAAN' 	=> $r_perkotaan
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
$eperiode 	= @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";

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

// if ($kecamatan=="") { 
	$data =  showTable ();
//} 
// else {
	// $data =  showTable(1,$nama);
// }

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
$objPHPExcel->getActiveSheet()->mergeCells('A2:I2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:I3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' REALISASI PEDESAAN DAN PERKOTAAN ');
$objPHPExcel->getActiveSheet()->setCellValue('A3', ' TAHUN '.$thn.' ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//Merge Header
$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');
$objPHPExcel->getActiveSheet()->mergeCells('B5:B6');
$objPHPExcel->getActiveSheet()->mergeCells('C5:D5');
$objPHPExcel->getActiveSheet()->mergeCells('E5:E6');
$objPHPExcel->getActiveSheet()->mergeCells('F5:G5');
$objPHPExcel->getActiveSheet()->mergeCells('E5:E6');
$objPHPExcel->getActiveSheet()->mergeCells('H5:H6');
$objPHPExcel->getActiveSheet()->mergeCells('I5:I6');

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A5', ' NO ')
            ->setCellValue('B5', ' KECAMATAN ')
            ->setCellValue('C5', " TARGET POTENSI ")
			->setCellValue('C6', " PEDESAAN ")
			->setCellValue('D6', " PERKOTAAN ")
            ->setCellValue('E5', " JUMLAH")
			->setCellValue('F5', " REALISASI ")
			->setCellValue('F6', " PEDESAAN ")
			->setCellValue('G6', " PERKOTAAN ")
			->setCellValue('H5', " JUMLAH")
			->setCellValue('I5', " % ")
			;

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 7;
$summary = array('SUM_T_PEDESAAN'=>0,'SUM_T_PERKOTAAN'=>0,'GRAND_TOTAL_T'=>0,'SUM_R_PEDESAAN'=>0,'SUM_R_PERKOTAAN'=>0,'GRAND_TOTAL_R'=>0);
for ($i=0;$i<$sumRows;$i++) {
	
	$sumTarget 		= ($data[$i]['T_PEDESAAN']+$data[$i]['T_PERKOTAAN']);
	$sumRealisasi	= ($data[$i]['R_PEDESAAN']+$data[$i]['R_PERKOTAAN']);
	$percent		= ($sumRealisasi != 0 && $sumTarget != 0) ? ($sumRealisasi/$sumTarget*100) : 0;
	
    $objPHPExcel->getActiveSheet()->setCellValue('A'.($row), ($row-6));
	$objPHPExcel->getActiveSheet()->setCellValue('B'.($row), $data[$i]['KECAMATAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('C'.($row), $data[$i]['T_PEDESAAN']);
    $objPHPExcel->getActiveSheet()->setCellValue('D'.($row), $data[$i]['T_PERKOTAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('E'.($row), $sumTarget);
	$objPHPExcel->getActiveSheet()->setCellValue('F'.($row), $data[$i]['R_PEDESAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('G'.($row), $data[$i]['R_PERKOTAAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('H'.($row), $sumRealisasi);
	$objPHPExcel->getActiveSheet()->setCellValue('I'.($row), number_format($percent,2));
    $row++;
	
	$summary['SUM_T_PEDESAAN'] 		+= $data[$i]['T_PEDESAAN'];
	$summary['SUM_T_PERKOTAAN'] 	+= $data[$i]['T_PERKOTAAN'];
	$summary['GRAND_TOTAL_T'] 		+= ($data[$i]['T_PEDESAAN']+$data[$i]['T_PERKOTAAN']);
	
	$summary['SUM_R_PEDESAAN'] 		+= $data[$i]['R_PEDESAAN'];
	$summary['SUM_R_PERKOTAAN'] 	+= $data[$i]['R_PERKOTAAN'];
	$summary['GRAND_TOTAL_R'] 		+= ($data[$i]['R_PEDESAAN']+$data[$i]['R_PERKOTAAN']);
	
	$summary['GRAND_TOTAL_PERCENT'] = ($summary['GRAND_TOTAL_R'] != 0 && $summary['GRAND_TOTAL_T'] != 0) ? ($summary['GRAND_TOTAL_R']/$summary['GRAND_TOTAL_T']*100) : 0;

}

// JUMLAH
$objPHPExcel->getActiveSheet()->mergeCells('A'.$row.':'.'B'.$row);
$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $summary['SUM_T_PEDESAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $summary['SUM_T_PERKOTAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $summary['GRAND_TOTAL_T']);
$objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $summary['SUM_R_PEDESAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('G'.$row, $summary['SUM_R_PERKOTAAN']);
$objPHPExcel->getActiveSheet()->setCellValue('H'.$row, $summary['GRAND_TOTAL_R']);
$objPHPExcel->getActiveSheet()->setCellValue('I'.$row, number_format($summary['GRAND_TOTAL_PERCENT'],2));


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
$objPHPExcel->getActiveSheet()->setTitle('Realisasi Pedesaan Perkotaan');

//----set style cell

//style header
$objPHPExcel->getActiveSheet()->getStyle('A5:I6')->applyFromArray(
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
$objPHPExcel->getActiveSheet()->getStyle('A5:I'.($sumRows+7))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A5:I6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A5:I6')->getFill()->getStartColor()->setRGB('E4E4E4');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Realisasi Pedesaan Perkotaan '.date('d-m-Y').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
