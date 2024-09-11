<?php
global $data, $User,$appDbLink;
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");
ob_start();
// includes
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'bphtb', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/ctools.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/log-payment.php");
require_once($sRootPath."inc/bphtb/bphtb-lookup.php");
require_once($sRootPath."inc/report/eng-report-table.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");


$PPID_setting=null;
$POSTPAID_setting=null;
SCANPayment_Pref_GetAllWithFilter($appDbLink,$data->ppid.".PP.bphtb.PC.print.%",$PPID_setting);
SCANPayment_Pref_GetAllWithFilter($appDbLink,"PP.%",$POSTPAID_setting);
//var_dump($POSTPAID_setting);
//var_dump($PPID_setting);
$printername=$PPID_setting[$data->ppid.".PP.bphtb.PC.print.printer"];
if(!$printername) $printername="Epson Lx-300+";
$printercopy=$PPID_setting[$data->ppid.".PP.bphtb.PC.print.copy"];
if(!$printercopy) 
	$printercopy=0;
else
	$printercopy=intval($printercopy);
//var_dump($PPID_setting);
$iErrCode = 0;
$sErrMsg = '';
$filtertgl=isset($_REQUEST["filtertgl"])?$_REQUEST["filtertgl"]:date("Y-m-d");
$filterbl = date("m");
$filterth = date("Y");

$arAppConfig = $User->GetAppConfig($application);

echo "\n<link href='inc/datepicker/datepickercontrol.css' rel='stylesheet' type='text/css'/>\n";
echo "<SCRIPT LANGUAGE='JavaScript' src='inc/datepicker/datepickercontrol.js'></SCRIPT> \n";
echo "<SCRIPT LANGUAGE='JavaScript' src='function/bphtb/bphtb-reprint.js'></SCRIPT> \n";
echo "<script language=\"javascript\"> var pcopy = '".$printercopy."';</script>\n"; 
echo "<input type='hidden' id='DPC_TODAY_TEXT' value='Hari Ini'> \n";
echo "<input type='hidden' id='DPC_BUTTON_TITLE' value='Buka Tanggal'> \n";
echo "<input type='hidden' id='DPC_MONTH_NAMES' value=\"['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']\"> \n";
echo "<input type='hidden' id='DPC_DAY_NAMES' value=\"['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']\"> \n";
echo "<table border='0' cellpadding='4' cellspacing='0'  class='transparent'> \n";
echo "<tr> \n<td class='transparent'>Tanggal </td> \n<td class='transparent'> \n";
echo "<span id='input_harian'><input readonly type='text' id='filtertgl' name='filtertgl' value='".$filtertgl."' datepicker='true' datepicker_format='YYYY-MM-DD' disableSelection='false' /><a href='#'><img src='image/icon/textfield_delete.png' width='24' height='24' alt='Kosongkan' onclick='document.getElementById(\"filtertgl\").value=\"\"'></a>";
echo "</span>\n";
echo "<span id='input_bulanan' style='display:none;'>\n";

echo '<select id="filterbl" name="filterbl">';
echo '<option value="01" '.($filterbl=="01"?"selected":"").'>Januari</option>';
echo '<option value="02" '.($filterbl=="02"?"selected":"").'>Februari</option>';
echo '<option value="03" '.($filterbl=="03"?"selected":"").'>Maret</option>';
echo '<option value="04" '.($filterbl=="04"?"selected":"").'>April</option>';
echo '<option value="05" '.($filterbl=="05"?"selected":"").'>Mei</option>';
echo '<option value="06" '.($filterbl=="06"?"selected":"").'>Juni</option>';
echo '<option value="07" '.($filterbl=="07"?"selected":"").'>Juli</option>';
echo '<option value="08" '.($filterbl=="08"?"selected":"").'>Agustus</option>';
echo '<option value="09" '.($filterbl=="09"?"selected":"").'>September</option>';
echo '<option value="10" '.($filterbl=="10"?"selected":"").'>Oktober</option>';
echo '<option value="11" '.($filterbl=="11"?"selected":"").'>November</option>';
echo '<option value="12" '.($filterbl=="12"?"selected":"").'>Desember</option>';
echo '</select><input type="text" id="filterth"  name="filterth" value="'.$filterth.'" size="4" />';

echo "</span>\n</td>\n</tr>";
echo "<tr>\n<td class='transparent'>&nbsp;</td>\n<td class='transparent'><input name='btnSend' type='button' value='Submit' onclick='sendReport(\"".$_REQUEST['a']."\");'><input type=\"button\" id=\"btnUnduh\" value=\"Unduh Detail\" onclick=\"downloadFile('".$_REQUEST['a']."');\"><iframe id=\"download-file\" src=\"_blank\" style=\"width:0;height:0;display:block\"></iframe></td>\n";
echo "</tr>\n";
echo "<tr><td colspan='3'><div id='main-report-result'></div></td></tr>";
echo "</table>\n";
echo "<div id='bphtb-report-result'></div>";
echo '<applet name="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" width="0" height="0"><param name="printer" value="'.$printername.'"><param name="sleep" value="200"></applet>';	

?>