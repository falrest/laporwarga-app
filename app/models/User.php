<?php
/**
 * Model User: query terkait tabel users & roles.
 * Semua query menggunakan PDO prepared statement.
 */

class User
{
    public static function findByEmailOrPhone(string $identifier): ?array
    {
        $stmt = db()->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE (u.email = ? OR u.no_hp = ?) AND u.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([$identifier, $identifier]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function emailExists(string $email): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function nikExists(string $nik): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE nik = ?');
        $stmt->execute([$nik]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function phoneExists(string $phone): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE no_hp = ?');
        $stmt->execute([$phone]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO users (role_id, nama, nik, email, no_hp, password, alamat, kecamatan_id, desa_id)
             VALUES (:role_id, :nama, :nik, :email, :no_hp, :password, :alamat, :kecamatan_id, :desa_id)'
        );
        $stmt->execute([
            ':role_id'      => 3, // default: masyarakat
            ':nama'         => $data['nama'],
            ':nik'          => $data['nik'],
            ':email'        => $data['email'] ?: null,
            ':no_hp'        => $data['no_hp'],
            ':password'     => password_hash($data['password'], PASSWORD_DEFAULT),
            ':alamat'       => $data['alamat'],
            ':kecamatan_id' => $data['kecamatan_id'],
            ':desa_id'      => $data['desa_id'],
        ]);
        return (int) db()->lastInsertId();
    }

    /** Rate limiting sederhana: max N percobaan dalam LOGIN_LOCKOUT_MINUTES (section 26). */
    public static function isLocked(array $user): bool
    {
        return !empty($user['locked_until']) && strtotime($user['locked_until']) > time();
    }

    public static function registerFailedAttempt(int $userId): void
    {
        $stmt = db()->prepare(
            'UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?'
        );
        $stmt->execute([$userId]);

        $stmt = db()->prepare('SELECT failed_login_attempts FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $attempts = (int) $stmt->fetchColumn();

        if ($attempts >= LOGIN_MAX_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_MINUTES * 60);
            $stmt = db()->prepare('UPDATE users SET locked_until = ? WHERE id = ?');
            $stmt->execute([$lockUntil, $userId]);
        }
    }

    public static function resetFailedAttempts(int $userId): void
    {
        $stmt = db()->prepare(
            'UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?'
        );
        $stmt->execute([$userId]);
    }

    // =====================================================================
    // ADMIN CRUD
    // =====================================================================

    /** Petugas aktif, untuk dropdown disposisi (section: Aksi Petugas). */
    public static function officerList(): array
    {
        $stmt = db()->query(
            "SELECT id, nama FROM users WHERE role_id = 2 AND status = 'aktif' AND deleted_at IS NULL ORDER BY nama ASC"
        );
        return $stmt->fetchAll();
    }

    /** Daftar user (masyarakat/petugas) dengan search & pagination. */
    public static function adminPaginated(string $roleName, string $search, int $page, int $pageSize): array
    {
        $offset = max(0, ($page - 1) * $pageSize);
        $where = "r.name = :role AND u.deleted_at IS NULL";
        $params = [':role' => $roleName];

        if ($search !== '') {
            $where .= ' AND (u.nama LIKE :search OR u.email LIKE :search OR u.no_hp LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $countStmt = db()->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT u.id, u.nama, u.email, u.no_hp, u.status, u.created_at, d.name AS kecamatan_name
                FROM users u
                JOIN roles r ON r.id = u.role_id
                LEFT JOIN districts d ON d.id = u.kecamatan_id
                WHERE $where
                ORDER BY u.created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page,
            'total_pages' => (int) ceil($total / $pageSize),
        ];
    }

    public static function adminCreate(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO users (role_id, nama, nik, email, no_hp, password, alamat, kecamatan_id, desa_id, status)
             VALUES (:role_id, :nama, :nik, :email, :no_hp, :password, :alamat, :kecamatan_id, :desa_id, :status)'
        );
        $stmt->execute([
            ':role_id'      => $data['role_id'],
            ':nama'         => $data['nama'],
            ':nik'          => $data['nik'] ?: null,
            ':email'        => $data['email'] ?: null,
            ':no_hp'        => $data['no_hp'],
            ':password'     => password_hash($data['password'], PASSWORD_DEFAULT),
            ':alamat'       => $data['alamat'] ?: null,
            ':kecamatan_id' => $data['kecamatan_id'] ?: null,
            ':desa_id'      => $data['desa_id'] ?: null,
            ':status'       => $data['status'] ?? 'aktif',
        ]);
        return (int) db()->lastInsertId();
    }

    public static function adminUpdate(int $id, array $data): void
    {
        $fields = ['nama = :nama', 'email = :email', 'no_hp = :no_hp', 'alamat = :alamat',
                   'kecamatan_id = :kecamatan_id', 'desa_id = :desa_id', 'status = :status'];
        $params = [
            ':id' => $id, ':nama' => $data['nama'], ':email' => $data['email'] ?: null,
            ':no_hp' => $data['no_hp'], ':alamat' => $data['alamat'] ?: null,
            ':kecamatan_id' => $data['kecamatan_id'] ?: null, ':desa_id' => $data['desa_id'] ?: null,
            ':status' => $data['status'] ?? 'aktif',
        ];
        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        db()->prepare($sql)->execute($params);
    }

    /** Soft delete - jangan hard delete karena user bisa punya riwayat laporan (FK RESTRICT). */
    public static function adminSoftDelete(int $id): void
    {
        db()->prepare('UPDATE users SET deleted_at = NOW(), status = "nonaktif" WHERE id = ?')->execute([$id]);
    }
}
