<?php
// /koperasi/admin/proses_hapus_pengeluaran.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_pengeluaran = (int)$_GET['id'];
    $ref_id = "PEN-$id_pengeluaran";
    
    // Ambil detail pengeluaran untuk notifikasi
    $q_detail = mysqli_query($koneksi, "SELECT keterangan, jumlah FROM pengeluaran WHERE id_pengeluaran = $id_pengeluaran");
    $d_detail = mysqli_fetch_assoc($q_detail);
    
    // Cek jika data ada
    if (!$d_detail) {
        $_SESSION['notif_error'] = "Gagal menghapus: Data pengeluaran ID #$id_pengeluaran tidak ditemukan.";
        header("location:laporan_pengeluaran.php");
        exit;
    }
    
    $keterangan_notif = htmlspecialchars($d_detail['keterangan']);
    $jumlah_notif = number_format($d_detail['jumlah'], 0, ',', '.');
    
    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // 1. HAPUS ENTRI JURNAL terkait
        $query_jurnal = "DELETE FROM jurnal_umum WHERE ref_id = '$ref_id'";
        $result_jurnal = mysqli_query($koneksi, $query_jurnal);

        // Harusnya 2 baris terhapus (DEBIT Beban, KREDIT Kas)
        if (!$result_jurnal || mysqli_affected_rows($koneksi) < 2) {
             throw new Exception("Gagal menghapus entri jurnal. Hanya " . mysqli_affected_rows($koneksi) . " baris terhapus.");
        }

        // 2. HAPUS DATA PENGELUARAN UTAMA
        $query_pengeluaran = "DELETE FROM pengeluaran WHERE id_pengeluaran = $id_pengeluaran";
        $result_pengeluaran = mysqli_query($koneksi, $query_pengeluaran);
        
        if (!$result_pengeluaran) {
            throw new Exception("Gagal menghapus data pengeluaran utama.");
        }
        
        // Commit jika semua sukses
        mysqli_commit($koneksi);
        
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Pengeluaran '**$keterangan_notif**' (Rp $jumlah_notif) berhasil dihapus.";
        header("location:laporan_pengeluaran.php");

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk gagal
        $_SESSION['notif_error'] = "Gagal menghapus pengeluaran (ID #$id_pengeluaran). Terjadi kesalahan sistem: " . $e->getMessage();
        header("location:laporan_pengeluaran.php");
    }

} else {
    // Jika diakses tanpa ID
    $_SESSION['notif_error'] = "Permintaan hapus pengeluaran tidak valid.";
    header("location:laporan_pengeluaran.php");
}
?>