<?php
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

$sRootPath = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'svr', '', dirname(__FILE__)).'/');

require_once($sRootPath."inc/payment/json.php");

ob_start();

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

// encode to json
$aResponse['dt'] = strftime("%Y-%m-%d %H:%M:%S", time());

$sResponse = $json->encode($aResponse);

// send response to client
header("content-type: application/json; charset=utf-8");
echo $sResponse;

ob_end_flush();
?>
