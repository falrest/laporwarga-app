<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Report.php';
require_once __DIR__ . '/../../app/helpers/icons.php';

$user = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$search = s($_GET['search'] ?? '');
$status = s($_GET['status'] ?? 'semua');
$page   = max(1, (int) ($_GET['page'] ?? 1));

$result = Report::paginatedByUser($user['id'], $search, $status, $page, DEFAULT_PAGE_SIZE);

// Tambahkan label & progress siap-pakai supaya frontend tidak perlu logika status.
foreach ($result['items'] as &$item) {
    $item['status_label'] = status_label($item['status']);
    $item['progress']     = status_progress_percent($item['status']);
    $item['photo_url']    = $item['photo'] ? BASE_URL . '/' . ltrim($item['photo'], '/') : null;
    unset($item['photo']);
}
unset($item);

json_success('Berhasil memuat laporan.', $result);
