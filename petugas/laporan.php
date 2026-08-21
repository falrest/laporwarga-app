<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/Category.php';

require_role_page(['petugas']);

$user = current_user();
$statusFilter = s($_GET['status'] ?? 'semua');
$categories = Category::allActive();

$statusTabs = [
    'semua' => 'Semua', 'diajukan' => 'Baru Ditugaskan', 'dalam_penanganan' => 'Diproses',
    'selesai' => 'Selesai', 'ditolak' => 'Ditolak',
];
$titleMap = [
    'semua' => 'Semua Laporan Saya', 'diajukan' => 'Laporan Baru Ditugaskan', 'dalam_penanganan' => 'Laporan Diproses',
    'selesai' => 'Laporan Selesai', 'ditolak' => 'Laporan Ditolak',
];
$active = ['diajukan' => 'masuk', 'dalam_penanganan' => 'diproses', 'selesai' => 'selesai', 'ditolak' => 'ditolak'][$statusFilter] ?? 'masuk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= out($titleMap[$statusFilter] ?? 'Laporan Saya') ?> — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/../admin/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/../admin/partials/topbar.php'; ?>

    <div class="admin-content">
      <h1 style="font-size:22px;margin:0 0 4px;"><?= out($titleMap[$statusFilter] ?? 'Laporan Saya') ?></h1>
      <p class="text-secondary" style="margin:0 0 20px;font-size:14px;">Laporan yang ditugaskan khusus kepada Anda.</p>

      <div class="status-tabs">
        <?php foreach ($statusTabs as $key => $label): ?>
          <a href="<?= BASE_URL ?>/petugas/laporan.php?status=<?= $key ?>" class="<?= $statusFilter === $key ? 'active' : '' ?>"><?= out($label) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="admin-panel">
        <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
          <div class="input-wrap" style="flex:1;min-width:220px;">
            <span class="icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
            <input class="input" type="text" id="search-input" placeholder="Cari nomor tiket atau judul...">
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table class="admin-table">
            <thead><tr><th>ID Laporan</th><th>Pelapor</th><th>Kategori</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody id="report-table-body"><tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Memuat...</td></tr></tbody>
          </table>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;">
          <span class="text-secondary" style="font-size:12.5px;" id="pagination-info"></span>
          <div style="display:flex;gap:8px;">
            <button class="btn-table" id="btn-prev-page" disabled>Sebelumnya</button>
            <button class="btn-table" id="btn-next-page" disabled>Berikutnya</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>
  window.BASE_URL = <?= json_encode(BASE_URL) ?>;
  window.STATUS_FILTER = <?= json_encode($statusFilter) ?>;
</script>
<script>
  // Petugas memakai script daftar yang sama dengan admin; API sudah otomatis
  // membatasi hasil hanya untuk laporan yang assigned_to = petugas ini (lihat api/reports/admin-list.php).
  const categoryFilterFallback = document.createElement('select');
  categoryFilterFallback.id = 'category-filter';
  categoryFilterFallback.style.display = 'none';
  categoryFilterFallback.innerHTML = '<option value="0">0</option>';
  document.body.appendChild(categoryFilterFallback);
</script>
<script src="<?= BASE_URL ?>/public/assets/js/admin-reports.js"></script>
</body>
</html>
