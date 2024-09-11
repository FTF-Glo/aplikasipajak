<?php
global $data, $User,$appDbLink;
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");
ob_start();
// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'voucher', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/log-payment.php");
require_once($sRootPath."inc/voucher/svc-voucher-lookup.php");
require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");


$PPID_setting=null;
$POSTPAID_setting=null;
SCANPayment_Pref_GetAllWithFilter($appDbLink,$data->ppid.".PP.voucher.PC.print.%",$PPID_setting);
SCANPayment_Pref_GetAllWithFilter($appDbLink,"PP.%",$POSTPAID_setting);
//var_dump($POSTPAID_setting);
//var_dump($PPID_setting);
$printername=$PPID_setting[$data->ppid.".PP.voucher.PC.print.printer"];
if(!$printername) $printername="Epson Lx-300+";
$printercopy=$PPID_setting[$data->ppid.".PP.voucher.PC.print.copy"];
if(!$printercopy) 
	$printercopy=0;
else
	$printercopy=intval($printercopy);
//var_dump($PPID_setting);
$iErrCode = 0;
$sErrMsg = '';

function executeCekStatusQuery($PPID,$MSN,$SUBID,$USESUB,$TRAN_DT,$USEDT) {
	global $iErrCode,$sErrMsg,$appDbLink;
	$DBLink=null;
	$DBConn=null;
	$retval=null;
	//lookup
	$arResult = LOOKUP_ALL_VOUCHER();
	if ($arResult) {
		$i = 0;
		foreach ($arResult as $res) {
			$resLookupId = $res["LOOK_ID"];
			$resDbHost = $res["DB_HOST"];
			$resDbUser = $res["DB_USER"];
			$resDbPwd = $res["DB_PWD"];
			$resDbName = $res["DB_NAME"];
			$resDbTable = $res["DB_TABLE"];

			SCANPayment_ConnectToDB($DBLink, $DBConn, $resDbHost, $resDbUser, $resDbPwd, $resDbName,true);
			$usesub=isset($USESUB)?$USESUB:0;
			if($usesub=="1"){
					$query = sprintf(
					"SELECT 
						*
					FROM ".$resDbTable." 
					WHERE 
						CSM_TM_SUBID like '%s' AND
						CSM_TM_PPID = '%s' AND 
						CSM_TM_FLAG = 1", 
					mysql_real_escape_string($SUBID,$DBLink)."%",
					mysql_real_escape_string($PPID,$DBLink)
				);
			}else{
				$query = sprintf(
					"SELECT 
						*
					FROM ".$resDbTable." 
					WHERE 
						CSM_TM_MSN = '%s' AND
						CSM_TM_PPID = '%s' AND 
						CSM_TM_FLAG = 1", 
					mysql_real_escape_string($MSN,$DBLink),
					mysql_real_escape_string($PPID,$DBLink)
				);
			}
			$usedt=isset($USEDT)?$USEDT:0;
			//echo $USEDT;
			if($usedt==1){
				$query.=sprintf(" AND CSM_TM_PAID like '%s' ",mysql_real_escape_string($TRAN_DT,$DBLink).'%');
			}
			//echo $query;
			$result = mysqli_query($DBLink, $query);
			while ($row = mysqli_fetch_assoc($result)) {
				$retval[$i] = $row;
				$i++;		
			}
			mysqli_free_result($result);

			SCANPayment_CloseDB($DBLink);
			// echo $query;
		}
	}
	return $retval;
}

if ($data) {
	$config = $User->GetModuleConfig($module);
	$PPIDInfo=$User->getPPIDInfo($data->ppid);
	$UserName=$User->GetUserName($data->uid);
	$reprintURL="svr/voucher/svc-voucher-reprint.php";
	$incprintURL="svr/voucher/svc-voucher-inc-print.php";

	$filterinput=isset($_REQUEST["filterinput"])?$_REQUEST["filterinput"]:"0";
	$filteridpel=isset($_REQUEST["filteridpel"])?$_REQUEST["filteridpel"]:"";
	$filtermsn=isset($_REQUEST["filtermsn"])?$_REQUEST["filtermsn"]:"";
	$filtertgl=isset($_REQUEST["filtertgl"])?$_REQUEST["filtertgl"]:date("Y-m-d");

	echo '<link href="inc/datepicker/datepickercontrol.css" rel="stylesheet" type="text/css"/>';
	echo '<SCRIPT LANGUAGE="JavaScript" src="inc/datepicker/datepickercontrol.js"></SCRIPT>';
	echo '<SCRIPT LANGUAGE="JavaScript" src="function/voucher/voucher-reprint.js"></SCRIPT>';
	echo '<input type="hidden" id="DPC_TODAY_TEXT" value="Hari Ini">';
	echo '<input type="hidden" id="DPC_BUTTON_TITLE" value="Buka Tanggal">';
	echo "<input type='hidden' id='DPC_MONTH_NAMES' value=\"['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']\">";
	echo "<input type='hidden' id='DPC_DAY_NAMES' value=\"['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']\">";
	// deprecated 03/01 title header
	// echo '<table width="100%">';
	// echo '<tr class="transparent">';
	// echo '<td class="transparent" valign="top"><div style="font-size:18pt;">Voucher Pulsa Seluler</div><div style="font-size:14pt;">Cek Data/Cetak Ulang</div></td>';
	// echo '<th  width="500" align="right" class="tableSubTitle">&nbsp;</th>	</tr></table>';
	echo '<div id="elpost-reprint-main" style="padding:3px;">';
	echo '<form method="POST">';
	echo '<table id="elpost-reprint-table-inquiry">';
	echo '<tr class="transparent">';
	echo '<td><br/>	</span>';
	echo '<div class="transparent" id="input_msn" style="display:'.($filterinput=="1"?"none":"inline").'">';
	echo '<span>No Hand Phone&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="text" id="filtermsisdn"  name="filtermsisdn" value="'.$filtermsn.'" maxlength="14" disableSelection="false" />';
	echo '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kode Voucher &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="text" id="filterprodid"  name="filterprodid" value="'.$filtermsn.'" maxlength="14" disableSelection="false" />';
	echo '</div>';
	echo '<div class="transparent">';
	echo '<span>Tanggal Transaksi&nbsp;&nbsp;&nbsp;&nbsp;</span><input readonly type="text" id="filtertgl"  name="filtertgl" value="'.$filtertgl.'" datepicker="true" datepicker_format="YYYY-MM-DD"><a href="#"><img src="image/icon/textfield_delete.png" width="24" height="24" alt="Kosongkan" onclick="'."document.getElementById('filtertgl').value=''".';"></a>';
	echo '<td>&nbsp;</span>';
	echo '</span></td>';
	echo '<tr class="transparent">';
	echo '<td> <input type="button" value="Cek" onclick="';
	echo "findVoucher();";
	echo '"><input type="button" value="Kosongkan" onclick="'."this.form.filtermsisdn.value='';this.form.filterprodid.value=''".';"></td><td>&nbsp;</td><td colspan="2">&nbsp;</td>';
	echo '</tr>';
	echo '<tr class="transparent">';
	echo '<td> <div style="font-size:10pt;font-style:italic;" disableSelection="true" >Masukan <b>No Hand Phone</b>, pilih <b>Tanggal Transaksi</b> dan <b>BLTH</b> lalu tekan tombol <b>&quot;Cek&quot;</b></div></td>';
	echo '</tr>';
	echo '<tr class="transparent">';
	echo '<td colspan="4"> <div style="font-size:10pt;font-style:italic;" disableSelection="true" >Kosongkan <b>Tanggal Transaksi</b> untuk mengecek semua tanggal, Kosongkan <b>BLTH</b> untuk mengecek semua BLTH</div></td>';
	echo '</tr>';
	echo '<tr class="transparent">';
	echo '<td colspan="4"><div style="font-size:10pt;font-style:italic;" disableSelection="true" >Tekan tombol <b>&quot;Kosongkan&quot;</b> untuk mengkosongkan semua</div></td>';
	echo '</tr>';
	echo '</table>';
	echo '<br/><hr/>';
	echo '<applet name="jZebra" code="jzebra.PrintApplet.class" archive="inc/jzebra/jzebra.jar" width="0" height="0"><param name="printer" value="'.$printername.'"><param name="sleep" value="200"></applet>';	
	echo '<div id="elpost-reprint-main-result" disableSelection="true" ><label style="font-weight:bold;color:red">Silahkan Masukan No Hand Phone</label></div>';
	echo '</form>';
	echo '</div>';
			
		
}	
?>
