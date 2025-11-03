<?php
// /koperasi/admin/proses_tambah_user.php - VERSI KOREKSI
session_start();
include '../config.php';

// 🛑 CEK LOGIN
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

// 🛑 PASTIKAN REQUEST ADALAH POST (dari form)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("location:tambah_user.php");
    exit;
}

// Header untuk mencegah caching (agar data terbaru langsung dimuat setelah redirect sukses)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. Ambil & Sanitasi data dari form
// Gunakan isset() untuk memastikan variabel ada
$id_anggota = $_POST['id_anggota'] ?? ''; 
$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'anggota'; // Default role jika tidak dikirim

// 2. Validasi Kosong (Gunakan Session Flash Message untuk konsistensi Snackbar)
if (empty($id_anggota) || empty($nama_lengkap) || empty($username) || empty($password)) {
    $_SESSION['notif_error'] = "Semua field wajib diisi.";
    header("location:tambah_user.php");
    exit;
}

$id_anggota = mysqli_real_escape_string($koneksi, $id_anggota);
$nama_lengkap = mysqli_real_escape_string($koneksi, $nama_lengkap);
$username = mysqli_real_escape_string($koneksi, $username);
$role = mysqli_real_escape_string($koneksi, $role);

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. Cek ketersediaan username (Gunakan Session Flash Message)
$q_check = mysqli_query($koneksi, "SELECT username FROM users WHERE username = '$username'");
if (mysqli_num_rows($q_check) > 0) {
    $_SESSION['notif_error'] = "Username **{$username}** sudah digunakan. Silakan pilih username lain.";
    header("location:tambah_user.php");
    exit;
}

// --- MULAI TRANSAKSI DATABASE UNTUK KEAMANAN DATA ---
mysqli_begin_transaction($koneksi);

try {
    // 4. LANGKAH 1: INSERT ke tabel users
    $query_insert_user = "INSERT INTO users (nama_lengkap, username, password, role) 
                          VALUES ('$nama_lengkap', '$username', '$hashed_password', '$role')";

    if (!mysqli_query($koneksi, $query_insert_user)) {
        throw new Exception("Gagal insert pengguna baru.");
    }
    
    // Ambil ID yang baru saja di-insert
    $new_id_user = mysqli_insert_id($koneksi);
    
    // 5. LANGKAH 2: UPDATE tabel anggota dengan id_user yang baru
    $query_update_anggota = "UPDATE anggota SET id_user = '$new_id_user' WHERE id_anggota = '$id_anggota'";
    
    if (!mysqli_query($koneksi, $query_update_anggota)) {
        throw new Exception("Gagal update relasi anggota.");
    }

    // 6. Commit Transaksi jika semua berhasil
    mysqli_commit($koneksi);
    
    // 7. Redirect Sukses (Menggunakan Session Flash Message)
    $_SESSION['notif_sukses'] = "Pengguna **" . htmlspecialchars($username) . "** berhasil ditambahkan!";
    header("location:daftar_user.php");
    exit;

} catch (Exception $e) {
    // 8. Rollback Transaksi jika terjadi kegagalan
    mysqli_rollback($koneksi);
    
    // Redirect ke form tambah user dengan pesan gagal
    $_SESSION['notif_error'] = "Gagal menambahkan pengguna: " . $e->getMessage();
    header("location:tambah_user.php");
    exit;
}
?>