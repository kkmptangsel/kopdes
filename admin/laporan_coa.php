<?php
// /koperasi/admin/laporan_coa.php
include '../config.php';
session_start(); 

include 'settings_helper.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN


$title = "Chart of Accounts | " . $SETTINGS['nama_koperasi'];
$page_heading = "Chart of Accounts";
include 'template/header.php';
?>
    
    <a href="tambah_coa.php" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 mt-3 mb-3">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
        Tambah Akun COA
    </a>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
    <div class="relative overflow-x-auto">
        <table class="w-full shadow-sm xl:rounded-lg sm:rounded-lg text-left rtl:text-right text-gray-500 dark:text-gray-400 mt-5">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">No. Akun</th>
                    <th scope="col" class="px-6 py-3">Nama Akun</th>
                    <th scope="col" class="px-6 py-3">Kategori</th>
                    <th scope="col" class="px-6 py-3">Saldo Normal</th>
                    <th scope="col" class="px-6 py-3">Saldo Akhir</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query Utama: JOIN COA dengan JURNAL UMUM untuk menghitung total DEBIT dan KREDIT
                $query = "
                    SELECT 
                        c.*,
                        SUM(COALESCE(j.debit, 0)) AS total_debit,
                        SUM(COALESCE(j.kredit, 0)) AS total_kredit
                    FROM 
                        coa c
                    LEFT JOIN 
                        jurnal_umum j ON c.no_akun = j.no_akun
                    GROUP BY 
                        c.no_akun
                    ORDER BY 
                        c.no_akun ASC
                ";

                $result = mysqli_query($koneksi, $query);
                
                if (!$result) {
                    die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                }

                while ($data = mysqli_fetch_assoc($result)) {
                    // Logika perhitungan Saldo Akhir
                    $saldo_normal = htmlspecialchars($data['posisi']);
                    $total_debit = (float)$data['total_debit'];
                    $total_kredit = (float)$data['total_kredit'];

                    if ($saldo_normal == 'Debit') {
                        $saldo_akhir = $total_debit - $total_kredit;
                    } else { // Kredit (Liabilitas, Modal, Pendapatan, Beban)
                        $saldo_akhir = $total_kredit - $total_debit;
                    }

                    // Format saldo akhir ke mata uang
                    $saldo_akhir_format = 'Rp ' . number_format($saldo_akhir, 0, ',', '.');
                ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['no_akun']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['nama_akun']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['kategori']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($data['posisi']); ?></td>
                        <td class="px-6 py-4 font-bold"><?php echo $saldo_akhir_format; ?></td>
                        <td class="px-6 py-4 flex items-center space-x-2">
                            <a href="edit_coa.php?no=<?php echo $data['no_akun']; ?>" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                            <button 
                                type="button" 
                                class="btn-delete-modal font-medium text-red-600 dark:text-red-500 hover:underline"
                                data-modal-target="global-delete-modal" 
                                data-modal-toggle="global-delete-modal"
                                data-item-id="<?php echo $data['no_akun']; ?>"
                                data-item-name="<?php echo htmlspecialchars($data['nama_akun']) . ' (' . $data['no_akun'] . ')'; ?>"
                                data-delete-url="proses_hapus_coa.php?no="
                                data-warning-msg="Menghapus Akun COA akan menghapus akun secara permanen. Akun dengan transaksi yang tercatat tidak dapat dihapus."
                            >
                                Hapus
                            </button>
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