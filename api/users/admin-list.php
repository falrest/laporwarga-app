<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$role   = s($_GET['role'] ?? 'masyarakat');
$search = s($_GET['search'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));

if (!in_array($role, ['masyarakat', 'petugas', 'admin'], true)) {
    json_error('Role tidak valid.', 422);
}

$result = User::adminPaginated($role, $search, $page, DEFAULT_PAGE_SIZE);

json_success('Berhasil memuat data pengguna.', $result);
