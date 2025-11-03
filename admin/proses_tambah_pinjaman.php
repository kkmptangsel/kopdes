<?php
// /koperasi/admin/proses_tambah_pinjaman.php
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
    // Konversi format uang ke float (misal: 100.000 menjadi 100000)
    $jumlah_raw = str_replace('.', '', $_POST['jumlah_pinjaman']);
    $jumlah_raw = str_replace(',', '', $jumlah_raw);
    $jumlah = (float)$jumlah_raw;
    
$kode_pinjaman = mysqli_real_escape_string($koneksi, $_POST['kode_pinjaman']);
$tipe_pinjaman = mysqli_real_escape_string($koneksi, $_POST['tipe_pinjaman']);
$lama_angsuran = mysqli_real_escape_string($koneksi, $_POST['lama_angsuran']);
    $tgl_pinjam = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tenggat = mysqli_real_escape_string($koneksi, $_POST['tenggat_waktu']);

    // Validasi sederhana
    if ($id_anggota <= 0 || $jumlah <= 0 || empty($tgl_pinjam) || empty($tenggat)) {
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk error validasi
        $_SESSION['notif_error'] = "Input pinjaman tidak valid atau ada kolom yang kosong.";
        header("location:data_pinjaman.php");
        exit;
    }

    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // --- 1. INSERT ke Tabel Pinjaman ---
        $query_pinjaman = "INSERT INTO pinjaman (id_anggota, jumlah_pinjaman, tgl_pinjam, tenggat_waktu, status, kode_pinjaman, tipe_pinjaman, lama_angsuran) 
                            VALUES ($id_anggota, $jumlah, '$tgl_pinjam', '$tenggat', 'belum lunas', '$kode_pinjaman', '$tipe_pinjaman', '$lama_angsuran')";
        
        $result_pinjaman = mysqli_query($koneksi, $query_pinjaman);
        if (!$result_pinjaman) {
            throw new Exception("Gagal menyimpan data pinjaman: " . mysqli_error($koneksi));
        }

        // --- 2. JURNAL UMUM (Auto-Jurnal) ---
        $keterangan = "Pemberian Pinjaman Anggota ID: " . $id_anggota . " dengan tenggat: " . $tenggat;
        
        // **A. DEBIT: Piutang Pinjaman (112)** - Aset (Piutang) Bertambah
        $query_jurnal_debit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit) 
                               VALUES ('$tgl_pinjam', '112', '$keterangan', '$jumlah', 0)";
        
        $result_jurnal_debit = mysqli_query($koneksi, $query_jurnal_debit);
        if (!$result_jurnal_debit) {
            throw new Exception("Gagal mencatat jurnal debit (Piutang).");
        }

        // **B. KREDIT: Kas (111)** - Aset (Kas) Berkurang
        $query_jurnal_kredit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit) 
                                 VALUES ('$tgl_pinjam', '111', '$keterangan', 0, '$jumlah')";
                                 
        $result_jurnal_kredit = mysqli_query($koneksi, $query_jurnal_kredit);
        if (!$result_jurnal_kredit) {
            throw new Exception("Gagal mencatat jurnal kredit (Kas).");
        }
        
        // Commit jika semua query sukses
        mysqli_commit($koneksi);
        
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Pinjaman baru sebesar Rp " . number_format($jumlah, 0, ',', '.') . " berhasil dicatat.";
        header("location:data_pinjaman.php");

    } catch (Exception $e) {
        // Rollback jika ada yang gagal
        mysqli_rollback($koneksi);
        
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk error
        $_SESSION['notif_error'] = "Gagal memproses pinjaman. Terjadi kesalahan sistem: " . $e->getMessage();
        header("location:data_pinjaman.php");
    }

} else {
    header("location:data_pinjaman.php");
}
?>