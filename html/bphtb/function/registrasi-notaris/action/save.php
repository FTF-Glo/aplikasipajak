<?php

// Langkah 1: Koneksi ke Database
$servername = "localhost";
$username = "root"; // Sesuaikan dengan username database Anda
$password = "pesawaran2@24"; // Sesuaikan dengan password database Anda
$dbname = "sw_ssb"; // Sesuaikan dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Langkah 2: Buat Fungsi untuk Menyimpan Data
function simpanData($id, $nama, $email, $notelp, $alamat, $kota, $conn)
{
    $sql = "UPDATE tbl_reg_user_notaris SET nm_lengkap=?, email=?, no_tlp=?, almt_jalan=?, almt_kota=? WHERE id=?";

    // Mempersiapkan dan mengikat
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nama, $email, $notelp, $alamat, $kota, $id);



    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }

    $stmt->close();
}

// Langkah 4: Panggil Fungsi Simpan dari Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $id = $_POST['id'];
    $nama = $_POST['namalk'];
    $email = $_POST['email'];
    $notelp = $_POST['notelp'];
    $alamat = $_POST['alamat'];
    $kota = $_POST['kota'];
    // var_dump($_POST);
    // die;
    // Panggil fungsi simpan
    if (simpanData($id, $nama, $email, $notelp, $alamat, $kota, $conn)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

// Menutup koneksi
$conn->close();
