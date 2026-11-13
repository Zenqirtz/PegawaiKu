<?php
// ============================================================
// api/detail.php
// Endpoint AJAX (jQuery): Mengambil detail data pegawai via JSON
// Digunakan untuk Quick View Modal tanpa reload halaman (SKKNI MUK JWD 2.b)
// Method: GET
// Parameter: ?id=... (int)
// Return: JSON { status: "success"|"error", data: {...} }
// ============================================================

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Muat koneksi database dan helper session
require __DIR__ . '/../config/koneksi.php';

// Pastikan hanya user login yang bisa mengakses
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID pegawai tidak valid.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare('SELECT id, nip, nama, email, jabatan, status, foto FROM employees WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$employee = $stmt->fetch();

if (!$employee) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data pegawai tidak ditemukan.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Format inisial nama untuk avatar
$nameParts = preg_split('/\s+/', trim((string) $employee['nama'])) ?: [];
$initials = mb_strtoupper(
    mb_substr($nameParts[0] ?? 'P', 0, 1) . 
    (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : '')
);

echo json_encode([
    'status' => 'success',
    'data'   => [
        'id'       => (int) $employee['id'],
        'nip'      => (string) $employee['nip'],
        'nama'     => (string) $employee['nama'],
        'email'    => (string) $employee['email'],
        'jabatan'  => (string) ($employee['jabatan'] ?: 'Staf Pelaksana'),
        'status'   => (string) ($employee['status'] ?? 'aktif'),
        'initial'  => $initials,
        'foto'     => (string) ($employee['foto'] ?? ''),
    ]
], JSON_UNESCAPED_UNICODE);
