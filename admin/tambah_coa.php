<?php
// /koperasi/admin/tambah_coa.php

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

$title = "Tambah COA | " . $SETTINGS['nama_koperasi'];
$page_heading = "Tambah Akun COA";
include 'template/header.php';
?>

<div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800">
    
    <form action="proses_coa.php" method="POST">
        <div class="mb-4">
            <label for="no_akun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. Akun</label>
            <input type="text" id="no_akun" name="no_akun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>
        <div class="mb-4">
            <label for="nama_akun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Akun</label>
            <input type="text" id="nama_akun" name="nama_akun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        </div>
        <div class="mb-4">
            <label for="kategori" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kategori</label>
            <select id="kategori" name="kategori" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <option value="">Pilih Kategori</option>
                <option value="ASET">ASET (Harta)</option>
                <option value="LIABILITAS">LIABILITAS (Kewajiban)</option>
                <option value="EKUITAS">EKUITAS (Modal)</option>
                <option value="PENDAPATAN">PENDAPATAN</option>
                <option value="BEBAN">BEBAN</option>
            </select>
        </div>
        <div class="mb-4">
            <label for="posisi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Saldo Normal</label>
            <select id="posisi" name="posisi" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                <option value="">Pilih Posisi Saldo</option>
                <option value="Debit">Debit</option>
                <option value="Kredit">Kredit</option>
            </select>
        </div>
        <button type="submit" name="action" value="tambah" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Tambah Akun</button>
        <a href="laporan_coa.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 ml-2">Batal</a>
    </form>
</div>

<?php include 'template/footer.php'; ?>