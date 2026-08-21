<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Report.php';
require_once __DIR__ . '/../../app/helpers/icons.php';

require_role_api(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$status     = s($_GET['status'] ?? 'semua');
$search     = s($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$page       = max(1, (int) ($_GET['page'] ?? 1));

$assignedTo = null;
if (current_user()['role'] === 'petugas') {
    $assignedTo = current_user()['id']; // petugas hanya melihat laporan yang ditugaskan padanya
}

$result = Report::adminPaginated($status, $search, $categoryId, $assignedTo, $page, DEFAULT_PAGE_SIZE);

foreach ($result['items'] as &$item) {
    $item['status_label'] = status_label($item['status']);
}
unset($item);

json_success('Berhasil memuat laporan.', $result);
