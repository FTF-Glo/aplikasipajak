<?php


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

error_reporting(E_ALL);
ini_set("display_errors", 1); 

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue ($id,$key) {
	global $DBLink;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	
		$res = mysqli_query($DBLink, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['CTR_AC_VALUE'];
		}
	
}

function getConfigure ($appID) {
  $config = array();
  $a=$appID;
  $config['TENGGAT_WAKTU'] = getConfigValue($a,'TENGGAT_WAKTU');
  $config['NPOPTKP_STANDAR'] = getConfigValue($a,'NPOPTKP_STANDAR');
  $config['NPOPTKP_WARIS'] = getConfigValue($a,'NPOPTKP_WARIS');
  $config['TARIF_BPHTB'] = getConfigValue($a,'TARIF_BPHTB');
  $config['PRINT_SSPD_BPHTB'] = getConfigValue($a,'PRINT_SSPD_BPHTB');
  $config['NAMA_DINAS'] = getConfigValue($a,'NAMA_DINAS');
  $config['ALAMAT'] = getConfigValue($a,'ALAMAT');
  $config['NAMA_DAERAH'] = getConfigValue($a,'NAMA_DAERAH');
  $config['KODE_POS'] = getConfigValue($a,'KODE_POS');
  $config['NO_TELEPON'] = getConfigValue($a,'NO_TELEPON');
  $config['NO_FAX'] = getConfigValue($a,'NO_FAX');
  $config['EMAIL'] = getConfigValue($a,'EMAIL');
  $config['WEBSITE'] = getConfigValue($a,'WEBSITE');
  $config['KODE_DAERAH'] = getConfigValue($a,'KODE_DAERAH');
  $config['KEPALA_DINAS'] = getConfigValue($a,'KEPALA_DINAS');
  $config['NAMA_JABATAN'] = getConfigValue($a,'NAMA_JABATAN');
  $config['NIP'] = getConfigValue($a,'NIP');
  $config['NAMA_PJB_PENGESAH'] = getConfigValue($a,'NAMA_PJB_PENGESAH');
  $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a,'JABATAN_PJB_PENGESAH');
  $config['NIP_PJB_PENGESAH'] = getConfigValue($a,'NIP_PJB_PENGESAH');
  return $config;
}

$id = @isset($_REQUEST['id']) ? intval($_REQUEST['id']) : "";
$appId =base64_decode(@isset($_REQUEST['axx']) ? $_REQUEST['axx'] : "");
$result = array();


if ($id) {
	$result['success'] = true;
	if ($id==5){
		$result["result"] =  getConfigValue($appId,'NPOPTKP_WARIS');
	} else {
		$result["result"] =  getConfigValue($appId,'NPOPTKP_STANDAR');
	}
	
	$sResponse = $json->encode($result);
	echo $sResponse;
}

SCANPayment_CloseDB($DBLink);
?>
