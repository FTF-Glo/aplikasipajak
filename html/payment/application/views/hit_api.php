<?php

$curl = curl_init();

$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcHByZXN0c2VydmljZSIsImF1ZCI6InVzZXIiLCJpYXQiOjE2NzUyMzY4ODksIm5iZiI6MTY3NTIzNjg5OSwiZXhwIjoxNjc1NTM2ODg5LCJyZXNwb25zZV9jb2RlIjoiMDAiLCJyZXNwb25zZV9tZXNzYWdlIjoiTG9naW4gc3VjY2VzcyIsImRhdGEiOnsidXNlcm5hbWUiOiJCTkl0cmlhbCJ9fQ.ugpRB8gfsYnyAHG6YBlM_1JXCcjd9QuJ0hw2nAGdAy8'
);

$data = array(
    'area_code' => '1808',
    'tax_type' => '0010',
    'billing_code' => '1808210500004310'
);

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://36.91.109.34:8094/Inq9Pajak",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($data),
  CURLOPT_HTTPHEADER => $headers,
));

$response = curl_exec($curl);

curl_close($curl);

echo $response;

?>