<?php

function getApi($city,$tahun,$nop){

  $curl = curl_init();


        
    $expires_on      = strtotime(date('Y-m-t H:i:s', strtotime(date('Y') . '-12-01 23:59:59')));
    $now             = strtotime(date('Y-m-d H:i:s'));
    $diff            = ($expires_on - $now);
    $diff_in_minutes = round(abs($diff) / 60);
  curl_setopt_array($curl, array(
    // CURLOPT_URL => "http://117.53.45.7/devp/bank/services/inquiryqr?city_code=$city&expired_duration=1000&tax_object_number={$nop}&tax_year={$tahun}&type_tax_code=00",
    CURLOPT_URL => "http://117.53.45.7/mst/bank/services/inquiryqr?city_code=1813&expired_duration={$diff_in_minutes}&tax_object_number={$nop}&tax_year={$tahun}&type_tax_code=00", #INI YANG ASLINYA
    // CURLOPT_URL => "http://117.53.45.7/mst/bank/services/inquiryqr?city_code=1813&expired_duration=3&tax_object_number={$nop}&tax_year={$tahun}&type_tax_code=00",
    // CURLOPT_HEADER => 1,
    CURLOPT_HTTPHEADER => array("Channel-Id: INDOMARET"),
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  ));
  
  $response = curl_exec($curl);
  $error = curl_error($curl);
  curl_close($curl);
  $response;
  // var_dump($response);
  $data = json_decode($response, true);
  return $data;
}


$city_ ="1801";
$tahun_ ="2023";
$nop_ ="181301000900300990";
$getapapi = getApi($city_,$tahun_,$nop_);
var_dump($getapapi);die;
