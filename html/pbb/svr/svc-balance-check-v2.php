<?php
// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();

// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/log-payment.php");

require_once($sRootPath."inc/electricity/electricity-protocol-generic.php");
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/deposit/deposit-protocol-balance-check.php");
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
SCANPayment_Pref_GetAllWithFilter($DBLink, "PC.%", $aCentralPrefs);
//var_dump($aCentralPrefs);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

// ---------------
// LOCAL FUNCTIONS
// ---------------

function BalanceCheck($acc, $dt) 
{
	global $aResponse, $arAreaConfig;
	$balance = 0;
	
	// echo "<pre>";
	// print_r($arAreaConfig);
	// echo "</pre>";
	$BalanceReq = new DepositProtocolBalanceCheckRequest();
	$BalanceReq->SetComponentTmp('pan', $acc);
	$BalanceReq->SetComponentTmp('dt', 	$dt);
	$BalanceReq->ConstructStream();
	$sBalanceRequestStream = $BalanceReq->GetConstructedStream();
	
	// echo "stream request payment:";
	// echo "<pre>[".$sBalanceRequestStream."]</pre>";
	$bOK = GetRemoteResponse($arAreaConfig['ServerAddressDep'],$arAreaConfig['ServerPortDep'],$arAreaConfig['ServerTimeOut'], $sBalanceRequestStream, $sResp);

	// echo "stream response";
	// echo "<pre>[".$sResp."]</pre>";
	// echo "nilai bok:".$bOK;
	
	if($bOK == 0)
	{
		$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
		$Res = new DepositProtocolBalanceCheckResponse($sResp);
		$Res->ExtractDataElement();
		
		// echo "<pre>";
		// print_r($Res->dataElement);
		// echo "</pre>";
		
		if (($Res->dataElement['mti'] == '2210') && ($Res->dataElement['rc'] == '0000')) // valid balance check response
		{
			$balance = ltrim($Res->dataElement['priv']['amount'], "0");
		}
	}
	return $balance;
}

function getOpeningBalance($sPPID, $sDate) {
        global $DBLink;
        $sBalance=0;
        $sDateMySql = substr($sDate, 0, 4)."-".substr($sDate, 4, 2)."-".substr($sDate, 6, 2);
        if ($DBLink)
          {
                // jalankan proses rekap untuk semua payment point 
                $sQ = "select CSM_TBD_ENDBALANCE from CSCMOD_EL_POST_TRANS_BALANCE_DAILY where CSM_TBD_PPID='$sPPID' and   CSM_TBD_DATE=date_format(DATE_SUB('$sDateMySql',INTERVAL 1 DAY), '%Y%m%d')";
                if ($res = mysqli_query($DBLink, $sQ))
                {
                           if ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
                                  $sBalance = $row['CSM_TBD_ENDBALANCE'];
                           }
                }
        }
        return  $sBalance;
 }

function getEfectiveBalance($sPPID) {
    global $DBLink;
        $sBalance=doubleval(0);
          if ($DBLink)
          {
                        $sQ = "SELECT CSM_DS_AMOUNT FROM CSCMOD_EL_POST_DEPO_SUMMARY where CSM_DS_PPID='".$sPPID."'";
                        if ($res = mysqli_query($DBLink, $sQ))                        {                                   if ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {                                          $sBalance = doubleval($row['CSM_DS_AMOUNT']);                                   }                        }  // end if $res
          }
        return  $sBalance;
}

function getRealBalance($sPPID)
{

     global $DBLink;
	// check apakah type PP-nya menggunakan account buffer
	$sParent = "";
	$sPPIDChildCriteriaToday = "";
	$sPPIDChildCriteriaDepo = "";
	$sDateMin = Date("Y-m-d 00:00:00");
	$sDateMax = Date("Y-m-d 23:59:59");
	
	$sQ = "select CSC_BA_PPID_P from csccore_buffer_account where CSC_BA_PPID='$sPPID' || CSC_BA_PPID_P='$sPPID'";
    if ($res = mysqli_query($DBLink, $sQ))
    {
		   if ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
				$sParent = $row['CSC_BA_PPID_P'];
				
				$sPPIDChildCriteriaToday = "";
				$sQ = "select CSC_BA_PPID from csccore_buffer_account where CSC_BA_PPID_P='$sPPID' ";
				if ($res = mysqli_query($DBLink, $sQ))
				{
						$sPPIDChildCriteriaDepo = "";
						$sCount = 0;
						while ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
								  if ($sCount!=0) {
										$sPPIDChildCriteriaToday = $sPPIDChildCriteriaToday.",";
								  }
								  $sPPIDChildCriteriaToday = $sPPIDChildCriteriaToday."'".$row['CSC_BA_PPID']."'";
								  $sCount = $sCount + 1;
						}
						$sPPIDChildCriteriaDepo = " in (".$sPPIDChildCriteriaToday.")";
						$sPPIDChildCriteriaToday = " CSM_TM_PPID in (".$sPPIDChildCriteriaToday.") ";
				}
		   }
    }

	$sOpeningBalance=doubleval(0);
	$sDate = date('Ymd');
	if ($sParent!="") {
		   $sOpeningBalance = doubleval(getOpeningBalance($sParent, $sDate));
	} else {
		  $sOpeningBalance = doubleval(getOpeningBalance($sPPID, $sDate));
    } 
//	echo "sOpeningBalance=$sOpeningBalance\n";
    $sTotalDeposit = doubleval(0);
    $sTotalTrans   = doubleval(0);
    $sBalance = doubleval(0);

	if ($sParent!="") {
		$sQ = "select Jumlah from ( (select DATE_FORMAT(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CSM_TM_TRANSACT_AMOUNT) Jumlah, count(CSM_TM_SUBID) as 'Jumlah Rekening', sum(CSM_TM_STATUS) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_EL_POST_TRAN_MAIN TM where $sPPIDChildCriteriaToday and CSM_TM_FLAG=1 and  CSM_TM_SETTLE_D <> '00000000' and CSM_TM_PAID is not null and CSM_TM_PAID >= '$sDateMin' and  CSM_TM_PAID <= '$sDateMax' group by Tanggal) union (select DATE_FORMAT(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CSM_TM_TRANSACT_AMOUNT) Jumlah, count(CSM_TM_MSN) as 'Jumlah Rekening', count(CSM_TM_MSN) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_EL_PRE_TRAN_MAIN TM where $sPPIDChildCriteriaToday and CSM_TM_FLAG=1  and CSM_TM_SETTLE_D <> '00000000' and date(CSM_TM_PAID) = date(now())  group by Tanggal) union(select DATE_FORMAT(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CPM_TRANS_TRANSACT_AMOUNT) Jumlah, count(CPM_TRANS_SUBID) as 'Jumlah Rekening', sum(CPM_TRANS_STATUS) as 'Jumlah Lembar', 'Debet-Telkom Product' Keterangan from  CSCMOD_PHONE_TRANSACTION TM where ".str_replace('CSM_TM_PPID', 'CPM_TRANS_PPID', $sPPIDChildCriteriaToday)." and CPM_TRANS_FLAG=1 and CPM_TRANS_SETTLE_D <> '00000000' and date(CPM_TRANS_PAID)=date(now()) group by Tanggal) union (select date_format(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CSM_TM_SELL_PRICE) Jumlah, count(CSM_TM_MSISDN) as 'Jumlah Rekening', count(CSM_TM_MSISDN) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_VOUCHER_TRAN_MAIN TM where $sPPIDChildCriteriaToday and CSM_TM_FLAG=1 and CSM_TM_SETTLE_DATE <> '00000000' and date(CSM_TM_PAID_DATE)=date(now())   group by Tanggal) union (select DATE_FORMAT(CSM_DRH_DT, '%Y-%m-%d %h:%i:%s') Tanggal, CSM_DRH_TO_AMOUNT Jumlah, '0', '0', 'Kredit' Keterangan  from CSCMOD_EL_POST_DEPO_RESET_HIST where (CSM_DRH_PPID $sPPIDChildCriteriaDepo or CSM_DRH_PPID='$sParent') and RIGHT(CSM_DRH_TO_AMOUNT,3)<>'407' and RIGHT(CSM_DRH_TO_AMOUNT,3)<>'907' and DATE_FORMAT(CSM_DRH_DT, '%Y%m%d')=DATE_FORMAT(NOW(), '%Y%m%d') )) as Baqir order by Tanggal, Jumlah";
	} else {
		$sQ = "select Jumlah from ( (select DATE_FORMAT(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CSM_TM_TRANSACT_AMOUNT) Jumlah, count(CSM_TM_SUBID) as 'Jumlah Rekening', sum(CSM_TM_STATUS) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_EL_POST_TRAN_MAIN where CSM_TM_PPID='$sPPID' and CSM_TM_FLAG=1  and  CSM_TM_SETTLE_D <> '00000000' and CSM_TM_PAID >= '$sDateMin' and  CSM_TM_PAID <= '$sDateMax' group by Tanggal) union (select DATE_FORMAT(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CSM_TM_TRANSACT_AMOUNT) Jumlah, count(CSM_TM_MSN) as 'Jumlah Rekening', count(CSM_TM_MSN) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_EL_PRE_TRAN_MAIN TM where CSM_TM_PPID='$sPPID' and CSM_TM_FLAG=1  and CSM_TM_SETTLE_D <> '00000000' and date(CSM_TM_PAID) = date(now())  group by Tanggal) union (select DATE_FORMAT(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CPM_TRANS_TRANSACT_AMOUNT) Jumlah, count(CPM_TRANS_SUBID) as 'Jumlah Rekening', sum(CPM_TRANS_STATUS) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_PHONE_TRANSACTION TM where CPM_TRANS_PPID='$sPPID' and CPM_TRANS_FLAG=1 and CPM_TRANS_SETTLE_D <> '00000000' and date(CPM_TRANS_PAID)=date(now())  group by Tanggal) union (select date_format(now(), '%Y-%m-%d %H:%i:%s') Tanggal, (-1)*sum(CSM_TM_SELL_PRICE) Jumlah, count(CSM_TM_MSISDN) as 'Jumlah Rekening', count(CSM_TM_MSISDN) as 'Jumlah Lembar', 'Debet' Keterangan from CSCMOD_VOUCHER_TRAN_MAIN TM where CSM_TM_PPID='$sPPID' and CSM_TM_FLAG=1 and CSM_TM_SETTLE_DATE <> '00000000' and date(CSM_TM_PAID_DATE)=date(now()) group by Tanggal) union (select DATE_FORMAT(CSM_DRH_DT, '%Y-%m-%d %h:%i:%s') Tanggal, CSM_DRH_TO_AMOUNT Jumlah, '0', '0', 'Kredit' Keterangan  from CSCMOD_EL_POST_DEPO_RESET_HIST where CSM_DRH_PPID='$sPPID' and RIGHT(CSM_DRH_TO_AMOUNT,3)<>'407' and RIGHT(CSM_DRH_TO_AMOUNT,3)<>'907' and DATE_FORMAT(CSM_DRH_DT, '%Y%m%d')=DATE_FORMAT(NOW(), '%Y%m%d'))  ) as Baqir order by Tanggal, Jumlah";
	}
//	echo "\n$sQ\n";
//        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] $sQ \n", 3, "/tmp/baqir-check-balance.log");

	if ($res = mysqli_query($DBLink, $sQ))
	{

		  while ($row = mysqli_fetch_array($res, MYSQL_ASSOC))
		  {

			if (doubleval($row['Jumlah'])<0)
			   $sTotalTrans =  $sTotalTrans + doubleval($row['Jumlah']);
			else
			   $sTotalDeposit =  $sTotalDeposit + doubleval($row['Jumlah']);

		  }
		  $sBalance = $sTotalDeposit + $sTotalTrans;
	}
	$sBalance = $sBalance + $sOpeningBalance;
//        error_log ("[".strftime("%Y-%m-%d %H:%M:%S", time())."] PPID=$sPPID, Opening Balance=$sOpeningBalance,Current Balance=$sBalance  \n", 3, "/tmp/baqir-check-balance.log");

	return $sBalance;

} // end of balance

function isBailout($sPPID, $sDate) {
    global $DBLink;

    $sQ = "select * from CSCMOD_EL_POST_DEPO_REQ where CSM_DR_PPID='$sPPID' and CSM_DR_ISAPPROVED=1 and date(CSM_DR_DT)='$sDate' and (RIGHT(CSM_DR_AMOUNT,3)='407' or RIGHT(CSM_DR_AMOUNT,3)='907') limit 0,1";
        $nRes = 0;
        if ($res = mysqli_query($DBLink, $sQ))
        {
                $nRes = mysqli_num_rows($res);
        }
        return ($nRes==1);
 }


function SCANPaymentCentral_PPCheckBalance($sPPID, &$aBalanceInfo)
{
  global $DBLink;
  $bOK =true;

  if ($sPPID != '')
  {
    $sQ  = "select CDD.CSM_CCD_FUND_TYPE TTYPE, DS.CSM_DS_AMOUNT BALANCE from csccore_central_downline CD left join CSCMOD_EL_POST_CENTRAL_DOWN_DTL CDD on CD.CSC_CD_ID=CDD.CSM_CCD_PPID left join CSCMOD_EL_POST_DEPO_SUMMARY DS on CD.CSC_CD_ID=DS.CSM_DS_PPID where CD.CSC_CD_ID='$sPPID'";
	//echo $sQ;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
    if ($res = mysqli_query($DBLink, $sQ))
    {
      $nRes = mysqli_num_rows($res);
      if ($nRes > 0)
      {
        if ($row = mysqli_fetch_array($res, MYSQL_ASSOC))
        {
          //var_dump($row);
          $aBalanceInfo['ttype'] = @intval($row['TTYPE']);
          if ($aBalanceInfo['ttype'] == 1){ // terminal/PP with deposit
            $aBalanceInfo['balance'] = isset($row['BALANCE']) ? $row['BALANCE'] : 0;
	    //bayu add second balance
	    $aBalanceInfo['balance2'] = isset($row['BALANCE']) ? $row['BALANCE'] : 0;
            $sParent = "";
            $sQ = "select CSC_BA_PPID_P from csccore_buffer_account where CSC_BA_PPID='$sPPID'";
            if ($res2 = mysqli_query($DBLink, $sQ))
            {
                if ($row2 = mysqli_fetch_array($res2, MYSQL_ASSOC)) {
                        $sParent = $row2['CSC_BA_PPID_P'];
                }
            }  // end if $res
            if ($sParent!="") {
                $sRealBalance = getRealBalance($sParent);
                $sEfectiveBalance = getEfectiveBalance($sParent);
                $aBalanceInfo['balance'] = $sRealBalance;
	        $aBalanceInfo['balance2'] = $sRealBalance;
            } else {
                $sRealBalance = getRealBalance($sPPID);
                $sEfectiveBalance = getEfectiveBalance($sPPID);
                $aBalanceInfo['balance'] = $sRealBalance;
	        $aBalanceInfo['balance2'] = $sRealBalance;
            }
			error_log ("[".strftime("%Y-%m-%d %H:%M:%S", time())."] PPID=$sPPID, Realbalance=$sRealBalance, EffBalance=$sEfectiveBalance, Selisih= ".($sEfectiveBalance-$sRealBalance)."  \n", 3, "/tmp/baqir-check-balance.log");
			if ($sEfectiveBalance != $sRealBalance) {
				if ( ($sEfectiveBalance < $sRealBalance) && ($sParent=="") ) {
					$sQ = "update CSCMOD_EL_POST_DEPO_SUMMARY set CSM_DS_AMOUNT=$sRealBalance where CSM_DS_PPID like '$sPPID'";
					mysqli_query($DBLink, $sQ);
					error_log ("[".strftime("%Y-%m-%d %H:%M:%S", time())."] updating EffBalance $sPPID with $sRealBalance\n", 3, "/tmp/baqir-check-balance.log");
				} else {
					$bBailout = isBailout($sPPID, date('Y-m-d'));
//					$bBailout = (isBailout($sPPID, date('Y-m-d')) || isBailout($sPPID, date('Y-m-d', strtotime('-1 days')))) ;

					if ((!$bBailout) && $sParent==""){
						$sQ = "update CSCMOD_EL_POST_DEPO_SUMMARY set CSM_DS_AMOUNT=$sRealBalance where CSM_DS_PPID like '$sPPID'";
						mysqli_query($DBLink, $sQ);
						error_log ("[".strftime("%Y-%m-%d %H:%M:%S", time())."] Warning $sPPID (sEfectiveBalance > sRealBalance)  updating EffBalance $sPPID with $sRealBalance\n", 3, "/tmp/baqir-check-balance.log");
					} else {
						error_log ("[".strftime("%Y-%m-%d %H:%M:%S", time())."] Warning $sPPID (sEfectiveBalance:$sEfectiveBalance > sRealBalance:$sRealBalance) ".(($bBailout)?" -> Bailout":"")."\n", 3, "/tmp/baqir-check-balance-eff_more_real.log");
					}
				}
			}

	  }
	}
      }
    }
    else
    {
      $iErrCode = -3;
      $sErrMsg = mysqli_error($DBLink);
      if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    }
  }
  else
  {
    $iErrCode = -712;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] Unable reconcil $sSubIDStream manually\n", 3, LOG_FILENAME);
  }
  return $bOK;
} // end of SCANPaymentCentral_PPCheckBalance

// ------------
// MAIN PROGRAM
// ------------

// get remote parameters
//$sQueryString = urldecode($_SERVER['QUERY_STRING']); // contains subscriber ids separated with @
$sQueryString = (@isset($_REQUEST['q']) ? $_REQUEST['q'] : ''); // because of post (form-urlencoded)
$sClientRemoteAddress = $_SERVER['REMOTE_ADDR'];

// TEST PURPOSE ONLY (comment all following test codes from operational use)
// end of TEST PURPOSE ONLY

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sQueryString] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);

$aResponse = array();
$aResponse['success'] = false;
$aResponse['errcode'] = 0;
$aResponse['balance'] = 0;
$aResponse['balance2'] = 0;


if ($sQueryString != '')
{
  $sBlockReq = base64_decode($sQueryString);

  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQueryString [$sQueryString] sBlockReq [$sBlockReq]\n", 3, LOG_FILENAME);
  
  if (isset($sBlockReq) != '')
  {
    $oReq=$json->decode($sBlockReq);
	$sPPID = $oReq->ppid;
    
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
      error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Got PPID [$sPPID]\n", 3, LOG_FILENAME);

    $aBalanceInfo = Array();
    $aBalanceInfo['ttype'] = 0;
    $aBalanceInfo['balance'] = 0;
    $aBalanceInfo['balance2'] = 0;
    if (SCANPaymentCentral_PPCheckBalance($sPPID, $aBalanceInfo))
    {
      $aResponse['success'] = true;
      $aResponse['ttype'] = $aBalanceInfo['ttype'];
      $aResponse['balance'] = $aBalanceInfo['balance'];
      //bayu add second balance
      $aResponse['balance2'] = $aBalanceInfo['balance2'];

    }
    else // deposit checking was failed
    {
      $aResponse['errcode'] = -401; // unable to do deposit checking

      if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] Unable to check deposit for PPID [$sPPID]\n", 3, LOG_FILENAME);
    }
  }
  else // $sBlockReq == ''
  {
    $aResponse['errcode'] = -999; // fatal error, no reconcil request stream
  }
}
else
{
  $aResponse['errcode'] = -501; // invalid request (require more specific stuffs)
}

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aResponse [".print_r($aResponse, true)."]\n", 3, LOG_FILENAME);

$sResponse = $json->encode($aResponse);

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] JSON Response [$sResponse]\n", 3, LOG_FILENAME);

header("content-type: application/json; charset=utf-8");
echo $sResponse;

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
{
	$iEnd = microtime(true);
	$iExec = $iEnd - $iStart;
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, LOG_FILENAME);
}

ob_end_flush();
?>

