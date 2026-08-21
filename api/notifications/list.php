<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Notification.php';

$user = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$notifications = Notification::listByUser($user['id']);

foreach ($notifications as &$n) {
    $n['time_label'] = Notification::relativeTime($n['created_at']);
}
unset($n);

json_success('Berhasil memuat notifikasi.', [
    'notifications' => $notifications,
    'unread_count'  => Notification::unreadCount($user['id']),
]);
