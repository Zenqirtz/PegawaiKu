<?php
// ============================================================
// pegawai/export_pdf.php
// Fitur Ekspor Data Seluruh Pegawai ke Format PDF / Print View
// Menggunakan standar print stylesheet & auto print window
// ============================================================

declare(strict_types=1);

require_once __DIR__ . '/../config/koneksi.php';
checkLogin();

// Ambil seluruh data pegawai dari database
try {
    $stmt = $pdo->query('SELECT nip, nama, email, jabatan, status FROM employees ORDER BY nip ASC');
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Gagal memuat data pegawai: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$tanggalCetak = date('d F Y, H:i') . ' WIB';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Seluruh Pegawai - PegawaiKu</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 11pt;
            background: #fff;
        }
        .header-kop {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-kop h2 {
            margin: 0 0 4px 0;
            font-size: 16pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .header-kop p {
            margin: 0;
            font-size: 9.5pt;
            color: #475569;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 9pt;
            color: #334155;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            font-size: 9.5pt;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
        }
        td.text-center {
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8.5pt;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-aktif {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-izin {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .badge-cuti {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .tanda-tangan {
            margin-top: 40px;
            float: right;
            text-align: center;
            width: 200px;
        }
        .tanda-tangan p {
            margin: 0;
            font-size: 9.5pt;
        }
        .tanda-tangan .space {
            height: 60px;
        }
        .no-print-bar {
            background: #0f172a;
            color: #fff;
            padding: 12px 20px;
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-action {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back {
            background: #475569;
        }
        .btn-action:hover {
            opacity: 0.9;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Laporan Data Pegawai (Siap Unduh/Cetak PDF)</strong>
            <div style="font-size: 11px; color: #94a3b8;">Gunakan dialog print browser lalu pilih destination "Save as PDF" / Simpan sebagai PDF.</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="index.php" class="btn-action btn-back">Kembali</a>
            <button onclick="window.print()" class="btn-action">Download / Cetak PDF</button>
        </div>
    </div>

    <div class="header-kop">
        <h2>PEGAWAIKU - SISTEM INFORMASI KEPEGAWAIAN</h2>
        <p>Laporan Resmi Rekapitulasi Data Seluruh Pegawai</p>
    </div>

    <div class="meta-info">
        <div><strong>Waktu Cetak:</strong> <?= $tanggalCetak ?></div>
        <div><strong>Total Pegawai:</strong> <?= count($employees) ?> Orang</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 170px;">NIP</th>
                <th>Nama Lengkap</th>
                <th>Email Instansi</th>
                <th>Jabatan / Posisi</th>
                <th style="width: 90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data pegawai.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($employees as $idx => $emp): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td><?= e($emp['nip']) ?></td>
                        <td><strong><?= e($emp['nama']) ?></strong></td>
                        <td><?= e($emp['email'] ?: '-') ?></td>
                        <td><?= e($emp['jabatan'] ?: '-') ?></td>
                        <td class="text-center">
                            <?php 
                                $st = strtolower($emp['status'] ?? 'aktif');
                                $badgeClass = 'badge-aktif';
                                if ($st === 'izin') $badgeClass = 'badge-izin';
                                if ($st === 'cuti') $badgeClass = 'badge-cuti';
                            ?>
                            <span class="badge-status <?= $badgeClass ?>"><?= e(ucfirst($st)) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="tanda-tangan">
        <p>Disahkan oleh,</p>
        <p><strong>Super Admin</strong></p>
        <div class="space"></div>
        <p><u>( <?= e($_SESSION['user_name'] ?? 'Administrator') ?> )</u></p>
    </div>

    <script>
        // Otomatis membuka dialog print/save as PDF saat halaman dibuka
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
