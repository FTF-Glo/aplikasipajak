<?php
// Koneksi ke database Anda
$dbName = '9pajak';
$dbHost = 'localhost';
$dbPwd = 'Balam_2@22';
$dbUser = 'pbb_balam';

$DBLink = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);

if (!$DBLink) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil nilai yang dipilih dari permintaan POST
$selectedValue = $_POST['selectedValue'];

// Query untuk mengambil data dari database berdasarkan nilai yang dipilih
$query = "SELECT * FROM patda_reklame_doc doc inner join patda_reklame_doc_atr atr ON atr.CPM_ATR_REKLAME_ID = doc.CPM_ID WHERE doc.CPM_NO = '$selectedValue'";

$result = mysqli_query($DBLink, $query);
if ($result) {
    // $data = mysqli_fetch_assoc($result);
    $data = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }


    // Mengirim data sebagai respons JSON
    header('Content-Type: application/json');

    echo json_encode($data);
} else {
    // Handle kesalahan jika query gagal
    echo json_encode(['error' => 'Gagal mengambil data.']);
}

// Tutup koneksi database
mysqli_close($DBLink);
