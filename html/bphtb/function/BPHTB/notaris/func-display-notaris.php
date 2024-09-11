<?php 
//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1); 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php"); 
require_once($sRootPath."function/BPHTB/func-display-document.php");

$dpn = new displayDocument(1,$_REQUEST['idssb']);
$sts = @isset($_REQUEST['sts']) ? $_REQUEST['sts']:"";
if($dpn->getConfigValue("1",'VERIFIKASI')!='1'){
	$save = @isset($_REQUEST['btn-save'])?($_REQUEST['btn-save']=="Simpan sebagai versi baru"?"1":"3"):"";
}else{
	$save = @isset($_REQUEST['btn-save'])?($_REQUEST['btn-save']=="Simpan sebagai versi baru"?"1":"2"):"";
}


echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>\n
<script src=\"function/BPHTB/notaris/print/sspd_print.js\"></script>\n
<!-- jZebra applet-->
<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
	<param name='printer' id='printer' value='EPSON-LQ-2190'>
	<param name='sleep' value='200'>
</applet>
<!-- end jZebra-->\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\" src=\"function/BPHTB/dispenda/func-display-dispenda.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
//$dpn = new displayDocument(1,$_REQUEST['idssb']);
// var_dump($save);exit;
if (($sts == "4")&&($save == "")) $dpn->setNewVersion();
if ($save != "") $dpn->setSubmitNewVersion($save);
if ($save == "") echo $dpn->display();
?>
