<?php
// /koperasi/admin/ubah_status_anggota.php - MENANGANI PERUBAHAN STATUS VIA AJAX
include '../config.php';
session_start(); 

header('Content-Type: application/json');

// Wajibkan request POST (dari AJAX)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
    exit;
}

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir. Silakan login ulang.']);
    exit;
}

// Ambil data JSON yang dikirim oleh AJAX
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$id = (int)$data['id']; 
$new_status_bool = $data['status']; // Nilai boolean true/false dari JS

// Konversi nilai boolean menjadi string database ('aktif' atau 'non-aktif')
$new_status_db = ($new_status_bool === true) ? 'aktif' : 'non-aktif';
$message_status = ($new_status_bool === true) ? 'Aktif' : 'Non-aktif';

$query = "UPDATE anggota SET status_anggota = '$new_status_db' WHERE id_anggota = $id";
$result = mysqli_query($koneksi, $query);

if ($result) {
    // Beri respons sukses dalam JSON
    echo json_encode([
        'success' => true, 
        'message' => "Status anggota berhasil diubah menjadi **{$message_status}**.",
        'new_status' => $new_status_db
    ]);
} else {
    // Beri respons gagal dalam JSON
    echo json_encode([
        'success' => false, 
        'message' => "Gagal mengubah status anggota. Terjadi kesalahan database."
    ]);
}
exit;
?>