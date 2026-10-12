<?php
/**
 * TaskMate - Task Repository & Business Logic
 */

require_once __DIR__ . '/../config/config.php';

function get_task_statistics(int $userId): array {
    $pdo = Database::getConnection();

    // Standardize query for both SQLite and MySQL safely:
    $now = date('Y-m-d H:i:s');
    $in3Days = date('Y-m-d H:i:s', strtotime('+3 days'));

    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        COALESCE(SUM(CASE WHEN status = 'Belum Dikerjakan' THEN 1 ELSE 0 END), 0) as belum_dikerjakan,
        COALESCE(SUM(CASE WHEN status = 'Sedang Dikerjakan' THEN 1 ELSE 0 END), 0) as sedang_dikerjakan,
        COALESCE(SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END), 0) as selesai,
        COALESCE(SUM(CASE WHEN status != 'Selesai' AND deadline < ? THEN 1 ELSE 0 END), 0) as terlambat,
        COALESCE(SUM(CASE WHEN status != 'Selesai' AND deadline >= ? AND deadline <= ? THEN 1 ELSE 0 END), 0) as mendekati_deadline
    FROM tasks WHERE user_id = ?");

    $stmt->execute([$now, $now, $in3Days, $userId]);
    $stats = $stmt->fetch();

    $total = (int)($stats['total'] ?? 0);
    $selesai = (int)($stats['selesai'] ?? 0);
    $persen = $total > 0 ? (int)round(($selesai / $total) * 100) : 0;

    return [
        'total' => $total,
        'belum_dikerjakan' => (int)($stats['belum_dikerjakan'] ?? 0),
        'sedang_dikerjakan' => (int)($stats['sedang_dikerjakan'] ?? 0),
        'selesai' => $selesai,
        'terlambat' => (int)($stats['terlambat'] ?? 0),
        'mendekati_deadline' => (int)($stats['mendekati_deadline'] ?? 0),
        'persentase_selesai' => $persen
    ];
}

function get_user_tasks(int $userId, array $filters = []): array {
    $pdo = Database::getConnection();

    $sql = "SELECT * FROM tasks WHERE user_id = :user_id";
    $params = [':user_id' => $userId];

    // Search keyword
    if (!empty($filters['search'])) {
        $keyword = '%' . trim($filters['search']) . '%';
        $sql .= " AND (judul LIKE :search1 OR mata_kuliah LIKE :search2 OR deskripsi LIKE :search3)";
        $params[':search1'] = $keyword;
        $params[':search2'] = $keyword;
        $params[':search3'] = $keyword;
    }

    // Filter status
    if (!empty($filters['status']) && in_array($filters['status'], ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    }

    // Filter prioritas
    if (!empty($filters['prioritas']) && in_array($filters['prioritas'], ['Rendah', 'Sedang', 'Tinggi'])) {
        $sql .= " AND prioritas = :prioritas";
        $params[':prioritas'] = $filters['prioritas'];
    }

    // Filter deadline
    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd   = date('Y-m-d 23:59:59');
    $now        = date('Y-m-d H:i:s');
    $weekEnd    = date('Y-m-d 23:59:59', strtotime('+7 days'));

    if (!empty($filters['time_filter'])) {
        switch ($filters['time_filter']) {
            case 'today':
                $sql .= " AND deadline BETWEEN :today_start AND :today_end";
                $params[':today_start'] = $todayStart;
                $params[':today_end']   = $todayEnd;
                break;
            case 'week':
                $sql .= " AND deadline BETWEEN :now AND :week_end";
                $params[':now']      = $now;
                $params[':week_end'] = $weekEnd;
                break;
            case 'overdue':
                $sql .= " AND deadline < :now_overdue AND status != 'Selesai'";
                $params[':now_overdue'] = $now;
                break;
            case 'upcoming':
                $sql .= " AND deadline >= :now_up AND status != 'Selesai'";
                $params[':now_up'] = $now;
                break;
        }
    }

    // Sorting
    $sort = $filters['sort'] ?? 'deadline_asc';
    switch ($sort) {
        case 'deadline_desc':
            $sql .= " ORDER BY deadline DESC";
            break;
        case 'priority_desc':
            // High -> Medium -> Low
            $sql .= " ORDER BY CASE prioritas 
                        WHEN 'Tinggi' THEN 1 
                        WHEN 'Sedang' THEN 2 
                        WHEN 'Rendah' THEN 3 
                        ELSE 4 END ASC, deadline ASC";
            break;
        case 'newest':
            $sql .= " ORDER BY created_at DESC";
            break;
        case 'oldest':
            $sql .= " ORDER BY created_at ASC";
            break;
        case 'deadline_asc':
        default:
            $sql .= " ORDER BY deadline ASC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_task_by_id(int $taskId, int $userId): ?array {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch();
    return $task ?: null;
}

function create_task(int $userId, array $data): int {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, judul, mata_kuliah, deskripsi, deadline, prioritas, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $data['judul'],
        $data['mata_kuliah'],
        $data['deskripsi'] ?? null,
        $data['deadline'],
        $data['prioritas'] ?? 'Sedang',
        $data['status'] ?? 'Belum Dikerjakan'
    ]);
    return (int)$pdo->lastInsertId();
}

function update_task(int $taskId, int $userId, array $data): bool {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE tasks 
                           SET judul = ?, mata_kuliah = ?, deskripsi = ?, deadline = ?, prioritas = ?, status = ?, updated_at = ?
                           WHERE id = ? AND user_id = ?");
    return $stmt->execute([
        $data['judul'],
        $data['mata_kuliah'],
        $data['deskripsi'] ?? null,
        $data['deadline'],
        $data['prioritas'],
        $data['status'],
        date('Y-m-d H:i:s'),
        $taskId,
        $userId
    ]);
}

function delete_task(int $taskId, int $userId): bool {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    return $stmt->execute([$taskId, $userId]);
}

function update_task_status(int $taskId, int $userId, string $newStatus): bool {
    if (!in_array($newStatus, ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'])) {
        return false;
    }
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = ? WHERE id = ? AND user_id = ?");
    return $stmt->execute([$newStatus, date('Y-m-d H:i:s'), $taskId, $userId]);
}

function get_urgent_tasks(int $userId, int $limit = 5): array {
    $pdo = Database::getConnection();
    // Tugas yang belum selesai, diurutkan dari deadline terdekat (termasuk yang terlambat)
    $stmt = $pdo->prepare("SELECT * FROM tasks 
                           WHERE user_id = ? AND status != 'Selesai' 
                           ORDER BY deadline ASC 
                           LIMIT " . (int)$limit);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_recent_tasks(int $userId, int $limit = 5): array {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM tasks 
                           WHERE user_id = ? 
                           ORDER BY created_at DESC 
                           LIMIT " . (int)$limit);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_tasks_for_calendar(int $userId, int $year, int $month): array {
    $pdo = Database::getConnection();
    $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $endDate = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $daysInMonth);

    $stmt = $pdo->prepare("SELECT id, judul, mata_kuliah, deadline, prioritas, status 
                           FROM tasks 
                           WHERE user_id = ? AND deadline BETWEEN ? AND ? 
                           ORDER BY deadline ASC");
    $stmt->execute([$userId, $startDate, $endDate]);
    return $stmt->fetchAll();
}

function get_deadline_notifications(int $userId): array {
    $pdo = Database::getConnection();
    $now = date('Y-m-d H:i:s');
    $in48h = date('Y-m-d H:i:s', strtotime('+48 hours'));

    // Ambil tugas belum selesai yang terlambat ATAU jatuh tempo dalam 48 jam
    $stmt = $pdo->prepare("SELECT id, judul, mata_kuliah, deadline, prioritas, status 
                           FROM tasks 
                           WHERE user_id = ? AND status != 'Selesai' AND deadline <= ?
                           ORDER BY deadline ASC 
                           LIMIT 10");
    $stmt->execute([$userId, $in48h]);
    $tasks = $stmt->fetchAll();

    $notifications = [];
    foreach ($tasks as $t) {
        $info = get_deadline_info($t['deadline'], $t['status']);
        $notifications[] = [
            'id' => $t['id'],
            'judul' => $t['judul'],
            'mata_kuliah' => $t['mata_kuliah'],
            'deadline' => $t['deadline'],
            'info_text' => $info['text'],
            'is_overdue' => $info['is_overdue'],
            'prioritas' => $t['prioritas']
        ];
    }
    return $notifications;
}
