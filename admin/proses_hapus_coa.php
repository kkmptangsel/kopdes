<?php
// /koperasi/admin/proses_hapus_coa.php
include '../config.php';
session_start(); 

// Cek sesi (Tambahkan cek admin untuk keamanan)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Akses ditolak.";
    header("location:../index.php"); 
    exit;
}

if (isset($_GET['no'])) {
    $no_akun = mysqli_real_escape_string($koneksi, $_GET['no']);
    
    // 1. CEK TRANSAKSI: Pastikan tidak ada transaksi di jurnal_umum
    $q_check = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM jurnal_umum WHERE no_akun = '$no_akun'");
    $d_check = mysqli_fetch_assoc($q_check);
    
    if ($d_check['total'] > 0) {
        // 🛑 KOREKSI: Gunakan Session Flash Message untuk notifikasi gagal (ada transaksi)
        $_SESSION['notif_error'] = "Gagal menghapus akun **{$no_akun}**. Akun tersebut sudah memiliki transaksi di Jurnal Umum.";
        header("location:laporan_coa.php");
        exit;
    }
    
    // 2. HAPUS COA
    $query_hapus = "DELETE FROM coa WHERE no_akun = '$no_akun'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        // 🛑 KOREKSI: Gunakan Session Flash Message untuk notifikasi sukses
        $_SESSION['notif_sukses'] = "Akun COA dengan nomor **{$no_akun}** berhasil dihapus.";
        header("location:laporan_coa.php");
        exit;
    } else {
        // 🛑 KOREKSI: Gunakan Session Flash Message untuk notifikasi gagal (database error)
        $_SESSION['notif_error'] = "Gagal menghapus akun. Terjadi kesalahan pada database.";
        header("location:laporan_coa.php");
        exit;
    }

} else {
    // 🛑 KOREKSI: Tambahkan pesan error jika tidak ada ID
    $_SESSION['notif_error'] = "ID Akun tidak ditemukan.";
    header("location:laporan_coa.php");
    exit;
}
?>