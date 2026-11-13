<?php
// ============================================================
// config/upload.php
// Helper pengelolaan berkas pasfoto pegawai
// ============================================================

declare(strict_types=1);

if (!function_exists('pegawaiPhotoDir')) {
    /**
     * pegawaiPhotoDir() - Path relatif folder foto dari root project
     */
    function pegawaiPhotoDir(): string
    {
        return 'uploads/pegawai';
    }
}

if (!function_exists('pegawaiPhotoRootDir')) {
    /**
     * pegawaiPhotoRootDir() - Path absolut direktori foto di disk server
     */
    function pegawaiPhotoRootDir(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pegawai';
    }
}

if (!function_exists('ensurePegawaiPhotoDir')) {
    /**
     * ensurePegawaiPhotoDir() - Pastikan direktori uploads/pegawai ada
     */
    function ensurePegawaiPhotoDir(): bool
    {
        $dir = pegawaiPhotoRootDir();
        if (!is_dir($dir)) {
            return @mkdir($dir, 0755, true) && is_dir($dir);
        }
        return true;
    }
}

if (!function_exists('pegawaiPhotoDelete')) {
    /**
     * pegawaiPhotoDelete() - Hapus file foto dari disk server jika ada
     * @param string|null $relPath Path relatif foto yang tersimpan di DB
     */
    function pegawaiPhotoDelete(?string $relPath): void
    {
        if ($relPath === null || trim($relPath) === '') {
            return;
        }

        // Cegah path traversal
        $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim($relPath));
        if (str_contains($normalized, '..' . DIRECTORY_SEPARATOR)) {
            return;
        }

        $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $normalized;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

if (!function_exists('pegawaiPhotoUpload')) {
    /**
     * pegawaiPhotoUpload() - Validasi dan simpan file pasfoto yang diunggah
     *
     * Aturan:
     * - Maksimal ukuran berkas: 2MB (2 * 1024 * 1024 bytes)
     * - Tipe MIME & ekstensi: JPEG, JPG, PNG
     * - Validasi integritas gambar dengan getimagesize()
     *
     * @param array|null $file $_FILES['foto']
     * @return string|null Path relatif file yang tersimpan (misal 'uploads/pegawai/foto_xxx.jpg')
     *                     atau null jika tidak ada file yang dipilih
     * @throws Exception jika berkas diunggah namun tidak lolos validasi
     */
    function pegawaiPhotoUpload(?array $file): ?string
    {
        // Jika tidak ada berkas diunggah
        if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Cek error upload sistem PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Gagal mengunggah berkas. Kode error: ' . $file['error']);
        }

        // Cek ukuran berkas (Maksimal 2MB)
        $maxBytes = 2 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new Exception('Ukuran pasfoto terlalu besar. Maksimal 2MB.');
        }

        // Validasi ekstensi
        $originalExt = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($originalExt, $allowedExtensions, true)) {
            throw new Exception('Format pasfoto tidak didukung. Harap unggah berkas bertipe JPEG atau PNG.');
        }

        // Validasi MIME type via finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png'];
        if (!in_array($mime, $allowedMimes, true)) {
            throw new Exception('Tipe berkas tidak valid. Harap unggah gambar JPG atau PNG.');
        }

        // Validasi bahwa berkas benar-benar gambar
        $imageSize = @getimagesize($file['tmp_name']);
        if ($imageSize === false) {
            throw new Exception('Berkas yang diunggah bukan gambar valid atau telah rusak.');
        }

        // Validasi resolusi minimal sesuai ketentuan pasfoto (400 x 600 px)
        if ($imageSize[0] < 400 || $imageSize[1] < 600) {
            throw new Exception('Resolusi pasfoto terlalu kecil. Minimal 400 x 600 px.');
        }

        // Pastikan direktori tujuan tersedia
        if (!ensurePegawaiPhotoDir()) {
            throw new Exception('Gagal menyiapkan direktori penyimpanan pasfoto di server.');
        }

        // Buat nama berkas acak yang aman dan unik
        $destExt = $originalExt === 'jpeg' ? 'jpg' : $originalExt;
        $uniqueName = 'pasfoto_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $destExt;

        $targetPath = pegawaiPhotoRootDir() . DIRECTORY_SEPARATOR . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Gagal memindahkan berkas foto ke folder penyimpanan.');
        }

        @chmod($targetPath, 0644);

        return pegawaiPhotoDir() . '/' . $uniqueName;
    }
}
