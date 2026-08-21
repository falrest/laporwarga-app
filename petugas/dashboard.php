<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Report.php';

require_role_page(['petugas']);

$user = current_user();
$counts = Report::countsForOfficer($user['id']);
$latest = Report::latestForOfficer($user['id'], 8);
$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Petugas — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/../admin/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/../admin/partials/topbar.php'; ?>

    <div class="admin-content">
      <h1 style="font-size:22px;margin:0 0 4px;">Halo, <?= out($user['nama']) ?> 👋</h1>
      <p class="text-secondary" style="margin:0 0 22px;font-size:14px;">Berikut ringkasan laporan yang ditugaskan kepada Anda.</p>

      <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="kpi-card">
          <div class="kpi-icon" style="background:var(--light-blue);color:var(--primary);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6l1 3h3v15H5V6h3l1-3Z"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['total']) ?></div>
          <div class="kpi-label">Total Ditugaskan</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:#EEF1F6;color:#475467;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 7v5l3 3"/></svg>
          </div>
          <div class="kpi-value"><?= number_format($counts['dalam_penanganan']) ?></div>
          <div class="kpi-label">Sedang Dikerjakan</div>
        </div>
        <div class="kpi-card">
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

      <div class="admin-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div class="admin-panel-title">Laporan Ditugaskan Kepada Anda</div>
          <a href="<?= BASE_URL ?>/petugas/laporan.php" style="font-size:13px;font-weight:600;">Lihat Semua</a>
        </div>
        <div style="overflow-x:auto;margin-top:14px;">
          <table class="admin-table">
            <thead><tr><th>ID Laporan</th><th>Pelapor</th><th>Kategori</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php if (empty($latest)): ?>
                <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Belum ada laporan yang ditugaskan.</td></tr>
              <?php endif; ?>
              <?php foreach ($latest as $r): ?>
                <tr>
                  <td style="font-weight:600;">#<?= out($r['ticket_number']) ?></td>
                  <td><?= out($r['pelapor_nama']) ?></td>
                  <td><?= out($r['category_name']) ?></td>
                  <td><span class="badge badge-<?= out($r['status']) ?>"><?= out(status_label($r['status'])) ?></span></td>
                  <td class="text-secondary"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                  <td><a href="<?= BASE_URL ?>/admin/laporan-detail.php?id=<?= (int) $r['id'] ?>" class="btn-table primary">Tangani</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
