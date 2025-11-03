<?php
// /koperasi/admin/proses_edit_simpanan.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

// Pastikan ini adalah request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_simpanan = (int)$_POST['id_simpanan'];
    $id_anggota = (int)$_POST['id_anggota'];
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_simpanan']);
    
    // Konversi format uang ke float
    $jumlah_raw = str_replace('.', '', $_POST['jumlah']);
    $jumlah_raw = str_replace(',', '', $jumlah_raw);
    $jumlah = (float)$jumlah_raw;
    
    $tgl = mysqli_real_escape_string($koneksi, $_POST['tgl_simpan']);

    // Validasi sederhana
    if (empty($id_simpanan) || empty($id_anggota) || empty($jenis) || empty($jumlah) || empty($tgl)) {
        // 🔴 KOREKSI: Gunakan Session Flash Message
        $_SESSION['notif_error'] = "Gagal mengupdate simpanan: Data input tidak lengkap.";
        header("location:data_simpanan.php"); 
        exit;
    }

    // CATATAN PENTING: Untuk transaksi Edit/Update, seharusnya ada proses Jurnal Balik (Reversal)
    // dan Jurnal Baru untuk mencatat perubahan yang akurat.
    // Karena kode jurnal umum sebelumnya tidak ada di proses edit ini, kita hanya fokus pada UPDATE data Simpanan.

    // Query untuk UPDATE data Simpanan
    $query = "UPDATE simpanan SET 
                id_anggota = $id_anggota, 
                jenis_simpanan = '$jenis', 
                jumlah = $jumlah, 
                tgl_simpan = '$tgl' 
              WHERE id_simpanan = $id_simpanan";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $jenis_clean = ucfirst(strtolower($jenis));
        $_SESSION['notif_sukses'] = "Simpanan **$jenis_clean** dengan ID #$id_simpanan berhasil diupdate.";
        header("location:data_simpanan.php");
    } else {
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk gagal
        $_SESSION['notif_error'] = "Gagal mengupdate simpanan. Terjadi kesalahan database: " . mysqli_error($koneksi);
        header("location:data_simpanan.php");
    }

} else {
    // Jika diakses langsung, tendang kembali
    header("location:data_simpanan.php");
}
?>