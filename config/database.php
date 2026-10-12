<?php
/**
 * TaskMate - Database Connection Manager
 * Mendukung koneksi MySQL (XAMPP/Hosting) dengan auto-fallback SQLite jika MySQL offline.
 */

class Database {
    private static ?PDO $instance = null;
    private static string $driverUsed = 'mysql';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Konfigurasi MySQL default (XAMPP standard)
        $mysqlHost = '127.0.0.1';
        $mysqlPort = '3306';
        $mysqlDb   = 'taskmate';
        $mysqlUser = 'root';
        $mysqlPass = '';

        // Opsi PDO
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // 1. Coba koneksi ke MySQL
        try {
            // Pertama hubungkan ke server MySQL
            $pdoServer = new PDO("mysql:host={$mysqlHost};port={$mysqlPort};charset=utf8mb4", $mysqlUser, $mysqlPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2 // timeout cepat jika MySQL belum running
            ]);

            // Buat database taskmate jika belum ada
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$mysqlDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Hubungkan langsung ke database taskmate
            $pdo = new PDO("mysql:host={$mysqlHost};port={$mysqlPort};dbname={$mysqlDb};charset=utf8mb4", $mysqlUser, $mysqlPass, $options);
            
            // Inisialisasi tabel jika belum ada
            self::initMysqlTables($pdo);

            self::$driverUsed = 'mysql';
            self::$instance = $pdo;
            return self::$instance;

        } catch (Throwable $e) {
            // 2. Jika MySQL tidak aktif/gagal, fallback secara otomatis ke SQLite
            return self::initSqliteFallback($options);
        }
    }

    public static function getDriverUsed(): string {
        return self::$driverUsed;
    }

    private static function initMysqlTables(PDO $pdo): void {
        // Cek apakah tabel sudah ada
        $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if (!$check) {
            $pdo->exec("
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
            ");

            self::seedDefaultData($pdo, 'mysql');
        }
    }

    private static function initSqliteFallback(array $options): PDO {
        $dbDir = dirname(__DIR__) . '/database';
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }
        $sqlitePath = $dbDir . '/taskmate.sqlite';
        $isNew = !file_exists($sqlitePath);

        $pdo = new PDO("sqlite:{$sqlitePath}", null, null, $options);
        $pdo->exec("PRAGMA foreign_keys = ON;");

        if ($isNew || filesize($sqlitePath) === 0) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                  `nama` TEXT NOT NULL,
                  `email` TEXT NOT NULL UNIQUE,
                  `password` TEXT NOT NULL,
                  `nim` TEXT DEFAULT NULL,
                  `universitas` TEXT DEFAULT 'Universitas Indonesia',
                  `jurusan` TEXT DEFAULT 'Teknik Informatika',
                  `foto_profil` TEXT DEFAULT 'default-avatar.png',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS `tasks` (
                  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                  `user_id` INTEGER NOT NULL,
                  `judul` TEXT NOT NULL,
                  `mata_kuliah` TEXT NOT NULL,
                  `deskripsi` TEXT DEFAULT NULL,
                  `deadline` DATETIME NOT NULL,
                  `prioritas` TEXT NOT NULL DEFAULT 'Sedang',
                  `status` TEXT NOT NULL DEFAULT 'Belum Dikerjakan',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
                );
            ");

            self::seedDefaultData($pdo, 'sqlite');
        }

        self::$driverUsed = 'sqlite';
        self::$instance = $pdo;
        return self::$instance;
    }

    private static function seedDefaultData(PDO $pdo, string $driver): void {
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count == 0) {
            // Password hashed untuk: password123
            $hashedPass = password_hash('password123', PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, nim, universitas, jurusan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Ahmad Fauzi Rahman', 'mahasiswa@taskmate.id', $hashedPass, '2201011054', 'Universitas Dian Nuswantoro', 'Teknik Informatika']);
            $userId = (int)$pdo->lastInsertId();

            // Format tanggal deadline yang dinamis
            $now = time();
            $dPlus1 = date('Y-m-d H:i:s', $now + 86400 * 1);
            $dPlus2 = date('Y-m-d 23:59:00', $now + 86400 * 2);
            $dPlus4 = date('Y-m-d 17:00:00', $now + 86400 * 4);
            $dPlus6 = date('Y-m-d 23:59:00', $now + 86400 * 6);
            $dMinus1 = date('Y-m-d 14:00:00', $now - 86400 * 1);
            $dPlus9 = date('Y-m-d 12:00:00', $now + 86400 * 9);

            $tasks = [
                [$userId, 'Membuat ERD & Skema Database E-Commerce', 'Basis Data Lanjut', 'Rancang normalisasi sampai 3NF dan buat diagram relasi tabel lengkap dengan constraint foreign key.', $dPlus1, 'Tinggi', 'Sedang Dikerjakan'],
                [$userId, 'Laporan Praktikum Pemrograman Jaringan Socket', 'Jaringan Komputer', 'Analisis sniffing paket data menggunakan Wireshark dan implementasi client-server TCP di Python.', $dPlus2, 'Sedang', 'Belum Dikerjakan'],
                [$userId, 'Review Jurnal AI: Transformer Architecture', 'Kecerdasan Buatan', 'Buat rangkuman 4 halaman tentang Attention Mechanism dan performanya pada Natural Language Processing.', $dPlus4, 'Rendah', 'Belum Dikerjakan'],
                [$userId, 'Tugas Besar Prototype UI/UX Mobile App di Figma', 'Interaksi Manusia dan Komputer', 'Desain high-fidelity mockup untuk aplikasi edukasi anak, lengkap dengan interactive prototype & usability testing.', $dPlus6, 'Tinggi', 'Belum Dikerjakan'],
                [$userId, 'Kuis Online Algoritma Dijkstra & Bellman-Ford', 'Algoritma & Struktur Data', 'Pelajari materi graph traversal dan shortest path sebelum kuis dimulai pukul 14:00 WIB.', $dMinus1, 'Tinggi', 'Selesai'],
                [$userId, 'Makalah Etika Profesi IT & Keamanan Siber', 'Etika Profesi', 'Bahas kasus kebocoran data di instansi pemerintah dan regulasi UU PDP di Indonesia.', $dPlus9, 'Sedang', 'Sedang Dikerjakan']
            ];

            $stmtTask = $pdo->prepare("INSERT INTO tasks (user_id, judul, mata_kuliah, deskripsi, deadline, prioritas, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($tasks as $t) {
                $stmtTask->execute($t);
            }
        }
    }
}
