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

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0){
    $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    die(json_encode(['status'=>false,'msg'=>$sErrMsg]));
}

$dbName = getConfigValue($a, 'ADMIN_GW_DBNAME');
$dbHost = getConfigValue($a, 'ADMIN_DBHOST').':'.getConfigValue($a, 'ADMIN_DBPORT');
$dbPwd  = getConfigValue($a, 'ADMIN_DBPWD');
$dbUser = getConfigValue($a, 'ADMIN_DBUSER');

SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $nop    = @isset($_POST['nop']) ? $_POST['nop'] : false;
    $sha1   = @isset($_POST['sha1']) ? $_POST['sha1'] : false;
    $tahun  = @isset($_POST['year']) ? $_POST['year'] : false;
    $exp    = @isset($_POST['exp']) ? $_POST['exp'] : false;
    $from   = @isset($_POST['from']) ? $_POST['from'] : false;
    $regen  = @isset($_POST['regen']) ? true : false;

    // ini khusus untuk Aplikasi cek pajak
    if($from && $from=='cek_pajak'){
        $qry = "SELECT 
                    DATE(SPPT_TANGGAL_JATUH_TEMPO) AS jatuh_tempo
                FROM pbb_sppt 
                WHERE 
                    nop='$nop' AND 
                    (PAYMENT_FLAG='0' OR PAYMENT_FLAG IS NULL) AND 
                    SPPT_TAHUN_PAJAK='$tahun' 
                LIMIT 0,1";
        $rse = mysqli_query($DBLinkLookUp, $qry);
        if(mysqli_num_rows($rse)>0){
            $expx= json_decode(json_encode(mysqli_fetch_assoc($rse)));
            $exp = $expx->jatuh_tempo . " 23:59:59";
        }
    }
    // ===================================

    if(!$nop || !$sha1 || !$tahun || !$exp) die(json_encode(['status'=>false,'msg'=>'PARAMETER FALSE','q'=>$qry]));
}else{
   die(json_encode(['status'=>false,'msg'=>'NOT POST METHOD']));
}

$sha1_validate = sha1('#PBB#LAMPUNG#SELATAN#'.$nop.'#'.date('Ymd').'#');

// print_r(json_encode([ssbid=>$ssbid,sha1=>$sha1_validate]));exit;
if($sha1!==$sha1_validate) die(json_encode(['status'=>false,'msg'=>'SHA1 NOT VALIDATE']));

function getConfigValue($id, $key) {
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "SELECT * FROM central_app_config WHERE CTR_AC_AID='aPBB' AND CTR_AC_KEY='$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) die(json_encode(['status'=>false,'msg'=>$DBLink,'query'=>$qry]));
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function getApiSnap($nop,$tahun,$expired_date){
    // print_r($nop.'|'.$tahun.'|'.$expired_date);exit;
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

$res = getApiSnap($nop,$tahun,$exp);

if(!$res->status) die(json_encode(['status'=>false,'msg'=>"repeat"]));

$data = $res->data;

if(!$data) die(json_encode(['status'=>false,'msg'=>"RESPONSE IS NULL"]));

if(!isset($data->responseCode)) die(json_encode(['status'=>false,'msg'=>'(2) RESPONSE IS NULL']));
if($data->responseCode!='2004700') die(json_encode(['status'=>false,'msg'=>$data->responseMessage]));

$datajson = json_encode($data, JSON_PRETTY_PRINT);
$datajson = str_replace("'"," ",$datajson);
$datajson = str_replace("&","dan",$datajson);

$insert = array(
    'tax_object'        => $nop,
    'year'              => $tahun,
    'expired_date_time' => $exp,
    'data'              => $datajson,
    'qr'                => $data->qrContent,
    'principalAmount'   => $data->additionalInfo->principalAmount,
    'fine_amount'       => $data->additionalInfo->fineAmount,
    'trx_amount'        => $data->additionalInfo->trxAmount,
    'trx_fee_amount'    => $data->additionalInfo->trxFeeAmount,
    'trx_total_amount'  => $data->additionalInfo->trxTotalAmount,
    'created_at'        => date('Y-m-d H:i:s')
);

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
        
        if($from && $from=='cek_pajak'){
            $img = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=".$data->qrContent;
            die(json_encode(['status'=>true,'msg'=>"OK",'qr'=>$img]));
        }else{
            die(json_encode(['status'=>false,'msg'=>"QRIS sudah Tergenerate"]));
        }
    }
}
    
SCANPayment_CloseDB($DBLink);

if(!$rs) die(json_encode(['status'=>false,'msg'=>"GAGAL MENYIMPAN QRCode"]));

if($from && $from=='cek_pajak'){
    $img = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=".$data->qrContent;
    die(json_encode(['status'=>true,'msg'=>"OK",'qr'=>$img]));
}else{
    die(json_encode(['status'=>true,'msg'=>"OK"]));
}