<?php
// Koneksi database
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "../../../../inc/payment/inc-payment-db-c.php");
$conn = mysqli_connect(ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);

if (isset($_POST['id'])) {
    $jenis_retribusi_id = $_POST['id'];
    // var_dump($jenis_retribusi_id);
    $sql = "SELECT rekening, jenis_penerimaan, anggaran, target FROM rekening_retribusi WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $jenis_retribusi_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
}
?>
