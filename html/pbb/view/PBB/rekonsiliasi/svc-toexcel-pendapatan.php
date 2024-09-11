<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'rekonsiliasi', '', dirname(__FILE__))) . '/';
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
// koneksi mysql
function openMysql () {
	global $appConfig;
        $host = $appConfig['GW_DBHOST'];
        $port = isset($appConfig['GW_DBPORT'])? $appConfig['GW_DBPORT']:'3306';
        $user = $appConfig['GW_DBUSER'];
        $pass = $appConfig['GW_DBPWD'];
        $dbname = $appConfig['GW_DBNAME']; 
	$myDBLink = mysqli_connect($host, $user, $pass, $dbname ,$port);
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

function getData($mod) {
	global $thn,$bulan,$arrBulan;
	
	// $arrBulan = array(1=>"January",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember");
	if ($mod==0){
		$nama_bulan =  $arrBulan;
		$c 			= count($nama_bulan);
	}else{ 
		$c 			= jumlah_hari($bulan,$thn);
		for($x=0;$x<=$c;$x++){
			$nama_bulan[] = $x;
		}
	}
	
	$data 	= array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["nama_bulan"] = $nama_bulan[$i+1];
		$bulan					= sprintf("%02d", $bulan);
		$tgl					= sprintf("%02d", ($i+1));
		$pendapatan				= getPendapatan($tgl,$bulan,$thn);
		
		$data[$i]["JML_OP"] 	= $pendapatan['JML_OP']; 
		$data[$i]["POKOK"] 		= $pendapatan['POKOK']; 
		$data[$i]["DENDA"] 		= $pendapatan['DENDA']; 
		$data[$i]["TOTAL"] 		= $pendapatan['TOTAL']; 
	}
	
	return $data;
}

function jumlah_hari($bulan=0, $tahun=0) {
    $bulan = $bulan > 0 ? $bulan : date("m");
    $tahun = $tahun > 0 ? $tahun : date("Y");
 
    switch($bulan) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12:
            return 31;
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            return 30;
            break;
        case 2:
            return $tahun % 4 == 0 ? 29 : 28;
            break;
    }
}

function showTable () {
	global $bulan,$thn;
	
	$dt 		= getData($bulan); 
	$c 			= count($dt);
	$a=1;
        for ($i=0;$i<$c;$i++) {
	            $dtname 	= $dt[$i]['nama_bulan'];
	            $jmlOp	 	= $dt[$i]['JML_OP'];
	            $pokok 		= $dt[$i]['POKOK'];
	            $denda		= $dt[$i]['DENDA'];
	            $total		= $dt[$i]['TOTAL'];
				
				$tmp = array(
					'BULAN' 	=> $dtname,
					'JML_OP' 	=> $jmlOp,
					'POKOK' 	=> $pokok,
					'DENDA' 	=> $denda,
					'TOTAL' 	=> $total
				);
				$data[] = $tmp;
				
          $a++;
        }
		  
	return $data;
}

function getPendapatan($tgl,$bulan,$thn) {
	global $myDBLink,$appConfig;

	$myDBLink 		= openMysql();
	$return			= array();
	$db_gw 			= $appConfig['GW_DBNAME'];
	$settle_date	= '';
	
	if($bulan==0) 
		$bulan = "";
		
	if($thn=='' && $bulan==''){
		$settle_date = "%".$bulan.$tgl."%";
	}else if($thn=='' && $bulan!=''){
		$settle_date = "%".$bulan.$tgl;
	} else {
		$settle_date = $thn.$bulan.$tgl."%";
	}
	
	$return["PENDAPATAN"] = 0;
	

	
	$query = "SELECT
				COUNT(NOP) AS JML_OP,
				SUM(SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
				SUM(PBB_DENDA) AS DENDA,
				SUM(PBB_TOTAL_BAYAR) AS TOTAL
			FROM
				PBB_SPPT
			WHERE
				PAYMENT_FLAG = '1'
			AND PAYMENT_SETTLEMENT_DATE LIKE '$settle_date' "; 
	// echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["JML_OP"]	=($row["JML_OP"]!="")?$row["JML_OP"]:0;
		$return["POKOK"]	=($row["POKOK"]!="")?$row["POKOK"]:0;
		$return["DENDA"]	=($row["DENDA"]!="")?$row["DENDA"]:0;
		$return["TOTAL"]	=($row["TOTAL"]!="")?$row["TOTAL"]:0;
	}
	closeMysql($myDBLink);
	return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$kd 				= $appConfig['KODE_KOTA'];
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$bulan 				= @isset($_REQUEST['bulan']) ? $_REQUEST['bulan'] : "";
$arrBulan 			= array(1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember");
$nmBulan			= "";

if($bulan==0)
	$th = "BULAN";
else {
	$th = "TANGGAL";
	$nmBulan = "BULAN ".$arrBulan[$bulan];
}


// print_r($_REQUEST);exit;
 
$data = showTable ();
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
$objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
$objPHPExcel->getActiveSheet()->setCellValue('A2', ' PENDAPATAN PBB-P2 ');
$objPHPExcel->getActiveSheet()->setCellValue('A3', strtoupper($nmBulan).' TAHUN '.$thn.' ');
$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A5', ' '.$th.' ')
            ->setCellValue('B5', ' JUMLAH OP ')
            ->setCellValue('C5', " POKOK ")
			->setCellValue('D5', " DENDA ")
			->setCellValue('E5', " TOTAL ")
			;

// Miscellaneous glyphs, UTF-8
$objPHPExcel->setActiveSheetIndex(0);

$row = 6;
$summary = array('SUM_JML_OP'=>0,'SUM_POKOK'=>0,'SUM_DENDA'=>0,'GRAND_TOTAL'=>0);
for ($i=0;$i<$sumRows;$i++) {
	
    $objPHPExcel->getActiveSheet()->setCellValue('A'.($row), $data[$i]['BULAN']);
	$objPHPExcel->getActiveSheet()->setCellValue('B'.($row), $data[$i]['JML_OP']);
    $objPHPExcel->getActiveSheet()->setCellValue('C'.($row), $data[$i]['POKOK']);
    $objPHPExcel->getActiveSheet()->setCellValue('D'.($row), $data[$i]['DENDA']);
	$objPHPExcel->getActiveSheet()->setCellValue('E'.($row), $data[$i]['TOTAL']);
    $row++;
	
	$summary['SUM_JML_OP'] 	+= $data[$i]['JML_OP'];
	$summary['SUM_POKOK'] 	+= $data[$i]['POKOK'];
	$summary['SUM_DENDA'] 	+= $data[$i]['DENDA'];
	$summary['GRAND_TOTAL'] 	+= $data[$i]['TOTAL'];

}

// // JUMLAH
$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, 'TOTAL');
$objPHPExcel->getActiveSheet()->setCellValue('B'.$row, $summary['SUM_JML_OP']);
$objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $summary['SUM_POKOK']);
$objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $summary['SUM_DENDA']);
$objPHPExcel->getActiveSheet()->setCellValue('E'.$row, $summary['GRAND_TOTAL']);


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
$objPHPExcel->getActiveSheet()->setTitle('Pendapatan PBB-P2');

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

// Redirect output to a client�s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Pendapatan PBB-P2 '.date('d-m-Y').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
