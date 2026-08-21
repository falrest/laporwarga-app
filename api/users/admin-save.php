<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Location.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();

$id           = (int) ($input['id'] ?? 0);
$roleName     = s($input['role'] ?? 'masyarakat');
$nama         = s($input['nama'] ?? '');
$nik          = s($input['nik'] ?? '');
$email        = s($input['email'] ?? '');
$no_hp        = s($input['no_hp'] ?? '');
$password     = (string) ($input['password'] ?? '');
$alamat       = s($input['alamat'] ?? '');
$kecamatan_id = (int) ($input['kecamatan_id'] ?? 0);
$desa_id      = (int) ($input['desa_id'] ?? 0);
$status       = s($input['status'] ?? 'aktif');

$roleIdMap = ['admin' => 1, 'petugas' => 2, 'masyarakat' => 3];
if (!isset($roleIdMap[$roleName])) {
    json_error('Role tidak valid.', 422);
}

$errors = [];
if (mb_strlen($nama) < 3) $errors['nama'] = 'Nama minimal 3 karakter.';
if (!is_valid_phone($no_hp)) {
    $errors['no_hp'] = 'Nomor HP tidak valid.';
} else {
    $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE no_hp = ? AND id != ?');
    $stmt->execute([$no_hp, $id]);
    if ((int) $stmt->fetchColumn() > 0) $errors['no_hp'] = 'Nomor HP sudah digunakan.';
}
if ($email !== '' && !is_valid_email($email)) $errors['email'] = 'Format email tidak valid.';
if ($nik !== '' && !is_valid_nik($nik)) $errors['nik'] = 'NIK harus 16 digit.';
if (!$id && !is_valid_password($password)) $errors['password'] = 'Password minimal 8 karakter (wajib untuk pengguna baru).';
if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors['status'] = 'Status tidak valid.';

if (!empty($errors)) {
    json_error('Periksa kembali data yang dimasukkan.', 422, $errors);
}

try {
    $data = [
        'role_id' => $roleIdMap[$roleName], 'nama' => $nama, 'nik' => $nik, 'email' => $email,
        'no_hp' => $no_hp, 'password' => $password, 'alamat' => $alamat,
        'kecamatan_id' => $kecamatan_id ?: null, 'desa_id' => $desa_id ?: null, 'status' => $status,
    ];

    if ($id) {
        User::adminUpdate($id, $data);
        log_activity(current_user()['id'], 'admin_update_user', 'users', $id);
        json_success('Data pengguna berhasil diperbarui.');
    } else {
        $newId = User::adminCreate($data);
        log_activity(current_user()['id'], 'admin_create_user', 'users', $newId);
        json_success('Pengguna baru berhasil ditambahkan.', ['id' => $newId], 201);
    }
} catch (Throwable $e) {
    error_log('Admin save user error: ' . $e->getMessage());
    json_error('Terjadi kesalahan pada sistem.', 500);
}
