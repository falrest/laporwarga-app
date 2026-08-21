<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Report.php';

require_role_page(['masyarakat']);

$user = current_user();
$categories = Category::allActive();
$latestReports = Report::latestByUser($user['id'], 3);

$firstName = explode(' ', $user['nama'])[0];
$pageTitle = 'Beranda';
$active = 'beranda';
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
    <h1 style="font-size:22px;margin:4px 0 6px;">Halo, <?= out($firstName) ?> 👋</h1>
    <p class="text-secondary" style="margin:0 0 20px;font-size:14.5px;">
      Ada masalah di sekitar kamu? Laporkan sekarang agar segera ditangani.
    </p>

    <a href="<?= BASE_URL ?>/masyarakat/pilih-kategori.php" class="btn btn-primary btn-block" style="margin-bottom:28px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
      Buat Pengaduan
    </a>

    <h2 style="font-size:17px;margin:0 0 14px;">Kategori Laporan</h2>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px 8px;margin-bottom:28px;">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/masyarakat/pilih-kategori.php?category_id=<?= (int) $cat['id'] ?>"
           style="text-align:center;color:var(--text);">
          <div style="width:52px;height:52px;border-radius:16px;background:var(--light-blue);color:var(--primary);
                      display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
            <?= category_icon($cat['icon']) ?>
          </div>
          <span style="font-size:11.5px;line-height:1.3;display:block;"><?= out($cat['nama']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
      <h2 style="font-size:17px;margin:0;">Laporan Saya</h2>
      <a href="<?= BASE_URL ?>/masyarakat/laporan.php" style="font-size:14px;font-weight:600;">Lihat Semua</a>
    </div>

    <?php if (empty($latestReports)): ?>
      <div class="empty-state card">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="1.5" style="margin:0 auto;"><path d="M9 3h6l1 3h3v15H5V6h3l1-3Z"/><path d="M9 11h6M9 15h6"/></svg>
        <h3>Belum Ada Laporan</h3>
        <p>Anda belum membuat pengaduan.</p>
        <a href="<?= BASE_URL ?>/masyarakat/pilih-kategori.php" class="btn btn-primary" style="margin-top:10px;">Buat Pengaduan</a>
      </div>
    <?php else: ?>
      <?php foreach ($latestReports as $r): ?>
        <?php $pct = status_progress_percent($r['status']); ?>
        <a href="<?= BASE_URL ?>/masyarakat/detail-laporan.php?id=<?= (int) $r['id'] ?>"
           class="card" style="display:block;margin-bottom:16px;padding:0;overflow:hidden;color:inherit;">
          <div style="position:relative;height:150px;background:#DDE6F2;">
            <?php if (!empty($r['photo'])): ?>
              <img src="<?= out(BASE_URL . '/' . ltrim($r['photo'], '/')) ?>" alt=""
                   style="width:100%;height:100%;object-fit:cover;" loading="lazy">
            <?php endif; ?>
            <span class="badge badge-<?= out($r['status']) ?>" style="position:absolute;top:12px;right:12px;background:rgba(255,255,255,.92);">
              <?= out(status_label($r['status'])) ?>
            </span>
          </div>
          <div style="padding:16px;">
            <h3 style="margin:0 0 4px;font-size:15.5px;"><?= out($r['title']) ?></h3>
            <p class="text-secondary" style="margin:0 0 12px;font-size:13px;display:flex;align-items:center;gap:4px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= out($r['address']) ?>
            </p>
            <div style="height:5px;border-radius:3px;background:var(--border);overflow:hidden;">
              <div style="height:100%;width:<?= $pct ?>%;background:var(--primary);"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);margin-top:6px;">
              <span>Diterima</span><span>Diproses</span><span>Selesai</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>
</body>
</html>
