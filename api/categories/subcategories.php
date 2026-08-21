<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Category.php';

require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$categoryId = (int) ($_GET['category_id'] ?? 0);
$category = Category::find($categoryId);

if (!$category) {
    json_error('Kategori tidak valid.', 422);
}

$subcategories = Category::subcategories($categoryId);

json_success('Berhasil memuat subkategori.', [
    'category'      => $category,
    'subcategories' => $subcategories,
]);
