<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/public/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/auth.css">
</head>
<body>
<div class="auth-shell">
  <div class="auth-logo-wrap">
    <img src="<?= BASE_URL ?>/public/assets/images/logo.svg" alt="LaporWarga">
  </div>
  <h1 class="auth-title">Masuk ke <?= out(APP_NAME) ?></h1>
  <p class="auth-subtitle">Layanan Aspirasi dan Pengaduan Online Rakyat</p>

  <div class="auth-card">
    <form id="login-form" novalidate>
      <?= csrf_field() ?>

      <div class="field">
        <label for="identifier">Email atau Nomor HP</label>
        <div class="input-wrap">
          <span class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input class="input" type="text" id="identifier" name="identifier" placeholder="Masukkan email atau no HP" autocomplete="username" required>
        </div>
        <div class="field-error" id="err-identifier"></div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <span class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="input" type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
          <button type="button" class="toggle-visibility" data-target="password" aria-label="Tampilkan password">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="field-error" id="err-password"></div>
      </div>

      <div class="auth-row-between">
        <label class="remember-me"><input type="checkbox" name="remember" id="remember"> Ingat saya</label>
        <a href="<?= BASE_URL ?>/public/forgot-password.php">Lupa password?</a>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="btn-login">Masuk</button>

      <div class="auth-divider">ATAU</div>

      <button type="button" class="btn-google" id="btn-google" disabled title="Konfigurasi Google OAuth belum diaktifkan">
        <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.6 6.1 29.6 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.7-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.6 6.1 29.6 4 24 4 16.3 4 9.6 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.5 0 10.4-1.9 14.2-5.1l-6.5-5.5C29.7 35.5 27 36.5 24 36.5c-5.2 0-9.6-3.3-11.3-7.9l-6.6 5.1C9.6 39.7 16.3 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.5l6.5 5.5C41.4 36 44 30.5 44 24c0-1.3-.1-2.7-.4-3.5z"/></svg>
        Masuk dengan Google
      </button>
    </form>
  </div>

  <div class="auth-footer">Belum punya akun? <a href="<?= BASE_URL ?>/public/register.php">Daftar Akun</a></div>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/public/assets/js/login.js"></script>
</body>
</html>
