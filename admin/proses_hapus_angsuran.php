<?php
// /koperasi/admin/proses_hapus_angsuran.php
include '../config.php';
session_start();

// Cek sesi dan validasi ID
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses.";
    header("location:../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['notif_error'] = "ID Angsuran tidak ditemukan.";
    header("location:data_angsuran.php");
    exit;
}

$id_angsuran = (int)$_GET['id'];
$ref_id = "ANG-$id_angsuran"; // Format reference ID jurnal

// Mulai Transaksi Database
mysqli_begin_transaction($koneksi);

try {
    // 1. Ambil data angsuran yang akan dihapus (PENTING untuk rollback status pinjaman)
    $query_select = "SELECT id_pinjaman FROM angsuran WHERE id_angsuran = $id_angsuran";
    $result_select = mysqli_query($koneksi, $query_select);
    $data_angsuran = mysqli_fetch_assoc($result_select);

    if (!$data_angsuran) {
        throw new Exception("Data angsuran tidak ditemukan.");
    }
    $id_pinjaman = $data_angsuran['id_pinjaman'];

    // 2. Hapus entri Jurnal Umum
    // Entri jurnal angsuran memiliki ref_id yang sama dengan format ANG-[id_angsuran]
    $query_delete_jurnal = "DELETE FROM jurnal_umum WHERE ref_id = '$ref_id'";
    $result_delete_jurnal = mysqli_query($koneksi, $query_delete_jurnal);

    if (!$result_delete_jurnal) {
        throw new Exception("Gagal menghapus entri jurnal terkait.");
    }

    // 3. Hapus Angsuran dari tabel angsuran
    $query_delete_angsuran = "DELETE FROM angsuran WHERE id_angsuran = $id_angsuran";
    $result_delete_angsuran = mysqli_query($koneksi, $query_delete_angsuran);

    if (!$result_delete_angsuran) {
        throw new Exception("Gagal menghapus data angsuran.");
    }
    
    // 4. Update status Pinjaman (hanya jika pinjaman tersebut LUNAS sebelum dihapus)
    // Cek sisa pinjaman setelah angsuran dihapus
    $query_pinjaman = mysqli_query($koneksi, "SELECT jumlah_pinjaman FROM pinjaman WHERE id_pinjaman = $id_pinjaman");
    $data_pinjaman = mysqli_fetch_assoc($query_pinjaman);
    $jumlah_pinjaman_awal = $data_pinjaman['jumlah_pinjaman'];
    
    // Hitung ulang total yang sudah dibayar setelah penghapusan
    $q_total = mysqli_query($koneksi, "SELECT SUM(jumlah_angsuran) as total_dibayar FROM angsuran WHERE id_pinjaman = $id_pinjaman");
    $d_total = mysqli_fetch_assoc($q_total);
    $total_dibayar_baru = (float)($d_total['total_dibayar'] ?? 0);
    
    // Jika total yang dibayar < jumlah pinjaman awal (artinya belum lunas lagi)
    if ($total_dibayar_baru < $jumlah_pinjaman_awal) {
        $query_update_pinjaman = "UPDATE pinjaman SET status='Belum Lunas' WHERE id_pinjaman=$id_pinjaman AND status='Lunas'";
        $result_update_pinjaman = mysqli_query($koneksi, $query_update_pinjaman);

        // Jika update gagal (meski tidak fatal), catat sebagai pengecualian
        if (!$result_update_pinjaman) {
             // throw new Exception("Peringatan: Gagal update status pinjaman, namun data dihapus."); 
             // Kita biarkan saja jika update tidak berhasil, tidak perlu membatalkan transaksi utama
        }
    }

    // Commit semua perubahan jika semua langkah berhasil
    mysqli_commit($koneksi);
    
    $_SESSION['notif_sukses'] = "Angsuran ID #$id_angsuran berhasil dihapus. Jurnal dan Sisa Pinjaman telah disesuaikan.";
    header("location:data_angsuran.php");

} catch (Exception $e) {
    // Rollback semua perubahan jika ada langkah yang gagal
    mysqli_rollback($koneksi);
    
    $_SESSION['notif_error'] = "Gagal menghapus angsuran. Terjadi kesalahan: " . $e->getMessage();
    header("location:data_angsuran.php");
}
exit;
?>