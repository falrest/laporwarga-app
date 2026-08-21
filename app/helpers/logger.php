<?php
/**
 * Mencatat aktivitas penting ke tabel activity_logs (section 35).
 */
function log_activity(?int $userId, string $action, ?string $tableName = null, ?int $recordId = null): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (Throwable $e) {
        // Logging tidak boleh menggagalkan proses utama.
        error_log('Activity log error: ' . $e->getMessage());
    }
}
