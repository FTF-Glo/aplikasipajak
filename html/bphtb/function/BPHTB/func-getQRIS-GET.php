<?php

error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

date_default_timezone_set("Asia/Jakarta");

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");

header('Content-Type: application/json; charset=utf-8');

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    die(json_encode([status => false, msg => 'FATAL ERROR: ' . $sErrMsg]));
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$ssbid = @isset($_GET['ssbid']) ? $_GET['ssbid'] : false;
$sha1 = @isset($_GET['sha1']) ? $_GET['sha1'] : false;

if (!$ssbid || !$sha1) die(json_encode(['status' => false, 'msg' => 'PARAMETER FALSE']));

// $sha1_validate = sha1('#BPHTB#LAMPUNG#SELATAN#'.$ssbid.'#'.date('Ymd').'#');

// print_r(json_encode([ssbid=>$ssbid,sha1=>$sha1_validate]));exit;
if ($sha1 !== $sha1_validate) die(json_encode(['status' => false, 'msg' => 'SHA1 NOT VALIDATE']));

function getConfigValue($id, $key)
{
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "SELECT * FROM central_app_config WHERE CTR_AC_AID='aBPHTB' AND CTR_AC_KEY='$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) die(json_encode(['status' => false, 'msg' => $DBLink, 'query' => $qry]));
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function getApiSnap($city, $paymentcode, $expired_date)
{
    // print_r($city.'|'.$paymentcode.'|'.$expired_date);exit;
    $exp    = $expired_date . '+07:00';
    $exp    = str_replace(' ', 'T', $exp); // contoh kadaluarsa 2023-07-30T16:33:00+07:00            // Kode BPHTB

    $url    = "http://103.6.53.226:21300/mst/snap/services/paids";
    $par[]  = 'partner_reference_no=180120230307145510';
    $par[]  = 'trx_code=SC4711'; // SC4711 utk bphtb & 9pajak
    $par[]  = "city_code=$city";
    $par[]  = "validity_period=$exp";
    $par[]  = "type_tax_code=02";
    $par[]  = "billing_code=$paymentcode";

    $parameter = implode('&', $par);
    // print_r($par);exit;

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
    // print_r( json_encode($response) );exit;

    $obj = (object)[];
    $obj->status = ($error) ? false : true;
    $obj->msg = ($error) ? $error : 'OK';
    $obj->data = ($error) ? false : json_decode($response);
    return $obj;
}

$dbName = getConfigValue($a, 'BPHTBDBNAME');
$dbHost = getConfigValue($a, 'BPHTBHOSTPORT');
$dbPwd  = getConfigValue($a, 'BPHTBPASSWORD');
$dbTable = getConfigValue($a, 'BPHTBTABLE');
$dbUser = getConfigValue($a, 'BPHTBUSERNAME');

SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

$qry = "SELECT payment_code AS c, expired_date AS exp FROM ssb WHERE id_switching='$ssbid' AND payment_flag=0";
$rs = mysqli_query($DBLinkLookUp, $qry);

if (mysqli_num_rows($rs) == 0) die(json_encode(['status' => false, 'msg' => "payment_flag 1"]));

$sbb    = json_decode(json_encode(mysqli_fetch_assoc($rs)));
$code   = $sbb->c;
$exp    = $sbb->exp . ' 23:59:59';
$city   = '1801';

$res = getApiSnap($city, $code, $exp);

if (!$res->status) die(json_encode(['status' => false, 'msg' => "repeat"]));

$data = $res->data;

if (!$data) die(json_encode(['status' => false, 'msg' => "RESPONSE IS NULL"]));

if (!isset($data->responseCode)) die(json_encode(['status' => false, 'msg' => '2 RESPONSE IS NULL']));

if ($data->responseCode != '2004700') die(json_encode(['status' => false, 'msg' => $data->responseMessage]));

$datajson = json_encode($data, JSON_PRETTY_PRINT);
$datajson = str_replace("'", " ", $datajson);
$datajson = str_replace("&", "dan", $datajson);

$insert = array(
    'id_switching'      => $ssbid,
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
    $values[] = "'" . $value . "'";
}
$fields = implode(',', $fields);
$values = implode(', ', $values);

$qry = "INSERT INTO qris ($fields) VALUES ($values)";


$rs = mysqli_query($DBLinkLookUp, $qry);

SCANPayment_CloseDB($DBLink);

if (!$rs) die(json_encode(['status' => false, 'msg' => 'Tidak dapat Menyimpan QR']));

die(json_encode(['status' => true, 'msg' => "OK"]));
