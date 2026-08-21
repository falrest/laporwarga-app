<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

require_role_page(['masyarakat']);

$user = current_user();
$pageTitle = 'Notifikasi';
$active = 'notifikasi';
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
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
      <h1 style="font-size:22px;margin:0;">Notifikasi</h1>
      <button id="btn-mark-all-read" class="text-secondary" style="background:none;border:none;font-size:13px;font-weight:600;color:var(--primary);cursor:pointer;font-family:var(--font);">
        Tandai Semua Dibaca
      </button>
    </div>
    <p class="text-secondary" style="font-size:14px;margin:0 0 20px;">Pembaruan terbaru dari laporan Anda.</p>

    <div id="notif-skeleton" style="display:flex;flex-direction:column;gap:10px;">
      <div class="skeleton" style="height:76px;"></div>
      <div class="skeleton" style="height:76px;"></div>
      <div class="skeleton" style="height:76px;"></div>
    </div>
    <div id="notif-list" style="display:flex;flex-direction:column;gap:10px;"></div>
    <div id="notif-empty" style="display:none;" class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="1.5" style="margin:0 auto;"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
      <h3>Belum Ada Notifikasi</h3>
      <p>Notifikasi terbaru akan muncul di sini.</p>
    </div>
  </main>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>

<style>
  .notif-item { display: flex; gap: 12px; padding: 14px; border-radius: 16px; background: var(--light-blue); border: none; text-align: left; width: 100%; cursor: pointer; font-family: var(--font); }
  .notif-item.is-read { background: var(--white); border: 1px solid var(--border); }
  .notif-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
</style>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>; window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/notifikasi.js"></script>
</body>
</html>
