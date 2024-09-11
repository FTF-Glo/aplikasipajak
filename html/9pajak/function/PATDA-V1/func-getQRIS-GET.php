<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
$DIR = "PATDA-V1";
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . $DIR, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/db-payment.php");



SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    die(json_encode(['status'=>false,'msg'=>$sErrMsg]));
}

$id = @isset($_POST['idswitching']) ? $_POST['idswitching']: false;
$sha1 = @isset($_POST['sha1']) ? $_POST['sha1']: false;

if(!$id || !$sha1) die(json_encode(['status'=>false,'msg'=>'PARAMETER FALSE']));

$sha1_validate = sha1('#9PAJAK#PESAWARAN#'.$id.'#'.date('Ymd').'#');

// print_r($sha1_validate);exit;
if($sha1!==$sha1_validate) die(json_encode(['status'=>false,'msg'=>'SHA1 NOT VALIDATE']));

function getConfigValue($key) {
    global $DBLink;
    $qry = "SELECT * FROM central_app_config WHERE CTR_AC_AID='aPatda' AND CTR_AC_KEY='$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        $err = mysqli_error($DBLink);
        die(json_encode(['status'=>false,'msg'=>$err]));
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function getApiSnap($city, $type, $paymentcode, $expired_date){
    // print_r($city .' | '. $type .' | '. $paymentcode .' | '. $expired_date);exit;
    $exp    = $expired_date.'+07:00';
    $exp    = str_replace(' ','T',$exp);// contoh kadaluarsa 2029-07-30T16:33:00+07:00 

    $url    = "http://103.6.53.226:21300/mst/snap/services/paids";
    $par[]  = "partner_reference_no=2020102900000000000001";
    $par[]  = 'trx_code=SC4711'; // SC4711 utk bphtb & 9pajak
    $par[]  = "city_code=$city";
    $par[]  = "validity_period=$exp";
    $par[]  = "type_tax_code=$type";
    $par[]  = "billing_code=$paymentcode";

    $parameter = implode('&',$par);
    // print_r($parameter);exit;

    $curl   = curl_init();
   
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

    if($response === false) {
        $obj = (object)[];
        $obj->status = false;
        $obj->msg = ($error) ? $error : 'Services not connect to GateWay 103.6.53.226';
        $obj->data = false;
        return $obj;
    }
	
    $obj = (object)[];
    $obj->status = ($error) ? false : true;
    $obj->msg = ($error) ? $error : 'OK';
    $obj->data = ($error) ? false : json_decode($response);
    return $obj;
}

$dbName = getConfigValue('PATDA_DBNAME');
$dbHost = getConfigValue('PATDA_HOSTPORT');
$dbPwd  = getConfigValue('PATDA_PASSWORD');
$dbTable= getConfigValue('PATDA_TABLE');
$dbUser = getConfigValue('PATDA_USERNAME');

SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

$qry = "SELECT payment_code AS c, expired_date AS exp, simpatda_type AS tipe FROM $dbName.$dbTable WHERE id_switching='$id' AND payment_flag=0";

$rs = mysqli_query($DBLinkLookUp, $qry);
if(mysqli_num_rows($rs)==0) die(json_encode(['status'=>false,'msg'=>"payment_flag 1"]));

$gw    = json_decode(json_encode(mysqli_fetch_assoc($rs)));
$code   = $gw->c;
$tipe   = $gw->tipe;
$exp    = $gw->exp . ' 23:59:59';
$city   = '1812';


///// tipe  ===
if($tipe=='4' || $tipe=='24'){
    $tipe = '03';
}
elseif($tipe=='5' || $tipe=='25'){
    $tipe = '04';
}
elseif($tipe=='6' || $tipe=='26'){
    $tipe = '05';
}
elseif($tipe=='7' || $tipe=='27'){
    $tipe = '06';
}
elseif($tipe=='8' || $tipe=='28'){
    $tipe = '07';
}
elseif($tipe=='9' || $tipe=='29'){
    $tipe = '08';
}
elseif($tipe=='10' || $tipe=='30'){
    $tipe = '09';
}
elseif($tipe=='11' || $tipe=='31'){
    $tipe = '10';
}
elseif($tipe=='12' || $tipe=='32'){
    $tipe = '11';
}


$res = getApiSnap($city,$tipe,$code,$exp);

if(!$res->status) die(json_encode(['status'=>false,'msg'=>"repeat"]));

$data = $res->data;

if(!$data) die(json_encode(['status'=>false,'msg'=>"RESPONSE IS NULL"]));
if(!isset($data->responseCode)) die(json_encode(['status'=>false,'msg'=>'2 RESPONSE IS NULL']));
if($data->responseCode!=='2004700') die(json_encode(['status'=>false,'msg'=>$data->responseMessage]));

$datajson = json_encode($data, JSON_PRETTY_PRINT);
$datajson = str_replace("'"," ", $datajson);
$datajson = str_replace("&","dan", $datajson);

$insert = array(
    'id_switching'      => $id,
    'expired_date_time' => $exp,
    'tax_object'        => $data->additionalInfo->billing->taxObjects[0]->number,
    'data'              => $datajson,
    'qr'                => $data->qrContent,
    'principalAmount'   => $data->additionalInfo->principalAmount,
    'fine_amount'       => $data->additionalInfo->fineAmount,
    'trx_amount'        => $data->additionalInfo->trxAmount,
    'trx_fee_amount'    => $data->additionalInfo->trxFeeAmount,
    'trx_total_amount'  => $data->additionalInfo->trxTotalAmount,
    'created_at'        => date('Y-m-d H:i:s')
);

$fields = [];
$values = [];
foreach ($insert as $key => $value) {
    $fields[] = $key;
    $values[] = "'".$value."'";
}
$fields = implode(',',$fields);
$values = implode(', ',$values);

$qry = "INSERT INTO simpatda_qris ($fields) VALUES ($values)";

$rs = mysqli_query($DBLinkLookUp, $qry);

SCANPayment_CloseDB($DBLink);

if(!$rs) die(json_encode(['status'=>false,'msg'=>'Tidak dapat Menyimpan QR']));

die(json_encode(['status'=>true,'msg'=>"OK"]));