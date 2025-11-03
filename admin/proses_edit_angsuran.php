<?php
// /koperasi/admin/proses_edit_angsuran.php
include '../config.php';
session_start();

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses.";
    header("location:../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_angsuran') {
    
    // Ambil data
    $id_angsuran = (int)$_POST['id_angsuran'];
    $id_pinjaman = (int)$_POST['id_pinjaman'];
    $tgl_bayar = mysqli_real_escape_string($koneksi, $_POST['tgl_bayar']);
    
    $jumlah_angsuran_baru = (float)$_POST['jumlah_angsuran_baru'];
    $jumlah_angsuran_lama = (float)$_POST['jumlah_angsuran_lama'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $sisa_pinjaman_maksimal = (float)$_POST['sisa_pinjaman_maksimal'];

    $ref_id = "ANG-$id_angsuran"; 
    $keterangan_jurnal = "Koreksi Angsuran Pinjaman #$id_pinjaman (Ref: $ref_id)";

    // Validasi
    if ($jumlah_angsuran_baru <= 0 || $jumlah_angsuran_baru > $sisa_pinjaman_maksimal) {
        $_SESSION['notif_error'] = "Jumlah pembayaran baru tidak valid atau melebihi sisa pinjaman yang diizinkan.";
        header("location:edit_angsuran.php?id=$id_angsuran");
        exit;
    }
    
    // Hitung perubahan nilai
    $selisih_nilai = $jumlah_angsuran_baru - $jumlah_angsuran_lama;

    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // --- 1. UPDATE Tabel Angsuran
        $query_angsuran = "UPDATE angsuran SET 
                           tgl_bayar = '$tgl_bayar', 
                           jumlah_angsuran = $jumlah_angsuran_baru, 
                           keterangan = '$keterangan' 
                           WHERE id_angsuran = $id_angsuran";
        $result_angsuran = mysqli_query($koneksi, $query_angsuran);
        
        if (!$result_angsuran) {
            throw new Exception("Gagal memperbarui data angsuran.");
        }

        // --- 2. UPDATE JURNAL UMUM (Berubah hanya jika nilai angsuran berubah)
        if ($selisih_nilai != 0) {
            // Jurnal Angsuran selalu terdiri dari dua baris (Kas/Debit dan Piutang/Kredit)
            
            // UPDATE Baris Jurnal DEBIT (Kas)
            $query_jurnal_debit = "UPDATE jurnal_umum SET 
                                   tgl_transaksi = '$tgl_bayar',
                                   debit = $jumlah_angsuran_baru
                                   WHERE ref_id = '$ref_id' AND no_akun = '111'"; // 111 = Kas
            $result_jurnal_debit = mysqli_query($koneksi, $query_jurnal_debit);

            // UPDATE Baris Jurnal KREDIT (Piutang)
            $query_jurnal_kredit = "UPDATE jurnal_umum SET 
                                    tgl_transaksi = '$tgl_bayar',
                                    kredit = $jumlah_angsuran_baru
                                    WHERE ref_id = '$ref_id' AND no_akun = '112'"; // 112 = Piutang Pinjaman
            $result_jurnal_kredit = mysqli_query($koneksi, $query_jurnal_kredit);

            if (!$result_jurnal_debit || !$result_jurnal_kredit) {
                throw new Exception("Gagal memperbarui jurnal angsuran.");
            }
        }
        
        // --- 3. Hitung ulang total angsuran dan UPDATE Status Pinjaman
        
        $q_total = mysqli_query($koneksi, "SELECT SUM(jumlah_angsuran) as total_dibayar FROM angsuran WHERE id_pinjaman = $id_pinjaman");
        $d_total = mysqli_fetch_assoc($q_total);
        $total_dibayar_baru = (float)($d_total['total_dibayar'] ?? 0);

        $q_pinjaman = mysqli_query($koneksi, "SELECT jumlah_pinjaman FROM pinjaman WHERE id_pinjaman = $id_pinjaman");
        $d_pinjaman = mysqli_fetch_assoc($q_pinjaman);
        $jumlah_pinjaman_awal = $d_pinjaman['jumlah_pinjaman'];
        
        $status_baru = ($total_dibayar_baru >= $jumlah_pinjaman_awal) ? 'Lunas' : 'Belum Lunas';
        
        $query_update_status = "UPDATE pinjaman SET status='$status_baru' WHERE id_pinjaman=$id_pinjaman";
        $result_update_status = mysqli_query($koneksi, $query_update_status);
        
        if (!$result_update_status) {
            throw new Exception("Gagal update status pinjaman.");
        }

        mysqli_commit($koneksi);
        
        $_SESSION['notif_sukses'] = "Angsuran ID #$id_angsuran berhasil diperbarui. Status pinjaman ($status_baru) telah disesuaikan.";
        header("location:data_angsuran.php");

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        
        $_SESSION['notif_error'] = "Gagal memproses edit angsuran. Terjadi kesalahan: " . $e->getMessage();
        header("location:edit_angsuran.php?id=$id_angsuran");
    }

} else {
    $_SESSION['notif_error'] = "Permintaan tidak valid.";
    header("location:data_angsuran.php");
}
?>