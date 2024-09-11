<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'update_fasilitas', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function showNamaFasilitas(){
	global $DBLink;
	$query = "SELECT a.uuid_op, ";
	$res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
		return 0;
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			return $row;
        }                
    }
	
}

function updateExtFinal(){
	global $DBLink;
	$query = "UPDATE cppmod_pbb_sppt_ext_final_MIGRASI SET $field = $value";
	$res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
		return 0;
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			return $row;
        }                
    }

}

$sql = "SELECT * FROM fasilitas_pbb_migrasi";
$result = mysqli_query($DBLink, $sql);
if(!$result){
	echo mysqli_error($DBLink);
}else{
	echo "Migrasi Sukses!!!";
}

?>