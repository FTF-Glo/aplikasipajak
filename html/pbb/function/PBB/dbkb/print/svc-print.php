<?php
/* 
 *  Print SPPT 
 *  Author By ardi@vsi.co.id
 */
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'dbkb'.DIRECTORY_SEPARATOR.'print', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/ctools.php"); 
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/error-messages.php"); 

require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/nid.php");
require_once($sRootPath."function/PBB/dbkb/print/svc-data.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

function doPrint() {
	$arrValues['code'] = '00';
	$arrValues['data'] = printRequest();
	return $arrValues;
}

function printRequest() {
	global $sRootPath;
	
	$sTemplateFile = $sRootPath."function/PBB/dbkb/print/dbkb-report-tmp.xml";
	$driver="epson";
	
	$re = new reportEngine($sTemplateFile,$driver);
	
	if ($aTemplateValue = GetValuesForPrint()){
		$re->ApplyTemplateValue($aTemplateValue);
		if($driver=="other"){
			$re->Print2OnpaysTXT($printValue);
			$strTXT = $printValue;
		}else{
			$re->Print2TXT($printValue);
			$strTXT = base64_encode($printValue);
		}
	}
	return $strTXT;
} 

$appID = @isset($_REQUEST['app']) ? $_REQUEST['app'] : '';

$json = new Services_JSON();
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($appID);
$kdPropinsi = $appConfig['KODE_PROVINSI'];
$kdDati2 	= substr($appConfig['KODE_KOTA'],2,2);
$tahun		= $appConfig['tahun_tagihan'];
$propinsi = $appConfig['NAMA_PROVINSI'];
$kota = $appConfig['NAMA_KOTA'];
$kanwil = $appConfig['KANWIL'];
$kpp = $appConfig['KPP'];
$kepala_nama = $appConfig['WALIKOTA_NAMA'];
$kepala_nim = $appConfig['WALIKOTA_NIP'];

$arrValues = array();

if(stillInSession($DBLink,$json,$sdata)){
	$arrValues = doPrint();
} else {
	$arrValues['code'] = '10';
}

$val = $json->encode($arrValues);
echo base64_encode($val);

?>
