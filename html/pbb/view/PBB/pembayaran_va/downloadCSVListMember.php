<?php
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");

date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembayaran_va', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once("classCollective.php");
require_once("PHPExcel_1.8.0/Classes/PHPExcel.php");
// require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$dbUtils = new DbUtils($dbSpec);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting 	= new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$appConfig = $User->GetAppConfig("aPBB");
$tahun     = $appConfig['tahun_tagihan'];
$host      = $appConfig['GW_DBHOST'];
$port      = $appConfig['GW_DBPORT'];
$user      = $appConfig['GW_DBUSER'];
$pass      = $appConfig['GW_DBPWD'];
$dbname    = $appConfig['GW_DBNAME'];


$svcCollective = new classCollective($dbSpec, $dbUtils);
$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER      = $user;
$svcCollective->C_PWD       = $pass;
$svcCollective->C_DB        = $dbname;
$svcCollective->C_PORT      = $port;

$limit = isset($_REQUEST['limit']) && $_REQUEST['limit'] ? $_REQUEST['limit'] : 0;
$offset = isset($_REQUEST['offset']) && $_REQUEST['offset'] ? $_REQUEST['offset'] : 0;
$fileNumber = isset($_REQUEST['fileNumber']) && $_REQUEST['fileNumber'] ? $_REQUEST['fileNumber'] : 0;

$dt = $svcCollective->getMemberByIDArray($_REQUEST['id'], false, $limit, $offset);
$NAMA_GROUP = strtoupper($dt[0]['NAMA_GROUP']);
$fileName = 'DATA_PEMBAYARAN_GROUP_'. $NAMA_GROUP;
if ($fileNumber !== 0) {
	$fileName .= '_' . $fileNumber;
}


if (empty($dt)) {
    echo 'Data tidak tersedia';
	exit;
}

$bulan = array(
	"01" => "Januari",
	"02" => "Februari",
	"03" => "Maret",
	"04" => "April",
	"05" => "Mei",
	"06" => "Juni",
	"07" => "Juli",
	"08" => "Agustus",
	"09" => "September",
	"10" => "Oktober",
	"11" => "November",
	"12" => "Desember"
);

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("Alfa System")
	->setLastModifiedBy("Alfa System")
	->setTitle("Data Pembayaran")
	->setSubject("Data Pembayaran")
	->setDescription("Data Pembayaran Kolektif Wajib Pajak PBB")
	->setKeywords("Alfa System PBB");
$center = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	)
);
$bold = array('font' => array('bold' => true));

$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'no')
    ->setCellValue('B1', 'nop')
    ->setCellValue('C1', 'tahun')
    ->setCellValue('D1', 'total');

$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($center);
$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($bold);

$rowIndex = 2;
foreach ($dt as $data) {
    $total = $data['SPPT_PBB_HARUS_DIBAYAR'] + $dbUtils->getDenda($data['SPPT_TANGGAL_JATUH_TEMPO'], $data['SPPT_PBB_HARUS_DIBAYAR']);
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue(('A' . $rowIndex), ($rowIndex - 1))
        ->setCellValueExplicit(('B' . $rowIndex), $data['NOP'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit(('C' . $rowIndex), $data['SPPT_TAHUN_PAJAK'], PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValueExplicit(('D' . $rowIndex), ceil($total), PHPExcel_Cell_DataType::TYPE_STRING);

    $rowIndex++;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="'. $fileName .'.csv"');
$objWriter = (new PHPExcel_Writer_CSV($objPHPExcel))->setDelimiter(';')->setEnclosure('');
$objWriter->save('php://output');