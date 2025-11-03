<?php
// /koperasi/admin/laporan_jurnal_umum.php
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

$title = "Jurnal Umum | " . $SETTINGS['nama_koperasi'];
$page_heading = "Jurnal Umum";
include 'template/header.php';
?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
       <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5">
        <table class='w-full text-left rtl:text-right text-gray-500 dark:text-gray-400'>
            <thead class='text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400'>
                <tr>
                    <th scope='col' class='px-6 py-3'>Tanggal</th>
                    <th scope='col' class='px-6 py-3'>Keterangan</th>
                    <th scope='col' class='px-6 py-3'>No. Akun</th>
                    <th scope='col' class='px-6 py-3'>Nama Akun</th>
                    <th  scope='col' class='px-6 py-3'>Debit (Rp)</th>
                    <th  scope='col' class='px-6 py-3'>Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query JOIN untuk mengambil data jurnal dan nama akun
                $query = "SELECT ju.*, c.nama_akun 
                          FROM jurnal_umum ju 
                          JOIN coa c ON ju.no_akun = c.no_akun 
                          ORDER BY ju.tgl_transaksi ASC, ju.id_jurnal ASC";
                $result = mysqli_query($koneksi, $query);

                $total_debit = 0;
                $total_kredit = 0;

                while ($data = mysqli_fetch_assoc($result)) {
                    $total_debit += $data['debit'];
                    $total_kredit += $data['kredit'];
                ?>
                    <tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'>
                        <td class='px-6 py-4'><?php echo date('d-m-Y', strtotime($data['tgl_transaksi'])); ?></td>
                        <td class='px-6 py-4'><?php echo htmlspecialchars($data['keterangan']); ?></td>
                        <td class='px-6 py-4'><?php echo htmlspecialchars($data['no_akun']); ?></td>
                        
                        <td class='px-6 py-4'>
                            <?php 
                            if ($data['kredit'] > 0) {
                                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars($data['nama_akun']);
                            } else {
                                echo htmlspecialchars($data['nama_akun']);
                            }
                            ?>
                        </td>

                        <td class="debit"><?php echo number_format($data['debit'], 0, ',', '.'); ?></td>
                        <td class="kredit"><?php echo number_format($data['kredit'], 0, ',', '.'); ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="font-semibold text-gray-900 dark:text-white">
                    <td colspan="4" class="px-6 py-3">Total</td>
                    <td class="px-6 py-3"><?php echo number_format($total_debit, 0, ',', '.'); ?></td>
                    <td class="px-6 py-3"><?php echo number_format($total_kredit, 0, ',', '.'); ?></td>
                </tr>
                <tr class="px-6 py-3" style="background-color: #e0ffe0;">
                    <td colspan="6" style="text-align: center;">
                        <?php 
                        if ($total_debit == $total_kredit) {
                            echo "Jurnal **SEIMBANG** (Debit = Kredit)";
                        } else {
                            echo "PERINGATAN! Jurnal **TIDAK SEIMBANG** (Selisih: Rp " . number_format(abs($total_debit - $total_kredit), 0, ',', '.') . ")";
                        }
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
      </div>
    </div>

<?php include 'template/footer.php';?>