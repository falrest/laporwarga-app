<?php
/** @var string $pageTitle */
/** @var array $user */
?>
<header class="app-header">
  <div class="brand">
    <img src="<?= BASE_URL ?>/public/assets/images/logo.svg" alt="">
    <?= out(APP_NAME) ?>
  </div>
  <div style="display:flex;align-items:center;gap:10px;">
    <span class="page-title"><?= out($pageTitle) ?></span>
    <a href="<?= BASE_URL ?>/masyarakat/profil.php">
      <img class="avatar" src="<?= out($user['foto'] ?: BASE_URL . '/public/assets/images/avatar-placeholder.png') ?>" alt="Profil">
    </a>
  </div>
</header>
