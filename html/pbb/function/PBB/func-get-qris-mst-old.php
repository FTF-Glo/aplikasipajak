<?php

error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);
date_default_timezone_set('Asia/Jakarta');
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");

$SNAP = true;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0){
    $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    die(json_encode(['status'=>false,'msg'=>$sErrMsg]));
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $nop    = @isset($_POST['nop']) ? $_POST['nop'] : false;
    $sha1   = @isset($_POST['sha1']) ? $_POST['sha1'] : false;
    $tahun  = @isset($_POST['year']) ? $_POST['year'] : false;
    $exp    = @isset($_POST['exp']) ? $_POST['exp'] : false;
    $regen  = @isset($_POST['regen']) ? true : false;
    if(!$nop || !$sha1 || !$tahun || !$exp) die(json_encode(['status'=>false,'msg'=>'PARAMETER FALSE']));
}else{
   die(json_encode(['status'=>false,'msg'=>'NOT POST METHOD']));
}

$sha1_validate = sha1('#PBB#LAMPUNG#SELATAN#'.$nop.'#'.date('Ymd').'#');

// print_r(json_encode([ssbid=>$ssbid,sha1=>$sha1_validate]));exit;
if($sha1!==$sha1_validate) die(json_encode(['status'=>false,'msg'=>'SHA1 NOT VALIDATE']));

function getConfigValue($id, $key) {
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "select * from central_app_config where CTR_AC_AID = 'aPBB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) die(json_encode(['status'=>false,'msg'=>$DBLink,'query'=>$qry]));
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function getApi($nop,$tahun,$expired_date){
    //print_r($paymentcode);exit;
    $exp    = strtotime(date('Y-m-d H:i:s', strtotime($expired_date)));
    $now    = strtotime(date('Y-m-d H:i:s'));
    $exp    = round(abs($exp - $now)/60); // contoh 207360 detik
    $city   = '1801';                       // 1801 is Lampung SELATAN
    $type   = '00';                         // Kode PBB

    $url    = "http://117.53.45.7/mst/bank/services/inquiryqrcode";
    $url    .= "?city_code=$city";
    $url    .= "&expired_duration=$exp";
    $url    .= "&tax_object_number=$nop";
    $url    .= "&tax_year=$tahun";
    $url    .= "&type_tax_code=$type";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => $url,
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

function getApiSnap($nop,$tahun,$expired_date){
    // print_r($nop.'|'.$tahun.'|'.$expired_date);exit;
    if($tahun>'2023') exit('<2023');
    $exp    = $expired_date.'+07:00';
    $exp    = str_replace(' ','T',$exp);// contoh kadaluarsa 2023-07-30T16:33:00+07:00
    $city   = substr($nop,0,4);         // 1801 Kode Kabupaten Lampung Selatan
    $type   = '00';                     // Kode PBB

    $url    = "http://103.6.53.226:21300/mst/snap/services/paids";
    // $par[]  = 'partner_reference_no=2020102900000000000001';
    $par[]  = 'partner_reference_no=180120230307145510';
    $par[]  = 'trx_code=SC4710';
    $par[]  = "city_code=$city";
    $par[]  = "validity_period=$exp";
    $par[]  = "tax_year=$tahun";
    $par[]  = "tax_object_number=$nop";
    $par[]  = "type_tax_code=$type";

    $parameter = implode('&',$par);
    // print_r($par);exit;
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $parameter,
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

$dbName = getConfigValue($a, 'ADMIN_GW_DBNAME');
$dbHost = getConfigValue($a, 'ADMIN_DBHOST').':'.getConfigValue($a, 'ADMIN_DBPORT');
$dbPwd  = getConfigValue($a, 'ADMIN_DBPWD');
$dbUser = getConfigValue($a, 'ADMIN_DBUSER');

SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

$qry = "SELECT 
            SPPT_PBB_HARUS_DIBAYAR AS bayar
        FROM pbb_sppt 
        WHERE 
            nop='$nop' AND 
            (PAYMENT_FLAG='0' OR PAYMENT_FLAG IS NULL) AND 
            SPPT_TAHUN_PAJAK='$tahun' 
        LIMIT 0,1";

$rs = mysqli_query($DBLinkLookUp, $qry);

if(mysqli_num_rows($rs)==0) die(json_encode(['status'=>false,'msg'=>"NOP '$nop' not exist"]));

$pbb    = json_decode(json_encode(mysqli_fetch_assoc($rs)));

$bayar  = $pbb->bayar;

if($bayar<=0 && $bayar>10000000) die(json_encode(['status'=>false,'msg'=>"Tagihan = $bayar"]));

if(!$SNAP){
    $res = getApi($nop,$tahun,$exp);
}else{
    $res = getApiSnap($nop,$tahun,$exp);
}

if(!$res->status) die(json_encode(['status'=>false,'msg'=>"repeat"]));

$data = $res->data;

if(!$data) die(json_encode(['status'=>false,'msg'=>"RESPONSE IS NULL"]));

if(!isset($data->responseCode) || $data->responseCode!='2004700') die(json_encode(['status'=>false,'msg'=>$data->responseMessage]));

$result = $data->data->result;

if(!$SNAP){
    $exp = date_create($result->data->expired_date_time);
    $exp = date_format($exp,"Y-m-d H:i:s");
}

$datajson = json_encode($data, JSON_PRETTY_PRINT);
$datajson = str_replace("'"," ",$datajson);
$datajson = str_replace("&","dan",$datajson);

if(!$SNAP){
    $insert = array(
        'tax_object'        => $nop,
        'year'              => $tahun,
        'expired_date_time' => $exp,
        'data'              => $datajson,
        'qr'                => $result->data->qr,
        'principalAmount'   => $result->data->principal_amount,
        'fine_amount'       => $result->data->fine_amount,
        'trx_amount'        => $result->data->trx_amount,
        'trx_fee_amount'    => $result->data->trx_fee_amount,
        'trx_total_amount'  => $result->data->trx_total_amount,
        'created_at'        => date('Y-m-d H:i:s')
    );
}else{
    $insert = array(
        'tax_object'        => $nop,
        'year'              => $tahun,
        'expired_date_time' => $exp,
        'data'              => $datajson,
        'qr'                => $result->qrContent."_Test",
        'principalAmount'   => $result->additionalInfo->principalAmount,
        'fine_amount'       => $result->additionalInfo->fineAmount,
        'trx_amount'        => $result->additionalInfo->trxAmount,
        'trx_fee_amount'    => $result->additionalInfo->trxFeeAmount,
        'trx_total_amount'  => $result->additionalInfo->trxTotalAmount,
        'created_at'        => date('Y-m-d H:i:s')
    );
}

if($regen){
    $sets = [];
    foreach ($insert as $key => $value) {
        if($value!=$nop && $value!=$tahun) $sets[] = "$key = '$value'";
    }
    $sets = implode(',',$sets);
    
    $qry = "UPDATE pbb_sppt_qris SET $sets WHERE tax_object='$nop' AND `year`='$tahun'";
    $rs = mysqli_query($DBLinkLookUp, $qry);
    
}else{
    $fields = [];
    $values = [];
    foreach ($insert as $key => $value) {
        $fields[] = $key;
        $values[] = "'".$value."'";
    }
    $fields = implode(',',$fields);
    $values = implode(', ',$values);

    $qry = "INSERT INTO pbb_sppt_qris ($fields) VALUES ($values)";

    $expcek = substr($exp,0,10);
    $cek = "SELECT tax_object
            FROM pbb_sppt_qris 
            WHERE 
                tax_object='$nop' AND 
                `year`='$tahun' AND 
                LEFT(expired_date_time,10)>='$expcek'
            LIMIT 0,1";

    $ce = mysqli_query($DBLinkLookUp, $cek);

    $rs = false;
    if(mysqli_num_rows($ce)==0) {
        $rs = mysqli_query($DBLinkLookUp, $qry);
    }else{
        die(json_encode(['status'=>false,'msg'=>"QRIS sudah Tergenerate"]));
    }
}
    
SCANPayment_CloseDB($DBLink);

if(!$rs) die(json_encode(['status'=>false,'msg'=>"GAGAL MENYIMPAN QRCode"]));

die(json_encode(['status'=>true,'msg'=>"OK"]));