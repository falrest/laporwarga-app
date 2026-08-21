<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Category.php';

require_role_page(['masyarakat']);

$user = current_user();
$categories = Category::allActive();

$selectedId = (int) ($_GET['category_id'] ?? ($categories[0]['id'] ?? 0));
$selected = null;
foreach ($categories as $c) {
    if ((int) $c['id'] === $selectedId) { $selected = $c; break; }
}
if (!$selected && !empty($categories)) {
    $selected = $categories[0];
    $selectedId = (int) $selected['id'];
}
$subcategories = $selected ? Category::subcategories($selectedId) : [];

$pageTitle = 'Buat Laporan';
$active = 'buat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= out($pageTitle) ?> — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main style="padding:20px;">
    <div class="input-wrap" style="margin-bottom:22px;">
      <span class="icon">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </span>
      <input class="input" type="text" id="category-search" placeholder="Cari Kategori..." autocomplete="off">
    </div>

    <h2 style="font-size:15px;margin:0 0 10px;">Kategori Aktif</h2>
    <div id="category-list" style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
      <?php foreach ($categories as $cat): ?>
        <?php $isActive = (int) $cat['id'] === $selectedId; ?>
        <a href="<?= BASE_URL ?>/masyarakat/pilih-kategori.php?category_id=<?= (int) $cat['id'] ?>"
           class="category-item"
           data-name="<?= out(mb_strtolower($cat['nama'])) ?>"
           style="display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:16px;text-decoration:none;
                  background:<?= $isActive ? 'var(--primary)' : 'var(--white)' ?>;
                  color:<?= $isActive ? '#fff' : 'var(--text)' ?>;
                  border:1px solid <?= $isActive ? 'var(--primary)' : 'var(--border)' ?>;">
          <span style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                       background:<?= $isActive ? 'rgba(255,255,255,.18)' : 'var(--light-blue)' ?>;
                       color:<?= $isActive ? '#fff' : 'var(--primary)' ?>;">
            <?= category_icon($cat['icon']) ?>
          </span>
          <span style="flex:1;">
            <strong style="display:block;font-size:15px;"><?= out($cat['nama']) ?></strong>
            <span style="font-size:12.5px;opacity:.85;"><?= count(Category::subcategories((int) $cat['id'])) ?> Subkategori</span>
          </span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($selected): ?>
      <h2 style="font-size:15px;margin:0 0 12px;">Pilih Subkategori</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <?php foreach ($subcategories as $sub): ?>
          <a href="<?= BASE_URL ?>/masyarakat/buat-laporan.php?category_id=<?= $selectedId ?>&subcategory_id=<?= (int) $sub['id'] ?>"
             class="card" style="text-align:center;padding:20px 12px;color:var(--text);">
            <span style="width:44px;height:44px;border-radius:12px;background:var(--light-blue);color:var(--primary);
                         display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
              <?= category_icon($selected['icon']) ?>
            </span>
            <span style="font-size:13px;font-weight:600;line-height:1.3;"><?= out($sub['nama']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>

<script>
document.getElementById('category-search').addEventListener('input', function (e) {
  const q = e.target.value.trim().toLowerCase();
  document.querySelectorAll('.category-item').forEach(function (el) {
    el.style.display = el.dataset.name.includes(q) ? 'flex' : 'none';
  });
});
</script>
</body>
</html>
