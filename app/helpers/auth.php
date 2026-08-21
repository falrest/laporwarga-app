<?php
/**
 * Autentikasi & RBAC (Role-Based Access Control).
 *
 * PENTING: RBAC di sini tidak hanya menyembunyikan menu — setiap endpoint
 * PHP (halaman & API) memanggil require_role() yang benar-benar menolak
 * akses (redirect / 403 JSON) jika role tidak sesuai. Lihat section 3.
 */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function login_user(array $userRow): void
{
    session_regenerate_id(true); // cegah session fixation setelah login
    $_SESSION['user'] = [
        'id'    => (int) $userRow['id'],
        'nama'  => $userRow['nama'],
        'email' => $userRow['email'],
        'no_hp' => $userRow['no_hp'],
        'foto'  => $userRow['foto'],
        'role'  => $userRow['role_name'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Dipanggil di halaman (non-API). Redirect ke login bila belum login,
 * redirect ke halaman "403" bila role tidak sesuai.
 */
function require_login_page(): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }
}

function require_role_page(array $allowedRoles): void
{
    require_login_page();
    $user = current_user();
    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        echo 'Akses ditolak. Anda tidak memiliki izin untuk membuka halaman ini.';
        exit;
    }
}

/**
 * Dipanggil di endpoint API. Mengembalikan JSON 401/403 (bukan redirect HTML)
 * karena dipanggil lewat fetch/AJAX.
 */
function require_login_api(): array
{
    if (!is_logged_in()) {
        json_error('Sesi Anda telah berakhir. Silakan login kembali.', 401);
    }
    return current_user();
}

function require_role_api(array $allowedRoles): array
{
    $user = require_login_api();
    if (!in_array($user['role'], $allowedRoles, true)) {
        json_error('Anda tidak memiliki akses untuk melakukan aksi ini.', 403);
    }
    return $user;
}
