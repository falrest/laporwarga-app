<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/Location.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$input = json_input();

$nama            = s($input['nama'] ?? '');
$nik             = s($input['nik'] ?? '');
$no_hp           = s($input['no_hp'] ?? '');
$email           = s($input['email'] ?? '');
$password        = (string) ($input['password'] ?? '');
$password_confirm = (string) ($input['password_confirm'] ?? '');
$alamat          = s($input['alamat'] ?? '');
$kecamatan_id    = (int) ($input['kecamatan_id'] ?? 0);
$desa_id         = (int) ($input['desa_id'] ?? 0);

// ---- Validasi backend (tidak boleh hanya mengandalkan JS - section 41) ----
$errors = [];

if ($nama === '' || mb_strlen($nama) < 3) {
    $errors['nama'] = 'Nama lengkap wajib diisi (minimal 3 karakter).';
}
if (!is_valid_nik($nik)) {
    $errors['nik'] = 'NIK harus terdiri dari 16 digit angka.';
} elseif (User::nikExists($nik)) {
    $errors['nik'] = 'NIK sudah terdaftar.';
}
if (!is_valid_phone($no_hp)) {
    $errors['no_hp'] = 'Nomor HP tidak valid.';
} elseif (User::phoneExists($no_hp)) {
    $errors['no_hp'] = 'Nomor HP sudah terdaftar.';
}
if ($email !== '') {
    if (!is_valid_email($email)) {
        $errors['email'] = 'Format email tidak valid.';
    } elseif (User::emailExists($email)) {
        $errors['email'] = 'Email sudah terdaftar.';
    }
}
if (!is_valid_password($password)) {
    $errors['password'] = 'Password minimal 8 karakter.';
}
if ($password !== $password_confirm) {
    $errors['password_confirm'] = 'Konfirmasi password tidak sama.';
}
if ($alamat === '') {
    $errors['alamat'] = 'Alamat wajib diisi.';
}
if (!Location::districtExists($kecamatan_id)) {
    $errors['kecamatan_id'] = 'Kecamatan tidak valid.';
} elseif (!Location::villageExists($desa_id, $kecamatan_id)) {
    $errors['desa_id'] = 'Desa/Kelurahan tidak valid untuk kecamatan yang dipilih.';
}

if (!empty($errors)) {
    json_error('Periksa kembali data yang Anda masukkan.', 422, $errors);
}

try {
    $userId = User::create([
        'nama'         => $nama,
        'nik'          => $nik,
        'email'        => $email,
        'no_hp'        => $no_hp,
        'password'     => $password,
        'alamat'       => $alamat,
        'kecamatan_id' => $kecamatan_id,
        'desa_id'      => $desa_id,
    ]);

    log_activity($userId, 'register', 'users', $userId);

    json_success('Akun berhasil dibuat. Silakan masuk untuk melanjutkan.', ['user_id' => $userId], 201);
} catch (PDOException $e) {
    error_log('Register error: ' . $e->getMessage());
    json_error('Terjadi kesalahan pada sistem. Silakan coba lagi.', 500);
}
