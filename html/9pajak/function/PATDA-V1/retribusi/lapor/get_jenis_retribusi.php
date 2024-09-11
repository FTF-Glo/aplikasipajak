<?php
// Koneksi database
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "../../../../inc/payment/inc-payment-db-c.php");

$conn = mysqli_connect(ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if (isset($_POST['id'])) {
    $jenis_penerimaan_id = $_POST['id'];
    $sql = "SELECT id, nama_pendapatan FROM rekening_retribusi WHERE jenis_penerimaan = '$jenis_penerimaan_id'";
    
    $result = mysqli_query($conn, $sql);
    // var_dump( $conn);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='{$row['id']}'>{$row['nama_pendapatan']}</option>";
        }
    } else {
        echo "<option value=''>Tidak ada retribusi yang tersedia</option>";
    }
}
?>
