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

function check_validate(){
  global $DBLink,$axx,$trsid,$nop,$noktp; 
  $qry = "SELECT * FROM cppmod_ssb_doc WHERE cppmod_ssb_doc.cpm_wp_noktp = \"{$noktp}\" AND cppmod_ssb_doc.cpm_op_nomor = \"{$nop}\" ORDER BY CPM_SSB_CREATED DESC limit 1";
  // echo $qry;
    $res = mysqli_query($DBLink, $qry);
    if ( $res === false ){
      echo $qry ."<br>";
      echo mysqli_error($DBLink);
    }
    $return = array();
    $return['ssb_id'] ='';
    while ($rows = mysqli_fetch_assoc($res)) {
        $return['ssb_id'] = $rows['CPM_SSB_ID'];
    }
    $qry2 = "SELECT * FROM cppmod_ssb_tranmain WHERE CPM_TRAN_SSB_ID = \"".$return['ssb_id']."\" ORDER BY CPM_TRAN_DATE DESC limit 1";
    $res2 = mysqli_query($DBLink, $qry2);
    if ( $res2 === false ){
      echo $qry2 ."<br>";
      echo mysqli_error($DBLink);
    }
    while ($rows = mysqli_fetch_assoc($res2)) {
        $return['status'] = $rows['CPM_TRAN_STATUS'];
    }
    if ($return['ssb_id']=='') {
      $return['status'] = 0;
    }
    return $return;
}
function check_ssb($ssb_id){

  global $DBLink,$nop,$trsid,$a; 
  $DbName = getConfigValue($a, 'BPHTBDBNAME');
  $DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
  $DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
  $DbTable = getConfigValue($a, 'BPHTBTABLE');
  $DbUser = getConfigValue($a, 'BPHTBUSERNAME');

  SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);

  $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID,PAYMENT_FLAG FROM $DbTable WHERE id_switching = '" . $ssb_id . "' ORDER BY saved_date DESC limit 1  ";
  $resBE = mysqli_query($LDBLink, $query);
  $return = array();
  $return['flag'] = '';
  while ($dtBE = mysqli_fetch_array($resBE)) {
      $return['flag'] = $dtBE['PAYMENT_FLAG'];
  }
  return $return;
}

$a = "aBPHTB";
$noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$trsid = @isset($_REQUEST['trsid']) ? $_REQUEST['trsid'] : "";


$result = array();
$result['success'] = true;
$result['status_pelaporan'] = 0;
if ($trsid== '') {
  $checkd = check_validate();
  if ($checkd['status']!=5) {
    $result['status_pelaporan'] = $checkd['status'];
  }
  else{
    $ssb = check_ssb($checkd['ssb_id']);
    $result['status_pelaporan'] = (isset($ssb['flag']) && $ssb['flag']==1) ? 9 : 99;
  }
}

$sResponse = $json->encode($result);
echo $sResponse;

SCANPayment_CloseDB($DBLink);
?>
