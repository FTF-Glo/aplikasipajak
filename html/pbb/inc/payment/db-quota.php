<?php
set_time_limit(0);


function getExpression($sExpression, $sTable, $sCriteria, $bSinggleRecord, $LinkDB) { 
	$sResult='';
	$sQ = "SELECT $sExpression as sResult";
	if ($sTable!='') {
		$sQ = $sQ." from $sTable";
		if  ($sCriteria!='')
			$sQ = $sQ." where $sCriteria";
	}
	if ($res = mysqli_query($LinkDB, $sQ))
	{
		   if ($row = mysqli_fetch_array($res)) {
				$sResult = $row["sResult"];	
				if (!$bSinggleRecord) {
					while ($row = mysqli_fetch_array($res)) 
					{
						$sResult = $sResult.",".$row["sResult"];	
					}
				}
		   }
	};
	mysqli_free_result($res);
	return 	$sResult;
 }

function getExpression1($sExpression, $sTable, $sCriteria, $bSinggleRecord, $LinkDB) { 
	$sResult='';
	$sQ = "SELECT $sExpression as sResult";
	if ($sTable!='') {
		$sQ = $sQ." from $sTable";
		if  ($sCriteria!='')
			$sQ = $sQ." where $sCriteria";
	}
	echo "Query: $sQ\n";	
	if ($res = mysqli_query($LinkDB, $sQ))
	{
		   if ($row = mysqli_fetch_array($res)) {
				$sResult = $row["sResult"];	
				if (!$bSinggleRecord) {
					while ($row = mysqli_fetch_array($res)) 
					{
						$sResult = $sResult.",".$row["sResult"];	
					}
				}
		   }
	};
	mysqli_free_result($res);
	return 	$sResult;
 }

function sGetBufferAccount($sPPID, $LinkDB) {
    $sParent = "";
    $sParent = getExpression("CSC_BA_PPID_P", "csccore_buffer_account", " (CSC_BA_PPID='".$sPPID."' || CSC_BA_PPID_P='".$sPPID."') limit 0,1", true, $LinkDB); // check Buffer Account
	return $sParent;
}

function sGetBufferAccount_V2($sPPID, $sDate, $LinkDB) {
    $sParent = "";
	$sParent = getExpression("CSC_BA_PPID_P", "csccore_buffer_account", " (CSC_BA_PPID='".$sPPID."' || CSC_BA_PPID_P='".$sPPID."') and  CSC_BA_EXP_DT >= '$sDate' and CSC_BA_START_DT <= '$sDate' limit 0,1", true, $LinkDB); // check Buffer Account
	return $sParent;
}

 function getBuffAcntMember($sPPID, &$sMemberList, $LinkDB) {
    $sParent = "";
	$sPPIDCriteria = "";
	$sMemberList = "";

	$sParent = sGetBufferAccount($sPPID, $LinkDB);
        $sPPIDCriteria = "('$sPPID')";
	
	if ($sParent!="") {
		$sQ = "select CSC_BA_PPID from csccore_buffer_account where CSC_BA_PPID_P ='$sParent' and CSC_BA_EXP_DT >= CURDATE() order by CSC_BA_PPID";
		if ($res = mysqli_query($LinkDB, $sQ))
		{
			$sPPIDChildCriteriaToday = "";
			$sCount = 0;
			while ($row = mysqli_fetch_array($res)) {
				  if ($sCount!=0) {
						$sPPIDChildCriteriaToday = $sPPIDChildCriteriaToday.",";
				  }
				  $sPPIDChildCriteriaToday = $sPPIDChildCriteriaToday."'".$row['CSC_BA_PPID']."'";
				  $sCount = $sCount + 1;

			}
			if ($sCount > 0) {
					$sPPIDCriteria = "('$sParent',".$sPPIDChildCriteriaToday.")";
			} else {
					$sPPIDCriteria = "('$sParent','$sPPID')";
			}
		}
		mysqli_free_result($res);

  }
   $sMemberList = $sPPIDCriteria;
   return $sParent;
 }

 function getBuffAcntMember_V2($sPPID, $sDate, &$sMemberList, $LinkDB) { // 
    $sParent = "";
	$sPPIDCriteria = "";
	$sMemberList = "";

	$sParent = sGetBufferAccount($sPPID, $LinkDB);
    $sPPIDCriteria = "('$sPPID')";
	
	if ($sParent!="") {
		$sQ = "select CSC_BA_PPID from csccore_buffer_account where CSC_BA_PPID_P ='$sParent' and CSC_BA_EXP_DT >= '$sDate' and CSC_BA_START_DT <= '$sDate' order by CSC_BA_PPID";
		if ($res = mysqli_query($LinkDB, $sQ))
		{
			$sPPIDChildCriteriaToday = "";
			$sCount = 0;
			while ($row = mysqli_fetch_array($res)) {
				  if ($sCount!=0) {
						$sPPIDChildCriteriaToday = $sPPIDChildCriteriaToday.",";
				  }
				  $sPPIDChildCriteriaToday = $sPPIDChildCriteriaToday."'".$row['CSC_BA_PPID']."'";
				  $sCount = $sCount + 1;

			}
			if ($sCount > 0) {
					$sPPIDCriteria = "('$sParent',".$sPPIDChildCriteriaToday.")";
			} else {
					$sPPIDCriteria = "('$sParent','$sPPID')";
			}
		}
		mysqli_free_result($res);

  }
   $sMemberList = $sPPIDCriteria;
   return $sParent;
 }

function isBailout($sPPID, $sDate, $LinkDB) {
	$nRes = intval(getExpression("count(*)", "CSCMOD_EL_POST_DEPO_REQ", "CSM_DR_PPID='$sPPID' and CSM_DR_ISAPPROVED=1 and CSM_DR_DT like '$sDate%' and (RIGHT(CSM_DR_AMOUNT,3)='407' or RIGHT(CSM_DR_AMOUNT,3)='907')", false, $LinkDB));
	return ($nRes>0); 
 }


function getEfectiveBalance($sPPID, $LinkDB) {
	return doubleval(getExpression("CSM_DS_AMOUNT", "cscmod_el_post_depo_summary", "CSM_DS_PPID='".$sPPID."'", true, $LinkDB));
}

function setEfectiveBalance($sPPID, $sEndBalance, $LinkDB) {
	$sQ = "	update cscmod_el_post_depo_summary set CSM_DS_AMOUNT=$sEndBalance where CSM_DS_PPID='$sPPID'";
	mysqli_query($LinkDB, $sQ);
 }

function getOpeningBalance($sPPID, $sDate, $LinkDB) { // format CCCCMMDD
	$sBalance=0;
	$sDateMySql = substr($sDate, 0, 4)."-".substr($sDate, 4, 2)."-".substr($sDate, 6, 2);
	
	$sParent = sGetBufferAccount($sPPID, $LinkDB); // check Buffer Account

	$isBufferAccount = ($sParent!="");
	if ($isBufferAccount)
	    $sPPID =  $sParent;

	$sQ = "select CSM_TBD_ENDBALANCE from cscmod_el_post_trans_balance_daily where CSM_TBD_PPID='$sPPID' and   CSM_TBD_DATE=date_format(DATE_SUB('$sDateMySql',INTERVAL 1 DAY), '%Y%m%d')";
	if ($res = mysqli_query($LinkDB, $sQ))
	{
	   if ($row = mysqli_fetch_array($res)) {
		  $sBalance = $row['CSM_TBD_ENDBALANCE'];
	   }
	}
	mysqli_free_result($res);
	return 	$sBalance;
 }


function getRealBalance($sPPID, $LinkDB)
{

	$sParent = getBuffAcntMember($sPPID, $sPPIDCriteria, $LinkDB); // checking buffer account

	$sOpeningBalance=doubleval(0);
	$sDate = date('Ymd');
    $sOpeningBalance = doubleval(getOpeningBalance($sPPID, $sDate,$LinkDB));

    $sTotalDeposit = doubleval(0);
    $sTotalTrans   = doubleval(0);
	$sBalance = doubleval(0);
	$dBalance = doubleval(0);
	$sDateMySql = "";

	$sDateMySql = getExpression('CURRENT_DATE', '', '', true, $LinkDB);

	$sQ = "select CSC_TD_TABLE,CSC_TD_CRITERIA,CSC_TD_BILL_AMOUNT_COLUMN from csccore_transaction_definition where CSC_TD_TYPE_TRX='-'";
    if ($res1 = mysqli_query($LinkDB, $sQ))
    {
		$dBalance = doubleval(0);
		while ($row1 = mysqli_fetch_array($res1))
		{
				 $sCriteria = str_replace("CCCC-MM-DD", $sDateMySql, $row1["CSC_TD_CRITERIA"]);
				 $sCriteria = str_replace("\$PPID\$", $sPPIDCriteria, $sCriteria);
				 $sTableName = $row1['CSC_TD_TABLE'];
				 $dBalance += doubleval(getExpression('(-1)*'.str_replace('TOTAL_TAG', '', $row1["CSC_TD_BILL_AMOUNT_COLUMN"]), $sTableName, $sCriteria,true, $LinkDB));
		}

	}
	mysqli_free_result($res1);

	$sQ = "select CSC_TD_TABLE,CSC_TD_CRITERIA,CSC_TD_BILL_AMOUNT_COLUMN from csccore_transaction_definition where CSC_TD_TYPE_TRX='+'";
    if ($res1 = mysqli_query($LinkDB, $sQ))
    {
		while ($row1 = mysqli_fetch_array($res1))
		{
				 $sCriteria = str_replace("CCCC-MM-DD", $sDateMySql, $row1["CSC_TD_CRITERIA"]);
				 $sCriteria = str_replace("\$PPID\$", $sPPIDCriteria, $sCriteria);
				 $sTableName = $row1['CSC_TD_TABLE'];
				 $dBalance += doubleval(getExpression('(1)*'.str_replace('TOTAL_TAG', '', $row1["CSC_TD_BILL_AMOUNT_COLUMN"]), $sTableName, $sCriteria,true, $LinkDB));
		}

	}
	mysqli_free_result($res1);
	
	$dBalance = $dBalance + $sOpeningBalance;
	return $dBalance;

} // end of balance

function RemoveBalance($sPPID, $sDate, $LinkDB) { // in format CCCCMMDD
	$sQ = "delete from cscmod_el_post_trans_balance_daily where CSM_TBD_PPID='$sPPID' and CSM_TBD_DATE ='$sDate'";
	mysqli_query($LinkDB, $sQ);

}

function RemoveRealBalance($sPPID, $sDate, $LinkDB) { // in format CCCCMMDD
	$sParent = sGetBufferAccount($sPPID, $LinkDB); // check Buffer Account

	$isBufferAccount = ($sParent!="");
	if ($isBufferAccount)
	    $sPPID =  $sParent;
	
	$sQ = "delete from cscmod_el_post_trans_balance_daily where CSM_TBD_PPID='$sPPID' and CSM_TBD_DATE ='$sDate'";
	mysqli_query($LinkDB, $sQ);

}

function setRealBalance($sPPID, $sDate, $LinkDB) { // format CCCCMMDD
	$sDateMySql = substr($sDate, 0, 4)."-".substr($sDate, 4, 2)."-".substr($sDate, 6, 2);

	// check buffer account 
	$sParent = getBuffAcntMember($sPPID, $sPPIDCriteria, $LinkDB); // checking buffer account

	$isBufferAccount = ($sParent!="");
	if ($isBufferAccount)
	    $sPPID =  $sParent;

	$sStartBalance = doubleval(getOpeningBalance($sPPID, $sDate,$LinkDB));

	if (!$isBufferAccount) {
		$sQ = " Select sum(b.CSM_DD_NAMOUNT) as EndBalance FROM cscmod_el_post_trans_summary_daily b
								where b.CSM_DD_DT like '$sDateMySql%' 
								and b.CSM_DD_PPID='$sPPID'";
	} else {
		$sQ = " Select sum(b.CSM_DD_NAMOUNT) as EndBalance FROM cscmod_el_post_trans_summary_daily b
								where b.CSM_DD_DT like '$sDateMySql%' 
								and (b.CSM_DD_PPID in (select CSC_BA_PPID from csccore_buffer_account where CSC_BA_PPID_P='$sPPID') or  b.CSM_DD_PPID='$sPPID')";
	
	}

	if ($res = mysqli_query($LinkDB, $sQ))
	{
	   if ($row = mysqli_fetch_array($res)) {
		  $sBalance = $row['EndBalance'];
	   }
	}
	mysqli_free_result($res);

	$sEndBalance = doubleval($sStartBalance) + doubleval($sBalance); 

	echo "[$sDate] $sPPID ==> start= ".sprintf("%15s", number_format($sStartBalance, 0, ',', '.')).", balance=".sprintf("%15s", number_format($sBalance, 0, ',', '.')).", End= ".sprintf("%15s", number_format($sEndBalance, 0, ',', '.'))." \n";

	 $sUniqueID = md5(uniqid(recondaily));
	 $sQ = "insert into cscmod_el_post_trans_balance_daily (CSM_TBD_ID, CSM_TBD_PPID, CSM_TBD_DATE, CSM_TBD_SET_DT, CSM_TBD_ENDBALANCE) values ('$sUniqueID', '$sPPID','$sDate', from_unixtime(unix_timestamp(date(concat(substring('$sDate', 1, 4),'-', substring('$sDate', 5, 2), '-',substring('$sDate', 7, 2))))+23*3600), $sEndBalance)";
	 mysqli_query($LinkDB, $sQ);
	 if ($isBufferAccount) {
			$sQ = "update cscmod_el_post_depo_summary set CSM_DS_AMOUNT=0 where CSM_DS_PPID like '$sPPID'";
			mysqli_query($LinkDB, $sQ);		    
	}	

 }


function setBalance($sPPID, $sDate, $isBufferAccount, $LinkDB) { // format CCCCMMDD
	$sDateMySql = substr($sDate, 0, 4)."-".substr($sDate, 4, 2)."-".substr($sDate, 6, 2);

// jalankan proses rekap untuk semua payment point 
	$sStartBalance = doubleval(getOpeningBalance($sPPID, $sDate,$LinkDB));
	if (!$isBufferAccount) {
		$sQ = " Select sum(b.CSM_DD_NAMOUNT) as EndBalance FROM cscmod_el_post_trans_summary_daily b
								where b.CSM_DD_DT like '$sDateMySql%' 
								and b.CSM_DD_PPID='$sPPID'";
	} else {
		$sQ = " Select sum(b.CSM_DD_NAMOUNT) as EndBalance FROM cscmod_el_post_trans_summary_daily b
								where b.CSM_DD_DT like '$sDateMySql%' 
								and (b.CSM_DD_PPID in (select CSC_BA_PPID from csccore_buffer_account where CSC_BA_PPID_P='$sPPID') or  b.CSM_DD_PPID='$sPPID')";
	
	}

	if ($res = mysqli_query($LinkDB, $sQ))
	{
	   if ($row = mysqli_fetch_array($res)) {
		  $sBalance = $row['EndBalance'];
	   }
	}
	mysqli_free_result($res);

	$sEndBalance = doubleval($sStartBalance) + doubleval($sBalance); 

	echo "[$sDate] $sPPID ==> start= ".sprintf("%15s", number_format($sStartBalance, 0, ',', '.')).", balance=".sprintf("%15s", number_format($sBalance, 0, ',', '.')).", End= ".sprintf("%15s", number_format($sEndBalance, 0, ',', '.'))." \n";

	 $sUniqueID = md5(uniqid(recondaily));
	 $sQ = "insert into cscmod_el_post_trans_balance_daily (CSM_TBD_ID, CSM_TBD_PPID, CSM_TBD_DATE, CSM_TBD_SET_DT, CSM_TBD_ENDBALANCE) values ('$sUniqueID', '$sPPID','$sDate', from_unixtime(unix_timestamp(date(concat(substring('$sDate', 1, 4),'-', substring('$sDate', 5, 2), '-',substring('$sDate', 7, 2))))+23*3600), $sEndBalance)";
	 mysqli_query($LinkDB, $sQ);
	 if ($isBufferAccount) {
			$sQ = "update cscmod_el_post_depo_summary set CSM_DS_AMOUNT=0 where CSM_DS_PPID like '$sPPID'";
			mysqli_query($LinkDB, $sQ);		    
	}	

 }

function getTotalBailout($sPPID, $sDate, $LinkDB) {
	$dSum = doubleval(getExpression("sum(CSM_DR_AMOUNT)", "CSCMOD_EL_POST_DEPO_REQ", "CSM_DR_PPID='$sPPID' and CSM_DR_ISAPPROVED=1 and CSM_DR_DT like '$sDate%' and ( RIGHT(CSM_DR_AMOUNT,3)='407' or RIGHT(CSM_DR_AMOUNT,3)='907' )", false, $LinkDB));
	return ($dSum); 
 }


?>
