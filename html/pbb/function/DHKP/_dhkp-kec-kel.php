<?php 
ini_set("display_errors",1);
$sRootPath = str_replace('\\', '/', str_replace('/function/DHKP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/report/eng-report-table.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
//function konekDB($host,$user,$pass,$db,&$con){
function konekDB($host,$user,$pass,$db,$con=""){
  $con=mysqli_connect($host,$user,$pass,$db);
  return $con;
	//mysql_select_db($db,$con);
}
function kecamatanCB(){// mangambil data kecamatan
  $host="127.0.0.1:3306";
  $user="sw_user";
  $pass="sw_pwd";
  $db="VSI_SWITCHER_DEVEL";
  $con = konekDB($host,$user,$pass,$db,$con);
  $sql="select OP_KECAMATAN_KODE, OP_KECAMATAN from cppmod_pbb_sppt_current where OP_KECAMATAN_KODE<>'' and OP_KECAMATAN<>'' GROUP BY OP_KECAMATAN_KODE order by OP_KECAMATAN";
  $query = mysqli_query($con, $sql) or die(mysqli_error($con));
	$kec[0]['label']="Semua"; 
	$kec[0]['data']=0; 
  $i=1;
  while($r=mysqli_fetch_array($query)){ 
	$kec[$i]['label']=$r['OP_KECAMATAN']; 
	$kec[$i]['data']=$r['OP_KECAMATAN_KODE']; 
	$i++;
  } 
  mysqli_close($con);
  return $kec;
  /*$json = new Services_JSON();
  echo base64_encode($json->encode($kec));*/
}

function kelurahanCB(){// mangambil data kelurahan 
  $host="127.0.0.1:3306";
  $user="sw_user";
  $pass="sw_pwd";
  $db="VSI_SWITCHER_DEVEL";
  $con = konekDB($host,$user,$pass,$db,$con);
	 $sql=" select OP_KELURAHAN_KODE, OP_KELURAHAN from cppmod_pbb_sppt_current where OP_KELURAHAN_KODE<>'' and OP_KELURAHAN<>'' ";
	 if ($_REQUEST['kec_id']!=0)  $sql.=" and OP_KECAMATAN_KODE='".$_REQUEST['kec_id']."' ";
	 $sql .= "GROUP BY OP_KELURAHAN_KODE order by OP_KELURAHAN";
	$query = mysqli_query($con, $sql) or die(mysqli_error($con));
	$kel[0]['label']="Semua"; 
	$kel[0]['data']=0; 
    $i=1;
	while( $r=mysqli_fetch_array($query) ){
	  $kel[$i]['label']=$r['OP_KELURAHAN']; 
	  $kel[$i]['data']=$r['OP_KELURAHAN_KODE']; 
	  $i++;
	} 
	mysqli_close($con);
	return $kel;
  /*$json = new Services_JSON();
  echo base64_encode($json->encode($kel));*/
}
function enJsonData(){
  $req=$_REQUEST['q'];
	  $res['REQ']=$req;
  if($req=="kecamatan"){
	  $res['DATA']=kecamatanCB();
  } else if($req=="kelurahan"){
	  $res['DATA']=kelurahanCB();
  }
  $json = new Services_JSON();
  //echo"<pre>"; print_r($res); echo"</pre>";
  echo base64_encode($json->encode($res));
}
enJsonData();
?>