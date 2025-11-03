<?php
// /koperasi/admin/edit_pengeluaran.php
include '../config.php';
session_start(); 

include 'settings_helper.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

if (!isset($_GET['id'])) {
    header("location:laporan_pengeluaran.php");
    exit;
}

$id_pengeluaran = (int)$_GET['id'];
$query_data = mysqli_query($koneksi, "SELECT * FROM pengeluaran WHERE id_pengeluaran=$id_pengeluaran");
if (mysqli_num_rows($query_data) == 0) {
    header("location:laporan_pengeluaran.php?pesan=not_found");
    exit;
}
$data = mysqli_fetch_assoc($query_data);

// Ambil daftar akun Beban dari COA
$query_coa = "SELECT no_akun, nama_akun FROM coa WHERE kategori='BEBAN' OR no_akun LIKE '5%' ORDER BY no_akun ASC";
$result_coa = mysqli_query($koneksi, $query_coa);

$title = "Edit Pengeluaran | " . $SETTINGS['nama_koperasi'];
$page_heading = "Edit Pengeluaran: ID " . $id_pengeluaran;
include 'template/header.php';
?>

<h2 class="text-4xl font-extrabold dark:text-white mb-5"><?php echo htmlspecialchars($page_heading); ?></h2>
<div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800">
    
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_edit'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">Gagal memperbarui pengeluaran. Cek data dan jurnal: <?php echo htmlspecialchars($_GET['error'] ?? ''); ?></span>
        </div>
    <?php endif; ?>

    <form action="proses_pengeluaran.php" method="POST">
        <input type="hidden" name="id_pengeluaran" value="<?php echo htmlspecialchars($data['id_pengeluaran']); ?>">
        <input type="hidden" name="tgl_lama" value="<?php echo htmlspecialchars($data['tgl_pengeluaran']); ?>">
        
        <div class="mb-4">
            <label for="tgl_pengeluaran" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal</label>
            <input type="date" id="tgl_pengeluaran" name="tgl_pengeluaran" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="<?php echo htmlspecialchars($data['tgl_pengeluaran']); ?>" required>
        </div>

        <div class="mb-4">
            <label for="no_akun_beban" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Akun Beban (COA)</label>
            <select id="no_akun_beban" name="no_akun_beban" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <?php while ($data_coa = mysqli_fetch_assoc($result_coa)): 
                    $selected = ($data_coa['no_akun'] == $data['no_akun']) ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($data_coa['no_akun']); ?>" <?= $selected ?>>
                        <?php echo htmlspecialchars($data_coa['no_akun']) . ' - ' . htmlspecialchars($data_coa['nama_akun']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan/Deskripsi</label>
            <input type="text" id="keterangan" name="keterangan" value="<?php echo htmlspecialchars($data['keterangan']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>

        <div class="mb-4">
            <label for="jumlah" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah (Rp)</label>
            <input type="number" id="jumlah" name="jumlah" min="1" step="any" value="<?php echo htmlspecialchars($data['jumlah']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>

        <button type="submit" name="action" value="edit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Simpan Perubahan</button>
        <a href="laporan_pengeluaran.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">Batal</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>