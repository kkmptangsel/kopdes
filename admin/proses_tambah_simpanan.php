<?php
// /koperasi/admin/proses_tambah_simpanan.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil dan sanitasi data dari form
    $id_anggota = (int)$_POST['id_anggota'];
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_simpanan']);
    
    // Konversi format uang ke float (misal: 100.000 menjadi 100000)
    // Asumsi input menggunakan format titik/koma sebagai pemisah ribuan
    $jumlah_raw = str_replace('.', '', $_POST['jumlah']);
    $jumlah_raw = str_replace(',', '', $jumlah_raw);
    $jumlah = (float)$jumlah_raw;
    
    $tgl = mysqli_real_escape_string($koneksi, $_POST['tgl_simpan']); // Variabel tanggal yang benar

    // Validasi sederhana
    if ($id_anggota <= 0 || empty($jenis) || $jumlah <= 0 || empty($tgl)) {
        // 🛑 KOREKSI: Gunakan Session Flash Message
        $_SESSION['notif_error'] = "Input data simpanan tidak valid atau ada kolom yang kosong.";
        header("location:data_simpanan.php"); // Redirect ke halaman utama simpanan
        exit;
    }

    // --- KONSISTENSI STRING JURNAL ---
    $jenis_clean = ucfirst(strtolower($jenis)); // Contoh: 'pokok' -> 'Pokok'
    $keterangan = "Penerimaan Simpanan " . $jenis_clean . " dari Anggota dengan ID: " . $id_anggota;
    
    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // --- 1. INSERT ke Tabel Simpanan ---
        $query_simpanan = "INSERT INTO simpanan (id_anggota, jenis_simpanan, jumlah, tgl_simpan) 
                            VALUES ($id_anggota, '$jenis', $jumlah, '$tgl')";
        
        $result_simpanan = mysqli_query($koneksi, $query_simpanan);
        if (!$result_simpanan) {
            throw new Exception("Gagal menyimpan data simpanan: " . mysqli_error($koneksi));
        }

        // --- 2. JURNAL UMUM (Auto-Jurnal) ---

        // A. DEBIT: Kas (111) - Aset Bertambah
        $query_jurnal_debit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit) 
                               VALUES ('$tgl', '111', '$keterangan', '$jumlah', 0)";
        
        $result_jurnal_debit = mysqli_query($koneksi, $query_jurnal_debit);
        if (!$result_jurnal_debit) {
            throw new Exception("Gagal mencatat jurnal debit (Kas).");
        }

        // B. KREDIT: Simpanan Pokok/Wajib (211) - Liabilitas Bertambah
        // CATATAN: Dalam sistem yang lebih kompleks, no_akun liabilitas akan berbeda (misal 211 untuk Pokok, 212 untuk Wajib).
        // Karena di kode ini hanya menggunakan 211, kita asumsikan 211 untuk kedua jenis simpanan.
        $query_jurnal_kredit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit) 
                                 VALUES ('$tgl', '211', '$keterangan', 0, '$jumlah')";
                                 
        $result_jurnal_kredit = mysqli_query($koneksi, $query_jurnal_kredit);
        if (!$result_jurnal_kredit) {
            throw new Exception("Gagal mencatat jurnal kredit (Simpanan).");
        }
        
        // Commit jika semua query sukses
        mysqli_commit($koneksi);
        
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Simpanan **$jenis_clean** sebesar Rp " . number_format($jumlah, 0, ',', '.') . " berhasil ditambahkan.";
        header("location:data_simpanan.php");

    } catch (Exception $e) {
        // Rollback jika ada yang gagal
        mysqli_rollback($koneksi);
        
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk error
        $_SESSION['notif_error'] = "Gagal memproses simpanan. Terjadi kesalahan sistem: " . $e->getMessage();
        header("location:data_simpanan.php");
    }

} else {
    header("location:data_simpanan.php");
}
?>