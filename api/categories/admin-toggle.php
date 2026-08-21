<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Category.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$id = (int) ($input['id'] ?? 0);

if (!$id || !Category::find($id)) {
    json_error('Kategori tidak ditemukan.', 404);
}

// Nonaktifkan (bukan hapus permanen) - kategori mungkin masih dirujuk laporan lama (FK RESTRICT).
Category::toggleStatus($id);
log_activity(current_user()['id'], 'admin_update_category', 'categories', $id);

json_success('Status kategori berhasil diperbarui.');
