<?php
// /koperasi/admin/pengaturan.php
include '../config.php';
session_start(); 

include 'settings_helper.php';
$title = "Pengaturan | " . $SETTINGS['nama_koperasi'];
$page_heading = "Pengaturan";
include 'template/header.php';

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

// Ambil data pengaturan (hanya 1 baris dengan ID=1)
$query = "SELECT * FROM pengaturan WHERE id = 1";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Jika tidak ada data, gunakan data default
if (!$data) {
    $data = [
        'nama_koperasi' => 'Nama Koperasi Default',
        'no_induk_koperasi' => '',
        'sk_ahu' => '',
        'no_telp' => '',
        'email' => '',
        'external_url' => '',
        'alamat' => ''
    ];
}
?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">

        <form  class="max-w-full mx-auto mt-5 mb-5" action="proses_pengaturan.php" method="POST">
            
            <input type="hidden" name="id" value="1">
            
            <div class="mb-5">
                <label for="nama_koperasi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Koperasi</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="nama_koperasi" name="nama_koperasi" value="<?php echo htmlspecialchars($data['nama_koperasi']); ?>" required>
            </div>
            
            <div class="mb-5">
                <label for="no_induk_koperasi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Induk Koperasi (NIK)</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="no_induk_koperasi" name="no_induk_koperasi" value="<?php echo htmlspecialchars($data['no_induk_koperasi']); ?>">
            </div>
            
            <div class="mb-5">
                <label for="sk_ahu" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">SK AHU</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="sk_ahu" name="sk_ahu" value="<?php echo htmlspecialchars($data['sk_ahu']); ?>" placeholder="Contoh: AHU-0023617.AH.01.29.TAHUN 2025">
            </div>
            
            <div class="mb-5">
                <label for="no_telp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Telp/HP</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="tel" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($data['no_telp']); ?>">
            </div>
            
            <div class="mb-5">
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>">
            </div>
            
            <div class="mb-5">
                <label for="external_url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">External URL (Alamat Web)</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="url" id="external_url" name="external_url" value="<?php echo htmlspecialchars($data['external_url']); ?>" placeholder="Contoh: https://koperasi-mandiri.com">
            </div>
            
            <div class="mb-5">
                <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat Koperasi</label>
                <textarea class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($data['alamat']); ?></textarea>
            </div>
            
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Simpan Pengaturan</button>
        </form>

    </div>

<?php include 'template/footer.php';?>