<?php
/**
 * Inisialisasi session yang aman + security headers.
 * File ini harus di-include di awal SETIAP halaman/endpoint yang butuh session.
 */

function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('lw_session');
    session_start();

    // Session timeout (30 menit tidak aktif -> logout otomatis)
    $timeoutSeconds = 30 * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutSeconds) {
        $_SESSION = [];
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();

    // Regenerasi ID secara berkala untuk mencegah session fixation
    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
    } elseif (time() - $_SESSION['created_at'] > 15 * 60) {
        session_regenerate_id(true);
        $_SESSION['created_at'] = time();
    }
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://*.tile.openstreetmap.org https://unpkg.com; script-src 'self' 'unsafe-inline' https://unpkg.com; style-src 'self' 'unsafe-inline' https://unpkg.com; connect-src 'self' https://nominatim.openstreetmap.org;");
}
