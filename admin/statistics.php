<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Report.php';

require_role_page(['admin']);

$user = current_user();
$counts = Report::countsByStatus();
$trend = Report::monthlyTrend(12);
$categoryBreakdown = Report::categoryBreakdown(8);
$active = 'statistik';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistik — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="admin-content">
      <h1 style="font-size:22px;margin:0 0 4px;">Statistik</h1>
      <p class="text-secondary" style="margin:0 0 22px;font-size:14px;">Analisis performa penanganan pengaduan secara menyeluruh.</p>

      <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="kpi-card">
          <div class="kpi-value"><?= number_format($counts['total']) ?></div>
          <div class="kpi-label">Total Laporan Sepanjang Waktu</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-value"><?= $counts['total'] > 0 ? round(($counts['selesai'] / $counts['total']) * 100) : 0 ?>%</div>
          <div class="kpi-label">Tingkat Penyelesaian</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-value"><?= $counts['total'] > 0 ? round(($counts['ditolak'] / $counts['total']) * 100) : 0 ?>%</div>
          <div class="kpi-label">Tingkat Penolakan</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
        <div class="admin-panel">
          <div class="admin-panel-title">Tren Pengaduan 12 Bulan Terakhir</div>
          <div id="trend-chart" style="margin-top:16px;"></div>
        </div>
        <div class="admin-panel">
          <div class="admin-panel-title">Distribusi Kategori</div>
          <div id="category-donut" style="margin-top:10px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  window.TREND_DATA = <?= json_encode($trend) ?>;
  window.CATEGORY_DATA = <?= json_encode($categoryBreakdown) ?>;
</script>
<script src="<?= BASE_URL ?>/public/assets/js/admin-dashboard.js"></script>
</body>
</html>
