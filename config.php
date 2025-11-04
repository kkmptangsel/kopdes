<?php
// /kopdes/config.php

// --- Detail Koneksi Baru (Wasmernet/Remote) ---
$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = '10272'; // Tambahkan Port
$user = '95752a1d7a50800009a7d19c37b8'; // Ganti dengan Username yang baru
$pass = ''; // *** PERHATIAN: Masukkan Password Anda di sini! ***
$db   = 'kopdes';

// Membuat koneksi database dengan menyertakan Port
// Format: mysqli_connect(host, username, password, dbname, port)
$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

if (!$koneksi) {
    // Memberikan pesan error yang lebih informatif
    die("Koneksi database GAGAL: " . mysqli_connect_error() . " (Pastikan HOST, PORT, USER, dan PASSWORD benar)");
}

// Opsional: Atur encoding agar karakter Indonesia (seperti Rp) tampil benar
mysqli_set_charset($koneksi, "utf8mb4");

?>
