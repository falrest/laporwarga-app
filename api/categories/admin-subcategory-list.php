<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Category.php';

require_role_api(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metode tidak diizinkan.', 405);
}

$categoryId = (int) ($_GET['category_id'] ?? 0);

json_success('Berhasil memuat subkategori.', [
    'subcategories' => Category::allSubcategoriesOf($categoryId),
]);
