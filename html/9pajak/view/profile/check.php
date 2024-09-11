<?php
$DIR = "PATDA-V1";
$modul = "profile";
$submodul = "";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

$host = ONPAYS_DBHOST;
$pass = ONPAYS_DBPWD;
$db = ONPAYS_DBNAME;
$user = ONPAYS_DBUSER;
$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_error());

 
if(isset($_POST["user_name"]))
{
 $username = mysqli_real_escape_string($conn, $_POST["user_name"]);
 $query = "SELECT * FROM CENTRAL_USER WHERE CTR_U_UID = '".$username."'";
 $result = mysqli_query($conn, $query);
 echo mysqli_num_rows($result);
}
?>
