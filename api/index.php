<?php
/**
 * TaskMate - Vercel Serverless Front Controller & Router
 * Memastikan semua request di Vercel dieksekusi oleh PHP Engine (bukan didownload sebagai file mentah).
 */

// 1. Set working directory ke root project agar semua include/require relative path berfungsi normal
chdir(dirname(__DIR__));

// 2. Ambil path request tanpa query string
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($requestUri, '/');

// 3. Jika request root (/), jalankan index.php
if ($path === '' || $path === 'api' || $path === 'api/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// 4. Jika request ke endpoint API lain (contoh: api/update_status.php atau api/get_calendar_events.php)
if (strpos($path, 'api/') === 0) {
    $apiFile = __DIR__ . '/../' . $path;
    if ($path !== 'api/index.php' && file_exists($apiFile) && is_file($apiFile)) {
        require $apiFile;
        exit;
    }
}

// 5. Jika request ke halaman PHP di root (contoh: login.php, dashboard.php, tasks.php, calendar.php, profile.php)
$rootTarget = __DIR__ . '/../' . $path;
if (file_exists($rootTarget) && is_file($rootTarget)) {
    require $rootTarget;
    exit;
}

// 6. Dukungan clean URL tanpa ekstensi .php (contoh: /dashboard -> dashboard.php, /login -> login.php)
if (file_exists($rootTarget . '.php') && is_file($rootTarget . '.php')) {
    require $rootTarget . '.php';
    exit;
}

// 7. Fallback ke index.php
require __DIR__ . '/../index.php';
