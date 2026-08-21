-- =========================================================
-- LAPORWARGA - DATABASE SCHEMA
-- Sistem Informasi Pengaduan Masyarakat
-- Import melalui phpMyAdmin: klik tab "Import", pilih file ini, jalankan.
-- =========================================================

CREATE DATABASE IF NOT EXISTS `laporwarga`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `laporwarga`;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- roles
-- ---------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(30) NOT NULL,
  UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- districts (kecamatan)
-- ---------------------------------------------------------
CREATE TABLE `districts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- villages (desa/kelurahan)
-- ---------------------------------------------------------
CREATE TABLE `villages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `district_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  KEY `idx_villages_district` (`district_id`),
  CONSTRAINT `fk_villages_district` FOREIGN KEY (`district_id`)
    REFERENCES `districts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- users
-- ---------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT UNSIGNED NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `nik` CHAR(16) NULL,
  `email` VARCHAR(150) NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `foto` VARCHAR(255) NULL,
  `alamat` VARCHAR(255) NULL,
  `kecamatan_id` INT UNSIGNED NULL,
  `desa_id` INT UNSIGNED NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `failed_login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME NULL,
  `remember_token` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_nik` (`nik`),
  UNIQUE KEY `uq_users_no_hp` (`no_hp`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_kecamatan` (`kecamatan_id`),
  KEY `idx_users_desa` (`desa_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`)
    REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_users_kecamatan` FOREIGN KEY (`kecamatan_id`)
    REFERENCES `districts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_desa` FOREIGN KEY (`desa_id`)
    REFERENCES `villages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: `email` and `nik` are UNIQUE but NULLable — MySQL allows multiple
-- NULLs in a unique index, so an optional email/NIK will not collide.

-- ---------------------------------------------------------
-- categories
-- ---------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- subcategories
-- ---------------------------------------------------------
CREATE TABLE `subcategories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_subcategories_category` (`category_id`),
  CONSTRAINT `fk_subcategories_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- reports
-- ---------------------------------------------------------
CREATE TABLE `reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` VARCHAR(20) NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `subcategory_id` INT UNSIGNED NOT NULL,
  `assigned_to` INT UNSIGNED NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `latitude` DECIMAL(10,7) NOT NULL,
  `longitude` DECIMAL(10,7) NOT NULL,
  `incident_date` DATE NOT NULL,
  `status` ENUM('diajukan','diverifikasi','diteruskan','diproses','selesai','ditolak')
    NOT NULL DEFAULT 'diajukan',
  `rejection_reason` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  UNIQUE KEY `uq_reports_ticket` (`ticket_number`),
  KEY `idx_reports_user` (`user_id`),
  KEY `idx_reports_category` (`category_id`),
  KEY `idx_reports_subcategory` (`subcategory_id`),
  KEY `idx_reports_assigned_to` (`assigned_to`),
  KEY `idx_reports_status` (`status`),
  KEY `idx_reports_created_at` (`created_at`),
  CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_subcategory` FOREIGN KEY (`subcategory_id`)
    REFERENCES `subcategories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_assigned_to` FOREIGN KEY (`assigned_to`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- report_files
-- ---------------------------------------------------------
CREATE TABLE `report_files` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `uploaded_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_report_files_report` (`report_id`),
  CONSTRAINT `fk_report_files_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_report_files_uploader` FOREIGN KEY (`uploaded_by`)
    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- report_status_history
-- ---------------------------------------------------------
CREATE TABLE `report_status_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT UNSIGNED NOT NULL,
  `status` ENUM('diajukan','diverifikasi','diteruskan','diproses','selesai','ditolak') NOT NULL,
  `description` VARCHAR(255) NULL,
  `changed_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_history_report` (`report_id`),
  CONSTRAINT `fk_history_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_history_changed_by` FOREIGN KEY (`changed_by`)
    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- report_updates (komentar / progress petugas)
-- ---------------------------------------------------------
CREATE TABLE `report_updates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `photo` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_updates_report` (`report_id`),
  CONSTRAINT `fk_updates_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_updates_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- notifications
-- ---------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `report_id` INT UNSIGNED NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_notifications_user` (`user_id`),
  KEY `idx_notifications_report` (`report_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_report` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- activity_logs
-- ---------------------------------------------------------
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(60) NULL,
  `record_id` INT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_logs_user` (`user_id`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- SEED DATA
-- =========================================================

INSERT INTO `roles` (`id`, `name`) VALUES
  (1, 'admin'),
  (2, 'petugas'),
  (3, 'masyarakat');

INSERT INTO `districts` (`id`, `name`) VALUES
  (1, 'Jekulo'),
  (2, 'Kota Kudus'),
  (3, 'Gebog'),
  (4, 'Bae');

INSERT INTO `villages` (`id`, `district_id`, `name`) VALUES
  (1, 1, 'Hongosoco'),
  (2, 1, 'Getasrabi'),
  (3, 1, 'Gondoharum'),
  (4, 2, 'Kajeksan'),
  (5, 2, 'Panjunan'),
  (6, 3, 'Getasan'),
  (7, 4, 'Bae');

-- Kategori & subkategori (section 9)
INSERT INTO `categories` (`id`, `nama`, `icon`) VALUES
  (1, 'Infrastruktur & Fasilitas Umum', 'infrastruktur'),
  (2, 'Kebersihan & Sampah', 'sampah'),
  (3, 'Penerangan', 'lampu'),
  (4, 'Lingkungan', 'pohon'),
  (5, 'Drainase & Banjir', 'drainase'),
  (6, 'Transportasi', 'transportasi'),
  (7, 'Pendidikan', 'pendidikan'),
  (8, 'Kesehatan', 'kesehatan');

INSERT INTO `subcategories` (`category_id`, `nama`) VALUES
  (1, 'Taman & Ruang Terbuka'),
  (1, 'Transportasi'),
  (1, 'Drainase'),
  (1, 'Fasilitas Pendidikan'),
  (1, 'Fasilitas Kesehatan'),
  (1, 'Fasilitas Olahraga'),
  (1, 'Sanitasi'),
  (1, 'Pejalan Kaki'),
  (2, 'Tumpukan Sampah Liar'),
  (2, 'TPS Penuh'),
  (2, 'Sampah Sungai'),
  (3, 'Lampu Jalan Mati'),
  (3, 'Instalasi Kabel Berbahaya'),
  (5, 'Saluran Tersumbat'),
  (5, 'Banjir/Genangan'),
  (6, 'Jalan Berlubang'),
  (6, 'Kemacetan');

-- Users: password_hash() bcrypt untuk masing-masing akun demo.
-- admin@laporwarga.test    / Admin123!
-- petugas@laporwarga.test  / Petugas123!
-- warga@laporwarga.test    / Warga123!
INSERT INTO `users`
  (`id`, `role_id`, `nama`, `nik`, `email`, `no_hp`, `password`, `alamat`, `kecamatan_id`, `desa_id`, `status`)
VALUES
  (1, 1, 'Administrator', '3271010101010001', 'admin@laporwarga.test', '081100000001',
   '$2b$10$aBbPeE/cqtmXLckn0FCsLugOtKoBja7cVRwvF5Y.lBlGd5fM9g2WK',
   'Kantor Dinas Kominfo', 2, 4, 'aktif'),
  (2, 2, 'Budi Santoso', '3271010101010002', 'petugas@laporwarga.test', '081100000002',
   '$2b$10$Po7L/URv3kAkFho6imT78.S2NrNg1HON7E1qqMllCJoGVsMvfzpkC',
   'Dinas Pekerjaan Umum', 2, 4, 'aktif'),
  (3, 3, 'Daffa Naufal', '3271010101010003', 'warga@laporwarga.test', '085612340001',
   '$2b$10$qGLQBChUVFml3k/iK4BxCO5wAhE.kSSa2GQmbywVC/Z7Ol9HHdY.2',
   'Jl. Hongosoco RT 04/RW 02', 1, 1, 'aktif');

-- Contoh laporan (mengikuti gambar referensi Laporan Saya / Detail Laporan)
INSERT INTO `reports`
  (`id`, `ticket_number`, `user_id`, `category_id`, `subcategory_id`, `assigned_to`,
   `title`, `description`, `address`, `latitude`, `longitude`, `incident_date`, `status`)
VALUES
  (1, 'LW-2026-000124', 3, 1, 16, 2,
   'Jalan Berlubang Dalam di Jl. Hongosoco',
   'Terdapat lubang besar dan dalam di tengah jalan yang sangat membahayakan pengendara motor, terutama saat malam hari karena minimnya penerangan. Sudah ada beberapa motor yang hampir jatuh minggu ini. Mohon segera diperbaiki sebelum memakan korban.',
   'Jl. Hongosoco, Kec. Jekulo, RT 04/RW 02', -6.7900000, 110.9200000, '2026-05-13', 'diproses');

INSERT INTO `report_status_history` (`report_id`, `status`, `description`, `changed_by`) VALUES
  (1, 'diajukan', 'Laporan dikirim oleh warga', 3),
  (1, 'diverifikasi', 'Laporan diverifikasi oleh admin', 1),
  (1, 'diteruskan', 'Diteruskan ke Dinas Pekerjaan Umum', 1),
  (1, 'diproses', 'Petugas mulai menangani laporan', 2);

INSERT INTO `report_updates` (`report_id`, `user_id`, `message`) VALUES
  (1, 2, 'Tim sedang melakukan pengerukan aspal lama sebelum ditambal ulang. Estimasi selesai sore ini.');

INSERT INTO `notifications` (`user_id`, `report_id`, `title`, `message`, `type`, `is_read`) VALUES
  (3, 1, 'Laporan Diverifikasi', 'Laporan Anda dengan nomor tiket #LW-2026-000124 telah berhasil diverifikasi.', 'status', 0),
  (3, 1, 'Petugas Menangani Laporan', 'Petugas dari Dinas Pekerjaan Umum sedang menuju lokasi untuk menangani.', 'assignment', 0);
