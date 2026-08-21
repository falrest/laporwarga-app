<?php
/**
 * Model Location: districts (kecamatan) & villages (desa/kelurahan).
 */

class Location
{
    public static function allDistricts(): array
    {
        $stmt = db()->query('SELECT id, name FROM districts ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public static function villagesByDistrict(int $districtId): array
    {
        $stmt = db()->prepare('SELECT id, name FROM villages WHERE district_id = ? ORDER BY name ASC');
        $stmt->execute([$districtId]);
        return $stmt->fetchAll();
    }

    public static function districtExists(int $id): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM districts WHERE id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function villageExists(int $id, int $districtId): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM villages WHERE id = ? AND district_id = ?');
        $stmt->execute([$id, $districtId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
