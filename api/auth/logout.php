<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$user = current_user();
if ($user) {
    log_activity($user['id'], 'logout', 'users', $user['id']);
}

logout_user();

json_success('Anda berhasil keluar.', ['redirect' => BASE_URL . '/public/login.php']);
