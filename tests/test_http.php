<?php
/**
 * Test HTTP request, login flow, cookie session, and dashboard rendering
 */
$ch = curl_init('http://127.0.0.1:8000/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'mahasiswa@taskmate.id',
    'password' => 'password123'
]));
$cookieFile = __DIR__ . '/test_cookie.txt';
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$info = curl_getinfo($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Effective URL: " . $info['url'] . "\n";
echo "Redirected to Dashboard: " . (strpos($info['url'], 'dashboard.php') !== false ? "YES (BERHASIL)" : "NO") . "\n";
echo "Contains Greeting: " . (strpos($response, 'Halo, Ahmad Fauzi Rahman!') !== false ? "YES (BERHASIL)" : "NO") . "\n";
echo "Contains 5 Stats Cards: " . (strpos($response, 'Total Seluruh Tugas') !== false ? "YES (BERHASIL)" : "NO") . "\n";

// Test mengakses tasks.php dengan session cookie yang sama
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/tasks.php');
curl_setopt($ch, CURLOPT_HTTPGET, true);
$tasksResponse = curl_exec($ch);
echo "Access tasks.php with Cookie: " . (strpos($tasksResponse, 'Semua Tugas Mahasiswa') !== false ? "YES (BERHASIL)" : "NO") . "\n";

// Test mengakses calendar.php
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/calendar.php');
$calResponse = curl_exec($ch);
echo "Access calendar.php with Cookie: " . (strpos($calResponse, 'Kalender Deadline') !== false ? "YES (BERHASIL)" : "NO") . "\n";

// Test mengakses profile.php
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/profile.php');
$profResponse = curl_exec($ch);
echo "Access profile.php with Cookie: " . (strpos($profResponse, 'Profil Pengguna') !== false ? "YES (BERHASIL)" : "NO") . "\n";

curl_close($ch);
if (file_exists($cookieFile)) unlink($cookieFile);
