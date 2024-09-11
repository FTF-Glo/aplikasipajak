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
function konekDB($host,$user,$pass,$db,&$con){
	$con=mysqli_connect($host,$user,$pass, $db);
	//mysql_select_db($db,$con);
}

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
  // define("LOG_FILENAME","errorngaco");
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

function kecamatanCB(){// mangambil data kecamatan
    global $DBLink;
  $sql="select OP_KECAMATAN_KODE, OP_KECAMATAN from cppmod_pbb_sppt_current where OP_KECAMATAN_KODE<>'' and OP_KECAMATAN<>'' GROUP BY OP_KECAMATAN_KODE order by OP_KECAMATAN";

  $query = mysqli_query($DBLink, $sql) or die(mysqli_error($DBLink));
	$kec[0]['label']="Semua"; 
	$kec[0]['data']=0; 
  $i=1;
  while($r=mysqli_fetch_array($query)){ 
	$kec[$i]['label']=$r['OP_KECAMATAN']; 
	$kec[$i]['data']=$r['OP_KECAMATAN_KODE']; 
	$i++;
  } 
  return $kec;
}

function kelurahanCB(){// mangambil data kelurahan 
    global $DBLink;
	 $sql=" select OP_KELURAHAN_KODE, OP_KELURAHAN from cppmod_pbb_sppt_current where OP_KELURAHAN_KODE<>'' and OP_KELURAHAN<>'' ";
	 if ($_REQUEST['kec_id']!=0)  $sql.=" and OP_KECAMATAN_KODE='".$_REQUEST['kec_id']."' ";
	 $sql .= "GROUP BY OP_KELURAHAN_KODE order by OP_KELURAHAN";
	$query = mysqli_query($DBLink, $sql) or die(mysqli_error($DBLink));
	$kel[0]['label']="Semua"; 
	$kel[0]['data']=0; 
    $i=1;
	while( $r=mysqli_fetch_array($query) ){
	  $kel[$i]['label']=$r['OP_KELURAHAN']; 
	  $kel[$i]['data']=$r['OP_KELURAHAN_KODE']; 
	  $i++;
	} 
	return $kel;
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
  echo base64_encode($json->encode($res));
}
enJsonData();
?>