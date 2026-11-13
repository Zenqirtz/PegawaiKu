<?php
// ============================================================
// proses/hapus.php
// DELETE (Backend): Menghapus satu data pegawai dari database
// Form daftar mengirim id pegawai menggunakan metode POST
// ============================================================

declare(strict_types=1);

// Muat koneksi database, session, dan helper
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../config/upload.php';

// Hanya user yang sudah login boleh menghapus data
checkLogin();

// --- 1. Cek Metode Request ---
// Hapus menggunakan POST, bukan GET, untuk mencegah link hapus tidak sengaja
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pegawai/index.php');
    exit;
}

// --- 2. Verifikasi CSRF Token ---
// Cocokkan token dari form dengan token di session
$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    exit('Token keamanan tidak valid. Silakan kembali, muat ulang halaman, lalu coba lagi.');
}

// --- 3. Ambil dan Validasi ID Pegawai ---
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    setFlash('danger', 'ID pegawai tidak valid.');
    header('Location: ../pegawai/index.php');
    exit;
}

// Ambil info foto sebelum dihapus dari database
$stmtFoto = $pdo->prepare('SELECT foto FROM employees WHERE id = :id');
$stmtFoto->execute(['id' => $id]);
$fotoToDelete = $stmtFoto->fetchColumn();

// --- 4. Jalankan Query DELETE dengan Prepared Statement ---
// WHERE id memastikan hanya pegawai dengan ID tersebut yang terhapus
$stmt = $pdo->prepare('DELETE FROM employees WHERE id = :id');
$stmt->execute(['id' => $id]);

// rowCount() memberi tahu apakah ada baris yang benar-benar terhapus
if ($stmt->rowCount() > 0) {
    pegawaiPhotoDelete($fotoToDelete ?: null);
    setFlash('success', 'Data pegawai berhasil dihapus.');
} else {
    setFlash('danger', 'Data pegawai tidak ditemukan atau sudah dihapus.');
}

// --- 5. Redirect ke daftar (pola PRG) ---
header('Location: ../pegawai/index.php');
exit;
