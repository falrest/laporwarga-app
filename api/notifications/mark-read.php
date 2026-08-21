<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Notification.php';

$user = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    json_error('ID notifikasi tidak valid.', 422);
}

Notification::markRead($id, $user['id']);

json_success('Notifikasi ditandai sebagai dibaca.');
