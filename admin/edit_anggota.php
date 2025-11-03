<?php
// /koperasi/admin/edit_anggota.php
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

if (!isset($_GET['id'])) {
    // 🛑 KOREKSI: Gunakan Session Flash Message jika ID tidak ada
    $_SESSION['notif_error'] = "ID Anggota tidak ditemukan.";
    header("location:anggota.php");
    exit;
}

$id = (int)$_GET['id']; // Konversi ke integer untuk keamanan

// Ambil data anggota dari database
$query = "SELECT * FROM anggota WHERE id_anggota = $id";
$result = mysqli_query($koneksi, $query);

// Jika data tidak ditemukan
if (mysqli_num_rows($result) < 1) {
    // 🛑 KOREKSI: Gunakan Session Flash Message jika data tidak ditemukan
    $_SESSION['notif_error'] = "Data anggota dengan ID tersebut tidak ditemukan.";
    header("location:anggota.php");
    exit;
}
$data = mysqli_fetch_assoc($result);

$title = "Edit Anggota | " . $SETTINGS['nama_koperasi'];
$page_heading = "Edit Anggota: " . $data['nama_anggota']; // Ubah page heading agar lebih informatif
include 'template/header.php';
?>
    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
        
        <form  class="w-full mx-auto" action="proses_edit_anggota.php" method="POST">
            <input type="hidden" name="id_anggota" value="<?php echo $data['id_anggota']; ?>">

            <div class="relative z-0 w-full mb-5">
                <label for="no_anggota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. Anggota</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="no_anggota" name="no_anggota" value="<?php echo htmlspecialchars($data['no_anggota']); ?>" required>
            </div>

            <div class="relative z-0 w-full mb-5">
                <label for="nama_anggota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="nama_anggota" name="nama_anggota" value="<?php echo htmlspecialchars($data['nama_anggota']); ?>" required>
            </div>
            
            <div class="relative z-0 w-full mb-5">
                <label for="no_ktp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nomor KTP</label>
                <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" id="no_ktp" name="no_ktp" value="<?php echo htmlspecialchars($data['no_ktp']); ?>" required>
            </div>
            
<div class="grid grid-cols-2 grid-rows-5 gap-4">
    <div class="relative z-0 w-full mb-5">
        <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kelamin</label>
        <select id="jenis_kelamin" name="jenis_kelamin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
            <option value="">-- Pilih --</option>
            <option value="Laki-Laki" <?php if ($data['jenis_kelamin'] == 'Laki-Laki') echo 'selected'; ?>>Laki-Laki</option>
            <option value="Perempuan" <?php if ($data['jenis_kelamin'] == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
        </select>
    </div>
    
    <div class="relative z-0 w-full mb-5">
        <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Agama</label>
        <select id="jenis_kelamin" name="agama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
            <option value="">-- Pilih --</option>
            <option value="Islam" <?php if ($data['agama'] == 'Islam') echo 'selected'; ?>>Islam</option>
            <option value="Khatolik" <?php if ($data['agama'] == 'Khatolik') echo 'selected'; ?>>Khatolik</option>
            <option value="Kristen" <?php if ($data['agama'] == 'Kristen') echo 'selected'; ?>>Kristen</option>
            <option value="Hindu" <?php if ($data['agama'] == 'Hindu') echo 'selected'; ?>>Hindu</option>
            <option value="Budha" <?php if ($data['agama'] == 'Budha') echo 'selected'; ?>>Budha</option>
        </select>
    </div>
</div> 

            <div class="relative z-0 w-full mb-5">
                <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                <textarea rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" id="alamat" name="alamat" required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
            </div>
            
<div class="grid grid-cols-2 grid-rows-5 gap-4">
    <div class="relative z-0 w-full mb-5">
        <label for="no_telp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. Telp/HP</label>
        <div class="relative">
            <div class="absolute inset-y-0 start-0 top-0 flex items-center ps-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 19 18">
                    <path d="M18 13.446a3.02 3.02 0 0 0-.946-1.985l-1.4-1.4a3.054 3.054 0 0 0-4.218 0l-.7.7a.983.983 0 0 1-1.39 0l-2.1-2.1a.983.983 0 0 1 0-1.389l.7-.7a2.98 2.98 0 0 0 0-4.217l-1.4-1.4a2.824 2.824 0 0 0-4.218 0c-3.619 3.619-3 8.229 1.752 12.979C6.785 16.639 9.45 18 11.912 18a7.175 7.175 0 0 0 5.139-2.325A2.9 2.9 0 0 0 18 13.446Z"/>
                </svg>
            </div>
            <input type="text" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($data['no_telp']); ?>" aria-describedby="no_telp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
        </div>
    </div>
    
    <div class="relative z-0 w-full mb-5">
        <label for="tgl_bergabung" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Bergabung</label>
        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="date" id="tgl_bergabung" name="tgl_bergabung" value="<?php echo $data['tgl_bergabung']; ?>" required>
    </div>

</div>
            
            <button type="submit" name="action" value="edit_anggota.php" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:gray-blue-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Update Data</button>

            <a class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:gray-blue-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800" href="anggota.php">Batal</a>
        </form>

    </div>

<?php include 'template/footer.php';?>