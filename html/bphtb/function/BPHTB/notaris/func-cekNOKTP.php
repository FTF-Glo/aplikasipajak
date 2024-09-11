<?php

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
}
// print_r($row_config['CTR_AC_VALUE']);exit;
//$NOP_INFO = getConfigValue("NOP_INFO");
$NOP_VALIDASI = getConfigValue("NOP_VALIDASI");
$THN_PAJAK_BERLAKU = getConfigValue("THN_PAJAK_BERLAKU");

if ($NOP_VALIDASI == 0){
    echo "exit";exit;
}
	$dbName =  getConfigValue("PBBDBNAME");
	$dbHost =  getConfigValue("PBBDBHOST");
	$dbPwd =   getConfigValue("PBBDBPASS");
	$dbUser =  getConfigValue("PBBDBUSER");
        //$dbTable = getConfigValue('PBBDBTABLE');

// echo $dbName . ' ' . $dbHost . ' ' .$dbPwd . ' ' .$dbUser . '<br>';exit();
//$conn = mysql_connect($dbHost, $dbUser, $dbPwd);
//$conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
$conn = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);
if ($conn) {
    // Akses Basis Data
    $qry_config="select CTR_AC_VALUE from sw_pbb.CENTRAL_APP_CONFIG where CTR_AC_AID='aPBB' and CTR_AC_KEY='tahun_tagihan'";
    $result_config=mysqli_query($conn, $qry_config);
    $row_config=mysqli_fetch_assoc($result_config);
    $db="";
    if ($row_config['CTR_AC_VALUE']== $THN_PAJAK_BERLAKU){
        $db="CPPMOD_PBB_SPPT_CURRENT";
    }else{
        $db="CPPMOD_PBB_SPPT_CETAK_$THN_PAJAK_BERLAKU";
    }
    $ktp = $_GET['noktp'];
    // var_dump($ktp);die;
    
    $query = "SELECT CPM_WP_NAMA,CPM_WP_ALAMAT,CPM_WP_NPWP,CPM_WP_RT,CPM_WP_RW,CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,CPM_WP_KABUPATEN,CPM_WP_KODEPOS
            FROM sw_ssb.cppmod_ssb_doc
            WHERE CPM_WP_NOKTP='{$ktp}' LIMIT 0,1;";
    // print_r($query);exit;
    // print_r($query);exit;
    $result = mysqli_query($conn, $query);
    
	if ($result->num_rows > 0) {
        // Data KTP ditemukan, kirim kembali data dalam format JSON
        $row = $result->fetch_assoc();
        $data = array(
            'success' => true,
            'name' => $row['CPM_WP_NAMA'],
            'npwp' => $row['CPM_WP_NPWP'],
            'address' => $row['CPM_WP_ALAMAT'],
            'rt' => $row['CPM_WP_RT'],
            'rw' => $row['CPM_WP_RW'],
            'kelurahan' => $row['CPM_WP_KELURAHAN'],
            'kecamatan' => $row['CPM_WP_KECAMATAN'],
            'kabupaten' => $row['CPM_WP_KABUPATEN'],
            'kodepos' => $row['CPM_WP_KODEPOS'],
        );
        echo json_encode($data);
    } else {
        // Data KTP tidak ditemukan
        $data = array(
            'success' => false
        );
        echo json_encode($data);
    }
    

} else {
    echo "Koneksi Database Gagal" . mysqli_error($conn);
}
?>
