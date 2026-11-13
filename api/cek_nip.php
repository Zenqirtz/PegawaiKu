<?php
// ============================================================
// api/cek_nip.php
// Endpoint AJAX (jQuery): Cek ketersediaan dan duplikasi NIP secara asinkron
// Digunakan secara real-time pada form tambah & edit pegawai (SKKNI MUK JWD 2.b)
// Method: GET
// Parameter:
//   - nip: string (NIP yang sedang diketik pengguna)
//   - exclude_id: int (opsional, ID pegawai saat mode edit)
// Return: JSON { status: "available"|"duplicate"|"empty", available: bool, message: string }
// ============================================================

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Muat koneksi database dan helper session
require __DIR__ . '/../config/koneksi.php';

// Pastikan hanya pengguna yang login yang dapat melakukan pengecekan
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status'    => 'error',
        'available' => false,
        'message'   => 'Sesi login telah berakhir. Silakan login kembali.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$nip = trim((string) ($_GET['nip'] ?? ''));
$excludeId = isset($_GET['exclude_id']) ? (int) $_GET['exclude_id'] : 0;

// Validasi input kosong
if ($nip === '') {
    echo json_encode([
        'status'    => 'empty',
        'available' => false,
        'message'   => 'NIP tidak boleh kosong.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Format dasar: NIP harus berupa angka
$nipClean = preg_replace('/\s+/', '', $nip);
if (!ctype_digit($nipClean)) {
    echo json_encode([
        'status'    => 'invalid',
        'available' => false,
        'message'   => 'NIP hanya boleh berisi karakter angka (0-9).'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cek apakah NIP sudah dipakai pegawai lain di database (cek format dengan spasi maupun tanpa spasi)
if ($excludeId > 0) {
    // Mode edit: kecualikan record milik pegawai itu sendiri
    $stmt = $pdo->prepare('SELECT id, nama, jabatan FROM employees WHERE (REPLACE(nip, " ", "") = :nip_clean OR nip = :nip_raw) AND id <> :id LIMIT 1');
    $stmt->execute([
        'nip_clean' => $nipClean,
        'nip_raw'   => $nip,
        'id'        => $excludeId
    ]);
} else {
    // Mode tambah baru: cek seluruh record
    $stmt = $pdo->prepare('SELECT id, nama, jabatan FROM employees WHERE REPLACE(nip, " ", "") = :nip_clean OR nip = :nip_raw LIMIT 1');
    $stmt->execute([
        'nip_clean' => $nipClean,
        'nip_raw'   => $nip
    ]);
}

$existing = $stmt->fetch();

if ($existing) {
    echo json_encode([
        'status'    => 'duplicate',
        'available' => false,
        'message'   => 'NIP sudah digunakan oleh: ' . $existing['nama'] . ' (' . ($existing['jabatan'] ?: 'Staf') . ')',
        'data'      => [
            'id'      => (int) $existing['id'],
            'nama'    => $existing['nama'],
            'jabatan' => $existing['jabatan']
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    $digitCount = strlen($nipClean);
    $statusText = $digitCount === 18 
        ? 'NIP valid (18 digit) & tersedia untuk didaftarkan.' 
        : 'NIP tersedia (' . $digitCount . ' digit). Standar resmi 18 digit.';

    echo json_encode([
        'status'    => 'available',
        'available' => true,
        'digits'    => $digitCount,
        'message'   => $statusText
    ], JSON_UNESCAPED_UNICODE);
}
