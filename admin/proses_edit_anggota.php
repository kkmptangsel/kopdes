<?php
// /koperasi/admin/proses_edit_anggota.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message untuk cek sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

// Pastikan ini adalah request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan lakukan sanitasi
    $id = (int)$_POST['id_anggota']; // Ambil ID dari hidden input
    $no_anggota = mysqli_real_escape_string($koneksi, $_POST['no_anggota']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_anggota']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $ktp = mysqli_real_escape_string($koneksi, $_POST['no_ktp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $agama = mysqli_real_escape_string($koneksi, $_POST['agama']);
    $telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $tgl = mysqli_real_escape_string($koneksi, $_POST['tgl_bergabung']);

    // Query untuk UPDATE data
    $query = "UPDATE anggota SET
                no_anggota = '$no_anggota',
                nama_anggota = '$nama',
                jenis_kelamin = '$jenis_kelamin',
                no_ktp = '$ktp',
                alamat = '$alamat',
                agama = '$agama',
                no_telp = '$telp',
                tgl_bergabung = '$tgl'
              WHERE id_anggota = $id";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses
        $_SESSION['notif_sukses'] = "Data anggota **$nama** dengan No. Anggota **$no_anggota** berhasil diperbarui.";
        header("location:anggota.php");
    } else {
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk gagal
        // Untuk error yang lebih spesifik (misal, duplikat), Anda mungkin perlu mengecek error code MySQL.
        $_SESSION['notif_error'] = "Gagal memperbarui data anggota. Kemungkinan No. Anggota atau No. KTP sudah digunakan oleh anggota lain.";
        header("location:anggota.php"); // Redirect kembali ke halaman daftar anggota
    }

} else {
    // Jika diakses langsung
    $_SESSION['notif_error'] = "Akses tidak valid.";
    header("location:anggota.php");
}
exit;
?>