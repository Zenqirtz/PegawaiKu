<?php
// ============================================================
// index.php
// File gerbang awal (Router sederhana)
// Jika sudah login -> diarahkan ke dashboard
// Jika belum login -> diarahkan ke form login
// ============================================================

declare(strict_types=1);

// Muat konfigurasi session
require __DIR__ . '/config/koneksi.php';

// Cek status session user
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
