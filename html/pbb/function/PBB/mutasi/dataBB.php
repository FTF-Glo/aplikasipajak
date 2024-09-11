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

$nopInduk = $_REQUEST['nopInduk'];
$nop = $_REQUEST['nop'];
$thn = $_REQUEST['thn'];
$almt = $_REQUEST['almt'];
$db_host = $_REQUEST['GW_DBHOST'];
$db_port = $_REQUEST['GW_DBPORT'];
$db_name = $_REQUEST['GW_DBNAME'];
$db_user = $_REQUEST['GW_DBUSER'];
$db_pwd = $_REQUEST['GW_DBPWD'];

function getDataTagihan($nop = '', $thn = '')
{
    global $db_host, $db_name, $db_user, $db_pwd, $db_port;

    $qry = "SELECT NOP, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, PAYMENT_FLAG, PAYMENT_PAID FROM PBB_SPPT WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$thn' ";
    $GWDBLink = mysqli_connect($db_host, $db_user, $db_pwd, $db_name, $db_port) or die(mysqli_error($GWDBLink));
    //mysql_select_db($db_name,$GWDBLink);
    $res = mysqli_query($GWDBLink, $qry);

    if (!$res) {
        generateError(mysqli_error($GWDBLink));
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {

            $tmp = array(
                'nop' => $row['NOP'],
                'tahun' => $row['SPPT_TAHUN_PAJAK'],
                'tagihan' => $row['SPPT_PBB_HARUS_DIBAYAR'],
                'status_pembayaran' => $row['PAYMENT_FLAG'],
                'tgl_pembayaran' => $row['PAYMENT_PAID']
            );
            $data = $tmp;
        }
        return $data;
    }
}

function getDataBumi($nop = "")
{
    global $DBLink;

    $qwhere = "";
    if ($nop) {
        $qwhere = " WHERE CPM_NOP='$nop'";
    }
    $qry = "SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_LUAS_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_OP_ALAMAT FROM cppmod_pbb_sppt_final " . $qwhere;
    $qry .= " UNION ALL ";
    $qry .= "SELECT CPM_NOP, CPM_WP_NAMA, CPM_OP_LUAS_TANAH, CPM_OP_LUAS_BANGUNAN, CPM_OP_ALAMAT FROM cppmod_pbb_sppt_susulan " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        generateError(mysqli_error($DBLink));
    } else {
        /*  if (mysqli_num_rows($res) == 0) {
            generateError("Data NOP tidak ditemukan di SPPT");
        } else { */
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'nop' => $row['CPM_NOP'],
                'namaWP' => $row['CPM_WP_NAMA'],
                'luas' => $row['CPM_OP_LUAS_TANAH'],
                'luasBangun' => $row['CPM_OP_LUAS_BANGUNAN'],
                'alamat' => $row['CPM_OP_ALAMAT']
            );
            $data = $tmp;
        }
        return $data;
        /* } */
    }
}

function getDataBangun($nop = "")
{
    global $DBLink;
    $arrJenis = array(
        1 => "Perumahan",
        2 => "Perkantoran Swasta",
        3 => "Pabrik",
        4 => "Toko/Apotik/Pasar/Ruko",
        5 => "Rumah Sakit/Klinik",
        6 => "Olah Raga/Rekreasi",
        7 => "Hotel/Wisma",
        8 => "Bengkel/Gudang/Pertanian",
        9 => "Gedung Pemerintah",
        10 => "Lain-lain",
        11 => "Bng Tidak Kena Pajak",
        12 => "Bangunan Parkir",
        13 => "Apartemen",
        14 => "Pompa Bensin",
        15 => "Tangki Minyak",
        16 => "Gedung Sekolah"
    );

    $qwhere = "";
    if ($nop) {
        $qwhere = " WHERE CPM_NOP='$nop'";
    }
    $qry = "SELECT * 
            FROM cppmod_pbb_sppt_final F
            JOIN cppmod_pbb_sppt_ext E ON (F.CPM_SPPT_DOC_ID = E.CPM_SPPT_DOC_ID) " . $qwhere . "
            ORDER BY CPM_OP_NUM";
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        generateError(mysqli_error($DBLink));
    } else {
        $data = array();
        if (mysqli_num_rows($res) == 0) {
            $data['nobgn'] = "Data bangunan tidak ditemukan";
            return $data;
        } else {
            while ($row = mysqli_fetch_assoc($res)) {
                $tmp = array(
                    'luas' => $row['CPM_OP_LUAS_BANGUNAN'],
                    'jenis' => $arrJenis[$row['cpm_op_penggunaan']]
                );
                $data[] = $tmp;
            }
            return $data;
        }
    }
}

//$dataBangun = getDataBangun($_REQUEST['nop']);
$dataBumi = getDataBumi($nop);
$dataTagihan = getDataTagihan($nop, $thn);

if (!empty($dataTagihan)) {
    // if ($dataTagihan['status_pembayaran'] == null || $dataTagihan['status_pembayaran'] != 1) {
    if ($dataTagihan['status_pembayaran'] == null ) { // ini permintaan -> belum bayar miinta tetep lolos utk di gabungin
        generateError("NOP " . $nop . " belum melakukan pembayaran untuk tahun pajak " . $thn . ". Silahkan lakukan pembayaran terlebih dahulu");
        exit;
    } else
	if (substr($nopInduk, 0, 10) != substr($dataBumi['nop'], 0, 10)) {
        generateError("NOP " . $nop . " tidak bisa melakukan penggabungan. Alamat tidak sama dengan alamat NOP induk.\n");
        exit;
    }
}

$response['r'] = true;
$response['errstr'] = "";
$response['dataBumi'] = $dataBumi;
$response['dataTagihan'] = $dataTagihan;
//$response['dataBangun'] = $dataBangun;

$val = $json->encode($response);
echo $val;

function generateError($errorString = '')
{
    global $json;

    $response['r'] = false;
    $response['errstr'] = $errorString;

    $val = $json->encode($response);
    echo $val;
    exit;
}
