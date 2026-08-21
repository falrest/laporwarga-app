<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/Location.php';

require_role_page(['admin']);

$user = current_user();
$districts = Location::allDistricts();
$active = 'masyarakat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Masyarakat — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="admin-content">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
          <h1 style="font-size:22px;margin:0 0 4px;">Data Masyarakat</h1>
          <p class="text-secondary" style="margin:0;font-size:14px;">Kelola akun warga terdaftar di LaporWarga.</p>
        </div>
        <button class="btn btn-primary btn-sm" id="btn-add-user">+ Tambah Warga</button>
      </div>

      <div class="admin-panel">
        <div class="input-wrap" style="max-width:320px;margin-bottom:16px;">
          <span class="icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
          <input class="input" type="text" id="search-input" placeholder="Cari nama, email, atau nomor HP...">
        </div>
        <div style="overflow-x:auto;">
          <table class="admin-table">
            <thead><tr><th>Nama</th><th>Kontak</th><th>Wilayah</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead>
            <tbody id="table-body"><tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Memuat...</td></tr></tbody>
          </table>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
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

<!-- Modal Tambah/Edit -->
<div class="admin-modal-overlay" id="user-modal-overlay">
  <div class="admin-modal">
    <h2 style="font-size:17px;margin:0 0 16px;" id="modal-title">Tambah Warga</h2>
    <form id="user-form">
      <?= csrf_field() ?>
      <input type="hidden" id="user_id" value="">
      <div class="field"><label>Nama Lengkap</label><input class="input" id="f-nama" required></div>
      <div class="field"><label>NIK (opsional)</label><input class="input" id="f-nik" maxlength="16"></div>
      <div class="field"><label>Nomor HP</label><input class="input" id="f-no_hp" required></div>
      <div class="field"><label>Email (opsional)</label><input class="input" type="email" id="f-email"></div>
      <div class="field"><label id="password-label">Password</label><input class="input" type="password" id="f-password" placeholder="Kosongkan jika tidak diubah"></div>
      <div class="field"><label>Alamat</label><textarea class="input" id="f-alamat" rows="2"></textarea></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="field">
          <label>Kecamatan</label>
          <select class="input" id="f-kecamatan_id">
            <option value="">Pilih</option>
            <?php foreach ($districts as $d): ?><option value="<?= (int) $d['id'] ?>"><?= out($d['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Desa/Kelurahan</label><select class="input" id="f-desa_id"><option value="">Pilih kecamatan dulu</option></select></div>
      </div>
      <div class="field">
        <label>Status</label>
        <select class="input" id="f-status"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" id="btn-cancel-modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btn-save-user">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>; window.USER_ROLE = 'masyarakat';</script>
<script src="<?= BASE_URL ?>/public/assets/js/admin-users.js"></script>
</body>
</html>
