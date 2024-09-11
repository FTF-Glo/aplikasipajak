<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

ob_start();

// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'pc'.DIRECTORY_SEPARATOR.'svr'.DIRECTORY_SEPARATOR.'spptprint', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/constant.php");
require_once($sRootPath."pc/inc/inc-payment-c.php");
require_once($sRootPath."pc/inc/inc-payment-db-c.php");
require_once($sRootPath."inc/prefs-payment.php");
require_once($sRootPath."inc/db-payment.php");
require_once($sRootPath."inc/ctools.php");
require_once($sRootPath."inc/json.php");
require_once($sRootPath."inc/log-payment.php");
require_once($sRootPath."inc/sayit.php");

// start stopwatch
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
	$iStart = microtime(true);
}

// global variables
$iCentralTS = time();
$iErrCode = 0;
$sErrMsg = '';
$DBLink = NULL;
$DBConn = NULL;
$sUID = '';
$sUName = '';
$bMLPOSignedOn = false; // pp is not signed-on MLPO system (use by NetMan)

// Payment related initialization
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

SCANPayment_Pref_GetAllWithFilter($DBLink, "PC.%", $aCentralPrefs);
//var_dump($aCentralPrefs);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

// ---------------
// LOCAL FUNCTIONS
// ---------------
function getConfigValue ($id,$key) {
	global $DBLink;	
	$id = $_REQUEST['a'];
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
function setFLAG($nop,$flag) {
	global $DBLink;
	$OK = false;
	$qry = "UPDATE cppmod_pbb_sppt_current SET FLAG = {$flag} where NOP = '{$nop}'";
	if ($res = mysqli_query($DBLink, $qry)) {
		$OK = true;
	} 
}

function insertIntoGateway() {
	$dbName = $this->getConfigValue($a,'BPHTBDBNAME');
	$dbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
	$dbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
	$dbTable = $this->getConfigValue($a,'BPHTBTABLE');
	$dbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
	$dbLimit = $this->getConfigValue($a,'TENGGAT_WAKTU');
	// Connect to lookup database
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	
	$query2 = "INSERT INTO $dbTable (wp_nama,wp_alamat,wp_rt,wp_rw,wp_kelurahan,wp_kecamatan,wp_kabupaten,wp_kodepos,op_letak,
	op_rt,op_rw,op_kelurahan,op_kecamatan,op_kabupaten,bphtb_dibayar,op_nomor,saved_date,wp_noktp,id_switching,expired_date,
	payment_flag) VALUES (
	'$wp_nama','$wp_alamat','$wp_rt','$wp_rw','$wp_kelurahan','$wp_kecamatan','$wp_kabupaten','$wp_kodepos','$op_letak',
	'$op_rt','$op_rw','$op_kelurahan','$op_kecamatan','$op_kabupaten','$bphtb_dibayar','$op_nomor','$saved_date','$noktp','$id_switching',
	DATE_ADD(DATE(saved_date), INTERVAL {$dbLimit} DAY),'$payment_flag')";
	
	$r = mysqli_query($DBLinkLookUp, $query2);
	if ( $r === false ){
		die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
	}
}

function GetDataReadyToPrint($aParams,&$Response)
{
  global $iErrCode, $sErrMsg, $aClientVar, $aPrefs, $iPPtimeDiff, $DBLink, $json;
  
  error_reporting(E_ALL);
  ini_set('error_reporting', E_ALL);

  $bOK = false;
  $iSvrTS = strtotime($aClientVar['svrdt']);

  $iErrCode = 0; $sErrMsg = '';
  $sFieldPrefix = '';
  $aBill = array();

  $n = sizeof($aParams);
  $sTS = array();
  

  for($i=0; $i<$n; $i++)
  {
    $sParams = trim($aParams[$i]);
    $jParams = $json->decode($aParams);
    $year = $jParams->y;
    $city = $jParams->c;
	$NOP = $jParams->n;
	$Name = $jParams->na;
  }

  //get transaction
  $sQCond = "";//" where OP_KOTAKAB_KODE = '$city' AND SPPT_TAHUN_PAJAK = '$year' AND PAYMENT_FLAG=0";
  $sQ = "select * from cppmod_pbb_sppt_current where FLAG = 1 and NOP = '".$NOP."'";

  $sQ .= $sQCond;
  $Response['success'] = false;
  $Response['readytoprint'] = false;
  $Response["sppt"][0]["n"] = $NOP;
  $Response["sppt"][0]["wn"] = $Name;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
	
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
	//print_r($nRes);
    $nRecord = $nRes;
    if ($nRes > 0)
    {
      $i = 0;
	  $Response['readytoprint'] = true;
	  $Response['success'] = true;
	  while($row = mysqli_fetch_array($res, MYSQL_ASSOC))
      {	  
	  	$Response["sppt"][$i]["no"] = $i+1;
		$Response["sppt"][$i]["n"] = trim($row['NOP']);
		$Response["sppt"][$i]["t"] = trim($row['SPPT_TAHUN_PAJAK']);
		$Response["sppt"][$i]["e"] = trim($row['SPPT_TANGGAL_JATUH_TEMPO']);
		$Response["sppt"][$i]["a"] = number_format(intval($row['SPPT_PBB_HARUS_DIBAYAR']),0,",",".");
		$Response["sppt"][$i]["wn"] = trim($row['WP_NAMA']);
		$Response["sppt"][$i]["wa"] = trim($row['WP_ALAMAT']);
		$Response["sppt"][$i]["wt"] = trim($row['WP_RT']);
		$Response["sppt"][$i]["ww"] = trim($row['WP_RW']);
		$Response["sppt"][$i]["wl"] = trim($row['WP_KELURAHAN']);
		$Response["sppt"][$i]["wc"] = trim($row['WP_KECAMATAN']);
		$Response["sppt"][$i]["wk"] = trim($row['WP_KOTAKAB']);
		$Response["sppt"][$i]["wp"] = trim($row['WP_KODEPOS']);
		$Response["sppt"][$i]["st"] = trim($row['SPPT_TANGGAL_TERBIT']);
		$Response["sppt"][$i]["sc"] = trim($row['SPPT_TANGGAL_CETAK']);
		$Response["sppt"][$i]["np"] = trim($row['NPWP']);
		$Response["sppt"][$i]["olbi"] = number_format(intval($row['OP_LUAS_BUMI']),0,",",".");
		$Response["sppt"][$i]["olbn"] = number_format(intval($row['OP_LUAS_BANGUNAN']),0,",",".");
		$Response["sppt"][$i]["okbi"] = trim($row['OP_KELAS_BUMI']);
		$Response["sppt"][$i]["okbn"] = trim($row['OP_KELAS_BANGUNAN']);
		$Response["sppt"][$i]["onbi"] = number_format(intval($row['OP_LUAS_BUMI']),0,",",".");
		$Response["sppt"][$i]["onbn"] = number_format(intval($row['OP_LUAS_BANGUNAN']),0,",",".");
		$Response["sppt"][$i]["on"] = number_format(intval($row['OP_NJOP']),0,",",".");
		$Response["sppt"][$i]["onb"] = number_format(intval($row['OP_NJOP_BUMI']),0,",",".");
		$Response["sppt"][$i]["ong"] = number_format(intval($row['OP_NJOP_BANGUNAN']),0,",",".");
		$totnjopbm = intval($row['OP_NJOP_BUMI'])*intval($row['OP_LUAS_BUMI']);
		$totnjopbn = intval($row['OP_NJOP_BANGUNAN'])*intval($row['OP_LUAS_BANGUNAN']);
		$Response["sppt"][$i]["onbt"] =  number_format($totnjopbm,0,",",".");
		$Response["sppt"][$i]["ongt"] =  number_format($totnjopbn,0,",",".");
		$Response["sppt"][$i]["ontkp"] = number_format($row['OP_NJOPTKP'],0,",",".");
		$njkp = $totnjopbn+$totnjopbm-intval($row['OP_NJOPTKP']) < 1000000000 ? 0.2 : 0.4;
		$pbbhut = 0.005;
		$Response["sppt"][$i]["onjkp"] = ($njkp * 100)."%";
		$Response["sppt"][$i]["pbbhut"] = ($pbbhut * 100)."%";
		$Response["sppt"][$i]["onjkpt"] = number_format($njkp * ($totnjopbn+$totnjopbm-intval($row['OP_NJOPTKP'])),0,",",".");
		$Response["sppt"][$i]["tpbbhut"] = number_format($njkp * ($totnjopbn+$totnjopbm-intval($row['OP_NJOPTKP']))*$pbbhut,0,",",".");
		$Response["sppt"][$i]["onjds"] = number_format($totnjopbn+$totnjopbm,0,",",".");
		$Response["sppt"][$i]["onjop"] = number_format($totnjopbn+$totnjopbm-intval($row['OP_NJOPTKP']),0,",",".");
		$Response["sppt"][$i]["say"] = strtoupper(SayInIndonesian(ceil($njkp * ($totnjopbn+$totnjopbm-intval($row['OP_NJOPTKP']))*$pbbhut)))." RUPIAH";
		
		$Response["sppt"][$i]["oa"] = trim($row['OP_ALAMAT']);
		$Response["sppt"][$i]["ot"] = trim($row['OP_RT']);
		$Response["sppt"][$i]["ow"] = trim($row['OP_RW']);
		$Response["sppt"][$i]["ol"] = trim($row['OP_KELURAHAN']);
		$Response["sppt"][$i]["oc"] = trim($row['OP_KECAMATAN']);
		$Response["sppt"][$i]["ok"] = trim($row['OP_KOTAKAB']);
		$Response["sppt"][$i]["olc"] = trim($row['OP_KELURAHAN_KODE']);
		$Response["sppt"][$i]["occ"] = trim($row['OP_KECAMATAN_KODE']);
		$Response["sppt"][$i]["okc"] = trim($row['OP_KOTAKAB_KODE']);
		$Response["sppt"][$i]["opc"] = trim($row['OP_PROVINSI_KODE']);
		//$Response["sppt"][$i]["CHECKED"] = 0;
		//$Response["sppt"][$i]["INDEX"] = 0;
		$bOK=true;
	  	if (!setFLAG($row['NOP'],"2")) $bOK=false;
		$i++;
		
	  }
	 
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
	//print_r($sErrMsg);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }

  return $bOK;
} // end of GetDataResult

// ------------
// MAIN PROGRAM
// ------------

// get remote parameters

$sQueryString = (@isset($_REQUEST['q']) ? $_REQUEST['q'] : ''); // because of post (form-urlencoded)
$sClientRemoteAddress = $_SERVER['REMOTE_ADDR'];
 error_reporting(E_ALL);
  ini_set('error_reporting', E_ALL);

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sQueryString] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);
	
$aResponse = array();
$aResponse['success'] = true;
$aResponse['errcode'] = 0;
$aResponse['sppt'] = array();



if ($sQueryString != '')
{
    $sBlockReq = base64_decode($sQueryString);

    if (CTOOLS_IsInFlag(DEBUG, DEBUG_INFO))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Payment point do data check for [$sBlockReq]\n", 3, LOG_FILENAME.'-data_check');

    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQueryString [$sQueryString] sBlockReq [$sBlockReq]\n", 3, LOG_FILENAME);

    if (trim($sBlockReq) != '')
    {
		//print_r($sBlockReq);
          if (GetDataReadyToPrint($sBlockReq, $aResponse))
          {
            $aResponse['success'] = true;
          }
          else 
          {
            $aResponse['errcode'] = -1; // return empty or fail

          }
    }
    else // $sBlockReq == ''
    {
      $aResponse['errcode'] = -2; // request decode return empty string
    }
}
else
{
  $aResponse['errcode'] = -3; // invalid request (require more specific stuffs)
}

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aResponse [".print_r($aResponse, true)."]\n", 3, LOG_FILENAME);

$sResponse = $json->encode($aResponse);

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] JSON Response [$sResponse]\n", 3, LOG_FILENAME);

//header("content-type: application/json; charset=utf-8");

echo $sResponse;

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
	$iEnd = microtime(true);
	$iExec = $iEnd - $iStart;
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, LOG_FILENAME);
}

ob_end_flush();

?>