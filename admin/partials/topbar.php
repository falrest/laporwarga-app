<?php
/** @var array $user */
$unread = class_exists('Notification') ? Notification::unreadCount($user['id']) : 0;
$roleLabel = $user['role'] === 'admin' ? 'Super Admin' : 'Petugas';
?>
<header class="admin-topbar">
  <div class="input-wrap" style="max-width:360px;flex:1;">
    <span class="icon">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
    </span>
    <input class="input" type="text" placeholder="Cari laporan atau data..." id="admin-global-search">
  </div>

  <div style="display:flex;align-items:center;gap:20px;">
    <a href="<?= BASE_URL ?>/admin/reports.php" style="position:relative;color:var(--text-secondary);">
      <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
      <?php if ($unread > 0): ?>
        <span style="position:absolute;top:-4px;right:-4px;width:16px;height:16px;border-radius:50%;background:var(--danger);color:#fff;font-size:9.5px;display:flex;align-items:center;justify-content:center;font-weight:700;"><?= min(9, $unread) ?><?= $unread > 9 ? '+' : '' ?></span>
      <?php endif; ?>
    </a>
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="text-align:right;line-height:1.3;">
        <div style="font-weight:700;font-size:13.5px;"><?= out($user['nama']) ?></div>
        <div class="text-secondary" style="font-size:11.5px;"><?= out($roleLabel) ?></div>
      </div>
      <img src="<?= out($user['foto'] ?: BASE_URL . '/public/assets/images/avatar-placeholder.png') ?>" alt=""
           style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--light-blue);">
    </div>
  </div>
</header>
