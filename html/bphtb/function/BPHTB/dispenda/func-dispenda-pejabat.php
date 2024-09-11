<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/BPHTB/func-display-document.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

$submit = @isset($_REQUEST['submit']) == 'Submit' ? true : false;
$dpn = new displayDocument(2, $_REQUEST['idssb']);
$dpn->setDirDispenda();
$dpn->setApproved();
$dpn->setUName($data->uname);
if ($submit) {
	// die(var_dump($_REQUEST));
	//$dpn->setSubmit($_REQUEST['RadioGroup1'],$_REQUEST['textarea-info']);
	$dpn->setSubmit($_REQUEST['RadioGroup1'], $_REQUEST['textarea-info'], $_REQUEST['textarea-info1']);
} else echo $dpn->display();
