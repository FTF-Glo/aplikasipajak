<?php
// URL API
$url = 'http://117.53.45.7/devp/snap/services/paids';

// Data yang akan dikirimkan dalam body request
$data = array(
    'partner_reference_no' => '180120230307145510',
    'trx_code' => 'SC4711',
    'city_code' => '1801',
    'validity_period' => '2023-04-20T16:33:00+07:00',
    'type_tax_code' => '02',
    'billing_code' => '1801230300048902'
);

// Menginisialisasi curl
$ch = curl_init();

// Set curl options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Mengirim request
$response = curl_exec($ch);

// Menutup curl
curl_close($ch);

// var_dump('sadsad');
// Menampilkan response dari API
echo $response;