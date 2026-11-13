<?php
// ============================================================
// pegawai/tambah.php
// CREATE: Menampilkan formulir untuk menambah pegawai.
// Kolom yang dikirim ke backend: nip, nama, email, jabatan.
// Data diproses dan disimpan oleh ../proses/simpan.php.
// ============================================================

declare(strict_types=1);

require __DIR__ . '/../config/koneksi.php';
checkLogin();

$flash = getFlash();

$pageTitle  = 'Tambah Pegawai | PegawaiKu';
$activePage = 'pegawai';

require __DIR__ . '/../partials/header.php';
?>

<div class="form-page-container">

    <!-- Breadcrumb -->
    <a class="form-back-link" href="index.php">
        <i class="bi bi-arrow-left"></i> Kembali ke Data Pegawai
    </a>

    <!-- Header -->
    <div class="form-page-header">
        <div>
            <h1>Tambah Pegawai Baru</h1>
            <p>Lengkapi formulir di bawah ini untuk menambahkan pegawai baru ke dalam sistem kepegawaian.</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <!-- ============================================================
         FORMULIR TAMBAH PEGAWAI
         ============================================================ -->
    <form id="formPegawai" method="post" action="../proses/simpan.php" enctype="multipart/form-data" novalidate>

        <!-- Token CSRF wajib di setiap form POST -->
        <!-- Token CSRF diverifikasi di proses/simpan.php untuk menolak request dari situs lain. -->
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <!-- BAGIAN 01: Informasi Identitas & Profil -->
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-header-left">
                    <div class="form-section-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <div>
                        <h2 class="form-section-title">Informasi Identitas &amp; Profil</h2>
                        <p class="form-section-subtitle">Data legalitas kependudukan dan registrasi aparatur resmi.</p>
                    </div>
                </div>
                <span class="badge-section-number">BAGIAN 01</span>
            </div>

            <!-- Upload Pasfoto Resmi Pegawai -->
            <div class="photo-upload-box">
                <label for="foto" class="avatar-preview-wrapper" title="Klik untuk pilih pasfoto">
                    <i class="bi bi-person-circle avatar-placeholder-icon"></i>
                    <img alt="Preview Pasfoto" class="avatar-preview-img" id="avatarPreview">
                    <span class="avatar-camera-btn" title="Pilih Foto">
                        <i class="bi bi-camera-fill"></i>
                    </span>
                </label>
                <div class="photo-upload-info">
                    <h5>Pasfoto Resmi Pegawai</h5>
                    <p>Format file didukung: JPEG atau PNG. Resolusi minimal 400&times;600 px (Maks. 2MB). Latar belakang merah/biru dianjurkan.</p>
                    <label for="foto" class="btn-upload-photo">
                        <i class="bi bi-upload"></i> Unggah Foto Resmi
                    </label>
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg" class="d-none">
                </div>
            </div>

            <div class="row">
                <!-- Input NIP -->
                <div class="col-md-6 mb-3">
                    <label class="input-label-modern" for="nip">
                        <span>Nomor Induk Pegawai (NIP) <span class="text-danger">*</span></span>
                        <span class="input-label-badge">18 Digit</span>
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-person-vcard input-group-icon"></i>
                        <input type="text" class="form-control-modern" id="nip" name="nip" placeholder="Contoh: 19980110 202301 2 004" autocomplete="off" required>
                    </div>
                    <!-- Feedback AJAX Real-time (jQuery) ketersediaan / duplikasi NIP -->
                    <div id="nipFeedback" class="mt-1"></div>
                    <div class="input-hint"><i class="bi bi-info-circle"></i> Format standar 18 digit NIP resmi kepegawaian (TahunLahir BulanLahir TMT JenisKelamin Urut)</div>
                    <div class="invalid-feedback">NIP wajib diisi.</div>
                </div>

                <!-- Input Nama -->
                <div class="col-md-6 mb-3">
                    <label class="input-label-modern" for="nama">
                        <span>Nama Lengkap Beserta Gelar <span class="text-danger">*</span></span>
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-person input-group-icon"></i>
                        <input type="text" class="form-control-modern" id="nama" name="nama" placeholder="Contoh: Ahmad Fauzi, S.T., M.Kom." required>
                    </div>
                    <div class="input-hint"><i class="bi bi-info-circle"></i> Gunakan huruf kapital sesuai kartu identitas KTP dan ijazah terakhir</div>
                    <div class="invalid-feedback">Nama wajib diisi (minimal 2 karakter).</div>
                </div>
            </div>
        </div>

        <!-- BAGIAN 02: Kontak & Penempatan Kerja -->
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-header-left">
                    <div class="form-section-icon"><i class="bi bi-briefcase-fill"></i></div>
                    <div>
                        <h2 class="form-section-title">Kontak &amp; Penempatan Kerja</h2>
                        <p class="form-section-subtitle">Jalur komunikasi kedinasan dan unit penempatan staf.</p>
                    </div>
                </div>
                <span class="badge-section-number">Bagian 02</span>
            </div>

            <div class="row">
                <!-- Input Email -->
                <div class="col-md-6 mb-3">
                    <label class="input-label-modern" for="email">
                        <span>Alamat Email Instansi <span class="text-danger">*</span></span>
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-envelope input-group-icon"></i>
                        <input type="email" class="form-control-modern" id="email" name="email" placeholder="nama.pegawai@gmail.com" required>
                    </div>
                    <div class="input-hint"><i class="bi bi-info-circle"></i> Email akan digunakan untuk notifikasi absensi &amp; slip gaji.</div>
                    <div class="invalid-feedback">Format email tidak valid.</div>
                </div>

                <!-- Input Jabatan -->
                <div class="col-md-6 mb-3">
                    <label class="input-label-modern" for="jabatan">
                        <span>Jabatan / Posisi Kerja <span class="text-danger">*</span></span>
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-briefcase input-group-icon"></i>
                        <input type="text" class="form-control-modern" id="jabatan" name="jabatan" placeholder="Contoh: Web Developer" required>
                    </div>
                    <div class="input-hint"><i class="bi bi-info-circle"></i> Tentukan formasi jabatan struktural atau fungsional sesuai kebutuhan instansi.</div>
                    <div class="invalid-feedback">Jabatan wajib diisi.</div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-action-bar">
            <a href="index.php" class="btn-form-cancel">Batal</a>
            <button class="btn-form-submit" type="submit">
                <i class="bi bi-check2-circle"></i> Simpan Data Pegawai
            </button>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
