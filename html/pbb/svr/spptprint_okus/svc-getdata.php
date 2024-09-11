<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");


ob_start();

// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'svr'.DIRECTORY_SEPARATOR.'spptprint', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/log-payment.php");
require_once($sRootPath."inc/payment/sayit.php");

require_once("inc-payment-db-c.php");
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
// SCANPayment_ConnectToDB($DBLink, $DBConn, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME);
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

SCANPayment_Pref_GetAllWithFilter($DBLink, "PC.%", $aCentralPrefs);
//var_dump($aCentralPrefs);
//$oPPSession = new SCANPaymentPointDBSessionInCentral(0, LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

// ---------------
// LOCAL FUNCTIONS
// ---------------
function GetList($aParams,&$Response)
{
  global $iErrCode, $sErrMsg, $aClientVar, $aPrefs, $iPPtimeDiff, $DBLink, $json;
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
  }

  //get transaction
  //$sQCond = " where OP_KOTAKAB_KODE = '$city' AND SPPT_TAHUN_PAJAK = '$year' AND (PAYMENT_FLAG=0 OR PAYMENT_FLAG=2 OR PAYMENT_FLAG IS NULL OR PAYMENT_FLAG=3 )";
  $sQ = "select * from cppmod_pbb_sppt_current {$sQCond} ORDER BY NOP ASC";

 // $sQ .= $sQCond;

  //echo $sQ;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    $nRecord = $nRes;
    if ($nRes > 0)
    {
      $i = 0;
	  $Response['totalrow'] = $nRes;
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
		// $Response["sppt"][$i]["pf"] = trim($row['PAYMENT_FLAG']);
		// $Response["sppt"][$i]["pp"] = trim($row['PAYMENT_PAID']);
		// $Response["sppt"][$i]["pr"] = trim($row['PAYMENT_REF_NUMBER']);
		// $Response["sppt"][$i]["pb"] = trim($row['PAYMENT_BANK_CODE']);
		// $Response["sppt"][$i]["ps"] = trim($row['PAYMENT_SW_REFNUM']);
		// $Response["sppt"][$i]["pg"] = trim($row['PAYMENT_GW_REFNUM']);
		// $Response["sppt"][$i]["pi"] = trim($row['PAYMENT_SW_ID']);
		// $Response["sppt"][$i]["pm"] = trim($row['PAYMENT_MERCHANT_CODE']);
		// $Response["sppt"][$i]["pl"] = trim($row['PBB_COLLECTIBLE']);
		
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
		$Response["sppt"][$i]["flag"] = trim($row['FLAG']);
		//$Response["sppt"][$i]["CHECKED"] = 0;
		//$Response["sppt"][$i]["INDEX"] = 0;
		$i++;
	  }
	  $bOK=true;
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);

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


if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sQueryString] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);
	
$aResponse = array();
$aResponse['success'] = false;
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

          if (GetList($sBlockReq, $aResponse))
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