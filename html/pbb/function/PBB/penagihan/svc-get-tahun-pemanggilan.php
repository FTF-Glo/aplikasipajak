<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");

$json 		= new Services_JSON();
$response 	= array();

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getTahunSP2($nop){
	global $db_host, $db_name, $db_user, $db_pwd;
	
	$GWDBLink = mysqli_connect($db_host,$db_user,$db_pwd, $db_name) or die(mysqli_error($DBLink));
	//mysql_select_db($db_name,$GWDBLink);
	
	$query 	= "SELECT TAHUN_SP2 FROM PBB_SPPT_PENAGIHAN WHERE NOP = '".$nop."' AND STATUS_SP = '1' AND STATUS_PERSETUJUAN = '2'";
	// echo $query;
	$res 	= mysqli_query($GWDBLink, $query);
	if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
			return $row['TAHUN_SP2'];
        }
        return 0;
    }
}

function getDataTahun($nop="",$tahunSP){
	global $db_host, $db_name, $db_user, $db_pwd;
	
	$GWDBLink = mysqli_connect($db_host,$db_user,$db_pwd, $db_name) or die(mysqli_error($DBLink));
	//mysql_select_db($db_name,$GWDBLink);
	
	$query 	= "SELECT
					A.TAHUN_SP2,
					A.KETETAPAN_SP2,
					B.WP_NAMA,
					B.SPPT_TAHUN_PAJAK
				FROM
					PBB_SPPT_PENAGIHAN A
				LEFT JOIN PBB_SPPT B ON A.NOP = B.NOP
				WHERE
					A.NOP = '".$nop."'
				AND B.SPPT_TAHUN_PAJAK IN ({$tahunSP})
				ORDER BY
					SPPT_TAHUN_PAJAK DESC LIMIT 1";
	// echo $query;
	// print_r($tahunSP);
	$res 	= mysqli_query($GWDBLink, $query);
	$data 	= array();
	$i		= 0;
	if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
			$data['TAHUN_SP2'][$i] 			= explode(',',$row["TAHUN_SP2"]);
			$data['WP_NAMA'][$i] 			= $row["WP_NAMA"];
			$data['KETETAPAN_SP2'][$i] 		= $row["KETETAPAN_SP2"];
			$i++;
        }
        return $data;
    }
}

$nop 		= $_REQUEST['nop'];
$db_host 	= $_REQUEST['dbhost'];
$db_name 	= $_REQUEST['dbname'];
$db_user 	= $_REQUEST['dbuser'];
$db_pwd 	= $_REQUEST['dbpwd'];

$dataTahunSP = getTahunSP2($_REQUEST['nop']);
if(empty($dataTahunSP)){
    generateError("Data tidak ditemukan!");
    exit;
}
$dataTahun = getDataTahun($_REQUEST['nop'],$dataTahunSP);

$response['r'] = true;
$response['errstr'] = "";
$response['dataTahun'] = $dataTahun;

$val = $json->encode($response);
echo $val;

function generateError($errorString=''){
	global $json;
	
	$response['r'] = false;
	$response['errstr'] = $errorString;
	$response['dataTahun'] = '';
	
	$val = $json->encode($response);
	echo $val;
	exit;
}

?>