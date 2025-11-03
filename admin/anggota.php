<?php
// /koperasi/admin/anggota.php - ROUTER UNTUK DAFTAR DAN DETAIL ANGGOTA
include '../config.php';
include 'settings_helper.php';
session_start();

// 🛑 1. CEK LOGIN HARUS DILAKUKAN DI SINI (SETELAH SESSION START, SEBELUM OUTPUT APAPUN)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan notifikasi error sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    
    // Redirect ke halaman index (login page)
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN


// Tentukan apakah ini tampilan DETAIL atau DAFTAR
$is_detail_view = isset($_GET['nik']) && !empty($_GET['nik']);
$is_daftar_view = !$is_detail_view;

// --- LOGIKA DETAIL ANGGOTA ---
if ($is_detail_view) {
    
    $nik_anggota = mysqli_real_escape_string($koneksi, $_GET['nik']);

    // 2. Query Data Anggota Utama
    $q_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE no_ktp='$nik_anggota'");
    if (mysqli_num_rows($q_anggota) == 0) {
        // 🛑 KOREKSI: Gunakan Session Flash Message jika data tidak ditemukan
        $_SESSION['notif_error'] = "Detail anggota dengan NIK **$nik_anggota** tidak ditemukan.";
        header("location:anggota.php");
        exit;
    }
    $data_anggota = mysqli_fetch_assoc($q_anggota);

    $title = "Detail Anggota: " . $data_anggota['nama_anggota'] . " | " . $SETTINGS['nama_koperasi'];
    $page_heading = "Detail Anggota";
    include 'template/header.php';

    // Ambil ID Anggota
    $id_anggota = $data_anggota['id_anggota'];

    // 3. Query Riwayat Transaksi
    $q_simpanan = mysqli_query($koneksi, "SELECT * FROM simpanan WHERE id_anggota='$id_anggota' ORDER BY tgl_simpan DESC");
    $q_pinjaman = mysqli_query($koneksi, "SELECT * FROM pinjaman WHERE id_anggota='$id_anggota' ORDER BY tgl_pinjam DESC");
    
    $q_total_simpanan = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total_simpanan FROM simpanan WHERE id_anggota='$id_anggota'");
    $total_simpanan = mysqli_fetch_assoc($q_total_simpanan)['total_simpanan'] ?? 0;

    // D. Riwayat Angsuran
    $loan_ids = [];
    $q_loan_ids = mysqli_query($koneksi, "SELECT id_pinjaman FROM pinjaman WHERE id_anggota='$id_anggota'");
    while ($id = mysqli_fetch_assoc($q_loan_ids)) {
        $loan_ids[] = $id['id_pinjaman'];
    }

    $q_angsuran = false;
    if (!empty($loan_ids)) {
        $loan_ids_string = "'" . implode("','", $loan_ids) . "'";
        $q_angsuran = mysqli_query($koneksi, "SELECT * FROM angsuran WHERE id_pinjaman IN ({$loan_ids_string}) ORDER BY tgl_bayar DESC");
    }
    
?>
        <a href="anggota.php" class="mt-4 inline-flex items-center text-sm font-medium text-blue-600">
            <svg class="w-4 h-4 mr-1 rtl:rotate-180" fill="none" viewBox="0 0 14 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4"/></svg>
            Kembali
        </a>

        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm mt-5">
           <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white mb-3">Detail Anggota</h5>
           <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-5">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">No. Anggota</span>
                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($data_anggota['no_anggota']); ?></span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Nama Lengkap</span>
                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($data_anggota['nama_anggota']); ?></span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">NIK KTP:</span>
                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($data_anggota['no_ktp']); ?></span>
                </li>
            </ul>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Jenis Kelamin:</span>
                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($data_anggota['jenis_kelamin']); ?></span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Tanggal Bergabung:</span>
                    <span class="font-bold text-blue-600"><?php echo htmlspecialchars($data_anggota['tgl_bergabung']); ?></span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Total Simpanan:</span>
                    <span class="font-bold text-blue-600">Rp.<?php echo number_format($total_simpanan, 0, ',', '.'); ?></span>
                </li>
            </ul>
           </div>
        </div>

    <div class="lg:col-span-2 mt-5">
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">Riwayat Semua Transaksi</h3>
            <div class="relative overflow-x-auto">
                <?php if (mysqli_num_rows($q_simpanan) > 0): ?>
                    <p>Total <?php echo mysqli_num_rows($q_simpanan); ?> transaksi simpanan.</p>
                <?php else: ?>
                    <p>Belum ada riwayat simpanan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php

} 
// --- LOGIKA DAFTAR ANGGOTA ---
else {
    
    // 1. Logika Filter Status (Tabs)
    $current_status = isset($_GET['status']) ? $_GET['status'] : 'aktif'; // Default: aktif
    $status_query = ($current_status == 'semua') ? '' : "WHERE status_anggota = '$current_status'";

    $title = "Daftar Anggota | " . $SETTINGS['nama_koperasi'];
    $page_heading = "Daftar Anggota";
    include 'template/header.php';
    // 🛑 CATATAN: Notifikasi sukses/gagal dari proses_tambah_anggota.php atau proses_edit_anggota.php
    // akan ditampilkan di sini melalui Snackbar yang ada di template/footer.php.
?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
        <a href="tambah_anggota.php" class="inline-flex items-center text-white bg-red-600 border border-red-700 focus:outline-none hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-1.5" type="button"><svg class="w-[14px] h-[14px] text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/></svg>
            <span class="ms-2 font-bold">Tambah Anggota</span>
        </a>

        <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400 mt-4">
            <li class="me-2">
                <a href="anggota.php?status=aktif" class="inline-block p-4 rounded-t-lg <?php echo ($current_status == 'aktif') ? 'text-red-600 bg-gray-100 dark:bg-gray-800 dark:text-red-500 active' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'; ?>">
                    Anggota Aktif
                </a>
            </li>
            <li class="me-2">
                <a href="anggota.php?status=non-aktif" class="inline-block p-4 rounded-t-lg <?php echo ($current_status == 'non-aktif') ? 'text-red-600 bg-gray-100 dark:bg-gray-800 dark:text-red-500 active' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'; ?>">
                    Anggota Non-aktif
                </a>
            </li>
            <li class="me-2">
                <a href="anggota.php?status=semua" class="inline-block p-4 rounded-t-lg <?php echo ($current_status == 'semua') ? 'text-red-600 bg-gray-100 dark:bg-gray-800 dark:text-red-500 active' : 'hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300'; ?>">
                    Tampilkan Semua
                </a>
            </li>
        </ul>

        <div class="relative overflow-x-auto shadow-xl xl:rounded-lg sm:rounded-lg mt-5 border-t-4 border-red-800">
            <table class="w-full text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-red-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">No. Anggota</th>
                        <th scope="col" class="px-6 py-3">Nama Anggota</th>
                        <th scope="col" class="px-6 py-3">No. KTP (NIK)</th>
                        <th scope="col" class="px-6 py-3">Jenis Kelamin</th>
                        <th scope="col" class="px-6 py-3">Agama</th>
                        <th scope="col" class="px-6 py-3">Alamat</th>
                        <th scope="col" class="px-6 py-3">No. Telp</th>
                        <th scope="col" class="px-6 py-3">Tgl Bergabung</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 2. Query Data: Memakai filter status
                    $query = "SELECT * FROM anggota {$status_query} ORDER BY nama_anggota ASC";
                    $result = mysqli_query($koneksi, $query);
                    
                    if (!$result) {
                        die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                    }

                    $no = 1;
                    while ($data = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4"><?php echo $no++; ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['no_anggota']); ?></td> 
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['nama_anggota']); ?></td>
                            <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($data['no_ktp']); ?></td> 
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['jenis_kelamin']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['agama']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['alamat']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['no_telp']); ?></td>
                            <td class="px-6 py-4"><?php echo date('d-m-Y', strtotime($data['tgl_bergabung'])); ?></td>
                            
                            <td class="px-6 py-4">
                                <?php 
                                $status = $data['status_anggota'];
                                $badge_class = ($status == 'aktif') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                echo "<span class='{$badge_class} text-xs font-medium me-2 px-2.5 py-0.5 rounded'>".strtoupper($status)."</span>";
                                ?>
                            </td>
                            
                            <td class="px-6 py-4 flex flex-col space-y-2">
                                <a type="button" class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300" href="anggota.php?nik=<?php echo $data['no_ktp']; ?>">Detail</a>

                                <a type="button" class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300" href="edit_anggota.php?id=<?php echo $data['id_anggota']; ?>">Edit</a>

                                <label class="inline-flex items-center cursor-pointer mt-2">
                                    <input 
                                        type="checkbox" 
                                        value="" 
                                        class="sr-only peer toggle-status-switch"
                                        data-anggota-id="<?php echo $data['id_anggota']; ?>"
                                        data-current-status="<?php echo $data['status_anggota']; ?>"
                                        <?php echo ($data['status_anggota'] == 'aktif') ? 'checked' : ''; ?> 
                                    >
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300 status-text-<?php echo $data['id_anggota']; ?>">
                                        <?php echo ($data['status_anggota'] == 'aktif') ? 'Aktif' : 'Non-aktif'; ?>
                                    </span>
                                </label>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table> 
        </div>

    </div>

<?php 
} // Akhir dari else (Logika Daftar)
include 'template/footer.php';
?>