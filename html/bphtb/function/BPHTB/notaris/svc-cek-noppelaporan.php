<?php
ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

function check_ssb(){

  global $DBLink,$nop,$trsid,$a,$noktp; 
  $DbName = getConfigValue($a, 'BPHTBDBNAME');
  $DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
  $DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
  $DbTable = getConfigValue($a, 'BPHTBTABLE');
  $DbUser = getConfigValue($a, 'BPHTBUSERNAME');

  SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);

  $query = "SELECT * FROM $DbTable WHERE op_nomor ='".$nop."' and wp_noktp = '" . $noktp . "' and PAYMENT_FLAG = 1 ORDER BY pelaporan_ke DESC limit 1";
  $resBE = mysqli_query($LDBLink, $query);
  $return = array();
  $return['pelaporan_ke'] = '';
  while ($dtBE = mysqli_fetch_array($resBE)) {
      $return['pelaporan_ke'] = $dtBE['pelaporan_ke'];
  }
  $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE op_nomor ='".$nop."' and wp_noktp = '" . $noktp . "' and PAYMENT_FLAG = 1 ORDER BY pelaporan_ke";
  $resBE = mysqli_query($LDBLink, $query);
  $return['num_rows']  = mysqli_num_rows($resBE);
  return $return;
}

$a = "aBPHTB";
$noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$trsid = @isset($_REQUEST['trsid']) ? $_REQUEST['trsid'] : "";


$result = array();
$result['success'] = true;
$result['feedback'] = "";
if ($trsid== '' && $noktp != '') {
    $ssb = check_ssb();
    if (isset($ssb['pelaporan_ke']) && $ssb['num_rows'] != 0) {
        $ssb['pelaporan_ke'] = ($ssb['pelaporan_ke'] != 0) ? $ssb['pelaporan_ke']: 1;
        $jumlah = (int)$ssb['pelaporan_ke'] > $ssb['num_rows'] ? $ssb['pelaporan_ke'] : $ssb['num_rows'];
        $pelaporan_ke = $jumlah + 1;
        $result['feedback'] = "<td style=\"color:red;font-style:italic;\"><b>NOP ".$nop." MERUPAKAN PERALIHAN KE ".$pelaporan_ke." DENGAN KTP ".$noktp." </b></td>";
    }
    else{
        $result['feedback'] = "<td style=\"color:red;font-style:italic;\"><b>NOP ".$nop." MERUPAKAN PERALIHAN KE 1 DENGAN KTP ".$noktp." </b></td>";
    }
}

$sResponse = $json->encode($result);
echo $sResponse;

SCANPayment_CloseDB($DBLink);
?>
