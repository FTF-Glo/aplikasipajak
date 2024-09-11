<?php

$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "status-bayar";
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul. DIRECTORY_SEPARATOR . $submodul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/FusionCharts/FusionCharts.php");
require_once($sRootPath . "inc/{$DIR}/dbMonitoring.php");
require_once($sRootPath . "function/{$DIR}/class-pajak.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$setting = new SCANCentralSetting(0, LOG_FILENAME, $DBLink);

$thn = @isset($_REQUEST['th']) ? $_REQUEST['th'] : 1;
$jns = @isset($_REQUEST['jns']) ? $_REQUEST['jns'] : 1;


$q = base64_decode($_REQUEST['q']);

$j = $json->decode($q);
$uid = $j->uid;
$area = $j->a;
$moduleIds = $j->m;
#print_r($j);

$pjk = new Pajak();
$arr_config = $pjk->get_config_value($area);

$dbname = $arr_config['PATDA_DBNAME'];
$host = $arr_config['PATDA_HOSTPORT'];
$pass = $arr_config['PATDA_PASSWORD'];
$user = $arr_config['PATDA_USERNAME'];
$port = "";


$arrWhere = array();

if ($jns != "" and $jns !="All"){
    array_push($arrWhere, "simpatda_type = '{$jns}'");
}    

if($jns == 'All'){
    $pajak ='Semua';
}else{
    $pajak = '';
}    
if ($thn != "" and $thn!='All'){
    array_push($arrWhere, "simpatda_tahun_pajak='{$thn}'");
}
if($thn=='All'){
    $tahun = date('Y'); 
    $tmp = $tahun-8;
    array_push($arrWhere, "simpatda_tahun_pajak <='{$tahun}' and simpatda_tahun_pajak >='{$tmp}'");
}
    
array_push($arrWhere, "payment_flag = 1");
$where = implode(" AND ", $arrWhere);

if($thn !='All'){
   $where .= " group by simpatda_bulan_pajak";
}else{
  
   $where .= " group by simpatda_tahun_pajak"; 
}
$where2 = implode(" AND ", $arrWhere);
if($thn !='All'){
    $where2 .= " group by simpatda_bulan_pajak) x";
}else{
    $where2 .= " group by simpatda_tahun_pajak) x";
}
#echo $jns;
if (stillInSession($DBLink, $json, $sdata)) {

    $monSimpatda = new dbMonitoring($host, $port, $user, $pass, $dbname);
    $monSimpatda->setStatus("1");
    $monSimpatda->setConnectToMysql();  
    $monSimpatda->query("select jenis"); 
    $monSimpatda->setTable("SIMPATDA_TYPE");
    $monSimpatda->setWhere("id ='{$jns}'");
    $type = $monSimpatda->QueryType();
    if($thn !='All'){
     $monSimpatda->query("select simpatda_tahun_pajak,simpatda_bulan_pajak,sum(simpatda_dibayar) as simpatda_dibayar");
    }else{
     $monSimpatda->query("select simpatda_tahun_pajak,sum(simpatda_dibayar) as simpatda_dibayar");   
    }
    
    $monSimpatda->setTable("SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id ");
    $monSimpatda->setWhere($where);
       
    $path = $monSimpatda->Grafik($type,$thn);
    #echo $path;exit;
    $monSimpatda->query("select max(simpatda_dibayar) as bayar from ( select sum(simpatda_dibayar) as simpatda_dibayar");
    $monSimpatda->setTable("SIMPATDA_GW a inner join SIMPATDA_TYPE b on a.simpatda_type = b.id");
    $monSimpatda->setWhere($where2);
    $keterangan = $monSimpatda->KetranganGrafik(); 
    ob_clean();
    echo  "<div style=''>".renderChart("inc/FusionCharts/FCF_Column3D.swf", '',$path , "Laporan", 1000, 300)."</div>";
   if ($keterangan != 'kosong') {
        echo '<div style=" border:1px #fffff solid;background:#FFFFFF;width:140px; height:280px; padding:10px;margin-top:10px;margin-top:-300px;margin-right:100px;float:right">
         <div style="margin-top:100px;padding-top:10px; padding-left:10px;height:80px;"><emb><strong>'.$pajak.' Pajak '.$type.'</strong></emb><br/><br/>
         <div style=" border:1px #000000 solid;padding-top:10px; padding-left:10px; padding-bottom:10px;">' . $keterangan . '</div></div></div>';
    }
}else {
    echo "Inquiry Gagal waktu akses telah habis silahkan refresh browser anda !\n";
}
?>
