<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

$json = new Services_JSON();

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getNoPel() {
    global $DBLink;
    $type = $_POST['type'];
    $nomor = "1";
    $arrJnsPerolehan = array(1=>"01",2=>"02",3=>"03",4=>"04",5=>"05",6=>"06",7=>"07",8=>"08",9=>"09",10=>"10",11=>"11",12=>"12",13=>"13",14=>"14",21=>"21",22=>"22", 30=>"30", 31=>"31", 32=>"32", 33=>"33");
    $jnsPerolehan = $arrJnsPerolehan[$type];
    $tahun = date("Y");
    
    $qry = "select * from cppmod_ssb_berkas WHERE CPM_BERKAS_JNS_PEROLEHAN = '{$type}'
            and DATE_FORMAT(STR_TO_DATE(CPM_BERKAS_TANGGAL,'%d-%m-%Y'),'%Y') ='{$tahun}' 
                order by CPM_BERKAS_ID DESC limit 0,1";
    $res = mysqli_query($DBLink, $qry);
    
    if ($row = mysqli_fetch_array($res)) {
		$nomor_exp = explode(".",$row['CPM_BERKAS_NOPEL']);
        $nomor = (int) $nomor_exp[2];
        $nomor ++;
        
    }    
    $nomor = str_pad($nomor, 5,"0",STR_PAD_LEFT);
	if($type!="0"){
			$noPel = "{$tahun}.{$jnsPerolehan}.{$nomor}";
	}else{
			$noPel = "";
		
	}
    return $noPel;    
}

$val = getNoPel();
echo trim($val);
?>