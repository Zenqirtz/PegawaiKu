<?php
// ============================================================
// proses/update.php
// UPDATE (Backend): Menerima POST dari form edit pegawai,
// melakukan validasi di server, dan memperbarui data di database
// Kolom: nip, nama, email, jabatan
// ============================================================

declare(strict_types=1);

// Muat koneksi database dan fungsi helper
require __DIR__ . '/../config/koneksi.php';

// Pastikan hanya user login yang bisa mengeksekusi
checkLogin();

// --- 1. Cek Metode Request ---
// File ini hanya boleh dipanggil via metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pegawai/index.php');
    exit;
}

// --- 2. Ambil dan Validasi ID Pegawai ---
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    setFlash('danger', 'ID pegawai tidak valid.');
    header('Location: ../pegawai/index.php');
    exit;
}

// --- 3. Verifikasi Token CSRF ---
$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    setFlash('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
    header("Location: ../pegawai/edit.php?id={$id}");
    exit;
}

// --- 4. Ambil dan Bersihkan Data Input ---
$nipNama         = trim((string) ($_POST['nip'] ?? ''));
$namaPegawai     = trim((string) ($_POST['nama'] ?? ''));
$emailPegawai    = trim((string) ($_POST['email'] ?? ''));
$jabatanPegawai  = trim((string) ($_POST['jabatan'] ?? ''));

// --- 5. Validasi Server-Side ---
if ($nipNama === '' || $namaPegawai === '' || $emailPegawai === '' || $jabatanPegawai === '') {
    setFlash('danger', 'Semua kolom bertanda bintang (*) wajib diisi.');
    header("Location: ../pegawai/edit.php?id={$id}");
    exit;
}

if (!filter_var($emailPegawai, FILTER_VALIDATE_EMAIL)) {
    setFlash('danger', 'Format email tidak valid.');
    header("Location: ../pegawai/edit.php?id={$id}");
    exit;
}

// --- 6. Validasi Format & Panjang NIP (Standar Resmi 18 Digit Angka) ---
$nipClean = preg_replace('/\s+/', '', $nipNama) ?? '';
if (!ctype_digit($nipClean)) {
    setFlash('danger', 'NIP hanya boleh berisi angka (0-9).');
    header("Location: ../pegawai/edit.php?id={$id}");
    exit;
}

$digitCount = strlen($nipClean);
if ($digitCount !== 18) {
    setFlash('danger', 'NIP wajib tepat 18 digit angka (saat ini ' . $digitCount . ' digit). Periksa kembali NIP yang Anda masukkan.');
    header("Location: ../pegawai/edit.php?id={$id}");
    exit;
}

// --- 7. Cek Duplikasi NIP pada Record LAIN ---
$stmtCheck = $pdo->prepare('SELECT id FROM employees WHERE (REPLACE(nip, " ", "") = :nip_clean OR nip = :nip_raw) AND id != :id LIMIT 1');
$stmtCheck->execute([
    'nip_clean' => $nipClean,
    'nip_raw'   => $nipNama,
    'id'        => $id,
]);

if ($stmtCheck->fetch()) {
    setFlash('danger', 'NIP sudah digunakan oleh pegawai lain.');
    header("Location: ../pegawai/edit.php?id={$id}");
    exit;
}

// --- 7. Eksekusi Query UPDATE dengan Prepared Statement ---
$sql = 'UPDATE employees 
        SET nip = :nip, nama = :nama, email = :email, jabatan = :jabatan 
        WHERE id = :id';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'nip'     => $nipNama,
    'nama'    => $namaPegawai,
    'email'   => $emailPegawai,
    'jabatan' => $jabatanPegawai,
    'id'      => $id,
]);

// --- 8. Set Flash Message & Redirect ke Daftar Pegawai ---
setFlash('success', 'Data pegawai berhasil diperbarui.');
header('Location: ../pegawai/index.php');
exit;
