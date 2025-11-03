<?php
// /koperasi/admin/data_pinjaman.php
include '../config.php';
session_start();
include 'settings_helper.php';

// 🛑 1. CEK LOGIN
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}
// 🛑 AKHIR CEK LOGIN

$title = "Daftar Pinjaman Anggota | " . $SETTINGS['nama_koperasi'];
$page_heading = "Daftar Pinjaman";

// Fungsi untuk menghitung sisa pinjaman (diperlukan untuk menampilkan status yang akurat)
function getSisaPinjaman($koneksi, $id_pinjaman, $jumlah_pinjaman) {
    $q_total_angsuran = mysqli_query($koneksi, "SELECT SUM(jumlah_angsuran) as total_dibayar FROM angsuran WHERE id_pinjaman = $id_pinjaman");
    $d_total_angsuran = mysqli_fetch_assoc($q_total_angsuran);
    $total_dibayar = (float)($d_total_angsuran['total_dibayar'] ?? 0);
    return $jumlah_pinjaman - $total_dibayar;
}

include 'template/header.php';
?>

<!-- Breadcrumb -->
<nav class="flex px-5 py-3 text-gray-700 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700 mt-5" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
    <li class="inline-flex items-center">
      <a href="dashboard.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
        Dashboard
      </a>
    </li>
    <li aria-current="page">
      <div class="flex items-center">
        <svg class="rtl:rotate-180  w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Daftar Pinjaman</span>
      </div>
    </li>
  </ol>
</nav>

<div class="block max-w-full p-6 bg-white dark:bg-gray-800 mt-5 rounded-lg">

<div class="grid grid-cols-2 gap-4 mb-5">
        <?php
        $q_simpanan = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total_simpanan FROM simpanan");
        $d_simpanan = mysqli_fetch_assoc($q_simpanan);
        $q_pinjaman = mysqli_query($koneksi, "SELECT SUM(jumlah_pinjaman) AS total_pinjaman FROM pinjaman");
        $d_pinjaman = mysqli_fetch_assoc($q_pinjaman);
        ?>
 <div class="max-w-full p-3 bg-yellow-100 border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Total Kas</h5>
    <p class="mb-1font-normal text-gray-700 dark:text-gray-400">Rp.<?php echo number_format($d_simpanan['total_simpanan'], 0, ',', '.'); ?></p>
 </div>
 <div class="max-w-full p-3 bg-blue-50 border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-1xl font-bold tracking-tight text-gray-900 dark:text-white">Total Pinjaman</h5>
    <p class="mb-1 font-normal text-gray-700 dark:text-gray-400">Rp.<?php echo number_format($d_pinjaman['total_pinjaman'], 0, ',', '.'); ?></p>
 </div>
</div>

        <div class="relative overflow-x-auto">
            <a href="tambah_pinjaman.php" class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 mb-2" type="button"><svg class="w-[14px] h-[14px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/></svg>
                <span class="sr-only">Tambah Anggota</span>
                <span class="ms-2 font-bold">Catat Pinjaman Baru</span>
            </a>

            <a href="data_angsuran.php" class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" type="button">
                <span class="ms-2 font-bold">Riwayat Pembayaran Angsuran</span>
            </a>

        <?php
        // Menampilkan pesan notifikasi (Perlu dikonversi ke Session Flash Message di masa depan)
        // ... (Kode Notifikasi Lama Dibiarkan di sini Sesuai Permintaan User) ...
        if (isset($_GET['pesan'])) {
            $pesan = $_GET['pesan'];
            $style = "color:green; background-color: #e6ffe6; padding: 10px; border: 1px solid green; border-radius: 5px; margin-bottom: 1rem;";
            $teks = "";

            if ($pesan == "tambah_sukses") $teks = "Pinjaman baru berhasil dicatat.";
            if ($pesan == "edit_sukses") $teks = "Data pinjaman berhasil diupdate.";
            if ($pesan == "hapus_sukses") $teks = "Data pinjaman berhasil dihapus.";
            
            if ($pesan == "tambah_gagal" || $pesan == "edit_gagal" || $pesan == "hapus_gagal") {
                $style = "color:red; background-color: #ffeeee; padding: 10px; border: 1px solid red; border-radius: 5px; margin-bottom: 1rem;";
                $teks = "Terjadi kesalahan pada operasi database.";
            }
            
            if ($teks) echo "<div style='$style'>$teks</div>";
        }
        ?>

        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 mt-5  border border-gray-200 rounded-lg shadow-sm sm:rounded-lg">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">No</th>
                    <th scope="col" class="px-6 py-3">ID Transaksi</th> 
                    <th scope="col" class="px-6 py-3">Nama Anggota</th>
                    <th scope="col" class="px-6 py-3">Jenis Angsuran</th> 
                    <th scope="col" class="px-6 py-3">Jml. Angsuran</th> 
                    <th scope="col" class="px-6 py-3">Jml Pinjam (Rp)</th>
                    <th scope="col" class="px-6 py-3">Tgl Pinjam</th>
                    <th scope="col" class="px-6 py-3">Tenggang Waktu</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                    <th scope="col" class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query JOIN untuk mengambil data pinjaman dan nama anggota
                $query = "SELECT pinjaman.*, anggota.nama_anggota, anggota.no_ktp 
                              FROM pinjaman 
                              JOIN anggota ON pinjaman.id_anggota = anggota.id_anggota
                              ORDER BY pinjaman.tgl_pinjam DESC";
                              
                $result = mysqli_query($koneksi, $query);
                
                if (!$result) {
                    die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                }

                $no = 1;
                while ($data = mysqli_fetch_assoc($result)) {
                    
                    // 🛑 KOREKSI: Hitung Sisa Pinjaman
                    $sisa_pinjaman = getSisaPinjaman($koneksi, $data['id_pinjaman'], $data['jumlah_pinjaman']);
                    
                    // 🛑 Tentukan status yang ditampilkan secara dinamis
                    $status_tampil = ($sisa_pinjaman <= 0) ? 'LUNAS' : ucfirst($data['status']);
                    $status_class = ($status_tampil == 'LUNAS') ? 'style="color:green; font-weight:bold;"' : 'style="color:red; font-weight:bold;"';

                ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4"><?php echo $no++; ?></td>
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200 text-xs">
                         <?php echo htmlspecialchars($data['kode_pinjaman']); ?>
                        </td>
                        <td class="px-6 py-4"><a href="riwayat_peminjam.php?nik=<?php echo htmlspecialchars($data['no_ktp']); ?>" class="font-medium text-blue-600 dark:text-blue-500 hover:underline"><?php echo htmlspecialchars($data['nama_anggota']); ?></a></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['tipe_pinjaman']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['lama_angsuran']); ?></td>
                        <td class="px-6 py-4"><?php echo number_format($data['jumlah_pinjaman'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4"><?php echo date('d-m-Y', strtotime($data['tgl_pinjam'])); ?></td>
                        <td class="px-6 py-4"><?php echo date('d-m-Y', strtotime($data['tenggat_waktu'])); ?></td>
                        <td class="px-6 py-4">
                            <?php
                                // 🛑 Tampilkan status dinamis
                                echo "<span $status_class>" . $status_tampil . "</span>";
                            ?>
                        </td>
                        <td>
                            <a type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mb-2" href="edit_pinjaman.php?id=<?php echo $data['id_pinjaman']; ?>">Edit</a>
                            
                            <a type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 mb-2" href="hapus_pinjaman.php?id=<?php echo $data['id_pinjaman']; ?>" onclick="return confirm('PERINGATAN! Menghapus Pinjaman juga akan menghapus semua Angsuran terkait. Lanjutkan?')">Hapus</a>
                            
                            <br>
                            <?php if ($sisa_pinjaman > 0): ?>
                                <a type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 mb-2 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800" href="tambah_angsuran.php?pinjam_id=<?php echo $data['id_pinjaman']; ?>">Bayar</a>
                            <?php else: ?>
                              <button type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-green-400 dark:bg-green-500 cursor-not-allowed rounded-lg" disabled>Bayar</button>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <a href="riwayat_peminjam.php?nik=<?php echo htmlspecialchars($data['no_ktp']); ?>"><svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></a>
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