<?php
// ============================================================
// config/koneksi.php
// File konfigurasi utama: koneksi database, session, helper
// ============================================================

declare(strict_types=1);

// --- Mulai session (untuk login, flash message, CSRF) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Konfigurasi koneksi database MySQL ---
$dbHost = '127.0.0.1';        // Alamat server MySQL
$dbName = 'latihan_bnsp_jwd'; // Nama database
$dbUser = 'root';              // Username MySQL
$dbPass = '';                  // Password MySQL (kosong di Laragon default)

// DSN (Data Source Name) untuk PDO
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

// Opsi PDO:
// - ERRMODE_EXCEPTION: error query langsung jadi exception (mudah debug)
// - FETCH_ASSOC: hasil query jadi array asosiatif (pakai nama kolom)
// - EMULATE_PREPARES false: pakai prepared statement asli dari MySQL
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Buat koneksi PDO ke database
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Kalau koneksi gagal, tampilkan pesan error dan hentikan script
    http_response_code(500);
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// --- Generate CSRF token (untuk keamanan form) ---
// Token ini disimpan di session dan dicek saat form di-submit
// Mencegah serangan Cross-Site Request Forgery (CSRF)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// FUNGSI HELPER (digunakan oleh semua halaman aplikasi)
// ============================================================

/**
 * e() - Escape output HTML (mencegah serangan XSS)
 * Setiap data yang ditampilkan ke browser WAJIB lewat fungsi ini.
 * Mengubah karakter spesial (like < > & " ') menjadi entitas HTML
 * agar tidak bisa dieksekusi sebagai skrip oleh browser.
 */
if (!function_exists('e')) {
    function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('setFlash')) {
    /**
     * setFlash() - Simpan pesan notifikasi ke session
     * Pesan ini tampil sekali setelah redirect (pola PRG)
     * @param string $type  'success' atau 'danger'
     * @param string $message  Isi pesan
     */
    function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('getFlash')) {
    /**
     * getFlash() - Ambil pesan flash lalu hapus dari session
     * Dipanggil di halaman yang menampilkan notifikasi
     */
    function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('checkLogin')) {
    /**
     * checkLogin() - Cek apakah user sudah login
     * Kalau belum login, redirect ke halaman login
     */
    function checkLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /pegawaiku_jwd/login.php');
            exit;
        }
    }
}

if (!function_exists('basePath')) {
    /**
     * basePath() - Menentukan path relatif ke root project
     * Berguna karena halaman ada di subfolder (pegawai/)
     */
    function basePath(): string
    {
        // Hanya halaman di folder pegawai/ yang perlu naik satu level.
        // Jangan pakai str_contains('/pegawai'): nama project pegawaiku_jwd
        // juga mengandung kata tersebut.
        $scriptDir = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        if (basename($scriptDir) === 'pegawai') {
            return '..';
        }
        return '.';
    }
}
