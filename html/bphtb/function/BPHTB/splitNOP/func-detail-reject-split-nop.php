<?php 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'splitNOP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php"); 
require_once($sRootPath."function/BPHTB/func-display-document.php");

$sts = @isset($_REQUEST['sts']) ? $_REQUEST['sts']:"";
$save = @isset($_REQUEST['btn-save'])?($_REQUEST['btn-save']=="Simpan sebagai versi baru"?"1":"2"):"";

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\" src=\"function/BPHTB/dispenda/func-display-dispenda.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
$dpn = new displayDocument(1,$_REQUEST['idssb']);

if (($sts == "4")&&($save == "")) $dpn->setNewVersion();
if ($save != "") $dpn->setSubmitNewVersion($save);
if ($save == "") echo $dpn->display();
?>
