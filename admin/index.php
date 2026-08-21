<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Report.php';
require_once __DIR__ . '/../app/models/ActivityLog.php';
require_once __DIR__ . '/../app/models/Notification.php';

require_role_page(['admin']);

$user = current_user();
$counts = Report::countsByStatus();
$delta = Report::monthOverMonthDelta();
$trend = Report::monthlyTrend(6);
$categoryBreakdown = Report::categoryBreakdown(5);
$mapPoints = Report::mapPoints(300);
$latestReports = Report::latestForAdmin(5);
$recentActivity = ActivityLog::recent(5);

$active = 'dashboard';

function delta_badge(array $delta, string $key): string
{
    $pct = $delta[$key] ?? 0;
    $cls = $pct >= 0 ? 'up' : 'down';
    $sign = $pct >= 0 ? '+' : '';
    return "<span class=\"kpi-delta $cls\">{$sign}{$pct}%</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="admin-content">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 style="font-size:24px;margin:0 0 4px;">Ringkasan Sistem Pengaduan</h1>
          <p class="text-secondary" style="margin:0;font-size:14px;">Pantau kinerja dan status laporan masyarakat secara real-time.</p>
        </div>
        <button class="btn btn-outline btn-sm" id="btn-export" type="button">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15V3M7 10l5 5 5-5"/><path d="M4 21h16"/></svg>
          Unduh Laporan
        </button>
      </div>

      <div class="filter-bar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2"><path d="M4 4h16l-6 8v6l-4 2v-8L4 4Z"/></svg>
        <select class="filter-select" disabled><option>Bulan Ini</option></select>
        <select class="filter-select" disabled><option>Semua Kategori</option></select>
        <select class="filter-select" disabled><option>Semua Wilayah</option></select>
        <select class="filter-select" disabled><option>Semua Status</option></select>
        <a href="<?= BASE_URL ?>/admin/index.php" style="margin-left:auto;font-size:13px;font-weight:600;">Reset Filter</a>
      </div>

      <!-- KPI Cards -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <?= delta_badge($delta, 'diajukan') ?>
          <div class="kpi-icon" style="background:var(--light-blue);color:var(--primary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6l1 3h3v15H5V6h3l1-3Z"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['total']) ?></div>
          <div class="kpi-label">Total Pengaduan</div>
        </div>
        <div class="kpi-card">
          <?= delta_badge($delta, 'diajukan') ?>
          <div class="kpi-icon" style="background:var(--light-blue);color:var(--primary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 2"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['diajukan']) ?></div>
          <div class="kpi-label">Pengaduan Baru</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#EEF1F6;color:#475467;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['dalam_penanganan']) ?></div>
          <div class="kpi-label">Sedang Diproses</div>
        </div>
        <div class="kpi-card">
          <?= delta_badge($delta, 'selesai') ?>
          <div class="kpi-icon" style="background:#E7F7ED;color:var(--success);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-5"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['selesai']) ?></div>
          <div class="kpi-label">Selesai</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#FDECEC;color:var(--danger);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['ditolak']) ?></div>
          <div class="kpi-label">Ditolak</div>
        </div>
      </div>

      <!-- Trend + Category donut -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;align-items:start;">
        <div class="admin-panel">
          <div class="admin-panel-title">Tren Pengaduan per Bulan</div>
          <div class="admin-panel-subtitle">Volume laporan selama 6 bulan terakhir</div>
          <div id="trend-chart" style="margin-top:16px;"></div>
        </div>
        <div class="admin-panel">
          <div class="admin-panel-title">Kategori Laporan</div>
          <div id="category-donut" style="margin-top:10px;"></div>
        </div>
      </div>

      <!-- Map + Activity -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;align-items:start;">
        <div class="admin-panel" style="padding:0;overflow:hidden;">
          <div style="padding:18px 20px 0;">
            <div class="admin-panel-title">Peta Persebaran Laporan</div>
            <div class="admin-panel-subtitle">Titik lokasi laporan masyarakat</div>
          </div>
          <div id="admin-map" style="height:340px;margin-top:14px;"></div>
        </div>
        <div class="admin-panel">
          <div class="admin-panel-title">Aktivitas Terbaru</div>
          <div style="margin-top:14px;display:flex;flex-direction:column;gap:16px;">
            <?php if (empty($recentActivity)): ?>
              <p class="text-secondary" style="font-size:13px;">Belum ada aktivitas.</p>
            <?php endif; ?>
            <?php foreach ($recentActivity as $a): ?>
              <div style="display:flex;gap:10px;">
                <span style="width:30px;height:30px;border-radius:50%;background:var(--light-blue);color:var(--primary);flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
                </span>
                <div>
                  <p style="margin:0;font-size:13px;line-height:1.4;">
                    <strong><?= out($a['user_nama'] ?? 'Sistem') ?></strong> <?= out(ActivityLog::actionLabel($a['action'])) ?>
                    <?php if ($a['record_id']): ?>#<?= (int) $a['record_id'] ?><?php endif; ?>
                  </p>
                  <span class="text-secondary" style="font-size:11.5px;"><?= out(Notification::relativeTime($a['created_at'])) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Latest reports table -->
      <div class="admin-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div class="admin-panel-title">Laporan Terbaru</div>
            <div class="admin-panel-subtitle">Daftar aduan masyarakat yang baru masuk</div>
          </div>
          <a href="<?= BASE_URL ?>/admin/reports.php" style="font-size:13px;font-weight:600;">Lihat Semua</a>
        </div>
        <div style="overflow-x:auto;margin-top:14px;">
          <table class="admin-table">
            <thead>
              <tr><th>ID Laporan</th><th>Pelapor</th><th>Kategori</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php if (empty($latestReports)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-secondary);padding:24px;">Belum ada laporan.</td></tr>
              <?php endif; ?>
              <?php foreach ($latestReports as $r): ?>
                <tr>
                  <td style="font-weight:600;">#<?= out($r['ticket_number']) ?></td>
                  <td>
                    <span class="admin-avatar-chip"><?= out(mb_strtoupper(mb_substr($r['pelapor_nama'], 0, 1))) ?></span>
                    <?= out($r['pelapor_nama']) ?>
                  </td>
                  <td><?= out($r['category_name']) ?></td>
                  <td><span class="badge badge-<?= out($r['status']) ?>"><?= out(status_label($r['status'])) ?></span></td>
                  <td class="text-secondary"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                  <td><a href="<?= BASE_URL ?>/admin/laporan-detail.php?id=<?= (int) $r['id'] ?>" class="btn-table <?= $r['status'] === 'diajukan' ? 'primary' : '' ?>"><?= $r['status'] === 'diajukan' ? 'Tinjau' : 'Detail' ?></a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  window.TREND_DATA = <?= json_encode($trend) ?>;
  window.CATEGORY_DATA = <?= json_encode($categoryBreakdown) ?>;
  window.MAP_POINTS = <?= json_encode($mapPoints) ?>;
</script>
<script src="<?= BASE_URL ?>/public/assets/js/admin-dashboard.js"></script>
</body>
</html>
