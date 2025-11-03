<?php
// /koperasi/admin/hapus_user.php
session_start();
include '../config.php';

// Cek sesi (Hanya admin yang boleh)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Akses ditolak.";
    header("location:../index.php"); 
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['notif_error'] = "ID pengguna tidak valid.";
    header("location:daftar_user.php");
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_GET['id']);

// Mulai transaksi (penting untuk unlink relasi)
mysqli_begin_transaction($koneksi);

try {
    // 1. UNLINK RELASI ANGGOTA (Anggota tidak terhapus, hanya relasi id_user di set NULL)
    $query_unlink = "UPDATE anggota SET id_user = NULL WHERE id_user = '$id_user'";
    if (!mysqli_query($koneksi, $query_unlink)) {
        throw new Exception("Gagal memutuskan relasi anggota.");
    }

    // 2. HAPUS DATA USER
    $query_delete = "DELETE FROM users WHERE id_user = '$id_user'";
    if (!mysqli_query($koneksi, $query_delete)) {
        throw new Exception("Gagal menghapus data pengguna.");
    }
    
    mysqli_commit($koneksi);

    // 🛑 Set notifikasi sukses
    $_SESSION['notif_sukses'] = "Pengguna berhasil dihapus dan relasi anggota diputuskan.";
    header("location:daftar_user.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['notif_error'] = "Gagal menghapus pengguna: " . $e->getMessage();
    header("location:daftar_user.php");
    exit;
}
?>