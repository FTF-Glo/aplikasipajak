<?php 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
require_once($sRootPath . "inc/PBB/CopyDataKeSusulan.php");
require_once($sRootPath . "inc/PBB/PenentuanKelas.php");
require_once($sRootPath . "inc/PBB/ZNT/FindNJOPbumi.php");
require_once($sRootPath . "inc/PBB/JPB/FindNJOPbgn.php");
require_once($sRootPath . "inc/PBB/ZNT/PenilaianBumi.php");
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

// TIPE 0 ->    'cppmod_pbb_sppt'
// TIPE 1 ->    'cppmod_pbb_sppt_final'
// TIPE 2 ->    'cppmod_pbb_sppt_susulan'
// TIPE 3 ->    'cppmod_pbb_service_change'
// TIPE 4 ->    'cppmod_pbb_service_merge_sppt'
// TIPE 5 ->    'cppmod_pbb_sppt_mundur'

$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : false);
if(!$getSvcRequest) die('REQUEST APA ?');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$request = $json->decode($getSvcRequest);
// print_r($request);exit;

$tipe       = (int)$request->TIPE;
$tahun      = (int)$request->TAHUN;
if(trim($request->NOP)!=''){
    $nops       = trim($request->NOP);
    $kelurahan  = false;
    $nops       = explode(",", $nops);
    if(count($nops)==0) die('NOP APA ?');
}else{
    $nops       = false;
    $kelurahan  = (int)$request->KELURAHAN;
    if($kelurahan==0) die('KELURAHAN APA ?');
    if(strlen($kelurahan)!=10) die($kelurahan.' KELURAHAN APA ?');
}

if($tipe==1){
    $tabel1 = 'cppmod_pbb_sppt_final';
    $tabel2 = 'cppmod_pbb_sppt_ext_final';
}elseif($tipe==2){
    $tabel1 = 'cppmod_pbb_sppt_susulan';
    $tabel2 = 'cppmod_pbb_sppt_ext_susulan';
}elseif($tipe==3){
    $tabel1 = 'cppmod_pbb_service_change';
    $tabel2 = 'cppmod_pbb_service_change_ext';
}elseif($tipe==4){
    $tabel1 = 'cppmod_pbb_service_merge_sppt';
    $tabel2 = 'cppmod_pbb_service_merge_sppt_ext';
}elseif($tipe==5){
    $tabel1 = 'cppmod_pbb_sppt_mundur';
    $tabel2 = 'cppmod_pbb_sppt_ext_mundur';
}else{
    $tabel1 = 'cppmod_pbb_sppt';
    $tabel2 = 'cppmod_pbb_sppt_ext';
}

// Penentuan ini waktu susulan atau tidak dan Copy Data ke Tabel Mundur
if($tipe==1 || $tipe==5){
    $paramSu = CopyDataKeSusulan::isPeriodeSusulan($tipe, $nops, $kelurahan, $conn, $tabel1, $tabel2);
    $masuk_periode_susulan = $paramSu->isSusulan;
    $tabel1 = $paramSu->tabel1;
    $tabel2 = $paramSu->tabel2;
}

$JmlRow = 0;

if($kelurahan){
    // MASAL PerKelurahan

    $nOTiN = [];
    $res = mysqli_query($conn, "SELECT CPM_NOP, COUNT(CPM_NOP) AS c FROM $tabel1 WHERE LEFT(CPM_NOP,10)='$kelurahan' GROUP BY CPM_NOP HAVING c > 1");
    while ($obj = mysqli_fetch_object($res)) {
        $nop = (int)$obj->CPM_NOP;
        if(strlen($nop)==18){
            $nOTiN[] = "'".$nop."'";
        }
    }
    $nOTiN = implode(',',$nOTiN);

    $res = mysqli_query($conn, "SELECT * FROM $tabel1 WHERE (TRIM(IFNULL(CPM_OP_LUAS_TANAH,0))*1)>0 AND LEFT(CPM_NOP,10)='$kelurahan' AND CPM_NOP NOT IN ($nOTiN)");
    while ($obj = mysqli_fetch_object($res)) {
        $nop    = (int)$obj->CPM_NOP;
        $znt    = addslashes(trim($obj->CPM_OT_ZONA_NILAI));
        $luas   = (float)$obj->CPM_OP_LUAS_TANAH;
        $jenis  = (int)$obj->CPM_OT_JENIS;
        $jmlBgn = (int)$obj->CPM_OP_JML_BANGUNAN;
        $luasBgn= (float)$obj->CPM_OP_LUAS_BANGUNAN;

        if($znt!=''){
            $nilai = FindNJOPbumi::penentuanNJOP($nop, $znt, $luas, $tahun, $conn, $tabel1);
            if($luasBgn>0 && $jmlBgn>0){
                $nilaibgn = FindNJOPbgn::penentuanNJOPBangunan($nop, $tahun, $conn, $tabel1, $tabel2);
                
                // utk NOP Ruko Pasar atau Apartement // Jika di perlukan //
                // $res = mysqli_query($conn, "SELECT CPM_NOP_INDUK FROM cppmod_obb_sppt_anggota WHERE CPM_NOP='$nop'");
                // while($obj = mysqli_fetch_object($res)){
                //     PenilaianOPAnggotaMassal::penilaianOPAnggotaMassal($obj->CPM_NOP_INDUK, $thn, $conn);
                // }
            }
            if($JmlRow>0) sleep(1);
            $JmlRow++;
        }
    }

}else{

    // Per NOP
    $nopIN = [];
    foreach ($nops as $nop) {
        $nop = (int)$nop;
        if(strlen($nop)==18){
            $nopIN[] = "'".$nop."'";
        }
    }
    $nopIN = implode(',',$nopIN);

    $res = mysqli_query($conn, "SELECT * FROM $tabel1 WHERE (TRIM(IFNULL(CPM_OP_LUAS_TANAH,0))*1)>0 AND CPM_NOP IN ($nopIN)");
    while ($obj = mysqli_fetch_object($res)) {
        $nop    = (int)$obj->CPM_NOP;
        $znt    = addslashes(trim($obj->CPM_OT_ZONA_NILAI));
        $luas   = (float)$obj->CPM_OP_LUAS_TANAH;
        $jenis  = (int)$obj->CPM_OT_JENIS;
        $jmlBgn = (int)$obj->CPM_OP_JML_BANGUNAN;
        $luasBgn= (float)$obj->CPM_OP_LUAS_BANGUNAN;

        $findredundant = mysqli_query($conn, "SELECT CPM_NOP FROM $tabel1 WHERE CPM_NOP='$nop'");
        $ndobl = mysqli_num_rows($findredundant);
        if($ndobl>1) exit('REDUNDANT NOP '.$nop);

        if($znt!=''){
            $nilai = FindNJOPbumi::penentuanNJOP($nop, $znt, $luas, $tahun, $conn, $tabel1);
            if($luasBgn>0 && $jmlBgn>0){
                $nilaibgn = FindNJOPbgn::penentuanNJOPBangunan($nop, $tahun, $conn, $tabel1, $tabel2);
                
                // utk NOP Ruko Pasar atau Apartement // Jika di perlukan //
                // $res = mysqli_query($conn, "SELECT CPM_NOP_INDUK FROM cppmod_obb_sppt_anggota WHERE CPM_NOP='$nop'");
                // while($obj = mysqli_fetch_object($res)){
                //     PenilaianOPAnggotaMassal::penilaianOPAnggotaMassal($obj->CPM_NOP_INDUK, $thn, $conn);
                // }
            }
            if($JmlRow>0) sleep(1);
            $JmlRow++;
        }
    }
}

$return = array('JUMLAH'=>$JmlRow);

header('Content-Type: application/json; charset=utf-8');
print_r( json_encode($return) );
exit;