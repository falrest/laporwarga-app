<?php
/**
 * Model Notification (section 16).
 */

class Notification
{
    public static function listByUser(int $userId, int $limit = 50): array
    {
        $stmt = db()->prepare(
            'SELECT n.*, r.ticket_number
             FROM notifications n
             LEFT JOIN reports r ON r.id = n.report_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markRead(int $id, int $userId): bool
    {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public static function markAllRead(int $userId): bool
    {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
        return $stmt->execute([$userId]);
    }

    /** Format waktu relatif sederhana: "1 jam lalu", "Kemarin", dst. */
    public static function relativeTime(string $datetime): string
    {
        $diff = time() - strtotime($datetime);

        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 172800) return 'Kemarin';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';

        return date('d M Y', strtotime($datetime));
    }
}
