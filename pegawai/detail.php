<?php
// ============================================================
// pegawai/detail.php
// READ: Menampilkan rincian data lengkap satu pegawai
// Mengambil data berdasarkan parameter query string: ?id=...
// Menampilkan kartu profil dengan inisial avatar dan informasi kepegawaian
// ============================================================

declare(strict_types=1);

require __DIR__ . '/../config/koneksi.php';
// Cek status autentikasi: hanya admin yang sudah login yang bisa mengakses
checkLogin();

// --- 1. Ambil & Validasi Parameter ID dari URL ---
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', 'ID pegawai tidak valid.');
    header('Location: index.php');
    exit;
}

// --- 2. Query Data Pegawai Berdasarkan ID ---
// Menggunakan prepared statement (:id) untuk mencegah SQL Injection
$stmt = $pdo->prepare('SELECT id, nip, nama, email, jabatan, foto FROM employees WHERE id = :id');
$stmt->execute(['id' => $id]);
$employee = $stmt->fetch();

// Jika data tidak ditemukan (misal ID sudah dihapus)
if (!$employee) {
    setFlash('danger', 'Data pegawai tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$pageTitle  = 'Detail Pegawai | PegawaiKu';
$activePage = 'pegawai';

require __DIR__ . '/../partials/header.php';
?>

<!-- Header Halaman + Tombol Aksi -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1>Detail Pegawai</h1>
        <p>Informasi lengkap mengenai data pegawai.</p>
    </div>
    <div class="d-flex gap-2">
        <!-- Tombol Edit: langsung ke form edit dengan ID yang sama -->
        <a class="btn btn-primary" href="edit.php?id=<?= (int) $employee['id'] ?>">
            <i class="bi bi-pencil-square me-1"></i> Edit
        </a>
        <a class="btn btn-outline-secondary" href="index.php">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- ============================================================
     KARTU DETAIL PEGAWAI
     ============================================================ -->
<div class="card p-4" style="max-width: 700px;">

    <!-- Avatar inisial / pasfoto + nama + jabatan -->
    <div class="d-flex align-items-center mb-4 pb-4 border-bottom gap-3">
        <?php if (!empty($employee['foto']) && file_exists(__DIR__ . '/../' . $employee['foto'])): ?>
            <img src="<?= $base ?>/<?= e($employee['foto']) ?>" alt="Pasfoto Pegawai" class="rounded-circle object-fit-cover shadow-sm" style="width:64px; height:64px;">
        <?php else: ?>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary" style="font-size:1.75rem; width:64px; height:64px;">
                <?= e(mb_strtoupper(mb_substr($employee['nama'], 0, 1))) ?>
            </div>
        <?php endif; ?>
        <div>
            <h2 class="h4 fw-bold mb-0"><?= e($employee['nama']) ?></h2>
            <p class="text-secondary mb-0 small"><?= e($employee['jabatan']) ?></p>
        </div>
    </div>

    <!-- Detail data dalam 2 kolom -->
    <div class="row g-4">

        <!-- NIP -->
        <div class="col-sm-6">
            <div class="text-secondary small fw-semibold mb-1">NIP</div>
            <div><?= e($employee['nip']) ?></div>
        </div>

        <!-- Nama -->
        <div class="col-sm-6">
            <div class="text-secondary small fw-semibold mb-1">Nama Lengkap</div>
            <div><?= e($employee['nama']) ?></div>
        </div>

        <!-- Email -->
        <div class="col-sm-6">
            <div class="text-secondary small fw-semibold mb-1">Email</div>
            <div>
                <a href="mailto:<?= e($employee['email']) ?>" class="text-decoration-none">
                    <?= e($employee['email'] ?: '-') ?>
                </a>
            </div>
        </div>

        <!-- Jabatan -->
        <div class="col-sm-6">
            <div class="text-secondary small fw-semibold mb-1">Jabatan</div>
            <div><?= e($employee['jabatan'] ?: '-') ?></div>
        </div>

    </div>

    <!-- Tombol aksi bawah kartu -->
    <div class="mt-4 pt-4 border-top d-flex gap-2">
        <a href="edit.php?id=<?= (int) $employee['id'] ?>" class="btn btn-primary">
            <i class="bi bi-pencil-square me-1"></i> Edit Data
        </a>
        <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
    </div>

</div>

<?php
require __DIR__ . '/../partials/footer.php';
?>
