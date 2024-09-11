<?php

$host = "localhost";
$user = "postgres";
$pass = "rahasia";
$db = "peta_palembang";

$conn = pg_pconnect("host=$host port=5432 dbname=$db user=$user password=$pass");
if (!$conn) {
	echoResponse(0);
    exit;
}

?>