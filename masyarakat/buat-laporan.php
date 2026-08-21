<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/Category.php';

require_role_page(['masyarakat']);

$user = current_user();
$categories = Category::allActive();

$categoryId    = (int) ($_GET['category_id'] ?? ($categories[0]['id'] ?? 0));
$subcategoryId = (int) ($_GET['subcategory_id'] ?? 0);
$subcategories = $categoryId ? Category::subcategories($categoryId) : [];

$pageTitle = 'Buat Laporan';
$active = 'buat';
$maxMb = round(UPLOAD_MAX_SIZE / (1024 * 1024));
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
    <!-- Step indicator -->
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:22px;">
      <span style="width:26px;height:26px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12.5px;font-weight:700;">1</span>
      <span style="flex:1;height:2px;background:var(--primary);"></span>
      <span style="width:26px;height:26px;border-radius:50%;background:var(--light-blue);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12.5px;font-weight:700;">2</span>
      <span style="flex:1;height:2px;background:var(--border);"></span>
      <span style="width:26px;height:26px;border-radius:50%;background:var(--light-blue);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12.5px;font-weight:700;">3</span>
    </div>

    <h1 style="font-size:20px;margin:0 0 6px;">Detail Laporan</h1>
    <p class="text-secondary" style="font-size:14px;margin:0 0 20px;">
      Lengkapi informasi di bawah ini agar laporan Anda dapat segera ditindaklanjuti.
    </p>

    <form id="report-form" novalidate>
      <?= csrf_field() ?>

      <div class="field">
        <label for="category_id">Kategori</label>
        <select class="input" id="category_id" name="category_id" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $categoryId ? 'selected' : '' ?>><?= out($c['nama']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="field-error" id="err-category_id"></div>
      </div>

      <div class="field">
        <label for="subcategory_id">Subkategori</label>
        <select class="input" id="subcategory_id" name="subcategory_id" required>
          <option value="">Pilih subkategori</option>
          <?php foreach ($subcategories as $sc): ?>
            <option value="<?= (int) $sc['id'] ?>" <?= (int) $sc['id'] === $subcategoryId ? 'selected' : '' ?>><?= out($sc['nama']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="field-error" id="err-subcategory_id"></div>
      </div>

      <div class="field">
        <label for="title">Judul Pengaduan</label>
        <input class="input" type="text" id="title" name="title" placeholder="Contoh: Jalan berlubang di Jl. Merdeka" required>
        <div class="field-error" id="err-title"></div>
      </div>

      <div class="field">
        <label for="description">Deskripsi Masalah</label>
        <textarea class="input" id="description" name="description" rows="4" placeholder="Jelaskan detail masalah yang Anda temui..." required></textarea>
        <div class="field-error" id="err-description"></div>
      </div>

      <h2 style="font-size:16px;margin:22px 0 12px;">Bukti Laporan</h2>
      <div class="field">
        <label id="upload-label" for="evidence" style="cursor:pointer;">
          <div id="upload-dropzone" style="border:1.5px dashed var(--border);border-radius:16px;background:var(--light-blue);
                      padding:30px 16px;text-align:center;">
            <div style="width:42px;height:42px;border-radius:50%;background:var(--primary);color:#fff;display:flex;
                        align-items:center;justify-content:center;margin:0 auto 10px;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 16V4M6 10l6-6 6 6"/><path d="M4 20h16"/></svg>
            </div>
            <div style="color:var(--primary);font-weight:600;font-size:14.5px;">Unggah Foto atau Video</div>
            <div class="text-secondary" style="font-size:12.5px;margin-top:4px;">Maksimal <?= $maxMb ?>MB. Format: JPG, PNG, MP4.</div>
          </div>
        </label>
        <input type="file" id="evidence" name="evidence[]" accept=".jpg,.jpeg,.png,.mp4" multiple style="display:none;">
        <div class="field-error" id="err-evidence"></div>
        <div id="preview-list" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px;"></div>
      </div>

      <h2 style="font-size:16px;margin:22px 0 12px;">Lokasi &amp; Waktu</h2>
      <div class="field">
        <label for="address">Lokasi Kejadian</label>
        <div class="input-wrap">
          <span class="icon">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          </span>
          <input class="input" type="text" id="address" name="address" placeholder="Cari alamat atau nama tempat..." required>
        </div>
        <div class="field-error" id="err-location"></div>
        <input type="hidden" id="latitude" name="latitude" value="">
        <input type="hidden" id="longitude" name="longitude" value="">
      </div>

      <div style="display:flex;gap:10px;margin-bottom:16px;">
        <button type="button" id="btn-use-location" class="btn btn-outline" style="flex:1;font-size:13.5px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>
          Gunakan Lokasi Saya
        </button>
        <a href="<?= BASE_URL ?>/masyarakat/pilih-lokasi.php" id="btn-open-map" class="btn btn-outline" style="width:52px;padding:0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 20 3 17V4l6 3m0 13 6-3m-6 3V7m6 10 6 3V7l-6-3m0 16V4m0 3-6-3"/></svg>
        </a>
      </div>

      <div class="field">
        <label for="incident_date">Tanggal Kejadian</label>
        <input class="input" type="date" id="incident_date" name="incident_date" max="<?= date('Y-m-d') ?>" required>
        <div class="field-error" id="err-incident_date"></div>
      </div>

      <div style="background:var(--light-blue);border-radius:12px;padding:14px;display:flex;gap:10px;margin:8px 0 24px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <p style="margin:0;font-size:12.5px;color:var(--primary-dark);">
          Pastikan foto dan lokasi laporan sesuai dengan kondisi sebenarnya. Laporan palsu dapat dikenakan sanksi sesuai peraturan yang berlaku.
        </p>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="btn-submit-report">
        Kirim Pengaduan
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 12 14-7-7 14-2-5-5-2Z"/></svg>
      </button>
    </form>
  </main>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>
  window.BASE_URL = <?= json_encode(BASE_URL) ?>;
  window.MAX_UPLOAD_SIZE = <?= (int) UPLOAD_MAX_SIZE ?>;
</script>
<script src="<?= BASE_URL ?>/public/assets/js/buat-laporan.js"></script>
</body>
</html>
