<?php
// /koperasi/admin/proses_hapus_pinjaman.php
include '../config.php';
session_start(); 

// Tambahkan Cek sesi untuk keamanan, jika belum ada
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_pinjaman = (int)$_GET['id'];
    
    // 1. Ambil detail Pinjaman sebelum dihapus untuk mendapatkan KETERANGAN JURNAL dan Jumlah (untuk notifikasi)
    $q_detail = mysqli_query($koneksi, "SELECT id_anggota, jumlah_pinjaman, tgl_pinjam FROM pinjaman WHERE id_pinjaman = $id_pinjaman");
    $d_detail = mysqli_fetch_assoc($q_detail);
    
    // Cek jika data pinjaman ada
    if (!$d_detail) {
        // 🔴 KOREKSI: Gunakan Session Flash Message jika data tidak ditemukan
        $_SESSION['notif_error'] = "Gagal menghapus: Data pinjaman dengan ID #$id_pinjaman tidak ditemukan.";
        header("location:data_pinjaman.php");
        exit;
    }
    
    $id_anggota = $d_detail['id_anggota'];
    $tgl_pinjam = $d_detail['tgl_pinjam'];
    $jumlah_pinjaman = number_format($d_detail['jumlah_pinjaman'], 0, ',', '.');

    // Keterangan Jurnal yang dibuat saat tambah pinjaman:
    // Gunakan % untuk LIKE agar sesuai dengan string lengkap (termasuk tenggat waktu)
    $keterangan_jurnal = "Pemberian Pinjaman Anggota ID: " . $id_anggota . "%"; 
    
    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // --- 1. HAPUS ENTRI JURNAL ---
        // Hapus Jurnal DEBIT (Piutang 112) dan KREDIT (Kas 111) berdasarkan Keterangan dan Tanggal
        $query_jurnal = "DELETE FROM jurnal_umum 
                          WHERE tgl_transaksi = '$tgl_pinjam' 
                          AND keterangan LIKE '$keterangan_jurnal' 
                          AND (no_akun = '111' OR no_akun = '112')"; 
                          
        $result_jurnal = mysqli_query($koneksi, $query_jurnal);
        
        if (!$result_jurnal) {
            throw new Exception("Gagal menghapus entri jurnal.");
        }
        
        // --- 2. HAPUS DATA PINJAMAN UTAMA ---
        // CATATAN: Asumsi trigger database/logika lain menangani penghapusan angsuran terkait.
        $query_pinjaman = "DELETE FROM pinjaman WHERE id_pinjaman = $id_pinjaman";
        $result_pinjaman = mysqli_query($koneksi, $query_pinjaman);
        
        if (!$result_pinjaman) {
            throw new Exception("Gagal menghapus data pinjaman utama.");
        }
        
        // Commit jika kedua penghapusan sukses
        mysqli_commit($koneksi);
        
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Data Pinjaman (Rp $jumlah_pinjaman) berhasil dihapus, beserta entri jurnal terkait.";
        header("location:data_pinjaman.php");

    } catch (Exception $e) {
        // Rollback jika ada yang gagal
        mysqli_rollback($koneksi);
        
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk error
        $_SESSION['notif_error'] = "Gagal menghapus pinjaman (ID #$id_pinjaman). Terjadi kesalahan sistem: " . $e->getMessage();
        header("location:data_pinjaman.php");
    }

} else {
    // Jika diakses tanpa ID
    $_SESSION['notif_error'] = "Permintaan hapus tidak valid.";
    header("location:data_pinjaman.php");
}
?>