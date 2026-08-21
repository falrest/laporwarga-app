<?php
/**
 * Model Category & Subcategory (section 9: harus berasal dari database).
 */

class Category
{
    public static function allActive(): array
    {
        $stmt = db()->query(
            "SELECT id, nama, icon FROM categories WHERE status = 'aktif' ORDER BY nama ASC"
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function subcategories(int $categoryId): array
    {
        $stmt = db()->prepare(
            "SELECT id, nama FROM subcategories
             WHERE category_id = ? AND status = 'aktif' ORDER BY nama ASC"
        );
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    // =====================================================================
    // ADMIN CRUD
    // =====================================================================

    public static function allWithSubcategoryCount(): array
    {
        $stmt = db()->query(
            "SELECT c.*, (SELECT COUNT(*) FROM subcategories s WHERE s.category_id = c.id AND s.status='aktif') AS subcategory_count
             FROM categories c ORDER BY c.nama ASC"
        );
        return $stmt->fetchAll();
    }

    public static function allSubcategoriesOf(int $categoryId): array
    {
        $stmt = db()->prepare('SELECT * FROM subcategories WHERE category_id = ? ORDER BY nama ASC');
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public static function create(string $nama, string $icon): int
    {
        $stmt = db()->prepare('INSERT INTO categories (nama, icon, status) VALUES (?, ?, "aktif")');
        $stmt->execute([$nama, $icon]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $nama, string $icon): void
    {
        db()->prepare('UPDATE categories SET nama = ?, icon = ? WHERE id = ?')->execute([$nama, $icon, $id]);
    }

    public static function toggleStatus(int $id): void
    {
        db()->prepare(
            "UPDATE categories SET status = IF(status='aktif','nonaktif','aktif') WHERE id = ?"
        )->execute([$id]);
    }

    public static function createSubcategory(int $categoryId, string $nama): int
    {
        $stmt = db()->prepare('INSERT INTO subcategories (category_id, nama, status) VALUES (?, ?, "aktif")');
        $stmt->execute([$categoryId, $nama]);
        return (int) db()->lastInsertId();
    }

    public static function updateSubcategory(int $id, string $nama): void
    {
        db()->prepare('UPDATE subcategories SET nama = ? WHERE id = ?')->execute([$nama, $id]);
    }

    public static function toggleSubcategoryStatus(int $id): void
    {
        db()->prepare(
            "UPDATE subcategories SET status = IF(status='aktif','nonaktif','aktif') WHERE id = ?"
        )->execute([$id]);
    }
}
