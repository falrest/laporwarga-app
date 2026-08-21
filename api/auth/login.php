<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$identifier = s($input['identifier'] ?? '');
$password   = (string) ($input['password'] ?? '');
$remember   = !empty($input['remember']);

if ($identifier === '' || $password === '') {
    json_error('Email/Nomor HP dan password wajib diisi.', 422, [
        'identifier' => $identifier === '' ? 'Wajib diisi.' : null,
        'password'   => $password === '' ? 'Wajib diisi.' : null,
    ]);
}

$user = User::findByEmailOrPhone($identifier);

if (!$user) {
    // Jangan bocorkan apakah akun ada atau tidak (mencegah user enumeration).
    json_error('Email/Nomor HP atau password salah.', 401);
}

if (User::isLocked($user)) {
    json_error('Akun sementara dikunci karena terlalu banyak percobaan gagal. Coba lagi beberapa menit lagi.', 429);
}

if ($user['status'] !== 'aktif') {
    json_error('Akun Anda tidak aktif. Hubungi admin untuk bantuan.', 403);
}

if (!password_verify($password, $user['password'])) {
    User::registerFailedAttempt((int) $user['id']);
    log_activity((int) $user['id'], 'login_failed', 'users', (int) $user['id']);
    json_error('Email/Nomor HP atau password salah.', 401);
}

User::resetFailedAttempts((int) $user['id']);
login_user($user);
log_activity((int) $user['id'], 'login', 'users', (int) $user['id']);

if ($remember) {
    // Cookie "remember me" terpisah dari session id (bukan pengganti autentikasi utama).
    $token = bin2hex(random_bytes(32));
    setcookie('lw_remember', $token, [
        'expires'  => time() + 30 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    $stmt = db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
    $stmt->execute([hash('sha256', $token), $user['id']]);
}

$redirect = match ($user['role_name']) {
    'admin'   => BASE_URL . '/admin/index.php',
    'petugas' => BASE_URL . '/petugas/dashboard.php',
    default   => BASE_URL . '/masyarakat/dashboard.php',
};

json_success('Login berhasil.', ['redirect' => $redirect]);
