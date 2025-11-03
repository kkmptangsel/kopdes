<?php
// /koperasi/admin/edit_user.php
session_start(); // <<< PASTIKAN INI ADA!
include '../config.php';
include 'settings_helper.php';

// Cek sesi (Hanya admin)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN

if (!isset($_GET['id']) || empty($_GET['id'])) {
    // 🛑 KOREKSI: Gunakan Session Flash Message sebelum redirect
    $_SESSION['notif_error'] = "ID pengguna tidak ditemukan.";
    header("location:daftar_user.php");
    exit;
}

$id_user_edit = mysqli_real_escape_string($koneksi, $_GET['id']);

// 1. Ambil data pengguna yang akan di-edit
$query_user = "SELECT * FROM users WHERE id_user = '$id_user_edit'";
$result_user = mysqli_query($koneksi, $query_user);

if (mysqli_num_rows($result_user) == 0) {
    // 🛑 KOREKSI: Gunakan Session Flash Message sebelum redirect
    $_SESSION['notif_error'] = "Data pengguna tidak ditemukan.";
    header("location:daftar_user.php"); 
    exit;
}
$data_user = mysqli_fetch_assoc($result_user);
$current_anggota_id = null;

// 2. Cari tahu anggota mana yang saat ini terhubung dengan user ini
$query_linked_anggota = "SELECT id_anggota, nama_anggota FROM anggota WHERE id_user = '$id_user_edit'";
$result_linked_anggota = mysqli_query($koneksi, $query_linked_anggota);
if (mysqli_num_rows($result_linked_anggota) > 0) {
    $linked_anggota = mysqli_fetch_assoc($result_linked_anggota);
    $current_anggota_id = $linked_anggota['id_anggota'];
}

// 3. Ambil daftar anggota yang BELUM terdaftar sebagai user
$query_unlinked_anggota = "SELECT id_anggota, nama_anggota FROM anggota WHERE id_user IS NULL OR id_user = '' ORDER BY nama_anggota ASC";
$result_unlinked_anggota = mysqli_query($koneksi, $query_unlinked_anggota);
$unlinked_anggota_list = [];
while($row = mysqli_fetch_assoc($result_unlinked_anggota)) {
    $unlinked_anggota_list[] = $row;
}

$title = "Edit Pengguna | " . $SETTINGS['nama_koperasi'];
$page_heading = "Edit Pengguna: " . htmlspecialchars($data_user['username']);
include 'template/header.php'; // HTML Output Dimulai di sini

?>

    <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5 mx-auto">

        <form action="proses_edit_user.php" method="POST">
            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($data_user['id_user']); ?>">

            <div class="mb-5">
                <label for="nama_lengkap" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($data_user['nama_lengkap']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
            </div>

            <div class="mb-5">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($data_user['username']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
            </div>

            <div class="mb-5">
                <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role</label>
                <select id="role" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                    <option value="admin" <?php echo ($data_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="anggota" <?php echo ($data_user['role'] == 'anggota') ? 'selected' : ''; ?>>Anggota</option>
                </select>
            </div>

            <div class="mb-5">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            </div>

            <div class="mb-5">
                <label for="konfirmasi_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            </div>

            <div class="mb-5">
                <label for="id_anggota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Terkait dengan Anggota</label>
                <select id="id_anggota" name="id_anggota" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="" <?php echo is_null($current_anggota_id) ? 'selected' : ''; ?>>-- Tidak Terkait (Hanya Akun Admin) --</option>
                    
                    <?php
                    // Opsi 1: Anggota yang saat ini terhubung (Jika ada)
                    if ($current_anggota_id) {
                        echo '<option value="' . htmlspecialchars($linked_anggota['id_anggota']) . '" selected>[' . htmlspecialchars($linked_anggota['id_anggota']) . '] ' . htmlspecialchars($linked_anggota['nama_anggota']) . ' (Saat Ini)</option>';
                    }
                    
                    // Opsi 2: Daftar anggota yang belum terhubung
                    foreach ($unlinked_anggota_list as $anggota) {
                        // Jangan tampilkan lagi anggota yang sedang terhubung
                        if ($anggota['id_anggota'] != $current_anggota_id) {
                             echo '<option value="' . htmlspecialchars($anggota['id_anggota']) . '">[' . htmlspecialchars($anggota['id_anggota']) . '] ' . htmlspecialchars($anggota['nama_anggota']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Anggota yang sudah terhubung dengan user lain tidak akan muncul dalam daftar untuk menghindari konflik.</p>
            </div>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update Pengguna</button>
            <a href="daftar_user.php" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 ms-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Batal</a>
        </form>
    </div>

<?php include 'template/footer.php';?>