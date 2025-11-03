<?php
// /koperasi/admin/detail_pinjaman.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

// Ambil ID Pinjaman dari URL
if (!isset($_GET['id'])) {
    header("location:data_pinjaman.php");
    exit;
}
$id_pinjaman = (int)$_GET['id'];

// Query 1: Ambil Data Pinjaman
$query_pinjam = "SELECT p.*, a.nama_anggota FROM pinjaman p JOIN anggota a ON p.id_anggota = a.id_anggota WHERE p.id_pinjaman = $id_pinjaman";
$result_pinjam = mysqli_query($koneksi, $query_pinjam);
$data_pinjam = mysqli_fetch_assoc($result_pinjam);

if (!$data_pinjam) {
    die("Pinjaman tidak ditemukan.");
}

// Query 2: Hitung Total Angsuran yang Sudah Dibayar
$query_total_angsuran = "SELECT SUM(jumlah_angsuran) AS total_bayar FROM angsuran WHERE id_pinjaman = $id_pinjaman";
$result_total = mysqli_query($koneksi, $query_total_angsuran);
$data_total = mysqli_fetch_assoc($result_total);
$total_bayar = $data_total['total_bayar'] ?? 0;

$sisa_pinjaman = $data_pinjam['jumlah_pinjaman'] - $total_bayar;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Pinjaman - Koperasi</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <div class="header">
        <h1>Dashboard Admin Koperasi</h1>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="nav">
        <a href="dashboard.php">Home</a>
        <a href="data_anggota.php">Data Anggota</a>
        <a href="data_simpanan.php">Data Simpanan</a>
        <a href="data_pinjaman.php">Data Pinjaman</a>
    </div>

    <div class="content">
        <h2>Detail Pinjaman dan Pencatatan Angsuran</h2>
        
        <a href="data_pinjaman.php" class="btn" style="background-color: #6c757d; color: white; margin-bottom: 2rem;">&larr; Kembali ke Data Pinjaman</a>
        
        <h3 style="margin-top: 1.5rem;">Informasi Pinjaman</h3>
        <table style="width: 50%; margin-bottom: 2rem;">
            <tr>
                <td style="width: 40%; background-color: #f4f4f4;">**Anggota**</td>
                <td><?php echo htmlspecialchars($data_pinjam['nama_anggota']); ?></td>
            </tr>
            <tr>
                <td style="background-color: #f4f4f4;">**Jumlah Pinjaman**</td>
                <td>**Rp <?php echo number_format($data_pinjam['jumlah_pinjaman'], 0, ',', '.'); ?>**</td>
            </tr>
            <tr>
                <td style="background-color: #f4f4f4;">**Tanggal Pinjam**</td>
                <td><?php echo date('d-m-Y', strtotime($data_pinjam['tgl_pinjam'])); ?></td>
            </tr>
            <tr>
                <td style="background-color: #f4f4f4;">**Tenggat Waktu**</td>
                <td><?php echo date('d-m-Y', strtotime($data_pinjam['tenggat_waktu'])); ?></td>
            </tr>
            <tr>
                <td style="background-color: #f4f4f4;">**Total Sudah Dibayar**</td>
                <td>Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td style="background-color: #f4f4f4;">**Sisa Pinjaman**</td>
                <td style="color: red; font-weight: bold;">Rp <?php echo number_format($sisa_pinjaman, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td style="background-color: #f4f4f4;">**Status**</td>
                <td><?php echo ucfirst($data_pinjam['status']); ?></td>
            </tr>
        </table>
        
        <?php
        // Cek apakah pinjaman sudah lunas
        if ($data_pinjam['status'] == 'belum lunas' && $sisa_pinjaman > 0) {
        ?>
        
        <h3 style="margin-top: 1.5rem;">Catat Angsuran Baru</h3>
        
        <?php 
        // Pesan notifikasi
        if (isset($_GET['pesan']) && $_GET['pesan'] == 'angsuran_sukses') {
            echo "<div style='color:green; background-color: #e6ffe6; padding: 10px; border: 1px solid green; border-radius: 5px; margin-bottom: 1rem;'>Angsuran berhasil dicatat! Status pinjaman telah diperbarui.</div>";
        }
        if (isset($_GET['pesan']) && $_GET['pesan'] == 'angsuran_gagal') {
            echo "<div style='color:red; background-color: #ffeeee; padding: 10px; border: 1px solid red; border-radius: 5px; margin-bottom: 1rem;'>Pencatatan angsuran gagal! Pastikan jumlah tidak melebihi sisa pinjaman.</div>";
        }
        ?>

        <form action="proses_tambah_angsuran.php" method="POST" style="border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
            <input type="hidden" name="id_pinjaman" value="<?php echo $id_pinjaman; ?>">
            <input type="hidden" name="sisa_pinjaman" value="<?php echo $sisa_pinjaman; ?>">
            
            <div class="form-group">
                <label for="jumlah_angsuran">Jumlah Angsuran (Maksimal: Rp <?php echo number_format($sisa_pinjaman, 0, ',', '.'); ?>)</label>
                <input type="number" id="jumlah_angsuran" name="jumlah_angsuran" min="1" max="<?php echo (int)$sisa_pinjaman; ?>" required>
            </div>

            <div class="form-group">
                <label for="tgl_bayar">Tanggal Pembayaran</label>
                <input type="date" id="tgl_bayar" name="tgl_bayar" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Catat Angsuran</button>
        </form>
        
        <?php
        } else {
            echo "<div style='background-color: #f0f0ff; padding: 10px; border: 1px solid #aaa; border-radius: 5px; margin-top: 1rem;'>**Pinjaman ini sudah lunas.** Tidak perlu mencatat angsuran lagi.</div>";
        }
        ?>

        <h3 style="margin-top: 3rem;">Riwayat Angsuran</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jumlah Angsuran (Rp)</th>
                    <th>Tanggal Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query 3: Ambil Riwayat Angsuran
                $query_angsuran = "SELECT * FROM angsuran WHERE id_pinjaman = $id_pinjaman ORDER BY tgl_bayar DESC";
                $result_angsuran = mysqli_query($koneksi, $query_angsuran);

                $no_angsuran = 1;
                while ($data_angsuran = mysqli_fetch_assoc($result_angsuran)) {
                ?>
                    <tr>
                        <td><?php echo $no_angsuran++; ?></td>
                        <td><?php echo number_format($data_angsuran['jumlah_angsuran'], 0, ',', '.'); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($data_angsuran['tgl_bayar'])); ?></td>
                        <td>
                            <small>(Tidak ada Aksi CRUD untuk Angsuran)</small>
                        </td>
                    </tr>
                <?php
                }
                if (mysqli_num_rows($result_angsuran) == 0) {
                    echo "<tr><td colspan='4' style='text-align: center;'>Belum ada riwayat angsuran.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</body>
</html>