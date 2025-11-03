<?php
// /koperasi/admin/edit_coa.php
include '../config.php';
include 'settings_helper.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message untuk cek sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if (!isset($_GET['no'])) {
    // 🛑 KOREKSI: Gunakan Session Flash Message jika ID tidak ada
    $_SESSION['notif_error'] = "Nomor Akun (COA) tidak ditemukan.";
    header("location:laporan_coa.php");
    exit;
}

$no_akun = mysqli_real_escape_string($koneksi, $_GET['no']);

$query_data = mysqli_query($koneksi, "SELECT * FROM coa WHERE no_akun='$no_akun'");
if (mysqli_num_rows($query_data) == 0) {
    // 🛑 KOREKSI: Gunakan Session Flash Message jika data tidak ditemukan
    $_SESSION['notif_error'] = "Data Akun COA tidak ditemukan.";
    header("location:laporan_coa.php");
    exit;
}
$data = mysqli_fetch_assoc($query_data);

$title = "Edit COA | " . $SETTINGS['nama_koperasi'];
$page_heading = "Edit Akun COA: " . $data['nama_akun'];
include 'template/header.php';
?>

<h2 class="text-4xl font-extrabold dark:text-white mb-5"><?php echo htmlspecialchars($page_heading); ?></h2>
<div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800">
    
    <form action="proses_coa.php" method="POST">
        <input type="hidden" name="no_akun_lama" value="<?php echo htmlspecialchars($data['no_akun']); ?>">
        
        <div class="mb-4">
            <label for="no_akun_baru" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. Akun Baru</label>
            <input type="text" id="no_akun_baru" name="no_akun_baru" value="<?php echo htmlspecialchars($data['no_akun']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Penting: Mengubah No. Akun akan mempengaruhi semua jurnal terkait.</p>
        </div>
        
        <div class="mb-4">
            <label for="nama_akun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Akun</label>
            <input type="text" id="nama_akun" name="nama_akun" value="<?php echo htmlspecialchars($data['nama_akun']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>
        
        <div class="mb-4">
            <label for="kategori" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kategori</label>
            <select id="kategori" name="kategori" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <option value="ASET" <?php echo ($data['kategori'] == 'ASET') ? 'selected' : ''; ?>>ASET (Harta)</option>
                <option value="LIABILITAS" <?php echo ($data['kategori'] == 'LIABILITAS') ? 'selected' : ''; ?>>LIABILITAS (Kewajiban)</option>
                <option value="EKUITAS" <?php echo ($data['kategori'] == 'EKUITAS') ? 'selected' : ''; ?>>EKUITAS (Modal)</option>
                <option value="PENDAPATAN" <?php echo ($data['kategori'] == 'PENDAPATAN') ? 'selected' : ''; ?>>PENDAPATAN</option>
                <option value="BEBAN" <?php echo ($data['kategori'] == 'BEBAN') ? 'selected' : ''; ?>>BEBAN</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label for="posisi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Saldo Normal</label>
            <select id="posisi" name="posisi" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <option value="Debit" <?php echo ($data['posisi'] == 'Debit') ? 'selected' : ''; ?>>Debit</option>
                <option value="Kredit" <?php echo ($data['posisi'] == 'Kredit') ? 'selected' : ''; ?>>Kredit</option>
            </select>
        </div>
        
        <button type="submit" name="action" value="edit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Simpan Perubahan</button>
        <a href="laporan_coa.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">Batal</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>