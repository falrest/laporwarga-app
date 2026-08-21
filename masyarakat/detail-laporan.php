<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Report.php';

require_role_page(['masyarakat']);

$user = current_user();
$reportId = (int) ($_GET['id'] ?? 0);
$report = Report::findDetailForUser($reportId, $user['id']);

if (!$report) {
    http_response_code(404);
    echo 'Laporan tidak ditemukan.';
    exit;
}

$files = Report::files($reportId);
$history = Report::statusHistory($reportId);
$updates = Report::updates($reportId);

$photos = array_filter($files, fn($f) => str_starts_with($f['file_type'], 'image/'));
$mainPhoto = $photos[array_key_first($photos)] ?? null;

// Timeline lengkap sesuai section 13 (5 tahap tetap, meski belum tercapai).
$fullTimeline = [
    'diajukan'     => 'Laporan Dikirim',
    'diverifikasi' => 'Sedang Diverifikasi',
    'diteruskan'   => 'Diteruskan ke Instansi',
    'diproses'     => 'Sedang Diproses',
    'selesai'      => 'Selesai',
];
$historyByStatus = [];
foreach ($history as $h) {
    $historyByStatus[$h['status']] = $h;
}
$statusOrder = array_keys($fullTimeline);
$currentIndex = array_search($report['status'], $statusOrder, true);
if ($currentIndex === false) $currentIndex = -1; // ditolak

$pageTitle = 'Detail Laporan';
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
    <a href="javascript:history.back()" style="display:flex;align-items:center;gap:10px;color:var(--text);font-weight:700;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
      Detail Laporan
    </a>
  </header>

  <div style="position:relative;height:220px;background:#DDE6F2;">
    <?php if ($mainPhoto): ?>
      <img src="<?= out(BASE_URL . '/' . ltrim($mainPhoto['file_path'], '/')) ?>" alt=""
           style="width:100%;height:100%;object-fit:cover;">
    <?php endif; ?>
    <span class="badge badge-<?= out($report['status']) ?>" style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,.92);">
      <?= out(status_label($report['status'])) ?>
    </span>
  </div>

  <div style="padding:20px;">
    <div class="card" style="margin-top:-40px;position:relative;z-index:2;margin-bottom:22px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <span class="text-secondary" style="font-size:13px;font-weight:600;">#<?= out($report['ticket_number']) ?></span>
        <span class="badge" style="background:var(--light-blue);color:var(--primary);"><?= out($report['category_name']) ?></span>
      </div>
      <h1 style="font-size:17px;margin:0 0 10px;"><?= out($report['title']) ?></h1>
      <p class="text-secondary" style="font-size:13.5px;margin:0 0 4px;display:flex;gap:6px;align-items:flex-start;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        <?= out($report['address']) ?>
      </p>
      <p class="text-secondary" style="font-size:13.5px;margin:0;display:flex;gap:6px;align-items:center;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <?= date('d M Y, H:i', strtotime($report['created_at'])) ?> WIB
      </p>
    </div>

    <h2 style="font-size:16px;margin:0 0 10px;">Deskripsi</h2>
    <p style="font-size:14.5px;line-height:1.6;color:var(--text);margin:0 0 26px;"><?= nl2br(out($report['description'])) ?></p>

    <h2 style="font-size:16px;margin:0 0 12px;">Status Laporan</h2>
    <div class="card" style="margin-bottom:26px;">
      <?php if ($report['status'] === 'ditolak'): ?>
        <div style="display:flex;gap:12px;">
          <span style="width:26px;height:26px;border-radius:50%;background:#FDECEC;color:var(--danger);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </span>
          <div>
            <strong style="font-size:14px;">Laporan Ditolak</strong>
            <p class="text-secondary" style="font-size:13px;margin:4px 0 0;"><?= out($report['rejection_reason'] ?: 'Tidak ada alasan yang dicantumkan.') ?></p>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($fullTimeline as $statusKey => $label): ?>
          <?php
            $idx = array_search($statusKey, $statusOrder, true);
            $done = $idx <= $currentIndex;
            $isLast = $statusKey === array_key_last($fullTimeline);
            $entry = $historyByStatus[$statusKey] ?? null;
          ?>
          <div style="display:flex;gap:12px;<?= $isLast ? '' : 'padding-bottom:18px;position:relative;' ?>">
            <?php if (!$isLast): ?>
              <span style="position:absolute;left:12px;top:26px;bottom:0;width:2px;background:<?= $done ? 'var(--primary)' : 'var(--border)' ?>;"></span>
            <?php endif; ?>
            <span style="width:26px;height:26px;border-radius:50%;flex-shrink:0;z-index:1;display:flex;align-items:center;justify-content:center;
                         background:<?= $done ? 'var(--primary)' : 'var(--light-blue)' ?>;color:<?= $done ? '#fff' : 'var(--text-secondary)' ?>;">
              <?php if ($done): ?>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m20 6-11 11-5-5"/></svg>
              <?php else: ?>
                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;"></span>
              <?php endif; ?>
            </span>
            <div>
              <strong style="font-size:14px;color:<?= $done ? 'var(--text)' : 'var(--text-secondary)' ?>;"><?= out($label) ?></strong>
              <p class="text-secondary" style="font-size:12.5px;margin:2px 0 0;">
                <?= $entry ? date('d M Y, H:i', strtotime($entry['created_at'])) : ($idx === $currentIndex + 1 ? 'Menunggu' : '') ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if (!empty($updates)): ?>
      <h2 style="font-size:16px;margin:0 0 12px;">Update Petugas</h2>
      <?php foreach ($updates as $u): ?>
        <div class="card" style="margin-bottom:14px;">
          <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;">
            <span style="width:34px;height:34px;border-radius:50%;background:var(--light-blue);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
            </span>
            <div>
              <strong style="font-size:13.5px;display:block;"><?= out($u['officer_name']) ?></strong>
              <span class="text-secondary" style="font-size:12px;"><?= date('d M Y, H:i', strtotime($u['created_at'])) ?></span>
            </div>
          </div>
          <p style="font-size:13.5px;margin:0;background:var(--light-blue);padding:12px 14px;border-radius:12px;line-height:1.5;">
            <?= nl2br(out($u['message'])) ?>
          </p>
          <?php if (!empty($u['photo'])): ?>
            <img src="<?= out(BASE_URL . '/' . ltrim($u['photo'], '/')) ?>" alt="Bukti penanganan"
                 style="width:100%;border-radius:12px;margin-top:10px;">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
