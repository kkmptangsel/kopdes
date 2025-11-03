<?php
// /koperasi/admin/dashboard.php - KODE KOREKSI URUTAN

session_start();
include '../config.php';
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

// Fungsi untuk mendapatkan total simpanan berdasarkan tipe
function getTotalSimpananByTipe($koneksi, $tipe) {
    $q = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM simpanan WHERE jenis_simpanan = '$tipe'");
    $d = mysqli_fetch_assoc($q);
    return (float)($d['total'] ?? 0);
}

// 1. STATISTIK KEUANGAN
$q_simpanan_total = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total_simpanan FROM simpanan");
$d_simpanan_total = mysqli_fetch_assoc($q_simpanan_total);
$total_kas = (float)($d_simpanan_total['total_simpanan'] ?? 0);

$simpanan_pokok = getTotalSimpananByTipe($koneksi, 'pokok');
$simpanan_wajib = getTotalSimpananByTipe($koneksi, 'wajib');
$simpanan_sukarela = getTotalSimpananByTipe($koneksi, 'sukarela');

$q_pinjaman = mysqli_query($koneksi, "SELECT SUM(jumlah_pinjaman) AS total_pinjaman FROM pinjaman");
$d_pinjaman = mysqli_fetch_assoc($q_pinjaman);

$q_peminjam_aktif = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_anggota) AS total_peminjam_aktif FROM pinjaman WHERE status = 'belum lunas'");
$d_peminjam_aktif = mysqli_fetch_assoc($q_peminjam_aktif);

$q_pengeluaran = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total_pengeluaran FROM pengeluaran");
$d_pengeluaran = mysqli_fetch_assoc($q_pengeluaran);


// 2. STATISTIK ANGGOTA

// Total Anggota
$q_anggota = mysqli_query($koneksi, "SELECT COUNT(id_anggota) AS total_anggota FROM anggota");
$d_anggota = mysqli_fetch_assoc($q_anggota);
$total_anggota = (int)($d_anggota['total_anggota'] ?? 0);

// Anggota Berdasarkan Jenis Kelamin
$q_jk = mysqli_query($koneksi, "SELECT jenis_kelamin, COUNT(*) as jumlah FROM anggota GROUP BY jenis_kelamin");
$data_jk = [];
while ($d = mysqli_fetch_assoc($q_jk)) {
    // Kunci array akan menjadi 'Laki-Laki' atau 'Perempuan' (sesuai data DB)
    $data_jk[$d['jenis_kelamin']] = (int)$d['jumlah'];
}

// Anggota Aktif & Non-Aktif (Menggunakan UPPER() untuk mengatasi perbedaan kasus huruf)
// ASUMSI: Nama kolom di DB adalah 'status_anggota'
$q_status_anggota = mysqli_query($koneksi, "SELECT UPPER(status_anggota) AS status, COUNT(*) AS jumlah FROM anggota GROUP BY UPPER(status_anggota)");

$data_status_anggota_default = [
    'AKTIF' => 0,    // Kunci array dibuat huruf kapital
    'NON-AKTIF' => 0
];
$data_status_anggota_db = [];
while ($d = mysqli_fetch_assoc($q_status_anggota)) {
    // Hasil query 'status' juga sudah pasti kapital
    $data_status_anggota_db[$d['status']] = (int)$d['jumlah'];
}

// Gabungkan data default dengan hasil database
$data_status_anggota = array_merge($data_status_anggota_default, $data_status_anggota_db);

// Variabel output untuk kartu dashboard
$anggota_aktif = $data_status_anggota['AKTIF'];
$anggota_non_aktif = $data_status_anggota['NON-AKTIF'];


// Anggota Berdasarkan Agama (Memastikan semua ENUM tampil: Islam, Katholik, Kristen, Hindu, Budha)
$semua_agama = [
    'Islam' => 0,
    'Katholik' => 0,
    'Kristen' => 0,
    'Hindu' => 0,
    'Budha' => 0
];

$q_agama = mysqli_query($koneksi, "SELECT agama, COUNT(*) as jumlah FROM anggota GROUP BY agama");
$data_agama_db = [];
while ($d = mysqli_fetch_assoc($q_agama)) {
    $data_agama_db[$d['agama']] = (int)$d['jumlah'];
}

// Gabungkan data default dengan hasil database untuk memastikan semua kategori agama tampil
$data_agama = array_merge($semua_agama, $data_agama_db);

// 2. TENTUKAN HEADER DAN INCLUDE FILE TAMPILAN
$title = "Dashboard Admin | " . $SETTINGS['nama_koperasi'];
$page_heading = "Dashboard"; 
include 'template/header.php'; // HTML output dimulai di sini
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    <div class="card w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="card-body">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Kas (Simpanan)</p>
            <p class="mt-3 text-2xl font-bold text-blue-600">Rp.<?php echo number_format($total_kas, 0, ',', '.'); ?></p>
        </div>
    </div>

    <div class="card w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="card-body">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Pinjaman</p>
            <p class="mt-3 text-2xl font-bold text-gray-900">Rp.<?php echo number_format($d_pinjaman['total_pinjaman'], 0, ',', '.'); ?></p>
            <p class="mt-2 text-xs text-gray-500">Pinjaman Aktif: <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-sm font-bold"><?php echo $d_peminjam_aktif['total_peminjam_aktif'];?></span></p>
        </div>
    </div>

    <div class="card w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="card-body">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Pengeluaran</p>
            <p class="mt-3 text-2xl font-bold text-gray-900">Rp.<?php echo number_format($d_pengeluaran['total_pengeluaran'], 0, ',', '.'); ?></p>
        </div>
    </div>

    <div class="card w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="card-body">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Anggota</p>
            <p class="mt-3 text-2xl font-bold text-green-600"><?php echo $total_anggota; ?> Orang</p>
<div class="mt-2 flex space-x-3">
    <p class="text-gray-500 text-sm">Aktif: 
        <span class="bg-green-100 text-green-800 text-xs px-2.5 py-0.5 rounded-sm font-bold">
            <?php echo $anggota_aktif;?>
        </span>
    </p>
    <p class="text-gray-500 text-sm">Non-Aktif: 
        <span class="bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded-sm font-bold">
            <?php echo $anggota_non_aktif;?>
        </span>
    </p>
  </div>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">

    <div class="lg:col-span-1 space-y-4">
        
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Detail Simpanan</h5>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Pokok:</span>
                    <span class="font-bold text-blue-600">Rp.<?php echo number_format($simpanan_pokok, 0, ',', '.'); ?></span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Wajib:</span>
                    <span class="font-bold text-blue-600">Rp.<?php echo number_format($simpanan_wajib, 0, ',', '.'); ?></span>
                </li>
                <li class="py-2 flex justify-between">
                    <span class="text-gray-500">Sukarela:</span>
                    <span class="font-bold text-blue-600">Rp.<?php echo number_format($simpanan_sukarela, 0, ',', '.'); ?></span>
                </li>
            </ul>
        </div>

        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            
    <div class="flex items-center justify-between mb-4">
        <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Identitas Koperasi</h5>
        <a type="button" href="pengaturan.php" class="px-3 py-2 text-xs font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Edit
        </a>
   </div>

   <div class="mb-5">
   <p class="text-gray-600">
      <span class="font-semibold underline"><?php echo $SETTINGS['nama_koperasi']; ?></span> telah <a href="<?php echo $SETTINGS['external_url']; ?>" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" target="_blank">berizin dan terdaftar</a> di Kementerian Koperasi Republik Indonesia dengan nomor <span class="underline">SK AHU</span> : <span class="font-semibold"><?php echo $SETTINGS['sk_ahu']; ?></span>. 
    </p>
   </div>
         <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
            <li class="py-3 sm:py-4">
                <div class="flex items-center">
                    <div class="flex-1 min-w-0 ms-4">
                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                            <?php echo $SETTINGS['nama_koperasi']; ?>
                        </p>
                        <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                            Nama Koperasi
                        </p>
                    </div>
                </div>
            </li>
            <li class="py-3 sm:py-4">
                <div class="flex items-center">
                    <div class="flex-1 min-w-0 ms-4">
                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                            <?php echo $SETTINGS['alamat']; ?>
                        </p>
                        <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                            Alamat
                        </p>
                    </div>
                </div>
            </li>
            <li class="py-3 sm:py-4">
                <div class="flex items-center">
                    <div class="flex-1 min-w-0 ms-4">
                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                            <?php echo $SETTINGS['no_telp']; ?>
                        </p>
                        <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                            No. Telp/HP
                        </p>
                    </div>
                </div>
            </li>
          </ul>
        </div>

    </div>

    <div class="lg:col-span-2 space-y-4">
        
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Anggota Berdasarkan Jenis Kelamin</h5>
            <canvas id="jenisKelaminChart" height="120"></canvas>
        </div>

        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Anggota Berdasarkan Agama</h5>
            <canvas id="agamaChart" height="120"></canvas>
        </div>
        
    </div>


    <div class="lg:col-span-2 space-y-4">
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
         <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Status Anggota</h5>
         <canvas id="statusAnggotaChart" height="120"></canvas>
        </div>
        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Anggota Terkini</h5>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ------------------------------------------
    // DATA DARI PHP KE JAVASCRIPT
    // ------------------------------------------
    const dataJenisKelamin = <?php echo json_encode($data_jk); ?>;
    const dataAgama = <?php echo json_encode($data_agama); ?>;
    // 🛑 DATA BARU: Anggota Aktif vs Non-Aktif
    const dataStatusAnggota = <?php echo json_encode($data_status_anggota); ?>;


    // ------------------------------------------
    // CHART JENIS KELAMIN (Pie/Doughnut)
    // ------------------------------------------
    new Chart(document.getElementById('jenisKelaminChart'), {
        type: 'doughnut', 
        data: {
            labels: Object.keys(dataJenisKelamin),
            datasets: [{
                data: Object.values(dataJenisKelamin),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)', // Biru untuk Laki-Laki
                    'rgba(255, 99, 132, 0.7)' // Merah untuk Perempuan
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // ------------------------------------------
    // CHART AGAMA (Bar)
    // ------------------------------------------
    new Chart(document.getElementById('agamaChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(dataAgama),
            datasets: [{
                label: 'Jumlah Anggota',
                data: Object.values(dataAgama),
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });

    // ------------------------------------------
    // 🛑 CHART BARU: STATUS ANGGOTA (Pie/Doughnut)
    // ------------------------------------------
    new Chart(document.getElementById('statusAnggotaChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(dataStatusAnggota),
            datasets: [{
                data: Object.values(dataStatusAnggota),
                backgroundColor: [
                    'rgba(25, 135, 84, 0.7)', // Hijau untuk Aktif
                    'rgba(220, 53, 69, 0.7)'  // Merah untuk Non-Aktif
                ],
                borderColor: [
                    'rgba(25, 135, 84, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
});
</script>
<?php include 'template/footer.php';?>