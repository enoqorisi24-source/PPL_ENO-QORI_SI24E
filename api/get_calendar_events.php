<?php
/**
 * API: Get Calendar Events for a Given Month and Year
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/task_functions.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = current_user();

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
    echo json_encode(['success' => false, 'message' => 'Format bulan/tahun salah']);
    exit;
}

$events = get_tasks_for_calendar($user['id'], $year, $month);

echo json_encode([
    'success' => true,
    'year' => $year,
    'month' => $month,
    'events' => $events
]);
