<?php
// ============================================================
// logout.php
// Menghapus seluruh session user dan mengarahkan kembali ke login
// ============================================================

declare(strict_types=1);

// Muat konfigurasi (session_start)
require __DIR__ . '/config/koneksi.php';

// 1. Kosongkan semua data dalam array $_SESSION
$_SESSION = [];

// 2. Hapus cookie session dari browser pengguna jika aktif
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Hancurkan file session di server
session_destroy();

// 4. Arahkan kembali ke halaman login
header('Location: login.php');
exit;
