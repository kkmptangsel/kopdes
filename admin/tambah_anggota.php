<?php
// /koperasi/admin/tambah_anggota.php
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

$title = "Pengaturan | " . $SETTINGS['nama_koperasi'];
$page_heading = "Tambah Anggota";
include 'template/header.php';
?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">

        <form class="w-full mx-auto" action="proses_tambah_anggota.php" method="POST">
            <div class="relative z-0 w-full mb-5 group">
                <label for="no_anggota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Anggota</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="no_anggota" name="no_anggota" required>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="nama_anggota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="nama_anggota" name="nama_anggota" required>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="no_ktp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor KTP</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="no_ktp" name="no_ktp" required>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kelamin</label>
    <select id="jenis_kelamin" name="jenis_kelamin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        <option value="">-- Pilih --</option>
        <option value="Laki-Laki">Laki-Laki</option>
        <option value="Perempuan">Perempuan</option>
    </select>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="agama" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Agama</label>
    <select id="agama" name="agama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        <option value="">-- Pilih --</option>
        <option value="Islam">Islam</option>
        <option value="Khatolik">Khatolik</option>
        <option value="Kristen">Kristen</option>
        <option value="Hindu">Hindu</option>
        <option value="Budha">Budha</option>
    </select>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                <textarea rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" id="alamat" name="alamat" required></textarea>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="no_telp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor Telepon/HP</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="tel" id="no_telp" name="no_telp" required>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <label for="tgl_bergabung" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Bergabung</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="date" id="tgl_bergabung" name="tgl_bergabung" required>
            </div>
            
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
            <a href="anggota.php" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:gray-blue-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800" >Batal</a>
        </form>
      

    </div>

<?php include 'template/footer.php';?>