<?
require_once("../payment/db-payment.php");
require_once("../payment/inc-payment-db-c.php");
require_once("../central/dbspec-central.php");
require_once("../payment/json.php");
require_once("dbUtils.php");
$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$dbUtils = new DbUtils($dbSpec);

$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest  = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);
$type = $prm->type;
$id = $prm->id;

$resultStatus = true;
$resultStringSelect = true;
if($type == 'kelurahan'){
	$Kelurahan = $dbUtils->getKelurahan(null, array("CPC_TKl_KCID" => $id));
	if(count($Kelurahan) == 0) $resultStatus = false;
	$resultStringSelect = "<select name='WP_KELURAHAN' id='WP_KELURAHAN' ><option value=''>Kelurahan</option>";
	foreach($Kelurahan as $row){ 				
		$resultStringSelect .= "<option value='".$row['CPC_TKL_ID']."' >".$row['CPC_TKL_KELURAHAN']."</option>";
	}
	$resultStringSelect .= "</select>";
}else if($type == 'kecamatan'){
	$Kecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $id));
	if(count($Kecamatan) == 0) $resultStatus = false;
	$resultStringSelect = "<select name='WP_KECAMATAN' id='WP_KECAMATAN' onchange='loadKel(this);'><option value=''>Kecamatan</option>";
	foreach($Kecamatan as $row){ 				
		$resultStringSelect .= "<option value='".$row['CPC_TKC_ID']."' >".$row['CPC_TKC_KECAMATAN']."</option>";
	}
	$resultStringSelect .= "</select>";
}else if($type == 'kota'){
	
	$KabKota = $dbUtils->getKabKota(null, array("CPC_TK_PID" => $id));
	
	if(count($KabKota) == 0) $resultStatus = false;
	$resultStringSelect = "<select name='WP_KOTAKAB' id='WP_KOTAKAB' onchange='loadKec(this);'><option value=''>Kab/kodya</option>";
	foreach($KabKota as $row){ 				
		$resultStringSelect .= "<option value='".$row['CPC_TK_ID']."' >".$row['CPC_TK_KABKOTA']."</option>";
	}
	$resultStringSelect .= "</select>";
}
/*echo "<pre>";
print_r($KabKota);
echo "</pre>";
echo $resultStringSelect;
*/

$response = array();
$response['r'] = $resultStatus;
$response['d']['stringselect'] = $resultStringSelect;


$val = $json->encode($response);
echo $val;
?>