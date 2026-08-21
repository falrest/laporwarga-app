<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Location.php';

require_role_page(['masyarakat']);

$sessionUser = current_user();
$user = User::findById($sessionUser['id']);
$districts = Location::allDistricts();
$villages = $user['kecamatan_id'] ? Location::villagesByDistrict((int) $user['kecamatan_id']) : [];

$pageTitle = 'Edit Profil';
$fotoUrl = $user['foto'] ? BASE_URL . '/' . ltrim($user['foto'], '/') : BASE_URL . '/public/assets/images/avatar-placeholder.png';
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
<div class="app-shell" style="padding-bottom:24px;">
  <header class="app-header">
    <a href="<?= BASE_URL ?>/masyarakat/profil.php" style="display:flex;align-items:center;gap:10px;color:var(--text);font-weight:700;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
      Edit Profil
    </a>
  </header>

  <main style="padding:20px;">
    <div style="text-align:center;margin-bottom:22px;position:relative;">
      <label for="foto" style="cursor:pointer;display:inline-block;position:relative;">
        <img id="foto-preview" src="<?= out($fotoUrl) ?>" alt="" style="width:88px;height:88px;border-radius:50%;object-fit:cover;box-shadow:var(--shadow-md);">
        <span style="position:absolute;bottom:0;right:0;width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        </span>
      </label>
      <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png" style="display:none;">
    </div>

    <form id="edit-profile-form" novalidate>
      <?= csrf_field() ?>

      <div class="field">
        <label for="nama">Nama Lengkap</label>
        <input class="input" type="text" id="nama" name="nama" value="<?= out($user['nama']) ?>" required>
        <div class="field-error" id="err-nama"></div>
      </div>

      <div class="field">
        <label for="no_hp">Nomor HP</label>
        <input class="input" type="tel" id="no_hp" name="no_hp" value="<?= out($user['no_hp']) ?>" required>
        <div class="field-error" id="err-no_hp"></div>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input class="input" type="email" id="email" name="email" value="<?= out($user['email'] ?? '') ?>">
        <div class="field-error" id="err-email"></div>
      </div>

      <div class="field">
        <label for="alamat">Alamat Lengkap</label>
        <textarea class="input" id="alamat" name="alamat" rows="2" required><?= out($user['alamat'] ?? '') ?></textarea>
        <div class="field-error" id="err-alamat"></div>
      </div>

      <div class="field">
        <label for="kecamatan_id">Kecamatan</label>
        <select class="input" id="kecamatan_id" name="kecamatan_id" required>
          <option value="">Pilih Kecamatan</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= (int) $d['id'] ?>" <?= (int) $user['kecamatan_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= out($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="field-error" id="err-kecamatan_id"></div>
      </div>

      <div class="field">
        <label for="desa_id">Desa / Kelurahan</label>
        <select class="input" id="desa_id" name="desa_id" required>
          <?php foreach ($villages as $v): ?>
            <option value="<?= (int) $v['id'] ?>" <?= (int) $user['desa_id'] === (int) $v['id'] ? 'selected' : '' ?>><?= out($v['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="field-error" id="err-desa_id"></div>
      </div>

      <div class="field">
        <label>NIK</label>
        <input class="input" type="text" value="<?= out(mask_nik($user['nik'])) ?>" disabled style="background:var(--bg);color:var(--text-secondary);">
        <div class="field-hint">NIK tidak dapat diubah langsung. Hubungi admin untuk verifikasi perubahan NIK.</div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="btn-save-profile">Simpan Perubahan</button>
    </form>
  </main>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/edit-profil.js"></script>
</body>
</html>
