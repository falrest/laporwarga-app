<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/Location.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/public/index.php');
    exit;
}

$districts = Location::allDistricts();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/auth.css">
</head>
<body>
<div class="auth-shell" style="padding-top:32px;">
  <div class="auth-logo-wrap">
    <div class="splash-logo" style="width:64px;height:64px;margin:0 auto;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#0053A6" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 11 2 2 4-4"/></svg>
    </div>
  </div>
  <h1 class="auth-title">Buat Akun <?= out(APP_NAME) ?></h1>
  <p class="auth-subtitle">Bergabunglah untuk mulai melaporkan dan memantau pelayanan publik di lingkungan Anda.</p>

  <form id="register-form" novalidate>
    <?= csrf_field() ?>

    <div class="section-card">
      <div class="section-title">
        <span class="section-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </span>
        Informasi Pribadi
      </div>
      <div class="field">
        <label for="nama">Nama Lengkap</label>
        <input class="input" type="text" id="nama" name="nama" placeholder="Sesuai KTP" required>
        <div class="field-error" id="err-nama"></div>
      </div>
      <div class="field mt-0">
        <label for="nik">NIK</label>
        <input class="input" type="text" id="nik" name="nik" placeholder="16 digit NIK" maxlength="16" inputmode="numeric" required>
        <div class="field-error" id="err-nik"></div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-title">
        <span class="section-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
        </span>
        Kontak &amp; Keamanan
      </div>
      <div class="field">
        <label for="no_hp">Nomor HP</label>
        <input class="input" type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" required>
        <div class="field-error" id="err-no_hp"></div>
      </div>
      <div class="field">
        <label for="email">Email (Opsional)</label>
        <input class="input" type="email" id="email" name="email" placeholder="contoh@email.com">
        <div class="field-error" id="err-email"></div>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <input class="input" style="padding-left:14px" type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
          <button type="button" class="toggle-visibility" data-target="password" aria-label="Tampilkan password">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="field-error" id="err-password"></div>
      </div>
      <div class="field mt-0">
        <label for="password_confirm">Konfirmasi Password</label>
        <div class="input-wrap">
          <input class="input" style="padding-left:14px" type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
          <button type="button" class="toggle-visibility" data-target="password_confirm" aria-label="Tampilkan password">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="field-error" id="err-password_confirm"></div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-title">
        <span class="section-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        Domisili
      </div>
      <div class="field">
        <label for="alamat">Alamat Lengkap</label>
        <textarea class="input" id="alamat" name="alamat" rows="2" placeholder="Nama jalan, RT/RW, nomor rumah" required></textarea>
        <div class="field-error" id="err-alamat"></div>
      </div>
      <div class="field">
        <label for="kecamatan_id">Kecamatan</label>
        <select class="input" id="kecamatan_id" name="kecamatan_id" required>
          <option value="">Pilih Kecamatan</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= (int) $d['id'] ?>"><?= out($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="field-error" id="err-kecamatan_id"></div>
      </div>
      <div class="field mt-0">
        <label for="desa_id">Desa / Kelurahan</label>
        <select class="input" id="desa_id" name="desa_id" required disabled>
          <option value="">Pilih Desa/Kelurahan</option>
        </select>
        <div class="field-error" id="err-desa_id"></div>
      </div>
    </div>

    <label class="consent-row">
      <input type="checkbox" id="consent" name="consent" required>
      <span>Saya telah membaca dan menyetujui
        <a href="<?= BASE_URL ?>/public/privacy.php">Kebijakan Privasi</a> serta
        <a href="<?= BASE_URL ?>/public/terms.php">Syarat &amp; Ketentuan</a> LaporWarga.</span>
    </label>

    <button type="submit" class="btn btn-primary btn-block" id="btn-register">Buat Akun Sekarang →</button>
  </form>

  <div class="auth-footer">Sudah punya akun? <a href="<?= BASE_URL ?>/public/login.php">Masuk di sini</a></div>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/register.js"></script>
</body>
</html>
