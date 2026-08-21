<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    json_error('ID pengguna tidak valid.', 422);
}
if ($id === current_user()['id']) {
    json_error('Anda tidak dapat menonaktifkan akun Anda sendiri.', 422);
}

try {
    User::adminSoftDelete($id);
    log_activity(current_user()['id'], 'admin_delete_user', 'users', $id);
    json_success('Pengguna berhasil dinonaktifkan.');
} catch (PDOException $e) {
    // FK RESTRICT: user tidak bisa dihapus jika masih punya laporan terkait sebagai referensi wajib.
    error_log('Admin delete user error: ' . $e->getMessage());
    json_error('Pengguna tidak dapat dinonaktifkan karena masih memiliki data terkait.', 409);
}
