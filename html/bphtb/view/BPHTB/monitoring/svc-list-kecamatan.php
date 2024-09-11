<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
if ( !isset($_REQUEST['term']) ) exit;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);
$a = $q->a;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);

$iErrCode = 0;
$DbName = $appConfig['BPHTBDBNAME'];
$DbHost = $appConfig['BPHTBHOSTPORT']; 
$DbPwd = $appConfig['BPHTBPASSWORD'];
$DbTable = $appConfig['BPHTBTABLE'];
$DbUser = $appConfig['BPHTBUSERNAME'];

SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}


$query = "select REPLACE(CPM_OP_KECAMATAN, ' ','') AS CPM_OP_KECAMATAN from SW_SSB.cppmod_ssb_doc  
          where REPLACE(CPM_OP_KECAMATAN, ' ','') like '". mysqli_real_escape_string($LDBLink, $_REQUEST['term']) ."%' 
          group by CPM_OP_KECAMATAN order by CPM_OP_KECAMATAN asc limit 0,10";
// echo "$query";
$rs = mysqli_query($LDBLink, $query);

{
	while( $row = mysqli_fetch_array($rs) )
	{
		$data[] = array(
			'label' => $row['CPM_OP_KECAMATAN'],
			'value' => $row['CPM_OP_KECAMATAN']
		);
	}
}
 
// jQuery wants JSON data
echo $json->encode($data);
flush();

?>
