-- ============================================================
-- pegawai_bnsp.sql
-- Database & Tabel PegawaiKu - BNSP (Junior Web Developer)
-- Sesuai rancangan slide pengayaan skenario Ujikom JWD:
-- id, nip, nama, email, jabatan
-- ============================================================

CREATE DATABASE IF NOT EXISTS latihan_bnsp_jwd
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE latihan_bnsp_jwd;

-- ------------------------------------------------------------
-- 1. Tabel users untuk Autentikasi Login (Halaman Login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. Tabel employees (Sesuai contoh slide SQL pengayaan BNSP)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    jabatan VARCHAR(80),
    foto VARCHAR(255) DEFAULT NULL
);

-- ------------------------------------------------------------
-- 3. Akun Demo Penguji / Asesi
-- Username: admin | Password: admin123
-- ------------------------------------------------------------
INSERT INTO users (name, username, password)
VALUES ('Administrator', 'admin', '$2y$12$Af4ObAKKtFx.NUNowREWReY3gu/tdBmfcPp0s/3x5M/WBdu2CTJA2')
ON DUPLICATE KEY UPDATE id=id;

-- ------------------------------------------------------------
-- 4. Data Awal Pegawai (Contoh data CRUD)
-- ------------------------------------------------------------
INSERT INTO employees (nip, nama, email, jabatan) VALUES
('19980110 202301 2 001', 'Alya Putri', 'alya.putri@gmail.com', 'Web Developer'),
('19940524 202012 1 002', 'Bima Pratama', 'bima.pratama@gmail.com', 'Web Developer'),
('19960815 202203 2 003', 'Citra Lestari', 'citra.lestari@gmail.com', 'Web Developer'),
('19880412 201402 1 001', 'Budi Santoso', 'budi.santoso@gmail.com', 'Web Developer'),
('19920315 201801 2 003', 'Siti Nurhaliza', 'siti.nurhaliza@gmail.com', 'Web Developer'),
('19940722 201903 1 004', 'Ahmad Fauzi', 'ahmad.fauzi@gmail.com', 'Web Developer'),
('19960110 202102 2 002', 'Dewi Sartika Putri', 'dewi.sartika@gmail.com', 'Web Developer'),
('19951105 202012 1 005', 'Rian Hidayat', 'rian.hidayat@gmail.com', 'Web Developer'),
('19910830 201704 2 001', 'Anisa Rahmawati', 'anisa.rahmawati@gmail.com', 'Web Developer'),
('19930514 201803 1 007', 'Hendro Prasetyo', 'hendro.prasetyo@gmail.com', 'Web Developer'),
('19970920 202201 1 003', 'Fajar Ramadhan', 'fajar.ramadhan@gmail.com', 'Web Developer'),
('19990418 202302 2 006', 'Nadia Permata', 'nadia.permata@gmail.com', 'Web Developer'),
('19890214 201503 1 008', 'Dedi Setyawan', 'dedi.setyawan@gmail.com', 'Web Developer'),
('19920621 201801 2 004', 'Lilis Suryani', 'lilis.suryani@gmail.com', 'Web Developer'),
('19901011 201602 1 005', 'Agus Saputra', 'agus.saputra@gmail.com', 'UI/UX Designer'),
('19950319 202001 2 007', 'Rina Marlina', 'rina.marlina@gmail.com', 'UI/UX Designer'),
('19961205 202103 1 006', 'Yoga Pratama', 'yoga.pratama@gmail.com', 'UI/UX Designer'),
('19930817 201901 1 009', 'Dwi Permana', 'dwi.permana@gmail.com', 'UI/UX Designer'),
('19970522 202202 2 008', 'Maya Sari', 'maya.sari@gmail.com', 'UI/UX Designer'),
('19910408 201703 1 010', 'Herdianto', 'herdianto@gmail.com', 'UI/UX Designer'),
('19941126 202002 1 011', 'Septian Wibowo', 'septian.wibowo@gmail.com', 'UI/UX Designer'),
('19980714 202301 2 009', 'Fitria Ayu', 'fitria.ayu@gmail.com', 'UI/UX Designer'),
('19870305 201302 1 002', 'Joko Susilo', 'joko.susilo@gmail.com', 'UI/UX Designer'),
('19960918 202101 2 010', 'Eka Purnama', 'eka.purnama@gmail.com', 'UI/UX Designer'),
('19990130 202303 2 011', 'Vina Oktavia', 'vina.oktavia@gmail.com', 'UI/UX Designer'),
('19861112 201201 1 001', 'Bambang Purnomo', 'bambang.purnomo@gmail.com', 'System Analyst'),
('19920824 201802 1 012', 'Teguh Wijaya', 'teguh.wijaya@gmail.com', 'System Analyst'),
('19950416 202003 2 012', 'Nurul Hidayah', 'nurul.hidayah@gmail.com', 'System Analyst'),
('19971003 202201 2 013', 'Dian Saputri', 'dian.saputri@gmail.com', 'System Analyst'),
('19940315 201902 1 013', 'Rio Danendra', 'rio.danendra@gmail.com', 'System Analyst'),
('19981208 202302 2 014', 'Aulia Ramadhani', 'aulia.ramadhani@gmail.com', 'System Analyst'),
('19930729 201803 2 015', 'Heni Widiyanti', 'heni.widiyanti@gmail.com', 'System Analyst'),
('19950904 202102 1 014', 'Zaki Fadlillah', 'zaki.fadlillah@gmail.com', 'System Analyst'),
('19960228 202101 2 016', 'Intan Permadi', 'intan.permadi@gmail.com', 'System Analyst'),
('19970617 202203 1 015', 'Rafly Maulana', 'rafly.maulana@gmail.com', 'System Analyst'),
('19910512 201701 2 017', 'Siti Aisyah', 'siti.aisyah@gmail.com', 'Project Manager'),
('19900923 201603 1 016', 'Arif Setiawan', 'arif.setiawan@gmail.com', 'Project Manager'),
('19941201 202001 2 018', 'Lestari Dewi', 'lestari.dewi@gmail.com', 'Project Manager'),
('19930118 201802 1 017', 'Taufik Hidayah', 'taufik.hidayah@gmail.com', 'Project Manager'),
('19980327 202301 2 019', 'Desi Kurniawati', 'desi.kurniawati@gmail.com', 'Project Manager'),
('19950810 202002 1 018', 'Andi Saputra', 'andi.saputra@gmail.com', 'Project Manager'),
('19971109 202202 2 020', 'Dita Aprillia', 'dita.aprillia@gmail.com', 'Project Manager'),
('19940602 201901 1 019', 'Fatur Rahman', 'fatur.rahman@gmail.com', 'Project Manager'),
('19990815 202303 2 021', 'Novianti', 'novianti@gmail.com', 'Database Administrator'),
('19920406 201702 1 020', 'Heri Susanto', 'heri.susanto@gmail.com', 'Database Administrator'),
('19960521 202103 2 022', 'Mega Silvia', 'mega.silvia@gmail.com', 'Database Administrator'),
('19931030 201801 1 021', 'Bayu Nugroho', 'bayu.nugroho@gmail.com', 'Database Administrator'),
('19950114 202003 2 023', 'Wulan Dari', 'wulan.dari@gmail.com', 'Database Administrator'),
('19970225 202201 1 022', 'Rizky Pratama', 'rizky.pratama@gmail.com', 'Database Administrator'),
('19980619 202302 2 024', 'Putri Ayu Wandira', 'putri.ayu@gmail.com', 'Database Administrator')
ON DUPLICATE KEY UPDATE id=id;
