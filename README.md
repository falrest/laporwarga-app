# LaporWarga

Sistem informasi pengaduan masyarakat berbasis web — PHP Native + MySQL,
tanpa framework, dijalankan di atas XAMPP.

> **Status proyek ini: Phase 1–3 dari rencana pengembangan (lihat bagian
> "Status Pengembangan" di bawah).** Database lengkap sudah dibuat untuk
> seluruh fitur, tapi implementasi backend PHP saat ini baru mencakup
> autentikasi penuh dan dashboard masyarakat. Fitur lain (buat laporan,
> upload, peta, admin, petugas) menyusul secara bertahap.

## 1. Teknologi

- Frontend: HTML5, CSS3 (custom, CSS variables), JavaScript Vanilla, Fetch API
- Backend: PHP 8.x native (tanpa framework), pola MVC sederhana
- Database: MySQL/MariaDB via PDO + prepared statements
- Server lokal: XAMPP (Apache + MySQL + phpMyAdmin)

## 2. Requirements

- XAMPP dengan PHP 8.0 atau lebih baru
- MySQL/MariaDB aktif
- Browser modern

## 3. Instalasi

1. Salin folder `laporwarga/` ke dalam `C:\xampp\htdocs\` (Windows) atau
   `/Applications/XAMPP/htdocs/` (Mac).
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.

## 4. Membuat Database via phpMyAdmin

1. Buka `http://localhost/phpmyadmin`.
2. Klik tab **Import**.
3. Klik **Choose File**, pilih `database/laporwarga.sql`.
4. Klik **Go / Kirim**. Database `laporwarga` beserta seluruh tabel dan
   data awal (roles, kategori, subkategori, akun demo, contoh laporan)
   akan langsung dibuat.

Alternatif via query console:

```sql
SOURCE /path/ke/database/laporwarga.sql;
```

## 5. Konfigurasi

Buka `app/config/database.php` dan sesuaikan bila kredensial MySQL Anda
berbeda dari default XAMPP (`root` tanpa password):

```php
const DB_HOST = '127.0.0.1';
const DB_NAME = 'laporwarga';
const DB_USER = 'root';
const DB_PASS = '';
```

Buka `app/config/app.php` dan sesuaikan `BASE_URL` jika folder project
Anda tidak bernama `laporwarga` persis di dalam `htdocs`:

```php
define('BASE_URL', '/laporwarga'); // ganti sesuai nama folder Anda
```

Batas ukuran upload (default 10MB) juga diatur di file yang sama:

```php
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);
```

## 6. Menjalankan Aplikasi

Buka browser ke:

```
http://localhost/laporwarga/public/index.php
```

Splash screen akan tampil sebentar, lalu mengarahkan ke halaman login
(atau dashboard bila sudah pernah login).

## 7. Akun Demo

| Role       | Email                     | Password       |
|------------|---------------------------|----------------|
| Admin      | admin@laporwarga.test     | Admin123!      |
| Petugas    | petugas@laporwarga.test   | Petugas123!    |
| Masyarakat | warga@laporwarga.test     | Warga123!      |

> ⚠️ **WAJIB diganti** sebelum digunakan di lingkungan production.
> Halaman admin/petugas pada versi ini belum diimplementasikan —
> gunakan akun masyarakat untuk mencoba alur yang sudah berjalan.

## 8. Struktur Folder

```
laporwarga/
├── app/
│   ├── config/       konfigurasi database, app, security
│   ├── models/        query database (PDO prepared statements)
│   └── helpers/        bootstrap, csrf, response, auth/RBAC, sanitasi, logger
├── public/           entry point publik: splash, login, register, assets
├── masyarakat/       halaman untuk role masyarakat
├── admin/            (menyusul) halaman untuk role admin
├── petugas/          (menyusul) halaman untuk role petugas
├── api/              endpoint AJAX (JSON), dipisah per domain
├── uploads/          file upload user (dilindungi .htaccess anti-eksekusi PHP)
└── database/
    └── laporwarga.sql
```

## 9. Keamanan yang Sudah Diterapkan

- Password di-hash dengan `password_hash()` / diverifikasi dengan `password_verify()` — tidak pernah plaintext.
- Semua query menggunakan PDO **prepared statements** (`ATTR_EMULATE_PREPARES` dimatikan).
- CSRF token wajib pada setiap POST (login, register, logout, dst).
- Session aman: `httponly`, `SameSite=Lax`, regenerasi ID setelah login, timeout tidak aktif 30 menit.
- Rate limiting login: maksimal 5 percobaan gagal → akun terkunci 15 menit.
- RBAC diperiksa di **setiap endpoint PHP**, bukan hanya menyembunyikan menu di UI (`require_role_page()` / `require_role_api()`).
- Output di-escape dengan `htmlspecialchars()` untuk mencegah XSS.
- `uploads/` diberi `.htaccess` yang mematikan eksekusi PHP, sehingga file berbahaya yang lolos validasi tetap tidak bisa dijalankan sebagai script.
- Error database mentah tidak pernah ditampilkan ke user — hanya dicatat ke log server.
- Security headers dasar (`X-Content-Type-Options`, `X-Frame-Options`, CSP, dll).

## 10. Troubleshooting

- **"Terjadi kesalahan pada sistem"**: cek `app/config/database.php`, dan lihat log error PHP/Apache untuk detail.
- **Halaman blank/putih**: aktifkan `display_errors` di `php.ini` XAMPP sementara untuk debugging development.
- **CSRF token tidak valid**: muat ulang halaman untuk mendapatkan token baru (token terikat ke session).
- **Redirect loop di login**: pastikan `BASE_URL` di `app/config/app.php` sesuai lokasi folder Anda.

## 11. Status Pengembangan (mengikuti prioritas 15 fase)

- [x] Phase 1 — Database + konfigurasi PHP
- [x] Phase 2 — Authentication (register, login, logout, session, RBAC, rate limiting)
- [x] Phase 3 — Dashboard masyarakat (kategori & laporan dari database)
- [x] Phase 4 — Create report (pilih kategori → form pengaduan, validasi penuh, nomor tiket unik)
- [x] Phase 5 — Upload file (evidence: validasi MIME/ekstensi/ukuran, nama file random, getimagesize())
- [x] Phase 6 — Map (Leaflet + OpenStreetMap: pilih lokasi, drag marker, search, geolocation, reverse geocoding)
- [x] Phase 7 — Report tracking (Laporan Saya dengan search/filter/pagination, Detail Laporan dengan timeline & update petugas)
- [x] Phase 8 — Notification (list, mark read, mark all read)
- [x] Phase 9 — Admin dashboard (KPI real-time, tren bulanan, donut kategori, peta persebaran Leaflet, aktivitas terbaru, tabel laporan terbaru)
- [x] Phase 10 — Petugas (dashboard ringkasan tugas, daftar laporan ditugaskan, RBAC: petugas hanya bisa lihat/ubah laporan miliknya)
- [x] Phase 11 — CRUD admin (Data Masyarakat, Instansi/Petugas, Kategori & Subkategori Pengaduan — semua modal + AJAX + soft delete)
- [ ] Phase 12 — Security hardening lanjutan
- [ ] Phase 13 — Performance optimization
- [ ] Phase 14 — Testing
- [ ] Phase 15 — Deployment

### Detail fitur admin & petugas yang sudah berfungsi penuh

- **Dashboard Admin** (`admin/index.php`) — 5 kartu KPI dengan badge naik/turun bulan-ke-bulan (query real dari DB), grafik tren SVG (tanpa library eksternal), donut kategori, peta Leaflet menampilkan semua titik laporan berwarna sesuai status, feed aktivitas dari `activity_logs`, tabel 5 laporan terbaru
- **Pengaduan Masuk/Diproses/Selesai/Ditolak** (`admin/reports.php?status=...`) — tab status, search debounced, filter kategori, pagination — dipakai bersama oleh admin & petugas (petugas otomatis hanya melihat laporan miliknya)
- **Detail Laporan + Aksi Petugas** (`admin/laporan-detail.php`) — data pelapor (NIK/HP ter-mask), media lampiran, peta lokasi read-only, form Update Status + Disposisi Petugas + Catatan + Upload Bukti Penanganan — satu submit memperbarui status, riwayat, notifikasi ke pelapor, dan log aktivitas sekaligus dalam satu transaction
- **Data Masyarakat / Instansi & Petugas** (`admin/users.php`, `admin/officers.php`) — tabel + modal tambah/edit + nonaktifkan (soft delete, karena user bisa punya riwayat laporan yang harus tetap ada)
- **Kategori Pengaduan** (`admin/categories.php`) — accordion kategori dengan CRUD subkategori di dalamnya, nonaktifkan (bukan hapus permanen, karena kategori lama mungkin masih dirujuk laporan)
- **Statistik** (`admin/statistics.php`) — tren 12 bulan & distribusi kategori lengkap
- **Pengaturan** (`admin/settings.php`) — ubah password akun sendiri (admin & petugas)

Login admin: `admin@laporwarga.test` / `Admin123!`. Login petugas: `petugas@laporwarga.test` / `Petugas123!` (otomatis diarahkan ke dashboard masing-masing sesuai role).

## 12. Deployment (ringkas)

1. Export database dari phpMyAdmin lokal (**Export → SQL**), import ke phpMyAdmin/MySQL hosting tujuan.
2. Upload seluruh folder project via FTP/File Manager hosting.
3. Sesuaikan `app/config/database.php` dengan kredensial database hosting.
4. Sesuaikan `BASE_URL` di `app/config/app.php`.
5. Pastikan folder `uploads/` writable (chmod 755/775) dan `.htaccess` di dalamnya ikut ter-upload.
6. Aktifkan HTTPS agar cookie `secure` pada session berfungsi.
