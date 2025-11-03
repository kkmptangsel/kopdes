<?php
// /koperasi/admin/tambah_angsuran.php
include '../config.php';
session_start();

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

// Cek ID Pinjaman
if (!isset($_GET['pinjam_id'])) {
    $_SESSION['notif_error'] = "ID Pinjaman tidak ditemukan. Kembali ke daftar pinjaman.";
    header("location:data_pinjaman.php");
    exit;
}

$id_pinjaman = (int)$_GET['pinjam_id'];

$query_pinjaman = "
    SELECT 
        p.id_pinjaman, p.jumlah_pinjaman, p.tgl_pinjam, 
        a.nama_anggota AS nama_anggota, a.id_anggota  
    FROM 
        pinjaman p
    JOIN 
        anggota a ON p.id_anggota = a.id_anggota
    WHERE 
        p.id_pinjaman = $id_pinjaman
";

$result_pinjaman = mysqli_query($koneksi, $query_pinjaman);
$data_pinjaman = mysqli_fetch_assoc($result_pinjaman);

if (!$data_pinjaman) {
    $_SESSION['notif_error'] = "Data pinjaman dengan ID #$id_pinjaman tidak ditemukan.";
    header("location:data_pinjaman.php");
    exit;
}

// Hitung Sisa Pinjaman dan Total Angsuran yang sudah dibayar
$q_total_angsuran = mysqli_query($koneksi, "SELECT SUM(jumlah_angsuran) as total_dibayar FROM angsuran WHERE id_pinjaman = $id_pinjaman");
$d_total_angsuran = mysqli_fetch_assoc($q_total_angsuran);
$total_dibayar = (float)($d_total_angsuran['total_dibayar'] ?? 0);
$sisa_pinjaman = $data_pinjaman['jumlah_pinjaman'] - $total_dibayar;

if ($sisa_pinjaman <= 0) {
    $_SESSION['notif_sukses'] = "Pinjaman ID #$id_pinjaman atas nama **{$data_pinjaman['nama_anggota']}** sudah LUNAS.";
    header("location:data_pinjaman.php");
    exit;
}

include 'settings_helper.php';
$title = "Bayar Angsuran | " . $SETTINGS['nama_koperasi'];
$page_heading = "Form Pembayaran Angsuran";
include 'template/header.php';
?>

<div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800">
    <div class="mb-6 border-b pb-4">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Pinjaman</h4>
        <p class="text-sm text-gray-700 dark:text-gray-400">Anggota: **<?php echo htmlspecialchars($data_pinjaman['nama_anggota']); ?>** (ID: <?php echo $data_pinjaman['id_anggota']; ?>)</p>
        <p class="text-sm text-gray-700 dark:text-gray-400">Jumlah Pinjaman: Rp <?php echo number_format($data_pinjaman['jumlah_pinjaman'], 0, ',', '.'); ?></p>
        <p class="text-sm text-gray-700 dark:text-gray-400 font-bold text-red-600">Sisa Pinjaman: Rp <?php echo number_format($sisa_pinjaman, 0, ',', '.'); ?></p>
    </div>

    <form action="proses_angsuran.php" method="POST">
        <input type="hidden" name="id_pinjaman" value="<?php echo $id_pinjaman; ?>">
        <input type="hidden" name="id_anggota" value="<?php echo $data_pinjaman['id_anggota']; ?>">
        <input type="hidden" name="sisa_pinjaman_sekarang" value="<?php echo $sisa_pinjaman; ?>">

        <div class="mb-4">
            <label for="tgl_bayar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Pembayaran</label>
            <input type="date" id="tgl_bayar" name="tgl_bayar" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="mb-4">
            <label for="jumlah_bayar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah Dibayar (Rp)</label>
            <input type="number" id="jumlah_bayar" name="jumlah_bayar" min="1" step="any" max="<?php echo (int)$sisa_pinjaman; ?>" 
                   placeholder="Maksimal: <?php echo number_format($sisa_pinjaman, 0, ',', '.'); ?>"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>
        
        <div class="mb-4">
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan (Opsional)</label>
            <input type="text" id="keterangan" name="keterangan" placeholder="Angsuran ke-X atau pelunasan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        </div>

        <button type="submit" name="action" value="tambah_angsuran" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Catat Pembayaran</button>
        <a href="data_pinjaman.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">Batal</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>