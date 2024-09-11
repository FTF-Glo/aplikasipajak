<?php
ini_set("display_errors",1);
require_once(dirname(__FILE__)."/func-update.php");
$q = base64_decode($_REQUEST['q']);
$_REQUEST = json_decode($q,true);
echo"<br>";
echo cekUpdate($_REQUEST);
?>