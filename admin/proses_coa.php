<?php
// /koperasi/admin/proses_coa.php
include '../config.php';
session_start(); 

// Cek sesi
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    // 🛑 KOREKSI: Session Flash Message untuk cek sesi
    $_SESSION['notif_error'] = "Sesi Anda telah berakhir atau Anda tidak memiliki akses. Silakan login kembali.";
    header("location:../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // Ambil data umum
    $nama_akun = mysqli_real_escape_string($koneksi, $_POST['nama_akun']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $posisi = mysqli_real_escape_string($koneksi, $_POST['posisi']);

    // --- LOGIKA TAMBAH COA ---
    if ($action == 'tambah') {
        $no_akun = mysqli_real_escape_string($koneksi, $_POST['no_akun']);

        // Validasi dasar (No Akun tidak boleh kosong)
        if (empty($no_akun)) {
            $_SESSION['notif_error'] = "Nomor Akun tidak boleh kosong.";
            header("location:tambah_coa.php");
            exit;
        }

        $query = "INSERT INTO coa (no_akun, nama_akun, kategori, posisi) 
                  VALUES ('$no_akun', '$nama_akun', '$kategori', '$posisi')";
        
        if (mysqli_query($koneksi, $query)) {
            // 🛑 KOREKSI: Session Flash Message untuk Sukses
            $_SESSION['notif_sukses'] = "Akun **{$no_akun} - {$nama_akun}** berhasil ditambahkan.";
            header("location:laporan_coa.php");
        } else {
            // 🛑 KOREKSI: Session Flash Message untuk Gagal (misal: No Akun duplikat)
            $_SESSION['notif_error'] = "Gagal menambahkan akun. Nomor Akun **{$no_akun}** mungkin sudah terdaftar.";
            header("location:tambah_coa.php");
        }
        exit;
    } 

    // --- LOGIKA EDIT COA ---
    elseif ($action == 'edit') {
        $no_akun_lama = mysqli_real_escape_string($koneksi, $_POST['no_akun_lama']);
        $no_akun_baru = mysqli_real_escape_string($koneksi, $_POST['no_akun_baru']);
        
        mysqli_begin_transaction($koneksi);

        try {
            // 1. UPDATE data di tabel COA
            $query_coa = "UPDATE coa SET 
                          no_akun='$no_akun_baru', 
                          nama_akun='$nama_akun', 
                          kategori='$kategori', 
                          posisi='$posisi' 
                          WHERE no_akun='$no_akun_lama'";
            
            $result_coa = mysqli_query($koneksi, $query_coa);
            if (!$result_coa) {
                // Gunakan error SQL jika ada, atau pesan umum
                throw new Exception("Gagal update data COA. Pastikan No Akun Baru tidak duplikat.");
            }

            // 2. Jika No Akun berubah, UPDATE semua jurnal yang terkait
            if ($no_akun_lama !== $no_akun_baru) {
                $query_jurnal = "UPDATE jurnal_umum SET no_akun='$no_akun_baru' WHERE no_akun='$no_akun_lama'";
                $result_jurnal = mysqli_query($koneksi, $query_jurnal);
                if (!$result_jurnal) {
                    throw new Exception("Gagal update jurnal terkait.");
                }
            }

            mysqli_commit($koneksi);
            // 🛑 KOREKSI: Session Flash Message untuk Sukses
            $_SESSION['notif_sukses'] = "Akun COA **{$no_akun_baru}** berhasil diperbarui.";
            header("location:laporan_coa.php");

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            // 🛑 KOREKSI: Session Flash Message untuk Gagal
            $_SESSION['notif_error'] = "Gagal update akun COA: " . $e->getMessage();
            header("location:edit_coa.php?no=" . $no_akun_lama);
        }
        exit;
    }

} else {
    // KASUS: Akses langsung ke proses_coa.php tanpa POST
    $_SESSION['notif_error'] = "Akses tidak valid.";
    header("location:laporan_coa.php");
    exit;
}
?>