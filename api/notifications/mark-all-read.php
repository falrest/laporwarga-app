<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Notification.php';

$user = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

Notification::markAllRead($user['id']);

json_success('Semua notifikasi ditandai sebagai dibaca.');
