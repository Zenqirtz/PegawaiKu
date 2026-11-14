-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 13, 2026 at 05:27 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `latihan_bnsp_jwd`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jabatan` varchar(80) DEFAULT NULL,
  `status` enum('aktif','izin','cuti') NOT NULL DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `nip`, `nama`, `email`, `jabatan`, `status`, `foto`) VALUES
(1, '19980110 202301 2 001', 'Alya Putri', 'alya.putri@gmail.com', 'Web Developer', 'aktif', NULL),
(2, '19940524 202012 1 002', 'Bima Pratama', 'bima.pratama@gmail.com', 'Web Developer', 'aktif', NULL),
(3, '19960815 202203 2 003', 'Citra Lestari', 'citra.lestari@gmail.com', 'Web Developer', 'cuti', NULL),
(4, '19880412 201402 1 001', 'Budi Santoso', 'budi.santoso@gmail.com', 'Web Developer', 'aktif', NULL),
(5, '19920315 201801 2 003', 'Siti Nurhaliza', 'siti.nurhaliza@gmail.com', 'Web Developer', 'izin', NULL),
(6, '19940722 201903 1 004', 'Ahmad Fauzi', 'ahmad.fauzi@gmail.com', 'Web Developer', 'aktif', NULL),
(7, '19960110 202102 2 002', 'Dewi Sartika Putri', 'dewi.sartika@gmail.com', 'Web Developer', 'cuti', NULL),
(8, '19951105 202012 1 005', 'Rian Hidayat', 'rian.hidayat@gmail.com', 'Web Developer', 'aktif', NULL),
(9, '19910830 201704 2 001', 'Anisa Rahmawati', 'anisa.rahmawati@gmail.com', 'Web Developer', 'aktif', NULL),
(10, '19930514 201803 1 007', 'Hendro Prasetyo', 'hendro.prasetyo@gmail.com', 'Web Developer', 'aktif', NULL),
(11, '19970920 202201 1 003', 'Fajar Ramadhan', 'fajar.ramadhan@gmail.com', 'Web Developer', 'aktif', NULL),
(12, '19990418 202302 2 006', 'Nadia Permata', 'nadia.permata@gmail.com', 'Web Developer', 'cuti', NULL),
(13, '19890214 201503 1 008', 'Dedi Setyawan', 'dedi.setyawan@gmail.com', 'Web Developer', 'aktif', NULL),
(14, '19920621 201801 2 004', 'Lilis Suryani', 'lilis.suryani@gmail.com', 'Web Developer', 'aktif', NULL),
(15, '19901011 201602 1 005', 'Agus Saputra', 'agus.saputra@gmail.com', 'UI/UX Designer', 'izin', NULL),
(16, '19950319 202001 2 007', 'Rina Marlina', 'rina.marlina@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(17, '19961205 202103 1 006', 'Yoga Pratama', 'yoga.pratama@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(18, '19930817 201901 1 009', 'Dwi Permana', 'dwi.permana@gmail.com', 'UI/UX Designer', 'cuti', NULL),
(19, '19970522 202202 2 008', 'Maya Sari', 'maya.sari@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(20, '19910408 201703 1 010', 'Herdianto', 'herdianto@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(21, '19941126 202002 1 011', 'Septian Wibowo', 'septian.wibowo@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(22, '19980714 202301 2 009', 'Fitria Ayu', 'fitria.ayu@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(23, '19870305 201302 1 002', 'Joko Susilo', 'joko.susilo@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(24, '19960918 202101 2 010', 'Eka Purnama', 'eka.purnama@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(25, '19990130 202303 2 011', 'Vina Oktavia', 'vina.oktavia@gmail.com', 'UI/UX Designer', 'aktif', NULL),
(26, '19861112 201201 1 001', 'Bambang Purnomo', 'bambang.purnomo@gmail.com', 'System Analyst', 'aktif', NULL),
(27, '19920824 201802 1 012', 'Teguh Wijaya', 'teguh.wijaya@gmail.com', 'System Analyst', 'aktif', NULL),
(28, '19950416 202003 2 012', 'Nurul Hidayah', 'nurul.hidayah@gmail.com', 'System Analyst', 'aktif', NULL),
(29, '19971003 202201 2 013', 'Dian Saputri', 'dian.saputri@gmail.com', 'System Analyst', 'aktif', NULL),
(30, '19940315 201902 1 013', 'Rio Danendra', 'rio.danendra@gmail.com', 'System Analyst', 'aktif', NULL),
(31, '19981208 202302 2 014', 'Aulia Ramadhani', 'aulia.ramadhani@gmail.com', 'System Analyst', 'aktif', NULL),
(32, '19930729 201803 2 015', 'Heni Widiyanti', 'heni.widiyanti@gmail.com', 'System Analyst', 'aktif', NULL),
(33, '19950904 202102 1 014', 'Zaki Fadlillah', 'zaki.fadlillah@gmail.com', 'System Analyst', 'aktif', NULL),
(34, '19960228 202101 2 016', 'Intan Permadi', 'intan.permadi@gmail.com', 'System Analyst', 'aktif', NULL),
(35, '19970617 202203 1 015', 'Rafly Maulana', 'rafly.maulana@gmail.com', 'System Analyst', 'aktif', NULL),
(36, '19910512 201701 2 017', 'Siti Aisyah', 'siti.aisyah@gmail.com', 'Project Manager', 'aktif', NULL),
(37, '19900923 201603 1 016', 'Arif Setiawan', 'arif.setiawan@gmail.com', 'Project Manager', 'aktif', NULL),
(38, '19941201 202001 2 018', 'Lestari Dewi', 'lestari.dewi@gmail.com', 'Project Manager', 'aktif', NULL),
(39, '19930118 201802 1 017', 'Taufik Hidayah', 'taufik.hidayah@gmail.com', 'Project Manager', 'aktif', NULL),
(40, '19980327 202301 2 019', 'Desi Kurniawati', 'desi.kurniawati@gmail.com', 'Project Manager', 'aktif', NULL),
(41, '19950810 202002 1 018', 'Andi Saputra', 'andi.saputra@gmail.com', 'Project Manager', 'aktif', NULL),
(42, '19971109 202202 2 020', 'Dita Aprillia', 'dita.aprillia@gmail.com', 'Project Manager', 'aktif', NULL),
(43, '19940602 201901 1 019', 'Fatur Rahman', 'fatur.rahman@gmail.com', 'Project Manager', 'aktif', NULL),
(44, '19990815 202303 2 021', 'Novianti', 'novianti@gmail.com', 'Database Administrator', 'aktif', NULL),
(45, '19920406 201702 1 020', 'Heri Susanto', 'heri.susanto@gmail.com', 'Database Administrator', 'aktif', NULL),
(46, '19960521 202103 2 022', 'Mega Silvia', 'mega.silvia@gmail.com', 'Database Administrator', 'aktif', NULL),
(47, '19931030 201801 1 021', 'Bayu Nugroho', 'bayu.nugroho@gmail.com', 'Database Administrator', 'aktif', NULL),
(48, '19950114 202003 2 023', 'Wulan Dari', 'wulan.dari@gmail.com', 'Database Administrator', 'aktif', NULL),
(49, '19970225 202201 1 022', 'Rizky Pratama', 'rizky.pratama@gmail.com', 'Database Administrator', 'aktif', NULL),
(50, '19980619 202302 2 021', 'Putri Ayu Wandira', 'putri.ayu@gmail.com', 'Database Administrator', 'aktif', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$12$Af4ObAKKtFx.NUNowREWReY3gu/tdBmfcPp0s/3x5M/WBdu2CTJA2', '2026-11-10 14:59:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
