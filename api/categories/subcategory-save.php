<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Category.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$action = s($input['action'] ?? 'save');

if ($action === 'toggle') {
    $id = (int) ($input['id'] ?? 0);
    if (!$id) json_error('ID subkategori tidak valid.', 422);
    Category::toggleSubcategoryStatus($id);
    log_activity(current_user()['id'], 'admin_update_category', 'subcategories', $id);
    json_success('Status subkategori berhasil diperbarui.');
}

$id         = (int) ($input['id'] ?? 0);
$categoryId = (int) ($input['category_id'] ?? 0);
$nama       = s($input['nama'] ?? '');

if (!Category::find($categoryId)) {
    json_error('Kategori induk tidak valid.', 422);
}
if (mb_strlen($nama) < 3) {
    json_error('Nama subkategori minimal 3 karakter.', 422, ['nama' => 'Nama subkategori minimal 3 karakter.']);
}

try {
    if ($id) {
        Category::updateSubcategory($id, $nama);
        log_activity(current_user()['id'], 'admin_update_category', 'subcategories', $id);
        json_success('Subkategori berhasil diperbarui.');
    } else {
        $newId = Category::createSubcategory($categoryId, $nama);
        log_activity(current_user()['id'], 'admin_create_category', 'subcategories', $newId);
        json_success('Subkategori baru berhasil ditambahkan.', ['id' => $newId], 201);
    }
} catch (Throwable $e) {
    error_log('Admin save subcategory error: ' . $e->getMessage());
    json_error('Terjadi kesalahan pada sistem.', 500);
}
