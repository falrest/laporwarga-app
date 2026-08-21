<?php
/** @var string $active */
/** @var array $user */
$isAdmin = ($user['role'] ?? '') === 'admin';
$dashboardHref = $isAdmin ? BASE_URL . '/admin/index.php' : BASE_URL . '/petugas/dashboard.php';
$reportsBaseHref = $isAdmin ? BASE_URL . '/admin/reports.php' : BASE_URL . '/petugas/laporan.php';

$menuGroups = [
    '' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => $dashboardHref,
         'icon' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>'],
    ],
    'MANAGEMENT PENGADUAN' => [
        ['key' => 'masuk',    'label' => 'Pengaduan Masuk',    'href' => $reportsBaseHref . '?status=diajukan',
         'icon' => '<path d="M9 3h6l1 3h3v15H5V6h3l1-3Z"/><path d="M9 11h6M9 15h6"/>'],
        ['key' => 'diproses', 'label' => 'Pengaduan Diproses', 'href' => $reportsBaseHref . '?status=dalam_penanganan',
         'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>'],
        ['key' => 'selesai',  'label' => 'Pengaduan Selesai',  'href' => $reportsBaseHref . '?status=selesai',
         'icon' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-5"/>'],
        ['key' => 'ditolak',  'label' => 'Pengaduan Ditolak',  'href' => $reportsBaseHref . '?status=ditolak',
         'icon' => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>'],
    ],
];

if ($isAdmin) {
    $menuGroups['DATA & REFERENSI'] = [
        ['key' => 'masyarakat', 'label' => 'Data Masyarakat',   'href' => BASE_URL . '/admin/users.php',
         'icon' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.8 3.1-6 7-6s7 2.2 7 6"/><path d="M17 8a3 3 0 1 1 0 6"/><path d="M17.5 14c2.5.3 4.5 2 4.5 5.5"/>'],
        ['key' => 'petugas',    'label' => 'Instansi/Petugas',  'href' => BASE_URL . '/admin/officers.php',
         'icon' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/>'],
        ['key' => 'kategori',   'label' => 'Kategori Pengaduan', 'href' => BASE_URL . '/admin/categories.php',
         'icon' => '<rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/>'],
    ];
    $menuGroups['SISTEM'] = [
        ['key' => 'statistik',  'label' => 'Statistik',  'href' => BASE_URL . '/admin/statistics.php',
         'icon' => '<path d="M3 3v18h18"/><path d="M7 15l4-6 4 3 5-7"/>'],
        ['key' => 'pengaturan', 'label' => 'Pengaturan', 'href' => BASE_URL . '/admin/settings.php',
         'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>'],
    ];
} else {
    $menuGroups['SISTEM'] = [
        ['key' => 'pengaturan', 'label' => 'Pengaturan', 'href' => BASE_URL . '/admin/settings.php',
         'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>'],
    ];
}
?>
<aside class="admin-sidebar">
  <div class="admin-brand">
    <span class="admin-brand-logo">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0053A6" stroke-width="2.4"><path d="M4 20V10l8-6 8 6v10"/></svg>
    </span>
    <?= out(APP_NAME) ?>
  </div>

  <nav class="admin-nav">
    <?php foreach ($menuGroups as $groupLabel => $items): ?>
      <?php if ($groupLabel): ?><div class="admin-nav-group-label"><?= out($groupLabel) ?></div><?php endif; ?>
      <?php foreach ($items as $item): ?>
        <a href="<?= $item['href'] ?>" class="admin-nav-item <?= $active === $item['key'] ? 'active' : '' ?>">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $item['icon'] ?></svg>
          <?= out($item['label']) ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
</aside>
