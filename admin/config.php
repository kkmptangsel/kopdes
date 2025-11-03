<?php
// /koperasi/config.php

$host = 'https://kopdes.co-id.id/';
$user = 'root'; // User default XAMPP
$pass = ''; // Password default XAMPP (kosong)
$db   = 'ux28bigh_kopdes';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


?>