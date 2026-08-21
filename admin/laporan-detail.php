<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/helpers/icons.php';
require_once __DIR__ . '/../app/models/Report.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/ActivityLog.php';
require_once __DIR__ . '/../app/models/Notification.php';

require_role_page(['admin', 'petugas']);

$user = current_user();
$reportId = (int) ($_GET['id'] ?? 0);
$report = Report::findDetailForAdmin($reportId);

if (!$report) {
    http_response_code(404);
    echo 'Laporan tidak ditemukan.';
    exit;
}

// Petugas hanya boleh melihat laporan miliknya sendiri (RBAC nyata, bukan cuma UI).
if ($user['role'] === 'petugas' && (int) $report['assigned_to'] !== (int) $user['id']) {
    http_response_code(403);
    echo 'Anda tidak memiliki akses ke laporan ini.';
    exit;
}

$files = Report::files($reportId);
$officers = User::officerList();
$activityLog = ActivityLog::forRecord('reports', $reportId, 10);

$statusOptions = [
    'diajukan'     => 'Diajukan (Menunggu Verifikasi)',
    'diverifikasi' => 'Diverifikasi (Siap Diproses)',
    'diteruskan'   => 'Diteruskan ke Instansi',
    'diproses'     => 'Sedang Diproses',
    'selesai'      => 'Selesai',
    'ditolak'      => 'Ditolak',
];

$active = 'masuk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan #<?= out($report['ticket_number']) ?> — <?= out(APP_NAME) ?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="admin-content" style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
      <!-- Kolom kiri -->
      <div>
        <div class="admin-panel" style="margin-bottom:20px;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div>
              <div class="text-secondary" style="font-size:12.5px;font-weight:600;letter-spacing:.03em;">LAPORAN #<?= out($report['ticket_number']) ?></div>
              <h1 style="font-size:21px;margin:6px 0 0;"><?= out($report['title']) ?></h1>
            </div>
            <span class="badge badge-<?= out($report['status']) ?>" style="flex-shrink:0;"><?= out(status_label($report['status'])) ?></span>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:22px 0;">
            <div>
              <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">👤 PELAPOR</div>
              <div style="font-weight:600;font-size:14px;"><?= out($report['pelapor_nama']) ?></div>
            </div>
            <div>
              <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">🪪 NIK</div>
              <div style="font-weight:600;font-size:14px;"><?= out(mask_nik($report['pelapor_nik'])) ?></div>
            </div>
            <div>
              <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">📞 TELEPON</div>
              <div style="font-weight:600;font-size:14px;"><?= out(mask_phone($report['pelapor_hp'])) ?></div>
            </div>
            <div>
              <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">📅 TANGGAL LAPORAN</div>
              <div style="font-weight:600;font-size:14px;"><?= date('d M Y, H:i', strtotime($report['created_at'])) ?> WIB</div>
            </div>
            <div>
              <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">🏷️ KATEGORI UTAMA</div>
              <div style="font-weight:600;font-size:14px;"><?= out($report['category_name']) ?></div>
            </div>
            <div>
              <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:4px;">↳ SUB-KATEGORI</div>
              <div style="font-weight:600;font-size:14px;"><?= out($report['subcategory_name']) ?></div>
            </div>
          </div>

          <div class="text-secondary" style="font-size:11.5px;font-weight:600;margin-bottom:8px;">📄 DESKRIPSI LENGKAP</div>
          <div style="background:var(--light-blue);border-radius:12px;padding:16px;font-size:14px;line-height:1.6;color:var(--text);">
            <?= nl2br(out($report['description'])) ?>
          </div>
        </div>

        <!-- Aksi Petugas -->
        <div class="admin-panel">
          <div class="admin-panel-title" style="display:flex;align-items:center;gap:8px;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/><path d="m17 11 2 2 4-4"/></svg>
            Aksi Petugas
          </div>

          <form id="staff-action-form" style="margin-top:16px;">
            <?= csrf_field() ?>
            <input type="hidden" name="report_id" value="<?= $reportId ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div class="field">
                <label for="status">Update Status Laporan</label>
                <select class="input" id="status" name="status" required>
                  <?php foreach ($statusOptions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $report['status'] === $key ? 'selected' : '' ?>><?= out($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label for="assigned_to">Disposisi Kepada (Petugas)</label>
                <select class="input" id="assigned_to" name="assigned_to">
                  <option value="">Belum ditugaskan</option>
                  <?php foreach ($officers as $o): ?>
                    <option value="<?= (int) $o['id'] ?>" <?= (int) $report['assigned_to'] === (int) $o['id'] ? 'selected' : '' ?>><?= out($o['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="field">
              <label for="note">Tanggapan / Catatan Penanganan</label>
              <textarea class="input" id="note" name="note" rows="3" placeholder="Tuliskan tanggapan resmi yang akan dibaca oleh pelapor..."></textarea>
              <div class="field-hint">Kosongkan jika hanya mengubah status tanpa catatan. Wajib diisi jika status "Ditolak" (alasan penolakan).</div>
            </div>

            <div class="field">
              <label>Unggah Bukti Penanganan</label>
              <label for="evidence" style="cursor:pointer;display:block;">
                <div style="border:1.5px dashed var(--border);border-radius:14px;background:var(--bg);padding:26px 16px;text-align:center;">
                  <div style="width:38px;height:38px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 16V4M6 10l6-6 6 6"/><path d="M4 20h16"/></svg>
                  </div>
                  <div class="text-secondary" style="font-size:13px;">Klik untuk memilih foto hasil perbaikan (Maks 10MB)</div>
                  <div id="evidence-filename" style="font-size:12.5px;color:var(--primary);font-weight:600;margin-top:6px;"></div>
                </div>
              </label>
              <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png" style="display:none;">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
              <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline">Batal</a>
              <button type="submit" class="btn btn-primary" id="btn-save-action">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Simpan Perubahan &amp; Update Pelapor
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Kolom kanan -->
      <div>
        <div class="admin-panel" style="margin-bottom:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div class="admin-panel-title" style="font-size:14.5px;">🖼️ Media Lampiran</div>
            <span class="badge" style="background:var(--light-blue);color:var(--primary);"><?= count($files) ?> File</span>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px;">
            <?php if (empty($files)): ?>
              <p class="text-secondary" style="font-size:13px;">Tidak ada lampiran.</p>
            <?php endif; ?>
            <?php foreach ($files as $f): ?>
              <?php if (str_starts_with($f['file_type'], 'image/')): ?>
                <a href="<?= out(BASE_URL . '/' . ltrim($f['file_path'], '/')) ?>" target="_blank">
                  <img src="<?= out(BASE_URL . '/' . ltrim($f['file_path'], '/')) ?>" alt="" style="width:100%;border-radius:12px;object-fit:cover;max-height:180px;">
                </a>
              <?php else: ?>
                <a href="<?= out(BASE_URL . '/' . ltrim($f['file_path'], '/')) ?>" target="_blank" style="display:flex;align-items:center;gap:8px;background:var(--light-blue);border-radius:10px;padding:10px;font-size:13px;color:var(--primary);font-weight:600;">
                  🎬 Video bukti laporan
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="admin-panel" style="margin-bottom:20px;">
          <div class="admin-panel-title" style="font-size:14.5px;">📍 Lokasi Kejadian</div>
          <div id="detail-map" style="height:200px;border-radius:12px;margin-top:12px;overflow:hidden;"></div>
          <div class="text-secondary" style="font-size:11.5px;margin-top:8px;font-family:monospace;">
            <?= (float) $report['latitude'] ?>, <?= (float) $report['longitude'] ?>
          </div>
          <div style="font-size:12.5px;font-weight:600;margin-top:10px;">Alamat Detail:</div>
          <p style="font-size:12.5px;color:var(--text-secondary);margin:4px 0 0;line-height:1.5;"><?= out($report['address']) ?></p>
        </div>

        <div class="admin-panel" style="background:var(--light-blue);border:none;">
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <strong style="font-size:13px;color:var(--primary-dark);">Log Aktivitas Terakhir</strong>
          </div>
          <?php if (empty($activityLog)): ?>
            <p style="font-size:12.5px;margin:0;color:var(--primary-dark);">Belum ada aktivitas tercatat untuk laporan ini.</p>
          <?php endif; ?>
          <?php foreach ($activityLog as $log): ?>
            <p style="font-size:12.5px;margin:0 0 8px;color:var(--primary-dark);line-height:1.5;">
              Laporan <?= out(ActivityLog::actionLabel($log['action'])) ?> oleh <?= out($log['user_nama'] ?? 'Sistem') ?>
              pada <?= date('d M Y, H:i', strtotime($log['created_at'])) ?> WIB.
            </p>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
<script>
  window.BASE_URL = <?= json_encode(BASE_URL) ?>;
  window.REPORT_LOCATION = { lat: <?= (float) $report['latitude'] ?>, lng: <?= (float) $report['longitude'] ?> };
</script>
<script src="<?= BASE_URL ?>/public/assets/js/admin-laporan-detail.js"></script>
</body>
</html>
