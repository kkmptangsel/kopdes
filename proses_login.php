<?php
// /koperasi/proses_login.php
include 'config.php';
session_start();

// Menangkap data yang dikirim dari form
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = mysqli_real_escape_string($koneksi, $_POST['password']);

// Mencari data user di database
$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);
$cek = mysqli_num_rows($result);

if ($cek > 0) {
    $data = mysqli_fetch_assoc($result);

    // Memverifikasi password yang di-input dengan hash di database
    // Ini adalah cara aman untuk cek password
    if (password_verify($password, $data['password'])) {

        // Buat session
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['status'] = "login";

        // Mengarahkan berdasarkan role
        if ($data['role'] == "admin") {
            header("location:admin/dashboard.php");
        } else if ($data['role'] == "anggota") {
            // Nanti bisa diarahkan ke dashboard anggota
            header("location:index.php?pesan=gagal"); // Sementara kita blok dulu
        } else {
            header("location:index.php?pesan=gagal");
        }
    } else {
        // Jika password salah
        header("location:index.php?pesan=gagal");
    }
} else {
    // Jika username tidak ditemukan
    header("location:index.php?pesan=gagal");
}
?>