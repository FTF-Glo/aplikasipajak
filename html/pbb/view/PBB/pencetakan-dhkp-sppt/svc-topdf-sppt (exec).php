<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pencetakan-dhkp-sppt', '', dirname(__FILE__))).'/';
date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath."inc/payment/json.php");
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* inisiasi parameter */
if(isset($_REQUEST['q'])){
	$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] :"";
	$q = base64_decode($q);
	$q = $json->decode($q);
	$q->kd_kel = $_POST['kd_kel'];
	$q->blok = $_POST['blok'];
	$q->tahun = $_POST['thn'];

	$q = $json->encode($q);
	$params = base64_encode($q);

	//contoh : eyJhIjoiYVBCQiIsIm0iOiJtUGVuY2V0YWthbkRIS1BTUFBUIiwidGFiIjoiMSIsIm4iOiIyIiwidSI6ImphamFuZyIsImtkX2tlbCI6IjMyMDQxMTEwMDkiLCJibG9rIjoiOTk5IiwidGFodW4iOiIyMDE2In0
	exec("/usr/local/bin/php /var/www-pbb-base/html/view/PBB/pencetakan-dhkp-sppt/cmd-topdf-sppt.php {$params}", $output, $return_var);

	$msg = !empty($output[0])? $output[0] : 'Proses pembuatan cetakan SPPT mulai diproses.';
	echo $json->encode(array('msg'=>$msg));
}
?>
