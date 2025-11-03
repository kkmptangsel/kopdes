<?php
// /koperasi/admin/data_pinjaman.php
include '../config.php';
session_start(); 
include 'settings_helper.php';

// 🛑 1. CEK LOGIN HARUS DILAKUKAN DI SINI (SETELAH SESSION START, SEBELUM OUTPUT APAPUN)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // Gunakan notifikasi error sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    
    // Redirect ke halaman index (login page)
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN

$title = "Daftar Pinjaman Anggota | " . $SETTINGS['nama_koperasi'];
$page_heading = "Daftar Pinjaman Anggota";
include 'template/header.php';

?>
    <div class="block max-w-full p-6 bg-white dark:bg-gray-800 mt-5">
        <div class="relative overflow-x-auto">
            <a href="tambah_pinjaman.php" class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" type="button"><svg class="w-[14px] h-[14px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/></svg>
                <span class="sr-only">Tambah Anggota</span>
                <span class="ms-2 font-bold">Catat Pinjaman Baru</span>
            </a>
        
        <?php
        // 🛑 KOREKSI: Blok PHP yang menampilkan pesan notifikasi berbasis $_GET['pesan'] DIHAPUS.
        // Notifikasi akan ditangani oleh template/footer.php menggunakan SESSION.
        ?>

        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 mt-5  border border-gray-200 rounded-lg shadow-sm sm:rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">No</th>
                    <th scope="col" class="px-6 py-3">Nama Anggota</th>
                    <th scope="col" class="px-6 py-3">Jumlah Pinjam (Rp)</th>
                    <th scope="col" class="px-6 py-3">Tgl Pinjam</th>
                    <th scope="col" class="px-6 py-3">Tenggat Waktu</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query JOIN untuk mengambil data pinjaman dan nama anggota
                $query = "SELECT pinjaman.*, anggota.nama_anggota 
                          FROM pinjaman 
                          JOIN anggota ON pinjaman.id_anggota = anggota.id_anggota
                          ORDER BY pinjaman.tgl_pinjam DESC";
                                
                $result = mysqli_query($koneksi, $query);
                
                if (!$result) {
                    die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                }

                $no = 1;
                while ($data = mysqli_fetch_assoc($result)) {
                ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4"><?php echo $no++; ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['nama_anggota']); ?></td>
                        <td class="px-6 py-4"><?php echo number_format($data['jumlah_pinjaman'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4"><?php echo date('d-m-Y', strtotime($data['tgl_pinjam'])); ?></td>
                        <td class="px-6 py-4"><?php echo date('d-m-Y', strtotime($data['tenggat_waktu'])); ?></td>
                        <td class="px-6 py-4">
                            <?php 
                                $status_class = ($data['status'] == 'lunas') ? 'style="color:green; font-weight:bold;"' : 'style="color:red; font-weight:bold;"';
                                echo "<span $status_class>" . ucfirst($data['status']) . "</span>";
                            ?>
                        </td>
                        <td>
                            <a href="edit_pinjaman.php?id=<?php echo $data['id_pinjaman']; ?>" class="btn btn-edit">Edit</a>
                            
                            <a href="hapus_pinjaman.php?id=<?php echo $data['id_pinjaman']; ?>" class="btn btn-hapus" onclick="return confirm('PERINGATAN! Menghapus Pinjaman juga akan menghapus semua Angsuran terkait. Lanjutkan?')">Hapus</a>
                            
                            <br><small><a href="tambah_angsuran.php?pinjam_id=<?php echo $data['id_pinjaman']; ?>">+ Angsuran</a></small>
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