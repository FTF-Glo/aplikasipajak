<?php
// error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

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

global $DBLink;

if (!$DBLink) {
    die("Koneksi database gagal: " . mysqli_DBLinkect_error());
}

// Periksa apakah ada data yang dikirimkan melalui POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mendapatkan data JSON dari request POST
    $json_data = file_get_contents("php://input");

    $json_data = json_decode($json_data, true);

// Mengambil nilai dari properti "data"
$data = json_decode($json_data['data'], true);

// Menyiapkan array untuk menyimpan nilai id
$id_values = array();

// Mendapatkan nilai "id" dari setiap objek dalam array
foreach ($data as $item) {
    $id_values[] = "'" . $item['id'] . "'";
    
}

// Mengonversi array nilai id menjadi string untuk klausa IN dalam SQL
$id_string = implode(",", $id_values);

    // Lakukan query update
    $sql = "UPDATE cppmod_ssb_doc SET CPM_STATUS_NTPD = 1 WHERE CPM_SSB_ID IN ($id_string)";
    if (mysqli_query($DBLink, $sql)) {
 
        echo "success";
    } else {

        echo "error";
    }
} else {
  
    echo "error";
}

// Tutup koneksi database
mysqli_close($DBLink);
?>
