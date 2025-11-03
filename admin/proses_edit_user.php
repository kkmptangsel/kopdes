<?php
// /koperasi/admin/proses_edit_user.php - KOREKSI FINAL UNTUK SNACKBAR ERROR

session_start();
include '../config.php';

// Cek sesi (Hanya admin)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

// Tambahkan header anti-caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("location:daftar_user.php");
    exit;
}

// Ambil dan sanitasi data POST
$id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
$nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$role = mysqli_real_escape_string($koneksi, $_POST['role']);
$password = $_POST['password']; 
$konfirmasi_password = $_POST['konfirmasi_password'];
$new_id_anggota = mysqli_real_escape_string($koneksi, $_POST['id_anggota']); 

// 1. Validasi
if (empty($nama_lengkap) || empty($username) || empty($role)) {
    // 🛑 KOREKSI: Gunakan Session Flash Message
    $_SESSION['notif_error'] = "Semua field wajib diisi.";
    header("location:edit_user.php?id=$id_user"); // Redirect ke form tanpa pesan URL
    exit;
}

if (!empty($password) && $password != $konfirmasi_password) {
    // 🛑 KOREKSI: Gunakan Session Flash Message
    $_SESSION['notif_error'] = "Password baru dan konfirmasi password tidak cocok.";
    header("location:edit_user.php?id=$id_user");
    exit;
}

// 2. Cek duplikasi username (kecuali username dari user yang sedang diedit)
$query_cek_username = "SELECT username FROM users WHERE username = '$username' AND id_user != '$id_user'";
$result_cek_username = mysqli_query($koneksi, $query_cek_username);

if (mysqli_num_rows($result_cek_username) > 0) {
    // 🛑 KOREKSI: Gunakan Session Flash Message
    $_SESSION['notif_error'] = "Username **{$username}** sudah digunakan oleh pengguna lain.";
    header("location:edit_user.php?id=$id_user");
    exit;
}

// 3. Mulai Transaksi Database
mysqli_begin_transaction($koneksi);

try {
    // A. UPDATE RELASI ANGGOTA (Logika sama)
    $query_unlink_old = "UPDATE anggota SET id_user = NULL WHERE id_user = '$id_user'";
    if (!mysqli_query($koneksi, $query_unlink_old)) {
        throw new Exception("Gagal unlink anggota lama.");
    }
    
    // A.2. LINK ANGGOTA BARU
    if (!empty($new_id_anggota)) {
        $query_link_new = "UPDATE anggota SET id_user = '$id_user' WHERE id_anggota = '$new_id_anggota'";
        if (!mysqli_query($koneksi, $query_link_new)) {
            throw new Exception("Gagal link anggota baru.");
        }
    }

    // B. UPDATE DATA USER (Logika sama)
    $set_password = "";
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $set_password = ", password = '$hashed_password'";
    }

    $query_update_user = "UPDATE users SET 
                            nama_lengkap = '$nama_lengkap',
                            username = '$username',
                            role = '$role'
                            $set_password
                          WHERE id_user = '$id_user'";
    
    if (!mysqli_query($koneksi, $query_update_user)) {
        throw new Exception("Gagal update data pengguna.");
    }

    // 4. Commit transaksi jika semua berhasil
    mysqli_commit($koneksi);
    
    // 5. REDIRECT SUKSES (Menggunakan Session untuk Snackbar - TIDAK BERUBAH)
    $_SESSION['notif_sukses'] = "Data pengguna berhasil diperbarui."; 
    header("location:daftar_user.php"); 
    exit;

} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($koneksi);
    
    // 🛑 KOREKSI: Gunakan Session Flash Message untuk error database
    $_SESSION['notif_error'] = "Gagal memperbarui data. Kesalahan: " . $e->getMessage();
    header("location:edit_user.php?id=$id_user"); // Redirect ke form tanpa pesan URL
    exit;
}
?>