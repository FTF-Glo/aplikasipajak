<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penagihan', '', dirname(__FILE__))) . '/';

//error_reporting(E_ALL);

//date_default_timezone_set('Asia/Jakarta');

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
require_once("config-monitoring.php");


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

$host = DBHOST;
$port = DBPORT;
$user = DBUSER;
$pass = DBPWD;
$dbname = DBNAME;
$myDBlink ="";


function headerMonitoringE2 ($mod,$nama) {
	$model = ($mod==0) ? "KECAMATAN" : "KELURAHAN";
	$dl = "";
	if ($mod==0) {
		$dl = "KOTA PALEMBANG";
	} else {
		$dl = $nama;
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\"><tr><td colspan=\"15\"><b>{$dl}<b></td></tr>
	  <col width=\"28\" />
	  <col width=\"187\" />
	  <col width=\"47\" />
	  <col width=\"89\" />
	  <tr>
		<td rowspan=\"2\" width=\"28\" align=\"center\">NO</td>
		<td rowspan=\"2\" width=\"117\" align=\"center\">{$model}</td>
		<td colspan=\"2\" width=\"136\" align=\"center\">TAGIHAN</td>
	  </tr>
	  <tr>
		<td align=\"center\">WP</td>
		<td align=\"center\">Rp</td>
	  </tr>
	";
	return $html;
}

// koneksi postgres
function openMysql () {
	$host = DBHOST;
	$port = DBPORT;
	$dbname = DBNAME;
	$user = DBUSER;
	$pass = DBPWD;
	$myDBLink = mysqli_connect($host , $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		//echo mysqli_error($myDBLink);
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
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='".$p."' ORDER BY CPC_TKC_URUTAN ASC";
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
			$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID ='".$p."' ORDER BY CPC_TKL_URUTAN ASC";
			$res = mysqli_query($DBLink, $query);
			if ($res === false) {
				 $result['msg'] = mysqli_error($DBLink);
				 echo $json->encode($result);
				 exit();
			}
			$data = array();
			$i=0;
			while ($row = mysqli_fetch_assoc($res)) {
				$data[$i]["id"] = $row["CPC_TKL_ID"];
				$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
				$i++;
			}

	return $data;
}

function getKetetapan($mod) {
	global $DBLink,$kd,$kecamatan,$kelurahan,$thn,$bulan,$kab,$s;
	if ($mod==0) $kec =  getKecamatan($kab);
	else $kec = getKelurahan($kecamatan);
    
    $tahun = "";
	if($thn != ""){$tahun = "and sppt_tahun_pajak='{$thn}'";}
	

	$c = count($kec);
	$data = array();
	for ($i=0;$i<$c;$i++) {
		$data[$i]["name"] = $kec[$i]["name"];
		$whr = " NOP like '".$kec[$i]["id"]."%' ".$tahun;
                $da = getData($whr);
		$data[$i]["WP"] = $da["WP"];
		$data[$i]["RP"] = $da["RP"];
	}
	
	return $data;
}

function showTable ($mod=0,$nama="") {
	global $eperiode;
	$dt = getKetetapan($mod);
	$dtall = array();
	
	$c = count($dt);

	$a=1;

    $data = array();
	$summary = array('name'=>'JUMLAH', 'ketetapan_wp'=>0, 'ketetapan_rp'=>0, 'rbl_wp'=>0, 'rbl_rp'=>0, 'percent1'=>0, 'rbi_wp'=>0, 'rbi_rp'=>0,'kom_rbi_wp'=>0, 'kom_rbi_rp'=>0, 'percent2'=>0, 'sk_wp'=>0, 'sk_rp'=>0, 'percent3'=>0);
	
	for ($i=0;$i<$c;$i++) {
			
            $tmp = array(
                "name" => $dt[$i]["name"],
                "ketetapan_wp" => number_format($dt[$i]["WP"],0,"",""),
                "ketetapan_rp" => number_format($dt[$i]["RP"],0,"","")
				
            );
            $data[] = $tmp;
			$summary['ketetapan_wp'] += $dt[$i]["WP"];
			$summary['ketetapan_rp'] += $dt[$i]["RP"];
	}
	
				
	$data[] = $summary;
	
	return $data;
}

function getData($where) {
	global $myDBLink,$kd,$thn,$bulan;

	$myDBLink = openMysql();
	$return=array();
	$return["RP"]=0;
	$return["WP"]=0;
	$whr=" where (payment_flag !='1' or payment_flag is null) ";
	if($where) {
		$whr .=" and {$where}";
	}	
	$query = "SELECT count(wp_nama) AS WP, sum(SPPT_PBB_HARUS_DIBAYAR) as RP FROM ".DBTABLE." {$whr}"; //echo $query.'<br/>';
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["RP"]=($row["RP"]!="")?$row["RP"]:0;
		$return["WP"]=($row["WP"]!="")?$row["WP"]:0;
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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);
$kd = $appConfig['KODE_KOTA'];

$kab  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA']; 
$bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","Nopember","Desember");
$kecamatan = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$nama = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$speriode = @isset($_REQUEST['speriode']) ? $_REQUEST['speriode'] : "";
$eperiode = @isset($_REQUEST['eperiode']) ? $_REQUEST['eperiode'] : "";
$target_ketetapan = @isset($_REQUEST['target_ketetapan']) ? $_REQUEST['target_ketetapan'] : "";

$arrWhere = array();

if ($kecamatan !="") {
        array_push($arrWhere,"nop like '{$kecamatan}%'");
}

if ($thn!=""){
    array_push($arrWhere,"sppt_tahun_pajak='{$thn}'");
    array_push($arrWhere,"payment_paid like '{$thn}%'");
}

$where = implode (" AND ",$arrWhere);

 if ($kecamatan=="") {
	$data = showTable ();
} else {
	$data = showTable(1,$nama);
} 


$fontSizeHeader = 10;
$fontSizeDefault = 9;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.8);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.5);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.3);

$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($fontSizeDefault)->setBold(true); 

// Set properties
$objPHPExcel->getProperties()->setCreator("vpost")
                             ->setLastModifiedBy("vpost")
                             ->setTitle("Alfa System")
                             ->setSubject("Alfa System pbb")
                             ->setDescription("pbb")
                             ->setKeywords("Alfa System");

// Header
$objRichText = new PHPExcel_RichText();

//$objRichText->createText(': KETETAPAN DAN REALISASI PBB TAHUN ANGGARAN '.$thn);
//$objPHPExcel->getActiveSheet()->getCell('D1')->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('D1:J1');
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText(': I s/d III');
//$objPHPExcel->getActiveSheet()->getCell('D2')->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('D2:J2');
//$objRichText = new PHPExcel_RichText();
//if($eperiode > 1)
//	$objRichText->createText(': JANUARI s/d '.strtoupper($bulan[$eperiode-1]).' '.$thn);
//else $objRichText->createText(': JANUARI  '.$thn);
//$objPHPExcel->getActiveSheet()->getCell('D3')->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('D3:J3'); 


$objPHPExcel->setActiveSheetIndex(0);

//$objPHPExcel->getActiveSheet()->setCellValue('C1', 'DAFTAR');
//$objPHPExcel->getActiveSheet()->setCellValue('C2', 'BUKU');
//$objPHPExcel->getActiveSheet()->setCellValue('C3', 'BULAN');
//$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Model E.2');

$objPHPExcel->getActiveSheet()->getStyle('C1:L3')->applyFromArray(
    array('font'    => array('size'=>$fontSizeHeader))
);
if($kecamatan = ''){
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('RANGKING');
	$objPHPExcel->getActiveSheet()->getCell('A5')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('KOTA PALEMBANG');
	$objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A6:B6');
	$objPHPExcel->getActiveSheet()->getStyle('A5:B6')->applyFromArray(
	    array(
	        'font'    => array('italic' => true,'size'=>$fontSizeHeader),
	        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
	    )
	);
}else{
	$objRichText = new PHPExcel_RichText();
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText('KECAMATAN : '.$nama);
	$objPHPExcel->getActiveSheet()->getCell('A6')->setValue($objRichText);
	$objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
	$objPHPExcel->getActiveSheet()->getStyle('A5:D6')->applyFromArray(
	    array(
	        'font'    => array('italic' => false,'size'=>$fontSizeHeader),
	        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
	    )
	);
} 

        

// Header Of Table
$objRichText = new PHPExcel_RichText();
$objRichText->createText('NO');
$objPHPExcel->getActiveSheet()->getCell('A8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('KECAMATAN');
$objPHPExcel->getActiveSheet()->getCell('B8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
$objRichText = new PHPExcel_RichText();
$objRichText->createText('TAGIHAN');
$objPHPExcel->getActiveSheet()->getCell('C8')->setValue($objRichText);
$objPHPExcel->getActiveSheet()->mergeCells('C8:D8');
$objPHPExcel->getActiveSheet()->setCellValue('C9', 'WP');
$objPHPExcel->getActiveSheet()->setCellValue('D9', 'RP');



// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Reporting');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Set style for header row using alternative method
$objPHPExcel->getActiveSheet()->getStyle('A8:D9')->applyFromArray(
    array(
        'font'    => array(            
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
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
//$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
//$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
//$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(7);
//$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
//$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
//$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
//$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(7);
//$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(8);
//$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
//$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(7);

$objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(18);
$objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(18);
$no=1;

$noBold = array('font' => array('bold' => false));
$bold = array('font' => array('bold' => true));

foreach($data as $buffer){
    $objPHPExcel->getActiveSheet()->getRowDimension(9+$no)->setRowHeight(18);
    $objPHPExcel->getActiveSheet()->setCellValue('A'.(9+$no), $no);
    $objPHPExcel->getActiveSheet()->setCellValue('B'.(9+$no), $buffer['name']);
	if($buffer['name'] == 'JUMLAH'){
		$objPHPExcel->getActiveSheet()->setCellValue('C'.(9+$no), $buffer['ketetapan_wp']);
	    $objPHPExcel->getActiveSheet()->setCellValue('D'.(9+$no), $buffer['ketetapan_rp']);
	}else {
	    $objPHPExcel->getActiveSheet()->setCellValue('C'.(9+$no), $buffer['ketetapan_wp'])->getStyle('C'.(9+$no))->applyFromArray($noBold);
	    $objPHPExcel->getActiveSheet()->setCellValue('D'.(9+$no), $buffer['ketetapan_rp'])->getStyle('D'.(9+$no))->applyFromArray($noBold);
	}
    $no++;
}
$objPHPExcel->getActiveSheet()->setCellValue('A'.(8+$no), '');
$objPHPExcel->getActiveSheet()->getStyle('A10:D'.(9+count($data)))->applyFromArray(
    array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
); 
$objPHPExcel->getActiveSheet()->getStyle('A10:A'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('C10:F'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('G10:G'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('H10:K'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('L10:L'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$objPHPExcel->getActiveSheet()->getStyle('M10:N'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//$objPHPExcel->getActiveSheet()->getStyle('O10:O'.(9+count($data)))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('PALEMBANG, '.strtoupper($bulan[date('m')-1]).' '.$thn);
//$objPHPExcel->getActiveSheet()->getCell('I'.(11+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(11+count($data)).':K'.(11+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('KEPALA DINAS PENDAPATAN DAERAH');
//$objPHPExcel->getActiveSheet()->getCell('I'.(12+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(12+count($data)).':K'.(12+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('KOTA PALEMBANG');
//$objPHPExcel->getActiveSheet()->getCell('I'.(13+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(13+count($data)).':K'.(13+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('Dra. Hj. SUMAIYAH. MZ, MM');
//$objPHPExcel->getActiveSheet()->getCell('I'.(17+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(17+count($data)).':K'.(17+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('PEMBINA UTAMA MUDA');
//$objPHPExcel->getActiveSheet()->getCell('I'.(18+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(18+count($data)).':K'.(18+count($data)));
//$objRichText = new PHPExcel_RichText();
//$objRichText->createText('NIP. 19550922 197903 2 003');
//$objPHPExcel->getActiveSheet()->getCell('I'.(19+count($data)))->setValue($objRichText);
//$objPHPExcel->getActiveSheet()->mergeCells('I'.(19+count($data)).':K'.(19+count($data)));
//
//$objPHPExcel->getActiveSheet()->getStyle('I'.(17+count($data)).':K'.(17+count($data)));
//$objPHPExcel->getActiveSheet()->getStyle('I'.(11+count($data)).':K'.(19+count($data)))->applyFromArray(
//    array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER))
//);

// Set page orientation and size
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="reporting_model_e2.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
