<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dispenda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$aResponse = array();
$aResponse['success'] = true;

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$uname = @isset($_REQUEST['uname']) ? $_REQUEST['uname']:"";
$dispenda = @isset($_REQUEST['dispenda']) ? $_REQUEST['dispenda']:"";

function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysqli_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysqli_fetch_field($mysql_result);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysqli_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysqli_fetch_array($mysql_result);
		  $json.="{\n";
		  for($y=0;$y<count($field_names);$y++) {
			   $json.="'$field_names[$y]' :	'$row[$y]'";
			   if($y==count($field_names)-1){
					$json.="\n";
			   }
			   else{
					$json.=",\n";
			   }
		  }
		  if($x==$rows-1){
			   $json.="\n}\n";
		  }
		  else{
			   $json.="\n},\n";
		  }
	 }
	 $json.="]\n}";
	 return($json);
}

function getDataDispenda ($sts) {
	global $DBLink,$uname,$dispenda;
	$query = "SELECT COUNT(*) AS TOT FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." AND  
			B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_READ IS NULL ";
	if ($dispenda==1) $query = "SELECT COUNT(*) AS TOT FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." AND  
			B.CPM_TRAN_FLAG=0 AND (B.CPM_TRAN_READ IS NULL OR B.CPM_TRAN_OPR_DISPENDA_1 ='' OR B.CPM_TRAN_OPR_DISPENDA_1 IS NULL)";
	if ($dispenda==2) $query = "SELECT COUNT(*) AS TOT FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." AND  
			B.CPM_TRAN_FLAG=0 AND (B.CPM_TRAN_READ IS NULL OR B.CPM_TRAN_OPR_DISPENDA_2 ='' OR B.CPM_TRAN_OPR_DISPENDA_2 IS NULL)";	
				
	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		return '0'; 
	}
	$json = new Services_JSON();
	$d =  $json->decode(mysql2json($res,"data"));	
	
	return $d->data[0]->TOT; 
}

$aResponse['approved'] = intval(getDataDispenda (3));
$aResponse['approved2'] = intval(getDataDispenda (5));
$aResponse['delay'] = intval(getDataDispenda (2));
$aResponse['reject'] = intval(getDataDispenda (4));
$aResponse['delay5'] = intval(getDataDispenda (3));
$aResponse['temporary'] = intval(getDataDispenda (1));
$aResponse['proses'] = intval(getDataDispenda (3));

$sResponse = $json->encode($aResponse);
echo $sResponse;
?>
