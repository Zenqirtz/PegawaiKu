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

// --- 6. Cek Duplikasi NIP pada Record LAIN ---
// Klausa "AND id != :id" penting: agar data pegawai ini sendiri tidak dianggap duplikat
$nipClean = preg_replace('/\s+/', '', $nipNama);
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
