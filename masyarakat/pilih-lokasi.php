<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

require_role_page(['masyarakat']);

$user = current_user();
$pageTitle = 'Buat Laporan';
$active = 'buat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pilih Lokasi — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<style>
  html, body { height: 100%; }
  #map { position: absolute; inset: 0; z-index: 1; }
  .map-search-bar {
    position: absolute; top: 14px; left: 14px; right: 14px; z-index: 10;
    background: var(--white); border-radius: var(--radius-full); box-shadow: var(--shadow-md);
    display: flex; align-items: center; padding: 4px 6px 4px 16px;
  }
  .map-search-bar input { flex: 1; border: none; outline: none; padding: 10px 8px; font-size: 14.5px; font-family: var(--font); }
  .map-locate-btn {
    position: absolute; z-index: 10; right: 14px; top: 68px;
    width: 42px; height: 42px; border-radius: 50%; background: var(--white); border: none;
    box-shadow: var(--shadow-md); display: flex; align-items: center; justify-content: center; color: var(--primary); cursor: pointer;
  }
  .map-bottom-sheet {
    position: absolute; left: 0; right: 0; bottom: 0; z-index: 10;
    background: var(--white); border-radius: 22px 22px 0 0; box-shadow: 0 -8px 24px rgba(23,32,51,.12);
    padding: 18px 20px calc(84px + env(safe-area-inset-bottom));
  }
</style>
</head>
<body>
<div class="app-shell" style="padding-bottom:0;">
  <?php include __DIR__ . '/partials/header.php'; ?>

  <div style="position:relative;height:calc(100vh - 62px);">
    <div id="map"></div>

    <div class="map-search-bar">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" id="map-search-input" placeholder="Cari alamat atau nama tempat...">
    </div>

    <button type="button" class="map-locate-btn" id="btn-locate" aria-label="Lokasi saya">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>
    </button>

    <div class="map-bottom-sheet">
      <div style="display:flex;gap:12px;margin-bottom:16px;">
        <span style="width:38px;height:38px;border-radius:10px;background:var(--light-blue);color:var(--primary);
                     display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <div style="flex:1;min-width:0;">
          <div id="selected-address-title" style="font-weight:700;font-size:15px;">Menggeser peta...</div>
          <div id="selected-address-sub" class="text-secondary" style="font-size:13px;"></div>
        </div>
      </div>
      <button type="button" id="btn-confirm-location" class="btn btn-primary btn-block" style="margin-bottom:10px;">
        Konfirmasi Lokasi
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m20 6-11 11-5-5"/></svg>
      </button>
      <button type="button" id="btn-edit-address" class="btn btn-block" style="background:var(--light-blue);color:var(--primary);">
        Edit Detail Alamat
      </button>
    </div>
  </div>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script src="<?= BASE_URL ?>/public/assets/js/pilih-lokasi.js"></script>
</body>
</html>
