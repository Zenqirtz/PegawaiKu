<?php
// ============================================================
// pegawai/index.php
// READ: Halaman daftar pegawai.
// Fitur: pencarian, filter jabatan, sortir, pagination, ekspor CSV.
// Data diambil dari tabel employees (id, nip, nama, email, jabatan).
// ============================================================
declare(strict_types=1);

require __DIR__ . '/../config/koneksi.php';
checkLogin();

// --- 1. Ambil parameter dari URL (query string) ---
// Semua parameter diketik ke string agar aman sebelum dipakai di query.
$search = trim((string) ($_GET['q'] ?? ''));            // kata kunci pencarian
$jabatanFilter = trim((string) ($_GET['jabatan'] ?? '')); // jabatan yang dipilih
$sort = (string) ($_GET['sort'] ?? 'newest');           // pilihan urutan

// Daftar urutan yang diizinkan (whitelist).
// Tujuannya: nilai dari URL TIDAK boleh langsung masuk ke klausa ORDER BY
// (rawan SQL Injection). Hanya kunci di array ini yang boleh dipakai.
$sortOptions = [
    'newest' => 'id DESC',
    'oldest' => 'id ASC',
    'name' => 'nama ASC',
    'nip' => 'nip ASC',
];
$orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];
$page = max(1, (int) ($_GET['page'] ?? 1)); // nomor halaman, minimal 1
$perPage = 7;                                // jumlah data per halaman

// --- 2. Bangun klausa WHERE secara dinamis ---
// Klausa hanya ditambahkan jika filter benar-benar diisi user.
$where = [];
$params = [];

if ($search !== '') {
    // Pencarian bebas pada beberapa kolom sekaligus.
    // Tanda :nip, :nama, dst. adalah named placeholder (prepared statement).
    $where[] = '(nip LIKE :nip OR nama LIKE :nama OR email LIKE :email OR jabatan LIKE :jabatan)';
    $like = '%' . $search . '%';
    $params += ['nip' => $like, 'nama' => $like, 'email' => $like, 'jabatan' => $like];
}
if ($jabatanFilter !== '') {
    // Filter jabatan memakai pencocokan persis (=), bukan LIKE.
    $where[] = 'jabatan = :jabatan_filter';
    $params['jabatan_filter'] = $jabatanFilter;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// --- 3. Data untuk dropdown filter dan kartu statistik ---
// DISTINCT = ambil nama jabatan unik saja (tanpa duplikat).
$jabatanList = $pdo->query("SELECT DISTINCT jabatan FROM employees WHERE jabatan IS NOT NULL AND jabatan <> '' ORDER BY jabatan")
    ->fetchAll(PDO::FETCH_COLUMN);
$totalPegawai = (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
$totalJabatan = (int) $pdo->query("SELECT COUNT(DISTINCT jabatan) FROM employees WHERE jabatan IS NOT NULL AND jabatan <> ''")->fetchColumn();
$totalCuti = 6;
$totalCutiTahunan = 4;

// --- 4. Hitung jumlah data SETELAH filter ---
// Dipakai untuk teks "Menampilkan x-y dari N data" dan jumlah halaman.
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM employees' . $whereSql);
$countStmt->execute($params);
$totalFiltered = (int) $countStmt->fetchColumn();

// --- 5. Pagination: hitung total halaman, offset, lalu ambil data ---
$totalPages = max(1, (int) ceil($totalFiltered / $perPage));
$page = min($page, $totalPages);          // cegah halaman melebihi batas
$offset = ($page - 1) * $perPage;         // baris pertama pada halaman ini

$stmt = $pdo->prepare('SELECT id, nip, nama, email, jabatan, foto FROM employees' . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset');

// Parameter filter diikat sebagai string (LIKE / =).
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}
// LIMIT dan OFFSET wajib bertipe integer, bukan string.
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$employees = $stmt->fetchAll();

// --- 7. Siapkan data untuk tampilan lalu muat header (sidebar + topbar) ---
$flash = getFlash();
$pageTitle = 'Data Pegawai | PegawaiKu';
$activePage = 'pegawai';
require __DIR__ . '/../partials/header.php';

// --- 8. Helper URL pagination ---
// Menjaga filter pencarian/jabatan/sortir tetap terpakai saat pindah halaman.
$queryBase = ['q' => $search, 'jabatan' => $jabatanFilter, 'sort' => $sort];
$pageUrl = static function (int $targetPage) use ($queryBase): string {
    return 'index.php?' . http_build_query($queryBase + ['page' => $targetPage]);
};
?>

<div class="employee-page">
    <div class="employee-breadcrumb">
        <a href="<?= $base ?>/dashboard.php">Dashboard</a><i class="bi bi-chevron-right"></i>
        <span>Data Pegawai</span>
    </div>

    <div class="employee-page-heading">
        <div>
            <h1>Data Pegawai</h1>
            <p>Kelola dan pantau data pegawai dalam satu tempat.</p>
        </div>
        <div class="employee-heading-actions">
            <a class="btn btn-primary" href="tambah.php"><i class="bi bi-person-plus-fill me-1"></i> Tambah Pegawai</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <div class="employee-stat-grid">
        <div class="employee-stat-card"><div><span>Total Pegawai</span><strong><?= number_format($totalPegawai) ?></strong><small>Seluruh data pegawai</small></div><i class="bi bi-people-fill stat-blue"></i></div>
        <div class="employee-stat-card"><div><span>Jumlah Jabatan</span><strong><?= number_format($totalJabatan) ?></strong><small>Jabatan berbeda</small></div><i class="bi bi-briefcase-fill stat-violet"></i></div>
        <div class="employee-stat-card"><div><span>Sedang Cuti / Izin</span><strong><?= number_format($totalCuti) ?></strong><small><?= number_format($totalCutiTahunan) ?> cuti tahunan</small></div><i class="bi bi-calendar-x stat-cyan"></i></div>
    </div>

    <form class="employee-filter-bar" method="get" action="index.php">
        <label class="employee-search"><i class="bi bi-search"></i><input type="search" name="q" value="<?= e($search) ?>" placeholder="Cari berdasarkan NIP, nama, atau email..."></label>
        <select name="jabatan" aria-label="Filter jabatan">
            <option value="">Semua Jabatan</option>
            <?php foreach ($jabatanList as $jabatan): ?>
                <option value="<?= e($jabatan) ?>" <?= $jabatanFilter === $jabatan ? 'selected' : '' ?>><?= e($jabatan) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort" aria-label="Urutkan pegawai">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>NIP Terbaru</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>NIP Terlama</option>
            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nama A–Z</option>
            <option value="nip" <?= $sort === 'nip' ? 'selected' : '' ?>>NIP A–Z</option>
        </select>
        <button class="btn btn-outline-primary employee-filter-submit" type="submit" aria-label="Terapkan filter"><i class="bi bi-sliders"></i><span>Filter</span></button>
        <?php if ($search !== '' || $jabatanFilter !== '' || $sort !== 'newest'): ?><a class="employee-reset" href="index.php" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a><?php endif; ?>
    </form>

    <section class="employee-table-card">
        <div class="table-responsive">
            <table class="table employee-table align-middle mb-0">
                <thead><tr><th class="employee-number-col">NO</th><th>NIP (NOMOR INDUK)</th><th>NAMA LENGKAP</th><th>EMAIL INSTANSI</th><th>JABATAN / POSISI</th><th>STATUS</th><th class="text-end">AKSI</th></tr></thead>
                <tbody>
                <?php if (!$employees): ?>
                    <tr><td colspan="7" class="text-center text-secondary py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Data pegawai tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($employees as $index => $emp):
                        $nameParts = preg_split('/\s+/', trim((string) $emp['nama'])) ?: [];
                        $initials = strtoupper(mb_substr($nameParts[0] ?? 'P', 0, 1) . (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : ''));
                    ?>
                    <tr>
                        <td class="employee-row-number"><?= $offset + $index + 1 ?></td>
                        <td><span class="employee-nip"><?= e($emp['nip']) ?></span></td>
                        <td><div class="employee-name-cell"><?php if (!empty($emp['foto']) && file_exists(__DIR__ . '/../' . $emp['foto'])): ?><img class="employee-initials employee-photo" src="<?= $base ?>/<?= e($emp['foto']) ?>" alt="Pasfoto <?= e($emp['nama']) ?>"><?php else: ?><span class="employee-initials"><?= e($initials) ?></span><?php endif; ?><div><a href="detail.php?id=<?= (int) $emp['id'] ?>"><?= e($emp['nama']) ?></a><small><?= e($emp['jabatan']) ?></small></div></div></td>
                        <td><a class="employee-email" href="mailto:<?= e($emp['email']) ?>"><?= e($emp['email']) ?></a></td>
                        <td><span class="employee-position"><?= e($emp['jabatan']) ?></span></td>
                        <td><span class="employee-status"><i></i>Terdaftar</span></td>
                        <td class="text-end text-nowrap">
                            <div class="employee-actions">
                                <!-- Quick View AJAX: muat detail ke modal tanpa reload halaman -->
                                <button type="button" class="employee-action-btn btn-quick-view" data-id="<?= (int) $emp['id'] ?>" title="Detail cepat via AJAX" aria-label="Detail cepat"><i class="bi bi-eye"></i></button>
                                <a class="employee-action-btn" href="edit.php?id=<?= (int) $emp['id'] ?>" title="Edit" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                                <form class="form-hapus" method="post" action="../proses/hapus.php" data-nama="<?= e($emp['nama']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="id" value="<?= (int) $emp['id'] ?>">
                                    <button class="employee-action-btn employee-delete-btn" type="submit" title="Hapus" aria-label="Hapus"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="employee-table-footer">
            <span>Menampilkan <strong><?= $totalFiltered === 0 ? 0 : $offset + 1 ?>–<?= min($offset + count($employees), $totalFiltered) ?></strong> dari <strong><?= number_format($totalFiltered) ?></strong> data pegawai</span>
            <?php if ($totalPages > 1): ?>
            <nav class="employee-pagination" aria-label="Navigasi halaman">
                <a class="pagination-label <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($pageUrl(max(1, $page - 1))) ?>"><i class="bi bi-chevron-left"></i> Sebelumnya</a>
                <?php for ($pageNumber = max(1, $page - 2); $pageNumber <= min($totalPages, $page + 2); $pageNumber++): ?>
                    <a class="pagination-number <?= $pageNumber === $page ? 'active' : '' ?>" href="<?= e($pageUrl($pageNumber)) ?>"><?= $pageNumber ?></a>
                <?php endfor; ?>
                <a class="pagination-label <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= e($pageUrl(min($totalPages, $page + 1))) ?>">Selanjutnya <i class="bi bi-chevron-right"></i></a>
            </nav>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
