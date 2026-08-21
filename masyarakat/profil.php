<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/sanitize.php';
require_once __DIR__ . '/../app/models/User.php';

require_role_page(['masyarakat']);

$sessionUser = current_user();
$user = User::findById($sessionUser['id']);

if (!$user) {
    logout_user();
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

$pageTitle = 'Profil';
$active = 'profil';
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
<div class="app-shell">
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main style="padding:24px 20px;">
    <div style="text-align:center;margin-bottom:22px;">
      <img src="<?= out($fotoUrl) ?>" alt="" style="width:88px;height:88px;border-radius:50%;object-fit:cover;box-shadow:var(--shadow-md);margin-bottom:12px;">
      <h1 style="font-size:19px;margin:0 0 2px;"><?= out($user['nama']) ?></h1>
      <p class="text-secondary" style="font-size:14px;margin:0;"><?= out($user['email'] ?: $user['no_hp']) ?></p>
    </div>

    <div style="display:flex;gap:12px;margin-bottom:24px;">
      <div class="card" style="flex:1;text-align:center;padding:14px 8px;">
        <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">NIK</div>
        <div style="font-size:13.5px;font-weight:700;letter-spacing:.3px;"><?= out(mask_nik($user['nik'])) ?></div>
      </div>
      <div class="card" style="flex:1;text-align:center;padding:14px 8px;">
        <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">Telepon</div>
        <div style="font-size:13.5px;font-weight:700;letter-spacing:.3px;"><?= out(mask_phone($user['no_hp'])) ?></div>
      </div>
    </div>

    <div class="text-secondary" style="font-size:12px;font-weight:700;letter-spacing:.05em;margin:0 0 10px;">AKUN SAYA</div>
    <div class="card" style="padding:4px 18px;margin-bottom:22px;">
      <?php
        $accountMenu = [
            ['icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>', 'label' => 'Edit Profil', 'href' => BASE_URL . '/masyarakat/edit-profil.php'],
            ['icon' => '<path d="M3 12a9 9 0 1 0 9-9"/><path d="M3 4v8h8"/>', 'label' => 'Riwayat Pengaduan', 'href' => BASE_URL . '/masyarakat/laporan.php'],
            ['icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>', 'label' => 'Pengaturan', 'href' => '#'],
        ];
      ?>
      <?php foreach ($accountMenu as $i => $item): ?>
        <a href="<?= $item['href'] ?>" style="display:flex;align-items:center;gap:14px;padding:15px 0;color:var(--text);
                  <?= $i < count($accountMenu) - 1 ? 'border-bottom:1px solid var(--border);' : '' ?>">
          <span style="width:34px;height:34px;border-radius:10px;background:var(--light-blue);color:var(--primary);display:flex;align-items:center;justify-content:center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $item['icon'] ?></svg>
          </span>
          <span style="flex:1;font-size:14.5px;font-weight:500;"><?= out($item['label']) ?></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="text-secondary" style="font-size:12px;font-weight:700;letter-spacing:.05em;margin:0 0 10px;">INFORMASI UMUM</div>
    <div class="card" style="padding:4px 18px;margin-bottom:24px;">
      <?php
        $infoMenu = [
            ['icon' => '<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 2-3 4"/><path d="M12 17h.01"/>', 'label' => 'Bantuan'],
            ['icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>', 'label' => 'Kebijakan Privasi'],
            ['icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>', 'label' => 'Tentang Aplikasi'],
        ];
      ?>
      <?php foreach ($infoMenu as $i => $item): ?>
        <a href="#" style="display:flex;align-items:center;gap:14px;padding:15px 0;color:var(--text);
                  <?= $i < count($infoMenu) - 1 ? 'border-bottom:1px solid var(--border);' : '' ?>">
          <span style="width:34px;height:34px;border-radius:10px;background:var(--light-blue);color:var(--primary);display:flex;align-items:center;justify-content:center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $item['icon'] ?></svg>
          </span>
          <span style="flex:1;font-size:14.5px;font-weight:500;"><?= out($item['label']) ?></span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      <?php endforeach; ?>
    </div>

    <button id="btn-logout" class="btn btn-danger-ghost btn-block" style="margin-bottom:14px;">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      Keluar
    </button>
    <p class="text-secondary" style="text-align:center;font-size:12px;margin:0;">Versi 1.0.0</p>
  </main>

  <?php include __DIR__ . '/partials/bottom-nav.php'; ?>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>
document.getElementById('btn-logout').addEventListener('click', async function () {
  if (!confirm('Yakin ingin keluar dari akun Anda?')) return;
  try {
    const res = await fetch(<?= json_encode(BASE_URL) ?> + '/api/auth/logout.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf_token: <?= json_encode(csrf_token()) ?> }),
    });
    const data = await res.json();
    window.location.href = data.data?.redirect || <?= json_encode(BASE_URL . '/public/login.php') ?>;
  } catch (err) {
    showToast('Gagal keluar. Coba lagi.', 'error');
  }
});
</script>
</body>
</html>
