<?php
function data_atr($selectedValue) {
    // Koneksi ke database Anda
    $dbName = '9pajak';
    $dbHost = 'localhost';
    $dbPwd = 'Balam_2@22';
    $dbUser = 'pbb_balam';

    $DBLink = mysqli_connect($dbHost, $dbUser, $dbPwd, $dbName);

    if (!$DBLink) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }

    // Query untuk mengambil data dari database berdasarkan nilai yang dipilih
    $query = "SELECT * FROM patda_reklame_doc doc inner join patda_reklame_doc_atr atr ON atr.CPM_ATR_REKLAME_ID = doc.CPM_ID WHERE doc.CPM_NO = '$selectedValue'";

    $result = mysqli_query($DBLink, $query);

    if ($result) {
        $data = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        // Tutup koneksi database
        mysqli_close($DBLink);

        return $data;
    } else {
        // Handle kesalahan jika query gagal
        mysqli_close($DBLink);
        return ['error' => 'Gagal mengambil data.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedValue = $_POST['selectedValue'];
    $data = fetchDataFromDatabase($selectedValue);

    // Mengirim data sebagai respons JSON
    header('Content-Type: application/json');
    echo json_encode($data);
}
