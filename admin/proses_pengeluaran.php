<?php
// /koperasi/admin/proses_pengeluaran.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Gunakan Session Flash Message dan redirect tanpa parameter
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // Ambil data umum
    $tgl_pengeluaran = mysqli_real_escape_string($koneksi, $_POST['tgl_pengeluaran']);
    $no_akun_beban = mysqli_real_escape_string($koneksi, $_POST['no_akun_beban']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    
    // Pastikan jumlah di-sanitize dari input non-number
    $jumlah_raw = str_replace(['.', ','], '', $_POST['jumlah']);
    $jumlah = (float)$jumlah_raw;

    // Akun Kas/Bank selalu 111 (Diasumsikan Kas)
    $no_akun_kas = '111';

    // Ambil Nama Akun Beban untuk Keterangan Jurnal
    $q_akun = mysqli_query($koneksi, "SELECT nama_akun FROM coa WHERE no_akun='$no_akun_beban'");
    $d_akun = mysqli_fetch_assoc($q_akun);
    $nama_akun_beban = $d_akun ? $d_akun['nama_akun'] : 'Akun Beban Tidak Dikenal';

    // Jurnal Keterangan
    $keterangan_jurnal = "Pengeluaran Kas untuk " . $nama_akun_beban . ": " . $keterangan;
    
    // Validasi Sederhana
    if ($jumlah <= 0 || empty($tgl_pengeluaran) || empty($no_akun_beban)) {
        $_SESSION['notif_error'] = "Data pengeluaran tidak lengkap atau jumlah tidak valid.";
        header("location:laporan_pengeluaran.php");
        exit;
    }

    // Mulai Transaksi Database
    mysqli_begin_transaction($koneksi);

    try {
        if ($action == 'tambah') {
            
            // 1. INSERT ke Tabel Pengeluaran
            $query_pengeluaran = "INSERT INTO pengeluaran (tgl_pengeluaran, no_akun, keterangan, jumlah) 
                                  VALUES ('$tgl_pengeluaran', '$no_akun_beban', '$keterangan', $jumlah)";
            $result_pengeluaran = mysqli_query($koneksi, $query_pengeluaran);
            
            if (!$result_pengeluaran) {
                throw new Exception("Gagal menyimpan data pengeluaran.");
            }
            $last_id = mysqli_insert_id($koneksi);

            // 2. JURNAL UMUM
            
            // A. DEBIT: Beban (Bertambah)
            $query_jurnal_debit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit, ref_id) 
                                   VALUES ('$tgl_pengeluaran', '$no_akun_beban', '$keterangan_jurnal', $jumlah, 0, 'PEN-$last_id')";
            $result_jurnal_debit = mysqli_query($koneksi, $query_jurnal_debit);

            // B. KREDIT: Kas (Berkurang)
            $query_jurnal_kredit = "INSERT INTO jurnal_umum (tgl_transaksi, no_akun, keterangan, debit, kredit, ref_id) 
                                    VALUES ('$tgl_pengeluaran', '$no_akun_kas', '$keterangan_jurnal', 0, $jumlah, 'PEN-$last_id')";
            $result_jurnal_kredit = mysqli_query($koneksi, $query_jurnal_kredit);

            if (!$result_jurnal_debit || !$result_jurnal_kredit) {
                throw new Exception("Gagal mencatat jurnal pengeluaran.");
            }
            
            mysqli_commit($koneksi);
            // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses tambah
            $_SESSION['notif_sukses'] = "Pengeluaran sebesar Rp " . number_format($jumlah, 0, ',', '.') . " berhasil dicatat.";
            header("location:laporan_pengeluaran.php");

        } elseif ($action == 'edit') {
            
            $id_pengeluaran = (int)$_POST['id_pengeluaran'];
            $tgl_lama = mysqli_real_escape_string($koneksi, $_POST['tgl_lama']); // Tidak digunakan, tapi dipertahankan untuk referensi
            
            // 1. UPDATE Tabel Pengeluaran
            $query_pengeluaran = "UPDATE pengeluaran SET 
                                  tgl_pengeluaran='$tgl_pengeluaran', 
                                  no_akun='$no_akun_beban', 
                                  keterangan='$keterangan', 
                                  jumlah=$jumlah 
                                  WHERE id_pengeluaran=$id_pengeluaran";
            $result_pengeluaran = mysqli_query($koneksi, $query_pengeluaran);
            
            if (!$result_pengeluaran) {
                throw new Exception("Gagal update data pengeluaran.");
            }

            // 2. UPDATE JURNAL UMUM
            $ref_id = "PEN-$id_pengeluaran";

            // Update DEBIT (Akun Beban)
            $query_jurnal_debit = "UPDATE jurnal_umum SET 
                                   tgl_transaksi='$tgl_pengeluaran', 
                                   no_akun='$no_akun_beban', 
                                   keterangan='$keterangan_jurnal', 
                                   debit=$jumlah, kredit=0 
                                   WHERE ref_id='$ref_id' AND (no_akun LIKE '5%' OR no_akun='$no_akun_beban')";
            $result_jurnal_debit = mysqli_query($koneksi, $query_jurnal_debit);

            // Update KREDIT (Akun Kas/Bank 111)
            $query_jurnal_kredit = "UPDATE jurnal_umum SET 
                                    tgl_transaksi='$tgl_pengeluaran', 
                                    keterangan='$keterangan_jurnal', 
                                    debit=0, kredit=$jumlah 
                                    WHERE ref_id='$ref_id' AND no_akun='$no_akun_kas'";
            $result_jurnal_kredit = mysqli_query($koneksi, $query_jurnal_kredit);

            if (!$result_jurnal_debit || !$result_jurnal_kredit) {
                throw new Exception("Gagal update jurnal pengeluaran.");
            }

            mysqli_commit($koneksi);
            // 🟢 KOREKSI: Gunakan Session Flash Message untuk sukses edit
            $_SESSION['notif_sukses'] = "Pengeluaran ID #$id_pengeluaran berhasil diperbarui.";
            header("location:laporan_pengeluaran.php");
        }

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        // 🔴 KOREKSI: Gunakan Session Flash Message untuk error
        $_SESSION['notif_error'] = "Gagal memproses pengeluaran ($action). Terjadi kesalahan: " . $e->getMessage();
        header("location:laporan_pengeluaran.php");
    }

} else {
    header("location:laporan_pengeluaran.php");
}
?>