<?php
// /koperasi/admin/laporan_buku_besar.php
session_start(); 
include '../config.php';
include 'settings_helper.php';

// Cek sesi (Hanya admin)
// 🛑 1. CEK LOGIN HARUS DILAKUKAN DI SINI (SETELAH SESSION START, SEBELUM OUTPUT APAPUN)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // Gunakan notifikasi error sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    
    // Redirect ke halaman index (login page)
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN

$title = "Buku Besar | " . $SETTINGS['nama_koperasi'];
$page_heading = "Buku Besar";
include 'template/header.php';
?>

    <div class="block max-w-full p-0 mt-5">
        
        <?php
        // 1. Ambil daftar semua akun dari COA
        $query_coa = "SELECT * FROM coa ORDER BY no_akun ASC";
        $result_coa = mysqli_query($koneksi, $query_coa);

        if (!$result_coa) {
            die("Query COA Error: " . mysqli_error($koneksi));
        }

        while ($akun = mysqli_fetch_assoc($result_coa)) {
            $no_akun = $akun['no_akun'];
            $nama_akun = $akun['nama_akun'];
            $posisi_saldo = $akun['posisi']; // DEBIT atau KREDIT
            $saldo_berjalan = 0; // Inisialisasi saldo awal (asumsi 0)

            // 2. Ambil semua transaksi Jurnal Umum untuk akun ini
            $query_jurnal = "SELECT * FROM jurnal_umum WHERE no_akun = '$no_akun' ORDER BY tgl_transaksi ASC, id_jurnal ASC";
            $result_jurnal = mysqli_query($koneksi, $query_jurnal);


            echo "<div class='relative overflow-x-auto shadow-md sm:rounded-lg mt-5'><table class='w-full text-left rtl:text-right text-gray-500 dark:text-gray-400'>";
            echo "<caption class='p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-white dark:text-white dark:bg-gray-800'>Akun: " . htmlspecialchars($no_akun) . " - " . htmlspecialchars($nama_akun) . "</caption>";
            echo "<thead class='text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400'>
                    <tr>
                        <th scope='col' class='px-6 py-3'>Tanggal</th>
                        <th scope='col' class='px-6 py-3'>Keterangan</th>
                        <th scope='col' class='px-6 py-3'>Debit (Rp)</th>
                        <th scope='col' class='px-6 py-3'>Kredit (Rp)</th>
                        <th scope='col' class='px-6 py-3'>Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>";

            // 3. Iterasi transaksi dan hitung Saldo Berjalan
            while ($transaksi = mysqli_fetch_assoc($result_jurnal)) {
                $debit = $transaksi['debit'];
                $kredit = $transaksi['kredit'];

                // Logika Perhitungan Saldo Berjalan
                if ($posisi_saldo == 'DEBIT') {
                    // Saldo Normal DEBIT: Saldo Bertambah jika DEBIT, Berkurang jika KREDIT
                    $saldo_berjalan = $saldo_berjalan + $debit - $kredit;
                } else { 
                    // Saldo Normal KREDIT: Saldo Bertambah jika KREDIT, Berkurang jika DEBIT
                    $saldo_berjalan = $saldo_berjalan - $debit + $kredit;
                }
                
                // Tampilkan baris transaksi
                echo "<tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'>
                        <td class='px-6 py-4'>" . date('d-m-Y', strtotime($transaksi['tgl_transaksi'])) . "</td>
                        <td class='px-6 py-4'>" . htmlspecialchars($transaksi['keterangan']) . "</td>
                        <td class='px-6 py-4'>" . number_format($debit, 0, ',', '.') . "</td>
                        <td class='px-6 py-4'>" . number_format($kredit, 0, ',', '.') . "</td>
                        <td class='px-6 py-4'>" . number_format($saldo_berjalan, 0, ',', '.') . "</td>
                    </tr>";
            }

            // Tampilkan Saldo Akhir
            echo "<tfoot>
                    <tr class='font-semibold text-gray-900 dark:text-white'>
                        <td colspan='4' class='px-6 py-3'>SALDO AKHIR</td>
                        <td class='px-6 py-3'>" . number_format($saldo_berjalan, 0, ',', '.') . "</td>
                    </tr>
                </tfoot>";
            echo "</tbody></table></div>";
        }
        ?>
    </div>

<?php include 'template/footer.php';?>