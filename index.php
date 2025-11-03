<?php
// /koperasi/index.php
include 'config.php';
session_start();

// Jika sudah login, redirect ke dashboard yang sesuai
if (isset($_SESSION['status']) && $_SESSION['status'] == 'login') {
    if ($_SESSION['role'] == 'admin') {
        header("location:admin/dashboard.php");
    } else {
        // Nanti bisa diarahkan ke dashboard anggota
        // header("location:anggota/dashboard.php");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Koperasi Simpan Pinjam</title>
    <style>
        body { font-family: Arial, sans-serif; display: grid; place-items: center; min-height: 90vh; background-color: #f4f4f4; }
        .login-container { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .login-container h2 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input { width: 300px; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; }
        .btn { width: 100%; padding: 0.75rem; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Login Koperasi</h2>

        <?php
        // Menampilkan pesan error jika login gagal
        if (isset($_GET['pesan'])) {
            if ($_GET['pesan'] == "gagal") {
                echo "<div class='error'>Username atau Password salah!</div>";
            } else if ($_GET['pesan'] == "logout") {
                echo "<div class='error' style='color:green;'>Anda telah berhasil logout.</div>";
            } else if ($_GET['pesan'] == "belum_login") {
                echo "<div class='error'>Anda harus login untuk mengakses halaman.</div>";
            }
        }
        ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>

</body>
</html>