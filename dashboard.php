<?php
// ============================================================
// dashboard.php
// Halaman Dashboard Modern PegawaiKu
// Sesuai Layout Desain Terpadu (Image 1)
// ============================================================

declare(strict_types=1);

require __DIR__ . '/config/koneksi.php';
checkLogin();

// --- 1. Hitung Ringkasan Statistik dari Database ---
// COUNT(*) menghitung total seluruh baris di tabel employees
$totalPegawai = (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();

// COUNT(DISTINCT jabatan) menghitung berapa jenis jabatan unik yang digunakan
$totalJabatan = (int) $pdo->query('SELECT COUNT(DISTINCT jabatan) FROM employees WHERE jabatan IS NOT NULL')->fetchColumn();

// --- 2. Query Distribusi Pegawai per Jabatan ---
// Mengelompokkan pegawai berdasarkan nama jabatan (GROUP BY)
// lalu menghitung jumlah orang per jabatan (COUNT(*)) dan diurutkan terbanyak
$stmtDistribution = $pdo->query(
    'SELECT COALESCE(NULLIF(jabatan, ""), "Belum ditentukan") AS jabatan, COUNT(*) AS jumlah
     FROM employees
     GROUP BY COALESCE(NULLIF(jabatan, ""), "Belum ditentukan")
     ORDER BY jumlah DESC, jabatan ASC'
);
$distribusiJabatan = $stmtDistribution->fetchAll();

// --- 3. Ambil 5 Data Pegawai yang Baru Didaftarkan ---
// ORDER BY id DESC mengambil id terbesar terlebih dahulu (data paling baru)
// LIMIT 5 membatasi hasil maksimal hanya 5 baris
$stmtRecent = $pdo->query('SELECT id, nip, nama, email, jabatan FROM employees ORDER BY id DESC LIMIT 5');
$recentEmployees = $stmtRecent->fetchAll();

// --- 4. Format Tanggal Indonesia Manual (tanpa modul intl/setlocale) ---
$hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$hari = $hariIndo[(int) date('w')];
$tgl  = date('j');
$bln  = $bulanIndo[(int) date('n')];
$thn  = date('Y');
$tanggalLengkap = "{$hari}, {$tgl} {$bln} {$thn}";
$tanggalSingkat = "{$tgl} " . substr($bln, 0, 3) . " {$thn}";

// --- 5. Data User Login & Konfigurasi Halaman ---
$userName = $_SESSION['user_name'] ?? 'Budi Santoso';
$flash = getFlash(); // Pesan sukses/error dari proses redirect sebelumnya

$pageTitle  = 'Dashboard | PegawaiKu';
$activePage = 'dashboard';

// Muat template layout header (sidebar navigasi + topbar)
require __DIR__ . '/partials/header.php';
?>

<!-- Alert Flash jika ada -->
<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i><?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<!-- ============================================================
     BANNER SAMBUTAN ATAS (Hero Greeting Header)
     Menampilkan sapaan dan gambar multimedia (SKKNI MUK JWD 1.c)
     ============================================================ -->
<div class="dashboard-hero mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <span class="hero-tag-badge">
                <i class="bi bi-shield-check me-1"></i> SISTEM HR TERINTEGRASI &bull; Periode Fiskal <?= date('Y') ?>
            </span>
            <h1 class="hero-title mt-2 mb-1">
                Selamat Datang Kembali, <?= e($userName) ?> <span class="wave-emoji">&#128075;</span>
            </h1>
            <p class="hero-subtitle mb-3">
                Berikut adalah ikhtisar ringkasan data kepegawaian hari ini, <?= $tanggalLengkap ?>.
            </p>
            <div class="d-flex align-items-center gap-2">
                <!-- Tombol Tanggal -->
                <button class="btn btn-hero-date" type="button" title="Tanggal Hari Ini">
                    <i class="bi bi-calendar3 me-1 text-primary"></i> <?= $tanggalSingkat ?>
                </button>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-2 small fw-semibold">
                    <i class="bi bi-shield-lock-fill me-1"></i> Autentikasi Terverifikasi
                </span>
            </div>
        </div>
        <!-- Elemen Gambar Multimedia Dashboard (SKKNI MUK JWD 1.c) -->
        <div class="col-lg-5 text-center text-lg-end d-none d-md-block">
            <img src="<?= $base ?>/assets/images/dashboard-hero.svg" alt="Ilustrasi Multimedia Analitik Kepegawaian" class="dashboard-hero-img">
        </div>
    </div>
</div>

<!-- ============================================================
     LAYOUT GRID UTAMA (Kiri 8 Kolom, Kanan 4 Kolom)
     ============================================================ -->
<div class="row g-4">

    <!-- ========== KOLOM KIRI (70%): Stat Cards + Tabel Roster ========== -->
    <div class="col-xl-8">

        <!-- Baris 2 Stat Card -->
        <div class="row g-3 mb-4">

            <!-- Card 1: TOTAL PEGAWAI -->
            <div class="col-md-6">
                <div class="card stat-card-modern h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="stat-card-label">TOTAL PEGAWAI</span>
                            <div class="stat-icon-circle bg-blue-subtle text-primary">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="stat-main-number"><?= $totalPegawai ?></span>
                        </div>

                        <div class="text-secondary small mb-3">
                            Data pegawai terdaftar
                        </div>

                        <div class="stat-card-bottom-bar pt-3 border-top d-flex justify-content-between align-items-center small">
                            <span class="text-secondary">
                                <i class="bi bi-database-check text-success me-1"></i> Diperbarui langsung dari database
                            </span>
                            <a class="fw-semibold text-primary text-decoration-none" href="pegawai/index.php">Lihat data</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: TOTAL JABATAN -->
            <div class="col-md-6">
                <div class="card stat-card-modern h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="stat-card-label">TOTAL JABATAN</span>
                            <div class="stat-icon-circle bg-blue-subtle text-primary">
                                <i class="bi bi-layers-fill"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="stat-main-number"><?= $totalJabatan ?></span>
                            <span class="text-secondary small fw-medium">Jabatan Aktif</span>
                        </div>

                        <div class="text-secondary small mb-3">
                            Jumlah variasi peran yang digunakan
                        </div>

                        <div class="stat-card-bottom-bar pt-3 border-top d-flex justify-content-between align-items-center small text-secondary">
                            <span>Sesuai data pegawai yang tersimpan</span>
                            <i class="bi bi-briefcase text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tabel: Pegawai Terbaru Terdaftar (Roster) -->
        <div class="card roster-card">
            <div class="card-header bg-white p-4 pb-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="roster-title mb-1">Pegawai Terbaru Terdaftar</h2>
                    <p class="roster-subtitle mb-0">Roster anggota aparatur dan staf yang baru teregistrasi</p>
                </div>
                <a href="pegawai/index.php" class="btn-link-viewall">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table roster-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 26%;">NIP</th>
                            <th style="width: 36%;">NAMA LENGKAP</th>
                            <th style="width: 26%;">EMAIL</th>
                            <th style="width: 12%;" class="text-end">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentEmployees)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">
                                Belum ada data pegawai. Silakan tambah data baru.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentEmployees as $i => $emp): ?>
                            <?php
                            $colors = ['#2563eb', '#059669', '#d97706', '#7c3aed', '#dc2626'];
                            $color = $colors[$i % count($colors)];
                            $initial = mb_strtoupper(mb_substr($emp['nama'], 0, 2));
                            ?>
                            <tr>
                                <!-- Kolom NIP Badge -->
                                <td>
                                    <span class="nip-pill-badge">
                                        <?= e($emp['nip']) ?>
                                    </span>
                                </td>

                                <!-- Kolom Nama + Avatar + Jabatan -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="roster-avatar-box" style="background-color: <?= $color ?>20; color: <?= $color ?>;">
                                            <?= e($initial) ?>
                                        </div>
                                        <div>
                                            <a href="pegawai/detail.php?id=<?= (int) $emp['id'] ?>" class="roster-name-link">
                                                <?= e($emp['nama']) ?>
                                            </a>
                                            <div class="roster-position-text">
                                                <?= e($emp['jabatan'] ?: 'Staf Pelaksana') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom Email -->
                                <td>
                                    <a href="mailto:<?= e($emp['email']) ?>" class="roster-email-link">
                                        <i class="bi bi-envelope me-1 text-muted"></i>
                                        <?= e($emp['email']) ?>
                                    </a>
                                </td>

                                <!-- Kolom Aksi: Quick View AJAX -->
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-quick-view py-1 px-2" data-id="<?= (int) $emp['id'] ?>" title="Detail cepat via AJAX" aria-label="Detail Cepat">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Mini -->
            <div class="card-footer bg-white p-3 px-4 border-top d-flex justify-content-between align-items-center small text-secondary">
                <span>Menampilkan <strong><?= count($recentEmployees) ?></strong> dari <strong><?= $totalPegawai ?></strong> data pegawai</span>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-mini-page" disabled><i class="bi bi-chevron-left"></i></button>
                    <span class="btn btn-sm btn-mini-page active">1</span>
                    <button class="btn btn-sm btn-mini-page" disabled><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>

    </div>

    <!-- ========== KOLOM KANAN: Distribusi Jabatan ========== -->
    <div class="col-xl-4">

        <!-- Distribusi per Jabatan -->
        <div class="card distribution-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 class="distribution-title mb-1">Distribusi per Jabatan</h3>
                        <p class="text-secondary small mb-0">Alokasi kekuatan formasi SDM</p>
                    </div>
                    <div class="dist-icon-badge text-muted">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                </div>

                <!-- Ringkasan total -->
                <div class="dist-summary mb-3">
                    <div class="number-label mb-1">TOTAL PEGAWAI TERDAFTAR</div>
                    <div class="dist-summary-numbers mb-2">
                        <span class="number-big"><?= $totalPegawai ?></span>
                        <span class="number-small">pegawai</span>
                    </div>
                    <div>
                        <span class="dist-summary-badge">
                            <i class="bi bi-diagram-3-fill me-1"></i><?= $totalJabatan ?> jabatan berbeda
                        </span>
                    </div>
                </div>

                <!-- Daftar jabatan & proporsi -->
                <div class="division-stat-list">
                    <?php if (empty($distribusiJabatan)): ?>
                        <div class="text-secondary small py-3 text-center">Belum ada data jabatan.</div>
                    <?php else: ?>
                        <?php
                        $barColors = ['#2563eb', '#059669', '#0284c7', '#818cf8', '#d97706'];
                        ?>
                        <?php foreach ($distribusiJabatan as $index => $row): ?>
                            <?php
                            $jumlahPegawai = (int) $row['jumlah'];
                            $persentase = $totalPegawai > 0 ? round(($jumlahPegawai / $totalPegawai) * 100, 1) : 0;
                            $color = $barColors[$index % count($barColors)];
                            ?>
                            <div class="division-stat-item mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="division-name fw-medium" style="color:#1e293b;"><?= e($row['jabatan']) ?></span>
                                    <span class="division-count text-muted">
                                        <?= $jumlahPegawai ?> pegawai (<?= $persentase ?>%)
                                    </span>
                                </div>
                                <div class="progress progress-thin">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $persentase ?>%; background: <?= $color ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Footer Card Distribusi -->
                <div class="pt-3 border-top d-flex justify-content-between align-items-center small text-secondary mt-3">
                    <span><i class="bi bi-database-check text-success me-1"></i> Data real-time dari DB</span>
                    <a href="pegawai/index.php" class="text-decoration-none small fw-semibold text-primary">
                        Lihat Semua Jabatan
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php
require __DIR__ . '/partials/footer.php';
?>
