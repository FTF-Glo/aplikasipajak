<?php 
ini_set("display_errors",1);
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'dashboard', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

function getConnect($query){
	$host = "192.168.30.2:7306";
	$user = "gw_user_devel";
	$pwd = "gw_pwd_devel";
	$db = "P_GATEWAY_DEVEL";
	SCANPayment_ConnectToDB($DBLink, $DBConn, $host, $user, $pwd, $db);
	$qu = mysqli_query($DBLink, $query) or die("no 34".mysqli_error($DBLink));
	$res = array();
	$n=0;
	$row = mysqli_fetch_assoc($qu);
	return $row['VAL'];
}
function getLastDay($bulan,$tahun){
	$lastday= date('d',strtotime('-1 second',strtotime('+1 month',strtotime(date($bulan).'/01/'.date($tahun).' 00:00:00'))));
	return $lastday;
}
function getxAxisDataDay(){
	$lastday = getLastDay(date('m'),date('Y'));
	$str="['";
	for($i=1; $i<=$lastday; $i++){
		if($i!=$lastday)
		$str.= $i."','" ;
		else
		$str.= $i ;
	}
	$str.="']";
	return $str;
}
function getxAxisData(){
	global $_REQ;
	$md = $_REQ['md'];
	if($md=='d'){
		$xAxisData = getxAxisDataDay();
	}else if($md=='m'){
		$xAxisData = "['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']";
	}
	return $xAxisData;
}
function getMonth($month){
	$cars=array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
	foreach($cars as $key => $value){
		if($month == ($key+1))
		return $value;
	}
} 
function getMD(){
	global $_REQ;
	$md = $_REQ['md'];
	if($md=='d'){
		$md = "Bulan ".getMonth(date('m'));
	}else if($md=='m'){
		$md = "Tahun ".date('Y');
	}
	return $md;
}
function explodeX($a){
	$a = explode("-",$a);
	return $a[1];
}
function getSQLData($i){
	global $_REQ;
	$md = $_REQ['md'];
	if($md=='d'){
		$where = " WHERE PAYMENT_FLAG='1' AND MONTH(PAYMENT_SETTLEMENT_DATE) = '".date('m')."' AND DAY(PAYMENT_SETTLEMENT_DATE) = '$i'";
		$groupBy = " GROUP BY day(DATE(PAYMENT_PAID)) ";
	}else if($md=='m'){
		$where = " WHERE PAYMENT_FLAG='1' AND YEAR(PAYMENT_SETTLEMENT_DATE) = '".date('Y')."' AND MONTH(PAYMENT_SETTLEMENT_DATE) = '$i'";
		$groupBy = " GROUP BY MONTH(DATE(PAYMENT_PAID)) ";
	}
	$query = "SELECT SUM(SPPT_PBB_HARUS_DIBAYAR) AS VAL FROM PBB_SPPT $where $groupBy  ORDER BY PAYMENT_PAID;"; //DATE(PAYMENT_PAID) DATE,
	//echo $query;
	$res = getConnect($query);
	return $res;
}
function getDataDay(){
	$lastday = getLastDay(date('m'),date('Y'));
	$str = "[";
	for($i=1; $i<=$lastday; $i++){
		if($i!=$lastday){
			if(getSQLData($i)!=0)
				$str .= getSQLData($i);
				else
				$str .="0";
		$str .= ", " ;
		}else{
		$str .= "0" ;
		}
	}
	$str.="]";
	return $str;
}
function getDataMonth(){
	$lastMonth = 12;
	$str = "[";
	for($i=1; $i<=$lastMonth; $i++){
		if($i!=$lastMonth){
			if(getSQLData($i)!=0)
				$str .= getSQLData($i);
				else
				$str .="0";
		$str .= ", " ;
		}else{
		$str .= "0" ;
		}
	}
	$str.="]";
	return $str;
}
function getData(){
	global $_REQ;
	$md = $_REQ['md'];
	if($md=='d'){
		$nameSeries = "Perhari";
		$data = getDataDay();
	}else if($md=='m'){
		$nameSeries = "Perbulan";
		$data = getDataMonth();
	}
	return $series = "[{name: '$nameSeries',data: $data}]";
}

$_REQ['md'] = 'm';
$res[0]['title'] = "Penerimaan PBB ".getMD(); //bulan ??? / tahun ???
$res[0]['subTitle'] = $_REQUEST['nmKota']; //Nama kota request dari configuration
$res[0]['series'] = getData(); //[{name: 'Yoko',data: [1, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0]}]
$res[0]['xAxisData'] = getxAxisData(); //['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
$res[0]['yAxisTitle'] = "Nilai Perolehan"; //Permanen

$_REQ['md'] = 'd';
$res[1]['title'] = "Penerimaan PBB ".getMD(); //bulan ??? / tahun ???
$res[1]['subTitle'] = $_REQUEST['nmKota']; //Nama kota request dari configuration
$res[1]['series'] = getData(); //[{name: 'Yoko',data: [1, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0]}]
$res[1]['xAxisData'] = getxAxisData(); //['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
$res[1]['yAxisTitle'] = "Nilai Perolehan"; //Permanen

//echo "[{name: 'namaField',data: [1, 2, 2, 3]}]";
$json = new Services_JSON();  echo $json->encode($res); 
?>