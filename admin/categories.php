<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Category.php';

require_role_page(['admin']);

$user = current_user();
$categories = Category::allWithSubcategoryCount();
$active = 'kategori';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategori Pengaduan — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="admin-content">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 style="font-size:22px;margin:0 0 4px;">Kategori Pengaduan</h1>
          <p class="text-secondary" style="margin:0;font-size:14px;">Kelola kategori dan subkategori laporan masyarakat.</p>
        </div>
        <button class="btn btn-primary btn-sm" id="btn-add-category">+ Tambah Kategori</button>
      </div>

      <div style="display:flex;flex-direction:column;gap:14px;" id="category-accordion">
        <?php foreach ($categories as $cat): ?>
          <div class="admin-panel category-row" data-id="<?= (int) $cat['id'] ?>" data-nama="<?= out($cat['nama']) ?>" data-icon="<?= out($cat['icon']) ?>">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div style="display:flex;align-items:center;gap:12px;">
                <span style="width:38px;height:38px;border-radius:10px;background:var(--light-blue);color:var(--primary);display:flex;align-items:center;justify-content:center;">
                  <?= category_icon($cat['icon']) ?>
                </span>
                <div>
                  <strong style="font-size:14.5px;"><?= out($cat['nama']) ?></strong>
                  <div class="text-secondary" style="font-size:12px;"><?= (int) $cat['subcategory_count'] ?> subkategori aktif</div>
                </div>
              </div>
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="badge <?= $cat['status'] === 'aktif' ? 'badge-selesai' : 'badge-ditolak' ?>"><?= $cat['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?></span>
                <button class="btn-table btn-edit-category">Edit</button>
                <button class="btn-table btn-toggle-category" style="background:#FDECEC;color:var(--danger);"><?= $cat['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                <button class="btn-table btn-toggle-subs">Subkategori ▾</button>
              </div>
            </div>
            <div class="subcategory-panel" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
              <div class="subcategory-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;"></div>
              <button class="btn-table btn-add-subcategory">+ Tambah Subkategori</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Kategori -->
<div class="admin-modal-overlay" id="category-modal-overlay">
  <div class="admin-modal">
    <h2 style="font-size:17px;margin:0 0 16px;" id="category-modal-title">Tambah Kategori</h2>
    <form id="category-form">
      <?= csrf_field() ?>
      <input type="hidden" id="category_id" value="">
      <div class="field"><label>Nama Kategori</label><input class="input" id="f-cat-nama" required></div>
      <div class="field">
        <label>Ikon</label>
        <select class="input" id="f-cat-icon">
          <option value="infrastruktur">Infrastruktur</option>
          <option value="sampah">Sampah</option>
          <option value="lampu">Penerangan</option>
          <option value="pohon">Lingkungan</option>
          <option value="drainase">Drainase</option>
          <option value="transportasi">Transportasi</option>
          <option value="pendidikan">Pendidikan</option>
          <option value="kesehatan">Kesehatan</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" id="btn-cancel-category-modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Subkategori -->
<div class="admin-modal-overlay" id="subcategory-modal-overlay">
  <div class="admin-modal" style="max-width:380px;">
    <h2 style="font-size:17px;margin:0 0 16px;" id="subcategory-modal-title">Tambah Subkategori</h2>
    <form id="subcategory-form">
      <?= csrf_field() ?>
      <input type="hidden" id="subcategory_id" value="">
      <input type="hidden" id="subcategory_category_id" value="">
      <div class="field"><label>Nama Subkategori</label><input class="input" id="f-sub-nama" required></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" id="btn-cancel-subcategory-modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/admin-categories.js"></script>
</body>
</html>
