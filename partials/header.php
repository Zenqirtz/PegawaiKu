<?php
// ============================================================
// partials/header.php
// Template layout bagian atas: <head>, sidebar navigasi, topbar.
// Disertakan (require) di setiap halaman yang membutuhkan login.
// Variabel yang dapat diisi halaman pemanggil:
//   $pageTitle  -> teks judul tab browser
//   $activePage -> penanda menu aktif ('dashboard' / 'pegawai')
// ============================================================

// $base = path relatif menuju root project ('..' jika halaman di subfolder)
$base = basePath();
// Nama user dari session, dipakai pada kartu profil sidebar & avatar topbar
$userName = $_SESSION['user_name'] ?? 'Budi Santoso';
// Huruf pertama nama, ditampilkan sebagai avatar berbentuk lingkaran
$firstLetter = mb_strtoupper(mb_substr($userName, 0, 1));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Base URL project, dibaca JavaScript (jQuery AJAX) untuk menyusun endpoint API -->
    <meta name="app-base" content="<?= e($base) ?>">
    <title><?= e($pageTitle ?? 'PegawaiKu') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $base ?>/assets/images/logo.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter / Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS Custom PegawaiKu; filemtime memaksa browser mengambil versi terbaru -->
    <link href="<?= $base ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/ajax.css?v=<?= filemtime(__DIR__ . '/../assets/css/ajax.css') ?>" rel="stylesheet">
</head>
<body>

<div id="wrapper">

    <!-- ========== SIDEBAR NAVIGASI ========== -->
    <aside class="sidebar" id="sidebar">

        <!-- Header Sidebar: Logo Icon + Brand + Subtitle + Toggle -->
        <div class="sidebar-header">
            <div class="sidebar-brand-box">
                <img class="brand-avatar-icon" src="<?= $base ?>/assets/images/logo.png" alt="Logo PegawaiKu">
                <div class="brand-text">
                    <div class="brand-title">PegawaiKu</div>
                    <div class="brand-sub">Sistem Kepegawaian</div>
                </div>
            </div>
            <!-- Tombol ciutkan sidebar (desktop) -->
            <button class="sidebar-toggle-btn" id="sidebarCollapseBtn" type="button" title="Ciutkan / Lebarkan sidebar" aria-label="Toggle Sidebar">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>
        </div>

        <!-- Menu Navigasi Utama -->
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="<?= $base ?>/dashboard.php"
                   class="sidebar-nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
                   data-tooltip="Dashboard">
                    <i class="bi bi-grid-fill"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="<?= $base ?>/pegawai/index.php"
                   class="sidebar-nav-link <?= ($activePage ?? '') === 'pegawai' ? 'active' : '' ?>"
                   data-tooltip="Data Pegawai">
                    <i class="bi bi-people-fill"></i>
                    <span class="nav-label">Data Pegawai</span>
                </a>
            </li>
        </ul>

        <!-- Footer Sidebar: User Card + Tombol Logout -->
        <div class="sidebar-footer">
            <div class="sidebar-user-card">
                <div class="user-avatar-circle">
                    <?= e($firstLetter) ?>
                </div>
                <div class="user-info-text">
                    <div class="user-name-title"><?= e($userName) ?></div>
                    <div class="user-role-sub">Super Admin</div>
                </div>
                <a href="<?= $base ?>/logout.php" class="sidebar-logout-icon" title="Keluar / Logout" data-tooltip="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

    </aside>
    <!-- ========== AKHIR SIDEBAR ========== -->

    <!-- ========== AREA KONTEN UTAMA ========== -->
    <div class="content-wrapper">

        <!-- Topbar Modern -->
        <header class="topbar">
            <!-- Mobile Toggle (hanya tampil di layar kecil/mobile, disembunyikan di desktop) -->
            <button class="topbar-toggle d-lg-none" id="sidebarMobileToggle" type="button" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>

            <!-- Breadcrumb Navigation: tampil nama aplikasi di topbar tengah -->
            <div class="topbar-breadcrumb d-none d-md-flex align-items-center">
                <div class="breadcrumb-icon-badge me-2">
                    <i class="bi bi-person-badge"></i>
                </div>
                <span class="fw-semibold text-dark small">PegawaiKu</span>
            </div>

            <!-- Topbar Right: Search Input + Notification + Status Badge + User -->
            <div class="topbar-right">
                <!-- Search Input Form -->
                <form class="topbar-search-form d-none d-lg-block" method="get" action="<?= $base ?>/pegawai/index.php">
                    <div class="topbar-search-wrap">
                        <i class="bi bi-search topbar-search-icon"></i>
                        <input type="text" name="q" class="topbar-search-input" placeholder="Cari NIP / Nama Pegawai...">
                    </div>
                </form>

                <!-- Avatar User Header -->
                <div class="topbar-user-avatar" title="<?= e($userName) ?>">
                    <span><?= e($firstLetter) ?></span>
                </div>
            </div>
        </header>

        <!-- Overlay mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Konten Halaman Dimulai -->
        <main class="main-content fade-in">
