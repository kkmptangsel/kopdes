<?php
// /koperasi/admin/tambah_pinjaman.php
include '../config.php';
session_start();
include 'settings_helper.php';

// 🛑 CEK LOGIN
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}

// 🛑 SET JUDUL & HEADER
$title = "Tambah Pinjaman Baru | " . htmlspecialchars($SETTINGS['nama_koperasi']);
$page_heading = "Tambah Pinjaman Baru";
include 'template/header.php';

// 🛑 FUNGSI OTOMATIS GENERATE KODE PINJAMAN
function generateKodePinjaman($koneksi) {
    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }

    do {
        // Buat kode acak 6 digit dengan prefix KSP-
        $random_number = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $kode_pinjaman = "KSP-" . $random_number;

        // Cek apakah kode sudah ada
        $check_query = "SELECT kode_pinjaman FROM pinjaman WHERE kode_pinjaman = '$kode_pinjaman'";
        $result = mysqli_query($koneksi, $check_query);

        if (!$result) {
            die("Query gagal: " . mysqli_error($koneksi));
        }

        $exists = mysqli_num_rows($result) > 0;
        mysqli_free_result($result);

    } while ($exists);

    return $kode_pinjaman;
}

// 🧩 Panggil fungsi untuk menghasilkan kode baru
$kode_pinjaman_baru = generateKodePinjaman($koneksi);
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
        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Tambah Pinjaman Baru</span>
      </div>
    </li>
  </ol>
</nav>

<div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
    <form class="w-full mx-auto" action="proses_tambah_pinjaman.php" method="POST">
        <div class="relative z-0 w-full mb-5 group">

            <div class="relative z-0 w-full mb-5 group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ID Transaksi (Otomatis)</label>
                <p class="text-lg font-bold text-blue-700 dark:text-blue-500 mb-2">
                    <?php echo $kode_pinjaman_baru; ?>
                </p>
                <input type="hidden" name="kode_pinjaman" value="<?php echo $kode_pinjaman_baru; ?>">
            </div>

            <div class="relative z-0 w-full">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="id_anggota">Pilih Anggota</label>
                <select id="id_anggota" name="id_anggota" required style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">-- Pilih Nama Anggota --</option>
                    <?php
                    $query_anggota = "SELECT id_anggota, nama_anggota FROM anggota ORDER BY nama_anggota ASC";
                    $result_anggota = mysqli_query($koneksi, $query_anggota);
                    while ($data_anggota = mysqli_fetch_assoc($result_anggota)) {
                        echo "<option value='" . $data_anggota['id_anggota'] . "'>" . htmlspecialchars($data_anggota['nama_anggota']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="relative z-0 w-full">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="jumlah_pinjaman">Jumlah Pinjaman (Rp)</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="jumlah_pinjaman" name="jumlah_pinjaman" required placeholder="Contoh: 5000000">
            </div>

            <div class="relative z-0 w-full mb-5 group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="tipe_pinjaman">Tipe Angsuran</label>
                <select id="tipe_pinjaman" name="tipe_pinjaman" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">-- Pilih Tipe --</option>
                    <option value="Bulanan">Bulanan</option>
                    <option value="Mingguan">Mingguan</option>
                    <option value="Harian">Harian</option>
                </select>
            </div>

            <div class="relative z-0 w-full mb-5 group">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="lama_angsuran">Lama Angsuran (Contoh: 12)</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="number" id="lama_angsuran" name="lama_angsuran" required min="1" placeholder="Masukkan jumlah kali bayar">
            </div>

            <div class="relative z-0 w-full">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="tgl_pinjam">Tanggal Pinjam</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="date" id="tgl_pinjam" name="tgl_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="relative z-0 w-full">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="tenggat_waktu">Tenggat Waktu (Jatuh Tempo)</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="date" id="tenggat_waktu" name="tenggat_waktu" required>
            </div>
            
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Simpan Pinjaman</button>
            <a class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800" href="data_pinjaman.php" style="background-color: #6c757d; color: white;">Batal</a>
        </div>
    </form>
</div>

<?php include 'template/footer.php'; ?>
