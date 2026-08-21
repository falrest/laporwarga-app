<?php
/** @var string $active */
$items = [
    'beranda'    => ['label' => 'Beranda',    'href' => BASE_URL . '/masyarakat/dashboard.php',
        'icon' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>'],
    'buat'       => ['label' => 'Buat',       'href' => BASE_URL . '/masyarakat/pilih-kategori.php',
        'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>'],
    'laporan'    => ['label' => 'Laporan',    'href' => BASE_URL . '/masyarakat/laporan.php',
        'icon' => '<path d="M9 3h6l1 3h3v15H5V6h3l1-3Z"/><path d="M9 11h6M9 15h6"/>'],
    'notifikasi' => ['label' => 'Notifikasi', 'href' => BASE_URL . '/masyarakat/notifikasi.php',
        'icon' => '<path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z"/><path d="M10 20a2 2 0 0 0 4 0"/>'],
    'profil'     => ['label' => 'Profil',     'href' => BASE_URL . '/masyarakat/profil.php',
        'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>'],
];
?>
<nav class="bottom-nav">
  <?php foreach ($items as $key => $item): ?>
    <a href="<?= $item['href'] ?>" class="<?= $active === $key ? 'active' : '' ?>">
      <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $item['icon'] ?></svg>
      <?= out($item['label']) ?>
    </a>
  <?php endforeach; ?>
</nav>
