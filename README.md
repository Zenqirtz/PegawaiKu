# PegawaiKu

Sistem Kepegawaian (HR) berbasis PHP + MySQL untuk latihan/BNSP.

## Fitur

- Login/logout dengan session + CSRF token
- Dashboard ringkasan (total pegawai, total jabatan, distribusi jabatan)
- CRUD data pegawai (tambah, detail, edit, hapus)
- Pencarian NIP / nama pegawai
- Pengecekan NIP duplikat via AJAX (`api/cek_nip.php`)

## Struktur

```
api/           endpoint AJAX (detail, cek_nip)
assets/        css, js, images
config/        koneksi.php (PDO, session, helper)
partials/      header.php, footer.php
pegawai/       index, tambah, edit, detail
proses/        simpan, update, hapus
index.php      entry point
login.php      form login
dashboard.php  halaman utama
logout.php
pegawai_bnsp.sql  dump skema + data
```

## Instalasi

1. Clone repo ke folder web server (mis. `D:\laragon\www\pegawaiku_jwd`):
   ```bash
   git clone https://github.com/Zenqirtz/PegawaiKu.git pegawaiku_jwd
   ```
2. Import database:
   ```bash
   mysql -u root < pegawai_bnsp.sql
   ```
3. Sesuaikan kredensial database di `config/koneksi.php`:
   ```php
   $dbHost = '127.0.0.1';
   $dbName = 'latihan_bnsp_jwd';
   $dbUser = 'root';
   $dbPass = '';
   ```
4. Buka `http://localhost/pegawaiku_jwd/` di browser.

## Kebutuhan

- PHP 8.0+ (menggunakan `declare(strict_types=1)`, `str_contains`)
- MySQL / MariaDB
- Ekstensi `pdo_mysql`
