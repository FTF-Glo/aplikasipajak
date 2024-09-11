<?php

function checkToken($array,$token){
    $nowdate = date('ymd');
    foreach ($array as $v) {
        if(password_verify($v.$nowdate, $token)) return true;
    }
    return false;
}


function getApi($city, $type, $trx_code, $nop, $year, $paymentcode, $expired_date, $ref){
    // print_r($exp);exit;
    $exp    = str_replace(' ', 'T', $expired_date) . '+07:00';

    $postParameter = array(
        "partner_reference_no"  => $ref,        // Kode partner system
        "trx_code"              => $trx_code,   // type SC4710=PBB,     SC4711=BPHTB & 9-Pajak,    SC4712=Retribusi
        "city_code"             => $city,       // Contoh 1801 is Lampung SELATAN
        "validity_period"       => $exp         // contoh 2025-01-28T16:33:00+07:00
    );

    if($type=='00'){
        $addPar = array(
            "tax_year"              => $year,   // Tahun Pajak
            "tax_object_number"     => $nop     // Nomor Objek Pajak
        );
    }else{
        $addPar = array(
            "type_tax_code"         => $type,       // Kode Jenis (dua digit dari belakang paymentcode)
            "billing_code"          => $paymentcode // Kode Bayar
        );
    }

    $postParameter = array_merge($postParameter,$addPar);

    foreach ($postParameter as $key => $value) $postParameterx[] = "$key=$value";
    $postParameter = implode('&',$postParameterx); //print_r($postParameter);exit;
    $url = "http://117.53.45.7/devp/snap/services/paids";
    $ip = '36.'.rand(0,225).'.'.rand(0,225).'.'.rand(0,225);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL             => $url,
		CURLOPT_POST			=> true,
        CURLOPT_POSTFIELDS      => $postParameter,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_ENCODING        => '',
        CURLOPT_MAXREDIRS       => 10,
        CURLOPT_TIMEOUT         => 10,
        CURLOPT_FOLLOWLOCATION  => 1,
		CURLOPT_SSL_VERIFYPEER 	=> 0,
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPAUTH       	=> CURLAUTH_ANY,
        CURLOPT_HTTPHEADER      => array('Cache-Control: no-cache','Content-Type: application/x-www-form-urlencoded','Cookie: ci_session='.generate_ci_session(),"REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip")
    ));

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
	
    $obj = (object)[];
    $obj->status = ($error) ? false : true;
    $obj->msg = ($error) ? $error : 'OK';
    $obj->data = ($error) ? false : json_decode($response);
    return $obj;
}

function getApimst($city, $type, $paymentcode, $expired_date){
    // print_r($expired_date);exit;
    $expires_on      = strtotime(date('Y-m-d H:i:s', strtotime($expired_date)));
    $now             = strtotime(date('Y-m-d').' 08:59:59');
    $diff            = ($expires_on - $now);
    $diff_in_minutes = round(abs($diff) / 60);

    $ch = curl_init();
    $param = "?city_code=$city";
    $param .= "&expired_duration=$diff_in_minutes";
    $param .= "&billing_code=$paymentcode";
    $param .= "&type_tax_code=".$type;
    curl_setopt_array($ch, array(
        CURLOPT_URL            => "http://117.53.45.7/mst/bank/services/inquiryqrcode".$param,
        CURLOPT_HTTPHEADER     => array("Channel-Id: QRIS"),
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT        => 10
    ));
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if($response === false) {
        $obj = (object)[];
        $obj->status = false;
        $obj->msg = ($error) ? $error : 'Services not connect to GateWay';
        $obj->data = false;
        return $obj;
    }
	
    $obj = (object)[];
    $obj->status = ($error) ? false : true;
    $obj->msg = ($error) ? $error : 'OK';
    $obj->data = ($error) ? false : json_decode($response);
    return $obj;
}


function generate_ci_session(){
	$c = " abcdefghijkmnopqrstuvwxyz0123456789";
    $l = strlen($c);
    $str = '';
    for ($i = 0; $i < 32; $i++) $str .= $c[rand(1, $l - 1)];
    return $str;
}


function executecallback($type, $nop, $year, $id_switching, $exp, $response, $callbackbypass, $formatrespont){
    if($type=='00'){
        $addPar = array('`year`' => $year);

    }elseif($type=='02'){
        $addPar = array('id_switching' => $id_switching);

    }elseif($type>='03' && $type<='11'){
        $addPar = array('tax_code'=> $type,'id_switching' => $id_switching);

    }elseif($type>='12' && $type<='50'){
        $addPar = array('tax_code'=> $type,'id_switching' => $id_switching);

    }else{
        return 'NOTTING INSERT IN '.getNameTax($type);
    }

    if($formatrespont=='devp'){
        $datajson = json_encode($response, JSON_PRETTY_PRINT);
        $datajson = str_replace("'"," ", $datajson);
        $datajson = str_replace("&","dan", $datajson);
        $insert = array(
            'tax_object'        => $nop,
            'expired_date_time' => $exp,
            'data'              => $datajson,
            'qr'                => $response->qrContent,
            'principalAmount'   => $response->additionalInfo->principalAmount,
            'fine_amount'       => $response->additionalInfo->fineAmount,
            'trx_amount'        => $response->additionalInfo->trxAmount,
            'trx_fee_amount'    => $response->additionalInfo->trxFeeAmount,
            'trx_total_amount'  => $response->additionalInfo->trxTotalAmount,
            'created_at'        => date('Y-m-d H:i:s')
        );
    }else{
        $response = $response->data;
        $datajson = json_encode($response, JSON_PRETTY_PRINT);
        $datajson = str_replace("'"," ", $datajson);
        $datajson = str_replace("&","dan", $datajson);
        $exp_ = $response->expired_date_time;
        $k = explode(' ',$exp_);
        if(count($k)>1){
            $tgl_exp = $k[0];
            $jam_exp = $k[1];
            $k = explode('-',$tgl_exp);
            if(count($k)==3){
                $exp = $k[2].'-'.$k[1].'-'.$k[0] . ' '. $jam_exp;
            }
        }
        $insert = array(
            'tax_object'        => $nop,
            'expired_date_time' => $exp,
            'data'              => $datajson,
            'qr'                => $response->qr,
            'principalAmount'   => $response->principal_amount,
            'fine_amount'       => $response->fine_amount,
            'trx_amount'        => $response->trx_amount,
            'trx_fee_amount'    => $response->trx_fee_amount,
            'trx_total_amount'  => $response->trx_total_amount,
            'created_at'        => date('Y-m-d H:i:s')
        );
    }
    

    $insert = array_merge($addPar,$insert);

    $fields = [];
    $values = [];
    foreach ($insert as $key => $value) {
        $fields[] = $key;
        $values[] = "'".$value."'";
    }
    $fields = implode(',',$fields);
    $values = implode(', ',$values);

    $tabelName = getTableName($type);

    $qry = "INSERT INTO $tabelName ($fields) VALUES ($values)";

    $codesha1 = sha1($qry);
    $postParameter = array("sha1"=>$codesha1,"qry"=>$qry);
    foreach ($postParameter as $key => $value) $postParameterx[] = "$key=$value";
    $postParameter = implode('&',$postParameterx);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL             => $callbackbypass,
		CURLOPT_POST			=> true,
		CURLOPT_RETURNTRANSFER	=> true,
        CURLOPT_POSTFIELDS      => $postParameter,
        CURLOPT_ENCODING        => '',
        CURLOPT_TIMEOUT         => 0
    ));
    $eksekusi = curl_exec($ch);
    $error = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($eksekusi===false){
        return 'CANT SAVE'; 
    }elseif($error){
        return $error ;
    }elseif($code!='200'){
        return 'CODE RESPONSE CALLBACK IS ' . $code; 
    }
    return 'OK';
}

function gettrxcode($type){
    $trx = json_decode(apicode,true);
    return isset($trx[$type][0]) ? $trx[$type][0] : false;
}

function getNameTax($type){
    $nm = json_decode(apicode,true);
    return isset($nm[$type][1]) ? $nm[$type][1] : '-';
}

function getTableName($type){
    $db = json_decode(apicode,true);
    return isset($db[$type][2]) ? $db[$type][2] : false;
}

function msg_false_($msg, $addKeyPar=false, $addValPar=false){
    $arr = array('status'=>false,'msg'=>$msg);
    if($addKeyPar) {
        $addPar = array($addKeyPar=>$addValPar);
        $arr = array_merge($arr,$addPar);
    }
    $ret = json_encode($arr);
    return $ret;
}


function strposa($haystack, $needles=array(), $offset=0) {
    $chr = array();
    foreach($needles as $needle) {
            $res = strpos($haystack, $needle, $offset);
            if ($res !== false) $chr[$needle] = $res;
    }
    if(empty($chr)) return false;
    return true;
}