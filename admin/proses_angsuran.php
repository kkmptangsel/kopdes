<?php
// /koperasi/admin/proses_angsuran.php
include '../config.php';
session_start();

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'tambah_angsuran') {
    
    // Ambil data
    $id_pinjaman = (int)$_POST['id_pinjaman'];
    $id_anggota = (int)$_POST['id_anggota'];
    $tgl_bayar = mysqli_real_escape_string($koneksi, $_POST['tgl_bayar']);
    
    // 🛑 KOREKSI: Gunakan nama variabel yang sesuai dengan kolom tabel
    $jumlah_angsuran = (float)$_POST['jumlah_bayar']; // Ambil dari input form 'jumlah_bayar'
    
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $sisa_pinjaman_sekarang = (float)$_POST['sisa_pinjaman_sekarang'];

    // Akun yang digunakan:
    $no_akun_kas = '111';   // Kas (bertambah, di DEBIT)
    $no_akun_piutang = '112'; // Piutang Pinjaman (berkurang, di KREDIT)
    
    // Validasi
    if ($jumlah_angsuran <= 0 || $jumlah_angsuran > $sisa_pinjaman_sekarang) {
        $_SESSION['notif_error'] = "Jumlah pembayaran tidak valid atau melebihi sisa pinjaman yang ada.";
        header("location:tambah_angsuran.php?pinjam_id=$id_pinjaman");
        exit;
    }
    
    $status_lunas = ($jumlah_angsuran >= $sisa_pinjaman_sekarang) ? 1 : 0;
    $keterangan_jurnal = "Penerimaan Angsuran Pinjaman Anggota ID: $id_anggota (Pinjaman #$id_pinjaman)";

    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        // --- 1. INSERT ke Tabel Angsuran
        // 🛑 KOREKSI: Kolom tujuan di INSERT harus 'jumlah_angsuran'
        $query_angsuran = "INSERT INTO angsuran (id_pinjaman, tgl_bayar, jumlah_angsuran, keterangan) 
                           VALUES ($id_pinjaman, '$tgl_bayar', $jumlah_angsuran, '$keterangan')";
        $result_angsuran = mysqli_query($koneksi, $query_angsuran);
        
        if (!$result_angsuran) {
            throw new Exception("Gagal menyimpan data angsuran.");
        }
        $last_id = mysqli_insert_id($koneksi);
        $ref_id = "ANG-$last_id";

        // --- 2. UPDATE status Pinjaman (jika lunas)
        if ($status_lunas) {
            $query_update_pinjaman = "UPDATE pinjaman SET status='Lunas' WHERE id_pinjaman=$id_pinjaman";
            $result_update_pinjaman = mysqli_query($koneksi, $query_update_pinjaman);
            if (!$result_update_pinjaman) {
                throw new Exception("Gagal update status pinjaman menjadi Lunas.");
            }
        }

        // --- 3. JURNAL UMUM
        
        // A. DEBIT: Kas (Bertambah)
        // 🛑 KOREKSI: Gunakan variabel $jumlah_angsuran
        $query_jurnal_debit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit, ref_id) 
                               VALUES ('$tgl_bayar', '$no_akun_kas', '$keterangan_jurnal', $jumlah_angsuran, 0, '$ref_id')";
        $result_jurnal_debit = mysqli_query($koneksi, $query_jurnal_debit);

        // B. KREDIT: Piutang Pinjaman (Berkurang)
        // 🛑 KOREKSI: Gunakan variabel $jumlah_angsuran
        $query_jurnal_kredit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit, ref_id) 
                                VALUES ('$tgl_bayar', '$no_akun_piutang', '$keterangan_jurnal', 0, $jumlah_angsuran, '$ref_id')";
        $result_jurnal_kredit = mysqli_query($koneksi, $query_jurnal_kredit);

        if (!$result_jurnal_debit || !$result_jurnal_kredit) {
            throw new Exception("Gagal mencatat jurnal angsuran.");
        }
        
        mysqli_commit($koneksi);
        
        // 🟢 Gunakan Session Flash Message untuk sukses
        $pesan_sukses = "Pembayaran Angsuran sebesar **Rp " . number_format($jumlah_angsuran, 0, ',', '.') . "** berhasil dicatat untuk Pinjaman #$id_pinjaman.";
        if ($status_lunas) {
            $pesan_sukses .= " Pinjaman ini sekarang **LUNAS**!";
        }
        
        $_SESSION['notif_sukses'] = $pesan_sukses;
        header("location:data_pinjaman.php");

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        
        // 🔴 Gunakan Session Flash Message untuk error
        $_SESSION['notif_error'] = "Gagal memproses angsuran Pinjaman #$id_pinjaman. Terjadi kesalahan: " . $e->getMessage();
        header("location:tambah_angsuran.php?pinjam_id=$id_pinjaman");
    }

} else {
    $_SESSION['notif_error'] = "Permintaan tidak valid.";
    header("location:data_pinjaman.php");
}
?>