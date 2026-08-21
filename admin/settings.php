<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

require_role_page(['admin', 'petugas']);

$user = current_user();
$active = 'pengaturan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="admin-content" style="max-width:520px;">
      <h1 style="font-size:22px;margin:0 0 4px;">Pengaturan Akun</h1>
      <p class="text-secondary" style="margin:0 0 22px;font-size:14px;">Kelola keamanan akun Anda, <?= out($user['nama']) ?>.</p>

      <div class="admin-panel">
        <div class="admin-panel-title">Ubah Password</div>
        <form id="change-password-form" style="margin-top:16px;">
          <?= csrf_field() ?>
          <div class="field"><label>Password Saat Ini</label><input class="input" type="password" id="current_password" required></div>
          <div class="field"><label>Password Baru</label><input class="input" type="password" id="new_password" required></div>
          <div class="field"><label>Konfirmasi Password Baru</label><input class="input" type="password" id="confirm_password" required></div>
          <button type="submit" class="btn btn-primary" id="btn-change-password">Simpan Password Baru</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>
document.getElementById('change-password-form').addEventListener('submit', async function (e) {
  e.preventDefault();
  const btn = document.getElementById('btn-change-password');
  setButtonLoading(btn, true, 'Menyimpan...');
  try {
    const res = await fetch(<?= json_encode(BASE_URL) ?> + '/api/users/change-password.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        current_password: document.getElementById('current_password').value,
        new_password: document.getElementById('new_password').value,
        confirm_password: document.getElementById('confirm_password').value,
        csrf_token: document.querySelector('input[name=csrf_token]').value,
      }),
    });
    const data = await res.json();
    if (data.success) { showToast(data.message, 'success'); e.target.reset(); }
    else showToast(data.message || 'Gagal mengubah password.', 'error');
  } catch (err) {
    showToast('Tidak dapat terhubung ke server.', 'error');
  } finally {
    setButtonLoading(btn, false);
  }
});
</script>
</body>
</html>
