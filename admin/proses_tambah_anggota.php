<?php
// /koperasi/admin/proses_tambah_anggota.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI UTAMA: Gunakan Session Flash Message untuk cek sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

// Pastikan ini adalah request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Perhatikan: Anda mengambil 'id_anggota' dan 'no_anggota'. Pastikan keduanya ada di form.
    // Jika 'id_anggota' adalah auto-increment di database, sebaiknya jangan diambil dari POST.
    // Asumsi: 'no_anggota' adalah ID yang dimasukkan pengguna, dan 'id_anggota' adalah hidden field.
    
    // Ambil data dari form dan lakukan sanitasi
    // Catatan: Jika 'id_anggota' tidak ada di form, ini akan memicu error Notice.
    $id_anggota = isset($_POST['id_anggota']) ? mysqli_real_escape_string($koneksi, $_POST['id_anggota']) : null;
    $no_anggota = mysqli_real_escape_string($koneksi, $_POST['no_anggota']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_anggota']);
    $ktp = mysqli_real_escape_string($koneksi, $_POST['no_ktp']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']); // Tambahkan Jenis Kelamin
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $agama = mysqli_real_escape_string($koneksi, $_POST['agama']);
    $telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $tgl = mysqli_real_escape_string($koneksi, $_POST['tgl_bergabung']);


    // Query untuk INSERT data
    // Perhatikan: Saya menambahkan 'jenis_kelamin' karena ada di form tambah_anggota.php
    $query = "INSERT INTO anggota (no_anggota, nama_anggota, no_ktp, jenis_kelamin, alamat, no_telp, tgl_bergabung) 
              VALUES ('$no_anggota', '$nama', '$ktp', '$jenis_kelamin', '$alamat', '$agama', '$telp', '$tgl')";

    // Jika id_anggota tidak di-set/auto-increment, gunakan query ini:
    /*
    $query = "INSERT INTO anggota (no_anggota, nama_anggota, no_ktp, jenis_kelamin, alamat, no_telp, tgl_bergabung) 
              VALUES ('$no_anggota', '$nama', '$ktp', '$jenis_kelamin', '$alamat', '$agama', '$telp', '$tgl')";
    */

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // Jika berhasil, gunakan SESSION sukses untuk Snackbar 🟢
        $_SESSION['notif_sukses'] = "Data anggota **$nama** berhasil ditambahkan.";
        header("location:anggota.php"); // Redirect ke halaman daftar anggota
    } else {
        // Jika gagal, gunakan SESSION error untuk Snackbar 🔴
        // Gunakan fungsi mysqli_error() untuk debugging yang lebih baik, atau pesan umum.
        // $error_msg = mysqli_error($koneksi); 
        $_SESSION['notif_error'] = "Gagal menambahkan data anggota. Kemungkinan No. Anggota, No. KTP, atau data duplikat lainnya sudah terdaftar.";
        header("location:tambah_anggota.php"); // Redirect kembali ke form input jika gagal
    }

} else {
    // Jika diakses langsung, tendang kembali
    $_SESSION['notif_error'] = "Akses tidak valid.";
    header("location:anggota.php");
}
exit;
?>