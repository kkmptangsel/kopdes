<?php
// /koperasi/admin/tambah_user.php - KOREKSI FINAL UNTUK SNACKBAR

session_start(); // <<< Tetap di baris pertama
include '../config.php';
include 'settings_helper.php';

// 🛑 1. CEK SESI HARUS DI SINI (SEBELUM INCLUDE HEADER)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // Gunakan notifikasi error sesi (Session Flash)
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN

// 2. Tentukan Header
$title = "Tambah Pengguna | " . $SETTINGS['nama_koperasi'];
$page_heading = "Tambah Pengguna Baru";
include 'template/header.php';

// 3. Ambil daftar anggota yang BELUM terdaftar sebagai user (id_user IS NULL)
$q_anggota = "SELECT id_anggota, nama_anggota, no_ktp FROM anggota 
              WHERE id_user IS NULL 
              ORDER BY nama_anggota ASC";
$r_anggota = mysqli_query($koneksi, $q_anggota);

if (!$r_anggota) {
    // Menampilkan error database (hanya untuk debugging)
    die("Query Error: " . mysqli_error($koneksi)); 
}

// 🛑 LOGIKA NOTIFIKASI LAMA (melalui $_GET) DIHAPUS 🛑

?>
    
    <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5 mx-auto">
        
        <?php 
        // 🛑 CATATAN: KODE SNACKBAR (MEMBACA $_SESSION['notif_error']) 
        // SEHARUSNYA SUDAH ADA DI 'template/footer.php' dan berfungsi di sini.
        // Jika tidak, Anda perlu menampilkannya di sini:
        
        /* if (isset($_SESSION['notif_error'])): ?>
            <div class="px-4 py-3 rounded relative mb-4 font-medium bg-red-100 border border-red-400 text-red-700" role="alert">
                <?php echo $_SESSION['notif_error']; unset($_SESSION['notif_error']); ?>
            </div>
        <?php endif; 
        */
        ?>

        <form method="POST" action="proses_tambah_user.php">
            <div class="mb-5">
                <label for="id_anggota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Anggota (Wajib)</label>
                <select id="id_anggota" name="id_anggota" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Pilih Anggota...</option>
                    <?php while ($d = mysqli_fetch_assoc($r_anggota)): ?>
                        <option value="<?php echo $d['id_anggota']; ?>" data-nama="<?php echo htmlspecialchars($d['nama_anggota']); ?>">
                            <?php echo htmlspecialchars($d['nama_anggota']) . " (" . htmlspecialchars($d['no_ktp']) . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Hanya anggota yang belum memiliki akun yang ditampilkan.</p>
            </div>
            
            <div class="mb-5">
                <label for="nama_lengkap_display" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap (Otomatis dari Anggota)</label>
                <input type="text" id="nama_lengkap_display" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 cursor-not-allowed dark:bg-gray-600 dark:border-gray-500 dark:text-gray-400" disabled value="Pilih anggota untuk mengisi nama">
                <input type="hidden" name="nama_lengkap" id="nama_lengkap_hidden" required> 
            </div>

            <div class="mb-5">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                <input type="text" id="username" name="username" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Masukkan username unik" required>
            </div>

            <div class="mb-5">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Masukkan password" required>
            </div>
            
            <input type="hidden" name="role" value="anggota">

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Tambahkan Pengguna</button>
        </form>

    </div>

<script>
    document.getElementById('id_anggota').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const namaLengkapDisplay = document.getElementById('nama_lengkap_display');
        const namaLengkapHidden = document.getElementById('nama_lengkap_hidden');

        if (selectedOption.value) {
            const nama = selectedOption.getAttribute('data-nama');
            namaLengkapDisplay.value = nama;
            namaLengkapHidden.value = nama;
        } else {
            namaLengkapDisplay.value = "Pilih anggota untuk mengisi nama";
            namaLengkapHidden.value = "";
        }
    });
</script>

<?php include 'template/footer.php';?>