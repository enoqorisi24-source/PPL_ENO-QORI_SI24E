-- Database: `taskmate`
-- Aplikasi Pengelolaan Tugas Mahasiswa

CREATE DATABASE IF NOT EXISTS `taskmate` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taskmate`;

-- --------------------------------------------------------
-- Struktur Tabel `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nim` VARCHAR(30) DEFAULT NULL,
  `universitas` VARCHAR(100) DEFAULT 'Universitas Indonesia',
  `jurusan` VARCHAR(100) DEFAULT 'Teknik Informatika',
  `foto_profil` VARCHAR(255) DEFAULT 'default-avatar.png',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur Tabel `tasks`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `judul` VARCHAR(200) NOT NULL,
  `mata_kuliah` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `deadline` DATETIME NOT NULL,
  `prioritas` ENUM('Rendah', 'Sedang', 'Tinggi') NOT NULL DEFAULT 'Sedang',
  `status` ENUM('Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai') NOT NULL DEFAULT 'Belum Dikerjakan',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tasks_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Data Awal (Sample Data untuk Pengujian)
-- Akun demo:
-- Email: mahasiswa@taskmate.id
-- Password: password123 (hashed bcrypt)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `nim`, `universitas`, `jurusan`) VALUES
(1, 'Ahmad Fauzi Rahman', 'mahasiswa@taskmate.id', '$2y$10$wT5M2m5Y2sA9eZ4c7p7OReUo27hVzR7N5W8z6R.uQZ12kL8P3aO9W', '2201011054', 'Universitas Dian Nuswantoro', 'Teknik Informatika')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Sample Tasks untuk Mahasiswa Demo
INSERT INTO `tasks` (`id`, `user_id`, `judul`, `mata_kuliah`, `deskripsi`, `deadline`, `prioritas`, `status`, `created_at`) VALUES
(1, 1, 'Membuat ERD & Skema Database E-Commerce', 'Basis Data Lanjut', 'Rancang normalisasi sampai 3NF dan buat diagram relasi tabel lengkap dengan constraint foreign key.', DATE_ADD(NOW(), INTERVAL 1 DAY), 'Tinggi', 'Sedang Dikerjakan', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 1, 'Laporan Praktikum Pemrograman Jaringan Socket', 'Jaringan Komputer', 'Analisis sniffing paket data menggunakan Wireshark dan implementasi client-server TCP di Python.', DATE_ADD(NOW(), INTERVAL 2 DAY), 'Sedang', 'Belum Dikerjakan', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 1, 'Review Jurnal AI: Transformer Architecture', 'Kecerdasan Buatan', 'Buat rangkuman 4 halaman tentang Attention Mechanism dan performanya pada Natural Language Processing.', DATE_ADD(NOW(), INTERVAL 4 DAY), 'Rendah', 'Belum Dikerjakan', NOW()),
(4, 1, 'Tugas Besar Prototype UI/UX Mobile App di Figma', 'Interaksi Manusia dan Komputer', 'Desain high-fidelity mockup untuk aplikasi edukasi anak, lengkap dengan interactive prototype & usability testing.', DATE_ADD(NOW(), INTERVAL 6 DAY), 'Tinggi', 'Belum Dikerjakan', NOW()),
(5, 1, 'Kuis Online Algoritma Dijkstra & Bellman-Ford', 'Algoritma & Struktur Data', 'Pelajari materi graph traversal dan shortest path sebelum kuis dimulai pukul 14:00 WIB.', DATE_ADD(NOW(), INTERVAL -1 DAY), 'Tinggi', 'Selesai', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 1, 'Makalah Etika Profesi IT & Keamanan Siber', 'Etika Profesi', 'Bahas kasus kebocoran data di instansi pemerintah dan regulasi UU PDP di Indonesia.', DATE_ADD(NOW(), INTERVAL 9 DAY), 'Sedang', 'Sedang Dikerjakan', DATE_SUB(NOW(), INTERVAL 3 DAY))
ON DUPLICATE KEY UPDATE `id` = `id`;
