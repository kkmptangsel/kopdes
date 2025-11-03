<?php
// /koperasi/admin/proses_tambah_angsuran.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("location:../index.php?pesan=belum_login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_pinjaman = (int)$_POST['id_pinjaman'];
    $jumlah_angsuran = (float)str_replace(['.', ','], '', $_POST['jumlah_angsuran']);
    $tgl_bayar = mysqli_real_escape_string($koneksi, $_POST['tgl_bayar']);
    $sisa_pinjaman = (float)$_POST['sisa_pinjaman'];
    
    // --- VALIDASI PENTING ---
    // Cek apakah jumlah angsuran melebihi sisa pinjaman
    if ($jumlah_angsuran > $sisa_pinjaman) {
        header("location:detail_pinjaman.php?id=$id_pinjaman&pesan=angsuran_gagal");
        exit;
    }
    // Jika jumlah angsuran 0 atau kurang
    if ($jumlah_angsuran <= 0) {
        header("location:detail_pinjaman.php?id=$id_pinjaman&pesan=angsuran_gagal");
        exit;
    }
    
    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // 1. INSERT data angsuran ke tabel `angsuran`
        $query_insert = "INSERT INTO angsuran (id_pinjaman, jumlah_angsuran, tgl_bayar) 
                         VALUES ($id_pinjaman, $jumlah_angsuran, '$tgl_bayar')";
        $result_insert = mysqli_query($koneksi, $query_insert);

        if (!$result_insert) {
            throw new Exception("Gagal mencatat angsuran.");
        }
        
        // 2. HITUNG SISA TERBARU (termasuk angsuran yang baru saja dicatat)
        $sisa_terbaru = $sisa_pinjaman - $jumlah_angsuran;

        // 3. LOGIKA UPDATE STATUS PINJAMAN
        if ($sisa_terbaru <= 0) {
            // Jika sisa pinjaman 0 atau kurang, update status pinjaman menjadi 'lunas'
            $query_update_status = "UPDATE pinjaman SET status = 'lunas' WHERE id_pinjaman = $id_pinjaman";
            $result_update = mysqli_query($koneksi, $query_update_status);

            if (!$result_update) {
                throw new Exception("Gagal update status pinjaman menjadi lunas.");
            }
        }
        // Catatan: Jika sisa_terbaru > 0, status tetap 'belum lunas', tidak perlu query UPDATE.

        // Commit (simpan permanen) jika semua sukses
        mysqli_commit($koneksi);
        header("location:detail_pinjaman.php?id=$id_pinjaman&pesan=angsuran_sukses");

    } catch (Exception $e) {
        // Rollback (batalkan) jika ada yang gagal
        mysqli_rollback($koneksi);
        // echo "Error: " . $e->getMessage(); // Untuk debugging
        header("location:detail_pinjaman.php?id=$id_pinjaman&pesan=angsuran_gagal");
    }

} else {
    // Jika diakses langsung
    header("location:data_pinjaman.php");
}
?>