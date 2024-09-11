<?php
ini_set("display_errors", 1); error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath."inc/PBB/dbSppt.php");

$dbSpec = mysqli_connect("192.168.168.192:3306","sw_user_devel","sw_pwd_devel","VSI_SWITCHER_DEVEL");
//mysql_select_db("VSI_SWITCHER_DEVEL");

$dbSppt = new DbSppt($dbSpec);
echo $dbSppt->isExist_NOP($_REQUEST['nop']);
?>
