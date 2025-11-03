<?php
// /koperasi/admin/daftar_user.php
session_start(); 
include '../config.php';
include 'settings_helper.php';

// Cek sesi (Hanya admin)
// 🛑 1. CEK LOGIN HARUS DILAKUKAN DI SINI (SETELAH SESSION START, SEBELUM OUTPUT APAPUN)
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // Gunakan notifikasi error sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    
    // Redirect ke halaman index (login page)
    header("location:../index.php"); 
    exit;
}
// 🛑 AKHIR CEK LOGIN

$title = "Daftar Pengguna | " . $SETTINGS['nama_koperasi'];
$page_heading = "Manajemen Pengguna";
include 'template/header.php';

?>

    <div class="block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 mt-5">
        
        <a href="tambah_user.php" class="inline-flex items-center text-white bg-blue-700 border border-blue-800 focus:outline-none hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
            <svg class="w-4 h-4 text-white me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/></svg>
            <span class="font-bold">Tambah Pengguna Baru</span>
        </a>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-5">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Nama Lengkap</th>
                        <th scope="col" class="px-6 py-3">Username</th>
                        <th scope="col" class="px-6 py-3">Role</th>
                        <th scope="col" class="px-6 py-3">Status Anggota</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query JOIN untuk mendapatkan data users dan nama anggota terkait
                    // Perhatikan: Karena relasi ada di tabel 'anggota', kita perlu JOIN sebaliknya
                    $query = "SELECT u.*, a.nama_anggota 
                              FROM users u 
                              LEFT JOIN anggota a ON a.id_user = u.id_user
                              ORDER BY u.role ASC, u.nama_lengkap ASC";
                                
                    $result = mysqli_query($koneksi, $query);
                    
                    if (!$result) {
                        die("Query Error: ".mysqli_errno($koneksi)." - ".mysqli_error($koneksi));
                    }

                    $no = 1;
                    while ($data = mysqli_fetch_assoc($result)) {
                        // Tentukan status Anggota
                        $status_anggota = $data['nama_anggota'] 
                                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Terkait: ' . htmlspecialchars($data['nama_anggota']) . '</span>'
                                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Tidak Terkait</span>';
                        
                        // Tentukan warna badge Role
                        $role_class = ($data['role'] == 'admin') ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800';
                    ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($data['username']); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $role_class; ?>">
                                    <?php echo ucfirst(htmlspecialchars($data['role'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo $status_anggota; ?>
                            </td>
                            <td class="px-6 py-4 flex space-x-2">
<a href="edit_user.php?id=<?php echo $data['id_user']; ?>" 
   class="px-3 py-2 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700">
    Edit
</a>
                                
                                <?php if ($data['role'] != 'admin'): // Admin tidak bisa menghapus akun Admin lain di sini ?>
<button 
    type="button" 
    data-modal-target="global-delete-modal" 
    data-modal-toggle="global-delete-modal" 
    class="btn-delete-modal px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-red-700 rounded-lg hover:bg-red-800"
    data-item-id="<?php echo $data['id_user']; ?>" 
    data-item-name="<?php echo htmlspecialchars($data['username']); ?>" 
    data-delete-url="hapus_user.php?id=" 
    data-warning-msg="Aksi ini akan menghapus akun dan memutuskan semua relasi dengan anggota.">
    Hapus
</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
    </div>

<?php include 'template/footer.php';?>