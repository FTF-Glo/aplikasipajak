<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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


die(var_dump('bacot'));
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
  
  $config['BPHTBDBNAME'] = getConfigValue($a,'BPHTBDBNAME');
  $config['BPHTBHOSTPORT'] = getConfigValue($a,'BPHTBHOSTPORT');
  $config['BPHTBPASSWORD'] = getConfigValue($a,'BPHTBPASSWORD');
  $config['BPHTBTABLE'] = getConfigValue($a,'BPHTBTABLE');
  $config['BPHTBUSERNAME'] = getConfigValue($a,'BPHTBUSERNAME');
  
  return $config;
}
function check_validate(){
  global $DBLink,$axx,$trsid,$nop,$noktp; 
  $qry = "SELECT * FROM cppmod_ssb_doc WHERE cppmod_ssb_doc.cpm_op_noktp = \"{$noktp}\" AND cppmod_ssb_doc.cpm_op_nomor = \"{$nop}\" ORDER BY CPM_SSB_CREATED DESC limit 1";
  // echo $qry;
    $res = mysqli_query($DBLink, $qry);
    if ( $res === false ){
      echo $qry ."<br>";
      echo mysqli_error($DBLink);
    }
    $return = array();
    while ($rows = mysqli_fetch_assoc($res)) {
        $return['ssb_id'] = $rows['CPM_SSB_ID'];
    }
    $qry2 = "SELECT * FROM cppmod_ssb_tranmain WHERE CPM_TRAN_SSB_ID = \"{$return['ssb_id']}\" ORDER BY CPM_TRAN_DATE DESC limit 1";
    $res2 = mysqli_query($DBLink, $qry2);
    if ( $res2 === false ){
      echo $qry2 ."<br>";
      echo mysqli_error($DBLink);
    }
    while ($rows = mysqli_fetch_assoc($res2)) {
        $return['status'] = $rows['CPM_TRAN_STATUS'];
    }
    return $return;
}
function check_SSB($ssb_id){
  global $DBLink,$axx,$trsid,$nop,$noktp; 
  $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
  $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
  $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
  $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
  $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

  SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
  if ($iErrCode != 0) {
      $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
      if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
          error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
      exit(1);
  }

  $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID,PAYMENT_FLAG FROM $DbTable WHERE id_switching = '" . $ssb_id . "' ORDER BY saved_date DESC limit 1  ";
  $resBE = mysqli_query($LDBLink, $query);
  $return = array();
  while ($dtBE = mysqli_fetch_array($resBE)) {
      $return['flag'] = $dtBE['PAYMENT_FLAG'];
  }
  return $return;
}
$appId = base64_decode(@isset($_REQUEST['axx']) ? $_REQUEST['axx'] : "");
$noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$trsid = @isset($_REQUEST['trsid']) ? $_REQUEST['trsid'] : "";


$result = array();
$result['success'] = true;
$result['status_pelaporan'] = 0;
if ($trsid!= '') {
  $checkd = check_validate();
  if ($check['status']!=5) {
    $result['status_pelaporan'] = $check['status'];
  }
  else{
    $ssb = check_SSB($checkd['ssb_id']);
    $result['status_pelaporan'] = ($ssb['flag']==1) ? 9 : 99;
  }
}


$sResponse = $json->encode($result);
echo $sResponse;

SCANPayment_CloseDB($DBLink);
?>
