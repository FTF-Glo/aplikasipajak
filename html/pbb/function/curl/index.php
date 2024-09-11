<?php 
error_reporting(-1);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
require_once("config.php");
require_once("func.php");

/*
#  INPUT DATA  //////////////////////////////////////////////////////////////////////////////
*/
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $token  = @isset($_POST['token'])   ? $_POST['token']   : false;
    $id     = @isset($_POST['id'])      ? $_POST['id']      : false;
    $exp    = @isset($_POST['exp'])     ? $_POST['exp']     : false;
    $ref    = @isset($_POST['ref'])     ? $_POST['ref']     : false;
    $codepay= (@isset($_POST['paymentcode'])&& is_numeric($_POST['paymentcode']))   ? $_POST['paymentcode'] : false;
    $nop    = (@isset($_POST['nop'])        && is_numeric($_POST['nop']))           ? $_POST['nop']         : false;
    $year   = (@isset($_POST['year'])       && is_numeric($_POST['year']))          ? $_POST['year']        : false;
    $callbackbypass = @isset($_POST['callback']) ? $_POST['callback'] : false;
    $city   = ($nop) ? substr($nop,0,4) : substr($codepay,0,4);
    


    if(!$token) die(msg_false_('PARAMETER TYPE PAJAK FALSE'));
    if(!$exp)   die(msg_false_('PARAMETER NEED EXPIRED DATE'));
    // if(!$ref)   die(msg_false_('PARAMETER NEED REFERENCE NUMBER'));
    if(!$ref) $ref = '2020102900000000000001';

    if($callbackbypass && !strposa($callbackbypass, $ORIGIN_ALLOW, 0)) die(msg_false_('PARAMETER CALL BACK NOT VALID.'));

    if($nop && $year){
        $type       = '00';
        $trx_code   = 'SC4710';
    }elseif($codepay){
        $type       = substr($codepay,-2,2);
        $trx_code   = gettrxcode($type);
        if(!$trx_code) die(msg_false_('UNKNOW TAX CODE TYPE'));
    }else{
        die(msg_false_('PARAMETER FALSE'));
    }
}else{
   die(msg_false_('NOT POST METHOD'));
}

$tokenAllow = checkToken($arraytokenIDENTIFIKASI,$token);

$formatrespont = 'devp';

if(!$tokenAllow) die(msg_false_('Token kasaluarsa, silakan refresh halaman ini'));


/*
#  PROSES DATA - GET API   //////////////////////////////////////////////////////////////////////////////
*/
if($type=='00'){
    $res = getApi($city, $type, $trx_code, $nop, $year, $codepay, $exp, $ref);
    if(!$res->status) die(msg_false_("repeat"));
    $data = $res->data;

}elseif($city=='1801' && ($type=='02' || $type=='03' || $type=='04' || $type=='05' || $type=='06' || $type=='07' || $type=='08' || $type=='09' || $type=='10' || $type=='11' || $type=='50') ){
    /*
    | BPHTB, 9 Pajak dan Retribusi Lampsel
    */
    $res = getApimst($city, $type, $codepay, $exp);
    if(!$res->status) die(msg_false_("repeat"));
    $data = $res->data;
    $nop  = @isset($_POST['nop'])? $_POST['nop']:'';
    $formatrespont = 'mst';

}else{
    $data = '{
                "status": true,
                "message": "Sukses",
                "data": {
                    "result": {
                        "responseCode": "2004700",
                        "responseMessage": "Successful",
                        "additionalInfo": {
                            "trxCode": "'.$trx_code.'",
                            "principalAmount": 0,
                            "fineAmount": 0,
                            "trxAmount": 0,
                            "trxFeeAmount": 0,
                            "trxTotalAmount": 0,
                            "typeTax": {
                                "code": "'.$type.'",
                                "name": "'.getNameTax($type).'"
                            },
                            "city": {
                                "code": "'.$city.'",
                                "name": "-"
                            },
                            "billing": {
                                "billingCode": "'.$code.'",
                                "taxPeriod1": "",
                                "taxPeriod2": "",
                                "taxpayer": {
                                    "identificationNumber": "",
                                    "fullName": "",
                                    "address": ""
                                },
                                "taxObjects": [{
                                    "number": "'.$nop.'",
                                    "name": "",
                                    "address": ""
                                }]
                            }
                        },
                        "referenceNo": "",
                        "partnerReferenceNo": "'.$ref.'",
                        "qrContent": "'.$city.'_'.getNameTax($type).'_'.rand(100,999).'",
                        "merchantName": "PAJAK '.getNameTax($type).'"
                    }
                }
            }';
    $data = json_decode($data);
}



/*
#  CHECKING DATA RESPONSE   //////////////////////////////////////////////////////////////////////////////
*/
if(!$data) die(msg_false_("RESPONSE IS NULL"));

if(!isset($data->status)) die(msg_false_('UNKNOW RESPONSE STATUS'));

if(!$data->status) die(msg_false_($data->message));

$result = $data->data->result;


/*
#  EXECUTE CALLBACK DATA  //////////////////////////////////////////////////////////////////////////////
*/
$insert_it = ($callbackbypass) ? executecallback($type, $nop, $year, $id, $exp, $result, $callbackbypass, $formatrespont) : 'OK';

/*
#  OUTPUT DATA  //////////////////////////////////////////////////////////////////////////////////////
*/
if($insert_it==='OK') {
    $qris = ($callbackbypass) ? '' : "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=".$result->qrContent;
    die(json_encode(['status'=>true,'msg'=>"OK",'qr'=>$qris]));
}else{
    die(msg_false_($insert_it));
}