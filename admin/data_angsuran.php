<?php
// /koperasi/admin/data_angsuran.php
include '../config.php';
session_start();

include 'settings_helper.php';
$title = "Data Angsuran | " . $SETTINGS['nama_koperasi'];
$page_heading = "Riwayat Pembayaran Angsuran";
include 'template/header.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}
?>


<!-- Breadcrumb -->
<nav class="flex px-5 py-3 text-gray-700 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700 mt-5" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
    <li class="inline-flex items-center">
      <a href="dashboard.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
        Dashboard
      </a>
    </li>
    <li>
      <div class="flex items-center">
        <svg class="rtl:rotate-180 block w-3 h-3 mx-1 text-gray-400 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <a href="data_pinjaman.php" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2 dark:text-gray-400 dark:hover:text-white">Daftar Pinjaman</a>
      </div>
    </li>
    <li aria-current="page">
      <div class="flex items-center">
        <svg class="rtl:rotate-180  w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Riwayat Pembayaran Angsuran</span>
      </div>
    </li>
  </ol>
</nav>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
    <div class="relative overflow-x-auto">
        <table class="w-full shadow-sm xl:rounded-lg sm:rounded-lg text-left rtl:text-right text-gray-500 dark:text-gray-400 mt-5">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">ID Angsuran</th>
                    <th scope="col" class="px-6 py-3">Tgl. Bayar</th>
                    <th scope="col" class="px-6 py-3">Anggota & Pinjaman ID</th>
                    <th scope="col" class="px-6 py-3">Jumlah Angsuran</th>
                    <th scope="col" class="px-6 py-3">Keterangan</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mengambil data angsuran dengan join ke tabel pinjaman dan anggota
                $query = "SELECT 
                            a.id_angsuran, a.tgl_bayar, a.jumlah_angsuran, a.keterangan,
                            p.id_pinjaman, p.jumlah_pinjaman,
                            ag.nama_anggota
                          FROM 
                            angsuran a
                          JOIN 
                            pinjaman p ON a.id_pinjaman = p.id_pinjaman
                          JOIN
                            anggota ag ON p.id_anggota = ag.id_anggota
                          ORDER BY 
                            a.tgl_bayar DESC, a.id_angsuran DESC";
                            
                $result = mysqli_query($koneksi, $query);
                
                if (!$result) {
                    die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                }

                while ($data = mysqli_fetch_assoc($result)) {
                ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['id_angsuran']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars(date('d-m-Y', strtotime($data['tgl_bayar']))); ?></td>
                        <td class="px-6 py-4">
                            <?php echo htmlspecialchars($data['nama_anggota']); ?><br>
                            <small class="text-gray-400">Pinj. ID: #<?php echo htmlspecialchars($data['id_pinjaman']); ?></small>
                        </td>
                        <td class="px-6 py-4 font-medium text-green-600">
                            Rp <?php echo number_format($data['jumlah_angsuran'], 0, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['keterangan'] ?? '-'); ?></td>
                        <td class="px-6 py-4 flex items-center space-x-2">
                            <a href="edit_angsuran.php?id=<?php echo $data['id_angsuran']; ?>" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                            
                            <a href="proses_hapus_angsuran.php?id=<?php echo $data['id_angsuran']; ?>" 
                               onclick="return confirm('Yakin ingin menghapus angsuran ini? Jurnal terkait akan dibatalkan, dan sisa pinjaman akan bertambah.')" 
                               class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                Hapus
                            </a>
<a href="kwitansi_angsuran.php?id=<?php echo $data['id_angsuran']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs">
    Cetak Kwitansi
</a>
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