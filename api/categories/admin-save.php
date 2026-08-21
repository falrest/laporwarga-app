<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Category.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$id   = (int) ($input['id'] ?? 0);
$nama = s($input['nama'] ?? '');
$icon = s($input['icon'] ?? 'infrastruktur');

if (mb_strlen($nama) < 3) {
    json_error('Nama kategori minimal 3 karakter.', 422, ['nama' => 'Nama kategori minimal 3 karakter.']);
}

try {
    if ($id) {
        Category::update($id, $nama, $icon);
        log_activity(current_user()['id'], 'admin_update_category', 'categories', $id);
        json_success('Kategori berhasil diperbarui.');
    } else {
        $newId = Category::create($nama, $icon);
        log_activity(current_user()['id'], 'admin_create_category', 'categories', $newId);
        json_success('Kategori baru berhasil ditambahkan.', ['id' => $newId], 201);
    }
} catch (Throwable $e) {
    error_log('Admin save category error: ' . $e->getMessage());
    json_error('Terjadi kesalahan pada sistem.', 500);
}
