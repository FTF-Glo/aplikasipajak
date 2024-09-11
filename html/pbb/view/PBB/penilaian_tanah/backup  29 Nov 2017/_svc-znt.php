<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$json = new Services_JSON();
$response = array();

function getDataZNT($kel = "") {
    global $DBLink;
    $qwhere = "";
    if ($nop) {
        $qwhere = " WHERE CPM_KODE_LOKASI='$nop'";
    }
    $qry = "SELECT * FROM cppmod_pbb_znt " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        generateError(mysqli_error($DBLink));
    } else {
            $data = array();
            while ($row = mysqli_fetch_assoc($res)) {
                $tmp = array(
                    'kode_lokasi' => $row['CPM_KODE_LOKASI'],
                );
                $data = $tmp;
            }
            return $data;
    }
}

$dataZNT = getDataZNT($_REQUEST['kel']);

$response['r'] = true;
$response['errstr'] = "";
$response['dataZNT'] = $dataZNT;

$val = $json->encode($response);
echo $val;