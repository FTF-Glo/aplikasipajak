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
    $Ok = false;

    $conn = mysql_connect('192.168.0.37:3306', 'gw_user', 'gw_pwd');
    mysql_select_db('GW_PBB_PALEMBANG');

    $respon = array();
    $respon['denied'] = 0; #diterima
    $respon['message'] = "";
    $thn = "";

    $sql_pbb = "select PAYMENT_FLAG, SPPT_TAHUN_PAJAK from PBB_SPPT where NOP ='{$nop}'";
    $res_pbb = mysqli_query($conn, $sql_pbb);
    if ($dt_pbb = mysqli_fetch_array($res_pbb)) {

        if ($dt_pbb['PAYMENT_FLAG'] == 0) {
            $respon['denied'] = 1; #ditolak
            $thn.= "{$dt_pbb['SPPT_TAHUN_PAJAK']},";
            $thn = substr($thn, 0, strlen($thn) - 1);
            $respon['message'] = "NOP {$nop} tahun {$thn} belum melakukan pembayaran.";
        } else {
            $respon['message'] = "NOP sudah dibayar.";
        }
        $found = true;
    } else {
        $respon['message'] = "NOP {$nop} tidak ditemukan.";
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
