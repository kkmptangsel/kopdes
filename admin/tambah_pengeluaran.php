<?php
// /koperasi/admin/tambah_pengeluaran.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

include 'settings_helper.php';
$title = "Tambah Pengeluaran | " . $SETTINGS['nama_koperasi'];
$page_heading = "Form Tambah Pengeluaran";
include 'template/header.php'; // Header harus di-include setelah cek sesi

// Ambil daftar akun Beban dari COA (posisi Beban biasanya dimulai dengan 5xx atau sesuai kebijakan)
$query_coa = "SELECT no_akun, nama_akun FROM coa WHERE kategori='BEBAN' OR no_akun LIKE '5%' ORDER BY no_akun ASC";
$result_coa = mysqli_query($koneksi, $query_coa);
?>

<div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800">
    
    <?php 
    // 🛑 KOREKSI: Blok PHP ini Dihapus. Notifikasi error dari proses_pengeluaran.php
    // kini akan ditampilkan oleh template/footer.php melalui $_SESSION['notif_error'].
    /*
    if (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_tambah'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">Gagal menambahkan pengeluaran. Akun Kas/Beban mungkin tidak ditemukan, atau error DB: <?php echo htmlspecialchars($_GET['error'] ?? ''); ?></span>
        </div>
    <?php endif; 
    */
    ?>

    <form action="proses_pengeluaran.php" method="POST">
        <div class="mb-4">
            <label for="tgl_pengeluaran" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal</label>
            <input type="date" id="tgl_pengeluaran" name="tgl_pengeluaran" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="mb-4">
            <label for="no_akun_beban" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Akun Beban (COA)</label>
            <select id="no_akun_beban" name="no_akun_beban" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <option value="">Pilih Akun Beban</option>
                <?php while ($data_coa = mysqli_fetch_assoc($result_coa)): ?>
                    <option value="<?php echo htmlspecialchars($data_coa['no_akun']); ?>">
                        <?php echo htmlspecialchars($data_coa['no_akun']) . ' - ' . htmlspecialchars($data_coa['nama_akun']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan/Deskripsi</label>
            <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Bayar listrik kantor bulan ini" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>

        <div class="mb-4">
            <label for="jumlah" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah (Rp)</label>
            <input type="number" id="jumlah" name="jumlah" min="1" step="any" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>

        <button type="submit" name="action" value="tambah" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Simpan Pengeluaran</button>
        <a href="laporan_pengeluaran.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">Batal</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>