<?php
// /koperasi/config.php

$host = 'localhost';
$user = 'root'; // User default XAMPP
$pass = ''; // Password default XAMPP (kosong)
$db   = 'kopdes';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


?>