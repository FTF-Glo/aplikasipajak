<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");

//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/notaris/mod-notaris.css?0002\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='".$data->uname."';</script>\n";
echo "<script language=\"javascript\">var axx='".base64_encode($_REQUEST['a'])."';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/notaris/mod-notaris.js\" type=\"text/javascript\"></script>\n";

$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
//$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;
//print_r($_REQUEST);

$par1 = $params."&n=1&s=5";
$par2 = $params."&n=2&s=4";
$par3 = $params."&n=3&s=2";
$par4 = $params."&n=4&s=1";
$par5 = $params."&n=5&s=100";

if ($sel==1) $sts=5;
if ($sel==2) $sts=4;
if ($sel==3) $sts=2;
if ($sel==4) $sts=1;
if ($sel==5) $sts=100;

$modNotaris = new  modBPHTBApprover(1,$data->uname);
$modNotaris->addMenu("Disetujui","app-menu",base64_encode($par1));
$modNotaris->addMenu("Ditolak","rej-menu",base64_encode($par2));
$modNotaris->addMenu("Tertunda","dil-menu",base64_encode($par3));
$modNotaris->addMenu("Sementara","tmp-menu",base64_encode($par4));
$modNotaris->addMenu("Semua Data","all-menu",base64_encode($par5));
//$modNotaris->addMenu("Form SSB(PDF)","pdf-menu","","printNFPDF()");
$modNotaris->setSelectedMenu($sel);
$modNotaris->setStatus($sts);
$modNotaris->setDataPerPage(50);
$modNotaris->setDefaultPage($page);

$del = @isset($_REQUEST['del'])?$_REQUEST['del']:"";

if ($del) {
	$json = new Services_JSON();
	$del = $json->decode($del);
	$c = count($del);
	
	for ($i=0;$i<$c;$i++) {
		$qry = "DELETE FROM cppmod_ssb_doc where CPM_SSB_ID = '{$del[$i]->id}'";
		
		$res = mysqli_query($appDbLink, $qry);
		if ( $res === false ){
			print_r(mysqli_error($appDbLink));
		}
	}
}
echo $modNotaris->showData();

?>

