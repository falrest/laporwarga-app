<?php
/**
 * Konfigurasi koneksi database (PDO + MySQL).
 * Ubah kredensial di bawah sesuai environment XAMPP Anda.
 */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'laporwarga';
const DB_USER = 'root';
const DB_PASS = '';       // default XAMPP: password kosong
const DB_CHARSET = 'utf8mb4';

/**
 * Mengembalikan instance PDO singleton.
 * Menggunakan prepared statement secara default (ATTR_EMULATE_PREPARES = false)
 * agar query benar-benar di-parse sebagai prepared statement oleh MySQL.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jangan pernah menampilkan detail error database mentah ke user.
            error_log('DB Connection Error: ' . $e->getMessage());
            http_response_code(500);
            die('Terjadi kesalahan pada sistem. Silakan coba lagi nanti.');
        }
    }

    return $pdo;
}
