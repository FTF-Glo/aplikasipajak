<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    
	$q = strtolower($_GET["q"]);
	if (!$q) return;
	$sql = "SELECT * FROM TBL_REG_USER_PBB WHERE nm_lengkap LIKE '%$q%'";
	$querysql = mysqli_query($DBLink, $sql);
	while($nm = mysqli_fetch_array($querysql)) {
		$nama = $nm['nm_lengkap'];
		echo "$nama\n";
	}
?>