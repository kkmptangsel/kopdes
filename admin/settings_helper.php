<?php
// /koperasi/includes/settings_helper.php
include '../config.php';

// Pastikan koneksi sudah ada ($koneksi dari config.php)
if (!isset($koneksi)) {
    // Jika file ini dipanggil tanpa config.php, kita hentikan
    die("Error: Koneksi database tidak ditemukan.");
}

// Ambil data pengaturan dari database
$query_settings = "SELECT * FROM pengaturan WHERE id = 1";
$result_settings = mysqli_query($koneksi, $query_settings);

if ($result_settings && mysqli_num_rows($result_settings) > 0) {
    // Simpan data pengaturan ke variabel global $SETTINGS
    $SETTINGS = mysqli_fetch_assoc($result_settings);
} else {
    // Data default jika tabel kosong atau gagal
    $SETTINGS = [
        'nama_koperasi' => 'Koperasi Default',
        'no_telp' => '0000',
        'email' => 'default@mail.com',
        'external_url' => 'https://merahputih.kop.id/'
    ];
}
?>