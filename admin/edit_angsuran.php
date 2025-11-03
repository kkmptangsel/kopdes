<?php
// /koperasi/admin/edit_angsuran.php
include '../config.php';
session_start();

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

// Cek ID Angsuran
if (!isset($_GET['id'])) {
    $_SESSION['notif_error'] = "ID Angsuran tidak ditemukan.";
    header("location:data_angsuran.php");
    exit;
}

$id_angsuran = (int)$_GET['id'];

// Ambil data Angsuran, Pinjaman, dan Anggota
$query_angsuran = "
    SELECT 
        a.*, 
        p.id_pinjaman, p.jumlah_pinjaman,
        ag.nama_anggota
    FROM 
        angsuran a
    JOIN 
        pinjaman p ON a.id_pinjaman = p.id_pinjaman
    JOIN
        anggota ag ON p.id_anggota = ag.id_anggota
    WHERE 
        a.id_angsuran = $id_angsuran
";
$result_angsuran = mysqli_query($koneksi, $query_angsuran);
$data_angsuran = mysqli_fetch_assoc($result_angsuran);

if (!$data_angsuran) {
    $_SESSION['notif_error'] = "Data angsuran dengan ID #$id_angsuran tidak ditemukan.";
    header("location:data_angsuran.php");
    exit;
}

// Hitung Sisa Pinjaman sebelum dan sesudah angsuran yang sedang diedit
$id_pinjaman = $data_angsuran['id_pinjaman'];
$jumlah_angsuran_lama = $data_angsuran['jumlah_angsuran'];

$q_total_angsuran_semua = mysqli_query($koneksi, "SELECT SUM(jumlah_angsuran) as total_dibayar FROM angsuran WHERE id_pinjaman = $id_pinjaman");
$d_total_angsuran_semua = mysqli_fetch_assoc($q_total_angsuran_semua);
$total_dibayar_semua = (float)($d_total_angsuran_semua['total_dibayar'] ?? 0);

// Sisa pinjaman sebelum angsuran yang sedang diedit dicatat (Ini adalah batasan maksimum edit)
$total_dibayar_tanpa_ini = $total_dibayar_semua - $jumlah_angsuran_lama;
$sisa_pinjaman_maksimal = $data_angsuran['jumlah_pinjaman'] - $total_dibayar_tanpa_ini;


include 'settings_helper.php';
$title = "Edit Angsuran | " . $SETTINGS['nama_koperasi'];
$page_heading = "Form Edit Pembayaran Angsuran";
include 'template/header.php';
?>

<div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800">
    <div class="mb-6 border-b pb-4">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Pinjaman</h4>
        <p class="text-sm text-gray-700 dark:text-gray-400">Anggota: **<?php echo htmlspecialchars($data_angsuran['nama_anggota']); ?>**</p>
        <p class="text-sm text-gray-700 dark:text-gray-400">Pinjaman Awal: Rp <?php echo number_format($data_angsuran['jumlah_pinjaman'], 0, ',', '.'); ?></p>
        <p class="text-sm text-gray-700 dark:text-gray-400 font-bold text-red-600">Sisa Pinjaman yang Belum Terbayar (Maksimal Edit): Rp <?php echo number_format($sisa_pinjaman_maksimal, 0, ',', '.'); ?></p>
    </div>

    <form action="proses_edit_angsuran.php" method="POST">
        <input type="hidden" name="id_angsuran" value="<?php echo $id_angsuran; ?>">
        <input type="hidden" name="id_pinjaman" value="<?php echo $id_pinjaman; ?>">
        <input type="hidden" name="jumlah_angsuran_lama" value="<?php echo $jumlah_angsuran_lama; ?>">
        <input type="hidden" name="sisa_pinjaman_maksimal" value="<?php echo $sisa_pinjaman_maksimal; ?>">

        <div class="mb-4">
            <label for="tgl_bayar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Pembayaran</label>
            <input type="date" id="tgl_bayar" name="tgl_bayar" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                   value="<?php echo date('Y-m-d', strtotime($data_angsuran['tgl_bayar'])); ?>" required>
        </div>

        <div class="mb-4">
            <label for="jumlah_angsuran_baru" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah Dibayar (Rp)</label>
            <input type="number" id="jumlah_angsuran_baru" name="jumlah_angsuran_baru" min="1" step="any" max="<?php echo (int)$sisa_pinjaman_maksimal; ?>" 
                   value="<?php echo (int)$jumlah_angsuran_lama; ?>"
                   placeholder="Maksimal: <?php echo number_format($sisa_pinjaman_maksimal, 0, ',', '.'); ?>"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>
        
        <div class="mb-4">
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan (Opsional)</label>
            <input type="text" id="keterangan" name="keterangan" value="<?php echo htmlspecialchars($data_angsuran['keterangan'] ?? ''); ?>" placeholder="Angsuran ke-X atau pelunasan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        </div>

        <button type="submit" name="action" value="edit_angsuran" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Simpan Perubahan</button>
        <a href="data_angsuran.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">Batal</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>