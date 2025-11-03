<?php
// /koperasi/admin/kwitansi_angsuran.php
include '../config.php';
session_start();

include 'settings_helper.php';

// Sertakan library FPDF (Asumsi FPDF diletakkan di admin/fpdf/fpdf.php)
require_once('fpdf\fpdf.php');

// Cek Login Admin
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak.");
}

// Cek ID Angsuran
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Angsuran tidak valid.");
}

$id_angsuran = mysqli_real_escape_string($koneksi, $_GET['id']);

// --- 1. Ambil Data Angsuran (Termasuk ID Pinjaman) ---
$q_angsuran = mysqli_query($koneksi, "SELECT * FROM angsuran WHERE id_angsuran = '$id_angsuran'");
$d_angsuran = mysqli_fetch_assoc($q_angsuran);

if (!$d_angsuran) {
    die("Data angsuran tidak ditemukan.");
}

$id_pinjaman = $d_angsuran['id_pinjaman'];

// --- 2. Ambil Data Pinjaman (Termasuk ID Anggota) ---
$q_pinjaman = mysqli_query($koneksi, "SELECT * FROM pinjaman WHERE id_pinjaman = '$id_pinjaman'");
$d_pinjaman = mysqli_fetch_assoc($q_pinjaman);

if (!$d_pinjaman) {
    die("Data pinjaman tidak ditemukan.");
}

$id_anggota = $d_pinjaman['id_anggota'];

// --- 3. Ambil Data Anggota ---
$q_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = '$id_anggota'");
$d_anggota = mysqli_fetch_assoc($q_anggota);

// --- 4. Tentukan Angsuran Ke- Berapa ---
// Kita hitung jumlah angsuran yang sudah dibayar SEBELUM angsuran ini
$q_urutan = mysqli_query($koneksi, "SELECT COUNT(id_angsuran) AS urutan FROM angsuran 
                                    WHERE id_pinjaman = '$id_pinjaman' 
                                    AND id_angsuran <= '$id_angsuran'");
$angsuran_ke = mysqli_fetch_assoc($q_urutan)['urutan'];

// --- 5. Fungsi Terbilang (Sederhana) ---
// Untuk implementasi lengkap, Anda mungkin perlu library atau fungsi yang lebih canggih.
// Kita asumsikan fungsi terbilang sudah ada (di file settings_helper.php atau di sini)
if (!function_exists('terbilang_rupiah')) {
    function terbilang_rupiah($angka) {
        $angka = abs($angka);
        $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $terbilang = "";

        if ($angka < 12) {
            $terbilang = " " . $baca[$angka];
        } else if ($angka < 20) {
            $terbilang = terbilang_rupiah($angka - 10) . " belas";
        } else if ($angka < 100) {
            $terbilang = terbilang_rupiah($angka / 10) . " puluh" . terbilang_rupiah($angka % 10);
        } else if ($angka < 200) {
            $terbilang = " seratus" . terbilang_rupiah($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = terbilang_rupiah($angka / 100) . " ratus" . terbilang_rupiah($angka % 100);
        } else if ($angka < 2000) {
            $terbilang = " seribu" . terbilang_rupiah($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = terbilang_rupiah($angka / 1000) . " ribu" . terbilang_rupiah($angka % 1000);
        } else if ($angka < 1000000000) {
            $terbilang = terbilang_rupiah($angka / 1000000) . " juta" . terbilang_rupiah($angka % 1000000);
        }
        return trim($terbilang);
    }
}


// --- 6. Definisi Data Kwitansi ---
$data_kwitansi = [
    'nama_koperasi' => $SETTINGS['nama_koperasi'], // Ambil dari settings_helper.php
    'alamat_koperasi' => $SETTINGS['alamat'], // Ambil dari settings_helper.php
    'no_telp_koperasi' => $SETTINGS['no_telp'], // Ambil dari settings_helper.php
    'jenis_angsuran' => $d_pinjaman['tipe_pinjaman'], // Asumsi tipe_pinjaman = Bulanan, Mingguan, dll.
    'nama_peminjam' => $d_anggota['nama_anggota'],
    'total_angsuran' => $d_pinjaman['lama_angsuran'], // Lama angsuran (cth: 10x)
    'kode_pinjaman' => $d_pinjaman['kode_pinjaman'], // Asumsi kolom ini ada
    'tgl_bayar' => date('d-m-Y', strtotime($d_angsuran['tgl_bayar'])),
    'angsuran_ke' => $angsuran_ke,
    'jumlah_bayar' => $d_angsuran['jumlah_angsuran'],
    'terbilang' => ucwords(terbilang_rupiah($d_angsuran['jumlah_angsuran'])) . " Rupiah",
];


// --- 7. Buat Objek PDF ---
$pdf = new FPDF('P', 'mm', 'A5'); // Potrait, milimeter, A5
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// --- KOP KWITANSI ---
// Ganti path ke file logo Anda
// $logo_path = '../assets/img/logo_koperasi.png'; 
// if (file_exists($logo_path)) {
//     $pdf->Image($logo_path, 15, 10, 20);
// }

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, strtoupper($data_kwitansi['nama_koperasi']), 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 4, $data_kwitansi['alamat_koperasi'], 0, 1, 'C');
$pdf->Cell(0, 4, "Telp: " . $data_kwitansi['no_telp_koperasi'], 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 25, 133, 25); // Garis pemisah kop
$pdf->Ln(5);

// --- JUDUL ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'KWITANSI PEMBAYARAN ANGSURAN', 0, 1, 'C');
$pdf->SetLineWidth(0.2);
$pdf->Line(40, 36, 108, 36);
$pdf->Ln(5);

// --- DETAIL TRANSAKSI ---
$pdf->SetFont('Arial', '', 10);
$lebar_label = 40;
$lebar_nilai = 70;

$pdf->Cell($lebar_label, 7, 'Jenis Angsuran', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($lebar_nilai, 7, ucfirst($data_kwitansi['jenis_angsuran']), 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell($lebar_label, 7, 'Nama Peminjam/Anggota', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($lebar_nilai, 7, $data_kwitansi['nama_peminjam'], 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell($lebar_label, 7, 'Jumlah Angsuran', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($lebar_nilai, 7, $data_kwitansi['total_angsuran'] . ' Kali', 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell($lebar_label, 7, 'Kode Pinjaman', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($lebar_nilai, 7, $data_kwitansi['kode_pinjaman'], 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell($lebar_label, 7, 'Tanggal Pembayaran', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($lebar_nilai, 7, $data_kwitansi['tgl_bayar'], 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell($lebar_label, 7, 'Angsuran Ke-', 0, 0, 'L');
$pdf->Cell(3, 7, ':', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($lebar_nilai, 7, $data_kwitansi['angsuran_ke'], 0, 1, 'L');

$pdf->Ln(5);

// --- JUMLAH DAN TERBILANG ---
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'JUMLAH ANGSURAN', 1, 0, 'C', true);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Rp ' . number_format($data_kwitansi['jumlah_bayar'], 0, ',', '.'), 1, 1, 'C', true);

$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 7, 'Terbilang: ' . $data_kwitansi['terbilang'], 0, 1, 'L');
$pdf->Ln(10);

// --- FOOTER TANDA TANGAN ---
$pdf->SetFont('Arial', '', 10);
$y_ttd = $pdf->GetY();
$x_kanan = 133 - 15; // Lebar A5 - Margin Kanan
$x_kiri = 15;

// Tanggal
$pdf->SetXY($x_kanan - 45, $y_ttd);
$pdf->Cell(45, 5, $SETTINGS['tgl_bayar'] . ', ' . $data_kwitansi['tgl_bayar'], 0, 1, 'C');
$pdf->Ln(2);

// Kasir
$pdf->SetX($x_kanan - 45);
$pdf->Cell(45, 5, 'Kasir', 0, 0, 'C');

// Penyetor
$pdf->SetX($x_kiri);
$pdf->Cell(45, 5, 'Penyetor/Anggota', 0, 1, 'C');

$pdf->Ln(15);

// Nama Tanda Tangan
$pdf->SetX($x_kanan - 45);
$pdf->Cell(45, 5, '(Nama Kasir/Admin)', 'B', 0, 'C');

$pdf->SetX($x_kiri);
$pdf->Cell(45, 5, '(' . $data_kwitansi['nama_peminjam'] . ')', 'B', 1, 'C');


// --- Output PDF ---
$filename = "Kwitansi_Angsuran_" . $data_kwitansi['kode_pinjaman'] . "_Angs_" . $data_kwitansi['angsuran_ke'] . ".pdf";
$pdf->Output('I', $filename); // 'I' untuk tampil di browser, 'D' untuk langsung download
?>