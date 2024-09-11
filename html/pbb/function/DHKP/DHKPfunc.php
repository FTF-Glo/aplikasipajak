<?php
global $data, $User,$appDbLink;
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'DHKP', '', dirname(__FILE__))).'/';

echo $printername=$PPID_setting[$data->ppid.".PP.voucher.PC.print.printer"];
if(!$printername) $printername="Epson Lx-300+ /II";


echo "<SCRIPT LANGUAGE='JavaScript' src='function/DHKP/reprint-ori.js'></SCRIPT> \n";
echo "<input name='btnSend' type='button' value='Submit' onclick='sendReport();'>";
echo "<div id='voucher-report-result'></div>";

echo '<applet name="jZebra" id="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" width="0" height="0">';
echo '<param name="printer" value="'.$printername.'"><param name="sleep" value="200">';
echo '</applet>';

?>