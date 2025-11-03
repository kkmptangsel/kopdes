<?php
// /koperasi/admin/logout.php

session_start();

// 1. Hancurkan semua sesi yang aktif
session_unset();
session_destroy();

// 2. Set notifikasi logout sebagai Session Flash Message
$_SESSION['notif_sukses'] = "Anda berhasil logout";

// 3. Redirect ke halaman index (login page)
header("location:./index.php"); 
exit();
?>