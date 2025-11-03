<?php
// /koperasi/admin/proses_edit_pinjaman.php
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
    $id_pinjaman = (int)$_POST['id_pinjaman'];
    $id_anggota = (int)$_POST['id_anggota'];
    
    // Konversi format uang ke float
    $jumlah_raw = str_replace('.', '', $_POST['jumlah_pinjaman']);
    $jumlah_raw = str_replace(',', '', $jumlah_raw);
    $jumlah = (float)$jumlah_raw;
    
    $tgl_pinjam = mysqli_real_escape_string($koneksi, $_POST['tgl_pinjam']);
    $tenggat = mysqli_real_escape_string($koneksi, $_POST['tenggat_waktu']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Validasi sederhana
    if (empty($id_pinjaman) || $id_anggota <= 0 || $jumlah <= 0) {
        // 🔴 KOREKSI: Gunakan Session Flash Message
        $_SESSION['notif_error'] = "Gagal mengupdate pinjaman: ID Pinjaman, ID Anggota, atau Jumlah Pinjaman tidak valid.";
        header("location:data_pinjaman.php");
        exit;
    }

    // Query untuk UPDATE data
    $query = "UPDATE pinjaman SET 
                id_anggota = $id_anggota, 
                jumlah_pinjaman = $jumlah, 
                tgl_pinjam = '$tgl_pinjam', 
                tenggat_waktu = '$tenggat',
                status = '$status'
              WHERE id_pinjaman = $id_pinjaman";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Data pinjaman (ID #$id_pinjaman) berhasil diupdate.";
        header("location:data_pinjaman.php");
    } else {
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk gagal
        $_SESSION['notif_error'] = "Gagal mengupdate pinjaman. Terjadi kesalahan database: " . mysqli_error($koneksi);
        header("location:data_pinjaman.php");
    }

} else {
    // Jika diakses langsung
    header("location:data_pinjaman.php");
}
?>