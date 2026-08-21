<?php
/**
 * Model Report: query read (dashboard, Laporan Saya, detail) dan create
 * (transaksi lengkap: laporan + file + status history + notifikasi).
 * Semua listing pakai pagination (section 28: jangan ambil semua data sekaligus).
 */

class Report
{
    /** Laporan terbaru milik user, untuk kartu di Dashboard. */
    public static function latestByUser(int $userId, int $limit = 3): array
    {
        $stmt = db()->prepare(
            "SELECT r.id, r.ticket_number, r.title, r.address, r.status, r.created_at,
                    (SELECT file_path FROM report_files f WHERE f.report_id = r.id ORDER BY f.id ASC LIMIT 1) AS photo
             FROM reports r
             WHERE r.user_id = ? AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Daftar laporan milik user dengan search, filter status, dan pagination
     * (section 14 & 28). $page dimulai dari 1.
     */
    public static function paginatedByUser(int $userId, string $search, string $statusFilter, int $page, int $pageSize): array
    {
        $offset = max(0, ($page - 1) * $pageSize);

        $where = 'r.user_id = :user_id AND r.deleted_at IS NULL';
        $params = [':user_id' => $userId];

        if ($search !== '') {
            $where .= ' AND (r.title LIKE :search OR r.ticket_number LIKE :search OR r.address LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($statusFilter !== '' && $statusFilter !== 'semua') {
            $statusMap = [
                'menunggu' => ['diajukan', 'diverifikasi', 'diteruskan'],
                'diproses' => ['diproses'],
                'selesai'  => ['selesai'],
                'ditolak'  => ['ditolak'],
            ];
            if (isset($statusMap[$statusFilter])) {
                $placeholders = [];
                foreach ($statusMap[$statusFilter] as $i => $st) {
                    $key = ":st$i";
                    $placeholders[] = $key;
                    $params[$key] = $st;
                }
                $where .= ' AND r.status IN (' . implode(',', $placeholders) . ')';
            }
        }

        $countStmt = db()->prepare("SELECT COUNT(*) FROM reports r WHERE $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT r.id, r.ticket_number, r.title, r.address, r.status, r.created_at, r.rejection_reason,
                       (SELECT file_path FROM report_files f WHERE f.report_id = r.id ORDER BY f.id ASC LIMIT 1) AS photo
                FROM reports r
                WHERE $where
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = db()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'       => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'page_size'   => $pageSize,
            'total_pages' => (int) ceil($total / $pageSize),
        ];
    }

    public static function findDetailForUser(int $reportId, int $userId): ?array
    {
        $stmt = db()->prepare(
            "SELECT r.*, c.nama AS category_name, sc.nama AS subcategory_name
             FROM reports r
             JOIN categories c ON c.id = r.category_id
             JOIN subcategories sc ON sc.id = r.subcategory_id
             WHERE r.id = ? AND r.user_id = ? AND r.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$reportId, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Membuat laporan baru beserta file, status history awal, dan notifikasi
     * dalam satu database transaction (section 12 & 43). Rollback otomatis
     * jika salah satu langkah gagal.
     *
     * @param array $data   detail laporan (user_id, category_id, subcategory_id, title, ...)
     * @param array $files  daftar hasil UploadService::handle()
     * @return array{id:int, ticket_number:string}
     */
    public static function create(array $data, array $files): array
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $ticket = generate_ticket_number($pdo);

            $stmt = $pdo->prepare(
                'INSERT INTO reports
                    (ticket_number, user_id, category_id, subcategory_id, title, description,
                     address, latitude, longitude, incident_date, status)
                 VALUES
                    (:ticket, :user_id, :category_id, :subcategory_id, :title, :description,
                     :address, :latitude, :longitude, :incident_date, :status)'
            );
            $stmt->execute([
                ':ticket'         => $ticket,
                ':user_id'        => $data['user_id'],
                ':category_id'    => $data['category_id'],
                ':subcategory_id' => $data['subcategory_id'],
                ':title'          => $data['title'],
                ':description'    => $data['description'],
                ':address'        => $data['address'],
                ':latitude'       => $data['latitude'],
                ':longitude'      => $data['longitude'],
                ':incident_date'  => $data['incident_date'],
                ':status'         => 'diajukan',
            ]);
            $reportId = (int) $pdo->lastInsertId();

            $fileStmt = $pdo->prepare(
                'INSERT INTO report_files (report_id, file_name, file_path, file_type, file_size, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($files as $f) {
                $fileStmt->execute([$reportId, $f['name'], $f['path'], $f['type'], $f['size'], $data['user_id']]);
            }

            $historyStmt = $pdo->prepare(
                'INSERT INTO report_status_history (report_id, status, description, changed_by)
                 VALUES (?, ?, ?, ?)'
            );
            $historyStmt->execute([$reportId, 'diajukan', 'Laporan dikirim oleh warga.', $data['user_id']]);

            $notifStmt = $pdo->prepare(
                'INSERT INTO notifications (user_id, report_id, title, message, type, is_read)
                 VALUES (?, ?, ?, ?, ?, 0)'
            );
            $notifStmt->execute([
                $data['user_id'],
                $reportId,
                'Laporan Berhasil Dikirim',
                "Laporan Anda dengan nomor tiket #$ticket telah berhasil dikirim dan menunggu verifikasi.",
                'status',
            ]);

            $pdo->commit();

            return ['id' => $reportId, 'ticket_number' => $ticket];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Perbandingan jumlah laporan bulan ini vs bulan lalu, per status (untuk badge naik/turun di KPI). */
    public static function monthOverMonthDelta(): array
    {
        $sql = "SELECT status,
                    SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS this_month,
                    SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
                              AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS last_month
                FROM reports WHERE deleted_at IS NULL GROUP BY status";
        $rows = db()->query($sql)->fetchAll();

        $delta = [];
        foreach ($rows as $r) {
            $this_m = (int) $r['this_month'];
            $last_m = (int) $r['last_month'];
            $pct = $last_m > 0 ? round((($this_m - $last_m) / $last_m) * 100) : ($this_m > 0 ? 100 : 0);
            $delta[$r['status']] = $pct;
        }
        return $delta;
    }

    public static function files(int $reportId): array
    {
        $stmt = db()->prepare('SELECT * FROM report_files WHERE report_id = ? ORDER BY id ASC');
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    // =====================================================================
    // ADMIN / PETUGAS
    // =====================================================================

    /** Hitung jumlah laporan per status, untuk kartu KPI dashboard admin. */
    public static function countsByStatus(): array
    {
        $stmt = db()->query(
            "SELECT status, COUNT(*) AS total FROM reports WHERE deleted_at IS NULL GROUP BY status"
        );
        $rows = $stmt->fetchAll();
        $counts = ['diajukan' => 0, 'diverifikasi' => 0, 'diteruskan' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];
        foreach ($rows as $r) {
            $counts[$r['status']] = (int) $r['total'];
        }
        $counts['total'] = array_sum($counts);
        // "Sedang diproses" gabungan tahap diverifikasi+diteruskan+diproses agar sama seperti tampilan referensi.
        $counts['dalam_penanganan'] = $counts['diverifikasi'] + $counts['diteruskan'] + $counts['diproses'];
        return $counts;
    }

    /** Tren jumlah laporan per bulan, N bulan terakhir. */
    public static function monthlyTrend(int $months = 6): array
    {
        $stmt = db()->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
             FROM reports
             WHERE deleted_at IS NULL AND created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY ym ORDER BY ym ASC"
        );
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Isi bulan yang kosong dengan 0 agar grafik tetap rapi berurutan.
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("-$i months"));
            $result[$ym] = 0;
        }
        foreach ($rows as $r) {
            $result[$r['ym']] = (int) $r['total'];
        }
        return $result;
    }

    /** Persentase laporan per kategori (top 5), untuk donut chart. */
    public static function categoryBreakdown(int $limit = 5): array
    {
        $stmt = db()->prepare(
            "SELECT c.nama, COUNT(r.id) AS total
             FROM categories c
             LEFT JOIN reports r ON r.category_id = c.id AND r.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY total DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Titik-titik laporan untuk peta persebaran (dibatasi agar tetap ringan). */
    public static function mapPoints(int $limit = 300): array
    {
        $stmt = db()->prepare(
            "SELECT id, title, status, latitude, longitude FROM reports
             WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function latestForAdmin(int $limit = 5): array
    {
        $stmt = db()->prepare(
            "SELECT r.id, r.ticket_number, r.title, r.status, r.created_at,
                    u.nama AS pelapor_nama, c.nama AS category_name
             FROM reports r
             JOIN users u ON u.id = r.user_id
             JOIN categories c ON c.id = r.category_id
             WHERE r.deleted_at IS NULL
             ORDER BY r.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Daftar laporan untuk admin/petugas dengan filter status, kategori,
     * pencarian, dan pagination (section 14, 28, 34).
     * $statusGroup: 'semua' | 'diajukan' | 'dalam_penanganan' | 'selesai' | 'ditolak'
     */
    public static function adminPaginated(string $statusGroup, string $search, int $categoryId, ?int $assignedTo, int $page, int $pageSize): array
    {
        $offset = max(0, ($page - 1) * $pageSize);
        $where = 'r.deleted_at IS NULL';
        $params = [];

        $groupMap = [
            'diajukan'         => ['diajukan'],
            'dalam_penanganan' => ['diverifikasi', 'diteruskan', 'diproses'],
            'selesai'          => ['selesai'],
            'ditolak'          => ['ditolak'],
        ];
        if (isset($groupMap[$statusGroup])) {
            $placeholders = [];
            foreach ($groupMap[$statusGroup] as $i => $st) {
                $key = ":gs$i";
                $placeholders[] = $key;
                $params[$key] = $st;
            }
            $where .= ' AND r.status IN (' . implode(',', $placeholders) . ')';
        }

        if ($search !== '') {
            $where .= ' AND (r.title LIKE :search OR r.ticket_number LIKE :search OR u.nama LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        if ($categoryId > 0) {
            $where .= ' AND r.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }
        if ($assignedTo !== null) {
            $where .= ' AND r.assigned_to = :assigned_to';
            $params[':assigned_to'] = $assignedTo;
        }

        $countSql = "SELECT COUNT(*) FROM reports r JOIN users u ON u.id = r.user_id WHERE $where";
        $countStmt = db()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT r.id, r.ticket_number, r.title, r.status, r.created_at,
                       u.nama AS pelapor_nama, c.nama AS category_name
                FROM reports r
                JOIN users u ON u.id = r.user_id
                JOIN categories c ON c.id = r.category_id
                WHERE $where
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items'       => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'total_pages' => (int) ceil($total / $pageSize),
        ];
    }

    public static function findDetailForAdmin(int $reportId): ?array
    {
        $stmt = db()->prepare(
            "SELECT r.*, c.nama AS category_name, sc.nama AS subcategory_name,
                    u.nama AS pelapor_nama, u.nik AS pelapor_nik, u.no_hp AS pelapor_hp,
                    a.nama AS assigned_nama
             FROM reports r
             JOIN categories c ON c.id = r.category_id
             JOIN subcategories sc ON sc.id = r.subcategory_id
             JOIN users u ON u.id = r.user_id
             LEFT JOIN users a ON a.id = r.assigned_to
             WHERE r.id = ? AND r.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$reportId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Update status laporan oleh admin/petugas: mengubah status utama,
     * menyimpan history, catatan/komentar, disposisi petugas, upload bukti,
     * dan notifikasi ke pelapor — semua dalam satu transaction (section 43).
     */
    public static function updateByStaff(int $reportId, array $data, int $staffId): void
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $fields = ['status = :status', 'updated_at = NOW()'];
            $params = [':status' => $data['status'], ':id' => $reportId];

            if (array_key_exists('assigned_to', $data) && $data['assigned_to']) {
                $fields[] = 'assigned_to = :assigned_to';
                $params[':assigned_to'] = $data['assigned_to'];
            }
            if ($data['status'] === 'ditolak' && !empty($data['note'])) {
                $fields[] = 'rejection_reason = :rejection_reason';
                $params[':rejection_reason'] = $data['note'];
            }

            $sql = 'UPDATE reports SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $pdo->prepare($sql)->execute($params);

            $pdo->prepare(
                'INSERT INTO report_status_history (report_id, status, description, changed_by) VALUES (?, ?, ?, ?)'
            )->execute([$reportId, $data['status'], $data['note'] ?: null, $staffId]);

            if (!empty($data['note'])) {
                $pdo->prepare(
                    'INSERT INTO report_updates (report_id, user_id, message, photo) VALUES (?, ?, ?, ?)'
                )->execute([$reportId, $staffId, $data['note'], $data['evidence_path'] ?? null]);
            }

            // Notifikasi ke pelapor
            $stmt = $pdo->prepare('SELECT user_id, ticket_number FROM reports WHERE id = ?');
            $stmt->execute([$reportId]);
            $report = $stmt->fetch();

            $notifText = [
                'diverifikasi' => 'telah diverifikasi oleh admin.',
                'diteruskan'   => 'telah diteruskan ke instansi terkait.',
                'diproses'     => 'sedang dalam penanganan petugas.',
                'selesai'      => 'telah selesai ditangani. Terima kasih atas laporan Anda.',
                'ditolak'      => 'ditolak. ' . ($data['note'] ?? ''),
            ];
            $message = "Laporan Anda #{$report['ticket_number']} " . ($notifText[$data['status']] ?? 'telah diperbarui.');

            $pdo->prepare(
                'INSERT INTO notifications (user_id, report_id, title, message, type, is_read) VALUES (?, ?, ?, ?, ?, 0)'
            )->execute([$report['user_id'], $reportId, 'Pembaruan Laporan', $message, 'status']);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function statusHistory(int $reportId): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM report_status_history WHERE report_id = ? ORDER BY created_at ASC'
        );
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    /** Ringkasan angka laporan yang ditugaskan ke petugas tertentu, untuk dashboard petugas. */
    public static function countsForOfficer(int $officerId): array
    {
        $stmt = db()->prepare(
            "SELECT status, COUNT(*) AS total FROM reports
             WHERE deleted_at IS NULL AND assigned_to = ? GROUP BY status"
        );
        $stmt->execute([$officerId]);
        $rows = $stmt->fetchAll();
        $counts = ['diajukan' => 0, 'diverifikasi' => 0, 'diteruskan' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];
        foreach ($rows as $r) {
            $counts[$r['status']] = (int) $r['total'];
        }
        $counts['total'] = array_sum($counts);
        $counts['dalam_penanganan'] = $counts['diverifikasi'] + $counts['diteruskan'] + $counts['diproses'];
        return $counts;
    }

    public static function latestForOfficer(int $officerId, int $limit = 5): array
    {
        $stmt = db()->prepare(
            "SELECT r.id, r.ticket_number, r.title, r.status, r.created_at,
                    u.nama AS pelapor_nama, c.nama AS category_name
             FROM reports r
             JOIN users u ON u.id = r.user_id
             JOIN categories c ON c.id = r.category_id
             WHERE r.deleted_at IS NULL AND r.assigned_to = ?
             ORDER BY r.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $officerId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function updates(int $reportId): array
    {
        $stmt = db()->prepare(
            "SELECT ru.*, u.nama AS officer_name
             FROM report_updates ru
             JOIN users u ON u.id = ru.user_id
             WHERE ru.report_id = ?
             ORDER BY ru.created_at ASC"
        );
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }
}
