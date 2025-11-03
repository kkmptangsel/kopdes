<?php
// /koperasi/admin/hapus_anggota.php

error_reporting(0); 
ini_set('display_errors', 0);

include '../config.php';
session_start();

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

// Ambil ID dari URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Konversi ke integer

    // Query untuk SOFT DELETE: Mengubah status anggota menjadi 'non-aktif'
    // Ini menghindari Fatal Error Foreign Key Constraint.
    $query = "UPDATE anggota SET status_anggota = 'non-aktif' WHERE id_anggota = $id";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // Notifikasi Sukses
        $_SESSION['notif_sukses'] = "Anggota berhasil dinon-aktifkan.";
        header("location:anggota.php");
    } else {
        // Notifikasi Gagal (DB Error)
        $_SESSION['notif_error'] = "Gagal menon-aktifkan anggota. Terjadi kesalahan database.";
        header("location:anggota.php");
    }

} else {
    // Jika diakses tanpa ID, kembali ke daftar
    header("location:anggota.php");
}
exit;
?>