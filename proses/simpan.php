<?php
// ============================================================
// proses/simpan.php
// CREATE (Backend): Menerima POST dari form tambah pegawai,
// melakukan validasi di server, dan menyimpan ke database
// Kolom: nip, nama, email, jabatan
// ============================================================

declare(strict_types=1);

// Muat koneksi database dan fungsi helper
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../config/upload.php';

// Pastikan hanya user login yang bisa mengeksekusi
checkLogin();

// --- 1. Cek Metode Request ---
// File proses ini HANYA menerima request POST (bukan diakses langsung via URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pegawai/index.php');
    exit;
}

// --- 2. Verifikasi Token CSRF ---
// Mencegah request palsu yang dikirim oleh pihak ketiga dari luar aplikasi
$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    setFlash('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
    header('Location: ../pegawai/tambah.php');
    exit;
}

// --- 3. Ambil dan Bersihkan Data Input ---
// trim() menghapus spasi kosong yang tidak sengaja terketik di awal/akhir
$nipNama         = trim((string) ($_POST['nip'] ?? ''));
$namaPegawai     = trim((string) ($_POST['nama'] ?? ''));
$emailPegawai    = trim((string) ($_POST['email'] ?? ''));
$jabatanPegawai  = trim((string) ($_POST['jabatan'] ?? ''));
$statusPegawai   = strtolower(trim((string) ($_POST['status'] ?? 'aktif')));

// Whitelist status: nilai di luar daftar ini ditolak/dianggap 'aktif'.
if (!in_array($statusPegawai, ['aktif', 'izin', 'cuti'], true)) {
    $statusPegawai = 'aktif';
}

// NIP input may contain spaces for readability; remove whitespace before validation/storage checks.
$nipClean = preg_replace('/\s+/', '', $nipNama) ?? '';

// --- 4. Validasi Sisi Server (Server-side Validation) ---
// Validasi server WAJIB ada, karena validasi browser (JavaScript) bisa dilewati/dimatikan.
if ($nipNama === '' || $namaPegawai === '' || $emailPegawai === '' || $jabatanPegawai === '') {
    setFlash('danger', 'Semua kolom bertanda bintang (*) wajib diisi.');
    header('Location: ../pegawai/tambah.php');
    exit;
}

// Validasi format email menggunakan filter bawaan PHP
if (!filter_var($emailPegawai, FILTER_VALIDATE_EMAIL)) {
    setFlash('danger', 'Format penulisan email tidak valid.');
    header('Location: ../pegawai/tambah.php');
    exit;
}

// --- 5. Validasi Format & Panjang NIP (Standar Resmi 18 Digit Angka) ---
if (!ctype_digit($nipClean)) {
    setFlash('danger', 'NIP hanya boleh berisi angka (0-9).');
    header('Location: ../pegawai/tambah.php');
    exit;
}

$digitCount = strlen($nipClean);
if ($digitCount !== 18) {
    setFlash('danger', 'NIP wajib tepat 18 digit angka (saat ini ' . $digitCount . ' digit). Periksa kembali NIP yang Anda masukkan.');
    header('Location: ../pegawai/tambah.php');
    exit;
}

// --- 6. Cek Duplikasi NIP ---
// NIP di tabel berstatus UNIQUE. Pencarian dilakukan dengan dua variasi:
// - REPLACE(nip, ' ', '') = format tanpa spasi (antisipasi input 18 digit polos)
// - nip = format asli yang tersimpan di database
$stmtCheck = $pdo->prepare('SELECT id FROM employees WHERE REPLACE(nip, " ", "") = :nip_clean OR nip = :nip_raw LIMIT 1');
$stmtCheck->execute([
    'nip_clean' => $nipClean,
    'nip_raw'   => $nipNama,
]);

if ($stmtCheck->fetch()) {
    setFlash('danger', 'NIP sudah terdaftar di sistem. Gunakan NIP lain.');
    header('Location: ../pegawai/tambah.php');
    exit;
}

// --- 7. Penanganan Upload Pasfoto ---
// Foto opsional: tidak wajib diisi. Jika diunggah, validasi di config/upload.php (max 2MB, JPG/PNG).
$fotoPath = null;
try {
    $fotoPath = pegawaiPhotoUpload($_FILES['foto'] ?? null);
} catch (Exception $e) {
    setFlash('danger', $e->getMessage());
    header('Location: ../pegawai/tambah.php');
    exit;
}

// --- 8. Eksekusi Query INSERT dengan Prepared Statement ---
// Prepared statement melindungi database dari serangan SQL Injection
$sql = 'INSERT INTO employees (nip, nama, email, jabatan, status, foto)
        VALUES (:nip, :nama, :email, :jabatan, :status, :foto)';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'nip'     => $nipNama,
    'nama'    => $namaPegawai,
    'email'   => $emailPegawai,
    'jabatan' => $jabatanPegawai,
    'status'  => $statusPegawai,
    'foto'    => $fotoPath,
]);

// --- 7. Berikan Pesan Sukses & Redirect (Pola PRG: Post-Redirect-Get) ---
// Mencegah data terkirim ganda jika pengguna me-refresh halaman browser
setFlash('success', 'Data pegawai baru berhasil ditambahkan.');
header('Location: ../pegawai/index.php');
exit;
