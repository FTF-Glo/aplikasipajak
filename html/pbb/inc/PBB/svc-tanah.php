<?php 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

$conn = mysqli_connect(ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);

// Check connection
if (mysqli_connect_errno()) {
    echo "Gagal melakukan koneksi ke MySQL <br>" . mysqli_connect_error();
    exit();
}

require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/PBB/PenentuanKelas.php");
require_once($sRootPath . "inc/PBB/ZNT/FindNJOPbumi.php");
require_once($sRootPath . "inc/PBB/ZNT/PenilaianBumi.php");

$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$request = $json->decode($getSvcRequest);

$nop 	= @$request->NOP;
$nop    = (int)$nop;

$znt 	= @$request->ZNT;
$znt 	= addslashes(trim($znt));

$luas 	= @$request->LUAS;
$luas 	= (float)$luas;

$tahun 	= @$request->TAHUN;
$tahun 	= (int)$tahun;

$tabel 	= @$request->TABEL;
$tabel 	= addslashes(trim($tabel));

$nilai = FindNJOPbumi::penentuanNJOP($nop, $znt, $luas, $tahun, $conn, $tabel);

header('Content-Type: application/json; charset=utf-8');
print_r( json_encode($nilai) );
exit;