<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Location.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$districtId = (int) ($_GET['district_id'] ?? 0);

if ($districtId <= 0 || !Location::districtExists($districtId)) {
    json_error('Kecamatan tidak valid.', 422);
}

$villages = Location::villagesByDistrict($districtId);

json_success('Berhasil memuat daftar desa/kelurahan.', ['villages' => $villages]);
