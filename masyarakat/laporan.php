<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

require_role_page(['masyarakat']);

$user = current_user();
$pageTitle = 'Laporan Saya';
$active = 'laporan';
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

  <main style="padding:20px 20px 0;">
    <div class="input-wrap" style="margin-bottom:14px;">
      <span class="icon">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </span>
      <input class="input" type="text" id="search-input" placeholder="Cari laporan saya..." autocomplete="off">
    </div>

    <div id="filter-tabs" style="display:flex;gap:8px;overflow-x:auto;padding-bottom:16px;">
      <button class="filter-tab active" data-status="semua">Semua</button>
      <button class="filter-tab" data-status="menunggu">Menunggu</button>
      <button class="filter-tab" data-status="diproses">Diproses</button>
      <button class="filter-tab" data-status="selesai">Selesai</button>
      <button class="filter-tab" data-status="ditolak">Ditolak</button>
    </div>

    <div id="report-list"></div>
    <div id="list-skeleton" style="display:flex;flex-direction:column;gap:14px;">
      <div class="skeleton" style="height:210px;"></div>
      <div class="skeleton" style="height:210px;"></div>
    </div>
    <div id="empty-state" style="display:none;" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="1.5" style="margin:0 auto;"><path d="M9 3h6l1 3h3v15H5V6h3l1-3Z"/><path d="M9 11h6M9 15h6"/></svg>
      <h3>Belum Ada Laporan</h3>
      <p>Anda belum membuat pengaduan.</p>
      <a href="<?= BASE_URL ?>/masyarakat/pilih-kategori.php" class="btn btn-primary" style="margin-top:10px;">Buat Pengaduan</a>
    </div>
    <button id="btn-load-more" class="btn btn-outline btn-block" style="display:none;margin:4px 0 24px;">Muat Lebih Banyak</button>
  </main>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>

<style>
  .filter-tab {
    flex-shrink: 0; padding: 9px 18px; border-radius: var(--radius-full); border: 1px solid var(--border);
    background: var(--white); color: var(--text-secondary); font-size: 13.5px; font-weight: 600; cursor: pointer; font-family: var(--font);
  }
  .filter-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; }
  .report-card { display: block; margin-bottom: 16px; color: inherit; }
</style>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/laporan.js"></script>
</body>
</html>
