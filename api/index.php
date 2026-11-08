<?php
/**
 * TaskMate - Vercel Serverless Front Controller & Router
 * Memastikan semua request di Vercel dieksekusi oleh PHP Engine (bukan didownload sebagai file mentah).
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($requestUri, '/');

// 1. Jika request root (/), jalankan index.php
if ($path === '' || $path === 'api' || $path === 'api/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// 2. Jika request ke endpoint API lain (contoh: api/update_status.php atau api/get_calendar_events.php)
if (strpos($path, 'api/') === 0) {
    $apiFile = __DIR__ . '/../' . $path;
    if ($path !== 'api/index.php' && file_exists($apiFile) && is_file($apiFile)) {
        require $apiFile;
        exit;
    }
}

// 3. Jika request ke halaman PHP di root (contoh: login.php, dashboard.php, tasks.php, calendar.php)
$rootTarget = __DIR__ . '/../' . $path;
if (file_exists($rootTarget) && is_file($rootTarget)) {
    require $rootTarget;
    exit;
}

// 4. Dukungan clean URL tanpa ekstensi .php (contoh: /dashboard -> dashboard.php)
if (file_exists($rootTarget . '.php') && is_file($rootTarget . '.php')) {
    require $rootTarget . '.php';
    exit;
}

// 5. Fallback ke index.php
require __DIR__ . '/../index.php';
