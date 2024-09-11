<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

require_once($sRootPath . "inc/payment/comm-central.php");


require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

$json = new Services_JSON();
$response = array();

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
// die(ONPAYS_DBPWD);
function getDataOp($nop = "")
{
    global $DBLink;

    $qwhere = "";
    if ($nop) {
        $qwhere = " WHERE CPM_NOP='$nop'";
    }

    $qry = "SELECT A.CPM_NOP, A.CPM_OP_ALAMAT, A.CPM_OP_NOMOR, A.CPM_OP_RT, A.CPM_OP_RW, A.CPM_OP_KELURAHAN, A.CPM_OP_KECAMATAN, 
			A.CPM_OP_KOTAKAB, A.CPM_WP_NAMA, A.CPM_WP_ALAMAT, A.CPM_WP_RT, A.CPM_WP_RW, A.CPM_WP_KELURAHAN, A.CPM_WP_KECAMATAN, A.CPM_WP_PROPINSI, A.CPM_WP_KOTAKAB, A.CPM_WP_NO_HP,
			B.CPC_TKL_KELURAHAN AS KELURAHAN_OP,
			C.CPC_TKC_KECAMATAN AS KECAMATAN_OP,
			D.CPC_TK_KABKOTA AS KABKOTA_OP,
			E.CPC_TP_PROPINSI AS PROPINSI_OP,
			SUBSTRING(A.CPM_OP_KOTAKAB,1,2) AS ID_PROPINSI_OP,
			A.CPM_WP_KELURAHAN AS KELURAHAN_WP,
			A.CPM_WP_KECAMATAN AS KECAMATAN_WP,
			A.CPM_WP_KOTAKAB AS KABKOTA_WP,
			A.CPM_WP_PROPINSI AS PROPINSI_WP,
			A.CPM_SPPT_THN_PENETAPAN, A.CPM_OT_JENIS,
			A.CPM_WP_NO_KTP
			FROM 
			(
			SELECT CPM_NOP, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_SPPT_THN_PENETAPAN,
			CPM_OP_KOTAKAB, CPM_WP_NAMA, CPM_WP_ALAMAT, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN, CPM_WP_PROPINSI, CPM_WP_KOTAKAB,CPM_WP_NO_HP,CPM_OT_JENIS, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_final
			" . $qwhere . "
			UNION ALL 
			SELECT CPM_NOP, CPM_OP_ALAMAT, CPM_OP_NOMOR, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_SPPT_THN_PENETAPAN,
			CPM_OP_KOTAKAB, CPM_WP_NAMA, CPM_WP_ALAMAT, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN, CPM_WP_PROPINSI, CPM_WP_KOTAKAB,CPM_WP_NO_HP,CPM_OT_JENIS, CPM_WP_NO_KTP FROM cppmod_pbb_sppt_susulan
			" . $qwhere . "
			) A 
			LEFT JOIN cppmod_tax_kelurahan B ON A.CPM_OP_KELURAHAN=B.CPC_TKL_ID
			LEFT JOIN cppmod_tax_kecamatan C ON C.CPC_TKC_ID=A.CPM_OP_KECAMATAN
			LEFT JOIN cppmod_tax_kabkota D ON D.CPC_TK_ID=A.CPM_OP_KOTAKAB
			LEFT JOIN cppmod_tax_propinsi E ON E.CPC_TP_ID=SUBSTRING(A.CPM_OP_KOTAKAB,1,2) ";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        // die($qry);
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {

            //            $queryPropinsi = "select * from cppmod_tax_propinsi";
            //            $dataPropinsi = mysql_query($queryPropinsi,$DBLink);
            $optionPropinsi = "";
            //            while($resPropinsi = mysqli_fetch_array($dataPropinsi)){
            //                $optionPropinsi .= ($resPropinsi['CPC_TP_ID'] == $row['ID_PROPINSI_WP'] )? 
            //                            "<option value='".$resPropinsi['CPC_TP_ID']."' selected>".$resPropinsi['CPC_TP_PROPINSI']."</option>" : 
            //                            "<option value='".$resPropinsi['CPC_TP_ID']."'>".$resPropinsi['CPC_TP_PROPINSI']."</option>";
            //            }
            // echo "<pre>";
            // print_r($row);
            // echo "</pre>";
            $tmp = array(
                'id' => $row['CPM_NOP'],
                'alamatOP' => $row['CPM_OP_ALAMAT'],
                'nomorOP' => $row['CPM_OP_NOMOR'],
                'rtOP' => $row['CPM_OP_RT'],
                'rwOP' => $row['CPM_OP_RW'],
                'idkelurahanOP' => $row['CPM_OP_KELURAHAN'],
                'idkecamatanOP' => $row['CPM_OP_KECAMATAN'],
                'idkabupatenOP' => $row['CPM_OP_KOTAKAB'],
                'idpropinsiOP' => $row['ID_PROPINSI_OP'],
                'kelurahanOP' => $row['KELURAHAN_OP'],
                'kecamatanOP' => $row['KECAMATAN_OP'],
                'kabupatenOP' => $row['KABKOTA_OP'],
                'propinsiOP' => $row['PROPINSI_OP'],
                'noKtpWP' => $row['CPM_WP_NO_KTP'],
                'namaWP' => $row['CPM_WP_NAMA'],
                'alamatWP' => $row['CPM_WP_ALAMAT'],
                'rtWP' => $row['CPM_WP_RT'],
                'rwWP' => $row['CPM_WP_RW'],
                'idkelurahanWP' => $row['CPM_WP_KELURAHAN'],
                'idkecamatanWP' => $row['CPM_WP_KECAMATAN'],
                'idkabupatenWP' => $row['CPM_WP_KOTAKAB'],
                'idpropinsiWP' => @$row['ID_PROPINSI_WP'],
                'kelurahanWP' => $row['KELURAHAN_WP'],
                'kecamatanWP' => $row['KECAMATAN_WP'],
                'kabupatenWP' => $row['KABKOTA_WP'],
                'propinsiWP' => $row['PROPINSI_WP'],
                'noHP' => $row['CPM_WP_NO_HP'],
                'optionPropinsi' => $optionPropinsi,
                'tahunPenetapan' => $row['CPM_SPPT_THN_PENETAPAN'],
                'JenisTanah' => $row['CPM_OT_JENIS']

            );
            $data = $tmp;
        }

        return $data;
    }
}

function getDataInProses($nop = "", $tahun)
{
    global $DBLink;
    $query = "SELECT CPM_OP_NUMBER, CPM_TYPE FROM cppmod_pbb_services WHERE CPM_OP_NUMBER='$nop' AND CPM_STATUS NOT IN ('4','5','6') AND CPM_SPPT_YEAR='" . $tahun . "'";
    $res = mysqli_query($DBLink, $query);
    if (!$res) {
        echo $query . "<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            $data = array();
            $tmp = array(
                'nop' => $row['CPM_OP_NUMBER'],
                'type' => $row['CPM_TYPE']
            );
            $data = $tmp;
        }
        return $data;
    }
}

function getDataTagihan($nop = '', $tahun = '', &$data = array(), &$error)
{
    global $db_host, $db_name, $db_user, $db_pwd;
    $error = 0;

    $data = array();

    $qry = "SELECT NOP, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR, PAYMENT_FLAG, PAYMENT_PAID FROM pbb_sppt " .
        "WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK = '$tahun' ";
    // echo $qry; exit;
    $GWDBLink = mysqli_connect($db_host, $db_user, $db_pwd, $db_name) or die(mysqli_error($this->DBLink));
    //mysql_select_db($db_name,$GWDBLink);
    $res = mysqli_query($GWDBLink, $qry);

    if (!$res) {
        $error = 1;
    } else {
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
    }
}

$jnsBerkas = $_REQUEST['jnsBerkas'];
$nop = $_REQUEST['nop'];
$tahun = $_REQUEST['tahun'];

$db_host = $_REQUEST['GW_DBHOST'];
$db_name = $_REQUEST['GW_DBNAME'];
$db_user = $_REQUEST['GW_DBUSER'];
$db_pwd = $_REQUEST['GW_DBPWD'];

if (@mysqli_num_rows($res) > 0) {
    generateError("Terjadi masalah sistem. Silahkan coba ulangi.");
    exit;
}
$dataOP = getDataOp($_REQUEST['nop']);
if (empty($dataOP)) {
    generateError("Data Objek Pajak tidak ditemukan.");
    exit;
}

if ($jnsBerkas == 5)
    $tahun = $dataOP['tahunPenetapan'];

$dataInProses = getDataInProses($_REQUEST['nop'], $tahun);
$type = array(
    1 => "OP Baru",
    2 => "Pemecahan",
    3 => "Penggabungan",
    4 => "Mutasi",
    5 => "Perubahan Data",
    6 => "Pembatalan",
    7 => "Salinan",
    8 => "Penghapusan",
    9 => "Pengurangan SPPT",
    10 => "Keberatan",
    11 => "Cetak SKNJOP",
    12 => "Pengurangan Denda",
);

$a = "aPBB";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig = $User->GetAppConfig($a);

if ($dataInProses && $jnsBerkas != '2') {
    generateError("Data sedang dalam proses " . $type[$dataInProses['type']] . ".");
    exit;
    // }else if($dataInProses && ($dataInProses['type'] != '2' && $jnsBerkas == '2')){
} else if ($dataInProses && ($jnsBerkas == '2')) {
    generateError("Data sedang dalam proses " . $type[$dataInProses['type']] . ".");
    exit;
}
function cekAdaTagihan($nop, $thnawal, $thnakhir)
{
    // SELECT * FROM PBB_SPPT WHERE nop = "360101000100100150" and 
    global $db_host, $db_name, $db_user, $db_pwd;


    $error = 0;

    $data = array();

    $qry = "SELECT SPPT_TAHUN_PAJAK FROM PBB_SPPT " .
        "WHERE NOP = '$nop' AND SPPT_TAHUN_PAJAK BETWEEN  '$thnawal' and '$thnakhir' and (PAYMENT_FLAG != 1 or PAYMENT_FLAG IS NULL )  ORDER BY SPPT_TAHUN_PAJAK ASC ";

    // echo $qry; exit;	
    $GWDBLink = mysqli_connect($db_host, $db_user, $db_pwd, $db_name) or die(mysqli_error($this->DBLink));
    //mysql_select_db($db_name,$GWDBLink);
    $res = mysqli_query($GWDBLink, $qry);
    $numrow = mysqli_num_rows($res);

    $thn = "";
    if ($numrow > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $thn .= $row['SPPT_TAHUN_PAJAK'] . ",";
            // return $thn;
            // exit(0);
        }
        // $thn = trim(str)
    } else {
        $thn = "";
    }
    return $thn;
}



$dataTagihan = array();
if ($dataOP['JenisTanah'] != 4) { #4:fasum, jika jenis tanahnya fasum (4) maka tidak perlu cek tagihan
    getDataTagihan($nop, $tahun, $dataTagihan, $error);


    // edited by 35utech 18 januari 2019
    $harusCek = $appConfig['CEK_LOKET'];
    $harusCek = json_decode($harusCek);


    if (in_array($jnsBerkas, $harusCek)) {
        if (cekAdaTagihan($nop, $appConfig['CEK_LOKET_TAHUN_AWAL'], $appConfig['tahun_tagihan']) != "") {
            generateError(" NOP $nop  belum melakukan pembayaran untuk tahun pajak " . cekAdaTagihan($nop, $appConfig['CEK_LOKET_TAHUN_AWAL'], $appConfig['tahun_tagihan']) . " Silahkan lakukan pembayaran terlebih dahulu");
            exit;
        }
    }

    // end edited by 35utech	

    if ($error == 1) {
        generateError("Terjadi masalah sistem. Silahkan coba ulangi.");
        exit;
    }

    if (!empty($dataTagihan)) {
        /*
		* Jenis Berkas : ('1' => 'OP Baru', '2'=>'Pemecahan', '3'=>'Penggabungan', '4'=>'Mutasi', '5'=>'Perubahan Data', '6'=>'Pembatalan, '7'=>'Salinan', '8'=>'Penghapusan', '9'=>'Pengurangan', '10'=>'Keberatan')
		* Berkas yang harus bayar dulu sebelum pelayanan adalah 
		*/
        // $arrHarusBayar = array('3');
        $arrHarusBayar = array(); // 04 Juli 2024 -> Permintaan Pemda Gabung harus bisa lolos walau belum bayar
        // $arrHarusBayar = array('2', '3', '7', '10'); // 25 april 2018
        $arrJanganBayar = array('9');

        if (in_array($jnsBerkas, $arrHarusBayar) && ($dataTagihan['status_pembayaran'] == null || $dataTagihan['status_pembayaran'] != 1)) {
            generateError("NOP " . $nop . " belum melakukan pembayaran untuk tahun pajak " . $tahun . ". Silahkan lakukan pembayaran terlebih dahulu");
            exit;
        } else if (in_array($jnsBerkas, $arrJanganBayar) && $dataTagihan['status_pembayaran'] == 1) {
            generateError("NOP " . $nop . " tidak bisa mengajukan permohonan pengurangan karena sudah melakukan pembayaran untuk tahun pajak " . $tahun . ".");
            exit;
        } else {
            if ($dataTagihan['tgl_pembayaran'] != null && trim($dataTagihan['tgl_pembayaran']) != '') {
                $dataTagihan['tgl_pembayaran'] = substr($dataTagihan['tgl_pembayaran'], 0, 10);
            }
        }
    } else {
        generateError("Tidak ditemukan SPPT untuk NOP " . $nop . " tahun pajak " . $tahun . ".");
        exit;
    }
}

$response['r'] = true;
$response['errstr'] = "";
$response['dataOP'] = $dataOP;
$response['dataTagihan'] = $dataTagihan;
// echo "<pre>";
// print_r($response); exit;

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
