<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
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
    mysql_close($DBLink);
}

$NOP_INFO = getConfigValue("NOP_INFO");
$NOP_VALIDASI = getConfigValue("NOP_VALIDASI");

function getNOPBPHTB($nop) {
    global $NOP_INFO, $NOP_VALIDASI;
	$dbName = getConfigValue('BPHTBDBNAME');
	$dbHost = getConfigValue('BPHTBHOSTPORT');
	$dbPwd = getConfigValue('BPHTBPASSWORD');
	$dbTable = getConfigValue('BPHTBTABLE');
	$dbUser = getConfigValue('BPHTBUSERNAME');
    $Ok = false;

//    $conn = mysql_connect('127.0.0.1', 'sw_user', 'sw_pwd',true);
//    mysql_select_db('VSI_SWITCHER_DEVEL');
    $conn = mysql_connect($dbHost, $dbUser, $dbPwd,true);
    mysql_select_db($dbName);
    $respon = array();
    $respon['denied'] = 0; #diterima
    $respon['message'] = "";
    $thn = "";

    $sql_pbb = "SELECT
	A.*,B.CPM_OP_THN_PEROLEH  from ssb A left join SW_SSB.cppmod_ssb_doc B ON A.id_switching=B.CPM_SSB_ID
WHERE
A.op_nomor = '{$nop}'";
//echo $sql_pbb;
    $res_pbb = mysqli_query($conn, $sql_pbb);
//    $row=mysql_fetch_array($res_pbb);
//    $rows=mysql_num_rows($res_pbb);
//    if ($rows== 0) { 
//	$respon['message'] = "NOP {$nop} sudah pernah terpakai dan masih dalam tahap proses.";
//	
//}
//    if(mysql_num_rows($res_pbb,$conn)<=0){
//        $respon['message'] = "NOP {$nop} belum melakukan pembayaran.";
//    }
    if ($dt_pbb = mysqli_fetch_array($res_pbb)) {

        if ($dt_pbb['payment_flag'] == 0) {
            $respon['denied'] = 1; #ditolak
            $thn.= "{$dt_pbb['CPM_OP_THN_PEROLEH']},";
            $thn = substr($thn, 0, strlen($thn) - 1);
            $respon['message'] = "NOP {$nop} tahun {$thn} belum melakukan pembayarannnnnnnnn.";
        } else {
            $respon['message'] = "NOP sudah dibayar.";
        }
        $found = true;
    } else {
        $respon['message'] = "NOP {$nop} belum terdaftar di BPHTB.";
        $respon['denied'] = 1; #ditolak
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
