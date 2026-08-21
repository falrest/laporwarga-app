<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';

$user = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();
$currentPassword = (string) ($input['current_password'] ?? '');
$newPassword     = (string) ($input['new_password'] ?? '');
$confirmPassword = (string) ($input['confirm_password'] ?? '');

$fullUser = User::findById($user['id']);

if (!$fullUser || !password_verify($currentPassword, $fullUser['password'])) {
    json_error('Password saat ini salah.', 422, ['current_password' => 'Password saat ini salah.']);
}
if (!is_valid_password($newPassword)) {
    json_error('Password baru minimal 8 karakter.', 422, ['new_password' => 'Password baru minimal 8 karakter.']);
}
if ($newPassword !== $confirmPassword) {
    json_error('Konfirmasi password tidak sama.', 422, ['confirm_password' => 'Konfirmasi password tidak sama.']);
}

$stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);

log_activity($user['id'], 'update_profile', 'users', $user['id']);

json_success('Password berhasil diperbarui.');
