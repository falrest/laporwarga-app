<?php
/**
 * Model ActivityLog: dipakai untuk "Aktivitas Petugas" di dashboard admin
 * dan "Log Aktivitas Terakhir" di detail laporan.
 */

class ActivityLog
{
    public static function recent(int $limit = 6): array
    {
        $stmt = db()->prepare(
            "SELECT a.*, u.nama AS user_nama
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function forRecord(string $tableName, int $recordId, int $limit = 10): array
    {
        $stmt = db()->prepare(
            "SELECT a.*, u.nama AS user_nama
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.table_name = ? AND a.record_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $tableName);
        $stmt->bindValue(2, $recordId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function actionLabel(string $action): string
    {
        $labels = [
            'login' => 'masuk ke sistem', 'logout' => 'keluar dari sistem',
            'register' => 'mendaftar akun', 'create_report' => 'membuat laporan baru',
            'update_report_status' => 'memperbarui status laporan', 'update_profile' => 'memperbarui profil',
            'admin_create_user' => 'menambahkan pengguna baru', 'admin_update_user' => 'memperbarui data pengguna',
            'admin_delete_user' => 'menonaktifkan pengguna', 'admin_create_category' => 'menambahkan kategori',
            'admin_update_category' => 'memperbarui kategori',
        ];
        return $labels[$action] ?? str_replace('_', ' ', $action);
    }
}
