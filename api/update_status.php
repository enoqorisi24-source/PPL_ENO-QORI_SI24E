<?php
/**
 * API: Update Task Status
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/task_functions.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = current_user();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['task_id']) || empty($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

$taskId = (int)$input['task_id'];
$status = trim($input['status']);

if (!in_array($status, ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'])) {
    echo json_encode(['success' => false, 'message' => 'Nilai status tidak valid']);
    exit;
}

$ok = update_task_status($taskId, $user['id'], $status);

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengubah status tugas']);
}
