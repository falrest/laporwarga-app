<?php
/**
 * Konfigurasi umum aplikasi.
 */

// Sesuaikan jika folder project diletakkan berbeda di htdocs XAMPP.
// Contoh: jika project ada di htdocs/laporwarga -> '/laporwarga'
define('BASE_URL', '/laporwarga');

define('APP_NAME', 'LaporWarga');
define('APP_TAGLINE', 'Suaramu, Perubahan untuk Kotamu.');

// Upload
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10 MB, configurable
define('UPLOAD_ALLOWED_IMAGE', ['jpg', 'jpeg', 'png']);
define('UPLOAD_ALLOWED_VIDEO', ['mp4']);
define('UPLOAD_ALLOWED_MIME', [
    'image/jpeg',
    'image/png',
    'video/mp4',
]);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);

// Rate limiting login
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// Timezone
date_default_timezone_set('Asia/Jakarta');
