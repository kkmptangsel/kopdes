<?php
// /koperasi/admin/data_simpanan.php
include '../config.php';
session_start(); 
include 'settings_helper.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message untuk cek sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

// --- LOGIKA REKAPITULASI SIMPANAN WAJIB ---

// 1. Tentukan tahun saat ini atau dari input filter
$current_year = date('Y');
// Pastikan parameter 'tahun' tetap ada meskipun form filter belum dikirim
$selected_year = $_GET['tahun'] ?? $current_year; 
$selected_year = mysqli_real_escape_string($koneksi, $selected_year); 

// 2. Daftar bulan untuk header tabel
$months = [
    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', 
    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', 
    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
];

// 3. Ambil semua anggota
$q_anggota = mysqli_query($koneksi, "SELECT id_anggota, no_anggota, nama_anggota FROM anggota ORDER BY nama_anggota ASC");

// 4. Ambil semua data Simpanan Wajib untuk tahun terpilih
// Asumsi kolom tanggal simpanan adalah 'tgl_simpan' dan jenisnya adalah 'Wajib'
$q_simpanan_wajib = mysqli_query($koneksi, "
    SELECT id_anggota, DATE_FORMAT(tgl_simpan, '%m') AS bulan_bayar, jumlah
    FROM simpanan
    WHERE jenis_simpanan = 'Wajib' AND YEAR(tgl_simpan) = '{$selected_year}'
");

// 5. Proses data ke dalam format rekapitulasi per bulan
$rekap_wajib = [];
while ($anggota = mysqli_fetch_assoc($q_anggota)) {
    $rekap_wajib[$anggota['id_anggota']] = [
        'id_anggota' => $anggota['id_anggota'],
        'no_anggota' => $anggota['no_anggota'],
        'nama_anggota' => $anggota['nama_anggota'],
        'bulan' => array_fill_keys(array_keys($months), 0), // Inisialisasi 12 bulan dengan nilai 0
        'total_tahun' => 0
    ];
}

// Masukkan data simpanan wajib ke array rekap
while ($data = mysqli_fetch_assoc($q_simpanan_wajib)) {
    $id_anggota = $data['id_anggota'];
    $bulan_bayar = $data['bulan_bayar'];
    $jumlah = $data['jumlah'];

    if (isset($rekap_wajib[$id_anggota])) {
        // Cek apakah bulan sudah ada di array bulan (untuk menghindari error)
        if (isset($rekap_wajib[$id_anggota]['bulan'][$bulan_bayar])) {
            $rekap_wajib[$id_anggota]['bulan'][$bulan_bayar] += $jumlah;
            $rekap_wajib[$id_anggota]['total_tahun'] += $jumlah;
        }
    }
}
// --- AKHIR LOGIKA REKAPITULASI ---

$title = "Simpanan Anggota | " . $SETTINGS['nama_koperasi'];
$page_heading = "Simpanan Anggota";
include 'template/header.php';
?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
        <a href="tambah_simpanan.php" class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" type="button"><svg class="w-[24px] h-[24px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/></svg>
            <span class="sr-only">Tambah Anggota</span>
            Tambah Simpanan
        </a>

        <?php
        // 🛑 KOREKSI: Blok PHP yang menampilkan pesan notifikasi berbasis $_GET['pesan'] DIHAPUS.
        // Notifikasi akan ditangani oleh template/footer.php menggunakan SESSION.
        ?>
        
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5 mb-5">
            <h3 class="text-xl font-bold mb-4 border-b pb-2 dark:text-white">Riwayat Transaksi Simpanan</h3>
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Nama Anggota</th>
                        <th scope="col" class="px-6 py-3">Jenis Simpanan</th>
                        <th scope="col" class="px-6 py-3">Jumlah (Rp)</th>
                        <th scope="col" class="px-6 py-3">Tanggal Simpan</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT simpanan.*, anggota.nama_anggota  
                                FROM simpanan  
                                JOIN anggota ON simpanan.id_anggota = anggota.id_anggota
                                ORDER BY simpanan.tgl_simpan DESC";
                                
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
                            <td class="px-6 py-4"><?php echo ucfirst(htmlspecialchars($data['jenis_simpanan'])); ?></td>
                            <td class="px-6 py-4"><?php echo number_format($data['jumlah'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4"><?php echo date('d-m-Y', strtotime($data['tgl_simpan'])); ?></td>
                            <td class="px-6 py-4">
                                <a href="edit_simpanan.php?id=<?php echo $data['id_simpanan']; ?>" class="btn btn-edit">Edit</a>
                                
                                <a href="hapus_simpanan.php?id=<?php echo $data['id_simpanan']; ?>" class="btn btn-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data simpanan ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 dark:text-white">Rekap Simpanan Wajib Tahun <?php echo $selected_year; ?></h2>
            
            <div class="mb-4">
                <form method="GET" action="data_simpanan.php" class="flex items-center space-x-2">
                    <label for="tahun" class="text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Tahun:</label>
                    <select name="tahun" id="tahun" class="border border-gray-300 rounded-md shadow-sm p-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" onchange="this.form.submit()">
                        <?php
                        // Menampilkan tahun dari tahun saat ini hingga 5 tahun sebelumnya
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($y == $selected_year) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto shadow-md rounded-lg">
                <table class="w-full bg-white rounded-lg border-t-4 border-gray-600 mb-5 text-sm dark:bg-gray-800 dark:border-gray-700">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th rowspan="2" class="py-3 px-2 text-left text-xs font-medium text-gray-600 uppercase border-r dark:text-gray-400">No</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-600 uppercase dark:text-gray-400">No Anggota</th>
                            <th rowspan="2" class="py-3 px-3 text-left text-xs font-medium text-gray-600 uppercase border-r dark:text-gray-400">Nama Lengkap</th>
                            <th colspan="12" class="py-2 px-1 text-center text-xs font-medium text-gray-600 uppercase border-b border-l dark:text-gray-400">Bulan Simpanan Wajib (Rp.)</th>
                            <th rowspan="2" class="py-3 px-3 text-center text-xs font-medium text-gray-600 uppercase border-l border-r dark:text-gray-400">Total Th. <?php echo $selected_year; ?></th>
                        </tr>
                        <tr>
                            <?php foreach ($months as $m) : ?>
                                <th class="py-2 px-1 text-center text-xs font-medium text-gray-600 uppercase border-l dark:text-gray-400"><?php echo $m; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $no = 1; foreach ($rekap_wajib as $data) : ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="py-2 px-2 border-r dark:text-white"><?php echo $no++; ?></td>
                                <td class="py-2 px-3 dark:text-gray-300"><?php echo $data['no_anggota']; ?></td>
                                <td class="py-2 px-3 border-r whitespace-nowrap font-medium dark:text-white"><?php echo $data['nama_anggota']; ?></td>
                                <?php
                                foreach ($data['bulan'] as $jumlah) :
                                    // Menentukan kelas warna untuk sel: hijau jika sudah bayar, merah jika belum
                                    $bg_class = ($jumlah > 0) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                ?>
                                    <td class="py-2 px-1 text-right border-l font-semibold <?php echo $bg_class; ?>">
                                        <?php echo number_format($jumlah, 0, ',', '.'); ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="py-2 px-3 text-right font-bold border-l border-r dark:text-yellow-400">
                                    <?php echo number_format($data['total_tahun'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>

<?php include 'template/footer.php';?>