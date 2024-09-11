<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue($key) {
    global $DBLink, $appID;
    $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
    mysqli_close();
}

$NOP_INFO = getConfigValue("NOP_INFO");
$NOP_VALIDASI = getConfigValue("NOP_VALIDASI");

function getNOPBPHTB($nop) {
    global $NOP_INFO, $NOP_VALIDASI;
    $Ok = false;

    $dbName =  getConfigValue("PBBGWDBNAME");
	$dbHost =  getConfigValue("PBBDBHOST");
	$dbPwd =   getConfigValue("PBBDBPASS");
	$dbUser =  getConfigValue("PBBDBUSER");

    $conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
   
    // mysql_select_db('GW_PBB');
	
    $respon = array();
    $respon['denied'] = 0; #diterima
    $respon['message'] = "";
    $thn = "";
	$year=date('Y');
	$year2=date('Y')-4;
	$year3=date('Y')-1;
	$thn_pajak_berlaku = getConfigValue("THN_PAJAK_BERLAKU");
	$thn_pajak_awal = getConfigValue("THN_PAJAK_AWAL");
	$thn_pajak_akhir = getConfigValue("THN_PAJAK_AKHIR");
	//$sql_pbb = "select PAYMENT_FLAG, SPPT_TAHUN_PAJAK from PBB_SPPT where NOP ='{$nop}' AND ( SPPT_TAHUN_PAJAK BETWEEN '{$thn_pajak_awal}' AND '{$thn_pajak_akhir}') AND (PAYMENT_FLAG <> 1 OR ISNULL(PAYMENT_FLAG)) ORDER BY SPPT_TAHUN_PAJAK DESC";
	$sql_pbb = "select * from pbb_sppt where NOP ='{$nop}' AND ( SPPT_TAHUN_PAJAK BETWEEN '{$thn_pajak_awal}' AND '{$thn_pajak_akhir}') ORDER BY SPPT_TAHUN_PAJAK DESC";
	// echo $sql_pbb;
    // exit;
	$res_pbb = mysqli_query($conn, $sql_pbb);
    if ($res_pbb === false) {
        echo $sql_pbb . "<br>";
        echo mysqli_error($LDBLink);
    }

	if (mysqli_num_rows($res_pbb)>=1) {

        while ($dt_pbb = mysqli_fetch_array($res_pbb)) {
            if ($dt_pbb['PAYMENT_FLAG'] <> 1 || $dt_pbb['PAYMENT_FLAG'] == NULL) {
                $respon['denied'] = 1; #ditolak
                $thn.= "{$dt_pbb['SPPT_TAHUN_PAJAK']},";
                $thn = substr($thn, 0, strlen($thn) - 1);
                //$respon['message'] = "NOP {$nop} tahun {$thn} belum melakukan pembayaran. " ;
                //$link= "<a href ='http://137.59.126.95:8090/portlet/portlet.php' target='_blank'>Cek Tagihan</a>";
                $respon['message'] = "NOP {$nop} pada tahun pajak {$thn_pajak_awal} s.d. {$thn_pajak_akhir} ada yang belum melakukan pembayaran. " ;        
                //$respon['message'] .= "<a href ='http://137.59.126.95:8090/portlet/portlet.php' target='_blank'>Cek Tagihan</a>";
            } 
            $found = true;
        }
	}
	else{
		$respon['message'] = "Tidak Ada Tunggakan.";
	}

    if ($NOP_INFO == 0)
        unset($respon['message']);
    if ($NOP_VALIDASI == 0)
        unset($respon['denied']);
    return $respon;
}

if (!isset($_POST['nop']))
    exit($json->encode(array()));
$nop = $_POST['nop'];
if ($NOP_INFO == 0 && $NOP_VALIDASI == 0)
    $res = array();
else
    $res = getNOPBPHTB($nop);

echo $json->encode($res);
exit;
?>
<!--
$tahun = array();
    $cekYear = array();
for($i=0;$i<6;$i++){
            $tahun[$i] = $year + $i;
        }
        print_r($tahun);
        //var_dump($result); 
            
        foreach($result as $key=>$value){
            $cekYear[$key] = $value->STATUS_PEMBAYARAN_SPPT;            
        }
        
        foreach($year as $value){
            foreach($cekYear as $isi){
                 if($value != $isi){
                    $respon['denied'] = 1; #ditolak
                    $thn .= "{$value},";
                    //$thn = substr($thn, 0, strlen($thn) - 1);
                
                 }
              }
            }
        }
        $found = true;
         if(!$respon['denied']){
              $respon['message'] = "NOP sudah dibayar.";
         }-->