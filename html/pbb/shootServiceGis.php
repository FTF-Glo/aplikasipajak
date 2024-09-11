<?php
/*kirim data peta*/
$url = "http://192.168.26.109/pangkalpinang-gis/service/migrate/movetopersil";

$vars = array(
	'jns_tanah'=>'',
	'kelas_jnt'=>'',
	'nir'=>'',
	'cpm_sppt_id'=>'',
	'jns_trnks'=>'',
	
);

$postData = http_build_query($vars);


$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );

print_r($response);
?>
