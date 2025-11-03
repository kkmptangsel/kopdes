<?php
// /koperasi/admin/proses_pengaturan.php
include '../config.php';
session_start(); // <<< PASTIKAN SESSION DIMULAI

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id = (int)$_POST['id']; // Harus selalu 1
    $nama_koperasi = mysqli_real_escape_string($koneksi, $_POST['nama_koperasi']);
    $no_induk_koperasi = mysqli_real_escape_string($koneksi, $_POST['no_induk_koperasi']);
    $sk_ahu = mysqli_real_escape_string($koneksi, $_POST['sk_ahu']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $external_url = mysqli_real_escape_string($koneksi, $_POST['external_url']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    // Query untuk UPDATE data. Kita hanya perlu WHERE id=1
    $query = "UPDATE pengaturan SET 
                nama_koperasi = '$nama_koperasi',
                no_induk_koperasi = '$no_induk_koperasi',
                sk_ahu = '$sk_ahu',
                no_telp = '$no_telp',
                email = '$email',
                external_url = '$external_url',
                alamat = '$alamat'
              WHERE id = $id";
              
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // Redirect sukses (Menggunakan Session untuk Snackbar) 🎯
        $_SESSION['notif_sukses'] = "Pengaturan berhasil diperbarui.";
        header("location:pengaturan.php"); // Redirect ke URL bersih
    } else {
        // Redirect gagal (Tetap menggunakan parameter URL atau Session flash error)
        $_SESSION['notif_error'] = "Gagal menyimpan pengaturan."; // Menggunakan Session Error jika Anda mau
        header("location:pengaturan.php?pesan=update_gagal"); 
    }

} else {
    header("location:pengaturan.php");
}
exit;
?>