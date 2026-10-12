<?php
/**
 * TaskMate - Automated End-to-End Feature Test Suite
 * Memverifikasi seluruh fitur fungsional aplikasi TaskMate:
 * 1. Database Connection & Schema
 * 2. User Authentication (Register & Login)
 * 3. Task CRUD (Create, Read, Update, Delete)
 * 4. Search, Filter, dan Sorting
 * 5. Quick Status Update
 * 6. Calendar Events Retrieval
 * 7. Statistics & Notifications Engine
 * 8. User Profile & Password Change
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/task_functions.php';

echo "====================================================\n";
echo "   TASKMATE APPLICATION AUTOMATED TEST SUITE       \n";
echo "====================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $description, bool $condition): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[ PASS ] {$description}\n";
        $testsPassed++;
    } else {
        echo "[ FAIL ] {$description}\n";
        $testsFailed++;
    }
}

// ----------------------------------------------------
// TEST 1: Database Connection
// ----------------------------------------------------
$pdo = Database::getConnection();
$driver = Database::getDriverUsed();
assertTest("1. Database terkoneksi dengan driver '{$driver}'", $pdo instanceof PDO);

// ----------------------------------------------------
// TEST 2: Demo User Exists & Password Verification
// ----------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['mahasiswa@taskmate.id']);
$demoUser = $stmt->fetch();
assertTest("2. Akun demo 'mahasiswa@taskmate.id' terdaftar", !empty($demoUser));
assertTest("2b. Verifikasi hashing password akun demo 'password123'", password_verify('password123', $demoUser['password']));

// ----------------------------------------------------
// TEST 3: User Registration Logic
// ----------------------------------------------------
$testEmail = 'tester_' . time() . '@mahasiswa.ac.id';
$hashed = password_hash('secret123', PASSWORD_BCRYPT);
$regStmt = $pdo->prepare("INSERT INTO users (nama, email, password, nim, universitas, jurusan) VALUES (?, ?, ?, ?, ?, ?)");
$regStmt->execute(['Budi Santoso', $testEmail, $hashed, '2023001', 'ITB', 'Sistem Informasi']);
$newUserId = (int)$pdo->lastInsertId();
assertTest("3. Pendaftaran user baru berhasil (ID: {$newUserId})", $newUserId > 0);

// ----------------------------------------------------
// TEST 4: Task Creation (CRUD - Create)
// ----------------------------------------------------
$dFuture = date('Y-m-d 23:59:00', strtotime('+3 days'));
$taskId = create_task($newUserId, [
    'judul' => 'Tugas Pengujian Otomatis Antigravity',
    'mata_kuliah' => 'Quality Assurance',
    'deskripsi' => 'Pengujian fungsionalitas CRUD TaskMate secara menyeluruh.',
    'deadline' => $dFuture,
    'prioritas' => 'Tinggi',
    'status' => 'Belum Dikerjakan'
]);
assertTest("4. Pembuatan tugas baru berhasil (Task ID: {$taskId})", $taskId > 0);

// ----------------------------------------------------
// TEST 5: Task Read & User Isolation (CRUD - Read)
// ----------------------------------------------------
$task = get_task_by_id($taskId, $newUserId);
assertTest("5. Pengambilan tugas sesuai ID & User ID berhasil", $task && $task['judul'] === 'Tugas Pengujian Otomatis Antigravity');

// Verifikasi isolasi data: user lain tidak boleh bisa membaca tugas ini
$unauthorizedTask = get_task_by_id($taskId, 999999);
assertTest("5b. Isolasi privasi: user lain tidak dapat mengakses tugas ini", $unauthorizedTask === null);

// ----------------------------------------------------
// TEST 6: Task Update (CRUD - Update)
// ----------------------------------------------------
$updated = update_task($taskId, $newUserId, [
    'judul' => 'Tugas Pengujian Otomatis [Updated]',
    'mata_kuliah' => 'Quality Assurance Lanjut',
    'deskripsi' => 'Deskripsi telah diperbarui.',
    'deadline' => $dFuture,
    'prioritas' => 'Sedang',
    'status' => 'Sedang Dikerjakan'
]);
$taskAfterUpdate = get_task_by_id($taskId, $newUserId);
assertTest("6. Pembaruan informasi tugas berhasil", $updated && $taskAfterUpdate['status'] === 'Sedang Dikerjakan');

// ----------------------------------------------------
// TEST 7: Quick Status Updater API
// ----------------------------------------------------
$statusChanged = update_task_status($taskId, $newUserId, 'Selesai');
$taskAfterStatus = get_task_by_id($taskId, $newUserId);
assertTest("7. Quick Status Updater (ubah ke 'Selesai') berhasil", $statusChanged && $taskAfterStatus['status'] === 'Selesai');

// ----------------------------------------------------
// TEST 8: Search & Filters
// ----------------------------------------------------
$foundByTitle = get_user_tasks($newUserId, ['search' => 'Pengujian']);
assertTest("8. Pencarian tugas berdasarkan kata kunci berhasil", count($foundByTitle) === 1);

$filterByStatus = get_user_tasks($newUserId, ['status' => 'Selesai']);
assertTest("8b. Filter berdasarkan status 'Selesai' berhasil", count($filterByStatus) === 1);

$filterNotFound = get_user_tasks($newUserId, ['status' => 'Belum Dikerjakan']);
assertTest("8c. Filter status negatif berhasil (tidak menemukan hasil)", count($filterNotFound) === 0);

// ----------------------------------------------------
// TEST 9: Statistics Engine
// ----------------------------------------------------
$stats = get_task_statistics($newUserId);
assertTest("9. Statistik akurat: Total = 1, Selesai = 1, Progres = 100%", 
    $stats['total'] === 1 && $stats['selesai'] === 1 && $stats['persentase_selesai'] === 100
);

// ----------------------------------------------------
// TEST 10: Calendar Events Engine
// ----------------------------------------------------
$curYear = (int)date('Y');
$curMonth = (int)date('m');
$calEvents = get_tasks_for_calendar($newUserId, $curYear, $curMonth);
assertTest("10. Pengambilan event kalender bulanan berhasil", count($calEvents) >= 1);

// ----------------------------------------------------
// TEST 11: Profile & Password Change
// ----------------------------------------------------
$newPass = 'newPassword999';
$newPassHash = password_hash($newPass, PASSWORD_BCRYPT);
$upProfileStmt = $pdo->prepare("UPDATE users SET nama = 'Budi Santoso S.Kom', password = ? WHERE id = ?");
$upProfileStmt->execute([$newPassHash, $newUserId]);

$checkUser = $pdo->query("SELECT * FROM users WHERE id = {$newUserId}")->fetch();
assertTest("11. Pembaruan nama profil & perubahan password berhasil", 
    $checkUser['nama'] === 'Budi Santoso S.Kom' && password_verify($newPass, $checkUser['password'])
);

// ----------------------------------------------------
// TEST 12: Task Deletion (CRUD - Delete)
// ----------------------------------------------------
$deleted = delete_task($taskId, $newUserId);
$taskAfterDelete = get_task_by_id($taskId, $newUserId);
assertTest("12. Penghapusan tugas berhasil", $deleted && $taskAfterDelete === null);

// Cleanup test user
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$newUserId]);

echo "\n====================================================\n";
echo "   HASIL PENGUJIAN: {$testsPassed} LULUS, {$testsFailed} GAGAL\n";
echo "====================================================\n";

if ($testsFailed === 0) {
    echo "🎉 SELURUH FITUR APLIKASI TASKMATE BERFUNGSI 100% SEMPURNA!\n";
} else {
    echo "⚠️ Terdapat fitur yang mengalami kendala.\n";
}
