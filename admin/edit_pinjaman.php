<?php
// /koperasi/admin/edit_pinjaman.php
include '../config.php';
session_start(); 

include 'settings_helper.php';
$title = "Edit Pinjaman | " . $SETTINGS['nama_koperasi'];
$page_heading = "Edit Pinjaman";
include 'template/header.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("location:data_pinjaman.php");
    exit;
}
$id_pinjaman = (int)$_GET['id']; 

// Ambil data pinjaman dari database
$query = "SELECT * FROM pinjaman WHERE id_pinjaman = $id_pinjaman";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (mysqli_num_rows($result) < 1) {
    echo "Data pinjaman tidak ditemukan!";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Pinjaman - Koperasi</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
</head>
<body>

    <div class="content">
        
        <form action="proses_edit_pinjaman.php" method="POST">
            <input type="hidden" name="id_pinjaman" value="<?php echo $data['id_pinjaman']; ?>">

            <div class="form-group">
                <label for="id_anggota">Anggota Peminjam</label>
                <select id="id_anggota" name="id_anggota" required style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">-- Pilih Nama Anggota --</option>
                    <?php
                    // Ambil data anggota untuk dropdown
                    $query_anggota = "SELECT id_anggota, nama_anggota FROM anggota ORDER BY nama_anggota ASC";
                    $result_anggota = mysqli_query($koneksi, $query_anggota);
                    while ($data_anggota = mysqli_fetch_assoc($result_anggota)) {
                        $selected = ($data_anggota['id_anggota'] == $data['id_anggota']) ? 'selected' : '';
                        echo "<option value='" . $data_anggota['id_anggota'] . "' $selected>" . htmlspecialchars($data_anggota['nama_anggota']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="jumlah_pinjaman">Jumlah Pinjaman (Rp)</label>
                <input type="text" id="jumlah_pinjaman" name="jumlah_pinjaman" value="<?php echo $data['jumlah_pinjaman']; ?>" required>
            </div>

            <div class="form-group">
                <label for="tgl_pinjam">Tanggal Pinjam</label>
                <input type="date" id="tgl_pinjam" name="tgl_pinjam" value="<?php echo $data['tgl_pinjam']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tenggat_waktu">Tenggat Waktu</label>
                <input type="date" id="tenggat_waktu" name="tenggat_waktu" value="<?php echo $data['tenggat_waktu']; ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status Pinjaman</label>
                <select id="status" name="status" required style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="belum lunas" <?php if($data['status'] == 'belum lunas') echo 'selected'; ?>>Belum Lunas</option>
                    <option value="lunas" <?php if($data['status'] == 'lunas') echo 'selected'; ?>>Lunas</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Pinjaman</button>
            <a href="data_pinjaman.php" class="btn" style="background-color: #6c757d; color: white;">Batal</a>
        </form>
    </div>

<?php include 'template/footer.php';?>