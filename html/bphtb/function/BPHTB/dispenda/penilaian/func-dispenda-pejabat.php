<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda' . DIRECTORY_SEPARATOR . 'penilaian', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
// require_once($sRootPath . "function/BPHTB/func-display-document.php");
// var_dump($sRootPath . "function/BPHTB/dispenda/penilaian/func-display-doc.php");
// die;
require_once($sRootPath . "function/BPHTB/dispenda/penilaian/func-display-doc.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

$submit = @isset($_REQUEST['submit']) == 'Submit' ? true : false;
$kurangBayar = @isset($_REQUEST['kurangbayar']) == 'Kurang Bayar' ? true : false;
// var_dump($_REQUEST);
$dpn = new displayDocument(2, $_REQUEST['idssb']);
$dpn->setDirDispenda2();
$dpn->setApproved();
$dpn->setUName($data->uname);
if ($submit) {
	// die(var_dump($submit));
	//$dpn->setSubmit($_REQUEST['RadioGroup1'],$_REQUEST['textarea-info']);
	$dpn->setSubmit($_REQUEST['RadioGroup1'], $_REQUEST['textarea-info'], $_REQUEST['textarea-info1']);
}else if($kurangBayar){
	$params = "a=aBPHTB&m=mVerifikasiNtpda=aBPHTB&m=modNotarisBPHTB";
	// $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $_REQUEST['idssb'];
	$kurang_bayar = $params . "a=aBPHTB&m=modNotarisBPHTB&f=funcKurangBayar&validasikb=1&idssb=" . $_REQUEST['idssb'];
	$url="main.php?param=".base64_encode($kurang_bayar);
	// die("main.php?param=".base64_encode($par1));
	
	header("Location: $url");
    exit();
	// return  "main.php?param=".base64_encode($par1);

}

else echo $dpn->display();
