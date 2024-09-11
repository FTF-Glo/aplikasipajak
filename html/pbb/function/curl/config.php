<?php

date_default_timezone_set("Asia/Jakarta");

/*
// HTTP_ORIGIN yang di bolehkan untuk akses file curl ini
//
*/
$ORIGIN_ALLOW = array(
    'http://36.92.151.83:2010',     // LAMPUNG SELATAN PBB
    'http://36.92.151.83:6030',     // LAMPUNG SELATAN BPHTB
    'http://36.92.151.83:2000',     // LAMPUNG SELATAN 9 PAJAK & RETRIBUSI MENARA
    'http://e-pajak.lampungselatankab.go.id:2000', // LAMPUNG SELATAN 9 PAJAK & RETRIBUSI MENARA
    'http://103.140.188.145:8090',  // LAMPUNG TENGAH 9 PAJAK & RETRIBUSI MENARA
    'http://103.140.188.162:5051',  // BANDAR LAMPUNG PBB
    'http://103.140.188.140:8090'   // LAMPUNG TENGAH 9 PAJAK
);

/*
// Array token IDENTIFIKASI untuk cek token dari POST
// silakan tambahkan jika ingin mengunakan service ini
*/
$arraytokenIDENTIFIKASI = array(
    'LAMPUNGSELATANPBB',
    'LAMPUNGSELATANBPHTB',
    'LAMPUNGSELATAN9PAJAK',
    'LAMPUNGSELATANRETRIBUSIMENARA',
    'BANDARLAMPUNGPBB',
    'LAMPUNGTENGAH9PAJAK'
);

/*
// Array sudah siap
// Kota Dan Type Pajak
// silakan tambahkan dan ubah jika ingin type pajak dan kota nya sudah siap
*/
$arraySUDAHSIAP = array(
    '1871'=> array('00','02'), 
    '1801'=> array('00','02','04'),
    '1801'=> array('00','02','04')
);

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $ORIGIN_ALLOW)){
    header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}else{
    die('ORIGIN_NOT_ALLOW');
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit();
}


$arrType = array(
    '00' => ['SC4710','PBB',                'gw_pbb.pbb_sppt_qris'],
    '02' => ['SC4711','BPHTB',              'gw_ssb.qris'],
    '03' => ['SC4711','HOTEL',              '9pajak.simpatda_qris'],
    '04' => ['SC4711','RESTORAN',           '9pajak.simpatda_qris'],
    '05' => ['SC4711','HIBURAN',            '9pajak.simpatda_qris'],
    '06' => ['SC4711','REKLAME',            '9pajak.simpatda_qris'],
    '07' => ['SC4711','PENERANGAN JALAN',   '9pajak.simpatda_qris'],
    '08' => ['SC4711','MINERBA',            '9pajak.simpatda_qris'],
    '09' => ['SC4711','PARKIR',             '9pajak.simpatda_qris'],
    '10' => ['SC4711','AIR BAWAH TANAH',    '9pajak.simpatda_qris'],
    '11' => ['SC4711','SARANG BURUNG WALET','9pajak.simpatda_qris'],
    '12' => ['SC4712','RETRIBUSI SAMPAH',   '9pajak_retri.terxxx'],
    '13' => ['SC4712','RETRIBUSI AAA',      '9pajak_retri.retaaa'],
    '14' => ['SC4712','RETRIBUSI BBB',      '9pajak_retri.retbbb'],
    '15' => ['SC4712','RETRIBUSI CCC',      '9pajak_retri.retccc'],
    '50' => ['SC4712','RETRIBUSI MENARA',   '9pajak_retri.simpatda_qris']
);

define('apicode',json_encode($arrType));

header('Content-Type: application/json; charset=utf-8');