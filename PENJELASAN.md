# PENJELASAN DETAIL SETIAP FILE — PegawaiKu

> Dokumen ini menjelaskan **fungsi, alur logika, dan teknik** yang digunakan di setiap file dalam project PegawaiKu.
> Cocok untuk keperluan dokumentasi ujikom / SKKNI JWD / BNSP.

---

## Daftar Isi

1. [config/koneksi.php](#1-configkoneksiphp)
2. [index.php](#2-indexphp)
3. [login.php](#3-loginphp)
4. [logout.php](#4-logoutphp)
5. [dashboard.php](#5-dashboardphp)
6. [partials/header.php](#6-partialsheaderphp)
7. [partials/footer.php](#7-partialsfooterphp)
8. [pegawai/index.php](#8-pegawaiindexphp)
9. [pegawai/tambah.php](#9-pegawaitambahphp)
10. [pegawai/edit.php](#10-pegawaieditphp)
11. [pegawai/detail.php](#11-pegawaidetailphp)
12. [proses/simpan.php](#12-prosessimpanphp)
13. [proses/update.php](#13-prosesupdatephp)
14. [proses/hapus.php](#14-proseshapusphp)
15. [api/cek_nip.php](#15-apicek_nipphp)
16. [api/detail.php](#16-apidetailphp)
17. [assets/js/script.js](#17-assetsjsscriptjs)
18. [assets/css/style.css](#18-assetscssstylecss)
19. [assets/css/ajax.css](#19-assetscssajaxcss)

---

## 1. config/koneksi.php

**Fungsi:** File konfigurasi sentral yang dimuat (`require`) oleh semua halaman.

**Apa yang dilakukan:**
- **Session Management:** Memanggil `session_start()` jika belum aktif. Session digunakan untuk menyimpan status login (`user_id`, `user_name`), token CSRF, dan flash message.
- **Koneksi Database PDO:** Membuat koneksi ke MySQL menggunakan PDO (PHP Data Objects) dengan konfigurasi:
  - `ERRMODE_EXCEPTION` — error SQL langsung menjadi exception PHP (mudah di-debug).
  - `FETCH_ASSOC` — hasil query dikembalikan sebagai array asosiatif (pakai nama kolom).
  - `EMULATE_PREPARES = false` — menggunakan prepared statement asli dari MySQL (lebih aman).
- **CSRF Token:** Membuat token acak 64 karakter (32 bytes hex) menggunakan `bin2hex(random_bytes(32))`. Token ini disimpan di session dan dicocokkan saat form di-submit untuk mencegah serangan Cross-Site Request Forgery.

**Fungsi Helper yang Disediakan:**

| Fungsi | Kegunaan |
|--------|----------|
| `e($str)` | Escape output HTML dengan `htmlspecialchars()`. Mencegah serangan XSS. Semua data yang ditampilkan di browser WAJIB lewat fungsi ini. |
| `setFlash($type, $message)` | Menyimpan pesan notifikasi ke `$_SESSION['flash']`. Dipakai setelah proses CRUD berhasil/gagal, sebelum redirect. |
| `getFlash()` | Mengambil dan menghapus flash message dari session. Pesan hanya tampil sekali (pola Post-Redirect-Get / PRG). |
| `checkLogin()` | Mengecek apakah `$_SESSION['user_id']` terisi. Jika kosong, redirect ke `login.php`. Dipanggil di awal setiap halaman yang butuh autentikasi. |
| `basePath()` | Menentukan path relatif ke root project. Halaman di subfolder `pegawai/` mengembalikan `..`, halaman di root mengembalikan `.`. Digunakan untuk menyusun URL aset dan link navigasi. |

---

## 2. index.php

**Fungsi:** File gerbang awal (entry point / router sederhana).

**Alur:**
1. Memuat `config/koneksi.php` (session aktif).
2. Jika `$_SESSION['user_id']` terisi → redirect ke `dashboard.php`.
3. Jika kosong → redirect ke `login.php`.
4. `exit` menghentikan eksekusi agar header redirect berjalan.

**Catatan:** File ini tidak menampilkan HTML apapun. Hanya berfungsi sebagai pengarah otomatis.

---

## 3. login.php

**Fungsi:** Halaman autentikasi pengguna (form login).

**Alur Backend (PHP):**
1. Cek jika user sudah login (`$_SESSION['user_id']` terisi) → redirect ke `dashboard.php`.
2. Jika HTTP method `POST`:
   - Ambil `username` dan `password` dari `$_POST`, bersihkan dengan `trim()`.
   - Validasi tidak boleh kosong.
   - Query database: `SELECT id, name, username, password FROM users WHERE username = :username LIMIT 1` — menggunakan prepared statement (anti SQL Injection).
   - Verifikasi password hash bcrypt dengan `password_verify()` — fungsi ini aman dari timing attacks.
   - Jika berhasil: `session_regenerate_id(true)` (mencegah Session Fixation), simpan user_id dan user_name ke session, redirect ke dashboard.
   - Jika gagal: pesan error generik "Username atau password tidak sesuai" — tidak menyebutkan secara spesifik mana yang salah (untuk keamanan).

**Alur Frontend (HTML + jQuery):**
- Form HTML5 dengan atribut `novalidate` (validasi di-handle JavaScript, bukan browser default).
- jQuery client-side validation: cek field kosong, tambah class `is-invalid` untuk Bootstrap feedback.
- Animasi CSS: class `.login-card` mengaktifkan animasi fade-in, stagger logo/judul/form/tombol.
- Cache-busting CSS: `?v=<?= filemtime(...) ?>` memaksa browser mengambil CSS terbaru.

---

## 4. logout.php

**Fungsi:** Menghapus seluruh session dan mengarahkan ke login.

**Langkah:**
1. `$_SESSION = []` — kosongkan array session.
2. Hapus cookie session dari browser (`setcookie()` dengan waktu expired di masa lalu).
3. `session_destroy()` — hancurkan file session di server.
4. Redirect ke `login.php`.

**Catatan:** Tiga langkah (kosongkan, hapus cookie, destroy) memastikan session benar-benar bersih dari sisi server maupun client.

---

## 5. dashboard.php

**Fungsi:** Halaman utama setelah login. Menampilkan ringkasan statistik kepegawaian.

**Query Database:**
| Query | Kegunaan |
|-------|----------|
| `SELECT COUNT(*) FROM employees` | Total seluruh pegawai |
| `SELECT COUNT(DISTINCT jabatan) FROM employees` | Jumlah jenis jabatan unik |
| `SELECT jabatan, COUNT(*) ... GROUP BY jabatan ORDER BY jumlah DESC` | Distribusi pegawai per jabatan (untuk progress bar) |
| `SELECT ... FROM employees ORDER BY id DESC LIMIT 5` | 5 pegawai terbaru terdaftar (roster) |

**Komponen UI:**
- **Hero Banner** — Sapaan user + tanggal Indonesia (format manual tanpa modul intl).
- **Stat Cards** — 2 kartu: Total Pegawai dan Total Jabatan, data real-time dari DB.
- **Roster Table** — 5 pegawai terbaru dengan avatar inisial, NIP badge, email, dan tombol Quick View AJAX.
- **Distribution Card** — Daftar jabatan dengan progress bar persentase.

**Teknik:**
- Tanggal Indonesia diformat manual menggunakan array `$hariIndo` dan `$bulanIndo` tanpa dependensi `setlocale()`.
- Warna avatar berputar menggunakan modulo: `$colors[$i % count($colors)]`.
- Semua output di-escape dengan `e()`.

---

## 6. partials/header.php

**Fungsi:** Template layout bagian atas, di-include oleh semua halaman terautentikasi.

**Apa yang disediakan:**
- `<head>`: meta charset, viewport (responsive), favicon, Bootstrap 5 CSS, Bootstrap Icons, Google Fonts (Plus Jakarta Sans), style.css & ajax.css dengan cache-busting.
- **Sidebar Navigasi:** Logo + brand text, menu Dashboard dan Data Pegawai (active state berdasarkan `$activePage`), user card dengan avatar inisial dan tombol logout.
- **Topbar:** Toggle hamburger mobile, breadcrumb, search form (action ke pegawai/index.php), status pill "Aktif", avatar user.
- **Overlay Mobile:** Elemen `#sidebarOverlay` untuk menutup sidebar saat di-klik di mobile.
- **Main Content:** Tag `<main class="main-content fade-in">` yang dibuka di sini dan ditutup di `footer.php`.

**Variabel yang diterima:**
| Variabel | Default | Kegunaan |
|----------|---------|----------|
| `$pageTitle` | 'PegawaiKu' | Judul tab browser |
| `$activePage` | — | Menandai menu sidebar yang aktif (`'dashboard'` atau `'pegawai'`) |

---

## 7. partials/footer.php

**Fungsi:** Template layout bagian bawah, menutup tag HTML yang dibuka di `header.php`.

**Isi:**
- Menutup `</main>`, `</div>` content-wrapper, `</div>` wrapper.
- **Quick View Modal** — Modal Bootstrap 5 untuk menampilkan detail pegawai via AJAX tanpa reload halaman. Isi modal (`#quickViewContent`) diisi oleh JavaScript (jQuery `$.getJSON`).
- Script CDN: jQuery 3.7.1 dan Bootstrap 5.3.3 JS Bundle.
- Custom JavaScript: `script.js` dengan cache-busting timestamp.

---

## 8. pegawai/index.php

**Fungsi:** READ — Menampilkan daftar seluruh pegawai dengan fitur pencarian, filter, sortir, dan pagination.

**Fitur Lengkap:**
1. **Pencarian (Search):** LIKE query pada kolom nip, nama, email, jabatan secara bersamaan.
2. **Filter Jabatan:** Dropdown select yang terisi dari `SELECT DISTINCT jabatan`.
3. **Sortir:** Whitelist array `$sortOptions` — nilai dari URL tidak pernah langsung masuk ORDER BY (anti SQL Injection).
4. **Pagination:** 7 data per halaman, LIMIT/OFFSET dengan `PDO::PARAM_INT`.

**Alur Backend:**
1. Ambil parameter `q`, `jabatan`, `sort`, `page` dari `$_GET`.
2. Bangun klausa `WHERE` secara dinamis (hanya ditambah jika filter terisi).
3. Hitung `$totalFiltered` (COUNT dengan WHERE) untuk pagination.
4. Query data dengan `LIMIT :limit OFFSET :offset`.
5. Bind parameter: filter sebagai `PDO::PARAM_STR`, limit/offset sebagai `PDO::PARAM_INT`.

**Alur Frontend:**
- Breadcrumb: Dashboard > Data Pegawai.
- Stat cards: Total Pegawai dan Jumlah Jabatan.
- Filter bar: search input + dropdown jabatan + dropdown sortir + tombol filter + tombol reset.
- Tabel data: No, NIP, Nama (dengan avatar inisial), Email, Jabatan, Status, Aksi (Quick View / Edit / Hapus).
- Footer pagination: "Menampilkan x-y dari N" + navigasi halaman.
- Tombol hapus menggunakan form POST (bukan link GET) dengan konfirmasi JavaScript.

---

## 9. pegawai/tambah.php

**Fungsi:** CREATE — Menampilkan formulir untuk menambah pegawai baru.

**Alur:**
1. Cek login, ambil flash message, muat header.
2. Tampilkan form dengan field: NIP, Nama, Email, Jabatan.
3. Setiap field memiliki ikon, label, dan petunjuk (hint).
4. CSRF token disisipkan sebagai hidden input.
5. Form dikirim via POST ke `../proses/simpan.php`.

**Fitur:**
- **AJAX NIP Check:** Input NIP memiliki id `nip` yang dipantau oleh `script.js`. Saat user mengetik, AJAX request dikirim ke `api/cek_nip.php` untuk mengecek duplikasi secara real-time.
- **Photo Upload Box:** UI upload pasfoto (hanya tampilan, belum ada backend handler).
- **Validasi Client-side:** Script.js menangani validasi wajib isi sebelum submit.

---

## 10. pegawai/edit.php

**Fungsi:** UPDATE — Menampilkan formulir untuk mengubah data pegawai yang sudah ada.

**Alur:**
1. Ambil ID dari `$_GET['id']`, validasi integer > 0.
2. Query data pegawai dari database berdasarkan ID.
3. Jika tidak ditemukan → flash error, redirect ke index.
4. Pre-fill form dengan data existing (`value="<?= e($employee['nip']) ?>"`).
5. CSRF token + hidden input `id` disisipkan.
6. Form dikirim via POST ke `../proses/update.php`.

**Bedanya dengan tambah.php:**
- Field sudah terisi data lama (pre-filled).
- AJAX NIP check menyertakan `exclude_id` agar NIP pegawai sendiri tidak dianggap duplikat.
- Hidden input `id` dikirim untuk identifikasi data yang diubah.

---

## 11. pegawai/detail.php

**Fungsi:** READ — Menampilkan rincian lengkap satu pegawai dalam kartu profil.

**Alur:**
1. Ambil ID dari `$_GET['id']`, validasi.
2. Query data pegawai: `SELECT id, nip, nama, email, jabatan FROM employees WHERE id = :id`.
3. Format inisial nama untuk avatar (2 huruf pertama dari nama depan dan belakang).
4. Tampilkan kartu profil: avatar bulat besar, nama, jabatan, NIP, email.
5. Tombol aksi: Edit dan Hapus (hapus pakai form POST).

---

## 12. proses/simpan.php

**Fungsi:** CREATE Backend — Menerima POST dari form tambah, validasi server-side, simpan ke database.

**Langkah Keamanan:**
1. **Cek Method:** Hanya menerima POST. Akses langsung via URL (GET) di-redirect.
2. **CSRF Verification:** `hash_equals()` membandingkan token form dengan token session — aman dari timing attacks.
3. **Validasi Input Server-side:** NIP, nama, email tidak boleh kosong; email harus valid (`filter_var`); NIP harus angka.
4. **Cek Duplikasi NIP:** Query database sebelum insert untuk memastikan NIP belum dipakai.
5. **Insert Data:** `INSERT INTO employees (nip, nama, email, jabatan) VALUES (:nip, :nama, :email, :jabatan)` — prepared statement.
6. **Flash + Redirect:** setFlash success/danger, redirect ke `pegawai/index.php` (pola PRG).

---

## 13. proses/update.php

**Fungsi:** UPDATE Backend — Menerima POST dari form edit, validasi, update database.

**Alur sama seperti simpan.php, bedanya:**
- Menerima parameter `id` dari form (hidden input).
- Validasi `$id > 0`.
- Cek duplikasi NIP: `AND id <> :id` agar NIP sendiri tidak dianggap duplikat.
- Query: `UPDATE employees SET nip = :nip, nama = :nama, email = :email, jabatan = :jabatan WHERE id = :id`.

---

## 14. proses/hapus.php

**Fungsi:** DELETE Backend — Menghapus satu data pegawai berdasarkan ID.

**Keamanan:**
- Hanya menerima POST (bukan GET — mencegah penghapusan via link/bookmark).
- CSRF token diverifikasi.
- ID divalidasi sebagai integer > 0.
- Query: `DELETE FROM employees WHERE id = :id` — prepared statement.
- Flash message + redirect.

---

## 15. api/cek_nip.php

**Fungsi:** Endpoint AJAX — Cek ketersediaan NIP secara real-time.

**Request:** `GET /api/cek_nip.php?nip=...&exclude_id=...`

**Response JSON:**
```json
{
  "status": "available|duplicate|empty|invalid",
  "available": true|false,
  "message": "...",
  "digits": 18
}
```

**Alur:**
1. Cek session login (401 jika expired).
2. Validasi NIP tidak kosong dan hanya angka (`ctype_digit`).
3. Query database: cek NIP yang sama (dengan/tanpa spasi) menggunakan `REPLACE(nip, " ", "")`.
4. Mode edit: exclude ID pegawai sendiri (`AND id <> :id`).
5. Jika duplikat: kembalikan nama dan jabatan pemilik NIP tersebut.
6. Jika tersedia: kembalikan jumlah digit (standar 18 digit).

**Digunakan oleh:** `assets/js/script.js` pada form tambah dan edit pegawai.

---

## 16. api/detail.php

**Fungsi:** Endpoint AJAX — Mengambil detail pegawai untuk Quick View Modal.

**Request:** `GET /api/detail.php?id=...`

**Response JSON:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "nip": "...",
    "nama": "...",
    "email": "...",
    "jabatan": "...",
    "initial": "BS"
  }
}
```

**Digunakan oleh:** Tombol Quick View (`btn-quick-view`) di dashboard dan pegawai/index.php. JavaScript menangkap data JSON dan mengisi konten modal `#quickViewContent`.

---

## 17. assets/js/script.js

**Fungsi:** JavaScript utama — menangani interaksi UI dan AJAX.

**Fitur:**

### 1. Sidebar Toggle (Desktop & Mobile)
- **Desktop:** Klik tombol `#sidebarCollapseBtn` → toggle class `collapsed` pada sidebar. State disimpan di `localStorage` agar tetap saat refresh.
- **Mobile:** Klik `#sidebarMobileToggle` → toggle class `sidebar-open` + overlay. Klik overlay atau link navigasi → tutup sidebar.

### 2. AJAX Cek NIP (SKKNI MUK JWD Butir 2.b)
- Event `input` pada field NIP (`#nip`).
- Debounce 400ms — menunggu user berhenti mengetik sebelum kirim request.
- `$.ajax()` ke `api/cek_nip.php` dengan parameter `nip` dan `exclude_id`.
- Response ditampilkan di elemen `#nipFeedback` dengan warna merah (duplikat) atau hijau (tersedia).

### 3. Quick View Modal (SKKNI MUK JWD Butir 2.b)
- Event delegasi `click` pada `.btn-quick-view`.
- `$.getJSON()` ke `api/detail.php?id=...`.
- Response JSON dirender menjadi HTML dan dimasukkan ke `#quickViewContent`.
- Modal Bootstrap ditampilkan via `new bootstrap.Modal().show()`.

### 4. Konfirmasi Hapus
- Event delegasi `submit` pada `.form-hapus`.
- `confirm()` dialog menampilkan nama pegawai yang akan dihapus.
- Jika user membatalkan → `e.preventDefault()` mencegah form terkirim.

### 5. Validasi Form (SKKNI MUK JWD Butir 1.b)
- Event `submit` pada `#formTambah` dan `#formEdit`.
- Cek setiap field required tidak boleh kosong.
- Validasi format email dengan regex sederhana.
- Menampilkan class `is-invalid` Bootstrap pada field yang salah.

---

## 18. assets/css/style.css

**Fungsi:** Stylesheet utama — mengatur seluruh tampilan visual aplikasi.

**Struktur:**

| Bagian | Baris | Keterangan |
|--------|-------|------------|
| Variabel CSS `:root` | 1-27 | Warna, ukuran, dan transisi global |
| Reset & Base | 29-42 | Box-sizing, font, scroll behavior |
| Sidebar | 44-270 | Layout, brand, navigasi, user card, toggle, tooltip |
| Topbar | 270-460 | Layout, search, breadcrumb, status pill, avatar |
| Responsive Mobile ≤991px | 510-543 | Sidebar slide-in, overlay, brand/text override |
| Fade-in & Animasi App-wide | 553-607 | content-wrapper fade, stagger children, card hover |
| Animasi Login | 608-723 | login-card enter, logo spin, stagger elements, button hover, prefers-reduced-motion |
| Dashboard Modern | 724-1210 | Hero banner, stat cards, roster table, distribution chart, progress bar |
| Final Overrides | 1210-1295 | Font normalisasi, sidebar collapsed, dashboard spacing, responsive ≤1199px, ≤767px, print |
| Halaman Data Pegawai | 1297-1700 | Breadcrumb, heading, stat grid, filter bar, table, pagination |
| Form Tambah/Edit | 1703-2125 | Back link, section cards, photo upload, input groups, status toggle, textarea, action bar |

**Responsive Breakpoints:**
- `≤ 1199.98px` — Padding content berkurang.
- `≤ 991.98px` — Sidebar berubah menjadi slide-in drawer mobile + overlay.
- `≤ 767.98px` — Layout stack vertikal, filter bar full-width, stat grid 2 kolom, form action stacked.
- `≤ 480px` — Stat grid 1 kolom.
- `print` — Sembunyikan sidebar/topbar, konten full-width.

**Animasi:**
- `.content-wrapper` — Fade in 0.9s saat halaman load.
- `.main-content > *` — Stagger fade-up (delay 0.08s per elemen).
- `.login-card` — Slide-up 0.7s + stagger children (logo, judul, input, tombol).
- `.card:hover` — translateY(-3px) + shadow membesar.
- `prefers-reduced-motion` — Semua animasi dan transisi dimatikan untuk aksesibilitas.

---

## 19. assets/css/ajax.css

**Fungsi:** Stylesheet tambahan untuk komponen yang dirender via AJAX.

**Isi:**
- `.dashboard-hero-img` — Ukuran dan filter gambar hero dashboard.
- `.quickview-modal` — Styling modal Quick View (border, radius, shadow).
- `.quickview-avatar` — Avatar inisial di modal (gradient background, rounded).
- `.quickview-info-list` — Grid info detail pegawai (NIP, Nama, Email, Jabatan).
- `#nipFeedback` — Sembunyikan jika kosong, minimum height jika ada isi.
- Responsive ≤767px — Hero image lebih kecil di mobile.

---

## Struktur Folder

```
pegawaiku_jwd/
├── config/
│   └── koneksi.php          ← Koneksi DB, session, helper functions
├── partials/
│   ├── header.php           ← Template atas: <head>, sidebar, topbar
│   └── footer.php           ← Template bawah: modal, script CDN
├── pegawai/
│   ├── index.php            ← READ: Daftar pegawai + filter + pagination
│   ├── tambah.php           ← CREATE: Form tambah pegawai
│   ├── edit.php             ← UPDATE: Form edit pegawai
│   └── detail.php           ← READ: Detail satu pegawai
├── proses/
│   ├── simpan.php           ← CREATE Backend: validasi + INSERT
│   ├── update.php           ← UPDATE Backend: validasi + UPDATE
│   └── hapus.php            ← DELETE Backend: validasi + DELETE
├── api/
│   ├── cek_nip.php          ← AJAX: Cek duplikasi NIP real-time
│   └── detail.php           ← AJAX: Detail pegawai untuk modal
├── assets/
│   ├── css/
│   │   ├── style.css        ← CSS utama (layout, animasi, responsive)
│   │   └── ajax.css         ← CSS komponen AJAX (modal, hero image)
│   ├── js/
│   │   └── script.js        ← JS utama (sidebar, AJAX, validasi)
│   └── images/
│       ├── logo.png         ← Logo aplikasi
│       └── dashboard-hero.svg ← Ilustrasi dashboard
├── index.php                ← Router: redirect login/dashboard
├── login.php                ← Halaman login
├── logout.php               ← Proses logout
├── dashboard.php            ← Dashboard utama
├── pegawai_bnsp.sql         ← SQL: struktur tabel + data dummy
└── README.md                ← Dokumentasi project
```

---

## Pola Arsitektur

- **MVC Sederhana (tanpa framework):**
  - **Model:** Query PDO langsung di setiap file (tidak ada class ORM).
  - **View:** File PHP yang menghasilkan HTML (dengan template partials).
  - **Controller:** Logika bisnis di file `proses/*.php` dan bagian PHP di atas HTML.

- **Pola PRG (Post-Redirect-Get):**
  Setiap form POST di-proses oleh file di `proses/`, lalu redirect dengan flash message. Mencegah duplikasi submit saat user menekan F5.

- **AJAX (Asynchronous JavaScript And XML):**
  Dua endpoint API mengembalikan JSON. JavaScript (jQuery) mengambil data tanpa reload halaman — sesuai SKKNI MUK JWD Butir 2.b.

- **Keamanan:**
  - Prepared Statement (anti SQL Injection)
  - CSRF Token (anti CSRF)
  - `htmlspecialchars()` / `e()` (anti XSS)
  - `password_verify()` + bcrypt (autentikasi aman)
  - `session_regenerate_id()` (anti Session Fixation)
  - POST-only untuk operasi destruktif (DELETE)
