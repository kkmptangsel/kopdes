<?php
// /koperasi/config.php

$host = 'db.fr-pari1.bengt.wasmernet.com';
$user = '95752a1d7a50800009a7d19c37b8'; // User default XAMPP
$pass = ''; // Password default XAMPP (kosong)
$db   = 'kopdes';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}



?>
