<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/dbspec-central.php");

require_once($sRootPath."inc/PBB/dbFinalSppt.php");
require_once($sRootPath."inc/PBB/dbSpptTran.php");
require_once($sRootPath."inc/PBB/dbGwCurrent.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

//error_reporting(E_ALL); ini_set("display_errors", 1); 


//mulai program
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
// echo "$q:".$q."<br>";

$q = base64_decode($q);
$q = $json->decode($q);
// echo "<pre>";
// print_r($q);
// echo "</pre>";

$a = $q->a;
$m = $q->m;
$srch = isset($q->srch)?$q->srch:"";

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

$arConfig = $User->GetModuleConfig($m);	
$appConfig = $User->GetAppConfig($a);
$dbFinalSppt = new DbFinalSppt($dbSpec);
if($srch){
	$aResult = $dbFinalSppt->get(null, null, array("CPM_NOP LIKE '%$srch%' OR CPM_WP_NAMA LIKE '%$srch%' OR CPM_WP_Alamat" => $srch));
} else {
	$aResult = $dbFinalSppt->get(null, null, null);
}

$i=1;
echo "<center>";
echo "<table>";
echo "  <tr>";
echo "      <th>No.</th>";
echo "      <th>NOP</th>";
echo "      <th>Nama Wajib Pajak</th>";
echo "      <th>Alamat Wajib Pajak</th>";
echo "  </tr>";
foreach ($aResult as $result) {
    echo "  <tr>";
    echo "      <td>".$i."</td>";
    echo "      <td>".$result['CPM_NOP']."</td>";
    echo "      <td>".$result['CPM_WP_NAMA']."</td>";
    echo "      <td>".$result['CPM_WP_ALAMAT']."</td>";
    echo "  </tr>";    
    $i++;
}
echo "</table>";
echo "</center>";
?>