<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'curl', '', dirname(__FILE__))) . '/';
date_default_timezone_set("Asia/Jakarta");

/*
// HTTP_ORIGIN yang di bolehkan untuk akses file curl ini
//
*/
$ORIGIN_ALLOW = array(
    'http://36.92.151.83:2010',   // LAMPUNG SELATAN PBB
    'http://103.140.188.162:5052' // BANDAR LAMPUNG PBB
);

/*
// Array token IDENTIFIKASI untuk cek token dari POST
//
*/
$arraytokenIDENTIFIKASI = array(
    'LAMPUNGSELATAN',
    'BANDARLAMPUNG'
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

header('Content-Type: application/json; charset=utf-8');

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $token= @isset($_POST['token']) ? $_POST['token'] : false;
    $nop  = (@isset($_POST['nop'])      && is_numeric($_POST['nop']))   ? $_POST['nop']     : false;
    $tahun= (@isset($_POST['tahun'])    && is_numeric($_POST['tahun'])) ? $_POST['tahun']   : false;
    if(!$token || !$nop || !$tahun) die(json_encode(['status'=>false,'msg'=>'PARAMETER FALSE']));
}else{
   die(json_encode(['status'=>false,'msg'=>'NOT POST METHOD']));
}

$tokenAllow = false;
$nowdate = date('ymd');
foreach ($arraytokenIDENTIFIKASI as $v) {
    $tokenIDENTIFIKASI = $v.$nowdate;
    if (password_verify($tokenIDENTIFIKASI, $token)) {
        $tokenAllow = true;
        break;
    }
}

if(!$tokenAllow) die(json_encode(['status'=>false,'msg'=>'Token kadaluarsa, silakan refresh halaman ini']));

function getApi($nop, $tahun){
    $expires_on      = strtotime(date('Y-m-t H:i:s', strtotime(date('Y') . '-12-01 23:59:59')));
    $now             = strtotime(date('Y-m-d H:i:s'));
    $diff            = ($expires_on - $now);
    $diff_in_minutes = round(abs($diff) / 60);
    $city_code       = substr($nop,0,4); // 1801 LAMPUNG SELATAN

    $curl = curl_init();
    $param = "?city_code=$city_code";
    $param .= "&expired_duration=$diff_in_minutes";
    $param .= "&tax_object_number=$nop";
    $param .= "&tax_year=$tahun";
    $param .= "&type_tax_code=00";
    curl_setopt_array($curl, array(
        CURLOPT_URL            => "http://117.53.45.7/mst/bank/services/inquiryqrcode".$param,
        CURLOPT_HTTPHEADER     => array("Channel-Id: QRIS"),
        CURLOPT_RETURNTRANSFER => 1,
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    $obj = (object)[];
    $obj->status = ($error) ? false : true;
    $obj->msg = ($error) ? $error : 'OK';
    $obj->data = ($error) ? false : json_decode($response);
    return $obj;
}

$res = getApi($nop,$tahun);
if(!$res->status) die(json_encode(['status'=>false,'msg'=>"Gagal, Mohon di ulangi lagi beberapa menit kemudian"]));

$data = $res->data;

if(!$data) die(json_encode(['status'=>false,'msg'=>"RESPONSE IS NULL"]));

if(!isset($data->status) || !$data->status) die(json_encode(['status'=>false,'msg'=>$data->message]));

$result = $data->data->result->data;

$qris = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=".$result->qr;

die(json_encode(['status'=>true,'msg'=>"OK",'qr'=>$qris]));