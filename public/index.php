<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

// Redirect langsung (section 5): splash tetap tampil sebentar via JS,
// lalu redirect ke tujuan yang sudah ditentukan server-side berikut ini.
if (is_logged_in()) {
    $role = current_user()['role'];
    $target = match ($role) {
        'admin'   => BASE_URL . '/admin/index.php',
        'petugas' => BASE_URL . '/petugas/dashboard.php',
        default   => BASE_URL . '/masyarakat/dashboard.php',
    };
} else {
    $target = BASE_URL . '/public/login.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= out(APP_NAME) ?> — Memuat...</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/auth.css">
</head>
<body>
  <div class="splash-shell">
    <div class="splash-logo">
      <img src="<?= BASE_URL ?>/public/assets/images/logo.svg" alt="LaporWarga">
    </div>
    <h1><?= out(APP_NAME) ?></h1>
    <p><?= out(APP_TAGLINE) ?></p>
    <div class="splash-dots"><span></span><span></span><span></span></div>
    <div class="splash-loading-text">MEMUAT...</div>
  </div>
  <script>
    // Delay singkat agar splash sempat terlihat, lalu redirect (section 5).
    setTimeout(function () {
      window.location.replace(<?= json_encode($target) ?>);
    }, 900);
  </script>
</body>
</html>
