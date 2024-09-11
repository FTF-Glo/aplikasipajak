<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");


/*-- $ab = base64_decode($_GET['q']);
var_dump($ab); --*/


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
  // define("LOG_FILENAME","errorngaco");
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

function clean($str) { 
  $search  = array('&'    , '"'     , "'"    , '<'   , '>'    ); 
  $replace = array('&amp;', '&quot;', '&#39;', '&lt;', '&gt;' ); 

  $str = str_replace($search, $replace, $str); 
  return $str; 
} 

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
  $kec = "";
  $kel = "";
  
  for($i=0; $i<$n; $i++)
  {
    $sParams = trim($aParams[$i]);
    $jParams = $json->decode($aParams);
    $year = $jParams->y;
    $city = $jParams->c;
	$page = $jParams->pg;
	$limit = $jParams->lm;
	$kec = $jParams->kc;
	$kel = $jParams->kl;
  }

  //get transaction 
  $sQCondKec = "";
  $sQCondKel = "";
  
  if ($kec !="" && $kec !="1") $sQCondKec = " AND A.OP_KECAMATAN_KODE = '{$kec}'";
  if ($kel !="" && $kel !="11") $sQCondKel = " AND A.OP_KELURAHAN_KODE = '{$kel}'";
  
  $sQCond = " AND A.OP_KOTAKAB_KODE = '$city' AND A.SPPT_TAHUN_PAJAK = '$year' $sQCondKec $sQCondKel ";

  $sQ = "select A.NOP,
        A.SPPT_TAHUN_PAJAK,
        A.SPPT_TANGGAL_JATUH_TEMPO,
        A.SPPT_PBB_HARUS_DIBAYAR,
        LEFT(A.WP_NAMA,35) AS WP_NAMA,
        A.WP_TELEPON,
        A.WP_NO_HP,
        RIGHT(A.WP_ALAMAT,35) AS WP_ALAMAT,
        A.WP_RT,
        A.WP_RW,
        LEFT(A.WP_KELURAHAN, 35) AS WP_KELURAHAN,
        A.WP_KECAMATAN,
        A.WP_KOTAKAB,
        A.WP_KODEPOS,
        A.SPPT_TANGGAL_TERBIT,
        A.SPPT_TANGGAL_CETAK,
        A.OP_LUAS_BUMI,
        A.OP_LUAS_BANGUNAN,
        A.OP_KELAS_BUMI,
        A.OP_KELAS_BANGUNAN,
        A.OP_NJOP_BUMI,
        A.OP_NJOP_BANGUNAN,
        A.OP_LUAS_BUMI_BERSAMA,
        A.OP_LUAS_BANGUNAN_BERSAMA,
        A.OP_KELAS_BUMI_BERSAMA,
        A.OP_KELAS_BANGUNAN_BERSAMA,
        A.OP_NJOP_BUMI_BERSAMA,
        A.OP_NJOP_BANGUNAN_BERSAMA,
        A.OP_NJOP,
        A.OP_NJOPTKP,
        A.OP_NJKP,
        A.PBB_COLLECTIBLE,
        RIGHT(A.OP_ALAMAT,35) AS OP_ALAMAT,
        A.OP_RT,
        A.OP_RW,
        LEFT(A.OP_KELURAHAN,35) AS OP_KELURAHAN,
        A.OP_KECAMATAN,
        A.OP_KOTAKAB,
        A.OP_KELURAHAN_KODE,
        A.OP_KECAMATAN_KODE,
        A.OP_KOTAKAB_KODE,
        A.OP_PROVINSI_KODE,
        A.FLAG,
        A.SPPT_PBB_PENGURANGAN,
        A.SPPT_PBB_PERSEN_PENGURANGAN,
        A.OP_TARIF,
        A.SPPT_DOC_ID, C.CPC_KD_AKUN, C.CPC_NM_SEKTOR from cppmod_pbb_sppt_current A, cppmod_tax_kelurahan B, cppmod_pbb_jns_sektor C
	WHERE A.OP_KELURAHAN_KODE = B.CPC_TKL_ID AND B.CPC_TKL_KDSEKTOR=C.CPC_KD_SEKTOR {$sQCond} ORDER BY A.NOP ASC LIMIT {$page}, {$limit} ";

  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
//echo(LOG_FILENAME);
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
		$Response["sppt"][$i]["a"] = $row['SPPT_PBB_HARUS_DIBAYAR'];
		$Response["sppt"][$i]["wn"] = clean(trim($row['WP_NAMA']));
		$Response["sppt"][$i]["wa"] = clean(trim($row['WP_ALAMAT']));
		$Response["sppt"][$i]["wt"] = trim($row['WP_RT']);
		$Response["sppt"][$i]["ww"] = trim($row['WP_RW']);
		$Response["sppt"][$i]["wl"] = clean(trim($row['WP_KELURAHAN']));
		$Response["sppt"][$i]["wc"] = clean(trim($row['WP_KECAMATAN']));
		$Response["sppt"][$i]["wk"] = clean(trim($row['WP_KOTAKAB']));
		$Response["sppt"][$i]["wp"] = trim($row['WP_KODEPOS']);
		$Response["sppt"][$i]["st"] = trim($row['SPPT_TANGGAL_TERBIT']);
		$Response["sppt"][$i]["sc"] = trim($row['SPPT_TANGGAL_CETAK']);
		//$Response["sppt"][$i]["np"] = trim($row['NPWP']);
		$Response["sppt"][$i]["olbi"] = $row['OP_LUAS_BUMI'];
		$Response["sppt"][$i]["olbn"] = $row['OP_LUAS_BANGUNAN'];
		$Response["sppt"][$i]["okbi"] = trim($row['OP_KELAS_BUMI']);
		$Response["sppt"][$i]["okbn"] = trim($row['OP_KELAS_BANGUNAN']);
		$Response["sppt"][$i]["on"] = $row['OP_NJOP'];
		$Response["sppt"][$i]["onb"] = $row['OP_NJOP_BUMI'];
		$Response["sppt"][$i]["ong"] = $row['OP_NJOP_BANGUNAN'];
                
                $Response["sppt"][$i]["olbib"] = ($row['OP_LUAS_BUMI_BERSAMA'] == null)? '0':$row['OP_LUAS_BUMI_BERSAMA'];
		$Response["sppt"][$i]["olbnb"] = ($row['OP_LUAS_BANGUNAN_BERSAMA'] == null)? '0':$row['OP_LUAS_BANGUNAN_BERSAMA'];
		$Response["sppt"][$i]["okbib"] = ($row['OP_KELAS_BUMI_BERSAMA'] == null)? '':$row['OP_KELAS_BUMI_BERSAMA'];
		$Response["sppt"][$i]["okbnb"] = ($row['OP_KELAS_BANGUNAN_BERSAMA'] == null)? '':$row['OP_KELAS_BANGUNAN_BERSAMA'];
		$Response["sppt"][$i]["onbb"] = ($row['OP_NJOP_BUMI_BERSAMA'] == null)? '0':$row['OP_NJOP_BUMI_BERSAMA'];
		$Response["sppt"][$i]["ongb"] = ($row['OP_NJOP_BANGUNAN_BERSAMA'] == null)? '0':$row['OP_NJOP_BANGUNAN_BERSAMA'];
                
		$Response["sppt"][$i]["ontkp"] = $row['OP_NJOPTKP'];
		$Response["sppt"][$i]["onjkp"] = $row['OP_NJKP'];
		
		$Response["sppt"][$i]["oa"] = clean(trim($row['OP_ALAMAT']));
		$Response["sppt"][$i]["ot"] = trim($row['OP_RT']);
		$Response["sppt"][$i]["ow"] = trim($row['OP_RW']);
		$Response["sppt"][$i]["ol"] = clean(trim($row['OP_KELURAHAN']));
		$Response["sppt"][$i]["oc"] = clean(trim($row['OP_KECAMATAN']));
		$Response["sppt"][$i]["ok"] = clean(trim($row['OP_KOTAKAB']));
		$Response["sppt"][$i]["olc"] = trim($row['OP_KELURAHAN_KODE']);
		$Response["sppt"][$i]["occ"] = trim($row['OP_KECAMATAN_KODE']);
		$Response["sppt"][$i]["okc"] = trim($row['OP_KOTAKAB_KODE']);
		$Response["sppt"][$i]["opc"] = trim($row['OP_PROVINSI_KODE']);
		$Response["sppt"][$i]["flag"] = trim($row['FLAG']);
/* Toto */ $Response["sppt"][$i]["spp"] = trim($row['SPPT_PBB_PENGURANGAN']);
/* Toto */ $Response["sppt"][$i]["sppp"] = trim($row['SPPT_PBB_PERSEN_PENGURANGAN']);
/* Toto */ $Response["sppt"][$i]["otrf"] = rtrim($row['OP_TARIF'], '0');
/* Toto */ $Response["sppt"][$i]["sdi"] = trim($row['SPPT_DOC_ID']);
		$Response["sppt"][$i]["ak"] = trim($row['CPC_KD_AKUN']);
		$Response["sppt"][$i]["sk"] = trim($row['CPC_NM_SEKTOR']);
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
/*------echo "zzz "; -*/
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
