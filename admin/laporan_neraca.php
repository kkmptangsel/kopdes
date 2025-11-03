<?php
// /koperasi/admin/laporan_neraca.php
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

$title = "Neraca | " . $SETTINGS['nama_koperasi'];
$page_heading = "Neraca";
include 'template/header.php';

// Tanggal Neraca (Biasanya akhir periode, kita ambil tanggal hari ini)
$tgl_neraca = date('d-m-Y');

// ----------------------------------------------------
// 1. FUNGSI UNTUK MENGHITUNG SALDO AKHIR SEMUA AKUN
// ----------------------------------------------------
function hitung_saldo_akhir($koneksi) {
    $saldo_akun = [];
    
    // Query untuk mengambil saldo debit/kredit total per akun
    $query_saldo = "SELECT 
                        ju.no_akun,
                        c.nama_akun,
                        c.posisi,
                        c.kategori,
                        SUM(ju.debit) AS total_debit,
                        SUM(ju.kredit) AS total_kredit
                    FROM jurnal_umum ju
                    JOIN coa c ON ju.no_akun = c.no_akun
                    GROUP BY ju.no_akun, c.nama_akun, c.posisi, c.kategori
                    ORDER BY c.kategori ASC, ju.no_akun ASC";
    
    $result_saldo = mysqli_query($koneksi, $query_saldo);
    
    if (!$result_saldo) {
        die("Query Saldo Error: " . mysqli_error($koneksi));
    }

    while ($data = mysqli_fetch_assoc($result_saldo)) {
        $saldo_bersih = $data['total_debit'] - $data['total_kredit'];
        
        // Logika Saldo Akhir Neraca
        if ($data['posisi'] == 'KREDIT') {
            // Untuk akun Liabilitas, Ekuitas, Pendapatan, saldo normal KREDIT
            // Jika hasil bersih negatif (Debit > Kredit), berarti saldo *minus*
            $saldo_akhir = -$saldo_bersih; 
        } else {
            // Untuk akun Aset, Beban, saldo normal DEBIT
            $saldo_akhir = $saldo_bersih; 
        }

        $saldo_akun[$data['no_akun']] = [
            'nama' => $data['nama_akun'],
            'kategori' => $data['kategori'],
            'saldo' => $saldo_akhir
        ];
    }

    return $saldo_akun;
}

$semua_saldo = hitung_saldo_akhir($koneksi);

// ----------------------------------------------------
// 2. KELOMPOKKAN DAN HITUNG TOTAL
// ----------------------------------------------------
$aset = 0;
$liabilitas = 0;
$ekuitas = 0;
$pendapatan = 0;
$beban = 0;

$detail_aset = [];
$detail_liabilitas = [];
$detail_ekuitas = [];
$detail_pendapatan = [];
$detail_beban = [];

foreach ($semua_saldo as $no_akun => $data) {
    if ($data['kategori'] == 'Aset') {
        $aset += $data['saldo'];
        $detail_aset[$no_akun] = $data;
    } elseif ($data['kategori'] == 'Liabilitas') {
        $liabilitas += $data['saldo'];
        $detail_liabilitas[$no_akun] = $data;
    } elseif ($data['kategori'] == 'Ekuitas') {
        $ekuitas += $data['saldo'];
        $detail_ekuitas[$no_akun] = $data;
    } elseif ($data['kategori'] == 'Pendapatan') {
        $pendapatan += $data['saldo'];
        $detail_pendapatan[$no_akun] = $data;
    } elseif ($data['kategori'] == 'Beban') {
        $beban += $data['saldo'];
        $detail_beban[$no_akun] = $data;
    }
}

// Hitung Laba/Rugi Bersih
$laba_rugi_bersih = $pendapatan - $beban;

// Tambahkan Laba/Rugi ke Ekuitas
$total_ekuitas = $ekuitas + $laba_rugi_bersih;

// Hitung Total Liabilitas dan Ekuitas
$total_liabilitas_ekuitas = $liabilitas + $total_ekuitas;
$neraca_seimbang = ($aset == $total_liabilitas_ekuitas);
?>

<div class="p-4 sm:p-6 lg:p-8 bg-white shadow-xl rounded-lg mt-5">
    
    <div class="mb-6">
        <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-0">Laporan Neraca</h2>
        <p class="text-center text-lg text-gray-600 mt-1 border-b-2 border-gray-300 pb-3">
            Per Tanggal: <span class="font-bold text-gray-800"><?php echo $tgl_neraca; ?></span>
        </p>
    </div>

    <div class="neraca-container grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div class="neraca-section border border-gray-300 p-4 rounded-lg bg-gray-50/50">
            <h3 class="text-2xl font-bold text-blue-700 border-b-2 border-blue-500 pb-2 mb-4">ASET</h3>
            <table class="w-full text-sm text-gray-600">
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($detail_aset as $no => $data): ?>
                    <tr class="hover:bg-gray-100 transition-colors">
                        <td class="py-2 pl-2 text-gray-800"><?php echo htmlspecialchars($data['nama']); ?></td>
                        <td class="py-2 pr-2 text-right font-medium text-gray-800">Rp <?php echo number_format($data['saldo'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr class="font-extrabold text-white bg-blue-600 total-akhir">
                        <td class="py-2 pl-2 text-lg">TOTAL ASET</td>
                        <td class="py-2 pr-2 text-right text-lg">Rp <?php echo number_format($aset, 0, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="neraca-section space-y-8">
            
            <div class="border border-gray-300 p-4 rounded-lg bg-gray-50/50">
                <h3 class="text-2xl font-bold text-red-700 border-b-2 border-red-500 pb-2 mb-4">LIABILITAS (KEWAJIBAN)</h3>
                <table class="w-full text-sm text-gray-600">
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($detail_liabilitas as $no => $data): ?>
                        <tr class="hover:bg-gray-100 transition-colors">
                            <td class="py-2 pl-2 text-gray-800"><?php echo htmlspecialchars($data['nama']); ?></td>
                            <td class="py-2 pr-2 text-right font-medium text-gray-800">Rp <?php echo number_format($data['saldo'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr class="font-bold total-row border-t-2 border-red-500">
                            <td class="py-2 pl-2">TOTAL LIABILITAS</td>
                            <td class="py-2 pr-2 text-right">Rp <?php echo number_format($liabilitas, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="border border-gray-300 p-4 rounded-lg bg-gray-50/50">
                <h3 class="text-2xl font-bold text-green-700 border-b-2 border-green-500 pb-2 mb-4">EKUITAS (MODAL)</h3>
                <table class="w-full text-sm text-gray-600">
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($detail_ekuitas as $no => $data): ?>
                        <tr class="hover:bg-gray-100 transition-colors">
                            <td class="py-2 pl-2 text-gray-800"><?php echo htmlspecialchars($data['nama']); ?></td>
                            <td class="py-2 pr-2 text-right font-medium text-gray-800">Rp <?php echo number_format($data['saldo'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr class="bg-indigo-50/80 font-medium">
                            <td class="py-2 pl-2 text-gray-700">Laba/Rugi Tahun Berjalan</td>
                            <td class="py-2 pr-2 text-right text-gray-700">Rp <?php echo number_format($laba_rugi_bersih, 0, ',', '.'); ?></td>
                        </tr>

                        <tr class="font-bold total-row border-t-2 border-green-500">
                            <td class="py-2 pl-2">TOTAL EKUITAS</td>
                            <td class="py-2 pr-2 text-right">Rp <?php echo number_format($total_ekuitas, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="bg-indigo-700 text-white font-extrabold p-4 rounded-lg shadow-lg">
                <table class="w-full">
                    <tbody>
                        <tr class="total-akhir">
                            <td class="text-lg">TOTAL LIABILITAS + EKUITAS</td>
                            <td class="text-lg text-right">Rp <?php echo number_format($total_liabilitas_ekuitas, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
        
    </div>
    
    <div class="mt-8 p-4 text-center font-bold text-lg rounded-lg seimbang-box 
        <?php echo $neraca_seimbang ? 'bg-green-100 text-green-700 border-2 border-green-500' : 'bg-red-100 text-red-700 border-2 border-red-500'; ?>">
        
        <?php if ($neraca_seimbang): ?>
            ✅ Neraca **SEIMBANG**! Aset sama dengan Liabilitas + Ekuitas.
        <?php else: ?>
            ❌ PERINGATAN! Neraca **TIDAK SEIMBANG**. Selisih: **Rp <?php echo number_format(abs($aset - $total_liabilitas_ekuitas), 0, ',', '.'); ?>**.
        <?php endif; ?>
    </div>

</div>

<?php include 'template/footer.php';?>