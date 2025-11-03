<?php
// /koperasi/admin/laporan_pengeluaran.php
include '../config.php';
session_start(); 

// 🛑 Pindah Cek sesi ke awal file, sebelum ada output (termasuk include header)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

include 'settings_helper.php';
$title = "Data Pengeluaran | " . $SETTINGS['nama_koperasi'];
$page_heading = "Data Pengeluaran Kas";
include 'template/header.php';

?>
    
    <a href="tambah_pengeluaran.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 mt-3 mb-3">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
        Tambah Pengeluaran
    </a>

    <?php 
    // 🛑 KOREKSI: Seluruh blok penanganan notifikasi $_GET['pesan'] DIHAPUS.
    // Notifikasi akan ditangani oleh template/footer.php menggunakan SESSION.
    ?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
    <div class="relative overflow-x-auto">
        <table class="w-full shadow-sm xl:rounded-lg sm:rounded-lg text-left rtl:text-right text-gray-500 dark:text-gray-400 mt-5">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Tanggal</th>
                    <th scope="col" class="px-6 py-3">Keterangan (Akun Beban)</th>
                    <th scope="col" class="px-6 py-3">Jumlah</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mengambil data pengeluaran
                $query = "SELECT * FROM pengeluaran ORDER BY tgl_pengeluaran DESC";
                $result = mysqli_query($koneksi, $query);
                
                if (!$result) {
                    die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                }

                while ($data = mysqli_fetch_assoc($result)) {
                ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['id_pengeluaran']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars(date('d-m-Y', strtotime($data['tgl_pengeluaran']))); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['keterangan']); ?></td>
                        <td class="px-6 py-4">Rp <?php echo number_format($data['jumlah'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4 flex items-center space-x-2">
                            <a href="edit_pengeluaran.php?id=<?php echo $data['id_pengeluaran']; ?>" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                            <a href="proses_hapus_pengeluaran.php?id=<?php echo $data['id_pengeluaran']; ?>" onclick="return confirm('Yakin ingin menghapus pengeluaran ini? Jurnal terkait juga akan dihapus.')" class="font-medium text-red-600 dark:text-red-500 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>

<?php include 'template/footer.php';?>