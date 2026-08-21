<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Location.php';
require_once __DIR__ . '/../../app/services/UploadService.php';

$user = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$nama         = s($_POST['nama'] ?? '');
$no_hp        = s($_POST['no_hp'] ?? '');
$email        = s($_POST['email'] ?? '');
$alamat       = s($_POST['alamat'] ?? '');
$kecamatan_id = (int) ($_POST['kecamatan_id'] ?? 0);
$desa_id      = (int) ($_POST['desa_id'] ?? 0);

$errors = [];

if (mb_strlen($nama) < 3) {
    $errors['nama'] = 'Nama lengkap minimal 3 karakter.';
}
if (!is_valid_phone($no_hp)) {
    $errors['no_hp'] = 'Nomor HP tidak valid.';
} else {
    $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE no_hp = ? AND id != ?');
    $stmt->execute([$no_hp, $user['id']]);
    if ((int) $stmt->fetchColumn() > 0) {
        $errors['no_hp'] = 'Nomor HP sudah digunakan akun lain.';
    }
}
if ($email !== '') {
    if (!is_valid_email($email)) {
        $errors['email'] = 'Format email tidak valid.';
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user['id']]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['email'] = 'Email sudah digunakan akun lain.';
        }
    }
}
if ($alamat === '') {
    $errors['alamat'] = 'Alamat wajib diisi.';
}
if (!Location::districtExists($kecamatan_id)) {
    $errors['kecamatan_id'] = 'Kecamatan tidak valid.';
} elseif (!Location::villageExists($desa_id, $kecamatan_id)) {
    $errors['desa_id'] = 'Desa/Kelurahan tidak valid.';
}

if (!empty($errors)) {
    json_error('Periksa kembali data Anda.', 422, $errors);
}

$fotoPath = null;
if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    try {
        $uploaded = UploadService::handle($_FILES['foto'], 'profiles');
        $fotoPath = $uploaded['path'];
    } catch (RuntimeException $e) {
        json_error($e->getMessage(), 422, ['foto' => $e->getMessage()]);
    }
}

try {
    $sql = 'UPDATE users SET nama = :nama, no_hp = :no_hp, email = :email, alamat = :alamat,
            kecamatan_id = :kecamatan_id, desa_id = :desa_id';
    $params = [
        ':nama'         => $nama,
        ':no_hp'        => $no_hp,
        ':email'        => $email ?: null,
        ':alamat'       => $alamat,
        ':kecamatan_id' => $kecamatan_id,
        ':desa_id'      => $desa_id,
        ':id'           => $user['id'],
    ];
    if ($fotoPath) {
        $sql .= ', foto = :foto';
        $params[':foto'] = $fotoPath;
    }
    $sql .= ' WHERE id = :id';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    log_activity($user['id'], 'update_profile', 'users', $user['id']);

    // Sinkronkan data session agar header langsung menampilkan perubahan.
    $_SESSION['user']['nama']  = $nama;
    $_SESSION['user']['email'] = $email ?: null;
    $_SESSION['user']['no_hp'] = $no_hp;
    if ($fotoPath) {
        $_SESSION['user']['foto'] = BASE_URL . '/' . $fotoPath;
    }

    json_success('Profil berhasil diperbarui.', ['redirect' => BASE_URL . '/masyarakat/profil.php']);
} catch (Throwable $e) {
    error_log('Update profile error: ' . $e->getMessage());
    json_error('Terjadi kesalahan pada sistem. Silakan coba lagi.', 500);
}
