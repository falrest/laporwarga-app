<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Category.php';
require_once __DIR__ . '/../../app/models/Report.php';
require_once __DIR__ . '/../../app/services/UploadService.php';

$user = require_role_api(['masyarakat']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

// Form-data (karena ada file), bukan JSON - token CSRF ada di $_POST.
csrf_require();

$categoryId    = (int) ($_POST['category_id'] ?? 0);
$subcategoryId = (int) ($_POST['subcategory_id'] ?? 0);
$title         = s($_POST['title'] ?? '');
$description   = s($_POST['description'] ?? '');
$address       = s($_POST['address'] ?? '');
$latitude      = $_POST['latitude'] ?? '';
$longitude     = $_POST['longitude'] ?? '';
$incidentDate  = s($_POST['incident_date'] ?? '');

// ---- Validasi backend penuh (section 41: jangan pernah hanya andalkan JS) ----
$errors = [];

$category = Category::find($categoryId);
if (!$category || $category['status'] !== 'aktif') {
    $errors['category_id'] = 'Kategori tidak valid.';
} else {
    $validSub = false;
    foreach (Category::subcategories($categoryId) as $sc) {
        if ((int) $sc['id'] === $subcategoryId) {
            $validSub = true;
            break;
        }
    }
    if (!$validSub) {
        $errors['subcategory_id'] = 'Subkategori tidak valid untuk kategori ini.';
    }
}

if (mb_strlen($title) < 5) {
    $errors['title'] = 'Judul pengaduan minimal 5 karakter.';
}
if (mb_strlen($description) < 10) {
    $errors['description'] = 'Deskripsi masalah minimal 10 karakter.';
}
if ($address === '') {
    $errors['address'] = 'Lokasi kejadian wajib diisi.';
}
if (!is_numeric($latitude) || !is_numeric($longitude) || (float) $latitude === 0.0) {
    $errors['location'] = 'Silakan pilih lokasi melalui peta atau gunakan lokasi Anda.';
}
$incidentTimestamp = strtotime($incidentDate);
if (!$incidentDate || $incidentTimestamp === false || $incidentTimestamp > time()) {
    $errors['incident_date'] = 'Tanggal kejadian tidak valid.';
}

if (!empty($errors)) {
    json_error('Periksa kembali data laporan Anda.', 422, $errors);
}

// ---- Upload file (opsional multi-file, tapi minimal 1 disarankan) ----
$uploadedFiles = [];
try {
    if (!empty($_FILES['evidence']) && is_array($_FILES['evidence']['name'])) {
        $count = count($_FILES['evidence']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['evidence']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $singleFile = [
                'name'     => $_FILES['evidence']['name'][$i],
                'type'     => $_FILES['evidence']['type'][$i],
                'tmp_name' => $_FILES['evidence']['tmp_name'][$i],
                'error'    => $_FILES['evidence']['error'][$i],
                'size'     => $_FILES['evidence']['size'][$i],
            ];
            $uploadedFiles[] = UploadService::handle($singleFile, 'reports');
        }
    }
} catch (RuntimeException $e) {
    json_error($e->getMessage(), 422, ['evidence' => $e->getMessage()]);
}

try {
    $result = Report::create([
        'user_id'        => $user['id'],
        'category_id'    => $categoryId,
        'subcategory_id' => $subcategoryId,
        'title'          => $title,
        'description'    => $description,
        'address'        => $address,
        'latitude'       => (float) $latitude,
        'longitude'      => (float) $longitude,
        'incident_date'  => date('Y-m-d', $incidentTimestamp),
    ], $uploadedFiles);

    log_activity($user['id'], 'create_report', 'reports', $result['id']);

    json_success('Laporan berhasil dikirim.', [
        'report_id'     => $result['id'],
        'ticket_number' => $result['ticket_number'],
        'redirect'      => BASE_URL . '/masyarakat/detail-laporan.php?id=' . $result['id'],
    ], 201);
} catch (Throwable $e) {
    error_log('Create report error: ' . $e->getMessage());
    json_error('Laporan gagal dikirim. Silakan coba lagi.', 500);
}
