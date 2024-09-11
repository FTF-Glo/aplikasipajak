<?php
$sEffDate = Date('dm');
$sEffDateBefore = Date('dm',strtotime('0 days'));

function IsValidPPID($sPPID)
{
	global $sRootPath, $dbSpec;
	//create DB Link 
	// require_once($sRootPath."inc/payment/inc-payment-db-sw.php");
	// $iErrCode = 0;
	// $DBLink = NULL;
	// $DBConn = NULL;
	// SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_SW_DBHOST, ONPAYS_SW_DBUSER, ONPAYS_SW_DBPWD, ONPAYS_SW_DBNAME);
	// if ($iErrCode != 0) {
	  // $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  // if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		// error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	  // exit(1);
	// }
	
	$sQ = "SELECT CCD.* FROM csccore_central_downline CCD INNER JOIN csccore_ppid_account CPA ON CCD.CSC_CD_ID=CPA.CSC_PA_PPID WHERE CCD.CSC_CD_ID='$sPPID'";
	// echo $sQ;
	
	$bOK = $dbSpec->sqlQuery($sQ, $res);
	
	// echo $sQ;
	if ($bOK)
	{  
		// echo "bok = true<br>";
		$nRes = mysqli_num_rows($res);
		// echo "nres : ".$nRes;
		if($nRes > 0) { 
			$bOK = true;
		} else {
			$bOK = false;
		}
	}
	return $bOK;
}
function GetPPIDAccount($ppid) {
		$sQ = "SELECT CSC_PA_ACCOUNT FROM csccore_ppid_account WHERE CSC_PA_PPID = '" . $ppid . "' ";
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				if ($row = mysqli_fetch_array($res)) {
					$account = $row["CSC_PA_ACCOUNT"];
					return $account;
				}
			}
		}
		
		return null;
}

function UpdateReqFlag($id, $schksum,$sPostDate, $sAmount)
{
	global $User;
	$bOK = false;
	$sQ = "update central_dep_request_buffer set CTR_RB_ISREQ = 1 where CTR_RB_ID = '$id' and CTR_RB_CHKSUM='$schksum' and  CTR_RB_CREDIT=$sAmount and CTR_RB_DATE_POST='$sPostDate'";
	// echo "$sQ   ---\n";
	if ($User->sqlQuery($sQ, $res)) 
		$bOK = true;
	return $bOK;
}

function SaveData($oData)
{
	global $User,$area,$uname;
	$bOK=false;
	foreach($oData as $value)
	{
		if (IsValidPPID($value->ppid)){
			$account=GetPPIDAccount($value->ppid);
			if(!is_null($account))
			//if(IsReffNotExistInReq(trim($value->reff), $value->chksum, $value->date, str_replace(".","",$value->credit)))
			//if(true)
			{
				$sQ  = "INSERT INTO dep_request (DR_ID, DR_PPID, DR_ACCOUNT, DR_CREATED, DR_AMOUNT, DR_FLAG, DR_USER_CREATE, DR_USER_EDIT, DR_APPROVED, DR_AREA, DR_APPROVER)";
				$sQ .= "VALUE ('".c_uuid()."','".trim($value->ppid)."', '".$account."', NOW(), '".str_replace(".","",$value->credit)."', 0, '".$uname."', '', '', '".$area."','')";
				
				$bOK = $User->sqlQuery($sQ, $res);
				// echo "$sQ   ---\n";

				$sQ = "update central_dep_request_buffer set CTR_RB_PPID='".$value->ppid."' where CTR_RB_ID = '".$value->reff."' and CTR_RB_CHKSUM='".$value->chksum."' and CTR_RB_DATE_POST='".$value->date."' and CTR_RB_CREDIT=".str_replace(".","",$value->credit);
				$bOK = $User->sqlQuery($sQ, $res);
			} else {
				// echo "Already in req";
			}
			$bOK = UpdateReqFlag(trim($value->reff), $value->chksum, $value->date,str_replace(".","",$value->credit));
			// if ($bOK) {
				// echo "Update buffer flag success";
			// } else {
				// echo "Update buffer flag FAILED";
			// }
		} else {
			// echo "PPID not valid";
		}
	}
	return $bOK;
}

function IsReffNotExistInReq($reff, $chksum, $sPostDate, $sAmount)
{
	global $User;
	$bOK = false;
	$sQ = "select * from dep_request where CSM_DR_REFNO = '$reff' and CSM_DR_CHKSUM='$chksum' and CSM_DR_AMOUNT=$sAmount and CSM_DR_DATE_POST='$sPostDate'";
	//echo "$sQ   ---\n";
	if ($User->sqlQuery($sQ, $res))
	{
		$nRes = mysqli_num_rows($res);

	}	
	if($nRes == 0) 
		$bOK=true;
		
	return $bOK;
}

function IsReffNotExist($reff, $chksum, $sPostDate, $sAmount)
{
	global $User;
	$bOK = false;
	$sQ = "select * from central_dep_request_buffer where CTR_RB_ID = '$reff' and CTR_RB_CHKSUM='$chksum' and CTR_RB_DATE_POST='$sPostDate' and CTR_RB_CREDIT=$sAmount";
	
	if ($User->sqlQuery($sQ, $res))
	{
		$nRes = mysqli_num_rows($res);
	}
	
	if($nRes == 0) {
		$bOK=true;
	} else {
		$sQ = "update central_dep_request_buffer set CTR_RB_CHKCOUNTER=CTR_RB_CHKCOUNTER+1 where CTR_RB_ID = '$reff' and CTR_RB_CHKSUM='$chksum' and CTR_RB_DATE_POST='$sPostDate' and CTR_RB_CREDIT=$sAmount";
		$User->sqlQuery($sQ, $res);
	}
	return $bOK;
}

function SaveDataBuffer($aData,$sAccountNumber)
{

	global $User,$sEffDate,$sEffDateBefore;
	$bOK = false;
	$sPPID = '';
	foreach($aData as $key=>$value){
		if($key == 'description')
		{
			//identify PPID
			$sPPID = GetPPID($value);
			if($sPPID=="")GetPPID2($value);
			if($sPPID=="")GetPPID3($value);
		}
	}
	
	//simpan ke database
	
	//if (intval($aData["eff_date"])==intval($sEffDate) or intval($aData["eff_date"])==intval($sEffDateBefore) ) {
		$sQ = "insert into central_dep_request_buffer (";
		$sQ .= "CTR_RB_ID,";
		$sQ .= "CTR_RB_BRANCH,";
		$sQ .= "CTR_RB_DATE_EFF,";
		$sQ .= "CTR_RB_DATE_POST,";
		$sQ .= "CTR_RB_CODE,";
		$sQ .= "CTR_RB_DESC,";
		$sQ .= "CTR_RB_DEBIT,";
		$sQ .= "CTR_RB_CREDIT,";
		$sQ .= "CTR_RB_BALANCE,";
		$sQ .= "CTR_RB_PPID,CTR_RB_VSI_ACCOUNT,CTR_RB_CHKSUM,CTR_RB_DATE_INSERT) values (";
		$sQ .= "'".$aData["reff"]."',";
		$sQ .= "'".$aData["branch"]."',";
		$sQ .= "'".$aData["eff_date"]."',";
		$sQ .= "'".$aData["post_date"]."',";
		$sQ .= "'".$aData["code"]."',";
		$sQ .= "'".mysqli_real_escape_string($this->DBLink, $aData["description"])."',";
		$sQ .= "'".(ltrim($aData["debit"],"0"))."',";
		$sQ .=  "'".(ltrim($aData["credit"],"0"))."',";
		$sQ .=  "'".(ltrim($aData["balance"],"0"))."',";
		$sQ .= "'$sPPID', '$sAccountNumber', '".md5($aData["post_date"].$aData["eff_date"].$aData["reff"].$aData["description"].$aData["credit"])."', now())";
		//echo "data=".($aData["post_date"].$aData["eff_date"].$aData["reff"].$aData["description"].$aData["credit"]);
		if (IsReffNotExist($aData["reff"], md5($aData["post_date"].$aData["eff_date"].$aData["reff"].$aData["description"].$aData["credit"]),$aData["post_date"],doubleval($aData["credit"])) && $User->sqlQuery($sQ, $res)) {
			$bOK=true;
			// echo "not";
		} else {
			// echo "exist";
		}
	//} else {
	//	echo "<!-- Eff Date = ".$aData["eff_date"].", PostDate = ".$aData["post_date"]." -->\n";
	//	echo "Request Time Limit has expired for Request ID : ".$aData["reff"]."<br>";
	//} 

	return $bOK;
}

function CleanDataMinusPos($sAccountNumber) {
	global $User;
	// cleansing deposit negatif
	$sQ = "select * from central_dep_request_buffer where CTR_RB_VSI_ACCOUNT='$sAccountNumber' and  date(CTR_RB_DATE_INSERT)=date(now()) and CTR_RB_CREDIT < 0 and CTR_RB_ISREQ=0";
	if ($User->sqlQuery($sQ, $res))
	{
		while ($row = mysqli_fetch_array($res))
		{
			$sDesc = $row['CTR_RB_DESC'];
			$sDBID = $row['CTR_RB_ID'];
			$sDBDATEPOST = $row['CTR_RB_DATE_POST'];
			$sDBCHKSUM = $row['CTR_RB_CHKSUM'];
			$sDBCREDIT = abs($row['CTR_RB_CREDIT']);

			$sQ = "update central_dep_request_buffer set CTR_RB_ISREQ=5 where CTR_RB_ID = '".$sDBID."' and CTR_RB_CHKSUM='".$sDBCHKSUM."' and CTR_RB_DATE_POST='".$sDBDATEPOST."' and abs(CTR_RB_CREDIT)=".$sDBCREDIT;
			$User->sqlQuery($sQ, $tRes);
		}
	}

}

function ReadDataBuffer(&$aData,$sAccountNumber,$sTypeView)
{
	global $User;
	$sCondView = "";
	switch ($sTypeView) {
		case 0 : $sCondView = " and CTR_RB_ISREQ=0"; break;
		case 1 : $sCondView = " and CTR_RB_ISREQ=1 and date(CTR_RB_DATE_INSERT)=date(now())"; break;
		case 2 : $sCondView = " and (CTR_RB_ISREQ=0 or date(CTR_RB_DATE_INSERT)=date(now()))"; break;
		default : break;

	}
	$sQ = "select * from central_dep_request_buffer where CTR_RB_VSI_ACCOUNT='$sAccountNumber' $sCondView";
	// echo $sQ;
	if ($User->sqlQuery($sQ, $res))
	{
		while ($row = mysqli_fetch_array($res))
		{
			$aData[] = array(
				"post_date" => $row["CTR_RB_DATE_POST"],
				"eff_date" => $row["CTR_RB_DATE_EFF"],
				"branch" => $row["CTR_RB_BRANCH"],
				"reff" => $row["CTR_RB_ID"],
				"code" => $row["CTR_RB_CODE"],
				"description" => $row["CTR_RB_DESC"],
				"debit" => $row["CTR_RB_DEBIT"],
				"credit" => $row["CTR_RB_CREDIT"],
				"balance" => $row["CTR_RB_BALANCE"],
				"ppid"=>$row["CTR_RB_PPID"],
				"flag"=>$row["CTR_RB_ISREQ"],
				"chkcounter"=>$row["CTR_RB_CHKCOUNTER"],
				"chksum"=>$row["CTR_RB_CHKSUM"]
			);
		}
	}
	
}

function strnpos($base, $str, $n)
{        
        if ($n <= 0 || intval($n) != $n || substr_count($base, $str) < $n)  return FALSE;
        
        $str = strval($str);
        $len = 0;
        
        for ($i=0 ; $i<$n-1 ; ++$i)
        {
            if ( strpos($base, $str) === FALSE ) return FALSE;
            
            $len += strlen( substr($base, 0, strpos($base, $str) + strlen($str)) );
            
            $base = substr($base, strpos($base, $str) + strlen($str) );
        }
        return strpos($base, $str) + $len;
}

function GetPPID2($sData)
{
	$bLog = false;
	$sPPID = '';
	$sData = strtoupper(trim($sData));
	$sData = str_replace(" ","",$sData);
	if ($bLog)
          echo "sData = $sData ";
	if(ereg("([0-9,a-z]{0,16})",$sData,$res))
	{   
		if ($bLog)
			echo " --> ".strtoupper($res[1])." --> ";
		if(IsValidPPID(trim(strtoupper($res[1])))) {
			$sPPID = trim(strtoupper($res[1]));
		} else {
			$sData = ereg_replace('NO|LOKET|SET TN|SETORAN|TUNAI|PPID|ATM|TRF|PPID|TFR|BER|BERS|\(|\/)',"",$sData);
			$sData_0 = $sData;
			if ($bLog)
				echo " sData_0 = $sData_0 -->";
			$sData = ereg_replace('(.*[\:|\;]+)',"",$sData);
			$sData_1 = $sData;
			if ($bLog)
				echo " sData_1 = $sData_1";
			if(ereg("([0-9,a-z]{0,16})",$sData_0,$res)) {
				if ($bLog)
					echo " --> ".strtoupper($res[1]);
				if(IsValidPPID(trim(strtoupper($res[1])))) {
					$sPPID = trim(strtoupper($res[1]));
				} else {
					if ($bLog)
						echo " Cari yg lain $sData_0 --> ";
					$iIdx = 1;
					$iPos = -1;
					$iPos = strnpos($sData_0, "52", $iIdx); 

					if ($iPos>=0) 
					{
						if ($bLog)
							echo " ($iPos) --> ".substr($sData_0, $iPos, 16);
						if(IsValidPPID(trim(strtoupper(substr($sData_0, $iPos, 16))))) {
							$sPPID = trim(strtoupper(substr($sData_0, $iPos, 16)));
						}
					}
				}
			}

		}
		
	}
    if ($bLog)
		echo "<BR>";
	return $sPPID;
}

function GetPPID3($sData)
{
	$bLog = false;
	$sPPID = '';
	$sData = strtoupper(trim($sData));
	$sData = str_replace(" ","",$sData);
    $spart=$sData;
	$lpart=strlen($spart);
	 if($lpart>=16){
			$end=$lpart-16;
			for($j=0;$j<=$end;$j++){
				$cpart=substr($spart,$j,16);
				if ($bLog)
					echo "cpart = $cpart<br> ";
				if(IsValidPPID($cpart)){
					$sPPID=$cpart;
					break;
				}
			}
   }
   
	if ($bLog)
		echo "<BR>";
	return $sPPID;
}

function GetPPID($sData)
{
	$bLog = false;
	$sPPID = '';
	$sData = strtoupper(trim($sData));
	if ($bLog)
          echo "sData = $sData ";
   $arrData=explode(" ",$sData);
   $n=count($arrData);
   for($i=0;$i<$n;$i++){
	   if ($bLog)
          echo "spart = $spart<br> ";
	   $spart=$arrData[$i];
	   $lpart=strlen($spart);
	   if($lpart>=16){
			$end=$lpart-16;
			for($j=0;$j<=$end;$j++){
				$cpart=substr($spart,$j,16);
				if ($bLog)
					echo "cpart = $cpart<br> ";
				if(IsValidPPID($cpart)){
					$sPPID=$cpart;
					break;
				}
			}
			if($sPPID!=""){
				break;
			}
	   }
   }
	if ($bLog)
		echo "<BR>";
	return $sPPID;
}

function PrintHeader($aData)
{
	echo '<table>';

	foreach($aData as $key=>$value)
	{
		if($key == 'account')
		{
			echo '<tr><td>&nbsp;<td><td>:';
			printf('%ld', $value);
			echo '</td></tr>';
		}

	} 
	echo '</table>';
}

function getVal($aData, $sKey)
{
    $sRes = '';
	foreach($aData as $key=>$value)
	{
		if($key == 'account')
		{
			$sRes = sprintf('%ld',$value);
		}

	} 
	return $sRes;
}

function PrintBodyBulk($sAccountNumber, &$sNumberOfRecord, $sTypeView)
{
	global $application,$module,$function;
	$aData = array();
	ReadDataBuffer($aData,$sAccountNumber,$sTypeView);
	//var_dump($aData);
    $isDebetOrBalance = false;
	$iRecord=0;
	$sValue='';
	// echo "<pre>";
	// print_r($aData);
	// echo "</pre>";
	foreach($aData as $vData)
	{
		$sRowTxt = "";
		$sRowContent = ""; 
		$checkDebit = 0;
		$isDebet = false;
		$isCheckProcess = false;
		$isWonderMustBeDeleted = false;
		foreach($vData as $key=>$value){
				if($key == "debit") {
					$checkDebit = doubleval($value);
				} 
				if($key == 'credit')
				{
					$sRowContent .= '<td align="right">';
					$sRowContent .= number_format($value, 0, ',', '.');
					$sRowContent .='</td>';
				}
				elseif($key == 'ppid')
				{
					$sRowContent .= '<td><input name="ppid" id="'.$vData["reff"].$iRecord.'-text" type="text" value="'.$value.'" disableSelection="false"></input></td>';
				}
				elseif($key == 'chkcounter')
				{
					$isWonderMustBeDeleted = (intval($value)==0);
				}
				elseif($key == 'flag')
				{
					$text = ($value==1)?'Sudah':'Belum';
					if ($text=='Sudah')
						$isCheckProcess = true;
					$sRowContent .= '<td>'.$text.'</td>';
				}
				elseif($key == "chksum")
				{
					$sRowContent .='<td style="display:none;">'.$value.'</td>';
				}
				elseif($key != "debit" && $key != "balance")
				{
					$sRowContent .='<td disableSelection="false">'.$value.'</td>';
				}
		}
		
		if ($checkDebit>0)  {
			$isDebet = true;
		}
		
		$sRowTxt .=	$sRowContent;
		if ($isCheckProcess) {
			$sRowTxt .='<td><img id="'.$vData["reff"].$iRecord.'" title="buka jendela filter PPID" src="image/icon/find.png"></td>';
		} else {
			$sRowTxt .='<td><img id="'.$vData["reff"].$iRecord.'" title="buka jendela filter PPID" src="image/icon/find.png" onclick="checkAvailablePpid(document.getElementById('."'".($vData["reff"].$iRecord.'-text')."').value,'".$application."','".$module."','".$function."','".($vData["reff"].$iRecord.'-text')."'".')" style="cursor:pointer"></td>';
		}

		$sRowTxt .='<td><input id="'.$vData["reff"].$iRecord.'-cb" type="checkbox" onchange="return validateCheck(this)"></input></td>';
		
		$sTagOpen="<font style=\"color:red\"";
		$sTagClose="</font>";
		if ($isWonderMustBeDeleted) {
			$sRowTxt = "<tr style=\"color:red\">".$sRowTxt."</tr>";
		} else {
			if ($isCheckProcess) {
				$sRowTxt = "<tr style=\"color:green\">".$sRowTxt."</tr>";
			}
			else {
				$sRowTxt = "<tr>".$sRowTxt."</tr>";
			}	
		}
		if (!$isDebet) {
			echo $sRowTxt;
			echo "\n";
			$iRecord++;
		}

	}
	$sNumberOfRecord = $iRecord;
}

?>
