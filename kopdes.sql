-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 07:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kopdes`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL,
  `no_anggota` varchar(20) NOT NULL,
  `no_ktp` varchar(20) NOT NULL,
  `nama_anggota` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-Laki','Perempuan','','') NOT NULL,
  `alamat` text NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `tgl_bergabung` date NOT NULL,
  `agama` enum('Islam','Khatolik','Kristen','Hindu','Budha') NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `status_anggota` varchar(10) DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id_anggota`, `no_anggota`, `no_ktp`, `nama_anggota`, `jenis_kelamin`, `alamat`, `no_telp`, `tgl_bergabung`, `agama`, `id_user`, `status_anggota`) VALUES
(1, '002', '3674056603830009', 'Lilis Saadah', 'Perempuan', 'Jl. SMP Mabad RT005/005', '0881024240202', '2025-10-30', 'Islam', 15, 'aktif'),
(2, '004', '3674056603830008', 'Ela Susila', 'Perempuan', 'Jl. Pahlawan RT009/001', '085172211985', '2025-09-01', 'Islam', 14, 'aktif'),
(3, '001', '3674035806820007', 'Trima Lestari', 'Perempuan', 'Pondok Betung RT009/001', '0881024240201', '2025-09-18', 'Islam', NULL, 'aktif'),
(4, '003', '3674030506590003', 'Ratno', 'Laki-Laki', 'Pondok Betung RT009/001', '0881024240204', '2025-09-13', 'Islam', NULL, 'non-aktif'),
(6, '006', '3674056603830001', 'Demoooooo', 'Laki-Laki', 'Gang Damai', '081234567891', '2025-11-02', 'Kristen', NULL, 'aktif'),
(7, '007', '3674056603830002', 'Demo2', 'Laki-Laki', 'Gg. Anggur', '081234567893', '2025-11-02', 'Islam', NULL, 'non-aktif');

-- --------------------------------------------------------

--
-- Table structure for table `angsuran`
--

CREATE TABLE `angsuran` (
  `id_angsuran` int(11) NOT NULL,
  `id_pinjaman` int(11) NOT NULL,
  `jumlah_angsuran` decimal(10,2) NOT NULL,
  `tgl_bayar` date NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `angsuran`
--

INSERT INTO `angsuran` (`id_angsuran`, `id_pinjaman`, `jumlah_angsuran`, `tgl_bayar`, `keterangan`) VALUES
(1, 6, 5000.00, '2025-11-02', ''),
(2, 7, 100000.00, '2025-11-02', '');

-- --------------------------------------------------------

--
-- Table structure for table `coa`
--

CREATE TABLE `coa` (
  `no_akun` varchar(10) NOT NULL,
  `nama_akun` varchar(100) NOT NULL,
  `posisi` enum('DEBIT','KREDIT') NOT NULL,
  `kategori` enum('Aset','Liabilitas','Ekuitas','Pendapatan','Beban') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coa`
--

INSERT INTO `coa` (`no_akun`, `nama_akun`, `posisi`, `kategori`) VALUES
('111', 'Kas', 'DEBIT', 'Aset'),
('112', 'Piutang Pinjaman', 'DEBIT', 'Aset'),
('211', 'Simpanan Pokok & Wajib', 'KREDIT', 'Liabilitas'),
('311', 'Modal Awal', 'KREDIT', 'Ekuitas'),
('411', 'Pendapatan Bunga Pinjaman', 'KREDIT', 'Pendapatan'),
('511', 'Beban Operasional', 'DEBIT', 'Aset');

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_umum`
--

CREATE TABLE `jurnal_umum` (
  `id_jurnal` int(11) NOT NULL,
  `tgl_transaksi` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `no_akun` varchar(10) NOT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ref_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurnal_umum`
--

INSERT INTO `jurnal_umum` (`id_jurnal`, `tgl_transaksi`, `keterangan`, `no_akun`, `debit`, `kredit`, `ref_id`) VALUES
(41, '2025-10-31', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 3', '111', 20000.00, 0.00, NULL),
(42, '2025-10-31', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 3', '211', 0.00, 20000.00, NULL),
(43, '2025-10-31', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 4', '111', 20000.00, 0.00, NULL),
(44, '2025-10-31', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 4', '211', 0.00, 20000.00, NULL),
(45, '2025-10-31', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 2', '111', 20000.00, 0.00, NULL),
(46, '2025-10-31', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 2', '211', 0.00, 20000.00, NULL),
(47, '2025-10-31', 'Penerimaan Simpanan Pokok dari Anggota dengan ID: 3', '111', 100000.00, 0.00, NULL),
(48, '2025-10-31', 'Penerimaan Simpanan Pokok dari Anggota dengan ID: 3', '211', 0.00, 100000.00, NULL),
(51, '2025-11-02', 'Pemberian Pinjaman Anggota ID: 2 dengan tenggat: 2025-12-01', '112', 20000.00, 0.00, NULL),
(52, '2025-11-02', 'Pemberian Pinjaman Anggota ID: 2 dengan tenggat: 2025-12-01', '111', 0.00, 20000.00, NULL),
(53, '2025-11-02', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 2', '111', 20000.00, 0.00, NULL),
(54, '2025-11-02', 'Penerimaan Simpanan Wajib dari Anggota dengan ID: 2', '211', 0.00, 20000.00, NULL),
(55, '2025-11-02', 'Pemberian Pinjaman Anggota ID: 6 dengan tenggat: 2025-11-15', '112', 10000.00, 0.00, NULL),
(56, '2025-11-02', 'Pemberian Pinjaman Anggota ID: 6 dengan tenggat: 2025-11-15', '111', 0.00, 10000.00, NULL),
(57, '2025-11-02', 'Penerimaan Angsuran Pinjaman Anggota ID: 2 (Pinjaman #6)', '111', 5000.00, 0.00, 'ANG-1'),
(58, '2025-11-02', 'Penerimaan Angsuran Pinjaman Anggota ID: 2 (Pinjaman #6)', '112', 0.00, 5000.00, 'ANG-1'),
(59, '2025-11-02', 'Penerimaan Angsuran Pinjaman Anggota ID: 6 (Pinjaman #7)', '111', 100000.00, 0.00, 'ANG-2'),
(60, '2025-11-02', 'Penerimaan Angsuran Pinjaman Anggota ID: 6 (Pinjaman #7)', '112', 0.00, 100000.00, 'ANG-2'),
(61, '2025-11-03', 'Pemberian Pinjaman Anggota ID: 4 dengan tenggat: 2025-11-13', '112', 10000.00, 0.00, NULL),
(62, '2025-11-03', 'Pemberian Pinjaman Anggota ID: 4 dengan tenggat: 2025-11-13', '111', 0.00, 10000.00, NULL),
(63, '2025-11-03', 'Penerimaan Simpanan Sukarela dari Anggota dengan ID: 2', '111', 15000.00, 0.00, NULL),
(64, '2025-11-03', 'Penerimaan Simpanan Sukarela dari Anggota dengan ID: 2', '211', 0.00, 15000.00, NULL),
(65, '2025-11-03', 'Penerimaan Simpanan Pokok dari Anggota dengan ID: 1', '111', 100000.00, 0.00, NULL),
(66, '2025-11-03', 'Penerimaan Simpanan Pokok dari Anggota dengan ID: 1', '211', 0.00, 100000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nama_koperasi` varchar(255) NOT NULL,
  `no_induk_koperasi` varchar(100) DEFAULT NULL,
  `sk_ahu` varchar(100) DEFAULT NULL,
  `no_telp` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `external_url` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_koperasi`, `no_induk_koperasi`, `sk_ahu`, `no_telp`, `email`, `external_url`, `alamat`) VALUES
(1, 'KKMP Pondok Betung', 'NIK. 00000000', 'AHU-0023617.AH.01.29.TAHUN 2025', '081234567890', 'admin@gmail.com', 'https://merahputih.kop.id/pondok-betung-kecamatan-pondok-aren', 'Jl. Pondok Betung Raya');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(11) NOT NULL,
  `tgl_pengeluaran` date NOT NULL,
  `no_akun` varchar(10) NOT NULL COMMENT 'No Akun Beban dari COA',
  `keterangan` varchar(255) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pinjaman`
--

CREATE TABLE `pinjaman` (
  `id_pinjaman` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `jumlah_pinjaman` decimal(10,2) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tenggat_waktu` date NOT NULL,
  `status` enum('belum lunas','lunas') NOT NULL DEFAULT 'belum lunas',
  `tipe_pinjaman` enum('Bulanan','Mingguan','Harian','') NOT NULL,
  `lama_angsuran` varchar(11) NOT NULL,
  `kode_pinjaman` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pinjaman`
--

INSERT INTO `pinjaman` (`id_pinjaman`, `id_anggota`, `jumlah_pinjaman`, `tgl_pinjam`, `tenggat_waktu`, `status`, `tipe_pinjaman`, `lama_angsuran`, `kode_pinjaman`) VALUES
(6, 2, 20000.00, '2025-11-02', '2025-12-01', 'belum lunas', 'Bulanan', '1', 'KSP-892151'),
(7, 6, 100000.00, '2025-11-02', '2025-11-15', 'lunas', 'Bulanan', '1', 'KSP-000966'),
(8, 4, 10000.00, '2025-11-03', '2025-11-13', 'belum lunas', 'Mingguan', '10', 'KSP-888660');

-- --------------------------------------------------------

--
-- Table structure for table `simpanan`
--

CREATE TABLE `simpanan` (
  `id_simpanan` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `jenis_simpanan` enum('pokok','wajib','sukarela') NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `tgl_simpan` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `simpanan`
--

INSERT INTO `simpanan` (`id_simpanan`, `id_anggota`, `jenis_simpanan`, `jumlah`, `tgl_simpan`) VALUES
(20, 3, 'wajib', 20000.00, '2025-10-31'),
(21, 4, 'wajib', 20000.00, '2025-10-31'),
(22, 2, 'wajib', 20000.00, '2025-10-31'),
(23, 3, 'pokok', 100000.00, '2025-10-31'),
(24, 2, 'wajib', 20000.00, '2025-11-02'),
(25, 2, 'sukarela', 15000.00, '2025-11-03'),
(26, 1, 'pokok', 100000.00, '2025-11-03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','anggota') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '$2y$10$fMplnNOGhf7qCPEES0jV9.WkVErVHCyqVnYLUhC7JSgwc5Z8hyP2W', 'admin'),
(14, 'Ela Susila', 'ela', '$2y$10$ePXqzthDJcFPQ257xb3ZE.wrRvBgNkMndxxJBMTyGt/ZBptM39XkC', 'anggota'),
(15, 'Lilis Saadah', 'lisa2', '$2y$10$anf9Y2/Kou.tGiwwgd3RP.ZIS3iet311LBCs9PXu3JivfHUS.LDlS', 'anggota');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`),
  ADD UNIQUE KEY `no_ktp` (`no_ktp`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `angsuran`
--
ALTER TABLE `angsuran`
  ADD PRIMARY KEY (`id_angsuran`),
  ADD KEY `id_pinjaman` (`id_pinjaman`);

--
-- Indexes for table `coa`
--
ALTER TABLE `coa`
  ADD PRIMARY KEY (`no_akun`);

--
-- Indexes for table `jurnal_umum`
--
ALTER TABLE `jurnal_umum`
  ADD PRIMARY KEY (`id_jurnal`),
  ADD KEY `no_akun` (`no_akun`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `no_akun` (`no_akun`);

--
-- Indexes for table `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD PRIMARY KEY (`id_pinjaman`),
  ADD KEY `id_anggota` (`id_anggota`);

--
-- Indexes for table `simpanan`
--
ALTER TABLE `simpanan`
  ADD PRIMARY KEY (`id_simpanan`),
  ADD KEY `id_anggota` (`id_anggota`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `angsuran`
--
ALTER TABLE `angsuran`
  MODIFY `id_angsuran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jurnal_umum`
--
ALTER TABLE `jurnal_umum`
  MODIFY `id_jurnal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pinjaman`
--
ALTER TABLE `pinjaman`
  MODIFY `id_pinjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `simpanan`
--
ALTER TABLE `simpanan`
  MODIFY `id_simpanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `angsuran`
--
ALTER TABLE `angsuran`
  ADD CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`id_pinjaman`) REFERENCES `pinjaman` (`id_pinjaman`);

--
-- Constraints for table `jurnal_umum`
--
ALTER TABLE `jurnal_umum`
  ADD CONSTRAINT `jurnal_umum_ibfk_1` FOREIGN KEY (`no_akun`) REFERENCES `coa` (`no_akun`);

--
-- Constraints for table `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`);

--
-- Constraints for table `simpanan`
--
ALTER TABLE `simpanan`
  ADD CONSTRAINT `simpanan_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
