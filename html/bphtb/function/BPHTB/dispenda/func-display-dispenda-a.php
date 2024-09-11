<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "function/BPHTB/func-display-document.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

$submit = @isset($_REQUEST['submit']) == 'Submit' ? true : false;
$dpn = new displayDocument(1, $_REQUEST['idssb']);
// $dpn = new displayDocument(1, $_REQUEST['payment_code']);


$q = @isset($_REQUEST['param']) ? $_REQUEST['param'] : false;
if (!$q) die;
$q = base64_decode($q);
$q = explode('&', $q);
$m = $q[1];
$m = explode('=', $m);
$m = $m[1];
// die(var_dump($m));

if ($m == 'modStaffDispendaBPHTB') $dpn->setStaffDispenda();
$dpn->setApproved();
if ($m == 'modPejDispendaBPHTB') $dpn->setDirDispenda();

if ($submit) {
	// die(var_dump($m));
	//$dpn->setSubmit($_REQUEST['RadioGroup1'],$_REQUEST['textarea-info']);
	$dpn->setSubmit($_REQUEST['RadioGroup1'], $_REQUEST['textarea-info'], $_REQUEST['textarea-info1'], $_REQUEST['RadioGroup99']);
} else echo $dpn->display();
