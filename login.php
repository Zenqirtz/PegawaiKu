<?php
// ============================================================
// login.php
// Halaman Login PegawaiKu
// Verifikasi kredensial user dari tabel users (MySQL)
// ============================================================

declare(strict_types=1);

// Muat koneksi database dan helper (otomatis session_start)
require __DIR__ . '/config/koneksi.php';

// Jika user sudah login sebelumnya, langsung arahkan ke dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Proses form login saat metode HTTP adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    // Validasi input: pastikan tidak ada yang kosong
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Query cari user berdasarkan username menggunakan prepared statement
        $stmt = $pdo->prepare('SELECT id, name, username, password FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Cek password hash bcrypt dengan password_verify()
        // password_verify() aman dari timing attacks
        if ($user && password_verify($password, $user['password'])) {
            // Cegah Session Fixation: ganti session ID baru setelah autentikasi sukses
            session_regenerate_id(true);

            // Simpan info user di session
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Redirect ke halaman dashboard
            header('Location: dashboard.php');
            exit;
        }

        // Pesan error umum: jangan sebut "password salah" secara spesifik untuk keamanan
        $error = 'Username atau password tidak sesuai.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | PegawaiKu</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- CSS Custom -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card p-4 shadow-sm fade-in" style="width: 100%; max-width: 420px;">

        <!-- Header Kartu: Logo dan Judul -->
        <div class="text-center mb-4">
            <img src="assets/images/logo.png" alt="Logo Aplikasi" height="56" class="mb-3">
            <h1 class="h4 fw-bold mb-1">PegawaiKu</h1>
            <p class="text-secondary small mb-0">Masuk untuk mengelola data pegawai dengan PegawaiKu</p>
        </div>

        <!-- Pesan error login jika salah -->
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Login dengan Validasi Sisi Klien (SKKNI MUK JWD Butir 1.b) -->
        <form id="loginForm" method="post" action="login.php" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Masukkan username" autofocus required>
                    <div class="invalid-feedback">Username wajib diisi.</div>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Masukkan password" required>
                    <div class="invalid-feedback">Password wajib diisi.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>

        <!-- Petunjuk Akun Demo untuk Penguji/Asesor -->
        <div class="mt-4 p-2 bg-light rounded border text-center small text-secondary">
            Akun Demo: <code>admin</code> / <code>admin123</code>
        </div>

        <p class="text-center text-secondary small mt-3 mb-0">PegawaiKu &bull; Latihan BNSP JWD &bull; UKOM 2026</p>
    </div>
</div>

<!-- jQuery CDN untuk manipulasi DOM & validasi cepat -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Validasi Form Sisi Klien (Client-side Validation) -->
<script>
$(function () {
    'use strict';

    const $form = $('#loginForm');
    const $username = $('#username');
    const $password = $('#password');

    $form.on('submit', function (e) {
        let valid = true;

        if ($.trim($username.val()) === '') {
            $username.addClass('is-invalid');
            valid = false;
        } else {
            $username.removeClass('is-invalid');
        }

        if ($.trim($password.val()) === '') {
            $password.addClass('is-invalid');
            valid = false;
        } else {
            $password.removeClass('is-invalid');
        }

        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
            if ($.trim($username.val()) === '') {
                $username.focus();
            } else {
                $password.focus();
            }
        }
    });

    // Hapus kelas is-invalid seketika pengguna mengetik
    $username.add($password).on('input', function () {
        if ($.trim($(this).val()) !== '') {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
</body>
</html>
