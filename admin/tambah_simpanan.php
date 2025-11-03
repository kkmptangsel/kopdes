<?php
// /koperasi/admin/tambah_simpanan.php
include '../config.php';
session_start(); 

include 'settings_helper.php';
$title = "Tambah Simpanan Anggota | " . $SETTINGS['nama_koperasi'];
$page_heading = "Tambah Simpanan Anggota";
include 'template/header.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}
?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
        
        <form class="max-w-full mx-auto" action="proses_tambah_simpanan.php" method="POST">
            <div class="form-group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="id_anggota">Pilih Anggota</label>
                <select id="id_anggota" name="id_anggota" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">-- Pilih Nama Anggota --</option>
                    <?php
                    // Ambil data anggota untuk dropdown
                    $query_anggota = "SELECT id_anggota, nama_anggota FROM anggota ORDER BY nama_anggota ASC";
                    $result_anggota = mysqli_query($koneksi, $query_anggota);
                    while ($data_anggota = mysqli_fetch_assoc($result_anggota)) {
                        echo "<option value='" . $data_anggota['id_anggota'] . "'>" . htmlspecialchars($data_anggota['nama_anggota']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="jenis_simpanan">Jenis Simpanan</label>
                <select id="jenis_simpanan" name="jenis_simpanan" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="pokok">Simpanan Pokok</option>
                    <option value="wajib">Simpanan Wajib</option>
                    <option value="sukarela">Simpanan Sukarela</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="jumlah">Jumlah (Rp)</label>
                <input type="text" id="jumlah" name="jumlah" required placeholder="Contoh: 100000 (tanpa titik atau koma)">
            </div>

            <div class="form-group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="tgl_simpan">Tanggal Simpan</label>
                <input type="date" id="tgl_simpan" name="tgl_simpan" value="<?php echo date('Y-m-d'); // Default hari ini ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="data_simpanan.php" class="btn" style="background-color: #6c757d; color: white;">Batal</a>
        </form>

    </div>

<?php include 'template/footer.php';?>