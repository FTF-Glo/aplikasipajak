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
require_once($sRootPath . "inc/PBB/JPB/FindNJOPbgn.php");
require_once($sRootPath . "inc/PBB/JPB/PenilaianBgn.php");
require_once($sRootPath . "inc/PBB/JPB/PenilaianStandard.php");
require_once($sRootPath . "inc/PBB/JPB/KomponenUtamaSTD.php");
require_once($sRootPath . "inc/PBB/JPB/KomponenMaterial.php");
require_once($sRootPath . "inc/PBB/JPB/Susut.php");
require_once($sRootPath . "inc/PBB/JPB/FasilitasSusut.php");
require_once($sRootPath . "inc/PBB/JPB/FasilitasSusutLuas.php");
require_once($sRootPath . "inc/PBB/JPB/FasilitasTidakSusut.php");
require_once($sRootPath . "inc/PBB/JPB/JPB2.php");
require_once($sRootPath . "inc/PBB/JPB/JPB3.php");
require_once($sRootPath . "inc/PBB/JPB/JPB4.php");
require_once($sRootPath . "inc/PBB/JPB/JPB5.php");
require_once($sRootPath . "inc/PBB/JPB/JPB6.php");
require_once($sRootPath . "inc/PBB/JPB/JPB7.php");
require_once($sRootPath . "inc/PBB/JPB/JPB8.php");
require_once($sRootPath . "inc/PBB/JPB/JPB9.php");
require_once($sRootPath . "inc/PBB/JPB/JPB12.php");
require_once($sRootPath . "inc/PBB/JPB/JPB13.php");
require_once($sRootPath . "inc/PBB/JPB/JPB14.php");
require_once($sRootPath . "inc/PBB/JPB/JPB15.php");
require_once($sRootPath . "inc/PBB/JPB/JPB16.php");

$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$request = $json->decode($getSvcRequest);
// print_r($request);exit;

$nop 	= @$request->NOP;
$nop    = (int)$nop;

$tahun  = @$request->TAHUN;
$tahun  = (int)$tahun;

$tabel1 = @$request->TABEL1;
$tabel1 = addslashes(trim($tabel1));

$tabel2 = @$request->TABEL2;
$tabel2 = addslashes(trim($tabel2));

$nilai = FindNJOPbgn::penentuanNJOPBangunan($nop, $tahun, $conn, $tabel1, $tabel2);

// utk NOP Ruko Pasar atau Apartement // Jika di perlukan //
// $res = mysqli_query($conn, "SELECT CPM_NOP_INDUK FROM cppmod_obb_sppt_anggota WHERE CPM_NOP='$nop'");
// while($obj = mysqli_fetch_object($res)){
//     PenilaianOPAnggotaMassal::penilaianOPAnggotaMassal($obj->CPM_NOP_INDUK, $thn, $conn);
// }

header('Content-Type: application/json; charset=utf-8');
print_r( json_encode($nilai) );
exit;