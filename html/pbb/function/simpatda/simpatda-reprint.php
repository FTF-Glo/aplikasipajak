<?
global $data, $User,$appDbLink;
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");
ob_start();
// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'simpatda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/log-payment.php");
require_once($sRootPath."inc/simpatda/simpatda-lookup.php"); 
require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");


$PPID_setting=null;
$POSTPAID_setting=null;
SCANPayment_Pref_GetAllWithFilter($appDbLink,$data->ppid.".PP.simpatda.PC.print.%",$PPID_setting);
SCANPayment_Pref_GetAllWithFilter($appDbLink,"PP.%",$POSTPAID_setting);

function getTypePajak($id) {
	global $DBLink;
	$Qry = "SELECT CSM_TAX_NAME FROM CSCMOD_TAX_SIMPATDA_TYPE_LIST WHERE CSM_TAX_TYPE='{$id}'";
	$res = mysqli_query($DBLink, $Qry);
	if ($res) {
		$row = mysqli_fetch_assoc($res);
		return  $row['CSM_TAX_NAME'];
	} else {
		echo mysqli_error($DBLink);
	}
	return "";
}

class taxRePrintClass {
	
	function __construct($printername,$REFNUM=NULL,$NOPNPWP=NULL,$DATE=NULL) {
		$data = NULL;
		if (($DATE && $NOPNPWP) || $REFNUM) {
        	$data = $this->executeCekStatusQuery($REFNUM,$NOPNPWP,$DATE);
		}
		$ret = $this->createHeader($REFNUM,$NOPNPWP,$DATE);
		//echo $data->PPID;
		$ret .= $this->createBody($data);
		
		$ret .= "<div id=\"tab-result\"></div>
				<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
					<param name='printer' value='".$printername."'>
					<param name='sleep' value='200'>
				</applet>
			</div>";
		echo $ret;
    }
	function updateNtrial ($REFNUM) {
		$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
		$UserName=$User->GetUserName($data->uid);
		$PPIDInfo=$User->getPPIDInfo($data->ppid);
		$myConn = $User->GetDbConnectionFromApp($_REQUEST['a']);;
		
		$arResult = LOOKUP_ALL_SIMPATDA($myConn);
		if (!$arResult) {
			return;
		}
		$LDBLink=null;
		$LDBConn=null;
		
		$i = 0;
		$lookDBLink=null;
		$lookDBConn=null;
		foreach ($arResult as $res) {
			$resLookupId = $res["LOOK_ID"];
			$resDbHost = $res["DB_HOST"];
			$resDbUser = $res["DB_USER"];
			$resDbPwd = $res["DB_PWD"];
			$resDbName = $res["DB_NAME"];
			$resDbTable = $res["DB_TABLE"];
			// Payment related initialization
			SCANPayment_ConnectToDB($lookDBLink, $lookDBConn, $resDbHost, $resDbUser, $resDbPwd, $resDbName,true);
			if ($iErrCode != 0)
			{
			  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
			  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
			  exit(1);
			}
			
			$sCond = " CSM_TM_SWITCH_REFNUM ='".$REFNUM."'";
			
			$sQ = "UPDATE ". $resDbTable ." SET CSM_TM_NTRIAL=1 WHERE ".$sCond;
			//echo "$sQ  <br><br>";
			$result = mysqli_query($lookDBLink, $sQ);
			
			SCANPayment_CloseDB($lookDBLink);
		}
		SCANPayment_CloseDB($LDBLink);
	}
	private function executeCekStatusQuery($REFNUM=NULL,$NOPNPWP=NULL,$DATE=NULL) {
		global $iErrCode,$sErrMsg,$DBLink,$data;
		$Response = array();
		$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
		$UserName=$User->GetUserName($data->uid);
		$PPIDInfo=$User->getPPIDInfo($data->ppid);
		$myConn = $User->GetDbConnectionFromApp($_REQUEST['a']);
		$configCode = $data->ppid.".PP.simpatda.PC.kabkot";
		
		$query = sprintf( "SELECT *	FROM c_registry	WHERE C_R_KEY LIKE '%s'", mysql_real_escape_string($configCode));
		$resultkk = mysqli_query($myConn, $query);
		
		if ($rowkk = mysqli_fetch_array($resultkk)) {
			if ($rowkk['C_R_VALUE']==0) $kabkot = "Kabupaten";
			if ($rowkk['C_R_VALUE']==1) $kabkot = "Kota";
		}
		
		$retval=null;
		$arResult = LOOKUP_ALL_SIMPATDA($myConn);
		if (!$arResult) {
			return;
		}
		$LDBLink=null;
		$LDBConn=null;
		
		$i = 0;
		$lookDBLink=null;
		$lookDBConn=null;
		foreach ($arResult as $res) {
			//var_dump($res);
			$resLookupId = $res["LOOK_ID"];
			$resDbHost = $res["DB_HOST"];
			$resDbUser = $res["DB_USER"];
			$resDbPwd = $res["DB_PWD"];
			$resDbName = $res["DB_NAME"];
			$resDbTable = $res["DB_TABLE"];
			// Payment related initialization
			SCANPayment_ConnectToDB($lookDBLink, $lookDBConn, $resDbHost, $resDbUser, $resDbPwd, $resDbName,true);
			//var_dump($lookDBLink);
			if ($iErrCode != 0)
			{
			  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
			  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
			  exit(1);
			}
			
			if ($NOPNPWP && $DATE) {
				$DATE = str_replace("-","",$DATE);
				$sCond = " CSM_TM_TRAN_DT LIKE '".$DATE."%'  AND CSM_TM_NOP_NPWP ='".$NOPNPWP."'";
			} elseif ($REFNUM) {
				$sCond = " CSM_TM_PAY_REFNUM ='".$REFNUM."' ";
			}
			$sQ = "select * from ";
			$sQ .= $resDbTable ." WHERE ".$sCond. " AND CSM_TM_FLAG=1 ORDER BY CSM_TM_ORIGINAL_DT limit 1";
			
			$result = mysqli_query($lookDBLink, $sQ);
			if ($result===false) {
				echo mysqli_error($DBLink);
				exit();
			}
			
			//echo "$sQ  <br><br>";
			if($result){
				$nRes = mysqli_num_rows($result);
				$nRecord = $nRes;
				
				if ($nRes > 0)
				{
				  $i = 0;
				 // $Response['totalrow'] = $nRes;
				  while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
				  {				
					$Response[$i]["OPR"]= $UserName;
					$Response[$i]["OPRNAME"]= $UserName;
					$Response[$i]["UID"] = $data->uid;
					$Response[$i]["PPID"] = $data->ppid;
					$Response[$i]["PPID_NAME"] = $PPIDInfo['NAMA'];
					$Response[$i]["TIMESTAMP"] = strftime("%Y-%m-%d %H:%M:%S");
					$Response[$i]["AREA_NAME"] = $row['CSM_TM_AREA_NAME'];
					$Response[$i]["AREA_KABKOTA"]=$kabkot;
					$Response[$i]["AREA_KABKOTA_UPPER"]=strtoupper($kabkot);
					$Response[$i]["AREA_NAME_UPPER"]=strtoupper($row['CSM_TM_AREA_NAME']);
					$Response[$i]["TAX_REFNUM"] =$row['CSM_TM_GW_REFNUM'];
					$Response[$i]["PAY_REFNUM"] = $row['CSM_TM_SWITCH_REFNUM'];
					$tdt = trim($row['CSM_TM_TRAN_DT']);
					//2010 11 25 154724
					$dt = mktime(substr($tdt,8,2), substr($tdt,10,2),substr($tdt,12,2),substr($tdt,4,2), substr($tdt,6,2), substr($tdt,0,4));
	
					$Response[$i]["TGL_BAYAR"] = strftime("%d-%m-%Y", $dt);
					$Response[$i]["JAM_BAYAR"]= strftime("%H:%M:%S", $dt);
					$Response[$i]["TRAN_DT"]= strftime("%d-%m-%Y %H:%M:%S", $dt);
					
					$Response[$i]["SUBJECT_NAME"] =  trim($row['CSM_TM_SUBJECT_NAME']);
					$Response[$i]["NOP"] = $row['CSM_TM_NOP_NPWP'];
					$Response[$i]["SUBJECT_ADDRESS"] =  trim($row['CSM_TM_SUBJECT_ADDRESS']);
					$Response[$i]["SUBJECT_RT_RW"] =  trim($row['CSM_TM_SUBJECT_RT_RW']);
					$Response[$i]["OBJECT_ADDRESS"] =  trim($row['CSM_TM_OBJECT_ADDRESS']);
					$Response[$i]["OBJECT_RT_RW"] =  trim($row['CSM_TM_OBJECT_RT_RW']);
					
					$Response[$i]["SUBJECT_KELURAHAN"] =  trim($row['CSM_TM_SUBJECT_KELURAHAN']);
					$Response[$i]["SUBJECT_KECAMATAN"] =  trim($row['CSM_TM_SUBJECT_KECAMATAN']);
					$Response[$i]["OBJECT_KELURAHAN"] =  trim($row['CSM_TM_OBJECT_KELURAHAN']);
					$Response[$i]["OBJECT_KECAMATAN"] =  trim($row['CSM_TM_OBJECT_KECAMATAN']);
				
					$Response[$i]["SUBJECT_KABUPATEN"] =  trim($row['CSM_TM_SUBJECT_KABUPATEN']);
					$Response[$i]["SUBJECT_ZIP_POST"] =  trim($row['CSM_TM_SUBJECT_ZIP_POS']);
					$Response[$i]["OBJECT_KABUPATEN"] =  trim($row['CSM_TM_OBJECT_KABUPATEN']);
					
					$minor = $row['CSM_TM_MINOR_48'];
					
					$arrMonth=array("01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April","05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember");
					
					$month = trim($row['CSM_TM_TAX_PERIOD']);
					$taxType = trim($row['CSM_TM_TAX_SIMPATDA_TYPE']);
					
					$aTemplateValues["PERIODE"] = $arrMonth[$month];
					$aTemplateValues["TYPE_PAJAK"] = getTypePajak($taxType);
					
					$tot =  $row['CSM_TM_COLLECTIVE_AMOUNT'] /pow(10,$minor) +	 $row['CSM_TM_PENALTY_FEE']/pow(10,$minor) +  $row['CSM_TM_ADMIN_FEE']/pow(10,$minor);	
					
					$Response[$i]["COLL_BILL_TEXT"] = number_format($row['CSM_TM_COLLECTIVE_AMOUNT']/pow(10,$minor), 2, ',', '.');
					$Response[$i]["MISC_BILL_TEXT"] = number_format($row['CSM_TM_PENALTY_FEE']/pow(10,$minor), 2, ',', '.');
					$Response[$i]["ADMIN_FEE_TEXT"] = number_format($row['CSM_TM_ADMIN_FEE']/pow(10,$minor), 2, ',', '.');
					
					$Response[$i]["TRAN_AMOUNT_TEXT"] =number_format($tot, 2, ',', '.');
					$Response[$i]["TERBILANG"] = SayInIndonesian($tot);
					$Response[$i]["FLAG"] = $row['CSM_TM_FLAG'];
					$Response[$i]["INFO_TEXT"] = " ";
					$Response[$i]["INFO_TEXT2"] = " ";
					$Response[$i]["STATUS"] = "CU-".$row['CSM_TM_NTRIAL'];
					$Response[$i]["SREF"] = $row['CSM_TM_SWITCH_REFNUM'];
					$i++;
				  }
				 
					mysqli_free_result($result);
				}
			}
			SCANPayment_CloseDB($lookDBLink);
		}
		SCANPayment_CloseDB($LDBLink);
		return $Response;
	}

	public function createBody($data=NULL) {
		
		$sTemplateFile = str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'simpatda', '', dirname(__FILE__)).DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."report".DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR."simpatda".DIRECTORY_SEPARATOR."simpatda-receipt.xml";
		$re = new reportEngine($sTemplateFile);	
		
		$body = "\n<br></br><div id=\"body-tax\" name=\"body-tax\">\n";
		$body .= "\t <table border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=\"880px\">\n";
		if ($data) {
			$body .= "\t\t<tr>\n";
			$body .= "\t\t\t<th width=\"20%\">Nama Subjek</td>\n";
			$body .= "\t\t\t<th width=\"20%\">Alamat Subjek</td>\n";
			$body .= "\t\t\t<th width=\"20%\">Alamat Subjek</td>\n";
			$body .= "\t\t\t<th width=\"15%\">Nilai Total</td>\n";
			$body .= "\t\t\t<th width=\"15%\">Status</td>\n";
			$body .= "\t\t\t<th>Print</td>\n";
			$body .= "\t\t</tr>\n";
			$i = count($data);
			for ($m=0;$m < $i;$m++) {
				$re->ApplyTemplateValue($data[$m]);
				$re->Print2TXT($strTXT);
				$strTXT = base64_encode($strTXT);
				$body .= "\t\t<tr><td>".$data[$m]['SUBJECT_NAME']."</td><td>".$data[$m]['SUBJECT_ADDRESS']."</td>";
				$body .= "<td>".$data[$m]['OBJECT_ADDRESS']."</td>";
				$body .= "<td align=\"right\">".$data[$m]['TRAN_AMOUNT_TEXT']."</td><td align=\"center\">".($data[$m]['FLAG']==1?"Terbayar":"Belum Terbayar")."</td>";
				//$body .= "<td><input type=\"hidden\" name=\"reprintVal-".$data[$m]['SREF']."\" value=\"".$strTXT."\" id=\"reprintVal-".$data[$m]['SREF']."\" />";
				$body .= "<td><input type=\"button\" name=\"reprint\" value=\"Cetak Ulang\" id=\"reprint\" onclick=\"sendReprint('".$data[$m]['SREF']."',aBon,'".$data[$m]['PPID']."','".$data[$m]['UID']."');\"></td>\n";
				$body .= "\t\t</tr>\n";
			}
			
		} else {
			$body .= "\t\t <tr>\n";
			$body .= "\t\t\t <td colspan=\"3\"  style=\"background-color:transparent;\"><font color=\"blue\"><i>Data kosong, silahkan isi filter pencarian di atas ! (NPWPD dan tanggal, atau hanya nomor referensi) </i></font></td>\n";
			$body .= "\t\t </tr>\n";
		}
		$body .= "\t </table>\n";
		$body .= "</div>\n";
		return $body;
	}
	
	private function createHeader($REFNUM,$NOPNPWP,$DATE) {
		$header = '<link href="inc/datepicker/datepickercontrol.css" rel="stylesheet" type="text/css"/>';
		$header .= "<SCRIPT LANGUAGE=\"JavaScript\" src=\"inc/datepicker/datepickercontrol.js\"></SCRIPT>\n"; 
		$header .= "<script language='javascript' src='function/simpatda/simpatda-reprint.js'></script>\n";
		$header .= "<script language='javascript' src='view/simpatda/date.format.js'></script>\n";
		$header .= "<script language=\"javascript\"> var aBon = '".$_REQUEST['a']."';</script>\n"; 
		$header .=  '<input type="hidden" id="DPC_TODAY_TEXT" value="Hari Ini">';
		$header .=  '<input type="hidden" id="DPC_BUTTON_TITLE" value="Buka Tanggal">';
		$header .=  "<input type='hidden' id='DPC_MONTH_NAMES' value=\"['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']\">";
		$header .= "<input type='hidden' id='DPC_DAY_NAMES' value=\"['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']\">";
		$header .= "<div id=\"header-tax\" name=\"header-tax\">\n\t<form action=\"\" method=\"post\" id=\"inqform\" name=\"inqform\">\n";
		$header .= "\t\t <table border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=\"780px\">\n";
		$header .= "\t\t\t <tr><td colspan=\"4\" style=\"background-color:transparent;\"><b>Pencarian</b></td><tr>\n";
		$header .= "\t\t\t <tr>\n";
		$header .= "\t\t\t\t <td width=\"\" style=\"background-color:transparent;\">NPWPD</td>\n";
		$header .= "\t\t\t\t <td width=\"\" style=\"background-color:transparent;\" colspan=\"2\"><input type=\"text\" name=\"nop_npwp\" id=\"nop_npwp\" maxlength=\"32\" size=\"32\" value=\"".$NOPNPWP."\"></td>\n";
		$header .= "\t\t\t\t <td width=\"\" style=\"background-color:transparent;\">Tanggal <input type=\"text\" name=\"trs-dt\" id=\"trs-dt\" datepicker=\"true\" datepicker_format=\"YYYY-MM-DD\" value=\"".$DATE."\"> \n";
		$header .= "\t\t\t\t </td>\n";
		$header .= "\t\t\t\t <td width=\"\" style=\"background-color:transparent;\"><input type=\"submit\" name=\"find\" value=\"Cari\" id=\"find\"></td>\n";
		$header .= "\t\t\t </tr>\n";
		$header .= "\t\t\t <tr><td style=\"background-color:transparent;\">Nomor Referensi</td>\n";
		$header .= "\t\t\t\t <td width=\"\" style=\"background-color:transparent;\" colspan=\"3\"><input type=\"text\" name=\"refnum\" id=\"refnum\" maxlength=\"32\" size=\"32\" value=\"".$REFNUM."\"></td>\n";
		$header .= "\t\t\t </tr>\n";
		$header .= "\t\t </table>\n";
		$header .= "\t<form>\n";
		$header .= "</div>\n";
		return $header;
	}
	
	public function taxFormdisplay($printername) {
		
	}
	
	
}
if(stillInSession($DBLink,$json,$sdata)){
	
	SCANPayment_Pref_GetAllWithFilter($appDbLink,$data->ppid.".PP.simpatda.PC.print.%",$PPID_setting);
	
	$printername = $PPID_setting[$data->ppid.".PP.simpatda.PC.print.printer"];
	if(!$printername) $printername="Epson Lx-300+";

	//main program
	$nopnpwp = (@isset($_REQUEST['nop_npwp']) ? $_REQUEST['nop_npwp'] : '');
	$date = (@isset($_REQUEST['trs-dt']) ? $_REQUEST['trs-dt'] : '');
	$refnum = (@isset($_REQUEST['refnum']) ? $_REQUEST['refnum'] : '');
	$UserName="";
	//echo $nopnpwp ." & ". $date;
	if (($nopnpwp != '' && $date != '') || $refnum != '') {
			 
		//$result = executeCekStatusQuery($refnum,$nopnpwp,$date);
		$simpatda = new taxRePrintClass($printername,$refnum,$nopnpwp,$date);
		
	}
	else {
		$simpatda = new taxRePrintClass($printername);
		//$simpatda->taxFormdisplay($printername);
	}
	
}else{
	$getSummaryResponse["rc"] = "rekues tidak diperkenankan";
	$getSummaryResponse["errcode"] = -601;
}
//print_r($_REQUEST);
//encode dan echo
//print_r($getSummaryResponse);
echo encodeGetResponse($getSummaryResponse);
//tpp end
?>
