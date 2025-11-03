<?php
// /koperasi/admin/riwayat_peminjam.php
include '../config.php';
session_start();
include 'settings_helper.php'; // Sesuaikan path jika berbeda

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

// 1. Ambil NIK dari URL
if (!isset($_GET['nik']) || empty($_GET['nik'])) {
    die("NIK (No. KTP) anggota tidak ditemukan.");
}
$nik_anggota = mysqli_real_escape_string($koneksi, $_GET['nik']);

// 2. Ambil Data Anggota
$q_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE no_ktp = '$nik_anggota'");
$d_anggota = mysqli_fetch_assoc($q_anggota);

if (!$d_anggota) {
    die("Anggota dengan NIK tersebut tidak ditemukan.");
}

$id_anggota = $d_anggota['id_anggota'];
$nama_anggota = htmlspecialchars($d_anggota['nama_anggota']);

$title = "Riwayat Pinjaman " . $nama_anggota . " | " . $SETTINGS['nama_koperasi'];
$page_heading = "Riwayat Pinjaman Anggota: " . $nama_anggota;

include 'template/header.php';

// Fungsi untuk menghitung Sisa Pinjaman (Sama seperti di data_pinjaman.php)
function getSisaPinjaman($koneksi, $id_pinjaman, $jumlah_pinjaman) {
    $q_total_angsuran = mysqli_query($koneksi, "SELECT SUM(jumlah_angsuran) as total_dibayar FROM angsuran WHERE id_pinjaman = $id_pinjaman");
    $d_total_angsuran = mysqli_fetch_assoc($q_total_angsuran);
    $total_dibayar = (float)($d_total_angsuran['total_dibayar'] ?? 0);
    return $jumlah_pinjaman - $total_dibayar;
}

?>

<a href="data_pinjaman.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 mt-3 mb-5">
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
    Kembali ke Daftar Pinjaman
</a>

<div class="block max-w-full p-6 bg-white border border-gray-200 shadow-sm dark:bg-gray-800">

    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Riwayat Pinjaman Anggota</h3>
    <p class="mb-5 text-gray-700 dark:text-gray-300">Berikut adalah daftar semua pinjaman yang pernah dilakukan oleh **<?php echo $nama_anggota; ?>**.</p>
    
<div class="flex justify-between mt-5 mb-5">
  <div>01</div>
  <div>02</div>
  <div>03</div>
</div>

<h3 class="text-center font-mono text-4xl font-extrabold">Bukti Pembayaran Angsuran</h3>

 <div class="relative overflow-x-auto mb-5">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <tbody>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Kode Pinjaman
                </th>
                <td class="px-6 py-1">
                    1
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Jenis Angsuran
                </th>
                <td class="px-6 py-1">
                    1
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Nama Peminjam
                </th>
                <td class="px-6 py-1">
                    1
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Jumlah Angsuran
                </th>
                <td class="px-6 py-1">
                    12 x
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Tanggal
                </th>
                <td class="px-6 py-1">
                    1
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Angsuran Ke
                </th>
                <td class="px-6 py-1">
                    1
                </td>
            </tr>
        </tbody>
    </table>
 </div>


    <?php
    // 3. Ambil Semua Riwayat Pinjaman Anggota
    $q_pinjaman = mysqli_query($koneksi, "SELECT * FROM pinjaman WHERE id_anggota = $id_anggota ORDER BY tgl_pinjam DESC");

    if (mysqli_num_rows($q_pinjaman) == 0) {
        echo "<div class='p-4 bg-yellow-100 text-yellow-800 rounded-lg'>Anggota ini belum memiliki riwayat pinjaman.</div>";
    } else {
        $counter = 1;
        while ($d_pinjaman = mysqli_fetch_assoc($q_pinjaman)) {
            $id_pinjaman = $d_pinjaman['id_pinjaman'];
            $sisa_pinjaman = getSisaPinjaman($koneksi, $id_pinjaman, $d_pinjaman['jumlah_pinjaman']);
            $status_lunas = ($sisa_pinjaman <= 0) ? true : false;
            $status_text = $status_lunas ? 'LUNAS' : ucfirst($d_pinjaman['status']);
            $status_color = $status_lunas ? 'text-green-600' : 'text-red-600';
            
            // 4. Ambil Riwayat Angsuran untuk Pinjaman Ini
            $q_angsuran = mysqli_query($koneksi, "SELECT * FROM angsuran WHERE id_pinjaman = $id_pinjaman ORDER BY tgl_bayar ASC");
            $total_angsuran_dibayar = mysqli_num_rows($q_angsuran);
            
            ?>
            <div class="bg-gray-50 border border-gray-300 p-4 rounded-lg shadow-sm mb-6 dark:bg-gray-700 dark:border-gray-600">
                <h4 class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                    Pinjaman Ke-<?php echo $counter++; ?> (ID: <?php echo htmlspecialchars($d_pinjaman['kode_pinjaman']); ?>)
                </h4>
                
                <table class="w-full text-sm text-gray-700 dark:text-gray-300 mt-2 mb-4">
                    <tr>
                        <td class="w-1/3 py-1">Jumlah Pinjaman</td>
                        <td class="w-2/3 py-1 font-bold">: Rp <?php echo number_format($d_pinjaman['jumlah_pinjaman'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="py-1">Tanggal Pinjam</td>
                        <td class="py-1">: <?php echo date('d-m-Y', strtotime($d_pinjaman['tgl_pinjam'])); ?></td>
                    </tr>
                    <tr>
                        <td class="py-1">Tipe / Lama Angsuran</td>
                        <td class="py-1">: <?php echo htmlspecialchars($d_pinjaman['tipe_pinjaman']); ?> / <?php echo htmlspecialchars($d_pinjaman['lama_angsuran']); ?> Kali</td>
                    </tr>
                    <tr>
                        <td class="py-1">Sisa Pinjaman</td>
                        <td class="py-1 font-bold text-red-600 dark:text-red-400">: Rp <?php echo number_format($sisa_pinjaman, 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="py-1">Status Pinjaman</td>
                        <td class="py-1 font-bold <?php echo $status_color; ?>">: <?php echo $status_text; ?></td>
                    </tr>
                </table>

                <h5 class="font-semibold mt-4 mb-2 text-gray-800 dark:text-gray-100">Riwayat Pembayaran Angsuran (Total: <?php echo $total_angsuran_dibayar; ?>x)</h5>
                
                <?php if (mysqli_num_rows($q_angsuran) == 0): ?>
                    <p class="text-sm text-gray-500 italic dark:text-gray-400">Belum ada pembayaran angsuran untuk pinjaman ini.</p>
                <?php else: ?>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-600 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-3 py-2">Angsuran Ke-</th>
                                    <th scope="col" class="px-3 py-2">Tgl. Bayar</th>
                                    <th scope="col" class="px-3 py-2">Jumlah Bayar</th>
                                    <th scope="col" class="px-3 py-2">Keterangan</th>
                                    <th scope="col" class="px-3 py-2">Kwitansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $angsuran_ke = 1;
                                while ($d_angsuran = mysqli_fetch_assoc($q_angsuran)): ?>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-3 py-2"><?php echo $angsuran_ke++; ?></td>
                                    <td class="px-3 py-2"><?php echo date('d-m-Y', strtotime($d_angsuran['tgl_bayar'])); ?></td>
                                    <td class="px-3 py-2 font-medium text-green-600">Rp <?php echo number_format($d_angsuran['jumlah_angsuran'], 0, ',', '.'); ?></td>
                                    <td class="px-3 py-2"><?php echo htmlspecialchars($d_angsuran['keterangan'] ?? '-'); ?></td>
                                    <td class="px-3 py-2">
                                        <a href="kwitansi_angsuran.php?id=<?php echo $d_angsuran['id_angsuran']; ?>" target="_blank" class="text-blue-600 hover:underline text-xs">Cetak</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
            <?php
        }
    }
    ?>

</div>

<?php include 'template/footer.php'; ?>