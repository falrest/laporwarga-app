<?php
require_once __DIR__ . '/../../app/helpers/bootstrap.php';
require_once __DIR__ . '/../../app/models/Report.php';
require_once __DIR__ . '/../../app/services/UploadService.php';

$user = require_role_api(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metode tidak diizinkan.', 405);
}

csrf_require();

$reportId   = (int) ($_POST['report_id'] ?? 0);
$status     = s($_POST['status'] ?? '');
$assignedTo = (int) ($_POST['assigned_to'] ?? 0);
$note       = s($_POST['note'] ?? '');

$validStatuses = ['diajukan', 'diverifikasi', 'diteruskan', 'diproses', 'selesai', 'ditolak'];

$report = Report::findDetailForAdmin($reportId);
if (!$report) {
    json_error('Laporan tidak ditemukan.', 404);
}

// Petugas hanya boleh mengubah laporan yang ditugaskan padanya (RBAC di endpoint, bukan cuma UI).
if ($user['role'] === 'petugas' && (int) $report['assigned_to'] !== (int) $user['id']) {
    json_error('Anda tidak memiliki akses untuk mengubah laporan ini.', 403);
}

$errors = [];
if (!in_array($status, $validStatuses, true)) {
    $errors['status'] = 'Status tidak valid.';
}
if ($status === 'ditolak' && $note === '') {
    $errors['note'] = 'Alasan penolakan wajib diisi.';
}
if (!empty($errors)) {
    json_error('Periksa kembali data yang dikirim.', 422, $errors);
}

$evidencePath = null;
if (!empty($_FILES['evidence']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
    try {
        $uploaded = UploadService::handle($_FILES['evidence'], 'evidence');
        $evidencePath = $uploaded['path'];
    } catch (RuntimeException $e) {
        json_error($e->getMessage(), 422, ['evidence' => $e->getMessage()]);
    }
}

try {
    Report::updateByStaff($reportId, [
        'status'        => $status,
        'assigned_to'   => $assignedTo ?: null,
        'note'          => $note ?: null,
        'evidence_path' => $evidencePath,
    ], $user['id']);

    log_activity($user['id'], 'update_report_status', 'reports', $reportId);

    json_success('Perubahan berhasil disimpan dan pelapor telah diberi tahu.');
} catch (Throwable $e) {
    error_log('Admin update report error: ' . $e->getMessage());
    json_error('Terjadi kesalahan pada sistem. Silakan coba lagi.', 500);
}
