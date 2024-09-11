<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

$json = new Services_JSON();

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getDataOp() {
    global $DBLink;
    $nop = $_REQUEST['nop'];
    $qry = "select * from cppmod_ssb_berkas WHERE CPM_BERKAS_NOP = '{$nop}'";
    $res = mysqli_query($DBLink, $qry);
    if ($row = mysqli_fetch_array($res)) {
        return array();
    }
    
    $qry = "select a.* from cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID
            where
            b.CPM_TRAN_STATUS='2' and
            b.CPM_TRAN_FLAG='0' and
            a.CPM_OP_NOMOR='{$nop}'";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $result = array();
        if ($row = mysqli_fetch_array($res)) {
            $result = array(
                "id" => $row["CPM_SSB_ID"],
                "alamatOp" => $row["CPM_OP_LETAK"],
                "kelurahanOp" => $row["CPM_OP_KELURAHAN"],
                "kecamatanOp" => $row["CPM_OP_KECAMATAN"],
                "alamatWp" => $row["CPM_WP_ALAMAT"],
                "namaWp" => $row["CPM_WP_NAMA"],
                "npwp" => $row["CPM_WP_NPWP"],
                "dibayar" => $row["CPM_OP_BPHTB_TU"]                
            );
        }
        return $result;
    }
}

$dataOP = getDataOp();

$val = $json->encode($dataOP);
echo $val;
?>