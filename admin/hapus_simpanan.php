<?php
// /koperasi/admin/hapus_simpanan.php
include '../config.php';
session_start(); 

// Cek sesi (Meski tidak ada di kode asli, penting untuk menambahkannya)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

if (isset($_GET['id'])) {
    $id_simpanan = (int)$_GET['id'];
    
    // 1. Ambil detail Simpanan sebelum dihapus untuk mendapatkan KETERANGAN JURNAL
    $q_detail = mysqli_query($koneksi, "SELECT id_anggota, jenis_simpanan, jumlah, tgl_simpan FROM simpanan WHERE id_simpanan = $id_simpanan");
    $d_detail = mysqli_fetch_assoc($q_detail);
    
    // Cek jika data simpanan ada
    if (!$d_detail) {
        // 🔴 KOREKSI: Gunakan Session Flash Message jika data tidak ditemukan
        $_SESSION['notif_error'] = "Gagal menghapus: Data simpanan dengan ID #$id_simpanan tidak ditemukan.";
        header("location:data_simpanan.php");
        exit;
    }
    
    $id_anggota = $d_detail['id_anggota'];
    $jumlah_simpanan = number_format($d_detail['jumlah'], 0, ',', '.');
    
    // --- KOREKSI KRITIS: Memastikan Kapitalisasi Konsisten ---
    $jenis_simpanan_clean = ucfirst(strtolower($d_detail['jenis_simpanan'])); // Contoh: 'pokok' menjadi 'Pokok'
    
    $tgl_transaksi_jurnal = $d_detail['tgl_simpan'];

    // Keterangan Jurnal yang dibuat saat tambah simpanan:
    $keterangan_jurnal = "Penerimaan Simpanan " . $jenis_simpanan_clean . " dari Anggota dengan ID: " . $id_anggota;
    
    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // --- 1. HAPUS ENTRI JURNAL ---
        // CATATAN: Penggunaan LIKE '$keterangan_jurnal%' untuk mengatasi potensi perbedaan spasi/karakter.
        $query_jurnal = "DELETE FROM jurnal_umum 
                          WHERE tgl_transaksi = '$tgl_transaksi_jurnal' 
                          AND keterangan LIKE '$keterangan_jurnal%' 
                          AND (no_akun = '111' OR no_akun = '211')";
                          
        $result_jurnal = mysqli_query($koneksi, $query_jurnal);
        
        // Cek jumlah baris yang terpengaruh (Harusnya 2: DEBIT Kas dan KREDIT Simpanan)
        if (mysqli_affected_rows($koneksi) < 2) {
             // Rollback jika kurang dari 2 baris terhapus (Jurnal DEBIT dan KREDIT)
             mysqli_rollback($koneksi);
             throw new Exception("Hanya " . mysqli_affected_rows($koneksi) . " entri jurnal yang ditemukan, bukan 2. Operasi dibatalkan.");
        }
        
        if (!$result_jurnal) {
            throw new Exception("Gagal menghapus entri jurnal: " . mysqli_error($koneksi));
        }

        // --- 2. HAPUS DATA SIMPANAN UTAMA ---
        $query_simpanan = "DELETE FROM simpanan WHERE id_simpanan = $id_simpanan";
        $result_simpanan = mysqli_query($koneksi, $query_simpanan);
        
        if (!$result_simpanan) {
            throw new Exception("Gagal menghapus data simpanan utama: " . mysqli_error($koneksi));
        }
        
        // Commit jika kedua penghapusan sukses
        mysqli_commit($koneksi);
        
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Data Simpanan **$jenis_simpanan_clean** (Rp $jumlah_simpanan) berhasil dihapus.";
        header("location:data_simpanan.php");

    } catch (Exception $e) {
        // Rollback jika ada yang gagal
        mysqli_rollback($koneksi);
        
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk error
        // Kode debugging 'echo "Error: " . $e->getMessage(); exit;' dihapus untuk live environment.
        $_SESSION['notif_error'] = "Gagal menghapus simpanan (ID #$id_simpanan). Terjadi kesalahan sistem: " . $e->getMessage();
        header("location:data_simpanan.php");
    }

} else {
    // Jika diakses tanpa ID
    $_SESSION['notif_error'] = "Permintaan hapus tidak valid.";
    header("location:data_simpanan.php");
}
?>